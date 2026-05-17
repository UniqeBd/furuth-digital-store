<?php
require_once __DIR__ . '/includes/init.php';
$db = Database::connect();

$slug = $_GET['slug'] ?? '';
$stmt = $db->prepare('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE p.slug = ? AND p.status = "active"');
$stmt->execute([$slug]);
$product = $stmt->fetch();
if (!$product) {
    flash('error', 'Product not found.');
    redirect(base_url('products.php'));
}

$screenshots = $db->prepare('SELECT * FROM product_screenshots WHERE product_id = ? ORDER BY sort_order');
$screenshots->execute([$product['id']]);
$screenshots = $screenshots->fetchAll();

$related = $db->prepare('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE p.category_id = ? AND p.id != ? AND p.status = "active" LIMIT 4');
$related->execute([$product['category_id'], $product['id']]);
$related = $related->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_cart') {
    cart_add((int)$product['id']);
    flash('success', 'Added to cart.');
    redirect(base_url('cart.php'));
}

$pageTitle = $product['title'];
require __DIR__ . '/includes/layout-header.php';
?>

<div class="container py-3 py-lg-4">
    <nav aria-label="breadcrumb" class="mb-3 small">
        <a href="<?= base_url('products.php') ?>">Products</a> / <span><?= e($product['title']) ?></span>
    </nav>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="product-gallery mb-3">
                <i class="fas fa-cube"></i>
            </div>
            <?php if ($screenshots): ?>
            <div class="row g-2">
                <?php foreach ($screenshots as $ss): ?>
                <div class="col-4"><img src="<?= e($ss['image_path']) ?>" class="img-fluid rounded" alt=""></div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="col-lg-6">
            <span class="badge bg-primary mb-2"><?= e($product['category_name'] ?? 'Digital') ?></span>
            <h1 class="h2 fw-bold mb-3"><?= e($product['title']) ?></h1>
            <p class="display-6 text-primary fw-bold mb-4"><?= format_price((float)$product['price']) ?></p>
            <div class="text-muted mb-4"><?= nl2br(e($product['description'])) ?></div>
            <form method="post" class="d-flex flex-column flex-sm-row gap-2">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add_cart">
                <button type="submit" class="btn btn-primary btn-touch flex-grow-1">
                    <i class="fas fa-cart-plus me-2"></i>Add to Cart
                </button>
                <a href="<?= base_url('checkout.php?buy=' . (int)$product['id']) ?>" class="btn btn-outline-primary btn-touch flex-grow-1">
                    <i class="fas fa-bolt me-2"></i>Buy Now
                </a>
            </form>
        </div>
    </div>

    <?php if ($related): ?>
    <section class="mt-5">
        <h2 class="section-title">Related Products</h2>
        <div class="products-grid d-none d-lg-grid">
            <?php foreach ($related as $p): include __DIR__ . '/includes/product-card.php'; endforeach; ?>
        </div>
        <div class="products-mobile-list d-lg-none">
            <?php foreach ($related as $p): include __DIR__ . '/includes/product-card-mobile.php'; endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/layout-footer.php'; ?>
