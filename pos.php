<?php
// admin/pos.php
require_once __DIR__ . '/includes/header.php';

// Fetch all active categories
$categories = $pdo->query("SELECT * FROM categories WHERE status = 'active'")->fetchAll();

// Fetch all active products
$products = $pdo->query("SELECT p.*, c.slug as category_slug FROM products p JOIN categories c ON p.category_id = c.id WHERE p.status = 'active' AND p.stock_qty > 0")->fetchAll();

$taxPercent = floatval(getSetting('tax_percent', '5.00'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Billing System — <?= htmlspecialchars($school_name) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="<?= isset($_COOKIE['dark_mode']) && $_COOKIE['dark_mode'] === 'true' ? 'dark-mode' : '' ?>">

    <!-- Top header navbar -->
    <nav class="navbar navbar-expand-lg glass-nav py-2 mb-0 border-bottom">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-warning fs-4" href="dashboard.php">
                <i class="fa-solid fa-burger"></i>
                <span class="text-dark dark-text-white"><?= htmlspecialchars($school_name) ?> POS</span>
            </a>
            <div class="d-flex align-items-center gap-3 ms-auto">
                <button class="btn btn-link text-dark dark-text-white p-0 shadow-none" id="theme-toggler">
                    <i class="fa-solid fa-moon fs-5"></i>
                </button>
                <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill btn-sm px-3 fw-bold"><i class="fa-solid fa-gauge me-2"></i>Exit POS</a>
            </div>
        </div>
    </nav>

    <!-- POS main layout -->
    <div class="container-fluid px-0">
        <div class="row g-0 pos-container">
            <!-- Left Column: Menu Catalog Grid -->
            <div class="col-md-7 col-lg-8 bg-light dark-bg-dark border-end h-100 pos-menu p-3">
                <div class="d-flex flex-column gap-3 mb-4">
                    <!-- Search Bar & Filters -->
                    <div class="row g-2">
                        <div class="col-sm-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 border-light"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                                <input type="text" id="posSearch" class="form-control border-start-0 border-light ps-0 shadow-none" placeholder="Search product by name..." oninput="filterPOSCatalog()">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <!-- Category selectors -->
                            <div class="d-flex gap-2 overflow-x-auto pb-1" style="white-space: nowrap;">
                                <button onclick="filterCategory('all')" class="btn btn-warning rounded-pill btn-sm px-3 fw-bold pos-cat-btn" id="cat-btn-all">All</button>
                                <?php foreach ($categories as $cat): ?>
                                    <button onclick="filterCategory('<?= $cat['slug'] ?>')" class="btn btn-outline-warning rounded-pill btn-sm px-3 fw-bold pos-cat-btn" id="cat-btn-<?= $cat['slug'] ?>"><?= htmlspecialchars($cat['name']) ?></button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Catalog Grid -->
                <div class="row g-3" id="pos-catalog-grid">
                    <?php foreach ($products as $p): ?>
                        <?php $price = $p['discount_price'] > 0 ? $p['discount_price'] : $p['price']; ?>
                        <div class="col-6 col-sm-4 col-xl-3 pos-product-item" data-category="<?= htmlspecialchars($p['category_slug']) ?>" data-name="<?= htmlspecialchars(strtolower($p['name'])) ?>">
                            <div onclick="addPOSItem(<?= htmlspecialchars(json_encode([
                                'product_id' => $p['id'],
                                'name' => $p['name'],
                                'price' => floatval($price),
                                'image' => $p['image'],
                                'stock' => $p['stock_qty']
                            ])) ?>)" class="pos-product-card text-center p-2 rounded-4 h-100 d-flex flex-column justify-content-between cursor-pointer border border-light" style="font-size: 0.85rem;">
                                <div>
                                    <img src="<?= (strpos($p['image'], 'http') === 0) ? htmlspecialchars($p['image']) : '../assets/images/' . htmlspecialchars($p['image']) ?>" onerror="this.src='../assets/images/placeholder.png'" class="w-100 rounded-3 mb-2" style="height: 100px; object-fit: cover;" alt="">
                                    <h6 class="fw-bold text-dark dark-text-white mb-1 text-truncate"><?= htmlspecialchars($p['name']) ?></h6>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mt-2">
                                    <span class="fw-bold text-warning"><?= $currency ?><?= number_format($price, 0) ?></span>
                                    <span class="badge bg-outline-secondary text-[10px]"><?= $p['stock_qty'] ?> Qty</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
 
            <!-- Right Column: Cart Billing sidebar panel -->
            <div class="col-md-5 col-lg-4 h-100 pos-cart d-flex flex-column">
                <div class="p-3 border-bottom">
                    <div class="pos-cart-header mb-3">
                        <h5 class="fw-bold mb-0"><i class="fa-solid fa-cart-shopping me-2 text-white"></i>Active Billing Cart</h5>
                    </div>
                    <!-- Customer Details -->
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="text" id="custName" class="form-control form-control-sm rounded-pill shadow-none" placeholder="Walk-in Customer">
                        </div>
                        <div class="col-6">
                            <input type="text" id="custPhone" class="form-control form-control-sm rounded-pill shadow-none" placeholder="Phone Number">
                        </div>
                        <div class="col-6">
                            <select id="posOrderType" class="form-select form-select-sm rounded-pill shadow-none" onchange="togglePOSTableInput()">
                                <option value="take_away" selected>Take Away</option>
                                <option value="dine_in">Dine In</option>
                                <option value="delivery">Delivery</option>
                            </select>
                        </div>
                        <div class="col-6 hidden" id="pos-table-group">
                            <input type="text" id="posTableNo" class="form-control form-control-sm rounded-pill shadow-none" placeholder="Table No">
                        </div>
                    </div>
                </div>
 
                <!-- Selected Cart Items list -->
                <div class="pos-cart-items p-3 d-flex flex-column gap-2" id="pos-cart-items-container">
                    <!-- Loaded dynamically via js -->
                    <div class="text-center py-5 text-muted my-auto">
                        <i class="fa-solid fa-calculator display-4 mb-2"></i>
                        <p class="small mb-0">Select items to begin checkout</p>
                    </div>
                </div>
 
                <!-- Totals and Checkout -->
                <div class="p-3 bg-light dark-bg-dark border-top mt-auto">
                    <div class="d-flex justify-content-between mb-1 text-sm">
                        <span class="text-secondary">Subtotal</span>
                        <span class="fw-bold" id="pos-subtotal"><?= $currency ?>0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1 text-sm">
                        <span class="text-secondary">GST (<?= $taxPercent ?>%)</span>
                        <span class="fw-bold" id="pos-tax"><?= $currency ?>0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 text-sm align-items-center">
                        <span class="text-secondary">Discount (<?= $currency ?>)</span>
                        <input type="number" id="posDiscount" class="form-control form-control-sm text-end rounded-pill shadow-none" style="width: 100px;" value="0.00" oninput="calculatePOSTotals()">
                    </div>
                    <div class="d-flex justify-content-between mb-3 border-top pt-2 fs-5 fw-bold">
                        <span class="text-dark dark-text-white">Grand Total</span>
                        <span class="text-warning" id="pos-grandtotal"><?= $currency ?>0.00</span>
                    </div>
                    
                    <div class="row g-2">
                        <div class="col-6">
                            <button onclick="alert('Order draft saved successfully!')" class="btn btn-save-cart w-100 py-2.5 d-flex align-items-center justify-content-center gap-2"><i class="fa-solid fa-save"></i> Save</button>
                        </div>
                        <div class="col-6">
                            <button onclick="checkoutPOSOrder()" class="btn btn-pay-now w-100 py-2.5 d-flex align-items-center justify-content-center gap-2"><i class="fa-solid fa-credit-card"></i> Pay Now</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script assets loader -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.all.min.js"></script>
    <script>
        // Set global settings in JS
        const currencySymbol = '<?= $currency ?>';
        const taxRate = parseFloat('<?= $taxPercent ?>');
    </script>
    <script src="../assets/js/pos.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
