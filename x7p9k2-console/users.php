<?php
require_once __DIR__ . '/../includes/init.php';
require_admin(['super_admin']);
$db = Database::connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $id = (int)$_POST['id'];
    $blocked = (int)($_POST['is_blocked'] ?? 0);
    $db->prepare('UPDATE ' . db_table('registry') . ' SET is_blocked = ? WHERE id = ?')->execute([$blocked, $id]);
    flash('success', 'User updated.');
    redirect(panel_url('users.php'));
}

$users = $db->query('
    SELECT u.*, COUNT(o.id) AS order_count, COALESCE(SUM(o.total),0) AS spent
    FROM ' . db_table('registry') . ' u LEFT JOIN orders o ON o.user_id=u.id AND o.payment_status="paid"
    GROUP BY u.id ORDER BY u.created_at DESC
')->fetchAll();

$adminTitle = 'Users';
require __DIR__ . '/../includes/admin-header.php';
?>

<div class="card"><div class="card-body table-responsive">
<table class="table">
<thead><tr><th>Name</th><th>Email</th><th>Orders</th><th>Spent</th><th>Status</th><th></th></tr></thead>
<tbody>
<?php foreach ($users as $u): ?>
<tr>
<td><?= e($u['name']) ?></td>
<td><?= e($u['email']) ?></td>
<td><?= (int)$u['order_count'] ?></td>
<td><?= format_price((float)$u['spent']) ?></td>
<td><?= $u['is_blocked'] ? '<span class="badge bg-danger">Blocked</span>' : '<span class="badge bg-success">Active</span>' ?></td>
<td>
<form method="post" class="d-inline">
<?= csrf_field() ?><input type="hidden" name="id" value="<?= $u['id'] ?>">
<input type="hidden" name="is_blocked" value="<?= $u['is_blocked'] ? 0 : 1 ?>">
<button class="btn btn-sm btn-outline-<?= $u['is_blocked']?'success':'danger' ?>"><?= $u['is_blocked']?'Unblock':'Block' ?></button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div></div>

<?php require __DIR__ . '/../includes/admin-footer.php'; ?>
