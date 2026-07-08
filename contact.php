<?php
// contact.php
require_once __DIR__ . '/includes/header.php';

$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = sanitizeInput($_POST['name'] ?? '');
    $email       = sanitizeInput($_POST['email'] ?? '');
    $phone       = sanitizeInput($_POST['phone'] ?? '');
    $subject     = sanitizeInput($_POST['subject'] ?? 'General Enquiry');
    $message     = sanitizeInput($_POST['message'] ?? '');
    $bookingDate = sanitizeInput($_POST['booking_date'] ?? '');
    $bookingTime = sanitizeInput($_POST['booking_time'] ?? '');
    $guests      = sanitizeInput($_POST['guests'] ?? '');
    $occasion    = sanitizeInput($_POST['occasion'] ?? '');

    if ($name && $email && $message) {
        try {
            if ($bookingDate && $bookingTime) {
                $message .= "\n\n[Table Reservation: Date: $bookingDate, Time: $bookingTime, Guests: $guests, Occasion: $occasion, Phone: $phone]";
            }
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message, status) VALUES (?, ?, ?, ?, 'unread')");
            $stmt->execute([$name, $email, $subject, $message]);
            $successMsg = 'Thank you! Your message has been sent successfully. We\'ll get back to you shortly.';
        } catch (Exception $e) {
            $errorMsg = 'Error saving message. Please try again.';
        }
    } else {
        $errorMsg = 'Please fill out all required fields.';
    }
}

$emailVal   = getSetting('contact_email', 'info@crispybytes.com');
$phoneVal   = getSetting('contact_phone', '+91 95164 40137');
$addressVal = getSetting('address', '202, Pink City Food Court, MI Road, Jaipur, Rajasthan, India');
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://unpkg.com/scrollreveal"></script>

<style>
:root {
    --primary: #FF6B35;
    --accent:  #FFBE0B;
    --dark:    #1a1a2e;
    --darker:  #0f0f1a;
}
body { font-family: 'Inter', sans-serif; background: #f6f5f1; }
h1,h2,h3,h4,h5,h6 { font-family: 'Poppins', sans-serif; }

/* ======= HERO ======= */
.contact-hero {
    background: linear-gradient(135deg, #0f0f1a 0%, #1a1a2e 40%, #2d1b4e 70%, #FF6B35 130%);
    border-radius: 32px; overflow: hidden; position: relative;
    min-height: 440px; display: flex; align-items: center;
}
.contact-hero::before {
    content: '';
    position: absolute; width: 500px; height: 500px; border-radius: 50%;
    background: radial-gradient(circle, rgba(255,107,53,0.18) 0%, transparent 70%);
    right: -100px; top: -100px; pointer-events: none;
}
.contact-hero::after {
    content: '';
    position: absolute; width: 300px; height: 300px; border-radius: 50%;
    background: radial-gradient(circle, rgba(255,190,11,0.12) 0%, transparent 70%);
    left: -60px; bottom: -60px; pointer-events: none;
}
.hero-badge-pill {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(255,190,11,0.15);
    border: 1px solid rgba(255,190,11,0.35);
    color: #FFBE0B; padding: 8px 20px; border-radius: 50px;
    font-size: 12px; font-weight: 700; letter-spacing: 0.5px;
    margin-bottom: 20px;
}
.hero-title { font-size: clamp(2.2rem,5vw,3.8rem); font-weight: 900; color: white; line-height: 1.1; }
.hero-title span { 
    background: linear-gradient(135deg, #FF6B35, #FFBE0B);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent; 
}
.hero-subtitle { color: rgba(255,255,255,0.72); font-size: 1.05rem; max-width: 480px; }
.hero-action-btn {
    background: linear-gradient(135deg, #FF6B35, #e65100);
    color: white; border: none; padding: 14px 32px;
    border-radius: 50px; font-weight: 700; font-size: 15px;
    display: inline-flex; align-items: center; gap: 8px;
    text-decoration: none; transition: all 0.25s ease;
    box-shadow: 0 10px 30px rgba(255,107,53,0.4);
}
.hero-action-btn:hover { transform: translateY(-3px) scale(1.03); color: white; box-shadow: 0 16px 40px rgba(255,107,53,0.5); }
.hero-img-circle {
    width: 300px; height: 300px; border-radius: 50%; overflow: hidden;
    border: 4px solid rgba(255,255,255,0.12);
    box-shadow: 0 30px 80px rgba(0,0,0,0.4);
    position: relative; z-index: 1;
}

/* ======= QUICK INFO CARDS ======= */
.quick-info-row { display: grid; grid-template-columns: repeat(4,1fr); gap: 16px; }
@media(max-width:991px){ .quick-info-row { grid-template-columns: repeat(2,1fr); } }
@media(max-width:575px){ .quick-info-row { grid-template-columns: 1fr 1fr; } }
.quick-info-card {
    background: white; border-radius: 20px; padding: 22px 18px; text-align: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.07);
    border: 2px solid transparent; transition: all 0.3s ease;
    text-decoration: none; display: block;
}
.quick-info-card:hover {
    border-color: var(--primary); transform: translateY(-6px);
    box-shadow: 0 16px 40px rgba(255,107,53,0.15);
}
body.dark-mode .quick-info-card { background: #1e1e30; }
.qic-icon {
    width: 56px; height: 56px; border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; margin: 0 auto 12px;
}
.qic-label { font-size: 11px; font-weight: 700; color: #999; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
.qic-value { font-size: 13px; font-weight: 700; color: #1a1a2e; }
body.dark-mode .qic-value { color: #f0f0f0; }

/* ======= FORM CARD ======= */
.form-card {
    background: white; border-radius: 28px; padding: 40px;
    box-shadow: 0 8px 40px rgba(0,0,0,0.08);
}
body.dark-mode .form-card { background: #1e1e30; }
.form-card .form-control,
.form-card .form-select {
    border: 2px solid #f0f0f0; border-radius: 14px; padding: 13px 16px;
    font-size: 14px; font-weight: 500; background: #fafafa;
    transition: all 0.2s ease; box-shadow: none !important;
}
.form-card .form-control:focus,
.form-card .form-select:focus {
    border-color: var(--primary);
    background: white;
    box-shadow: 0 0 0 4px rgba(255,107,53,0.08) !important;
}
.form-card label {
    font-size: 11px; font-weight: 700; color: #999;
    text-transform: uppercase; letter-spacing: 0.5px;
    margin-bottom: 6px; display: block;
}
.form-tab-btns { display: flex; gap: 8px; margin-bottom: 28px; }
.form-tab-btn {
    flex: 1; padding: 12px 16px; border-radius: 14px;
    border: 2px solid #f0f0f0; background: #fafafa;
    font-weight: 700; font-size: 13px; cursor: pointer; color: #666;
    transition: all 0.2s ease; text-align: center;
}
.form-tab-btn.active {
    border-color: var(--primary); background: rgba(255,107,53,0.07); color: var(--primary);
}
.form-submit-btn {
    background: linear-gradient(135deg, #FF6B35, #e65100);
    color: white; border: none; padding: 16px 32px; border-radius: 14px;
    font-weight: 700; font-size: 15px; width: 100%; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 10px;
    transition: all 0.25s ease; box-shadow: 0 8px 24px rgba(255,107,53,0.35);
}
.form-submit-btn:hover { transform: translateY(-2px); box-shadow: 0 14px 35px rgba(255,107,53,0.45); }

/* ======= LOCATION CARD (Premium) ======= */
.location-card {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 60%, #0f3460 100%);
    border-radius: 28px; overflow: hidden; padding: 0;
    box-shadow: 0 20px 60px rgba(15,15,26,0.4);
    position: relative;
}
.location-card::before {
    content: ''; position: absolute;
    width: 220px; height: 220px; border-radius: 50%;
    background: radial-gradient(circle, rgba(255,107,53,0.2) 0%, transparent 70%);
    top: -60px; right: -60px; pointer-events: none;
}
.location-card-header {
    padding: 30px 30px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    position: relative; z-index: 1;
}
.loc-title { font-size: 1.1rem; font-weight: 800; color: white; margin-bottom: 4px; }
.loc-subtitle { color: rgba(255,255,255,0.5); font-size: 12px; }
.location-info-rows { padding: 24px 30px; display: flex; flex-direction: column; gap: 20px; position: relative; z-index: 1; }
.loc-row { display: flex; align-items: flex-start; gap: 16px; }
.loc-icon-box {
    width: 46px; height: 46px; border-radius: 14px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
}
.loc-info-label { font-size: 10px; font-weight: 700; color: rgba(255,255,255,0.4); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; }
.loc-info-value { font-size: 13px; font-weight: 600; color: rgba(255,255,255,0.88); line-height: 1.4; }
.loc-info-sub { font-size: 11px; color: rgba(255,255,255,0.45); margin-top: 2px; }
.loc-action-row {
    padding: 20px 30px 30px; display: flex; gap: 10px; position: relative; z-index: 1;
}
.loc-btn {
    flex: 1; padding: 13px 16px; border-radius: 14px;
    font-weight: 700; font-size: 13px; text-align: center;
    text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px;
    transition: all 0.2s ease; cursor: pointer; border: none;
}
.loc-btn-primary { background: linear-gradient(135deg,#FF6B35,#e65100); color: white; box-shadow: 0 6px 20px rgba(255,107,53,0.4); }
.loc-btn-primary:hover { transform: translateY(-2px); color: white; box-shadow: 0 10px 28px rgba(255,107,53,0.5); }
.loc-btn-secondary { background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.8); border: 1px solid rgba(255,255,255,0.15); }
.loc-btn-secondary:hover { background: rgba(255,255,255,0.18); color: white; }

/* ======= MAP CARD ======= */
.map-card { background: white; border-radius: 24px; overflow: hidden; box-shadow: 0 8px 30px rgba(0,0,0,0.08); }
.map-header { padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f0f0f0; }
body.dark-mode .map-card { background: #1e1e30; }
body.dark-mode .map-header { border-color: #2a2a3e; }

/* ======= HOURS CARD ======= */
.hours-card { background: white; border-radius: 24px; padding: 28px; box-shadow: 0 4px 20px rgba(0,0,0,0.07); }
body.dark-mode .hours-card { background: #1e1e30; }
.hours-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f8f8f8; }
body.dark-mode .hours-row { border-color: #2a2a3e; }
.hours-row:last-child { border-bottom: none; }
.hours-day { font-size: 13px; font-weight: 600; color: #444; }
body.dark-mode .hours-day { color: #bbb; }
.hours-time { font-size: 13px; font-weight: 700; color: var(--primary); }
.hours-badge { font-size: 10px; font-weight: 700; padding: 3px 10px; border-radius: 20px; }

/* ======= SOCIAL CARD ======= */
.social-card { background: white; border-radius: 24px; padding: 28px; box-shadow: 0 4px 20px rgba(0,0,0,0.07); }
body.dark-mode .social-card { background: #1e1e30; }
.social-btn {
    display: flex; align-items: center; gap: 14px; padding: 14px 18px;
    border-radius: 14px; text-decoration: none; color: inherit;
    border: 2px solid #f0f0f0; transition: all 0.2s ease; margin-bottom: 10px;
    font-weight: 600; font-size: 14px;
}
.social-btn:hover { transform: translateX(6px); border-color: transparent; color: white; }
.social-btn.ig:hover  { background: linear-gradient(135deg,#e1306c,#833ab4); }
.social-btn.fb:hover  { background: #1877f2; }
.social-btn.yt:hover  { background: #ff0000; }
.social-btn.tw:hover  { background: #1da1f2; }
.social-btn.wa:hover  { background: #25D366; }
.social-btn-icon { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; color: white; flex-shrink: 0; }

/* ======= FAQ ======= */
.faq-item {
    background: white; border-radius: 16px; margin-bottom: 10px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.05); overflow: hidden;
    border: 1px solid #f0f0f0;
}
body.dark-mode .faq-item { background: #1e1e30; border-color: #2a2a3e; }
.faq-item .accordion-button {
    background: transparent; font-weight: 700; font-size: 14px; color: #1a1a2e;
    box-shadow: none !important; padding: 18px 20px;
}
body.dark-mode .faq-item .accordion-button { color: #f0f0f0; }
.faq-item .accordion-button::after { filter: none; }
.faq-item .accordion-body { color: #666; font-size: 13px; padding: 0 20px 18px; background: transparent; }
body.dark-mode .faq-item .accordion-body { color: #aaa; }

/* ======= STATS ======= */
.stats-banner {
    background: linear-gradient(135deg, #FF6B35 0%, #FFBE0B 100%);
    border-radius: 28px; padding: 48px 32px;
}
.stat-num { font-size: clamp(1.8rem,4vw,2.8rem); font-weight: 900; color: white; font-family:'Poppins',sans-serif; }
.stat-lbl { color: rgba(255,255,255,0.75); font-size: 13px; font-weight: 700; margin-top: 4px; }

/* ======= FLOATING BTNS ======= */
.float-actions { position: fixed; bottom: 30px; right: 30px; z-index: 1000; display: flex; flex-direction: column; gap: 10px; }
.float-btn { width: 52px; height: 52px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; border: none; font-size: 1.2rem; box-shadow: 0 8px 24px rgba(0,0,0,0.2); transition: all 0.2s ease; text-decoration: none; }
.float-btn:hover { transform: scale(1.12); color: white; }
</style>

<!-- ===== HERO ===== -->
<div class="container py-4">
    <div class="contact-hero p-4 p-md-5 mb-5" id="contact-hero">
        <div class="row align-items-center g-4 w-100 position-relative" style="z-index:1;">
            <div class="col-lg-7 hero-text">
                <div class="hero-badge-pill"><i class="fa-solid fa-headset"></i> We'd Love to Hear From You</div>
                <h1 class="hero-title mb-3">Get in <span>Touch</span><br>With Us</h1>
                <p class="hero-subtitle mb-4">Need help with an order? Want to book a table for a party? Planning corporate catering? We're here 24/7!</p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="#contact-grid" class="hero-action-btn"><i class="fa-solid fa-paper-plane"></i> Send Message</a>
                    <a href="tel:+919516440137" class="hero-action-btn" style="background:rgba(255,255,255,0.12); box-shadow:none; border:1px solid rgba(255,255,255,0.2);">
                        <i class="fa-solid fa-phone"></i> Call Now
                    </a>
                </div>
            </div>
            <div class="col-lg-5 text-center hero-img">
                <div class="hero-img-circle mx-auto">
                    <img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=600" class="w-100 h-100 object-cover" alt="Crispy Bytes">
                </div>
            </div>
        </div>
    </div>

    <!-- ===== QUICK INFO STRIP ===== -->
    <div class="quick-info-row mb-5 reveal-section">
        <a href="tel:+919516440137" class="quick-info-card">
            <div class="qic-icon" style="background:rgba(255,107,53,0.1);">📞</div>
            <div class="qic-label">Phone</div>
            <div class="qic-value"><?= htmlspecialchars($phoneVal) ?></div>
        </a>
        <a href="mailto:<?= htmlspecialchars($emailVal) ?>" class="quick-info-card">
            <div class="qic-icon" style="background:rgba(99,102,241,0.1);">✉️</div>
            <div class="qic-label">Email</div>
            <div class="qic-value"><?= htmlspecialchars($emailVal) ?></div>
        </a>
        <a href="https://maps.google.com/?q=Jaipur+Rajasthan" target="_blank" class="quick-info-card">
            <div class="qic-icon" style="background:rgba(34,197,94,0.1);">📍</div>
            <div class="qic-label">Location</div>
            <div class="qic-value">MI Road, Jaipur</div>
        </a>
        <div class="quick-info-card">
            <div class="qic-icon" style="background:rgba(245,158,11,0.1);">🕐</div>
            <div class="qic-label">Open Today</div>
            <div class="qic-value" style="color:#22c55e;">11 AM – 11 PM</div>
        </div>
    </div>

    <!-- ===== MAIN GRID ===== -->
    <div class="row g-5 mb-5" id="contact-grid">

        <!-- LEFT: Form -->
        <div class="col-lg-7">
            <div class="form-card mb-4 reveal-section">
                <h4 class="fw-bold mb-1">🍽️ Book a Table & Message Us</h4>
                <p class="text-muted small mb-4">Reserve a table or send us a query — we'll respond within 10 minutes!</p>

                <!-- Tabs -->
                <div class="form-tab-btns">
                    <div class="form-tab-btn active" onclick="switchTab(this,'msg-fields')">💬 Send Message</div>
                    <div class="form-tab-btn" onclick="switchTab(this,'res-fields')">🍽️ Book a Table</div>
                </div>

                <?php if ($successMsg): ?>
                    <div class="alert rounded-4 border-0 py-3 mb-4 fw-semibold" style="background:rgba(34,197,94,0.12); color:#15803d;">
                        <i class="fa-solid fa-circle-check me-2"></i><?= $successMsg ?>
                    </div>
                <?php endif; ?>
                <?php if ($errorMsg): ?>
                    <div class="alert rounded-4 border-0 py-3 mb-4 fw-semibold" style="background:rgba(239,68,68,0.1); color:#dc2626;">
                        <i class="fa-solid fa-circle-xmark me-2"></i><?= $errorMsg ?>
                    </div>
                <?php endif; ?>

                <form action="contact.php" method="POST" id="contactForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label>Full Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Sonu Singh" required>
                        </div>
                        <div class="col-md-6">
                            <label>Email Address *</label>
                            <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
                        </div>
                        <div class="col-md-6">
                            <label>Phone Number</label>
                            <input type="tel" name="phone" class="form-control" placeholder="+91 98765 43210">
                        </div>
                        <div class="col-md-6">
                            <label>Subject</label>
                            <select name="subject" class="form-select">
                                <option>General Enquiry</option>
                                <option>Order Issue</option>
                                <option>Table Booking</option>
                                <option>Corporate Catering</option>
                                <option>Feedback</option>
                                <option>Complaint</option>
                            </select>
                        </div>

                        <!-- Reservation Fields (hidden by default) -->
                        <div id="res-fields" style="display:none; width:100%;">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label>Booking Date</label>
                                    <input type="date" name="booking_date" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label>Booking Time</label>
                                    <input type="time" name="booking_time" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label>No. of Guests</label>
                                    <input type="number" name="guests" min="1" max="20" class="form-control" placeholder="2">
                                </div>
                                <div class="col-12">
                                    <label>Occasion</label>
                                    <select name="occasion" class="form-select">
                                        <option value="">Select Occasion (Optional)</option>
                                        <option>Birthday Party</option>
                                        <option>Anniversary</option>
                                        <option>Corporate Meeting</option>
                                        <option>Family Dinner</option>
                                        <option>Date Night</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label>Message / Special Instructions *</label>
                            <textarea name="message" rows="4" class="form-control" required placeholder="Write your message, table preferences, or any special requests..."></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="form-submit-btn">
                                <i class="fa-solid fa-paper-plane"></i> Send Message
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- FAQ -->
            <div class="reveal-section">
                <h5 class="fw-bold mb-3">💬 Frequently Asked Questions</h5>
                <div class="accordion" id="faqAcc">
                    <?php
                    $faqs = [
                        ['q'=>'How long does home delivery take?','a'=>'Jaipur local deliveries take around 20–30 minutes maximum to maintain optimal flavor and temperature.'],
                        ['q'=>'Do you accept online wallet payments?','a'=>'Yes! We accept Google Pay, PhonePe, Paytm, UPI, credit/debit cards, and net banking at checkout.'],
                        ['q'=>'Can I book for corporate bulk catering?','a'=>'Absolutely. Select "Corporate Catering" as the subject and include your requirements. We\'ll call you within 30 minutes.'],
                        ['q'=>'Is there a minimum order for free delivery?','a'=>'Yes — orders above ₹299 get free delivery within Jaipur city limits. Below that, a small ₹30 delivery fee applies.'],
                        ['q'=>'Can I modify or cancel my order?','a'=>'Orders can be modified or cancelled within 5 minutes of placing. After that, please call us directly at +91 95164 40137.'],
                    ];
                    foreach ($faqs as $i => $faq): ?>
                    <div class="faq-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= $i ?>">
                                <?= $faq['q'] ?>
                            </button>
                        </h2>
                        <div id="faq<?= $i ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>" data-bs-parent="#faqAcc">
                            <div class="accordion-body"><?= $faq['a'] ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- RIGHT: Location Card, Map, Hours, Socials -->
        <div class="col-lg-5">

            <!-- ===== PREMIUM LOCATION CARD ===== -->
            <div class="location-card mb-4 reveal-section">
                <div class="location-card-header">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:44px;height:44px;border-radius:14px;background:rgba(255,107,53,0.2);display:flex;align-items:center;justify-content:center;font-size:1.2rem;">📍</div>
                        <div>
                            <div class="loc-title">Jaipur Main Outlet</div>
                            <div class="loc-subtitle">Open today · 11:00 AM – 11:00 PM</div>
                        </div>
                        <span class="badge ms-auto rounded-pill" style="background:rgba(34,197,94,0.2);color:#4ade80;font-size:10px;font-weight:700;padding:5px 12px;">● OPEN</span>
                    </div>
                </div>

                <div class="location-info-rows">
                    <div class="loc-row">
                        <div class="loc-icon-box" style="background:rgba(255,107,53,0.15);">🏠</div>
                        <div>
                            <div class="loc-info-label">Address</div>
                            <div class="loc-info-value"><?= htmlspecialchars($addressVal) ?></div>
                            <div class="loc-info-sub">Landmark: Near MI Road Junction</div>
                        </div>
                    </div>
                    <div class="loc-row">
                        <div class="loc-icon-box" style="background:rgba(99,102,241,0.15);">📞</div>
                        <div>
                            <div class="loc-info-label">Phone</div>
                            <div class="loc-info-value"><?= htmlspecialchars($phoneVal) ?></div>
                            <div class="loc-info-sub">Available 11 AM – 11 PM daily</div>
                        </div>
                    </div>
                    <div class="loc-row">
                        <div class="loc-icon-box" style="background:rgba(245,158,11,0.15);">✉️</div>
                        <div>
                            <div class="loc-info-label">Email Support</div>
                            <div class="loc-info-value"><?= htmlspecialchars($emailVal) ?></div>
                            <div class="loc-info-sub">We reply within 10 minutes</div>
                        </div>
                    </div>
                    <div class="loc-row">
                        <div class="loc-icon-box" style="background:rgba(34,197,94,0.15);">⭐</div>
                        <div>
                            <div class="loc-info-label">Rating & Response</div>
                            <div class="loc-info-value">4.9/5 Google Rating</div>
                            <div class="loc-info-sub">Avg. response: Under 10 minutes</div>
                        </div>
                    </div>
                </div>

                <div class="loc-action-row">
                    <a href="https://maps.google.com/?q=MI+Road+Jaipur+Rajasthan" target="_blank" class="loc-btn loc-btn-primary">
                        <i class="fa-solid fa-map-location-dot"></i> Get Directions
                    </a>
                    <button class="loc-btn loc-btn-secondary"
                        onclick="navigator.clipboard.writeText('<?= htmlspecialchars($addressVal) ?>'); this.innerHTML='<i class=\'fa-solid fa-check\'></i> Copied!';">
                        <i class="fa-regular fa-copy"></i> Copy
                    </button>
                </div>
            </div>

            <!-- MAP -->
            <div class="map-card mb-4 reveal-section">
                <div class="map-header">
                    <span class="fw-bold text-sm"><i class="fa-solid fa-map-location-dot text-warning me-2"></i>Live Location Map</span>
                    <a href="https://maps.google.com/?q=MI+Road+Jaipur" target="_blank" class="text-xs fw-bold" style="color:var(--primary);">Open in Maps →</a>
                </div>
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d14234.629051871217!2d75.7872709078125!3d26.907960300000004!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x396db61232bca5c3%3A0x6e9f2913e4b772e3!2sJaipur%2C%20Rajasthan%2C%20India!5e0!3m2!1sen!2sin!4v1700000000000!5m2!1sen!2sin"
                        width="100%" height="220" style="border:0; display:block;" allowfullscreen="" loading="lazy"></iframe>
            </div>

            <!-- HOURS -->
            <div class="hours-card mb-4 reveal-section">
                <h6 class="fw-bold mb-3"><i class="fa-regular fa-clock text-warning me-2"></i>Opening Hours</h6>
                <?php
                $hours = [
                    ['day'=>'Monday – Thursday', 'time'=>'11:00 AM – 10:30 PM', 'status'=>'open'],
                    ['day'=>'Friday',             'time'=>'11:00 AM – 11:30 PM', 'status'=>'open'],
                    ['day'=>'Saturday',           'time'=>'10:00 AM – 11:30 PM', 'status'=>'open'],
                    ['day'=>'Sunday',             'time'=>'10:00 AM – 11:00 PM', 'status'=>'open'],
                    ['day'=>'Public Holidays',    'time'=>'12:00 PM – 10:00 PM', 'status'=>'limited'],
                ];
                $today = date('N'); // 1=Mon, 7=Sun
                foreach ($hours as $i => $h): ?>
                <div class="hours-row">
                    <span class="hours-day"><?= $h['day'] ?></span>
                    <div class="d-flex align-items-center gap-2">
                        <span class="hours-time"><?= $h['time'] ?></span>
                        <?php if ($h['status']==='limited'): ?>
                        <span class="hours-badge" style="background:rgba(245,158,11,0.15); color:#d97706;">Limited</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- SOCIAL -->
            <div class="social-card reveal-section">
                <h6 class="fw-bold mb-3">📱 Follow & Connect With Us</h6>
                <a href="#" class="social-btn ig">
                    <div class="social-btn-icon" style="background:linear-gradient(135deg,#e1306c,#833ab4);">
                        <i class="fa-brands fa-instagram"></i>
                    </div>
                    <div>
                        <div style="font-size:13px; font-weight:700; color:#1a1a2e;">Instagram</div>
                        <div style="font-size:11px; color:#999;">@crispybytes.jaipur</div>
                    </div>
                    <i class="fa-solid fa-arrow-right ms-auto" style="color:#ddd; font-size:12px;"></i>
                </a>
                <a href="#" class="social-btn fb">
                    <div class="social-btn-icon" style="background:#1877f2;">
                        <i class="fa-brands fa-facebook-f"></i>
                    </div>
                    <div>
                        <div style="font-size:13px; font-weight:700; color:#1a1a2e;">Facebook</div>
                        <div style="font-size:11px; color:#999;">@CrispyBytes</div>
                    </div>
                    <i class="fa-solid fa-arrow-right ms-auto" style="color:#ddd; font-size:12px;"></i>
                </a>
                <a href="https://wa.me/919516440137" target="_blank" class="social-btn wa">
                    <div class="social-btn-icon" style="background:#25D366;">
                        <i class="fa-brands fa-whatsapp"></i>
                    </div>
                    <div>
                        <div style="font-size:13px; font-weight:700; color:#1a1a2e;">WhatsApp Order</div>
                        <div style="font-size:11px; color:#999;">+91 95164 40137</div>
                    </div>
                    <i class="fa-solid fa-arrow-right ms-auto" style="color:#ddd; font-size:12px;"></i>
                </a>
                <a href="#" class="social-btn yt">
                    <div class="social-btn-icon" style="background:#ff0000;">
                        <i class="fa-brands fa-youtube"></i>
                    </div>
                    <div>
                        <div style="font-size:13px; font-weight:700; color:#1a1a2e;">YouTube</div>
                        <div style="font-size:11px; color:#999;">Crispy Bytes Kitchen</div>
                    </div>
                    <i class="fa-solid fa-arrow-right ms-auto" style="color:#ddd; font-size:12px;"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- ===== STATS BANNER ===== -->
    <div class="stats-banner mb-5 reveal-section">
        <div class="row g-4 text-center">
            <div class="col-6 col-md-3">
                <div class="stat-num" id="s1">0</div>
                <div class="stat-lbl">Happy Customers</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-num" id="s2">0</div>
                <div class="stat-lbl">Orders Delivered</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-num" id="s3">0</div>
                <div class="stat-lbl">Branches</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-num">4.9★</div>
                <div class="stat-lbl">Average Rating</div>
            </div>
        </div>
    </div>

    <!-- ===== CTA ===== -->
    <div class="card border-0 p-4 p-md-5 text-white mb-4 reveal-section"
         style="border-radius:28px; background:linear-gradient(135deg,#1a1a2e 0%, #FF6B35 120%);">
        <div class="row align-items-center g-4">
            <div class="col-md-8">
                <h3 class="fw-extrabold mb-2">Hungry? Order Your Favourite Food 🍔</h3>
                <p class="mb-4" style="color:rgba(255,255,255,0.75);">Get delicious burgers, pizzas, Indian street food — delivered hot to your doorstep in 30 minutes.</p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="menu.php" class="hero-action-btn">
                        <i class="fa-solid fa-utensils"></i> Order Now
                    </a>
                    <a href="tel:+919516440137" class="hero-action-btn" style="background:rgba(255,255,255,0.15); box-shadow:none;">
                        <i class="fa-solid fa-phone"></i> Call Order
                    </a>
                </div>
            </div>
            <div class="col-md-4 text-center d-none d-md-block">
                <img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=350" class="img-fluid rounded-4" style="box-shadow:0 20px 60px rgba(0,0,0,0.3);" alt="Order Now">
            </div>
        </div>
    </div>

</div>

<!-- Floating Buttons -->
<div class="float-actions">
    <a href="https://wa.me/919516440137" target="_blank" class="float-btn" style="background:#25D366;" title="WhatsApp">
        <i class="fa-brands fa-whatsapp"></i>
    </a>
    <button class="float-btn" style="background:var(--primary);" onclick="alert('Live chat coming soon!')" title="Chat">
        <i class="fa-regular fa-comments"></i>
    </button>
</div>

<script>
// Tab switcher for form
function switchTab(el, showId) {
    document.querySelectorAll('.form-tab-btn').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('res-fields').style.display = (showId === 'res-fields') ? 'block' : 'none';
}

// Counter animation
function animCounter(id, target, suffix, speed) {
    const el = document.getElementById(id);
    if (!el) return;
    let cur = 0;
    const step = Math.ceil(target / 60);
    const t = setInterval(() => {
        cur = Math.min(cur + step, target);
        el.innerText = cur.toLocaleString() + suffix;
        if (cur >= target) clearInterval(t);
    }, speed || 20);
}

document.addEventListener('DOMContentLoaded', function() {
    ScrollReveal().reveal('.hero-text',      { delay:100, origin:'left',   distance:'40px' });
    ScrollReveal().reveal('.hero-img',       { delay:200, origin:'right',  distance:'40px' });
    ScrollReveal().reveal('.reveal-section', { delay:150, origin:'bottom', distance:'35px', interval:80, reset:false });

    const ob = new IntersectionObserver(entries => {
        if (entries[0].isIntersecting) {
            animCounter('s1', 50000, '+', 15);
            animCounter('s2', 200000, '+', 8);
            animCounter('s3', 20, '+', 60);
            ob.disconnect();
        }
    });
    const el = document.getElementById('s1');
    if (el) ob.observe(el.closest('.stats-banner'));
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
