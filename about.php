<?php
// about.php
require_once __DIR__ . '/includes/header.php';
?>

<!-- Include Poppins & Inter fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<!-- Scroll Reveal CDN -->
<script src="https://unpkg.com/scrollreveal"></script>
<!-- Link Premium Unified CSS Structure -->
<link rel="stylesheet" href="assets/css/menu-premium.css">

<style>
body {
    font-family: 'Inter', sans-serif;
    background-color: var(--luxury-white);
}
.about-hero {
    background: rgba(255, 255, 255, 0.75) !important;
    backdrop-filter: blur(16px) saturate(180%);
    -webkit-backdrop-filter: blur(16px) saturate(180%);
    border: 1px solid rgba(197, 168, 128, 0.25) !important;
    border-radius: 30px;
    overflow: hidden;
}
.chef-card {
    background: white;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(0,0,0,0.03);
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.4s ease;
}
.chef-card:hover {
    transform: translateY(-6px);
    box-shadow: var(--hover-shadow) !important;
}
.chef-img {
    height: 300px;
    object-fit: cover;
}
.partner-logo {
    font-size: 1.25rem;
    font-weight: 700;
    color: #a0aec0;
    letter-spacing: 2px;
    transition: color 0.3s;
}
.partner-logo:hover {
    color: var(--matte-dark);
}
@keyframes livePulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: 0.4; transform: scale(1.3); }
}
.hover-zoom { transition: transform 0.5s ease; }
.hover-zoom:hover { transform: scale(1.05); }
</style>

<div class="container py-5">

    <!-- 1. Luxury Hero Section -->
    <div class="about-hero p-4 p-md-5 mb-5 position-relative">
        <div class="row align-items-center g-4">
            <div class="col-lg-7 text-dark hero-text">
                <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                    <span class="premium-tag-badge"><i class="fa-solid fa-medal me-1"></i> Established 2018</span>
                    <span class="rating-pill"><i class="fa-solid fa-star me-1"></i>4.9 Global Rating</span>
                </div>
                <h1 class="display-4 fw-extrabold mb-3" style="font-family: 'Poppins', sans-serif; letter-spacing: -1px;">
                    Jaipur's Elite <span style="color: var(--premium-gold);">Gourmet Grid</span>
                </h1>
                <p class="lead text-muted mb-4" style="font-size: 1rem; line-height: 1.75;">
                    Trusted by 10,000+ patrons across the Pink City. We specialize in handcrafted sourdough pizzas, flame-grilled luxury burgers, and artisanal sides made with passion daily.
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="menu.php" class="btn btn-gradient-yellow rounded-pill px-4 py-2 fw-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Explore Menu</a>
                    <a href="#kitchen-video" class="btn btn-outline-dark rounded-pill px-4 py-2 fw-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Watch Kitchen</a>
                </div>
            </div>
            <div class="col-lg-5 text-center hero-img">
                <div class="rounded-circle overflow-hidden mx-auto shadow-lg" style="width: 300px; height: 300px; border: 3px solid var(--premium-gold);">
                    <img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=500" class="w-100 h-100" style="object-fit: cover;" alt="Tasty Burgers">
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Premium Kitchen Showcase Section -->
    <div id="kitchen-video" class="mb-5 reveal-section">
        <div class="text-center mb-5">
            <span class="premium-tag-badge mb-2">
                <i class="fa-solid fa-shield-halved me-1"></i> 100% Hygienic Certified Workspace
            </span>
            <h2 class="fw-bold mb-2" style="font-family: 'Poppins', sans-serif;">Our Kitchen — Live & Transparent</h2>
            <p class="text-muted" style="font-size: 12px;">Complete transparency metrics. Watch our chefs handle every single dish with meticulous safety guidelines.</p>
        </div>

        <!-- 3-Column Video Grid -->
        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="position-relative overflow-hidden shadow-sm" style="border-radius: 24px; height: 340px; border: 1px solid rgba(197, 168, 128, 0.2);">
                    <video class="w-100 h-100" autoplay loop muted playsinline style="object-fit:cover; position:absolute; inset:0;">
                        <source src="https://assets.mixkit.co/videos/preview/mixkit-chef-preparing-a-fresh-vegetable-salad-41582-large.mp4" type="video/mp4">
                    </video>
                    <div style="position:absolute;inset:0; background:linear-gradient(to top, rgba(0,0,0,0.85) 0%, transparent 70%); z-index:1;"></div>
                    <div style="position:absolute;bottom:0;left:0;right:0; padding:24px; z-index:2;">
                        <span class="badge bg-success text-uppercase rounded-pill px-2 py-1" style="font-size:9px; letter-spacing:0.5px;">🌱 Live Prep</span>
                        <h5 class="text-white fw-bold mt-2 mb-1" style="font-family:'Poppins',sans-serif;">Organic Ingredient Assembly</h5>
                        <p class="text-white-50 mb-0" style="font-size:12px;">Hand-picked seasonal vegetables processed fresh every morning.</p>
                    </div>
                    <div style="position:absolute;top:16px;right:16px;z-index:3;background:rgba(239,68,68,0.9);color:white;font-size:10px;font-weight:800;padding:5px 12px;border-radius:20px;display:flex;align-items:center;gap:6px;">
                        <span style="width:7px;height:7px;background:white;border-radius:50%;animation:livePulse 1s infinite;display:inline-block;"></span> LIVE CAMERA
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="d-flex flex-column gap-3">
                    <div class="position-relative overflow-hidden shadow-sm" style="border-radius:24px; height:162px; border: 1px solid rgba(197, 168, 128, 0.15);">
                        <video class="w-100 h-100" autoplay loop muted playsinline style="object-fit:cover; position:absolute; inset:0;">
                            <source src="https://v1.mixkit.co/videos/preview/mixkit-kneading-dough-on-a-floured-surface-41551-large.mp4" type="video/mp4">
                        </video>
                        <div style="position:absolute;inset:0; background:linear-gradient(to top, rgba(0,0,0,0.8) 0%, transparent 70%); z-index:1;"></div>
                        <div style="position:absolute;bottom:0;left:0;right:0; padding:18px; z-index:2;">
                            <span class="badge rounded-pill text-uppercase px-2 py-1" style="background:#121212;color:var(--premium-gold);font-size:9px;">Pizza Deck</span>
                            <h6 class="text-white fw-bold mt-1 mb-0">Artisanal Sourdough Kneading</h6>
                        </div>
                    </div>
                    <div class="position-relative overflow-hidden shadow-sm" style="border-radius:24px; height:162px; border: 1px solid rgba(197, 168, 128, 0.15);">
                        <video class="w-100 h-100" autoplay loop muted playsinline style="object-fit:cover; position:absolute; inset:0;">
                            <source src="https://v1.mixkit.co/videos/preview/mixkit-cooking-hamburgers-on-a-hot-grill-41589-large.mp4" type="video/mp4">
                        </video>
                        <div style="position:absolute;inset:0; background:linear-gradient(to top, rgba(0,0,0,0.8) 0%, transparent 70%); z-index:1;"></div>
                        <div style="position:absolute;bottom:0;left:0;right:0; padding:18px; z-index:2;">
                            <span class="badge rounded-pill text-uppercase px-2 py-1" style="background:#121212;color:var(--premium-gold);font-size:9px;">Grill House</span>
                            <h6 class="text-white fw-bold mt-1 mb-0">Sear-Station Temperature Audits</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trust Badge Strip -->
        <div class="d-flex flex-wrap justify-content-center gap-3 mb-5">
            <?php
            $trustBadges = [
                ['icon' => 'fa-shield-virus',        'text' => 'FSSAI Certified Hub'],
                ['icon' => 'fa-temperature-low',     'text' => 'Cold Chain Verification'],
                ['icon' => 'fa-spray-can-sparkles',  'text' => 'Daily Deep Sanitization'],
                ['icon' => 'fa-leaf',                'text' => '100% Organic Sourcing'],
            ];
            foreach ($trustBadges as $b): ?>
            <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-pill"
                 style="background:#ffffff; border:1px solid rgba(197,168,128,0.25); box-shadow:0 2px 12px rgba(0,0,0,0.04);">
                <i class="fa-solid <?= $b['icon'] ?>" style="color: var(--premium-gold); font-size: 13px;"></i>
                <span style="font-size: 12px; font-weight: 700; color: var(--matte-dark);"><?= $b['text'] ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 3. Master Chefs Profile Panel -->
    <div class="mb-5 reveal-section">
        <div class="text-center mb-5">
            <span class="premium-tag-badge mb-2">Culinary Experts</span>
            <h2 class="fw-bold" style="font-family: 'Poppins', sans-serif;">Meet Our Master Chefs</h2>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="chef-card text-center p-3">
                    <div class="overflow-hidden rounded-4 mb-3" style="height: 280px;">
                        <img src="https://images.unsplash.com/photo-1577219491135-ce391730fb2c?w=400" class="w-100 h-100 hover-zoom" style="object-fit: cover;" alt="Chef Sonu Singh">
                    </div>
                    <h5 class="fw-bold mb-1 text-dark" style="font-family: 'Poppins', sans-serif;">Sonu Singh Sikarwar</h5>
                    <div class="rating-pill mb-2 d-inline-block"><i class="fa-solid fa-star me-1"></i>5.0 Head Kitchen Exec</div>
                    <p class="text-muted mb-1" style="font-size: 12px;"><strong>Experience:</strong> 5 Years Elite Gastronomy</p>
                    <p class="text-muted mb-3" style="font-size: 12px;"><strong>Signature Masterpiece:</strong> 🍕 Double Cheese Sourdough</p>
                    <div class="d-flex justify-content-center gap-3 text-muted border-top pt-3">
                        <a href="#" class="text-secondary"><i class="fa-brands fa-instagram fs-5"></i></a>
                        <a href="#" class="text-secondary"><i class="fa-brands fa-linkedin fs-5"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-5 col-lg-4">
                <div class="chef-card text-center p-3">
                    <div class="overflow-hidden rounded-4 mb-3" style="height: 280px;">
                        <img src="https://images.unsplash.com/photo-1607631568010-a87245c0daf8?w=400" class="w-100 h-100 hover-zoom" style="object-fit: cover;" alt="Chef Priya Sharma">
                    </div>
                    <h5 class="fw-bold mb-1 text-dark" style="font-family: 'Poppins', sans-serif;">Priya Sharma</h5>
                    <div class="rating-pill mb-2 d-inline-block"><i class="fa-solid fa-star me-1"></i>4.9 Pastry & Desserts Chef</div>
                    <p class="text-muted mb-1" style="font-size: 12px;"><strong>Experience:</strong> 4 Years Patisserie</p>
                    <p class="text-muted mb-3" style="font-size: 12px;"><strong>Signature Masterpiece:</strong> 🍩 Glazed Choco Donut</p>
                    <div class="d-flex justify-content-center gap-3 text-muted border-top pt-3">
                        <a href="#" class="text-secondary"><i class="fa-brands fa-instagram fs-5"></i></a>
                        <a href="#" class="text-secondary"><i class="fa-brands fa-linkedin fs-5"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Accreditations & Recognitions -->
    <div class="mb-5 reveal-section">
        <div class="text-center mb-5">
            <span class="premium-tag-badge mb-2">Honors</span>
            <h2 class="fw-bold" style="font-family: 'Poppins', sans-serif;">Awards & Recognition</h2>
        </div>
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="card border-0 filter-sidebar-glass p-4 h-100">
                    <i class="fa-solid fa-trophy mb-3 fs-2" style="color: var(--premium-gold);"></i>
                    <h5 class="fw-bold text-dark mb-2" style="font-family: 'Poppins', sans-serif;">Best Cloud Concept 2024</h5>
                    <p class="text-muted mb-0" style="font-size: 12px;">Awarded by Jaipur Foodies Council for maintaining unmatched luxury sanitization parameters.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 filter-sidebar-glass p-4 h-100">
                    <i class="fa-solid fa-award mb-3 fs-2" style="color: var(--premium-gold);"></i>
                    <h5 class="fw-bold text-dark mb-2" style="font-family: 'Poppins', sans-serif;">Food Sourcing Excellence</h5>
                    <p class="text-muted mb-0" style="font-size: 12px;">Recognizing our farm-to-table organic deployment and daily ingredient audit benchmarks.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 filter-sidebar-glass p-4 h-100">
                    <i class="fa-solid fa-thumbs-up mb-3 fs-2" style="color: var(--premium-gold);"></i>
                    <h5 class="fw-bold text-dark mb-2" style="font-family: 'Poppins', sans-serif;">Patron Choice Title</h5>
                    <p class="text-muted mb-0" style="font-size: 12px;">Maintained for consecutive standard execution of rapid sub-30 minute elite home delivery logistics.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. Luxury Call-To-Action Canvas Bar -->
    <div class="card border-0 p-4 p-md-5 text-white mb-4 shadow-sm reveal-section"
         style="border-radius: 28px; background: linear-gradient(135deg, #11141a 0%, #1e2330 100%); border: 1px solid rgba(197, 168, 128, 0.15) !important;">
        <div class="row align-items-center g-4">
            <div class="col-md-8 text-center text-md-start">
                <h3 class="fw-extrabold mb-2" style="font-family: 'Poppins', sans-serif; letter-spacing: -0.5px; font-size: clamp(1.4rem, 3vw, 2rem);">
                    Ready to Experience Gourmet Perfection?
                </h3>
                <p class="mb-0" style="color: rgba(255,255,255,0.55); font-size: 13px;">Select your priority culinary artwork and place a swift delivery request now.</p>
                <div class="d-flex justify-content-center justify-content-md-start gap-3 mt-4">
                    <a href="menu.php" class="btn btn-gradient-yellow rounded-pill px-5 py-2 fw-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Order Online Now</a>
                </div>
            </div>
            <div class="col-md-4 text-center d-none d-md-block">
                <img src="https://images.unsplash.com/photo-1513104890138-7c749659a591?w=350"
                     class="img-fluid rounded-4" style="border: 1px solid rgba(197,168,128,0.25); max-height: 200px; object-fit: cover;"
                     alt="Loaded Pizza">
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    ScrollReveal().reveal('.hero-text', { delay: 100, origin: 'left',   distance: '40px', duration: 700 });
    ScrollReveal().reveal('.hero-img',  { delay: 200, origin: 'right',  distance: '40px', duration: 700 });
    ScrollReveal().reveal('.reveal-section', { delay: 150, origin: 'bottom', distance: '40px', interval: 100, duration: 700 });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
