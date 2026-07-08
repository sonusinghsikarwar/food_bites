<?php
// login.php
require_once __DIR__ . '/includes/header.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit;
}

// ── CSRF Token Generation ──────────────────────────────────────────────────
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error      = '';
$lockoutMsg = '';
$isLocked   = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── CSRF Verification ──────────────────────────────────────────────────
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die('Invalid request. Please refresh the page and try again.');
    }

    $email    = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $ip       = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    if ($email && $password) {

        // ── Timing-attack safe dummy hash ──────────────────────────────────
        // Always call password_verify() regardless of user existence
        $dummyHash = '$2y$10$abcdefghijklmnopqrstuvuuuuuuuuuuuuuuuuuuuuuuuuuuu';

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // ── Brute-Force: Check lockout state ──────────────────────────────
        $maxAttempts = 5;
        $lockoutMins = 15;
        $attemptsNow = (int)($user['login_attempts'] ?? 0);
        $lastAttempt = $user['last_attempt_at'] ?? null;
        $lockedUntil = $lastAttempt ? strtotime($lastAttempt) + ($lockoutMins * 60) : 0;

        if ($attemptsNow >= $maxAttempts && time() < $lockedUntil) {
            $remainMins = ceil(($lockedUntil - time()) / 60);
            $isLocked   = true;
            $lockoutMsg = "Too many failed attempts. Account locked for {$remainMins} more minute(s). Please try again later.";
        } else {

            // ── Always run password_verify to prevent timing attacks ───────
            $validPassword = password_verify($password, $user['password'] ?? $dummyHash);

            if ($user && $validPassword) {
                if ($user['status'] === 'inactive') {
                    $error = 'Your account has been deactivated. Please contact support.';
                } else {
                    // ── Reset brute-force counter on success ───────────────
                    try {
                        $pdo->prepare("UPDATE users SET login_attempts=0, last_attempt_at=NULL WHERE id=?")
                            ->execute([$user['id']]);
                    } catch (Exception $e) { /* column may not exist yet */ }

                    // ── Session fixation protection ────────────────────────
                    session_regenerate_id(true);

                    $_SESSION['user_id']   = $user['id'];
                    $_SESSION['user_name'] = $user['name'];

                    // ── Transfer session cart → user cart ──────────────────
                    $sessId    = session_id();
                    $stmt2     = $pdo->prepare("SELECT * FROM carts WHERE session_id = ?");
                    $stmt2->execute([$sessId]);
                    $sessItems = $stmt2->fetchAll();

                    foreach ($sessItems as $item) {
                        $check = $pdo->prepare("SELECT id, quantity FROM carts WHERE user_id = ? AND product_id = ? LIMIT 1");
                        $check->execute([$user['id'], $item['product_id']]);
                        $existing = $check->fetch();
                        if ($existing) {
                            $newQty = $existing['quantity'] + $item['quantity'];
                            $pdo->prepare("UPDATE carts SET quantity = ? WHERE id = ?")
                                ->execute([$newQty, $existing['id']]);
                        } else {
                            $pdo->prepare("UPDATE carts SET user_id = ?, session_id = NULL WHERE id = ?")
                                ->execute([$user['id'], $item['id']]);
                        }
                    }
                    $pdo->prepare("DELETE FROM carts WHERE session_id = ?")->execute([$sessId]);

                    // ── Login audit log ────────────────────────────────────
                    try {
                        $pdo->prepare("INSERT INTO login_logs (user_id, user_type, ip_address, status) VALUES (?,?,?,?)")
                            ->execute([$user['id'], 'user', $ip, 'success']);
                    } catch (Exception $e) { /* table may not exist yet */ }

                    $_SESSION['success'] = "Welcome back, " . htmlspecialchars($user['name']);
                    header("Location: index.php");
                    exit;
                }
            } else {
                // ── Increment failed attempt counter ───────────────────────
                if ($user) {
                    // If previous lockout window expired, reset counter from 1
                    $newAttempts = ($attemptsNow >= $maxAttempts && time() >= $lockedUntil) ? 1 : $attemptsNow + 1;
                    try {
                        $pdo->prepare("UPDATE users SET login_attempts=?, last_attempt_at=NOW() WHERE id=?")
                            ->execute([$newAttempts, $user['id']]);
                    } catch (Exception $e) {}
                }

                // ── Log failed attempt ─────────────────────────────────────
                try {
                    $pdo->prepare("INSERT INTO login_logs (user_id, user_type, ip_address, status) VALUES (?,?,?,?)")
                        ->execute([$user['id'] ?? null, 'user', $ip, 'failed']);
                } catch (Exception $e) {}

                $attemptsLeft = max(0, $maxAttempts - ($attemptsNow + 1));
                if ($attemptsNow + 1 >= $maxAttempts) {
                    $error = "Account locked for {$lockoutMins} minutes due to too many failed attempts.";
                } elseif ($attemptsLeft <= 3 && $attemptsLeft > 0) {
                    $error = "Invalid email or password. ({$attemptsLeft} attempt(s) left before lockout)";
                } else {
                    $error = 'Invalid email or password.';
                }
            }
        }
    } else {
        $error = 'Please enter your email and password.';
    }
}
?>

<style>
@keyframes fadeInUp {
    from { opacity:0; transform:translateY(24px); }
    to   { opacity:1; transform:translateY(0); }
}
.glass-card { animation: fadeInUp 0.45s ease; }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="glass-card p-4 p-md-5">
                <div class="text-center mb-4">
                    <i class="fa-solid fa-lock text-warning display-4 mb-3"></i>
                    <h3 class="fw-bold text-dark dark-text-white mb-1">Customer Login</h3>
                    <p class="text-muted">Login to track and place your food orders</p>
                </div>

                <?php if ($lockoutMsg): ?>
                    <div class="alert border-0 rounded-4 py-3 mb-4 fw-semibold"
                         style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.2)!important; color:#dc2626;">
                        <i class="fa-solid fa-lock me-2"></i><?= htmlspecialchars($lockoutMsg) ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger border-0 rounded-4 py-3 mb-4">
                        <i class="fa-solid fa-circle-xmark me-2"></i><?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-warning border-0 rounded-4 py-3 mb-4">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST" id="loginForm" onsubmit="showBtnLoading()">
                    <!-- CSRF hidden field -->
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-xs text-uppercase tracking-wider text-muted">Email Address</label>
                        <input type="email" name="email"
                               class="form-control rounded-pill border-light p-3 shadow-none"
                               autocomplete="email" required
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               <?= $isLocked ? 'disabled' : '' ?>>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-xs text-uppercase tracking-wider text-muted">Password</label>
                        <div class="position-relative">
                            <input type="password" name="password" id="login-pass"
                                   class="form-control rounded-pill border-light p-3 pe-5 shadow-none"
                                   autocomplete="current-password" required
                                   <?= $isLocked ? 'disabled' : '' ?>>
                            <button type="button" onclick="togglePassVisibility()"
                                    class="btn position-absolute top-50 end-0 translate-middle-y me-3 border-0 bg-transparent p-0 shadow-none text-muted">
                                <i class="fa-solid fa-eye" id="toggle-pass-icon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-4 mt-2">
                        <div class="form-check">
                            <input type="checkbox" name="remember" class="form-check-input" id="rememberMe">
                            <label class="form-check-label text-xs text-muted" for="rememberMe">Remember Me</label>
                        </div>
                        <a href="register.php" class="text-xs text-warning fw-semibold text-decoration-none">Forgot Password?</a>
                    </div>

                    <button type="submit" id="login-btn"
                            class="btn btn-warning rounded-pill w-100 py-3 fw-bold"
                            <?= $isLocked ? 'disabled' : '' ?>>
                        <?= $isLocked
                            ? '<i class="fa-solid fa-lock me-2"></i>Account Locked'
                            : '<i class="fa-solid fa-right-to-bracket me-2"></i>Login' ?>
                    </button>

                    <p class="text-center text-muted small mt-4 mb-0">
                        Don't have an account?
                        <a href="register.php" class="text-warning fw-bold text-decoration-none"
                           style="border-bottom: 1.5px dashed var(--primary);">Register here</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassVisibility() {
    const passInput = document.getElementById('login-pass');
    const passIcon  = document.getElementById('toggle-pass-icon');
    if (passInput.type === 'password') {
        passInput.type = 'text';
        passIcon.className = 'fa-solid fa-eye-slash';
    } else {
        passInput.type = 'password';
        passIcon.className = 'fa-solid fa-eye';
    }
}

function showBtnLoading() {
    const btn = document.getElementById('login-btn');
    if (btn.disabled) return false;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Logging in...';
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
