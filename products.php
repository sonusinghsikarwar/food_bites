<?php
// admin/products.php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$success = '';
$error = '';

// Handle Product Add/Edit Form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = intval($_POST['id'] ?? 0);
    $category_id = intval($_POST['category_id'] ?? 0);
    $name = sanitizeInput($_POST['name'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0.00);
    $discount_price = floatval($_POST['discount_price'] ?? 0.00);
    $stock_qty = intval($_POST['stock_qty'] ?? 0);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $status = sanitizeInput($_POST['status'] ?? 'active');

    // Handle Image Upload
    $imageName = $_POST['existing_image'] ?? '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            // Create uploads dir if not exists
            $uploadFileDir = __DIR__ . '/../assets/images/';
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            $dest_path = $uploadFileDir . $newFileName;
            
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $imageName = $newFileName;
            } else {
                $error = 'Error moving uploaded file.';
            }
        } else {
            $error = 'Upload failed. Allowed formats: JPG, JPEG, PNG, WEBP.';
        }
    }

    if ($name && $category_id && $price && !$error) {
        if ($action === 'add') {
            try {
                $stmt = $pdo->prepare("INSERT INTO products (category_id, name, description, price, discount_price, stock_qty, is_featured, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$category_id, $name, $description, $price, $discount_price, $stock_qty, $is_featured, $imageName, $status]);
                $success = 'Product added successfully!';
            } catch (Exception $e) {
                $error = 'Database error adding product: ' . $e->getMessage();
            }
        } elseif ($action === 'edit' && $id) {
            try {
                $stmt = $pdo->prepare("UPDATE products SET category_id = ?, name = ?, description = ?, price = ?, discount_price = ?, stock_qty = ?, is_featured = ?, image = ?, status = ? WHERE id = ?");
                $stmt->execute([$category_id, $name, $description, $price, $discount_price, $stock_qty, $is_featured, $imageName, $status, $id]);
                $success = 'Product updated successfully!';
            } catch (Exception $e) {
                $error = 'Database error updating product: ' . $e->getMessage();
            }
        }
    } else {
        if (!$error) $error = 'Please fill out all required fields.';
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $success = 'Product deleted successfully!';
    }
}

// Fetch categories for dropdown
$categories = $pdo->query("SELECT * FROM categories WHERE status = 'active'")->fetchAll();

// Fetch products list
$products = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC")->fetchAll();
?>

<div class="row g-4">
    <!-- List Columns -->
    <div class="col-lg-8 order-2 order-lg-1">
        <div class="glass-card p-4">
            <h5 class="fw-bold text-dark dark-text-white mb-4">Products List</h5>
            <div class="table-responsive">
                <table class="table align-middle border-light mb-0">
                    <thead>
                        <tr class="text-secondary text-xs uppercase tracking-wider">
                            <th>Product</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $p): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="<?= (strpos($p['image'], 'http') === 0) ? htmlspecialchars($p['image']) : '../assets/images/' . htmlspecialchars($p['image'] ?? '') ?>" onerror="this.src='https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=100'" class="rounded-3 object-cover" style="width: 50px; height: 50px;" alt="">
                                            <div>
                                                <h6 class="fw-bold text-dark dark-text-white mb-0"><?= htmlspecialchars($p['name']) ?></h6>
                                                <?php if ($p['is_featured']): ?>
                                                    <span class="badge bg-warning text-dark text-[10px] uppercase">Featured</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($p['category_name']) ?></td>
                                    <td>
                                        <?php if ($p['discount_price'] > 0): ?>
                                            <span class="fw-bold text-warning"><?= $currency ?><?= number_format($p['discount_price'], 2) ?></span>
                                            <span class="text-decoration-line-through text-muted small ms-2"><?= $currency ?><?= number_format($p['price'], 2) ?></span>
                                        <?php else: ?>
                                            <span class="fw-bold"><?= $currency ?><?= number_format($p['price'], 2) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $p['stock_qty'] < 15 ? 'bg-danger' : 'bg-outline-secondary' ?>">
                                            <?= $p['stock_qty'] ?> units
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $p['status'] === 'active' ? 'bg-success' : 'bg-danger' ?> text-xs text-uppercase">
                                            <?= $p['status'] ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button onclick="editProduct(<?= htmlspecialchars(json_encode($p)) ?>)" class="btn btn-outline-warning rounded-pill btn-sm px-3 fw-bold"><i class="fa-solid fa-pen-to-square"></i> Edit</button>
                                            <a href="products.php?delete=<?= $p['id'] ?>" onclick="return confirm('Delete this product?')" class="btn btn-outline-danger rounded-pill btn-sm px-3 fw-bold"><i class="fa-solid fa-trash-can"></i> Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No products available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Form Column -->
    <div class="col-lg-4 order-1 order-lg-2">
        <div class="glass-card p-4">
            <h5 class="fw-bold text-dark dark-text-white mb-4" id="form-title">Add New Product</h5>
            
            <?php if ($success): ?>
                <div class="alert alert-success border-0 rounded-4 py-2 text-xs mb-3"><i class="fa-solid fa-circle-check me-2"></i><?= $success ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger border-0 rounded-4 py-2 text-xs mb-3"><i class="fa-solid fa-circle-xmark me-2"></i><?= $error ?></div>
            <?php endif; ?>

            <form action="products.php" method="POST" enctype="multipart/form-data" id="productForm">
                <input type="hidden" name="action" id="form-action" value="add">
                <input type="hidden" name="id" id="product-id" value="">
                <input type="hidden" name="existing_image" id="existing-image" value="">

                <div class="mb-3">
                    <label class="form-label text-xs fw-bold text-uppercase text-muted">Category *</label>
                    <select name="category_id" id="product-category" class="form-select rounded-pill border-light p-3 shadow-none" style="padding-top: 15px; padding-bottom: 15px;" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label text-xs fw-bold text-uppercase text-muted">Product Name *</label>
                    <input type="text" name="name" id="product-name" class="form-control rounded-pill border-light p-3 shadow-none" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-xs fw-bold text-uppercase text-muted">Description</label>
                    <textarea name="description" id="product-description" class="form-control rounded-4 border-light p-3 shadow-none" rows="3"></textarea>
                </div>

                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label text-xs fw-bold text-uppercase text-muted">Price (₹) *</label>
                        <input type="number" step="0.01" name="price" id="product-price" class="form-control rounded-pill border-light p-3 shadow-none" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-xs fw-bold text-uppercase text-muted">Discount Price (₹)</label>
                        <input type="number" step="0.01" name="discount_price" id="product-discount" class="form-control rounded-pill border-light p-3 shadow-none" value="0.00">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label text-xs fw-bold text-uppercase text-muted">Stock Quantity *</label>
                        <input type="number" name="stock_qty" id="product-stock" class="form-control rounded-pill border-light p-3 shadow-none" value="100" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-xs fw-bold text-uppercase text-muted">Status</label>
                        <select name="status" id="product-status" class="form-select rounded-pill border-light p-3 shadow-none" style="padding-top: 15px; padding-bottom: 15px;">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-xs fw-bold text-uppercase text-muted">Product Image</label>
                    <input type="file" name="image" class="form-control rounded-4 border-light p-2.5 shadow-none">
                    <div id="image-preview-box" class="mt-2 text-center hidden">
                        <img id="product-img-prev" src="" class="rounded-3 shadow-sm" style="max-height: 80px;">
                    </div>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="is_featured" id="product-featured" class="form-check-input">
                    <label class="form-check-label text-xs fw-bold text-uppercase text-muted" for="product-featured">Featured Product</label>
                </div>

                <button type="submit" class="btn btn-warning rounded-pill w-100 py-3 fw-bold mt-2" id="submit-btn">Add Product</button>
                <button type="button" class="btn btn-outline-secondary rounded-pill w-100 py-2 fw-semibold mt-2 hidden" id="cancel-btn" onclick="resetProductForm()">Cancel Edit</button>
            </form>
        </div>
    </div>
</div>

<script>
function editProduct(product) {
    document.getElementById('form-title').innerText = 'Edit Product';
    document.getElementById('form-action').value = 'edit';
    document.getElementById('product-id').value = product.id;
    document.getElementById('product-category').value = product.category_id;
    document.getElementById('product-name').value = product.name;
    document.getElementById('product-description').value = product.description;
    document.getElementById('product-price').value = product.price;
    document.getElementById('product-discount').value = product.discount_price;
    document.getElementById('product-stock').value = product.stock_qty;
    document.getElementById('product-status').value = product.status;
    document.getElementById('product-featured').checked = (product.is_featured == 1);
    
    document.getElementById('existing-image').value = product.image;
    if (product.image) {
        document.getElementById('product-img-prev').src = `../assets/images/${product.image}`;
        document.getElementById('image-preview-box').classList.remove('hidden');
    } else {
        document.getElementById('image-preview-box').classList.add('hidden');
    }
    
    document.getElementById('submit-btn').innerText = 'Update Product';
    document.getElementById('cancel-btn').classList.remove('hidden');
}

function resetProductForm() {
    document.getElementById('form-title').innerText = 'Add New Product';
    document.getElementById('form-action').value = 'add';
    document.getElementById('product-id').value = '';
    document.getElementById('product-category').value = '';
    document.getElementById('product-name').value = '';
    document.getElementById('product-description').value = '';
    document.getElementById('product-price').value = '';
    document.getElementById('product-discount').value = '0.00';
    document.getElementById('product-stock').value = '100';
    document.getElementById('product-status').value = 'active';
    document.getElementById('product-featured').checked = false;
    document.getElementById('existing-image').value = '';
    document.getElementById('image-preview-box').classList.add('hidden');
    
    document.getElementById('submit-btn').innerText = 'Add Product';
    document.getElementById('cancel-btn').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
