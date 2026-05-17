<?php
require_once __DIR__ . '/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $action = $_POST['action'] ?? '';
    $pid = (int)($_POST['product_id'] ?? 0);
    if ($action === 'remove') {
        cart_remove($pid);
    } elseif ($action === 'update') {
        $qty = max(1, (int)($_POST['qty'] ?? 1));
        if (isset($_SESSION['cart'][(string)$pid])) {
            $_SESSION['cart'][(string)$pid]['qty'] = $qty;
        }
    }
    redirect(base_url('cart.php'));
}

$cart = cart_get();
$subtotal = cart_subtotal();
$pageTitle = 'Cart';
require __DIR__ . '/includes/layout-header.php';
?>

<div class="container py-3 py-lg-4">
    <h1 class="h3 fw-bold mb-4">Shopping Cart</h1>

    <?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>

    <?php if (empty($cart)): ?>
    <div class="text-center py-5">
        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
        <p class="text-muted">Your cart is empty.</p>
        <a href="<?= base_url('products.php') ?>" class="btn btn-primary btn-touch">Browse Products</a>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <?php foreach ($cart as $item): ?>
            <div class="cart-item d-flex flex-wrap align-items-center gap-3">
                <div class="flex-grow-1">
                    <h3 class="h6 fw-bold mb-1"><?= e($item['title']) ?></h3>
                    <span class="text-primary fw-semibold"><?= format_price($item['price']) ?></span>
                </div>
                <form method="post" class="d-flex align-items-center gap-2">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>">
                    <input type="hidden" name="action" value="update">
                    <input type="number" name="qty" value="<?= (int)$item['qty'] ?>" min="1" class="form-control" style="width:70px">
                    <button type="submit" class="btn btn-sm btn-outline-primary">Update</button>
                </form>
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>">
                    <input type="hidden" name="action" value="remove">
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                </form>
                <strong><?= format_price($item['price'] * $item['qty']) ?></strong>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="col-lg-4">
            <div class="checkout-summary">
                <h3 class="h6 fw-bold mb-3">Order Summary</h3>
                <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><span><?= format_price($subtotal) ?></span></div>
                <hr>
                <div class="d-flex justify-content-between fw-bold fs-5 mb-4">
                    <span>Total</span><span class="text-primary"><?= format_price($subtotal) ?></span>
                </div>
                <a href="<?= base_url('checkout.php') ?>" class="btn btn-primary btn-touch w-100">Proceed to Checkout</a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/layout-footer.php'; ?>
