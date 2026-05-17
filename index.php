<?php
require_once __DIR__ . '/includes/init.php';

$db = Database::connect();

$featured = $db->query('
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.status = "active" AND p.is_featured = 1
    ORDER BY p.sales_count DESC LIMIT 8
')->fetchAll();

$testimonials = $db->query('SELECT * FROM testimonials WHERE is_active = 1 ORDER BY sort_order LIMIT 6')->fetchAll();
$faqs = $db->query('SELECT * FROM faqs WHERE is_active = 1 ORDER BY sort_order LIMIT 4')->fetchAll();

$pageTitle = 'Home';
require __DIR__ . '/includes/layout-header.php';
?>

<section class="hero-section animate-in">
    <div class="container text-center">
        <h1 class="display-5 display-lg-3 mb-3">Premium Digital Products</h1>
        <p class="lead mb-4 col-lg-8 mx-auto">Templates, graphics, software & courses — instant download after purchase.</p>
        <a href="<?= base_url('products.php') ?>" class="btn btn-light btn-lg btn-touch px-4">
            <i class="fas fa-compass me-2"></i>Explore Products
        </a>
    </div>
</section>

<div class="container pb-5">
    <section class="mb-5 animate-in">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="section-title mb-0">Featured Products</h2>
            <a href="<?= base_url('products.php') ?>" class="btn btn-outline-primary btn-sm d-none d-lg-inline-flex">View All</a>
        </div>
        <div class="products-grid d-none d-lg-grid">
            <?php foreach ($featured as $p): ?>
            <article class="product-card">
                <div class="product-card-img"><i class="fas fa-cube"></i></div>
                <div class="product-card-body">
                    <span class="badge bg-primary bg-opacity-10 text-primary mb-2"><?= e($p['category_name'] ?? 'Digital') ?></span>
                    <h3 class="h6 fw-bold"><?= e($p['title']) ?></h3>
                    <p class="text-muted small flex-grow-1"><?= e(mb_substr(strip_tags($p['description'] ?? ''), 0, 80)) ?>…</p>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <span class="price"><?= format_price((float)$p['price']) ?></span>
                        <a href="<?= base_url('product.php?slug=' . urlencode($p['slug'])) ?>" class="btn btn-primary btn-sm">View</a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <div class="products-mobile-list d-lg-none">
            <?php foreach ($featured as $p): ?>
            <a href="<?= base_url('product.php?slug=' . urlencode($p['slug'])) ?>" class="product-card text-decoration-none">
                <div class="product-card-img"><i class="fas fa-cube"></i></div>
                <div class="product-card-body">
                    <h3 class="h6 fw-bold text-body"><?= e($p['title']) ?></h3>
                    <span class="price"><?= format_price((float)$p['price']) ?></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="mb-5">
        <h2 class="section-title text-center">What Customers Say</h2>
        <div class="row g-3">
            <?php foreach ($testimonials as $t): ?>
            <div class="col-md-4">
                <div class="testimonial-card">
                    <p class="mb-3">"<?= e($t['content']) ?>"</p>
                    <strong><?= e($t['name']) ?></strong>
                    <?php if ($t['role']): ?><span class="text-muted d-block small"><?= e($t['role']) ?></span><?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="mb-5">
        <h2 class="section-title text-center">FAQ</h2>
        <div class="accordion col-lg-8 mx-auto" id="homeFaq">
            <?php foreach ($faqs as $i => $f): ?>
            <div class="accordion-item">
                <h3 class="accordion-header">
                    <button class="accordion-button <?= $i ? 'collapsed' : '' ?>" type="button" data-mdb-collapse-init data-mdb-target="#faq<?= $i ?>">
                        <?= e($f['question']) ?>
                    </button>
                </h3>
                <div id="faq<?= $i ?>" class="accordion-collapse collapse <?= $i ? '' : 'show' ?>" data-mdb-parent="#homeFaq">
                    <div class="accordion-body text-muted"><?= nl2br(e($f['answer'])) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <p class="text-center mt-3"><a href="<?= base_url('faq.php') ?>">View all FAQs →</a></p>
    </section>
</div>

<?php require __DIR__ . '/includes/layout-footer.php'; ?>
