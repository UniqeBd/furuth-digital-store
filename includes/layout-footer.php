<?php
$footerText = setting('footer_text', '© ' . date('Y') . ' Furuth Digital');
$hideBottomNav = $hideBottomNav ?? false;
$isAuthPage = strpos($_SERVER['PHP_SELF'] ?? '', '/auth/') !== false;
?>
</main>

<footer class="site-footer d-none d-lg-block">
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-lg-4">
                <h5 class="fw-bold mb-3"><?= e(setting('site_name', 'Furuth Digital')) ?></h5>
                <p class="text-muted"><?= e($footerText) ?></p>
            </div>
            <div class="col-lg-2">
                <h6 class="fw-semibold mb-3">Shop</h6>
                <ul class="list-unstyled footer-links">
                    <li><a href="<?= base_url('products.php') ?>">All Products</a></li>
                    <li><a href="<?= base_url('cart.php') ?>">Cart</a></li>
                    <li><a href="<?= base_url('orders.php') ?>">My Orders</a></li>
                </ul>
            </div>
            <div class="col-lg-2">
                <h6 class="fw-semibold mb-3">Support</h6>
                <ul class="list-unstyled footer-links">
                    <li><a href="<?= base_url('faq.php') ?>">FAQ</a></li>
                    <li><a href="<?= base_url('contact.php') ?>">Contact</a></li>
                </ul>
            </div>
            <div class="col-lg-4">
                <h6 class="fw-semibold mb-3">Stay Updated</h6>
                <p class="text-muted small">Premium digital products for creators and businesses.</p>
            </div>
        </div>
        <hr class="my-4 opacity-25">
        <p class="text-center text-muted small mb-0"><?= e($footerText) ?></p>
    </div>
</footer>

<?php if (!$hideBottomNav && !$isAuthPage): ?>
<nav class="bottom-nav d-lg-none" aria-label="Main navigation">
    <a href="<?= base_url() ?>" class="bottom-nav-item <?= active_nav('home') ?>">
        <i class="fas fa-home"></i>
        <span>Home</span>
    </a>
    <a href="<?= base_url('products.php') ?>" class="bottom-nav-item <?= active_nav('products') ?>">
        <i class="fas fa-box"></i>
        <span>Products</span>
    </a>
    <a href="<?= base_url('cart.php') ?>" class="bottom-nav-item <?= active_nav('cart') ?>">
        <i class="fas fa-shopping-cart"></i>
        <span>Cart</span>
        <?php if (cart_count() > 0): ?><em class="bottom-badge"><?= cart_count() ?></em><?php endif; ?>
    </a>
    <a href="<?= base_url(is_logged_in() ? 'profile.php' : 'auth/login.php') ?>" class="bottom-nav-item <?= active_nav('profile') ?>">
        <i class="fas fa-user"></i>
        <span>Profile</span>
    </a>
</nav>
<?php endif; ?>

<script>window.FURUTH_BASE = <?= json_encode(rtrim(base_url(), '/')) ?>;</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.3.2/mdb.umd.min.js"></script>
<script src="<?= asset_url('js/app.js') ?>"></script>
</body>
</html>
