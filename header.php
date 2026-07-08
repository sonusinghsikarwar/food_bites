<?php
// includes/header.php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth_helper.php';

// Calculate cart count
$cartCount = 0;
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT SUM(quantity) as count FROM carts WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cartCount = $stmt->fetch()['count'] ?? 0;
} else {
    $sessId = session_id();
    $stmt = $pdo->prepare("SELECT SUM(quantity) as count FROM carts WHERE session_id = ?");
    $stmt->execute([$sessId]);
    $cartCount = $stmt->fetch()['count'] ?? 0;
}

$school_name = getSetting('restaurant_name', 'Crispy Bytes');
$currency = getSetting('currency_symbol', '₹');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($school_name) ?> — Modern Food & POS</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts Poppins & Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="<?= isset($_COOKIE['dark_mode']) && $_COOKIE['dark_mode'] === 'true' ? 'dark-mode' : '' ?>">

    <!-- Loader Overlay -->
    <div id="loader-overlay">
        <div class="text-center">
            <div class="spinner-food mb-2"></div>
            <p class="fw-semibold text-muted">Cooking delicious moments...</p>
        </div>
    </div>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg glass-nav sticky-top py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-warning fs-3" href="index.php">
                <i class="fa-solid fa-burger"></i>
                <span class="text-dark dark-text-white" id="brand-text"><?= htmlspecialchars($school_name) ?></span>
            </a>
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                <i class="fa-solid fa-bars fs-4"></i>
            </button>
            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-1 gap-lg-3">
                    <li class="nav-item">
                        <a class="nav-link fw-semibold px-3 py-2 rounded-pill" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold px-3 py-2 rounded-pill" href="menu.php">Menu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold px-3 py-2 rounded-pill" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold px-3 py-2 rounded-pill" href="gallery.php">Gallery</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold px-3 py-2 rounded-pill" href="offers.php">Offers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold px-3 py-2 rounded-pill" href="contact.php">Contact</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-3">
                    <!-- Light/Dark Mode Switcher -->
                    <button class="btn btn-link text-dark dark-text-white p-0 shadow-none" id="theme-toggler">
                        <i class="fa-solid fa-moon fs-5"></i>
                    </button>

                    <!-- Cart icon -->
                    <a href="cart.php" class="btn btn-outline-warning rounded-pill px-3 py-1.5 position-relative d-flex align-items-center gap-2">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span class="d-none d-md-inline fw-semibold text-xs">Cart</span>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white" id="cart-badge">
                            <?= $cartCount ?>
                        </span>
                    </a>

                    <!-- User Account / Logins -->
                    <?php if (isLoggedIn()): ?>
                        <?php $user = getLoggedInUser(); ?>
                        <div class="dropdown">
                            <button class="btn btn-warning rounded-pill px-3 py-1.5 dropdown-toggle d-flex align-items-center gap-2 fw-semibold" type="button" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-user"></i>
                                <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow mt-2 rounded-4">
                                <li><a class="dropdown-item py-2" href="profile.php"><i class="fa-solid fa-circle-user me-2"></i>My Profile</a></li>
                                <li><a class="dropdown-item py-2" href="profile.php#orders"><i class="fa-solid fa-bag-shopping me-2"></i>My Orders</a></li>
                                <?php if (isAdminLoggedIn()): ?>
                                    <li><a class="dropdown-item py-2 text-warning fw-bold" href="admin/dashboard.php"><i class="fa-solid fa-gauge me-2"></i>Admin Dashboard</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-warning rounded-pill px-4 py-2 fw-semibold">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
