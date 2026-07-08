<?php
// checkout.php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth_helper.php';

// Requires customer login
checkLoginRedirect();

$userId = $_SESSION['user_id'];
$user = getLoggedInUser();

// Fetch cart items
$stmt = $pdo->prepare("SELECT c.*, p.name, p.price, p.discount_price, p.stock_qty 
                       FROM carts c 
                       JOIN products p ON c.product_id = p.id 
                       WHERE c.user_id = ?");
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll();

if (empty($cartItems)) {
    header("Location: cart.php");
    exit;
}

$subtotal = 0;
foreach ($cartItems as $item) {
    $price = $item['discount_price'] > 0 ? $item['discount_price'] : $item['price'];
    $subtotal += ($price * $item['quantity']);
}

$taxPercent = floatval(getSetting('tax_percent', '5.00'));
$tax = ($subtotal * $taxPercent) / 100;
$grandTotal = $subtotal + $tax;

$couponCode = '';
$discountAmount = 0.00;
$couponError = '';
$couponSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_coupon'])) {
    $couponCode = sanitizeInput($_POST['coupon_code'] ?? '');
    
    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND status = 'active' AND expiry_date >= CURDATE() LIMIT 1");
    $stmt->execute([$couponCode]);
    $coupon = $stmt->fetch();
    
    if ($coupon) {
        if ($subtotal < $coupon['min_cart_amount']) {
            $couponError = "Minimum order value to use this coupon is " . $currency . number_format($coupon['min_cart_amount'], 2);
        } else {
            if ($coupon['type'] === 'percentage') {
                $discountAmount = ($subtotal * $coupon['value']) / 100;
            } else {
                $discountAmount = $coupon['value'];
            }
            $grandTotal -= $discountAmount;
            if ($grandTotal < 0) $grandTotal = 0;
            $couponSuccess = "Coupon applied successfully! Saving " . $currency . number_format($discountAmount, 2);
        }
    } else {
        $couponError = "Invalid or expired coupon code.";
    }
}

// Handle Order Placement Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $customerName = sanitizeInput($_POST['customer_name'] ?? '');
    $customerPhone = sanitizeInput($_POST['customer_phone'] ?? '');
    $deliveryAddress = sanitizeInput($_POST['delivery_address'] ?? '');
    $paymentMethod = sanitizeInput($_POST['payment_method'] ?? 'cod');
    $orderType = sanitizeInput($_POST['order_type'] ?? 'delivery');
    $tableNo = sanitizeInput($_POST['table_no'] ?? '');

    $couponCode = sanitizeInput($_POST['applied_coupon_code'] ?? '');
    if ($couponCode) {
        $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND status = 'active' AND expiry_date >= CURDATE() LIMIT 1");
        $stmt->execute([$couponCode]);
        $coupon = $stmt->fetch();
        if ($coupon && $subtotal >= $coupon['min_cart_amount']) {
            if ($coupon['type'] === 'percentage') {
                $discountAmount = ($subtotal * $coupon['value']) / 100;
            } else {
                $discountAmount = $coupon['value'];
            }
        }
    }
    
    $chefNotes = sanitizeInput($_POST['chef_notes'] ?? '');
    $grandTotal = $subtotal + $tax - $discountAmount;
    if ($grandTotal < 0) $grandTotal = 0;

    $orderNo = generateOrderNo();
    $paymentStatus = ($paymentMethod === 'cod') ? 'pending' : 'paid';

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO orders (user_id, order_no, customer_name, customer_phone, order_type, table_no, total_amount, discount_amount, tax_amount, grand_total, payment_method, payment_status, status, address, chef_notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?)");
        $stmt->execute([$userId, $orderNo, $customerName, $customerPhone, $orderType, $tableNo, $subtotal, $discountAmount, $tax, $grandTotal, $paymentMethod, $paymentStatus, $deliveryAddress, $chefNotes]);
        $orderId = $pdo->lastInsertId();

        foreach ($cartItems as $item) {
            $price = $item['discount_price'] > 0 ? $item['discount_price'] : $item['price'];
            $itemTotal = $price * $item['quantity'];

            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, total) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$orderId, $item['product_id'], $item['quantity'], $price, $itemTotal]);

            $stmt = $pdo->prepare("UPDATE products SET stock_qty = stock_qty - ? WHERE id = ?");
            $stmt->execute([$item['quantity'], $item['product_id']]);
        }

        if ($paymentStatus === 'paid') {
            $txnNo = 'TXN-' . strtoupper(uniqid());
            $stmt = $pdo->prepare("INSERT INTO payments (order_id, payment_method, transaction_no, amount, status) VALUES (?, ?, ?, ?, 'success')");
            $stmt->execute([$orderId, $paymentMethod, $txnNo, $grandTotal]);
        }

        $stmt = $pdo->prepare("DELETE FROM carts WHERE user_id = ?");
        $stmt->execute([$userId]);

        $pdo->commit();

        $_SESSION['success'] = "Order placed successfully! Order No: " . $orderNo;
        header("Location: invoice.php?order_no=" . urlencode($orderNo));
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $couponError = "Transaction failed: " . $e->getMessage();
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<link rel="stylesheet" href="assets/css/menu-premium.css">

<style>
.custom-input-premium {
    border: 1px solid rgba(0,0,0,0.08) !important;
    background: var(--luxury-white) !important;
    font-size: 13px !important;
    color: var(--matte-dark) !important;
    font-weight: 500;
}
.custom-input-premium:focus {
    border-color: var(--premium-gold) !important;
    box-shadow: 0 0 0 3px rgba(197, 168, 128, 0.15) !important;
}
.payment-selector-card {
    border: 1px solid rgba(0,0,0,0.06);
    background: #ffffff;
    transition: all 0.3s ease;
}
.payment-selector-card:hover {
    border-color: rgba(197, 168, 128, 0.4);
}
.address-tile {
    border: 1px solid rgba(0,0,0,0.08) !important;
    color: var(--matte-dark) !important;
    background: transparent;
    transition: all 0.3s ease !important;
}
.address-tile.active-tile {
    background: linear-gradient(135deg, #121212 0%, #2a2a2a 100%) !important;
    color: var(--premium-gold) !important;
    border-color: transparent !important;
}
</style>

<div class="container py-5">
    <div class="mb-5">
        <span class="text-uppercase tracking-wider fw-bold text-xs" style="color: var(--premium-gold); letter-spacing: 1.5px;">Secure Verification</span>
        <h1 class="fw-extrabold tracking-tight text-dark mb-2" style="font-family: 'Poppins', sans-serif;">Gourmet Checkout</h1>
        <div style="width: 50px; height: 3px; background: var(--premium-gold); border-radius: 2px;"></div>
    </div>

    <div class="row g-4">
        <!-- Billing Details Form -->
        <div class="col-lg-7">
            <div class="card border-0 filter-sidebar-glass p-4 p-md-5">
                <h4 class="fw-bold text-dark mb-4" style="font-family: 'Poppins', sans-serif; letter-spacing: -0.5px;">Delivery & Assignment Details</h4>

                <form id="checkoutForm" action="checkout.php" method="POST">
                    <input type="hidden" name="applied_coupon_code" value="<?= htmlspecialchars($couponCode) ?>">

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold text-xs text-uppercase tracking-wider text-muted">Gourmet Name *</label>
                            <input type="text" name="customer_name" class="form-control rounded-pill p-3 custom-input-premium" value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-xs text-uppercase tracking-wider text-muted">Primary Phone *</label>
                            <input type="text" name="customer_phone" class="form-control rounded-pill p-3 custom-input-premium" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-xs text-uppercase tracking-wider text-muted">Service Mode *</label>
                            <select name="order_type" id="order_type" class="form-select rounded-pill p-3 custom-input-premium" onchange="toggleOrderTypeInputs()">
                                <option value="delivery" selected>Elite Home Delivery</option>
                                <option value="take_away">Instant Take Away</option>
                                <option value="dine_in">Luxury Dine In</option>
                            </select>
                        </div>
                        
                        <div class="col-12" id="delivery-address-group">
                            <label class="form-label fw-bold text-xs text-uppercase tracking-wider text-muted mb-2">Delivery Destination *</label>
                            
                            <!-- Address Type Tiles Selection -->
                            <div class="d-flex gap-2 mb-3">
                                <button type="button" onclick="selectAddressTile('home', '🏠 Home: C-14, Vaishali Nagar, Jaipur')" class="btn rounded-pill py-2 px-3 fw-bold text-xs d-flex align-items-center gap-1 address-tile active-tile"><i class="fa-solid fa-house"></i> Residence</button>
                                <button type="button" onclick="selectAddressTile('office', '🏢 Office: Tech Park, Phase 2, Malviya Nagar, Jaipur')" class="btn rounded-pill py-2 px-3 fw-bold text-xs d-flex align-items-center gap-1 address-tile"><i class="fa-solid fa-briefcase"></i> Office Suite</button>
                                <button type="button" onclick="selectAddressTile('current', '📍 Current Location: M.I. Road, Near Panch Batti, Jaipur')" class="btn rounded-pill py-2 px-3 fw-bold text-xs d-flex align-items-center gap-1 address-tile"><i class="fa-solid fa-location-crosshairs"></i> Current Hub</button>
                            </div>

                            <textarea name="delivery_address" id="delivery_address" class="form-control rounded-4 p-3 custom-input-premium" rows="3" required><?= htmlspecialchars($user['address'] ?? '🏠 Home: C-14, Vaishali Nagar, Jaipur') ?></textarea>
                        </div>

                        <!-- Special Chef Notes hidden transfer -->
                        <input type="hidden" name="chef_notes" id="hidden_chef_notes" value="">

                        <div class="col-12 d-none" id="table-number-group">
                            <label class="form-label fw-bold text-xs text-uppercase tracking-wider text-muted">Lounge Table Allocation *</label>
                            <input type="text" name="table_no" class="form-control rounded-pill p-3 custom-input-premium" placeholder="Example: Suite Table 05">
                        </div>

                        <h5 class="fw-bold text-dark mt-4 mb-2" style="font-family: 'Poppins', sans-serif;">Premium Settlement Vault</h5>
                        <div class="col-12">
                            <div class="d-flex flex-column gap-2">
                                <label class="d-flex align-items-center gap-3 p-3 rounded-4 cursor-pointer payment-selector-card">
                                    <input type="radio" name="payment_method" value="cod" checked>
                                    <div><span class="fw-bold text-sm text-dark">Cash on Delivery / On-site Handover</span></div>
                                </label>
                                <label class="d-flex align-items-center gap-3 p-3 rounded-4 cursor-pointer payment-selector-card">
                                    <input type="radio" name="payment_method" value="upi">
                                    <div><span class="fw-bold text-sm text-dark">Instant UPI Node (GPay / PhonePe)</span></div>
                                </label>
                                <label class="d-flex align-items-center gap-3 p-3 rounded-4 cursor-pointer payment-selector-card">
                                    <input type="radio" name="payment_method" value="card">
                                    <div><span class="fw-bold text-sm text-dark">Digital Vault Card (Credit / Debit)</span></div>
                                </label>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" name="place_order" class="btn btn-gradient-yellow rounded-pill w-100 py-3 fw-bold text-uppercase tracking-wider" style="font-size: 12px;"><i class="fa-solid fa-square-check me-2"></i> Confirm & Authorize Order</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Checkout Summary + Coupon -->
        <div class="col-lg-5">
            <div class="card border-0 filter-sidebar-glass p-4 mb-4">
                <h5 class="fw-bold text-dark mb-3" style="font-family: 'Poppins', sans-serif;">Privilege Coupon</h5>
                
                <?php if ($couponError): ?>
                    <div class="alert alert-danger border-0 rounded-4 py-2 text-xs mb-3"><i class="fa-solid fa-circle-xmark me-2"></i><?= $couponError ?></div>
                <?php endif; ?>

                <?php if ($couponSuccess): ?>
                    <div class="alert alert-success border-0 rounded-4 py-2 text-xs mb-3"><i class="fa-solid fa-circle-check me-2"></i><?= $couponSuccess ?></div>
                <?php endif; ?>

                <form action="checkout.php" method="POST" class="d-flex gap-2">
                    <input type="text" name="coupon_code" class="form-control rounded-pill custom-input-premium" style="padding: 10px 16px;" placeholder="Enter vault key..." value="<?= htmlspecialchars($couponCode) ?>" required>
                    <button type="submit" name="apply_coupon" class="btn btn-gradient-yellow rounded-pill px-4 fw-bold text-xs text-uppercase">Apply</button>
                </form>
            </div>

            <div class="card border-0 filter-sidebar-glass p-4">
                <h5 class="fw-bold text-dark mb-4" style="font-family: 'Poppins', sans-serif;">Selected Masterpieces</h5>
                <ul class="list-unstyled d-flex flex-column gap-3 mb-4">
                    <?php foreach ($cartItems as $item): ?>
                        <?php $price = $item['discount_price'] > 0 ? $item['discount_price'] : $item['price']; ?>
                        <li class="d-flex align-items-center justify-content-between text-sm">
                            <span class="text-muted fw-medium"><?= htmlspecialchars($item['name']) ?> <strong style="color: var(--premium-gold);">x<?= $item['quantity'] ?></strong></span>
                            <span class="fw-bold text-dark"><?= $currency ?><?= number_format($price * $item['quantity'], 2) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="d-flex align-items-center justify-content-between mb-2 border-top pt-3 text-sm">
                    <span class="text-muted fw-medium">Items Subtotal</span>
                    <span class="fw-bold text-dark"><?= $currency ?><?= number_format($subtotal, 2) ?></span>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-2 text-sm">
                    <span class="text-muted fw-medium">GST Breakdown (<?= $taxPercent ?>%)</span>
                    <span class="fw-bold text-dark"><?= $currency ?><?= number_format($tax, 2) ?></span>
                </div>

                <?php if ($discountAmount > 0): ?>
                    <div class="d-flex align-items-center justify-content-between mb-2 text-sm text-success">
                        <span class="fw-medium">Privilege Reduction</span>
                        <span class="fw-bold">- <?= $currency ?><?= number_format($discountAmount, 2) ?></span>
                    </div>
                <?php endif; ?>

                <div class="d-flex align-items-center justify-content-between mb-0 border-top pt-3">
                    <span class="fw-bold text-dark fs-5" style="font-family: 'Poppins', sans-serif;">Sum Total</span>
                    <span class="fw-extrabold fs-3" style="color: #b08d5b !important; font-family: 'Poppins', sans-serif;"><?= $currency ?><?= number_format($grandTotal, 2) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleOrderTypeInputs() {
    const type = document.getElementById('order_type').value;
    const addrGroup = document.getElementById('delivery-address-group');
    const tableGroup = document.getElementById('table-number-group');
    
    if (type === 'delivery') {
        addrGroup.classList.remove('d-none');
        tableGroup.classList.add('d-none');
    } else if (type === 'dine_in') {
        addrGroup.classList.add('d-none');
        tableGroup.classList.remove('d-none');
    } else { 
        addrGroup.classList.add('d-none');
        tableGroup.classList.add('d-none');
    }
}

function selectAddressTile(type, address) {
    document.querySelectorAll('.address-tile').forEach(btn => {
        btn.classList.remove('active-tile');
    });

    const activeTile = window.event.currentTarget;
    activeTile.classList.add('active-tile');
    document.getElementById('delivery_address').value = address;
}

document.addEventListener('DOMContentLoaded', () => {
    const savedNotes = sessionStorage.getItem('special_chef_notes');
    if (savedNotes) {
        document.getElementById('hidden_chef_notes').value = savedNotes;
    }
    
    const form = document.getElementById('checkoutForm');
    if (form) {
        form.addEventListener('submit', (e) => {
            Swal.fire({
                title: '⚜️ Authenticating Order Vault',
                html: `
                    <div class="text-center py-3">
                        <div class="spinner-grow mb-3" style="width: 3rem; height: 3rem; background-color: #C5A880;" role="status"></div>
                        <h6 class="fw-bold mb-2 text-dark" style="font-family: 'Poppins';">Transmitting Order to Chef Grid...</h6>
                        <p class="text-xs text-muted mb-0">Your request is tunneling directly to our elite kitchen station. Securing ingredients...</p>
                        <div class="progress mt-4 mx-auto" style="width: 85%; height: 5px; border-radius: 10px; background: #eaeae8;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%; border-radius: 10px; background-color: #121212;"></div>
                        </div>
                    </div>
                `,
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                customClass: { popup: 'rounded-4 border-0 shadow-lg' }
            });
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
