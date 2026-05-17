<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();
$db = Database::connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    if (($_POST['action'] ?? '') === 'delete') {
        $db->prepare('DELETE FROM coupons WHERE id = ?')->execute([(int)$_POST['id']]);
        flash('success', 'Coupon deleted.');
    } else {
        $db->prepare('INSERT INTO coupons (code, type, value, min_order, usage_limit, expires_at, is_active) VALUES (?,?,?,?,?,?,?)')
            ->execute([
                strtoupper(trim($_POST['code'])),
                $_POST['type'],
                (float)$_POST['value'],
                (float)$_POST['min_order'],
                $_POST['usage_limit'] ?: null,
                $_POST['expires_at'] ?: null,
                isset($_POST['is_active']) ? 1 : 0,
            ]);
        flash('success', 'Coupon created.');
    }
    redirect(panel_url('coupons.php'));
}

$coupons = $db->query('SELECT * FROM coupons ORDER BY created_at DESC')->fetchAll();
$adminTitle = 'Coupons';
require __DIR__ . '/../includes/admin-header.php';
?>

<div class="row g-4">
<div class="col-md-4">
<div class="card p-3">
<h2 class="h6 fw-bold mb-3">Create Coupon</h2>
<form method="post"><?= csrf_field() ?>
<div class="mb-2"><label class="form-label">Code</label><input name="code" class="form-control" required></div>
<div class="mb-2"><label class="form-label">Type</label><select name="type" class="form-select"><option value="percentage">Percentage</option><option value="flat">Flat</option></select></div>
<div class="mb-2"><label class="form-label">Value</label><input type="number" step="0.01" name="value" class="form-control" required></div>
<div class="mb-2"><label class="form-label">Min Order</label><input type="number" step="0.01" name="min_order" class="form-control" value="0"></div>
<div class="mb-2"><label class="form-label">Usage Limit</label><input type="number" name="usage_limit" class="form-control"></div>
<div class="mb-2"><label class="form-label">Expires</label><input type="datetime-local" name="expires_at" class="form-control"></div>
<div class="form-check mb-3"><input type="checkbox" name="is_active" class="form-check-input" checked id="ca"><label for="ca" class="form-check-label">Active</label></div>
<button class="btn btn-primary w-100">Create</button>
</form>
</div>
</div>
<div class="col-md-8">
<div class="card p-3 table-responsive">
<table class="table mb-0">
<thead><tr><th>Code</th><th>Type</th><th>Value</th><th>Used</th><th>Expires</th><th></th></tr></thead>
<tbody>
<?php foreach ($coupons as $c): ?>
<tr>
<td><strong><?= e($c['code']) ?></strong></td>
<td><?= e($c['type']) ?></td>
<td><?= $c['type']==='percentage' ? $c['value'].'%' : format_price((float)$c['value']) ?></td>
<td><?= (int)$c['used_count'] ?><?= $c['usage_limit'] ? '/'.$c['usage_limit'] : '' ?></td>
<td><?= $c['expires_at'] ? date('M j, Y', strtotime($c['expires_at'])) : '—' ?></td>
<td>
<form method="post" onsubmit="return confirm('Delete?')"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $c['id'] ?>"><button class="btn btn-sm btn-danger">Del</button></form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>
</div>

<?php require __DIR__ . '/../includes/admin-footer.php'; ?>
