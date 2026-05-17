<?php
require_once __DIR__ . '/includes/init.php';
$db = Database::connect();

$search = trim($_GET['q'] ?? '');
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'popular';
$minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : null;
$maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : null;

$where = ['p.status = "active"'];
$params = [];

if ($search !== '') {
    $where[] = '(p.title LIKE ? OR p.description LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
if ($category !== '') {
    $where[] = 'c.slug = ?';
    $params[] = $category;
}
if ($minPrice !== null) {
    $where[] = 'p.price >= ?';
    $params[] = $minPrice;
}
if ($maxPrice !== null && $maxPrice > 0) {
    $where[] = 'p.price <= ?';
    $params[] = $maxPrice;
}

$orderBy = match ($sort) {
    'price_low'  => 'p.price ASC',
    'price_high' => 'p.price DESC',
    'newest'     => 'p.created_at DESC',
    default      => 'p.sales_count DESC',
};

$sql = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug
    FROM products p LEFT JOIN categories c ON c.id = p.category_id
    WHERE ' . implode(' AND ', $where) . " ORDER BY {$orderBy}";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
$categories = $db->query('SELECT * FROM categories ORDER BY name')->fetchAll();

$pageTitle = 'Products';
require __DIR__ . '/includes/layout-header.php';
?>

<div class="container py-3 py-lg-4">
    <div class="page-header">
        <h1 class="h3 fw-bold mb-1">Products</h1>
        <p class="text-muted mb-0"><?= count($products) ?> digital products</p>
    </div>

    <form method="get" class="filters-bar">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-lg-4">
                <label class="form-label small">Search</label>
                <input type="search" name="q" class="form-control" placeholder="Search products…" value="<?= e($search) ?>">
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label small">Category</label>
                <select name="category" class="form-select">
                    <option value="">All</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= e($cat['slug']) ?>" <?= $category === $cat['slug'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label small">Sort</label>
                <select name="sort" class="form-select">
                    <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Popularity</option>
                    <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low</option>
                    <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High</option>
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label small">Min Price</label>
                <input type="number" name="min_price" class="form-control" step="0.01" value="<?= $minPrice !== null ? e((string)$minPrice) : '' ?>">
            </div>
            <div class="col-6 col-lg-2">
                <button type="submit" class="btn btn-primary w-100 btn-touch"><i class="fas fa-filter me-1"></i> Filter</button>
            </div>
        </div>
    </form>

    <?php if (empty($products)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-box-open fa-3x mb-3"></i>
        <p>No products found.</p>
    </div>
    <?php else: ?>
    <div class="products-grid d-none d-lg-grid">
        <?php foreach ($products as $p): include __DIR__ . '/includes/product-card.php'; endforeach; ?>
    </div>
    <div class="products-mobile-list d-lg-none">
        <?php foreach ($products as $p): include __DIR__ . '/includes/product-card-mobile.php'; endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/layout-footer.php'; ?>
