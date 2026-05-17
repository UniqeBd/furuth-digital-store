<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();
$db = Database::connect();

$daily = $db->query("
    SELECT DATE(created_at) AS d, COUNT(*) AS orders, SUM(total) AS revenue, SUM(tax_amount) AS tax
    FROM orders WHERE payment_status='paid' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at) ORDER BY d DESC
")->fetchAll();

$monthly = $db->query("
    SELECT DATE_FORMAT(created_at,'%Y-%m') AS m, COUNT(*) AS orders, SUM(total) AS revenue, SUM(tax_amount) AS tax
    FROM orders WHERE payment_status='paid'
    GROUP BY m ORDER BY m DESC LIMIT 12
")->fetchAll();

$top = $db->query('SELECT title, sales_count, price FROM products ORDER BY sales_count DESC LIMIT 10')->fetchAll();
$totals = $db->query("SELECT SUM(total) AS revenue, SUM(tax_amount) AS tax FROM orders WHERE payment_status='paid'")->fetch();

$adminTitle = 'Reports';
require __DIR__ . '/../includes/admin-header.php';
?>

<div class="row g-3 mb-4">
<div class="col-md-6"><div class="admin-stat-card"><div class="text-muted small">Total Revenue</div><div class="fs-4 fw-bold"><?= format_price((float)($totals['revenue'] ?? 0)) ?></div></div></div>
<div class="col-md-6"><div class="admin-stat-card"><div class="text-muted small">Total Tax Collected</div><div class="fs-4 fw-bold"><?= format_price((float)($totals['tax'] ?? 0)) ?></div></div></div>
</div>

<div class="row g-4">
<div class="col-lg-6"><div class="card p-3"><h2 class="h6 fw-bold">Daily Sales (30 days)</h2>
<table class="table table-sm"><thead><tr><th>Date</th><th>Orders</th><th>Revenue</th><th>Tax</th></tr></thead><tbody>
<?php foreach ($daily as $r): ?><tr><td><?= e($r['d']) ?></td><td><?= $r['orders'] ?></td><td><?= format_price((float)$r['revenue']) ?></td><td><?= format_price((float)$r['tax']) ?></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<div class="col-lg-6"><div class="card p-3"><h2 class="h6 fw-bold">Monthly Sales</h2>
<table class="table table-sm"><thead><tr><th>Month</th><th>Orders</th><th>Revenue</th></tr></thead><tbody>
<?php foreach ($monthly as $r): ?><tr><td><?= e($r['m']) ?></td><td><?= $r['orders'] ?></td><td><?= format_price((float)$r['revenue']) ?></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<div class="col-12"><div class="card p-3"><h2 class="h6 fw-bold">Top Selling Products</h2>
<table class="table"><thead><tr><th>Product</th><th>Sales</th><th>Price</th></tr></thead><tbody>
<?php foreach ($top as $p): ?><tr><td><?= e($p['title']) ?></td><td><?= (int)$p['sales_count'] ?></td><td><?= format_price((float)$p['price']) ?></td></tr><?php endforeach; ?>
</tbody></table></div></div>
</div>

<?php require __DIR__ . '/../includes/admin-footer.php'; ?>
