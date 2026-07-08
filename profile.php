<?php
// profile.php
require_once __DIR__ . '/includes/header.php';
checkLoginRedirect();

$userId = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle Profile Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name    = sanitizeInput($_POST['name'] ?? '');
    $phone   = sanitizeInput($_POST['phone'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');

    if ($name) {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $address, $userId]);
        $success = 'Profile updated successfully!';
    } else {
        $error = 'Name is required.';
    }
}

$user = getLoggedInUser();

// Fetch past orders
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();
?>

<link rel="stylesheet" href="assets/css/menu-premium.css">

<style>
.custom-input-premium {
    border: 1px solid rgba(197, 168, 128, 0.2) !important;
    font-size: 13px !important;
    font-weight: 500 !important;
    background: #fafaf8 !important;
}
.custom-input-premium:focus {
    border-color: var(--premium-gold) !important;
    box-shadow: 0 0 0 3px rgba(197, 168, 128, 0.15) !important;
}
</style>

<div class="container py-5">
    <div class="mb-5">
        <span class="text-uppercase fw-bold text-xs" style="color: var(--premium-gold); letter-spacing: 1.5px;">Gourmet Member</span>
        <h1 class="fw-extrabold tracking-tight text-dark mb-2" style="font-family: 'Poppins', sans-serif;">My Profile</h1>
        <div style="width: 50px; height: 3px; background: var(--premium-gold); border-radius: 2px;"></div>
    </div>

    <div class="row g-4">
        <!-- Patron Profile Card (Left) -->
        <div class="col-lg-4">
            <div class="card border-0 filter-sidebar-glass p-4 text-center sticky-top" style="top: 110px;">
                <!-- Avatar -->
                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 fw-extrabold" style="width: 72px; height: 72px; font-size: 1.6rem; background: linear-gradient(135deg, #121212, #2a2a2a); color: var(--premium-gold); border: 2px solid var(--premium-gold); font-family: 'Poppins', sans-serif; box-shadow: 0 8px 24px rgba(0,0,0,0.15);">
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                </div>
                <h5 class="fw-bold text-dark mb-0" style="font-family: 'Poppins', sans-serif;"><?= htmlspecialchars($user['name']) ?></h5>
                <div style="width: 30px; height: 2px; background: var(--premium-gold); border-radius: 1px; margin: 6px auto 4px;"></div>
                <p class="text-muted mb-3" style="font-size: 12px;"><i class="fa-solid fa-phone me-1"></i> <?= htmlspecialchars($user['phone'] ?? 'No Phone') ?></p>

                <!-- Default address display -->
                <div class="p-3 rounded-4 text-start mb-4" style="background: #fafaf8; border: 1px solid rgba(197,168,128,0.15); font-size: 12px;">
                    <strong class="text-dark d-block mb-1" style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--premium-gold) !important;">Default Vault Destination</strong>
                    <span class="text-muted"><?= htmlspecialchars($user['address'] ?? 'No default address saved.') ?></span>
                </div>

                <!-- Divider -->
                <div class="border-top pt-4 mb-3 text-start" style="border-color: rgba(197,168,128,0.15) !important;">
                    <h6 class="fw-bold text-dark mb-3" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.8px;">Update Details</h6>

                    <?php if ($success): ?>
                        <div class="alert alert-success border-0 rounded-4 py-2 mb-3" style="font-size: 12px;"><i class="fa-solid fa-circle-check me-2"></i><?= $success ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger border-0 rounded-4 py-2 mb-3" style="font-size: 12px;"><i class="fa-solid fa-circle-xmark me-2"></i><?= $error ?></div>
                    <?php endif; ?>

                    <form action="profile.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label text-xs fw-bold text-muted text-uppercase" style="letter-spacing: 0.4px;">Full Name</label>
                            <input type="text" name="name" class="form-control rounded-pill p-3 shadow-none custom-input-premium" value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-xs fw-bold text-muted text-uppercase" style="letter-spacing: 0.4px;">Phone Number</label>
                            <input type="text" name="phone" class="form-control rounded-pill p-3 shadow-none custom-input-premium" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-xs fw-bold text-muted text-uppercase" style="letter-spacing: 0.4px;">Default Address</label>
                            <textarea name="address" class="form-control rounded-4 p-3 shadow-none custom-input-premium" rows="3"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-gradient-yellow rounded-pill w-100 py-2 fw-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Orders History Timeline (Right) -->
        <div class="col-lg-8" id="orders">
            <div class="card border-0 filter-sidebar-glass p-4">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h5 class="fw-bold text-dark mb-0" style="font-family: 'Poppins', sans-serif;">
                        <i class="fa-solid fa-clock-rotate-left me-2" style="color: var(--premium-gold);"></i> Your Culinary History
                    </h5>
                    <span class="badge rounded-pill fw-bold" style="background: rgba(197,168,128,0.12); color: var(--premium-gold); font-size: 10px; padding: 6px 14px;"><?= count($orders) ?> Orders</span>
                </div>

                <?php if (!empty($orders)): ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($orders as $o): ?>
                            <?php
                                $status = $o['status'];
                                $dotColor = '#C5A880';
                                if ($status === 'pending')   $dotColor = '#f59e0b';
                                if ($status === 'preparing') $dotColor = '#121212';
                                if ($status === 'cancelled') $dotColor = '#ef4444';
                            ?>
                            <div class="p-3 food-card d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center position-relative" style="border-left: 3px solid <?= $dotColor ?>; padding-left: 20px !important;">
                                <!-- Status dot indicator -->
                                <div style="position: absolute; left: -7px; top: 18px; width: 12px; height: 12px; border-radius: 50%; background: <?= $dotColor ?>; border: 2px solid white; box-shadow: 0 0 0 2px <?= $dotColor ?>44;"></div>

                                <div>
                                    <span class="text-uppercase fw-bold" style="font-size: 9px; color: var(--premium-gold); letter-spacing: 1px;">Token: #<?= htmlspecialchars($o['order_no']) ?></span>
                                    <h6 class="fw-bold text-dark my-1" style="font-family: 'Poppins', sans-serif; font-size: 13px;"><?= date('d M Y, h:i A', strtotime($o['created_at'])) ?></h6>
                                    <div class="d-flex gap-2 align-items-center mt-1 flex-wrap">
                                        <span class="badge rounded-pill text-uppercase" style="background: #121212; color: var(--premium-gold); font-size: 9px; padding: 4px 10px; letter-spacing: 0.4px;"><?= htmlspecialchars($o['order_type']) ?></span>
                                        <span class="badge rounded-pill text-uppercase" style="background: rgba(<?= $status === 'completed' ? '197,168,128' : ($status === 'cancelled' ? '239,68,68' : ($status === 'preparing' ? '18,18,18' : '245,158,11')) ?>,0.12); color: <?= $dotColor ?>; font-size: 9px; padding: 4px 10px;"><?= $status ?></span>
                                        <span class="fw-bold" style="font-size: 13px; color: #b08d5b; font-family: 'Poppins', sans-serif;"><?= $currency ?><?= number_format($o['grand_total'], 2) ?></span>
                                    </div>
                                </div>

                                <div class="d-flex gap-2 mt-3 mt-sm-0 flex-shrink-0">
                                    <a href="invoice.php?order_no=<?= urlencode($o['order_no']) ?>" class="btn btn-outline-dark rounded-pill fw-bold text-uppercase" style="font-size: 10px; padding: 6px 14px; border-color: rgba(0,0,0,0.15);">
                                        <i class="fa-solid fa-file-invoice me-1"></i> Track / Receipt
                                    </a>
                                    <button onclick="oneClickReorder(<?= (int)$o['id'] ?>)" class="btn btn-gradient-yellow rounded-pill fw-bold text-uppercase" style="font-size: 10px; padding: 6px 14px;">
                                        <i class="fa-solid fa-rotate-left me-1"></i> Reorder
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="mb-3" style="font-size: 3.5rem; filter: grayscale(0.3);">🍽️</div>
                        <h5 class="fw-bold text-dark mb-2" style="font-family: 'Poppins', sans-serif;">No Orders Yet</h5>
                        <p class="text-muted mb-4" style="font-size: 13px;">No culinary orders found in your timeline ledger.</p>
                        <a href="menu.php" class="btn btn-gradient-yellow rounded-pill px-5 py-2 fw-bold text-uppercase" style="font-size: 11px;">Discover Menu</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// 1-Click Reorder via cart_action.php → reorder_paste action
function oneClickReorder(orderId) {
    Swal.fire({
        title: '🔄 Reorder this selection?',
        text: 'This will add all items from this past transaction into your active basket.',
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#121212',
        cancelButtonColor: '#e5e5e5',
        confirmButtonText: 'Yes, Reorder',
        customClass: { popup: 'rounded-4 border-0' }
    }).then((result) => {
        if (result.isConfirmed) {
            // Show premium loading state
            Swal.fire({
                title: '⚜️ Assembling Basket...',
                html: `<div class="text-center py-2">
                    <div class="spinner-grow mb-3" style="color:#C5A880;" role="status"></div>
                    <p class="text-xs text-muted mb-0">Fetching your previous selection from our records...</p>
                </div>`,
                showConfirmButton: false,
                allowOutsideClick: false,
                timer: 1600
            });

            const formData = new FormData();
            formData.append('action', 'reorder_paste');
            formData.append('order_id', orderId);

            fetch('api/cart_action.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: '⚜️ Reorder Confirmed',
                        text: data.added + ' item(s) added to your Gourmet Basket.',
                        confirmButtonColor: '#121212',
                        confirmButtonText: 'Go to Basket',
                        customClass: { popup: 'rounded-4 border-0' }
                    }).then(() => window.location.href = 'cart.php');
                } else {
                    Swal.fire('Notice', data.message || 'Failed to reorder items.', 'error');
                }
            })
            .catch(() => Swal.fire('Error', 'Connection failed. Please try again.', 'error'));
        }
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
