<a href="<?= base_url('product.php?slug=' . urlencode($p['slug'])) ?>" class="product-card text-decoration-none">
    <div class="product-card-img"><i class="fas fa-cube"></i></div>
    <div class="product-card-body">
        <h3 class="h6 fw-bold text-body"><?= e($p['title']) ?></h3>
        <span class="text-muted small"><?= e($p['category_name'] ?? '') ?></span>
        <span class="price d-block"><?= format_price((float)$p['price']) ?></span>
    </div>
</a>
