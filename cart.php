<?php
// cart.php
require_once __DIR__ . '/includes/header.php';

$userId = isLoggedIn() ? $_SESSION['user_id'] : null;
$sessionId = session_id();

// Fetch cart items
if ($userId) {
    $stmt = $pdo->prepare("SELECT c.*, p.name, p.price, p.discount_price, p.image, p.stock_qty 
                           FROM carts c 
                           JOIN products p ON c.product_id = p.id 
                           WHERE c.user_id = ?");
    $stmt->execute([$userId]);
} else {
    $stmt = $pdo->prepare("SELECT c.*, p.name, p.price, p.discount_price, p.image, p.stock_qty 
                           FROM carts c 
                           JOIN products p ON c.product_id = p.id 
                           WHERE c.session_id = ?");
    $stmt->execute([$sessionId]);
}
$cartItems = $stmt->fetchAll();

$subtotal = 0;
?>

<!-- Premium Typography Links inside page wrapper -->
<link rel="stylesheet" href="assets/css/menu-premium.css">

<div class="container py-5">
    <div class="mb-5">
        <span class="text-uppercase tracking-wider fw-bold text-xs" style="color: var(--premium-gold); letter-spacing: 1.5px;">Gourmet Basket</span>
        <h1 class="fw-extrabold tracking-tight text-dark mb-2" style="font-family: 'Poppins', sans-serif;">Your Selection</h1>
        <div style="width: 50px; height: 3px; background: var(--premium-gold); border-radius: 2px;"></div>
    </div>

    <div class="row g-4">
        <!-- Cart Items List -->
        <div class="col-lg-8">
            <div class="card border-0 filter-sidebar-glass p-4">
                <?php if (!empty($cartItems)): ?>
                    <h5 class="fw-bold mb-4" style="color: var(--matte-dark); font-family: 'Poppins', sans-serif; letter-spacing: -0.3px;"><i class="fa-solid fa-utensils me-2" style="color: var(--premium-gold);"></i> Selected Delicacies</h5>
                    <div class="d-flex flex-column gap-3 mb-4">
                        <?php foreach ($cartItems as $item): ?>
                            <?php 
                                $price = $item['discount_price'] > 0 ? $item['discount_price'] : $item['price'];
                                $itemTotal = $price * $item['quantity'];
                                $subtotal += $itemTotal;
                            ?>
                            <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between p-3 food-card position-relative" id="cart-row-<?= $item['product_id'] ?>">
                                <div class="d-flex align-items-center gap-3 w-100">
                                    <div class="overflow-hidden rounded-4" style="width: 75px; height: 75px; flex-shrink: 0; border: 1px solid rgba(197, 168, 128, 0.2);">
                                        <img src="<?= (strpos($item['image'], 'http') === 0) ? htmlspecialchars($item['image']) : 'assets/images/' . htmlspecialchars($item['image'] ?? 'placeholder.png') ?>" onerror="this.src='https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=100'" class="w-100 h-100 object-cover" alt="">
                                    </div>
                                    <div class="w-100 text-truncate">
                                        <h6 class="fw-bold text-dark mb-1 text-truncate" style="font-family: 'Poppins', sans-serif;"><?= htmlspecialchars($item['name']) ?></h6>
                                        <div class="text-xs text-muted mb-2 fw-medium">Unit Price: <?= $currency ?><?= number_format($price, 2) ?></div>
                                        <button onclick="removeCartItem(<?= $item['product_id'] ?>)" class="btn btn-link text-danger p-0 text-decoration-none shadow-none fw-semibold" style="font-size: 11.5px;"><i class="fa-regular fa-trash-can me-1"></i> Remove From Basket</button>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between justify-content-sm-end gap-4 w-100 mt-3 mt-sm-0 pt-2 pt-sm-0 border-top border-sm-top-0 border-light">
                                    <div class="input-group input-group-sm border rounded-pill overflow-hidden" style="max-width: 90px; background: #f8f8f7; border-color: rgba(0,0,0,0.05) !important;">
                                        <button onclick="updateCartItem(<?= $item['product_id'] ?>, <?= $item['quantity'] - 1 ?>)" class="btn btn-light border-0 py-1 bg-transparent"><i class="fa-solid fa-minus text-xs" style="font-size: 10px;"></i></button>
                                        <input type="text" class="form-control text-center border-0 bg-transparent shadow-none px-1 fw-bold text-dark" value="<?= $item['quantity'] ?>" readonly style="font-size: 12px;">
                                        <button onclick="updateCartItem(<?= $item['product_id'] ?>, <?= $item['quantity'] + 1 ?>)" class="btn btn-light border-0 py-1 bg-transparent"><i class="fa-solid fa-plus text-xs" style="font-size: 10px;"></i></button>
                                    </div>
                                    <div class="text-end" style="min-width: 90px;">
                                        <span class="fw-bold" style="font-size: 1.15rem; color: #b08d5b !important; font-family: 'Poppins', sans-serif;"><?= $currency ?><?= number_format($itemTotal, 2) ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Custom Instructions Field -->
                    <div class="border-top pt-4" style="border-color: rgba(197, 168, 128, 0.15) !important;">
                        <label class="form-label fw-bold text-xs mb-2 d-flex align-items-center gap-2" style="color: var(--matte-dark); letter-spacing: 0.3px;">
                            <i class="fa-regular fa-comment-dots" style="color: var(--premium-gold); font-size: 14px;"></i> Culinary Preferences / Note for the Chef
                        </label>
                        <textarea class="form-control p-3 text-xs shadow-none" id="specialInstructions" style="border: 1px solid rgba(197, 168, 128, 0.25); background: var(--luxury-white); border-radius: 16px;" rows="2" placeholder="Example: Make it extra spicy, No onions, request well done..."></textarea>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="mb-4" style="font-size: 3.5rem; filter: grayscale(0.2);">🍽️</div>
                        <h4 class="fw-bold text-dark" style="font-family: 'Poppins', sans-serif;">Your Basket is Empty</h4>
                        <p class="text-muted text-xs mb-4 max-w-md mx-auto">Explore our premium cloud kitchen culinary masterpieces and select something extraordinary.</p>
                        <a href="menu.php" class="btn btn-gradient-yellow rounded-pill px-5 py-2.5 fw-bold btn-sm text-uppercase tracking-wider" style="font-size: 11px;">Discover Menu</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Cart Summary / Order Checkout Card -->
        <?php if (!empty($cartItems)): ?>
            <div class="col-lg-4">
                <div class="card border-0 filter-sidebar-glass p-4 sticky-top" style="top: 110px; z-index: 100;">
                    <h5 class="fw-bold text-dark mb-4" style="font-family: 'Poppins', sans-serif; letter-spacing: -0.3px;">Gourmet Invoice</h5>
                    
                    <div class="d-flex align-items-center justify-content-between mb-3 text-sm">
                        <span class="text-muted fw-medium">Items Subtotal</span>
                        <span class="fw-bold" id="cart-subtotal" style="color: var(--matte-dark);"><?= $currency ?><?= number_format($subtotal, 2) ?></span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-3 text-sm">
                        <span class="text-muted fw-medium">Kitchen Prep Charges</span>
                        <span class="fw-bold text-xs px-2 py-1 rounded-pill" style="background: rgba(34, 197, 94, 0.1); color: #16a34a;">COMPLIMENTARY</span>
                    </div>

                    <?php 
                        $taxPercent = floatval(getSetting('tax_percent', '5.00'));
                        $tax = ($subtotal * $taxPercent) / 100;
                        $grandTotal = $subtotal + $tax;
                    ?>

                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3 text-sm" style="border-color: rgba(197, 168, 128, 0.15) !important;">
                        <span class="text-muted fw-medium">Taxes & Statutory GST (<?= $taxPercent ?>%)</span>
                        <span class="fw-semibold text-dark" id="cart-tax"><?= $currency ?><?= number_format($tax, 2) ?></span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-4 pt-2">
                        <span class="fw-bold text-dark" style="font-family: 'Poppins', sans-serif; font-size: 1.1rem;">Grand Total</span>
                        <span class="fw-extrabold fs-3" id="cart-total" style="color: #b08d5b !important; font-family: 'Poppins', sans-serif;"><?= $currency ?><?= number_format($grandTotal, 2) ?></span>
                    </div>

                    <a href="javascript:void(0)" onclick="proceedToCheckoutWithNotes()" class="btn btn-gradient-yellow w-100 rounded-pill py-3 fw-bold text-uppercase tracking-wider" style="font-size: 12px;"><i class="fa-solid fa-lock me-2"></i> Secure Checkout</a>
                    <a href="menu.php" class="btn btn-outline-dark w-100 rounded-pill py-2 fw-bold border-2 mt-2" style="font-size: 12px; border-color: var(--matte-dark);">Continue Exploring Menu</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function updateCartItem(productId, quantity) {
    if (quantity < 1) return;
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('product_id', productId);
    formData.append('quantity', quantity);

    fetch('api/cart_action.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') { window.location.reload(); }
        else { showToast(data.message || 'Error updating quantity', 'error'); }
    });
}

function removeCartItem(productId) {
    Swal.fire({
        title: 'Remove item?',
        text: 'Do you want to remove this culinary artwork from your basket?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#121212',
        cancelButtonColor: '#e5e5e5',
        confirmButtonText: 'Yes, remove',
        customClass: { popup: 'rounded-4' }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'remove');
            formData.append('product_id', productId);

            fetch('api/cart_action.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') { window.location.reload(); }
                else { showToast(data.message || 'Error removing item', 'error'); }
            });
        }
    });
}

function proceedToCheckoutWithNotes() {
    const notesInput = document.getElementById('specialInstructions');
    if (notesInput && notesInput.value.trim() !== '') {
        sessionStorage.setItem('special_chef_notes', notesInput.value.trim());
    } else {
        sessionStorage.removeItem('special_chef_notes');
    }
    window.location.href = 'checkout.php';
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
