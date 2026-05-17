<article class="product-card">
    <div class="product-card-img"><i class="fas fa-cube"></i></div>
    <div class="product-card-body">
        <span class="badge bg-primary bg-opacity-10 text-primary mb-2"><?= e($p['category_name'] ?? 'Digital') ?></span>
        <h3 class="h6 fw-bold"><?= e($p['title']) ?></h3>
        <p class="text-muted small"><?= e(mb_substr(strip_tags($p['description'] ?? ''), 0, 70)) ?>…</p>
        <div class="d-flex justify-content-between align-items-center mt-auto">
            <span class="price"><?= format_price((float)$p['price']) ?></span>
            <a href="<?= base_url('product.php?slug=' . urlencode($p['slug'])) ?>" class="btn btn-primary btn-sm">View</a>
        </div>
    </div>
</article>
