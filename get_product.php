<?php
// api/get_product.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_helper.php';

$productId = intval($_GET['id'] ?? 0);
if (!$productId) {
    echo '<div class="col-12 text-center py-5 text-danger"><p>Invalid product selection.</p></div>';
    exit;
}

$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ? AND p.status = 'active' LIMIT 1");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    echo '<div class="col-12 text-center py-5 text-danger"><p>Product details unavailable.</p></div>';
    exit;
}

$currency = getSetting('currency_symbol', '₹');
?>
<div class="col-md-6 bg-light d-flex align-items-center justify-content-center py-5" style="min-height: 380px;">
    <img src="<?= (strpos($product['image'], 'http') === 0) ? htmlspecialchars($product['image']) : 'assets/images/' . htmlspecialchars($product['image'] ?? 'placeholder.png') ?>" onerror="this.src='https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=500&auto=format&fit=crop&q=60'" class="img-fluid rounded-4 shadow-sm" style="max-height: 300px; object-fit: contain;" alt="">
</div>
<div class="col-md-6 p-4 p-md-5 d-flex flex-column justify-content-between">
    <div>
        <span class="badge bg-warning/20 text-warning mb-2 px-3 py-1.5 fw-semibold"><?= htmlspecialchars($product['category_name']) ?></span>
        <h3 class="fw-bold text-dark dark-text-white mb-2"><?= htmlspecialchars($product['name']) ?></h3>
        
        <div class="mb-3">
            <?php if ($product['discount_price'] > 0): ?>
                <span class="fs-4 fw-bold text-warning"><?= $currency ?><?= number_format($product['discount_price'], 2) ?></span>
                <span class="text-decoration-line-through text-muted ms-2 small"><?= $currency ?><?= number_format($product['price'], 2) ?></span>
            <?php else: ?>
                <span class="fs-4 fw-bold text-warning"><?= $currency ?><?= number_format($product['price'], 2) ?></span>
            <?php endif; ?>
        </div>

        <p class="text-secondary mb-4" style="line-height: 1.6;"><?= htmlspecialchars($product['description']) ?></p>

        <div class="mb-4">
            <label class="form-label fw-bold text-xs text-uppercase tracking-wider">Select Quantity</label>
            <div class="input-group" style="width: 130px;">
                <button class="btn btn-outline-secondary border-light rounded-start-pill" type="button" onclick="changeQty(-1)"><i class="fa-solid fa-minus text-xs"></i></button>
                <input type="text" id="quick-view-qty" class="form-control text-center border-light shadow-none" value="1" readonly>
                <button class="btn btn-outline-secondary border-light rounded-end-pill" type="button" onclick="changeQty(1)"><i class="fa-solid fa-plus text-xs"></i></button>
            </div>
        </div>
    </div>
    
    <!-- Frequently Bought Together (Cross-selling) -->
    <div class="mb-4 pt-3 border-top">
        <label class="form-label fw-bold text-xs text-uppercase tracking-wider text-muted mb-2">🔥 Frequently Bought Together</label>
        <div class="d-flex align-items-center gap-3 p-2.5 rounded-3 bg-light border border-light">
            <?php
            // Suggest complementary item based on current product category
            if ($product['category_name'] === 'Burgers' || $product['category_name'] === 'Pizza') {
                $suggestName = 'Peri Peri Masala Fries';
                $suggestPrice = '₹109';
                $suggestImg = 'assets/images/special_butter_pav_bhaji.png'; // or generic fries image
                $suggestId = 30;
            } else {
                $suggestName = 'Double Choco Muffin';
                $suggestPrice = '₹100';
                $suggestImg = 'assets/images/double_choco_chip_muffin.png';
                $suggestId = 52;
            }
            ?>
            <div style="width: 44px; height: 44px; flex-shrink: 0;">
                <img src="<?= $suggestImg ?>" class="w-100 h-100 object-cover rounded-2" alt="">
            </div>
            <div class="flex-grow-1 min-w-0">
                <h6 class="text-xs fw-bold text-dark mb-0.5 text-truncate"><?= $suggestName ?></h6>
                <span class="text-xs text-warning fw-bold"><?= $suggestPrice ?></span>
            </div>
            <button onclick="addToCart(<?= $suggestId ?>, 1); Swal.fire({title: 'Added!', text: '<?= $suggestName ?> added to cart', icon: 'success', toast: true, position: 'top-end', timer: 2000, showConfirmButton: false});" class="btn btn-xs btn-outline-warning rounded-pill px-3 py-1 fw-bold text-xs" style="border-width: 1.5px;">+ Add</button>
        </div>
    </div>

    <div>
        <button onclick="addQuickViewToCart(<?= $product['id'] ?>)" class="btn btn-warning w-100 rounded-pill py-3 fw-bold"><i class="fa-solid fa-cart-plus me-2"></i>Add to Cart</button>
    </div>
</div>

<script>
// Handle Quantity Changes inside Quickview
function changeQty(amount) {
    const qtyInput = document.getElementById('quick-view-qty');
    if (!qtyInput) return;
    let qty = parseInt(qtyInput.value) + amount;
    if (qty < 1) qty = 1;
    qtyInput.value = qty;
}

// Add Item via Quickview quantity count
function addQuickViewToCart(productId) {
    const qtyInput = document.getElementById('quick-view-qty');
    if (!qtyInput) return;
    const qty = parseInt(qtyInput.value);
    
    addToCart(productId, qty);
    
    // Close Modal
    const modalEl = document.getElementById('quickViewModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();
}
</script>
