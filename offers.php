<?php
// offers.php
require_once __DIR__ . '/includes/header.php';
?>

<!-- Include Poppins & Inter fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<!-- Scroll Reveal CDN -->
<script src="https://unpkg.com/scrollreveal"></script>
<!-- Canvas Confetti CDN -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

<style>
body {
    font-family: 'Inter', sans-serif;
    background-color: #fbf9f4;
    background-image: url('https://www.transparenttextures.com/patterns/food.png');
}
h1, h2, h3, h4, h5, h6 {
    font-family: 'Poppins', sans-serif;
}

/* Coupon Perforated Edge Design */
.coupon-ticket {
    background: white;
    border-radius: 16px;
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(0,0,0,0.05);
}
.coupon-ticket::before, .coupon-ticket::after {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    background: #fbf9f4;
    border-radius: 50%;
    top: 50%;
    transform: translateY(-50%);
    z-index: 2;
    border: 1px solid rgba(0,0,0,0.05);
}
.coupon-ticket::before {
    left: -10px;
}
.coupon-ticket::after {
    right: -10px;
}

/* Category Filter active state */
.offer-filter-btn {
    transition: all 0.3s ease;
}
.offer-filter-btn.active {
    background-color: var(--primary) !important;
    color: white !important;
    border-color: var(--primary) !important;
}

/* Scratch Card effect */
.scratch-box {
    width: 100%;
    max-width: 300px;
    height: 150px;
    background: #ccc;
    border-radius: 16px;
    position: relative;
    overflow: hidden;
    cursor: pointer;
    box-shadow: inset 0 0 20px rgba(0,0,0,0.2);
}
.scratch-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #7f8c8d 0%, #95a5a6 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 1.1rem;
    transition: opacity 0.5s ease;
    z-index: 5;
}
.scratch-box.scratched .scratch-overlay {
    opacity: 0;
    pointer-events: none;
}
.scratch-prize {
    position: absolute;
    inset: 0;
    background: #fff8e1;
    display: flex;
    flex-column: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: #ff9800;
}
</style>

<div class="container py-4">

    <!-- 1. Hero Offer Banner -->
    <div class="card border-0 p-4 p-md-5 text-white mb-5 shadow-lg position-relative overflow-hidden" style="border-radius: 30px; background: linear-gradient(135deg, #e65100 0%, #ff9800 100%);">
        <div class="row align-items-center g-4 position-relative" style="z-index: 2;">
            <div class="col-lg-7 text-dark hero-text">
                <span class="badge bg-danger mb-2 px-3 py-1.5 rounded-pill fw-bold text-xs uppercase"><i class="fa-solid fa-fire me-1"></i> Mega Festival Sale</span>
                <h1 class="display-4 fw-extrabold mb-2 text-white">Flat 40% OFF</h1>
                <p class="lead mb-4 text-white/95">Order your favorite burgers & pizzas. Use coupon code <strong class="bg-white/20 px-2.5 py-1 rounded text-white">MEGA40</strong> at checkout.</p>
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <div class="bg-black/20 text-white rounded-pill px-4 py-2.5 fw-bold text-sm">
                        <i class="fa-regular fa-clock me-2"></i> Ends in: <span id="hero-countdown">03:12:45</span>
                    </div>
                    <a href="menu.php" class="btn btn-dark rounded-pill px-4 py-2.5 fw-bold text-white shadow-lg">Order Now</a>
                </div>
            </div>
            <div class="col-lg-5 text-center position-relative hero-img">
                <img src="https://images.unsplash.com/photo-1513104890138-7c749659a591?w=500" class="img-fluid rounded-4 shadow-sm" alt="Mega Pizza Combo">
            </div>
        </div>
    </div>

    <!-- 5. Offer Categories tabs -->
    <div class="d-flex flex-wrap justify-content-center gap-2 mb-5">
        <button onclick="filterOffers('all')" class="btn btn-outline-warning rounded-pill px-4 py-2 fw-bold offer-filter-btn active" id="offer-btn-all">All Offers</button>
        <button onclick="filterOffers('coupons')" class="btn btn-outline-warning rounded-pill px-4 py-2 fw-bold offer-filter-btn" id="offer-btn-coupons">Coupons</button>
        <button onclick="filterOffers('combos')" class="btn btn-outline-warning rounded-pill px-4 py-2 fw-bold offer-filter-btn" id="offer-btn-combos">Combo Deals</button>
        <button onclick="filterOffers('bank')" class="btn btn-outline-warning rounded-pill px-4 py-2 fw-bold offer-filter-btn" id="offer-btn-bank">Bank Offers</button>
    </div>

    <!-- 3. Coupon Cards Grid -->
    <div class="row g-4 mb-5 offer-section" data-type="coupons">
        <h4 class="fw-bold mb-1 col-12"><i class="fa-solid fa-ticket text-warning me-2"></i>Featured Promo Coupons</h4>
        
        <!-- Coupon 1 -->
        <div class="col-md-6 col-lg-4">
            <div class="coupon-ticket p-4 hover-lift">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="badge bg-danger/10 text-danger text-xs fw-bold"><i class="fa-solid fa-bolt me-1"></i> Popular Coupon</span>
                    <span class="text-xs text-secondary">Ends in <span class="countdown-span" data-hours="5">05h 00m</span></span>
                </div>
                <h4 class="fw-extrabold text-dark mb-1">50% OFF</h4>
                <p class="text-secondary small mb-3">On all Pizza items above ₹399. Valid today only.</p>
                <div class="progress mb-3" style="height: 6px;">
                    <div class="progress-bar bg-warning" style="width: 82%;"></div>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="text-xs fw-bold text-secondary">🔥 82% Claimed</span>
                    <button onclick="copyCouponCode('PIZZA50')" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold">PIZZA50</button>
                </div>
            </div>
        </div>

        <!-- Coupon 2 -->
        <div class="col-md-6 col-lg-4">
            <div class="coupon-ticket p-4 hover-lift">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="badge bg-danger/10 text-danger text-xs fw-bold"><i class="fa-solid fa-bolt me-1"></i> Trending</span>
                    <span class="text-xs text-secondary">Ends in <span class="countdown-span" data-hours="8">08h 00m</span></span>
                </div>
                <h4 class="fw-extrabold text-dark mb-1">₹100 FLAT OFF</h4>
                <p class="text-secondary small mb-3">Save flat ₹100 on your first burger box order above ₹299.</p>
                <div class="progress mb-3" style="height: 6px;">
                    <div class="progress-bar bg-warning" style="width: 60%;"></div>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="text-xs fw-bold text-secondary">🔥 60% Claimed</span>
                    <button onclick="copyCouponCode('BURGER100')" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold">BURGER100</button>
                </div>
            </div>
        </div>

        <!-- Coupon 3 -->
        <div class="col-md-6 col-lg-4">
            <div class="coupon-ticket p-4 hover-lift">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="badge bg-danger/10 text-danger text-xs fw-bold"><i class="fa-solid fa-bolt me-1"></i> Limited Time</span>
                    <span class="text-xs text-secondary">Ends in <span class="countdown-span" data-hours="12">12h 00m</span></span>
                </div>
                <h4 class="fw-extrabold text-dark mb-1">FREE DELIVERY</h4>
                <p class="text-secondary small mb-3">Free home delivery on all lunch orders above ₹249.</p>
                <div class="progress mb-3" style="height: 6px;">
                    <div class="progress-bar bg-warning" style="width: 45%;"></div>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="text-xs fw-bold text-secondary">🔥 45% Claimed</span>
                    <button onclick="copyCouponCode('FREEDEL')" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold">FREEDEL</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Featured Combo Offers -->
    <div class="row g-4 mb-5 offer-section" data-type="combos">
        <h4 class="fw-bold mb-1 col-12"><i class="fa-solid fa-burger text-warning me-2"></i>Featured Combo Specials</h4>
        
        <!-- Combo 1 -->
        <div class="col-md-6">
            <div class="card border-0 p-4 shadow-sm hover-lift h-100" style="border-radius: 20px; background: white;">
                <div class="row align-items-center g-3">
                    <div class="col-sm-5">
                        <img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=300" class="img-fluid rounded-4" alt="Burger Combo">
                    </div>
                    <div class="col-sm-7">
                        <span class="badge bg-danger mb-2">HOT COMBO</span>
                        <h5 class="fw-bold text-dark mb-1">Double Burger & Coke Combo</h5>
                        <p class="text-secondary text-xs mb-3">2 Classic Cheese Burgers, Large French Fries, & 2 Ice Cold Coca Colas.</p>
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="text-warning fw-extrabold fs-4">₹349</span>
                            <span class="text-decoration-line-through text-muted small">₹499</span>
                            <span class="badge bg-warning text-dark text-xs font-bold">Save ₹150</span>
                        </div>
                        <a href="menu.php?category=burgers" class="btn btn-warning rounded-pill px-4 btn-sm fw-bold">Order Combo</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Combo 2 -->
        <div class="col-md-6">
            <div class="card border-0 p-4 shadow-sm hover-lift h-100" style="border-radius: 20px; background: white;">
                <div class="row align-items-center g-3">
                    <div class="col-sm-5">
                        <img src="https://images.unsplash.com/photo-1513104890138-7c749659a591?w=300" class="img-fluid rounded-4" alt="Pizza Combo">
                    </div>
                    <div class="col-sm-7">
                        <span class="badge bg-danger mb-2">SAVER DEALS</span>
                        <h5 class="fw-bold text-dark mb-1">Family Pizza Feast Combo</h5>
                        <p class="text-secondary text-xs mb-3">1 Gourmet Paneer Pizza, 1 Classic Margherita Pizza, Garlic Bread, & Coke.</p>
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="text-warning fw-extrabold fs-4">₹599</span>
                            <span class="text-decoration-line-through text-muted small">₹799</span>
                            <span class="badge bg-warning text-dark text-xs font-bold">Save ₹200</span>
                        </div>
                        <a href="menu.php?category=pizza" class="btn btn-warning rounded-pill px-4 btn-sm fw-bold">Order Combo</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. Bank Offers -->
    <div class="row g-4 mb-5 offer-section" data-type="bank">
        <h4 class="fw-bold mb-1 col-12"><i class="fa-solid fa-credit-card text-warning me-2"></i>Instant Bank Partnerships</h4>
        
        <div class="col-md-4">
            <div class="card border-0 p-4 shadow-sm h-100 text-center" style="border-radius: 16px;">
                <div class="mb-3"><i class="fa-solid fa-credit-card fs-1 text-primary"></i></div>
                <h6 class="fw-bold">SBI Credit Cards</h6>
                <h4 class="fw-extrabold text-warning">10% CASHBACK</h4>
                <p class="text-secondary small mb-0">Up to ₹100 on transactions above ₹499 via SBI Cards.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 p-4 shadow-sm h-100 text-center" style="border-radius: 16px;">
                <div class="mb-3"><i class="fa-solid fa-credit-card fs-1 text-danger"></i></div>
                <h6 class="fw-bold">HDFC Bank Debit Cards</h6>
                <h4 class="fw-extrabold text-warning">15% INSTANT OFF</h4>
                <p class="text-secondary small mb-0">Use code HDFCOFF on checkout orders above ₹599.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 p-4 shadow-sm h-100 text-center" style="border-radius: 16px;">
                <div class="mb-3"><i class="fa-solid fa-credit-card fs-1 text-info"></i></div>
                <h6 class="fw-bold">ICICI Bank NetBanking</h6>
                <h4 class="fw-extrabold text-warning">FLAT ₹100 OFF</h4>
                <p class="text-secondary small mb-0">Get flat ₹100 off on your first month order above ₹399.</p>
            </div>
        </div>
    </div>

    <!-- 8. Interactive Scratch Card -->
    <div class="card border-0 p-4 mb-5 shadow-sm text-center align-items-center" style="border-radius: 20px; background: white;">
        <h5 class="fw-bold mb-1">🎁 Scratch & Win Prizes</h5>
        <p class="text-secondary small mb-3">Scratch or click on the card below to unlock a random weekend reward coupon!</p>
        <div class="scratch-box" id="scratchCard" onclick="scratchReward()">
            <div class="scratch-overlay" id="scratchOverlay">SCRATCH & WIN HERE</div>
            <div class="scratch-prize">
                <h4 class="fw-bold mb-1">🎉 YOU WON!</h4>
                <h5 class="fw-extrabold text-warning m-0" id="prizeText">FREE FRIES</h5>
                <p class="text-secondary text-xs mt-1">On orders above ₹199</p>
            </div>
        </div>
    </div>

    <!-- 9. Loyalty Rewards & Referral Program -->
    <div class="row g-4 mb-5 reveal-section">
        <div class="col-md-6">
            <div class="card border-0 p-4 shadow-sm h-100" style="border-radius: 20px; background: white;">
                <span class="badge bg-warning text-dark align-self-start mb-2 px-3 py-1 rounded-pill fw-bold text-xs"><i class="fa-solid fa-crown"></i> GOLD MEMBER</span>
                <h5 class="fw-bold">Loyalty Rewards Points</h5>
                <p class="text-secondary small mb-3">Earn coins on every burger/pizza order checkout and redeem them for free cold drinks, garlic bread, or cheesecakes.</p>
                <div class="d-flex align-items-center justify-content-between border-top pt-3">
                    <span class="small font-bold text-dark">Your Balance: <strong>1,200 Points</strong></span>
                    <button class="btn btn-warning btn-sm rounded-pill fw-bold" onclick="alert('Loyalty store integration is on its way!')">Redeem Now</button>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 p-4 shadow-sm h-100" style="border-radius: 20px; background: white;">
                <span class="badge bg-info text-white align-self-start mb-2 px-3 py-1 rounded-pill fw-bold text-xs"><i class="fa-solid fa-users"></i> REFERRAL</span>
                <h5 class="fw-bold">Invite Friends & Earn Cash</h5>
                <p class="text-secondary small mb-3">Share your custom referral link with friends. When they place their first order, both of you get flat ₹100 in your wallet instantly!</p>
                <div class="d-flex align-items-center justify-content-between border-top pt-3">
                    <span class="small font-bold text-dark">Code: <strong>CRISPY100</strong></span>
                    <button class="btn btn-warning btn-sm rounded-pill fw-bold" onclick="copyCouponCode('CRISPY100')">Copy Code</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 11. Coupon Terms (Accordion) -->
    <div class="mb-5 reveal-section" style="max-width: 750px; margin: 0 auto;">
        <h5 class="fw-bold text-center mb-4">📜 Coupon Rules & Disclaimers</h5>
        <div class="accordion border-0" id="termsAccordion">
            <div class="accordion-item border-0 mb-2 rounded-3 overflow-hidden shadow-xs">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-bold text-dark shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#t1">
                        What is the minimum order value for PIZZA50?
                    </button>
                </h2>
                <div id="t1" class="accordion-collapse collapse" data-bs-parent="#termsAccordion">
                    <div class="accordion-body text-secondary bg-white text-sm">
                        You need to add items worth at least ₹399 from the Pizza category to claim the 50% discount.
                    </div>
                </div>
            </div>
            <div class="accordion-item border-0 mb-2 rounded-3 overflow-hidden shadow-xs">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-bold text-dark shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#t2">
                        Can bank offers be combined with discount coupons?
                    </button>
                </h2>
                <div id="t2" class="accordion-collapse collapse" data-bs-parent="#termsAccordion">
                    <div class="accordion-body text-secondary bg-white text-sm">
                        No, only one promotional code (either a platform coupon code or an direct bank discount) can be processed per billing cycle.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 14. CTA Section -->
    <div class="card border-0 p-4 p-md-5 text-white mb-4 shadow-sm" style="border-radius: 24px; background: linear-gradient(135deg, #11141a 0%, #1e2330 100%);">
        <div class="row align-items-center g-4">
            <div class="col-md-8">
                <h3 class="display-6 fw-extrabold mb-2">Ready to Save More?</h3>
                <p class="text-secondary mb-0">Claim any active coupons and checkout with premium savings instantly.</p>
                <div class="d-flex gap-3 mt-4">
                    <a href="menu.php" class="btn btn-warning rounded-pill px-4 py-2.5 fw-bold shadow-lg">Order Now</a>
                </div>
            </div>
            <div class="col-md-4 text-center d-none d-md-block">
                <img src="https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=350" class="img-fluid rounded-4 hover-zoom" alt="Mega Pizza Combo">
            </div>
        </div>
    </div>

</div>

<!-- Success Toast Notification -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 10000;">
    <div id="copyToast" class="toast align-items-center text-white bg-success border-0 rounded-4 shadow" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fw-bold">
                <i class="fa-solid fa-circle-check me-2"></i> Coupon Copied Successfully!
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
// Countdown timers
function updateCountdownTimers() {
    // Hero timer
    const heroTimer = document.getElementById('hero-countdown');
    if (heroTimer) {
        let parts = heroTimer.innerText.split(':');
        let h = parseInt(parts[0]);
        let m = parseInt(parts[1]);
        let s = parseInt(parts[2]);
        s--;
        if (s < 0) { s = 59; m--; }
        if (m < 0) { m = 59; h--; }
        if (h < 0) { h = 3; m = 12; s = 45; } // reset loop
        heroTimer.innerText = `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
    }
}
setInterval(updateCountdownTimers, 1000);

// Filter Categories
function filterOffers(categoryType) {
    document.querySelectorAll('.offer-filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    const activeBtn = document.getElementById(`offer-btn-${categoryType}`);
    if (activeBtn) activeBtn.classList.add('active');

    document.querySelectorAll('.offer-section').forEach(sec => {
        const type = sec.getAttribute('data-type');
        if (categoryType === 'all' || type === categoryType) {
            sec.style.display = 'flex';
        } else {
            sec.style.display = 'none';
        }
    });
}

// Copy Coupon Trigger + Confetti
function copyCouponCode(code) {
    navigator.clipboard.writeText(code).then(() => {
        // Show Toast
        const toastEl = document.getElementById('copyToast');
        if (toastEl) {
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        }
        
        // Shoot Confetti
        confetti({
            particleCount: 80,
            spread: 60,
            origin: { y: 0.8 }
        });
    });
}

// Scratch card trigger
function scratchReward() {
    const box = document.getElementById('scratchCard');
    const overlay = document.getElementById('scratchOverlay');
    const prizeText = document.getElementById('prizeText');
    
    if (box && !box.classList.contains('scratched')) {
        box.classList.add('scratched');
        
        // Randomize prize
        const prizes = ["FREE FRIES", "20% OFF DEALS", "FREE ICE COLD COKE", "BUY 1 GET 1 PIZZA"];
        const randPrize = prizes[Math.floor(Math.random() * prizes.length)];
        prizeText.innerText = randPrize;
        
        confetti({
            particleCount: 120,
            spread: 80,
            origin: { y: 0.7 }
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    ScrollReveal().reveal('.reveal-section', { delay: 150, origin: 'bottom', distance: '40px', interval: 100 });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
