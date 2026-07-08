<?php
// admin/categories.php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$success = '';
$error = '';

// Handle Add/Edit Form Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = sanitizeInput($_POST['name'] ?? '');
    $slug = sanitizeInput($_POST['slug'] ?? '');
    if (empty($slug)) {
        $slug = strtolower(str_replace(' ', '-', $name));
    }
    $status = sanitizeInput($_POST['status'] ?? 'active');
    $id = intval($_POST['id'] ?? 0);

    // Handle Image Upload
    $image_name = $_POST['existing_image'] ?? '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['image']['tmp_name'];
        $original_name = basename($_FILES['image']['name']);
        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
        if (in_array($ext, $allowed)) {
            $image_name = 'cat_' . time() . '.' . $ext;
            move_uploaded_file($tmp_name, __DIR__ . '/../assets/images/' . $image_name);
        }
    }

    if ($name) {
        if ($action === 'add') {
            try {
                $stmt = $pdo->prepare("INSERT INTO categories (name, slug, image, status) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $slug, $image_name, $status]);
                $success = 'Category added successfully!';
            } catch (Exception $e) {
                $error = 'Slug already exists or database error.';
            }
        } elseif ($action === 'edit' && $id) {
            try {
                $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, image = ?, status = ? WHERE id = ?");
                $stmt->execute([$name, $slug, $image_name, $status, $id]);
                $success = 'Category updated successfully!';
            } catch (Exception $e) {
                $error = 'Database error updating category: ' . $e->getMessage();
            }
        }
    } else {
        $error = 'Category name is required.';
    }
}

// Handle Delete Action
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $success = 'Category deleted successfully!';
    }
}

// Handle Bulk Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    $bulk_act = $_POST['bulk_action_type'] ?? '';
    $selected_ids = $_POST['selected_categories'] ?? [];
    if (!empty($selected_ids) && $bulk_act) {
        $ids_placeholder = implode(',', array_fill(0, count($selected_ids), '?'));
        if ($bulk_act === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id IN ($ids_placeholder)");
            $stmt->execute($selected_ids);
            $success = 'Selected categories deleted successfully!';
        } elseif ($bulk_act === 'activate') {
            $stmt = $pdo->prepare("UPDATE categories SET status = 'active' WHERE id IN ($ids_placeholder)");
            $stmt->execute($selected_ids);
            $success = 'Selected categories activated!';
        } elseif ($bulk_act === 'deactivate') {
            $stmt = $pdo->prepare("UPDATE categories SET status = 'inactive' WHERE id IN ($ids_placeholder)");
            $stmt->execute($selected_ids);
            $success = 'Selected categories deactivated!';
        }
    }
}

// Pagination setup
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$totalCategories = $pdo->query("SELECT COUNT(id) FROM categories")->fetchColumn();
$totalPages = ceil($totalCategories / $limit);

// Fetch categories with product count
$categories = $pdo->prepare("SELECT c.*, (SELECT COUNT(id) FROM products WHERE category_id = c.id) as product_count 
                             FROM categories c 
                             ORDER BY c.id DESC LIMIT ? OFFSET ?");
$categories->bindValue(1, $limit, PDO::PARAM_INT);
$categories->bindValue(2, $offset, PDO::PARAM_INT);
$categories->execute();
$categoriesList = $categories->fetchAll();
?>

<style>
.badge-active { background: #D1FAE5; color: #065F46; font-weight: 600; padding: 0.4em 0.8em; border-radius: 50px; }
.badge-inactive { background: #FEE2E2; color: #991B1B; font-weight: 600; padding: 0.4em 0.8em; border-radius: 50px; }
.table-hover tbody tr:hover { background-color: #f8f9fa; }
.drag-drop-zone {
    border: 2px dashed rgba(0,0,0,0.1);
    border-radius: 15px;
    padding: 20px;
    text-align: center;
    background: #fbfbfb;
    cursor: pointer;
    transition: all 0.3s ease;
}
.drag-drop-zone:hover {
    border-color: var(--primary);
    background: #fffbef;
}
</style>

<div class="row g-4">
    <!-- Form Card (Add/Edit) -->
    <div class="col-lg-4">
        <div class="glass-card p-4">
            <h5 class="fw-bold text-dark dark-text-white mb-4" id="form-title">Add Category</h5>
            
            <?php if ($success): ?>
                <div class="alert alert-success border-0 rounded-4 py-2 text-xs mb-3"><i class="fa-solid fa-circle-check me-2"></i><?= $success ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger border-0 rounded-4 py-2 text-xs mb-3"><i class="fa-solid fa-circle-xmark me-2"></i><?= $error ?></div>
            <?php endif; ?>

            <form action="categories.php" method="POST" id="categoryForm" enctype="multipart/form-data">
                <input type="hidden" name="action" id="form-action" value="add">
                <input type="hidden" name="id" id="category-id" value="">
                <input type="hidden" name="existing_image" id="existing-image" value="">
                
                <div class="mb-3">
                    <label class="form-label text-xs fw-bold text-uppercase text-muted">Category Name</label>
                    <input type="text" name="name" id="category-name" class="form-control rounded-pill border-light p-3 shadow-none" onkeyup="generateSlug(this.value)" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-xs fw-bold text-uppercase text-muted">Slug</label>
                    <input type="text" name="slug" id="category-slug" class="form-control rounded-pill border-light p-3 shadow-none" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-xs fw-bold text-uppercase text-muted">Category Thumbnail</label>
                    <div class="drag-drop-zone" onclick="document.getElementById('category-img-input').click()">
                        <i class="fa-regular fa-image fs-3 text-muted mb-2"></i>
                        <p class="small text-secondary mb-0">Click to upload category icon</p>
                        <input type="file" name="image" id="category-img-input" class="d-none" accept="image/*" onchange="previewImg(this)">
                    </div>
                    <div id="img-preview-box" class="mt-2 text-center d-none">
                        <img id="img-preview" src="" class="rounded-3 shadow-sm" style="max-height: 80px;">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-xs fw-bold text-uppercase text-muted">Status</label>
                    <select name="status" id="category-status" class="form-select rounded-pill border-light p-3 shadow-none" style="padding-top: 15px; padding-bottom: 15px;">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-warning rounded-pill w-100 py-2.5 fw-bold mt-2" id="submit-btn">Add Category</button>
                <button type="button" class="btn btn-outline-secondary rounded-pill w-100 py-2 fw-semibold mt-2 d-none" id="cancel-btn" onclick="resetCategoryForm()">Cancel Edit</button>
            </form>
        </div>
    </div>

    <!-- Category List Table -->
    <div class="col-lg-8">
        <div class="glass-card p-4">
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                <h5 class="fw-bold text-dark dark-text-white mb-0">Categories List</h5>
                
                <!-- Category search -->
                <div class="position-relative" style="width: 200px;">
                    <input type="text" id="catSearch" placeholder="Search category..." class="form-control rounded-pill border-light p-2 ps-3 text-xs shadow-none" onkeyup="filterCategoriesTable(this.value)">
                </div>
            </div>

            <!-- Bulk actions form wrapper -->
            <form action="categories.php" method="POST" onsubmit="return confirmBulkAction()">
                <div class="d-flex gap-2 align-items-center mb-3">
                    <select name="bulk_action_type" class="form-select rounded-pill border-light text-xs py-2 shadow-none" style="width: 150px;">
                        <option value="">Bulk Actions</option>
                        <option value="activate">Set Active</option>
                        <option value="deactivate">Set Inactive</option>
                        <option value="delete">Delete Selected</option>
                    </select>
                    <button type="submit" name="bulk_action" class="btn btn-dark rounded-pill px-3 btn-sm fw-bold">Apply</button>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle border-light mb-0 table-hover" id="cat-table-data">
                        <thead>
                            <tr class="text-secondary text-xs uppercase tracking-wider">
                                <th style="width: 40px;"><input type="checkbox" onclick="toggleSelectAll(this)"></th>
                                <th>Category</th>
                                <th>Slug</th>
                                <th>Items</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($categoriesList)): ?>
                                <?php foreach ($categoriesList as $cat): ?>
                                    <tr>
                                        <td><input type="checkbox" name="selected_categories[]" value="<?= $cat['id'] ?>" class="cat-checkbox"></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="<?= $cat['image'] ? '../assets/images/' . htmlspecialchars($cat['image']) : '../assets/images/placeholder.png' ?>" class="rounded-circle object-cover" style="width: 32px; height: 32px;" onerror="this.src='https://images.unsplash.com/photo-1513104890138-7c749659a591?w=50'">
                                                <span class="fw-bold text-dark dark-text-white"><?= htmlspecialchars($cat['name']) ?></span>
                                                <small class="text-muted d-block" style="font-size: 0.65rem;">ID: #<?= $cat['id'] ?></small>
                                            </div>
                                        </td>
                                        <td class="small text-secondary"><?= htmlspecialchars($cat['slug']) ?></td>
                                        <td><span class="badge bg-secondary rounded-pill"><?= $cat['product_count'] ?> items</span></td>
                                        <td>
                                            <span class="<?= $cat['status'] === 'active' ? 'badge-active' : 'badge-inactive' ?> text-xs text-uppercase">
                                                <?= $cat['status'] ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-2">
                                                <button type="button" onclick="editCategory(<?= $cat['id'] ?>, '<?= htmlspecialchars($cat['name']) ?>', '<?= $cat['slug'] ?>', '<?= htmlspecialchars($cat['image'] ?? '') ?>', '<?= $cat['status'] ?>')" class="btn btn-link text-warning p-1 shadow-none" title="Edit Category"><i class="fa-solid fa-pen-to-square"></i></button>
                                                <button type="button" onclick="confirmDelete(<?= $cat['id'] ?>)" class="btn btn-link text-danger p-1 shadow-none" title="Delete Category"><i class="fa-solid fa-trash-can"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">No categories created yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </form>

            <!-- Pagination buttons -->
            <?php if ($totalPages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination pagination-sm justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $page === $i ? 'active' : '' ?>">
                                <a class="page-link" href="categories.php?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function generateSlug(val) {
    const slugInput = document.getElementById('category-slug');
    slugInput.value = val.toLowerCase().replace(/ /g, '-').replace(/[^\w-]+/g, '');
}

function previewImg(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('img-preview').src = e.target.result;
            document.getElementById('img-preview-box').classList.remove('d-none');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function editCategory(id, name, slug, image, status) {
    document.getElementById('form-title').innerText = 'Edit Category';
    document.getElementById('form-action').value = 'edit';
    document.getElementById('category-id').value = id;
    document.getElementById('category-name').value = name;
    document.getElementById('category-slug').value = slug;
    document.getElementById('category-status').value = status;
    document.getElementById('existing-image').value = image;

    if (image) {
        document.getElementById('img-preview').src = '../assets/images/' + image;
        document.getElementById('img-preview-box').classList.remove('d-none');
    } else {
        document.getElementById('img-preview-box').classList.add('d-none');
    }

    document.getElementById('submit-btn').innerText = 'Update Category';
    document.getElementById('cancel-btn').classList.remove('d-none');
}

function resetCategoryForm() {
    document.getElementById('form-title').innerText = 'Add Category';
    document.getElementById('form-action').value = 'add';
    document.getElementById('category-id').value = '';
    document.getElementById('category-name').value = '';
    document.getElementById('category-slug').value = '';
    document.getElementById('category-status').value = 'active';
    document.getElementById('existing-image').value = '';
    document.getElementById('img-preview-box').classList.add('d-none');

    document.getElementById('submit-btn').innerText = 'Add Category';
    document.getElementById('cancel-btn').classList.add('d-none');
}

function toggleSelectAll(master) {
    document.querySelectorAll('.cat-checkbox').forEach(cb => {
        cb.checked = master.checked;
    });
}

function filterCategoriesTable(val) {
    const term = val.toLowerCase();
    const rows = document.querySelectorAll('#cat-table-data tbody tr');
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        if (text.includes(term)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function confirmDelete(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "Delete this category and all related products?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'categories.php?delete=' + id;
        }
    });
}

function confirmBulkAction() {
    return confirm("Apply this bulk action to all selected categories?");
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
