<?php
// admin/index.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_helper.php';

if (isAdminLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

// ── CSRF Token (admin-specific key) ───────────────────────────────────────
if (empty($_SESSION['csrf_token_admin'])) {
    $_SESSION['csrf_token_admin'] = bin2hex(random_bytes(32));
}

$error      = '';
$lockoutMsg = '';
$isLocked   = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── CSRF Verification ──────────────────────────────────────────────────
    if (!hash_equals($_SESSION['csrf_token_admin'] ?? '', $_POST['csrf_token'] ?? '')) {
        die('Invalid request. Please refresh the page and try again.');
    }

    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $ip       = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    if ($username && $password) {

        // ── Timing-attack safe dummy hash ──────────────────────────────────
        $dummyHash = '$2y$10$abcdefghijklmnopqrstuvuuuuuuuuuuuuuuuuuuuuuuuuuuu';

        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        // ── Brute-Force: Check lockout state ──────────────────────────────
        $maxAttempts = 5;
        $lockoutMins = 15;
        $attemptsNow = (int)($admin['login_attempts'] ?? 0);
        $lastAttempt = $admin['last_attempt_at'] ?? null;
        $lockedUntil = $lastAttempt ? strtotime($lastAttempt) + ($lockoutMins * 60) : 0;

        if ($attemptsNow >= $maxAttempts && time() < $lockedUntil) {
            $remainMins = ceil(($lockedUntil - time()) / 60);
            $isLocked   = true;
            $lockoutMsg = "Too many failed attempts. Admin account locked for {$remainMins} more minute(s).";
        } else {

            // ── Always run password_verify to prevent timing attacks ───────
            $validPassword = password_verify($password, $admin['password'] ?? $dummyHash);

            if ($admin && $validPassword) {
                if ($admin['status'] === 'inactive') {
                    $error = 'Your admin account is inactive. Contact the super admin.';
                } else {
                    // ── Reset brute-force counter on success ───────────────
                    try {
                        $pdo->prepare("UPDATE admins SET login_attempts=0, last_attempt_at=NULL WHERE id=?")
                            ->execute([$admin['id']]);
                    } catch (Exception $e) {}

                    // ── Session fixation protection ────────────────────────
                    session_regenerate_id(true);

                    $_SESSION['admin_id']   = $admin['id'];
                    $_SESSION['admin_name'] = $admin['name'];
                    $_SESSION['admin_role'] = $admin['role'];

                    // ── Login audit log ────────────────────────────────────
                    try {
                        $pdo->prepare("INSERT INTO login_logs (user_id, user_type, ip_address, status) VALUES (?,?,?,?)")
                            ->execute([$admin['id'], 'admin', $ip, 'success']);
                    } catch (Exception $e) {}

                    header("Location: dashboard.php");
                    exit;
                }
            } else {
                // ── Increment failed attempt counter ───────────────────────
                if ($admin) {
                    $newAttempts = ($attemptsNow >= $maxAttempts && time() >= $lockedUntil) ? 1 : $attemptsNow + 1;
                    try {
                        $pdo->prepare("UPDATE admins SET login_attempts=?, last_attempt_at=NOW() WHERE id=?")
                            ->execute([$newAttempts, $admin['id']]);
                    } catch (Exception $e) {}
                }

                // ── Log failed attempt ─────────────────────────────────────
                try {
                    $pdo->prepare("INSERT INTO login_logs (user_id, user_type, ip_address, status) VALUES (?,?,?,?)")
                        ->execute([$admin['id'] ?? null, 'admin', $ip, 'failed']);
                } catch (Exception $e) {}

                $attemptsLeft = max(0, $maxAttempts - ($attemptsNow + 1));
                if ($attemptsNow + 1 >= $maxAttempts) {
                    $error = "Admin account locked for {$lockoutMins} minutes due to too many failed attempts.";
                } elseif ($attemptsLeft <= 3 && $attemptsLeft > 0) {
                    $error = "Invalid username or password. ({$attemptsLeft} attempt(s) left before lockout)";
                } else {
                    $error = 'Invalid username or password.';
                }
            }
        }
    } else {
        $error = 'Please fill out all fields.';
    }
}

$school_name = getSetting('restaurant_name', 'Crispy Bytes');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — <?= htmlspecialchars($school_name) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'Inter', sans-serif;
        background: #0d0d1a;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
    }

    /* Animated background blobs */
    .bg-blob {
        position: fixed;
        border-radius: 50%;
        filter: blur(80px);
        opacity: 0.18;
        animation: blobFloat 8s ease-in-out infinite alternate;
        pointer-events: none;
        z-index: 0;
    }
    .blob-1 { width:500px; height:500px; background:#FF6B35; top:-150px; right:-150px; animation-delay:0s; opacity: 0.15; }
    .blob-2 { width:400px; height:400px; background:#FFBE0B; bottom:-100px; left:-100px; animation-delay:2s; opacity: 0.15; }
    .blob-3 { width:300px; height:300px; background:#FF8E53; top:50%; left:50%; animation-delay:4s; opacity: 0.15; }

    @keyframes blobFloat {
        from { transform: translate(0,0) scale(1); }
        to   { transform: translate(30px,20px) scale(1.06); }
    }

    /* Grid dots background */
    body::before {
        content: '';
        position: fixed; inset: 0;
        background-image: radial-gradient(rgba(0,0,0,0.02) 1px, transparent 1px);
        background-size: 32px 32px;
        z-index: 0; pointer-events: none;
    }

    /* Login card */
    .admin-login-wrap {
        position: relative; z-index: 1;
    }
    body {
        background: linear-gradient(135deg, #FFF0E6 0%, #FFEBE0 100%) !important;
        font-family: 'Poppins', sans-serif;
        color: #2D3748;
    }
    .admin-wrapper {
        width: 100%; max-width: 460px;
        padding: 20px;
    }
    .admin-card {
        background: #ffffff;
        border: 1px solid rgba(255, 107, 53, 0.1);
        border-radius: 28px;
        padding: 48px 44px;
        box-shadow: 0 30px 60px rgba(255, 107, 53, 0.08);
        animation: cardIn 0.5s cubic-bezier(0.34,1.56,0.64,1);
    }
    @keyframes cardIn {
        from { opacity:0; transform:translateY(32px) scale(0.97); }
        to   { opacity:1; transform:translateY(0) scale(1); }
    }
 
    /* Logo icon */
    .logo-icon {
        width: 72px; height: 72px; border-radius: 22px;
        background: linear-gradient(135deg, #FF6B35, #FF8E53);
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 16px;
        box-shadow: 0 16px 40px rgba(255,107,53,0.3);
        font-size: 1.8rem; color: white;
        animation: logoBounce 0.6s 0.3s cubic-bezier(0.34,1.56,0.64,1) both;
    }
    @keyframes logoBounce {
        from { opacity:0; transform:scale(0.6); }
        to   { opacity:1; transform:scale(1); }
    }
 
    /* Live indicator */
    .live-pill {
        display: inline-flex; align-items: center; gap: 7px;
        background: rgba(255, 107, 53, 0.08);
        border: 1px solid rgba(255, 107, 53, 0.15);
        color: #FF6B35; font-size: 11px; font-weight: 700;
        padding: 5px 14px; border-radius: 20px;
    }
    .live-dot {
        width: 7px; height: 7px; border-radius: 50%;
        background: #FF6B35;
        animation: livePulse 1.2s ease-in-out infinite;
    }
    @keyframes livePulse {
        0%,100% { opacity:1; transform:scale(1); }
        50%     { opacity:0.35; transform:scale(1.5); }
    }
 
    /* Form inputs */
    .admin-label {
        color: #718096;
        font-size: 11px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.6px;
        margin-bottom: 8px; display: block;
    }
    .admin-input {
        width: 100%;
        background: #F8F9FA !important;
        border: 2px solid rgba(0, 0, 0, 0.03) !important;
        border-radius: 14px !important;
        padding: 14px 48px 14px 18px !important;
        color: #2D3748 !important;
        font-size: 14px; font-weight: 500;
        transition: all 0.22s ease;
        outline: none;
    }
    .admin-input:focus {
        border-color: #FF6B35 !important;
        background: #ffffff !important;
        box-shadow: 0 0 0 4px rgba(255,107,53,0.12) !important;
    }
    .admin-input::placeholder { color: #A0AEC0; }
    .admin-input:disabled { opacity: 0.5; cursor: not-allowed; }
 
    .input-icon {
        position: absolute; right: 16px; top: 50%;
        transform: translateY(-50%);
        color: #A0AEC0; font-size: 14px;
        cursor: pointer; background: none; border: none; padding: 0;

        transition: color 0.2s;
    }
    .input-icon:hover { color: rgba(255,255,255,0.6); }

    /* Submit button */
    .admin-submit-btn {
        width: 100%;
        background: linear-gradient(135deg, #FF6B35 0%, #e65100 100%);
        color: white; border: none;
        border-radius: 14px; padding: 15px 24px;
        font-size: 15px; font-weight: 700;
        font-family: 'Poppins', sans-serif;
        cursor: pointer;
        box-shadow: 0 10px 30px rgba(255,107,53,0.4);
        transition: all 0.22s ease;
        display: flex; align-items: center; justify-content: center; gap: 10px;
    }
    .admin-submit-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 16px 40px rgba(255,107,53,0.5);
    }
    .admin-submit-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

    /* Alert boxes */
    .admin-alert {
        border-radius: 14px; padding: 13px 16px;
        font-size: 13px; font-weight: 600;
        display: flex; align-items: flex-start; gap: 10px;
        margin-bottom: 20px; border: 1px solid transparent;
    }
    .admin-alert-danger { background:rgba(239,68,68,0.12); border-color:rgba(239,68,68,0.25); color:#fca5a5; }
    .admin-alert-lock   { background:rgba(239,68,68,0.08); border-color:rgba(239,68,68,0.2);  color:#f87171; }

    /* Divider */
    .admin-divider {
        border: none; border-top: 1px solid rgba(255,255,255,0.07);
        margin: 24px 0;
    }

    /* Back link */
    .back-link {
        color: rgba(255,255,255,0.28);
        font-size: 12px; text-decoration: none;
        display: inline-flex; align-items: center; gap: 6px;
        transition: color 0.2s;
    }
    .back-link:hover { color: rgba(255,255,255,0.6); }

    /* Security badge row */
    .security-badges {
        display: flex; gap: 10px; flex-wrap: wrap;
        justify-content: center; margin-top: 20px;
    }
    .sec-badge {
        font-size: 10px; font-weight: 700; color: rgba(255,255,255,0.3);
        display: flex; align-items: center; gap: 5px;
    }
    </style>
</head>
<body>

<!-- Background blobs -->
<div class="bg-blob blob-1"></div>
<div class="bg-blob blob-2"></div>
<div class="bg-blob blob-3"></div>

<div class="admin-login-wrap">
    <div class="admin-card">

        <!-- Logo & Title -->
        <div class="text-center mb-4">
            <a href="../index.php" class="text-decoration-none d-block">
                <div class="logo-icon">
                    <i class="fa-solid fa-burger"></i>
                </div>
                <h4 style="color:white; font-family:'Poppins',sans-serif; font-weight:800; font-size:1.3rem; margin-bottom:4px;">
                    <?= htmlspecialchars($school_name) ?>
                </h4>
                <p style="color:rgba(255,255,255,0.4); font-size:12px; margin-bottom:14px;">Secure Admin Control Panel</p>
            </a>
            <div class="live-pill">
                <span class="live-dot"></span>
                System Online &nbsp;·&nbsp; <?= date('d M Y, h:i A') ?>
            </div>
        </div>

        <!-- Alerts -->
        <?php if ($lockoutMsg): ?>
        <div class="admin-alert admin-alert-lock">
            <i class="fa-solid fa-lock mt-1" style="flex-shrink:0;"></i>
            <div><?= htmlspecialchars($lockoutMsg) ?></div>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="admin-alert admin-alert-danger">
            <i class="fa-solid fa-triangle-exclamation mt-1" style="flex-shrink:0;"></i>
            <div><?= htmlspecialchars($error) ?></div>
        </div>
        <?php endif; ?>

        <!-- Form -->
        <form action="index.php" method="POST" id="adminLoginForm">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token_admin'] ?>">

            <!-- Username -->
            <div class="mb-3">
                <label class="admin-label">Username</label>
                <div class="position-relative">
                    <input type="text"
                           name="username"
                           class="admin-input form-control"
                           autocomplete="username"
                           placeholder="Enter admin username"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                           required <?= $isLocked ? 'disabled' : '' ?>>
                    <i class="fa-solid fa-user input-icon" style="cursor:default;"></i>
                </div>
            </div>

            <!-- Password -->
            <div class="mb-4">
                <label class="admin-label">Password</label>
                <div class="position-relative">
                    <input type="password"
                           name="password"
                           id="admin-pass"
                           class="admin-input form-control"
                           autocomplete="current-password"
                           placeholder="Enter your password"
                           required <?= $isLocked ? 'disabled' : '' ?>>
                    <button type="button" class="input-icon" onclick="toggleAdminPass()" title="Show/Hide Password">
                        <i class="fa-solid fa-eye" id="admin-pass-icon"></i>
                    </button>
                </div>
            </div>

            <!-- Submit -->
            <button type="submit"
                    id="admin-login-btn"
                    class="admin-submit-btn"
                    <?= $isLocked ? 'disabled' : '' ?>>
                <?php if ($isLocked): ?>
                    <i class="fa-solid fa-lock"></i> Account Locked
                <?php else: ?>
                    <i class="fa-solid fa-right-to-bracket"></i> Login to Control Panel
                <?php endif; ?>
            </button>
        </form>

        <hr class="admin-divider">

        <!-- Footer -->
        <div class="text-center">
            <a href="../index.php" class="back-link">
                <i class="fa-solid fa-arrow-left"></i> Back to Website
            </a>
        </div>

        <!-- Security badges -->
        <div class="security-badges">
            <span class="sec-badge"><i class="fa-solid fa-shield-halved" style="color:#22c55e;"></i> CSRF Protected</span>
            <span class="sec-badge"><i class="fa-solid fa-lock" style="color:#3b82f6;"></i> Brute-Force Guard</span>
            <span class="sec-badge"><i class="fa-solid fa-clock-rotate-left" style="color:#f59e0b;"></i> Audit Logged</span>
        </div>
    </div>
</div>

<script>
function toggleAdminPass() {
    const inp  = document.getElementById('admin-pass');
    const icon = document.getElementById('admin-pass-icon');
    inp.type = inp.type === 'password' ? 'text' : 'password';
    icon.className = inp.type === 'password' ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
}

document.getElementById('adminLoginForm').addEventListener('submit', function () {
    const btn = document.getElementById('admin-login-btn');
    if (btn.disabled) return false;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>&nbsp; Authenticating...';
});
</script>
</body>
</html>
