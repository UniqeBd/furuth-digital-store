<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();
$db = Database::connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $id = (int)$_POST['id'];
  $status = $_POST['payment_status'] ?? 'paid';
    $db->prepare('UPDATE orders SET payment_status = ? WHERE id = ?')->execute([$status, $id]);
    flash('success', 'Order updated.');
    redirect(panel_url('orders.php'));
}

$orders = $db->query('SELECT o.*, u.name, u.email FROM orders o JOIN ' . db_table('registry') . ' u ON u.id=o.user_id ORDER BY o.created_at DESC')->fetchAll();
$adminTitle = 'Orders';
require __DIR__ . '/../includes/admin-header.php';
?>

<div class="card"><div class="card-body table-responsive">
<table class="table">
<thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Txn ID</th><th>Payment</th><th>Date</th><th></th></tr></thead>
<tbody>
<?php foreach ($orders as $o): ?>
<tr>
<td><?= e($o['order_number']) ?></td>
<td><?= e($o['name']) ?><br><small><?= e($o['email']) ?></small></td>
<td><?= format_price((float)$o['total']) ?></td>
<td><small><?= e($o['transaction_id'] ?? '-') ?></small></td>
<td><span class="badge bg-<?= $o['payment_status']==='paid'?'success':($o['payment_status']==='refunded'?'secondary':'warning') ?>"><?= e($o['payment_status']) ?></span></td>
<td><?= date('M j, Y', strtotime($o['created_at'])) ?></td>
<td>
<form method="post" class="d-flex gap-1">
<?= csrf_field() ?><input type="hidden" name="id" value="<?= $o['id'] ?>">
<select name="payment_status" class="form-select form-select-sm">
<option value="paid" <?= $o['payment_status']==='paid'?'selected':'' ?>>paid</option>
<option value="pending" <?= $o['payment_status']==='pending'?'selected':'' ?>>pending</option>
<option value="failed" <?= $o['payment_status']==='failed'?'selected':'' ?>>failed</option>
<option value="refunded" <?= $o['payment_status']==='refunded'?'selected':'' ?>>refunded</option>
</select>
<button class="btn btn-sm btn-primary">Save</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div></div>

<?php require __DIR__ . '/../includes/admin-footer.php'; ?>
