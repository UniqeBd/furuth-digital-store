<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();
$db = Database::connect();

$todaySales = $db->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE payment_status='paid' AND DATE(created_at)=CURDATE()")->fetchColumn();
$monthSales = $db->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE payment_status='paid' AND MONTH(created_at)=MONTH(CURDATE())")->fetchColumn();
$totalOrders = $db->query("SELECT COUNT(*) FROM orders WHERE payment_status='paid'")->fetchColumn();
$totalUsers = $db->query('SELECT COUNT(*) FROM ' . db_table('registry'))->fetchColumn();
$topProducts = $db->query('SELECT title, sales_count, price FROM products ORDER BY sales_count DESC LIMIT 5')->fetchAll();
$recentOrders = $db->query('SELECT o.*, u.name FROM orders o JOIN ' . db_table('registry') . ' u ON u.id=o.user_id ORDER BY o.created_at DESC LIMIT 8')->fetchAll();

$adminTitle = 'Dashboard';
require __DIR__ . '/../includes/admin-header.php';
?>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="admin-stat-card"><div class="text-muted small">Today's Sales</div><div class="fs-4 fw-bold"><?= format_price((float)$todaySales) ?></div></div></div>
    <div class="col-md-3"><div class="admin-stat-card"><div class="text-muted small">Monthly Sales</div><div class="fs-4 fw-bold"><?= format_price((float)$monthSales) ?></div></div></div>
    <div class="col-md-3"><div class="admin-stat-card"><div class="text-muted small">Total Orders</div><div class="fs-4 fw-bold"><?= (int)$totalOrders ?></div></div></div>
    <div class="col-md-3"><div class="admin-stat-card"><div class="text-muted small">Users</div><div class="fs-4 fw-bold"><?= (int)$totalUsers ?></div></div></div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card"><div class="card-body">
            <h2 class="h6 fw-bold mb-3">Recent Orders</h2>
            <table class="table table-sm mb-0">
                <thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($recentOrders as $o): ?>
                <tr>
                    <td><?= e($o['order_number']) ?></td>
                    <td><?= e($o['name']) ?></td>
                    <td><?= format_price((float)$o['total']) ?></td>
                    <td><span class="badge bg-<?= $o['payment_status']==='paid'?'success':'warning' ?>"><?= e($o['payment_status']) ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div></div>
    </div>
    <div class="col-lg-5">
        <div class="card"><div class="card-body">
            <h2 class="h6 fw-bold mb-3">Top Products</h2>
            <ul class="list-group list-group-flush">
                <?php foreach ($topProducts as $p): ?>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span><?= e($p['title']) ?></span>
                    <span class="text-muted"><?= (int)$p['sales_count'] ?> sales</span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div></div>
    </div>
</div>

<?php require __DIR__ . '/../includes/admin-footer.php'; ?>
