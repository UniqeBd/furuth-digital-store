<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();
$db = Database::connect();
$categories = $db->query('SELECT * FROM categories ORDER BY name')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        $db->prepare('DELETE FROM products WHERE id = ?')->execute([(int)$_POST['id']]);
        flash('success', 'Product deleted.');
    } elseif ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $slug = slugify($title);
        $data = [
            (int)($_POST['category_id'] ?: null),
            $title, $slug,
            trim($_POST['description'] ?? ''),
            (float)($_POST['price'] ?? 0),
            $_POST['status'] ?? 'active',
            isset($_POST['is_featured']) ? 1 : 0,
        ];
        if ($id) {
            $db->prepare('UPDATE products SET category_id=?, title=?, slug=?, description=?, price=?, status=?, is_featured=? WHERE id=?')
                ->execute([...$data, $id]);
        } else {
            $db->prepare('INSERT INTO products (category_id, title, slug, description, price, status, is_featured) VALUES (?,?,?,?,?,?,?)')
                ->execute($data);
            $id = (int)$db->lastInsertId();
        }
        if (!empty($_FILES['digital_file']['name'])) {
            $dir = app_config('upload_path') . '/digital';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $fname = 'product-' . $id . '-' . basename($_FILES['digital_file']['name']);
            move_uploaded_file($_FILES['digital_file']['tmp_name'], $dir . '/' . $fname);
            $db->prepare('UPDATE products SET file_path = ? WHERE id = ?')->execute([$fname, $id]);
        }
        flash('success', 'Product saved.');
    }
    redirect(panel_url('products.php'));
}

$products = $db->query('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id=p.category_id ORDER BY p.created_at DESC')->fetchAll();
$edit = null;
if (!empty($_GET['edit'])) {
    $stmt = $db->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}

$adminTitle = 'Products';
require __DIR__ . '/../includes/admin-header.php';
?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card"><div class="card-body">
            <h2 class="h6 fw-bold mb-3"><?= $edit ? 'Edit' : 'Add' ?> Product</h2>
            <form method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
                <div class="mb-2"><label class="form-label">Title</label><input name="title" class="form-control" required value="<?= e($edit['title'] ?? '') ?>"></div>
                <div class="mb-2"><label class="form-label">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">—</option>
                        <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($edit['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-2"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"><?= e($edit['description'] ?? '') ?></textarea></div>
                <div class="mb-2"><label class="form-label">Price</label><input type="number" step="0.01" name="price" class="form-control" required value="<?= e($edit['price'] ?? '') ?>"></div>
                <div class="mb-2"><label class="form-label">Digital File</label><input type="file" name="digital_file" class="form-control"></div>
                <div class="mb-2"><label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= ($edit['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($edit['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" name="is_featured" class="form-check-input" id="feat" <?= !empty($edit['is_featured']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="feat">Featured</label>
                </div>
                <button class="btn btn-primary">Save Product</button>
            </form>
        </div></div>
    </div>
    <div class="col-lg-7">
        <div class="card"><div class="card-body table-responsive">
            <table class="table">
                <thead><tr><th>Title</th><th>Price</th><th>Status</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td><?= e($p['title']) ?><br><small class="text-muted"><?= e($p['category_name'] ?? '') ?></small></td>
                    <td><?= format_price((float)$p['price']) ?></td>
                    <td><span class="badge bg-<?= $p['status']==='active'?'success':'secondary' ?>"><?= e($p['status']) ?></span></td>
                    <td>
                        <a href="?edit=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form method="post" class="d-inline" onsubmit="return confirm('Delete?')">
                            <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger">Del</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div></div>
    </div>
</div>

<?php require __DIR__ . '/../includes/admin-footer.php'; ?>
