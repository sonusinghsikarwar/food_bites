<?php
// register.php
require_once __DIR__ . '/includes/header.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $address = sanitizeInput($_POST['address'] ?? '');

    if ($name && $email && $password) {
        if ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } else {
            // Check if email already registered
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'This email is already registered.';
            } else {
                try {
                    $hashed = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, address) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $email, $phone, $hashed, $address]);
                    $success = 'Account created successfully! You can now login.';
                } catch (Exception $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        }
    } else {
        $error = 'Please fill out all required fields.';
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="glass-card p-4 p-md-5">
                <div class="text-center mb-4">
                    <i class="fa-solid fa-user-plus text-warning display-4 mb-3"></i>
                    <h3 class="fw-bold text-dark dark-text-white mb-1">Create Account</h3>
                    <p class="text-muted">Register to order premium bytes online</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger border-0 rounded-4 py-3 mb-4"><i class="fa-solid fa-circle-xmark me-2"></i><?= $error ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success border-0 rounded-4 py-3 mb-4"><i class="fa-solid fa-circle-check me-2"></i><?= $success ?></div>
                <?php endif; ?>

                <form action="register.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-xs text-uppercase tracking-wider text-muted">Full Name *</label>
                        <input type="text" name="name" class="form-control rounded-pill border-light p-3 shadow-none" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-xs text-uppercase tracking-wider text-muted">Email Address *</label>
                        <input type="email" name="email" class="form-control rounded-pill border-light p-3 shadow-none" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-xs text-uppercase tracking-wider text-muted">Phone Number</label>
                        <input type="text" name="phone" class="form-control rounded-pill border-light p-3 shadow-none">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-xs text-uppercase tracking-wider text-muted">Delivery Address</label>
                        <input type="text" name="address" class="form-control rounded-pill border-light p-3 shadow-none" placeholder="Flat No, Building, Area, Jaipur">
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold text-xs text-uppercase tracking-wider text-muted">Password *</label>
                            <input type="password" name="password" class="form-control rounded-pill border-light p-3 shadow-none" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold text-xs text-uppercase tracking-wider text-muted">Confirm *</label>
                            <input type="password" name="confirm_password" class="form-control rounded-pill border-light p-3 shadow-none" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-warning rounded-pill w-100 py-3 fw-bold mt-3">Register</button>
                    <p class="text-center text-muted small mt-4 mb-0">Already have an account? <a href="login.php" class="text-warning fw-bold text-decoration-none">Login here</a></p>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
