<?php
// includes/auth_helper.php
// User & Admin session helper utilities

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getLoggedInUser() {
    global $pdo;
    if (!isLoggedIn()) return null;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function checkLoginRedirect() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = "Please login to access this page.";
        header("Location: login.php");
        exit;
    }
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function checkAdminRedirect() {
    if (!isAdminLoggedIn()) {
        header("Location: index.php");
        exit;
    }
}

function getLoggedInAdmin() {
    global $pdo;
    if (!isAdminLoggedIn()) return null;
    
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ? LIMIT 1");
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch();
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function generateOrderNo() {
    return 'ORD-' . strtoupper(uniqid());
}
?>
