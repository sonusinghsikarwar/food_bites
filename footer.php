<?php
// includes/footer.php
$restaurant_name = getSetting('restaurant_name', 'Crispy Bytes');
$email = getSetting('contact_email', 'info@crispybytes.com');
$phone = getSetting('contact_phone', '+91 98765 43210');
$address = getSetting('address', '101, Food Court, Indore, India');
$hours = getSetting('opening_hours', 'Daily 11:00 AM - 11:00 PM');
?>
    <!-- Footer Section -->
    <footer class="bg-dark text-white pt-5 pb-3 mt-5">
        <div class="container">
            <div class="row g-4 mb-4">
                <div class="col-lg-4 col-md-6">
                    <h5 class="text-warning fw-bold mb-3"><i class="fa-solid fa-burger me-2"></i><?= htmlspecialchars($restaurant_name) ?></h5>
                    <p class="text-secondary">Serving premium-quality, sizzling burgers, stone-oven baked pizzas, and customized treats crafted with fresh local ingredients.</p>
                    <div class="d-flex gap-3 mt-3">
                        <a href="#" class="btn btn-outline-light rounded-circle btn-sm d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" class="btn btn-outline-light rounded-circle btn-sm d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="btn btn-outline-light rounded-circle btn-sm d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;"><i class="fa-brands fa-twitter"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="fw-bold mb-3">Quick Links</h6>
                    <ul class="list-unstyled d-flex flex-column gap-2 text-secondary">
                        <li><a href="menu.php" class="text-decoration-none text-secondary hover-text-white">Our Menu</a></li>
                        <li><a href="about.php" class="text-decoration-none text-secondary hover-text-white">Our Story</a></li>
                        <li><a href="gallery.php" class="text-decoration-none text-secondary hover-text-white">Gallery</a></li>
                        <li><a href="offers.php" class="text-decoration-none text-secondary hover-text-white">Offers</a></li>
                        <li><a href="admin/" class="text-decoration-none text-secondary hover-text-white"><i class="fa-solid fa-lock text-warning me-1"></i>Admin Panel</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h6 class="fw-bold mb-3">Opening Hours</h6>
                    <p class="text-secondary mb-1"><i class="fa-regular fa-clock text-warning me-2"></i><?= htmlspecialchars($hours) ?></p>
                    <p class="text-secondary"><i class="fa-solid fa-truck text-warning me-2"></i>Home Delivery Available</p>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h6 class="fw-bold mb-3">Contact Us</h6>
                    <ul class="list-unstyled d-flex flex-column gap-2 text-secondary">
                        <li><i class="fa-solid fa-location-dot text-warning me-2"></i><?= htmlspecialchars($address) ?></li>
                        <li><i class="fa-solid fa-phone text-warning me-2"></i><?= htmlspecialchars($phone) ?></li>
                        <li><i class="fa-solid fa-envelope text-warning me-2"></i><?= htmlspecialchars($email) ?></li>
                    </ul>
                </div>
            </div>
            <hr class="border-secondary">
            <div class="text-center text-secondary text-sm pt-2">
                &copy; <?= date('Y') ?> <?= htmlspecialchars($restaurant_name) ?>. All Rights Reserved. Designed for premium food experiences.
            </div>
        </div>
    </footer>

    <!-- Toast Notification Container -->
    <div id="toast-container"></div>

    <!-- Bootstrap & jQuery & SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.all.min.js"></script>
    <!-- Custom Main JS -->
    <script src="assets/js/main.js"></script>
</body>
</html>
