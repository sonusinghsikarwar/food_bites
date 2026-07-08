<?php
// menu.php
require_once __DIR__ . '/includes/header.php';

// Categories fetch
$categories = $pdo->query("SELECT * FROM categories WHERE status = 'active'")->fetchAll();

// Get filter inputs
$categoryFilter = sanitizeInput($_GET['category'] ?? '');
$searchQuery = sanitizeInput($_GET['search'] ?? '');
$sortBy = sanitizeInput($_GET['sort'] ?? 'newest');
$vegOnly = isset($_GET['veg_only']) ? 1 : 0;
$maxPrice = floatval($_GET['max_price'] ?? 500);

// Build SQL query dynamically
$sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM products p 
        JOIN categories c ON p.category_id = c.id 
        WHERE p.status = 'active'";

$params = [];

if ($categoryFilter) {
    $sql .= " AND c.slug = ?";
    $params[] = $categoryFilter;
}

if ($searchQuery) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
}

// 🧠 Veg Only Toggle Fix using is_veg column
if ($vegOnly) {
    $sql .= " AND p.is_veg = 1";
}

if ($maxPrice > 0) {
    $sql .= " AND (p.price <= ? OR (p.discount_price > 0 AND p.discount_price <= ?))";
    $params[] = $maxPrice;
    $params[] = $maxPrice;
}

// Order by Sorting
if ($sortBy === 'price_low') {
    $sql .= " ORDER BY CASE WHEN p.discount_price > 0 THEN p.discount_price ELSE p.price END ASC";
} elseif ($sortBy === 'price_high') {
    $sql .= " ORDER BY CASE WHEN p.discount_price > 0 THEN p.discount_price ELSE p.price END DESC";
} elseif ($sortBy === 'rating') {
    $sql .= " ORDER BY p.is_featured DESC, p.id DESC";
} else {
    $sql .= " ORDER BY p.id DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$currency = getSetting('currency_symbol', '₹');
?>

<!-- Include Poppins & Inter fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<!-- Premium Stylesheet Refactored -->
<link rel="stylesheet" href="assets/css/menu-premium.css">

<!-- Moving Banner with Countdown -->
<div class="moving-promo-banner py-2.5 px-4 d-flex align-items-center justify-content-between text-sm shadow-sm">
    <div class="d-flex align-items-center gap-2">
        <i class="fa-solid fa-clock-rotate-left fa-spin text-warning"></i>
        <span>⏰ Limited Time Offer: Ends in <strong id="top-countdown">01:45:32</strong></span>
    </div>
    <a href="#offers" class="text-white text-decoration-underline fw-bold small ms-3">Claim Offer →</a>
</div>

<div class="container py-4">

    <!-- 1. Hero Auto Slider Banner (5 Slides with Dual CTA Buttons) -->
    <div id="heroAutoSlider" class="carousel slide hero-slider-container mb-5" data-bs-ride="carousel" data-bs-interval="4000">
        <!-- Indicators -->
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroAutoSlider" data-bs-slide-to="0" class="active"></button>
            <button type="button" data-bs-target="#heroAutoSlider" data-bs-slide-to="1"></button>
            <button type="button" data-bs-target="#heroAutoSlider" data-bs-slide-to="2"></button>
            <button type="button" data-bs-target="#heroAutoSlider" data-bs-slide-to="3"></button>
            <button type="button" data-bs-target="#heroAutoSlider" data-bs-slide-to="4"></button>
        </div>
        
        <!-- Slider Content -->
        <div class="carousel-inner p-4 p-md-5">
            <!-- Slide 1: Burger -->
            <div class="carousel-item active">
                <div class="row align-items-center">
                    <div class="col-md-7 text-dark">
                        <span class="badge bg-danger mb-3 px-3 py-2 rounded-pill fw-bold text-uppercase text-xs">20% OFF</span>
                        <h2 class="display-5 fw-extrabold mb-2 text-dark">Burger Bonanza Special</h2>
                        <p class="lead mb-4">Double grilled patties layered with molten cheddar cheese. Delicious sides included.</p>
                        <div class="d-flex gap-3">
                            <a href="menu.php?category=burgers" class="btn btn-gradient-yellow rounded-pill px-4 py-2.5 fw-bold">Order Now</a>
                            <a href="#products-grid" class="btn btn-outline-dark rounded-pill px-4 py-2.5 fw-semibold">View Menu</a>
                        </div>
                    </div>
                    <div class="col-md-5 text-center d-none d-md-block">
                        <img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=500" class="img-fluid slider-img shadow-lg" alt="Burgers">
                    </div>
                </div>
            </div>
            <!-- Slide 2: Pizza -->
            <div class="carousel-item">
                <div class="row align-items-center">
                    <div class="col-md-7 text-dark">
                        <span class="badge bg-danger mb-3 px-3 py-2 rounded-pill fw-bold text-uppercase text-xs">30% OFF</span>
                        <h2 class="display-5 fw-extrabold mb-2 text-dark">Stone-Oven Pizzas</h2>
                        <p class="lead mb-4">Sourdough base loaded with double mozzarella cheese and woodfired vegetables.</p>
                        <div class="d-flex gap-3">
                            <a href="menu.php?category=pizza" class="btn btn-gradient-yellow rounded-pill px-4 py-2.5 fw-bold">Order Now</a>
                            <a href="#products-grid" class="btn btn-outline-dark rounded-pill px-4 py-2.5 fw-semibold">View Menu</a>
                        </div>
                    </div>
                    <div class="col-md-5 text-center d-none d-md-block">
                        <img src="https://images.unsplash.com/photo-1513104890138-7c749659a591?w=500" class="img-fluid slider-img shadow-lg" alt="Pizzas">
                    </div>
                </div>
            </div>
            <!-- Slide 3: Fries -->
            <div class="carousel-item">
                <div class="row align-items-center">
                    <div class="col-md-7 text-dark">
                        <span class="badge bg-danger mb-3 px-3 py-2 rounded-pill fw-bold text-uppercase text-xs">Buy 1 Get 1</span>
                        <h2 class="display-5 fw-extrabold mb-2 text-dark">Peri Peri French Fries</h2>
                        <p class="lead mb-4">Crispy golden fries tossed in peri-peri masala spices with grilled toast combo.</p>
                        <div class="d-flex gap-3">
                            <a href="menu.php?category=fries" class="btn btn-gradient-yellow rounded-pill px-4 py-2.5 fw-bold">Order Now</a>
                            <a href="#products-grid" class="btn btn-outline-dark rounded-pill px-4 py-2.5 fw-semibold">View Menu</a>
                        </div>
                    </div>
                    <div class="col-md-5 text-center d-none d-md-block">
                        <img src="https://images.unsplash.com/photo-1576107232684-1279f390859f?w=500" class="img-fluid slider-img shadow-lg" alt="French Fries">
                    </div>
                </div>
            </div>
            <!-- Slide 4: Milkshakes -->
            <div class="carousel-item">
                <div class="row align-items-center">
                    <div class="col-md-7 text-dark">
                        <span class="badge bg-danger mb-3 px-3 py-2 rounded-pill fw-bold text-uppercase text-xs">15% OFF</span>
                        <h2 class="display-5 fw-extrabold mb-2 text-dark">Thick Milkshakes & Sodas</h2>
                        <p class="lead mb-4">Chilled strawberry, oreo chocolate, mango smoothies, and fresh mint lime mojitos.</p>
                        <div class="d-flex gap-3">
                            <a href="menu.php?category=drinks" class="btn btn-gradient-yellow rounded-pill px-4 py-2.5 fw-bold">Order Now</a>
                            <a href="#products-grid" class="btn btn-outline-dark rounded-pill px-4 py-2.5 fw-semibold">View Menu</a>
                        </div>
                    </div>
                    <div class="col-md-5 text-center d-none d-md-block">
                        <img src="https://images.unsplash.com/photo-1572490122747-3968b75cc699?w=500" class="img-fluid slider-img shadow-lg" alt="Milkshakes">
                    </div>
                </div>
            </div>
            <!-- Slide 5: Combos -->
            <div class="carousel-item">
                <div class="row align-items-center">
                    <div class="col-md-7 text-dark">
                        <span class="badge bg-danger mb-3 px-3 py-2 rounded-pill fw-bold text-uppercase text-xs">MEGA SAVER</span>
                        <h2 class="display-5 fw-extrabold mb-2 text-dark">Family Combo Platters</h2>
                        <p class="lead mb-4">Combo packs with loaded paneer pizzas, cheese hamburgers, french fries, and cold drink cups.</p>
                        <div class="d-flex gap-3">
                            <a href="menu.php" class="btn btn-gradient-yellow rounded-pill px-4 py-2.5 fw-bold">Order Now</a>
                            <a href="#products-grid" class="btn btn-outline-dark rounded-pill px-4 py-2.5 fw-semibold">View Menu</a>
                        </div>
                    </div>
                    <div class="col-md-5 text-center d-none d-md-block">
                        <img src="https://images.unsplash.com/photo-1544025162-d76694265947?w=500" class="img-fluid slider-img shadow-lg" alt="Combo Feast">
                    </div>
                </div>
            </div>
        </div>

        <!-- Arrows -->
        <button class="carousel-control-prev" type="button" data-bs-target="#heroAutoSlider" data-bs-slide="prev">
            <span class="carousel-control-prev-icon bg-dark rounded-circle p-2"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroAutoSlider" data-bs-slide="next">
            <span class="carousel-control-next-icon bg-dark rounded-circle p-2"></span>
        </button>
    </div>

    <!-- Video Banner (Autoplay Muted Food Preparation Promo) -->
    <div class="rounded-4 overflow-hidden mb-5 position-relative shadow-lg video-banner-premium" style="box-shadow: 0 10px 40px rgba(197, 168, 128, 0.08) !important;">
        <div class="position-absolute inset-0 d-flex align-items-center px-4" style="z-index: 2; background: linear-gradient(to right, rgba(10, 10, 10, 0.85) 40%, rgba(10, 10, 10, 0.4) 100%); backdrop-filter: blur(2px); -webkit-backdrop-filter: blur(2px);">
            <div>
                <span class="badge mb-2 fw-bold text-xs uppercase" style="background: linear-gradient(135deg, #C5A880, #D4AF37) !important; color: #0A0A0A !important; padding: 6px 14px; border-radius: 50px; box-shadow: 0 4px 15px rgba(197, 168, 128, 0.25);"><i class="fa-solid fa-shield-halved me-1"></i> Clean Kitchen Promise</span>
                <h4 class="text-white fw-bold mb-1" style="font-family: 'Poppins', sans-serif; letter-spacing: -0.5px; font-size: 1.45rem;">
                    <i class="fa-solid fa-certificate text-warning me-2" style="color: #C5A880 !important; text-shadow: 0 0 15px rgba(197,168,128,0.5);"></i> Handcrafted fresh bytes in Jaipur's cleanest kitchens
                </h4>
                <p class="text-white-50 text-xs mb-0" style="font-size: 12px; letter-spacing: 0.2px;"><i class="fa-solid fa-circle-check text-success me-1"></i> 100% sanitized prep zones, daily temperature audits & pure ingredients.</p>
            </div>
        </div>
        <video class="w-100 h-100 object-cover" autoplay loop muted playsinline style="z-index: 1; filter: brightness(0.95);">
            <source src="https://assets.mixkit.co/videos/preview/mixkit-chef-preparing-a-fresh-vegetable-salad-41582-large.mp4" type="video/mp4">
        </video>
    </div>

    <!-- Featured Today Section (Horizontal categories grid with ribbon) -->
    <div class="mb-5">
        <h4 class="fw-bold mb-4" style="font-family: 'Poppins', sans-serif; color: var(--matte-dark); font-size: 1.5rem; letter-spacing: -0.5px;">
            <i class="fa-solid fa-crown text-warning me-2" style="color: var(--premium-gold) !important; text-shadow: 0 2px 10px rgba(197,168,128,0.3);"></i> Featured Today
        </h4>
        <div class="row g-3">
            <div class="col-6 col-sm-3">
                <div class="card p-3 text-center border-0 shadow-sm position-relative food-card" style="border-radius: 20px; background: white; border: 1px solid rgba(197, 168, 128, 0.15) !important;">
                    <span class="badge position-absolute top-3 start-3 text-[9px] uppercase fw-bold" style="background: linear-gradient(135deg, #121212, #2a2a2a); color: var(--premium-gold); border: 1px solid rgba(197, 168, 128, 0.35); padding: 4px 10px; border-radius: 50px;">New</span>
                    <div class="overflow-hidden rounded-3 mb-2" style="height: 100px;">
                        <img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=250" class="w-100 h-100 object-cover hover-zoom" alt="">
                    </div>
                    <h6 class="fw-bold mt-2 mb-0 text-sm" style="color: var(--matte-dark); font-family: 'Poppins', sans-serif;">Spicy Burger</h6>
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="card p-3 text-center border-0 shadow-sm position-relative food-card" style="border-radius: 20px; background: white; border: 1px solid rgba(197, 168, 128, 0.15) !important;">
                    <span class="badge position-absolute top-3 start-3 text-[9px] uppercase fw-bold" style="background: linear-gradient(135deg, #121212, #2a2a2a); color: var(--premium-gold); border: 1px solid rgba(197, 168, 128, 0.35); padding: 4px 10px; border-radius: 50px;">New</span>
                    <div class="overflow-hidden rounded-3 mb-2" style="height: 100px;">
                        <img src="https://images.unsplash.com/photo-1513104890138-7c749659a591?w=250" class="w-100 h-100 object-cover hover-zoom" alt="">
                    </div>
                    <h6 class="fw-bold mt-2 mb-0 text-sm" style="color: var(--matte-dark); font-family: 'Poppins', sans-serif;">Cheese Pizza</h6>
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="card p-3 text-center border-0 shadow-sm position-relative food-card" style="border-radius: 20px; background: white; border: 1px solid rgba(197, 168, 128, 0.15) !important;">
                    <span class="badge position-absolute top-3 start-3 text-[9px] uppercase fw-bold" style="background: linear-gradient(135deg, #121212, #2a2a2a); color: var(--premium-gold); border: 1px solid rgba(197, 168, 128, 0.35); padding: 4px 10px; border-radius: 50px;">New</span>
                    <div class="overflow-hidden rounded-3 mb-2" style="height: 100px;">
                        <img src="https://images.unsplash.com/photo-1576107232684-1279f390859f?w=250" class="w-100 h-100 object-cover hover-zoom" alt="">
                    </div>
                    <h6 class="fw-bold mt-2 mb-0 text-sm" style="color: var(--matte-dark); font-family: 'Poppins', sans-serif;">Masala Fries</h6>
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="card p-3 text-center border-0 shadow-sm position-relative food-card" style="border-radius: 20px; background: white; border: 1px solid rgba(197, 168, 128, 0.15) !important;">
                    <span class="badge position-absolute top-3 start-3 text-[9px] uppercase fw-bold" style="background: linear-gradient(135deg, #121212, #2a2a2a); color: var(--premium-gold); border: 1px solid rgba(197, 168, 128, 0.35); padding: 4px 10px; border-radius: 50px;">New</span>
                    <div class="overflow-hidden rounded-3 mb-2" style="height: 100px;">
                        <img src="https://images.unsplash.com/photo-1572490122747-3968b75cc699?w=250" class="w-100 h-100 object-cover hover-zoom" alt="">
                    </div>
                    <h6 class="fw-bold mt-2 mb-0 text-sm" style="color: var(--matte-dark); font-family: 'Poppins', sans-serif;">Coke & Shake</h6>
                </div>
            </div>
        </div>
    </div>

    <!-- 🔥 Combo Saver Section (Burger + Fries + Drink) -->
    <div class="card border-0 p-4 mb-5 shadow-sm" style="border-radius: 20px; background: linear-gradient(135deg, #11141a 0%, #1e2330 100%); color: white;">
        <div class="row align-items-center">
            <div class="col-md-8">
                <span class="badge bg-warning text-dark mb-2 fw-bold text-xs uppercase"><i class="fa-solid fa-fire me-1"></i> Mega Combo Saver</span>
                <h4 class="fw-bold mb-1">Burger + Crispy Fries + Soft Drink</h4>
                <p class="text-secondary mb-0 small">Perfect individual treat. Get crispy veg cheese burger, salted french fries, and coke soda cup.</p>
                <div class="d-flex align-items-center gap-3 mt-3">
                    <h3 class="fw-bold text-warning mb-0">₹299 <span class="text-decoration-line-through text-muted small ms-1">₹398</span></h3>
                    <span class="badge bg-success fw-bold text-xs">Save ₹99</span>
                </div>
            </div>
            <div class="col-md-4 text-end mt-3 mt-md-0">
                <button onclick="addComboToCart()" class="btn btn-warning rounded-pill px-4 py-2.5 fw-bold shadow-lg"><i class="fa-solid fa-cart-plus me-2"></i>Order Combo</button>
            </div>
        </div>
    </div>

    <!-- Live Chef Tracker Component (Premium UI Add-on) -->
    <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded-4" style="background: rgba(34, 197, 94, 0.05); border: 1px solid rgba(34, 197, 94, 0.15);">
        <span class="position-relative d-inline-flex">
            <span class="p-1 bg-success rounded-circle position-absolute" style="animation: premium-pulse 2s infinite; inset: 0;"></span>
            <span class="p-1 bg-success rounded-circle" style="position: relative; z-index: 2;"></span>
        </span>
        <small class="text-success fw-bold" style="font-size: 12.5px; letter-spacing: 0.3px; font-family: 'Inter', sans-serif;">
            ✨ Live: 14 elite chefs are crafting fresh gourmet orders right now in Jaipur kitchens
        </small>
    </div>

    <style>
    @keyframes premium-pulse {
        0% { transform: scale(1); opacity: 1; }
        100% { transform: scale(3); opacity: 0; }
    }
    </style>

    <!-- Dynamic Category/Grid Catalog Section -->
    <div class="row" id="products-grid">
        <!-- Sidebar filters with Glassmorphism -->
        <div class="col-lg-3 mb-4">
            <!-- Categories selector -->
            <div class="filter-sidebar-glass p-4 mb-4 border-0 shadow-sm">
                <h5 class="fw-bold mb-3 text-dark dark-text-white">🍔 Categories</h5>
                <div class="list-group list-group-flush gap-2">
                    <a href="menu.php" class="list-group-item list-group-item-action border-0 rounded-pill py-2 px-4 fw-semibold <?= !$categoryFilter ? 'bg-warning text-dark' : 'bg-transparent text-secondary hover-bg-light' ?>">
                        All Foods
                    </a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="menu.php?category=<?= htmlspecialchars($cat['slug']) ?>" class="list-group-item list-group-item-action border-0 rounded-pill py-2 px-4 fw-semibold <?= $categoryFilter === $cat['slug'] ? 'bg-warning text-dark' : 'bg-transparent text-secondary' ?>">
                            <?= htmlspecialchars($cat['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Filters form -->
            <div class="filter-sidebar-glass p-4 border-0 shadow-sm">
                <h5 class="fw-bold mb-4 text-dark dark-text-white">⚙️ Filter & Sort</h5>
                <form action="menu.php" method="GET">
                    <?php if ($categoryFilter): ?>
                        <input type="hidden" name="category" value="<?= htmlspecialchars($categoryFilter) ?>">
                    <?php endif; ?>
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="veg_only" id="vegSwitch" value="1" <?= $vegOnly ? 'checked' : '' ?> onchange="this.form.submit()">
                            <label class="form-check-label fw-bold text-xs text-muted" for="vegSwitch">🥬 Veg Only</label>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-xs fw-bold text-muted">Sort By</label>
                        <select name="sort" class="form-select rounded-pill shadow-none" onchange="this.form.submit()">
                            <option value="newest" <?= $sortBy === 'newest' ? 'selected' : '' ?>>Newest</option>
                            <option value="popular" <?= $sortBy === 'popular' ? 'selected' : '' ?>>Popular</option>
                            <option value="price_low" <?= $sortBy === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="price_high" <?= $sortBy === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                            <option value="rating" <?= $sortBy === 'rating' ? 'selected' : '' ?>>Best Rating</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-xs fw-bold text-muted d-flex justify-content-between">
                            <span>Max Price</span>
                            <span class="text-warning fw-bold"><?= $currency ?><span id="val-box"><?= $maxPrice ?></span></span>
                        </label>
                        <input type="range" name="max_price" class="form-range" min="30" max="500" step="10" value="<?= $maxPrice ?>" oninput="document.getElementById('val-box').innerText = this.value" onchange="this.form.submit()">
                    </div>
                    <button type="submit" class="btn btn-warning rounded-pill w-100 py-2.5 fw-bold">Apply Filters</button>
                </form>
            </div>
        </div>

        <!-- Product Grid Column -->
        <div class="col-lg-9">
            <!-- Dynamic Sticky Skeleton Loaders Shimmer (shows only during empty or loaded states if you want to simulate loading via js) -->
            <div id="skeleton-container" class="row g-4 d-none">
                <?php for($k=0; $k<6; $k++): ?>
                <div class="col-md-6 col-xl-4">
                    <div class="card border-0 p-3 h-100" style="border-radius: 20px;">
                        <div class="skeleton-shimmer rounded-4 w-100 mb-3" style="height: 160px;"></div>
                        <div class="skeleton-shimmer rounded-2 w-75 mb-2" style="height: 16px;"></div>
                        <div class="skeleton-shimmer rounded-2 w-50" style="height: 12px;"></div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>

            <div class="row g-4" id="real-products-grid">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $p): ?>
                        <div class="col-md-6 col-xl-4">
                            <?php 
                            $isVeg = (int)$p['is_veg'];
                            ?>
                            <div class="card border-0 food-card h-100 p-3 d-flex flex-column justify-content-between position-relative shadow-sm">
                                
                                <!-- Top Right & Left Ribbons -->
                                <?php if ($p['is_featured']): ?>
                                    <span class="featured-ribbon"><i class="fa-solid fa-award me-1"></i> Bestseller</span>
                                <?php endif; ?>
                                
                                <div class="d-flex align-items-center justify-content-between position-absolute w-100 px-3 start-0 top-3" style="z-index: 10;">
                                    <div class="ms-auto d-flex align-items-center gap-1">
                                        <!-- Dynamic tags e.g. Royal Choice, Organic, Authentically Spicy -->
                                        <?php if ($p['price'] >= 180): ?>
                                            <span class="premium-tag-badge">👑 Royal Choice</span>
                                        <?php elseif ($p['is_featured']): ?>
                                            <span class="premium-tag-badge">🔥 Spicy Choice</span>
                                        <?php else: ?>
                                            <span class="premium-tag-badge">🌱 Fresh Made</span>
                                        <?php endif; ?>
                                        <button onclick="toggleWishlist(<?= $p['id'] ?>)" class="btn btn-light rounded-circle shadow-sm border-0 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" id="wish-btn-<?= $p['id'] ?>">
                                            <i class="fa-regular fa-heart text-danger"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Image and Thumbnails -->
                                <div class="mt-4">
                                    <div class="overflow-hidden rounded-4 mb-2 position-relative" style="height: 160px;">
                                        <img src="<?= (strpos($p['image'], 'http') === 0) ? htmlspecialchars($p['image']) : 'assets/images/' . htmlspecialchars($p['image'] ?? 'placeholder.png') ?>" id="card-main-img-<?= $p['id'] ?>" class="w-100 h-100 object-cover hover-zoom" alt="">
                                        <div class="img-overlay-gradient"></div>
                                        
                                        <!-- Quick View Icon overlay -->
                                        <button onclick="openQuickView(<?= $p['id'] ?>)" class="btn btn-warning rounded-circle shadow position-absolute start-50 top-50 translate-middle opacity-0 hover-trigger" style="width: 44px; height: 44px;" title="Quick View">
                                            <i class="fa-solid fa-eye text-white"></i>
                                        </button>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="rating-pill"><i class="fa-solid fa-star me-1"></i>4.5 (120 reviews)</span>
                                        
                                        <!-- Recognized Indian Veg / Non-Veg Square Icon -->
                                        <?php if ($isVeg): ?>
                                            <div class="d-inline-flex align-items-center justify-content-center border border-success" style="width: 18px; height: 18px; padding: 2px;" title="Veg">
                                                <div class="bg-success rounded-circle" style="width: 8px; height: 8px;"></div>
                                            </div>
                                        <?php else: ?>
                                            <div class="d-inline-flex align-items-center justify-content-center border border-danger" style="width: 18px; height: 18px; padding: 2px;" title="Non-Veg">
                                                <div class="bg-danger rounded-circle" style="width: 8px; height: 8px;"></div>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <h6 class="fw-bold text-dark mb-1 text-truncate"><?= htmlspecialchars($p['name']) ?></h6>
                                    
                                    <!-- Ingredients Preview line -->
                                    <p class="text-xs text-muted mb-2 text-truncate" style="max-height: 18px; font-style: italic;">
                                        <?= htmlspecialchars($p['description'] ?? 'Made with fresh hand-rolled dough and cheese.') ?>
                                    </p>
                                    
                                    <!-- Delivery details, Distance, Calories, Spice level -->
                                    <div class="d-flex flex-wrap gap-2 text-muted text-xs mb-2 font-semibold">
                                        <span><i class="fa-regular fa-clock me-1 text-warning"></i> 20-25 min</span>
                                        <span><i class="fa-solid fa-location-dot me-1 text-danger"></i> 2 km away</span>
                                        <span><i class="fa-solid fa-fire-flame-curved me-1 text-info"></i> 350 Cal</span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between text-xs text-muted mb-3">
                                        <span>🌶 Spice Level:</span>
                                        <span class="fw-bold text-warning">Medium Spicy</span>
                                    </div>

                                    <!-- Customize Options dropdown -->
                                    <div class="mb-3">
                                        <select class="form-select form-select-sm rounded-pill shadow-none" style="font-size: 11px;">
                                            <option>Size: Regular (Standard)</option>
                                            <option>Size: Medium (+₹50)</option>
                                            <option>Size: Large (+₹100)</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Price, quantity counters and actions -->
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-3 border-top pt-2">
                                        <div>
                                            <?php if ($p['discount_price'] > 0): ?>
                                                <?php 
                                                $discPct = round((($p['price'] - $p['discount_price']) / $p['price']) * 100); 
                                                $saveAmt = $p['price'] - $p['discount_price'];
                                                ?>
                                                <span class="fs-5 fw-bold text-warning"><?= $currency ?><?= number_format($p['discount_price'], 0) ?></span>
                                                <span class="text-decoration-line-through text-muted small ms-1"><?= $currency ?><?= number_format($p['price'], 0) ?></span>
                                                <div class="text-success text-[10px] fw-bold"><?= $discPct ?>% OFF (Save <?= $currency ?><?= $saveAmt ?>)</div>
                                            <?php else: ?>
                                                <span class="fs-5 fw-bold text-warning"><?= $currency ?><?= number_format($p['price'], 0) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Inline Quantity Counter -->
                                        <div class="input-group input-group-sm rounded-pill overflow-hidden border" style="width: 80px;">
                                            <button class="btn btn-light border-0 py-0 px-2" type="button" onclick="changeGridQty(<?= $p['id'] ?>, -1)"><i class="fa-solid fa-minus text-xs"></i></button>
                                            <input type="text" id="grid-qty-<?= $p['id'] ?>" class="form-control text-center border-0 bg-transparent py-0 px-0 shadow-none fw-bold" value="1" readonly style="font-size: 0.85rem;">
                                            <button class="btn btn-light border-0 py-0 px-2" type="button" onclick="changeGridQty(<?= $p['id'] ?>, 1)"><i class="fa-solid fa-plus text-xs"></i></button>
                                        </div>
                                    </div>

                                    <div class="row g-2">
                                        <div class="col-4">
                                            <button onclick="openQuickView(<?= $p['id'] ?>)" class="btn btn-outline-secondary w-100 rounded-pill btn-sm py-2 shadow-none" title="Quick View"><i class="fa-solid fa-eye"></i></button>
                                        </div>
                                        <div class="col-8">
                                            <button onclick="addGridItemToCart(<?= $p['id'] ?>)" class="btn btn-gradient-yellow w-100 rounded-pill btn-sm py-2 fw-bold"><i class="fa-solid fa-cart-shopping me-1"></i>Add</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5 text-muted">
                        <p class="fw-semibold">No food items found matching your filters.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Instagram Food Images Feed Slider (6 images) -->
    <div class="mt-5 pt-4">
        <h4 class="fw-bold mb-4 text-center"><i class="fa-brands fa-instagram text-danger me-2"></i>Follow Our Instagram @CrispyBytes</h4>
        <div class="row g-3">
            <div class="col-4 col-md-2">
                <img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=200" class="w-100 rounded-4 hover-zoom object-cover shadow-sm" style="height: 120px;" alt="">
            </div>
            <div class="col-4 col-md-2">
                <img src="https://images.unsplash.com/photo-1513104890138-7c749659a591?w=200" class="w-100 rounded-4 hover-zoom object-cover shadow-sm" style="height: 120px;" alt="">
            </div>
            <div class="col-4 col-md-2">
                <img src="https://images.unsplash.com/photo-1576107232684-1279f390859f?w=200" class="w-100 rounded-4 hover-zoom object-cover shadow-sm" style="height: 120px;" alt="">
            </div>
            <div class="col-4 col-md-2">
                <img src="https://images.unsplash.com/photo-1544025162-d76694265947?w=200" class="w-100 rounded-4 hover-zoom object-cover shadow-sm" style="height: 120px;" alt="">
            </div>
            <div class="col-4 col-md-2">
                <img src="https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=200" class="w-100 rounded-4 hover-zoom object-cover shadow-sm" style="height: 120px;" alt="">
            </div>
            <div class="col-4 col-md-2">
                <img src="https://images.unsplash.com/photo-1501443762994-82bd5dace89a?w=200" class="w-100 rounded-4 hover-zoom object-cover shadow-sm" style="height: 120px;" alt="">
            </div>
        </div>
    </div>

</div>

<!-- Floating cart -->
<a href="cart.php" class="btn btn-warning rounded-pill px-4 py-3 floating-cart-badge d-flex align-items-center gap-2 text-decoration-none">
    <i class="fa-solid fa-cart-shopping fs-5"></i>
    <span class="fw-bold d-none d-sm-inline">Check Active Cart</span>
</a>

<!-- Quick View Popup Modal -->
<div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden glass-card p-0">
            <div class="modal-body p-0">
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3 z-3" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="row g-0" id="quickview-content">
                    <!-- Dynamic details loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Refactored Scripts Link -->
<script src="assets/js/menu.js"></script>

<script>
// Countdown timer script
let countdownVal = 6332; // seconds
setInterval(() => {
    countdownVal--;
    if (countdownVal < 0) countdownVal = 6332; // reset loop
    
    let hours = Math.floor(countdownVal / 3600);
    let minutes = Math.floor((countdownVal % 3600) / 60);
    let seconds = countdownVal % 60;
    
    const topCountdown = document.getElementById('top-countdown');
    if (topCountdown) {
        topCountdown.innerText = 
            String(hours).padStart(2, '0') + ':' + 
            String(minutes).padStart(2, '0') + ':' + 
            String(seconds).padStart(2, '0');
    }
}, 1000);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
