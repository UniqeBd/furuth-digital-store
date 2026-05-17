<?php
require_once __DIR__ . '/includes/init.php';
require_login();
$db = Database::connect();

$orders = $db->prepare('
    SELECT o.*, 
        (SELECT GROUP_CONCAT(oi.product_title SEPARATOR ", ") FROM order_items oi WHERE oi.order_id = o.id) AS items
    FROM orders o WHERE o.user_id = ? AND o.payment_status = "paid"
    ORDER BY o.created_at DESC
');
$orders->execute([$_SESSION['user_id']]);
$orders = $orders->fetchAll();

$downloads = $db->prepare('
    SELECT dt.*, p.title, p.slug, o.order_number
    FROM download_tokens dt
    JOIN products p ON p.id = dt.product_id
    JOIN orders o ON o.id = dt.order_id
    WHERE dt.user_id = ?
    ORDER BY dt.created_at DESC
');
$downloads->execute([$_SESSION['user_id']]);
$downloads = $downloads->fetchAll();

$pageTitle = 'My Orders';
$bodyClass = 'page-orders';
require __DIR__ . '/includes/layout-header.php';
?>

<div class="container py-3 py-lg-4">
    <h1 class="h3 fw-bold mb-4">Orders & Downloads</h1>
    <?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>

    <section class="mb-5">
        <h2 class="h5 fw-bold mb-3">Your Downloads</h2>
        <?php if (empty($downloads)): ?>
        <p class="text-muted">No purchases yet. <a href="<?= base_url('products.php') ?>">Shop now</a></p>
        <?php else: ?>
        <?php foreach ($downloads as $d):
            $expired = $d['expires_at'] && strtotime($d['expires_at']) < time();
            $limitReached = $d['download_count'] >= $d['max_downloads'];
        ?>
        <div class="order-item d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="h6 fw-bold mb-1"><?= e($d['title']) ?></h3>
                <span class="text-muted small">Order #<?= e($d['order_number']) ?></span>
                <?php if ($d['expires_at']): ?>
                <span class="d-block small text-muted">Expires: <?= date('M j, Y g:i A', strtotime($d['expires_at'])) ?></span>
                <?php endif; ?>
            </div>
            <?php if ($expired || $limitReached): ?>
            <span class="badge bg-secondary">Unavailable</span>
            <?php else: ?>
            <a href="<?= base_url('download.php?token=' . urlencode($d['token'])) ?>" class="btn btn-primary btn-touch">
                <i class="fas fa-download me-1"></i> Download
            </a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <section>
        <h2 class="h5 fw-bold mb-3">Order History</h2>
        <?php if (empty($orders)): ?>
        <p class="text-muted">No orders yet.</p>
        <?php else: ?>
        <div class="table-responsive d-none d-lg-block">
            <table class="table">
                <thead><tr><th>Order</th><th>Items</th><th>Total</th><th>Date</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td><?= e($o['order_number']) ?></td>
                    <td><?= e($o['items']) ?></td>
                    <td><?= format_price((float)$o['total']) ?></td>
                    <td><?= date('M j, Y', strtotime($o['created_at'])) ?></td>
                    <td><a href="<?= base_url('invoice.php?id=' . (int)$o['id']) ?>" class="btn btn-sm btn-outline-primary">Invoice</a></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php foreach ($orders as $o): ?>
        <div class="order-item d-lg-none">
            <div class="d-flex justify-content-between mb-2">
                <strong><?= e($o['order_number']) ?></strong>
                <span class="text-primary"><?= format_price((float)$o['total']) ?></span>
            </div>
            <p class="small text-muted mb-2"><?= e($o['items']) ?></p>
            <div class="d-flex justify-content-between align-items-center">
                <span class="small"><?= date('M j, Y', strtotime($o['created_at'])) ?></span>
                <a href="<?= base_url('invoice.php?id=' . (int)$o['id']) ?>" class="btn btn-sm btn-outline-primary">Invoice</a>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </section>
</div>

<?php require __DIR__ . '/includes/layout-footer.php'; ?>
