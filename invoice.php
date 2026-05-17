<?php
require_once __DIR__ . '/includes/init.php';
require_login();
$db = Database::connect();

$id = (int)($_GET['id'] ?? 0);
$stmt = $db->prepare('SELECT o.*, u.name, u.email FROM orders o JOIN ' . db_table('registry') . ' u ON u.id = o.user_id WHERE o.id = ? AND o.user_id = ?');
$stmt->execute([$id, $_SESSION['user_id']]);
$order = $stmt->fetch();
if (!$order) {
    flash('error', 'Invoice not found.');
    redirect(base_url('orders.php'));
}

$items = $db->prepare('SELECT * FROM order_items WHERE order_id = ?');
$items->execute([$id]);
$items = $items->fetchAll();

$hideBottomNav = true;
$pageTitle = 'Invoice ' . $order['order_number'];
require __DIR__ . '/includes/layout-header.php';
?>

<div class="container py-4">
    <div class="card border-0 shadow-sm p-4" id="invoice">
        <div class="d-flex justify-content-between mb-4">
            <div>
                <h1 class="h4 fw-bold"><?= e(setting('site_name', 'Furuth Digital')) ?></h1>
                <p class="text-muted small mb-0">Invoice / Receipt</p>
            </div>
            <div class="text-end">
                <strong>#<?= e($order['order_number']) ?></strong>
                <p class="text-muted small mb-0"><?= date('F j, Y', strtotime($order['created_at'])) ?></p>
            </div>
        </div>
        <p><strong>Bill to:</strong> <?= e($order['name']) ?> &lt;<?= e($order['email']) ?>&gt;</p>
        <table class="table mt-3">
            <thead><tr><th>Product</th><th class="text-end">Price</th></tr></thead>
            <tbody>
            <?php foreach ($items as $item): ?>
            <tr><td><?= e($item['product_title']) ?></td><td class="text-end"><?= format_price((float)$item['price']) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div class="text-end">
            <p>Subtotal: <?= format_price((float)$order['subtotal']) ?></p>
            <?php if ($order['discount'] > 0): ?><p>Discount: -<?= format_price((float)$order['discount']) ?></p><?php endif; ?>
            <p><?= e(setting('tax_label', 'Tax')) ?>: <?= format_price((float)$order['tax_amount']) ?></p>
            <p class="fs-5 fw-bold">Total: <?= format_price((float)$order['total']) ?></p>
            <p class="small text-muted">Transaction: <?= e($order['transaction_id'] ?? 'N/A') ?></p>
        </div>
        <button onclick="window.print()" class="btn btn-primary mt-3 no-print"><i class="fas fa-print me-1"></i> Print / Save PDF</button>
    </div>
</div>
<style>@media print { .no-print, .mobile-appbar, .bottom-nav, .desktop-navbar { display: none !important; } }</style>
<?php require __DIR__ . '/includes/layout-footer.php'; ?>
