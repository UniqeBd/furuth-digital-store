<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$db = Database::connect();

// Buy now single product
if (!empty($_GET['buy'])) {
    cart_clear();
    cart_add((int)$_GET['buy']);
}

$cart = cart_get();
if (empty($cart)) {
    flash('error', 'Your cart is empty.');
    redirect(base_url('products.php'));
}

$subtotal = cart_subtotal();
$tax = calculate_tax($subtotal);
$discount = 0;
$couponId = null;
$appliedCoupon = $_SESSION['applied_coupon'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $appliedCoupon = trim($_POST['coupon_code'] ?? '');
    if ($appliedCoupon) {
        $result = apply_coupon($appliedCoupon, $subtotal);
        if ($result) {
            $discount = $result['discount'];
            $couponId = $result['coupon']['id'];
            $_SESSION['applied_coupon'] = $appliedCoupon;
        }
    }

    $afterDiscount = max(0, $subtotal - $discount);
    $tax = calculate_tax($afterDiscount);
    $total = $afterDiscount + $tax;
    $orderNumber = generate_order_number();
    $gateway = setting('payment_gateway', 'razorpay');

    $db->beginTransaction();
    try {
        $stmt = $db->prepare('INSERT INTO orders (user_id, order_number, subtotal, discount, tax_amount, total, coupon_id, currency, payment_gateway, payment_status, status) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
        $stmt->execute([
            $_SESSION['user_id'], $orderNumber, $subtotal, $discount, $tax, $total,
            $couponId, setting('currency', 'INR'), $gateway, 'pending', 'pending'
        ]);
        $orderId = (int)$db->lastInsertId();

        foreach ($cart as $item) {
            $db->prepare('INSERT INTO order_items (order_id, product_id, product_title, price) VALUES (?,?,?,?)')
                ->execute([$orderId, $item['id'], $item['title'], $item['price']]);
        }

        // Simulate payment success (integrate Razorpay/Stripe/PayPal in production)
        $txnId = strtoupper($gateway) . '_' . bin2hex(random_bytes(8));
        $db->prepare('UPDATE orders SET payment_status = "paid", status = "completed", transaction_id = ? WHERE id = ?')
            ->execute([$txnId, $orderId]);

        if ($couponId) {
            $db->prepare('UPDATE coupons SET used_count = used_count + 1 WHERE id = ?')->execute([$couponId]);
        }

        foreach ($cart as $item) {
            create_download_token((int)$_SESSION['user_id'], (int)$item['id'], $orderId);
            $db->prepare('UPDATE products SET sales_count = sales_count + 1 WHERE id = ?')->execute([$item['id']]);
        }

        $db->commit();
        cart_clear();
        unset($_SESSION['applied_coupon']);

        $user = current_user();
        send_mail_simple($user['email'], 'Order Confirmation - ' . $orderNumber,
            '<p>Thank you for your purchase! Order <strong>' . e($orderNumber) . '</strong> is confirmed.</p><p><a href="' . base_url('orders.php') . '">View your downloads</a></p>');

        flash('success', 'Payment successful! Order #' . $orderNumber);
        redirect(base_url('orders.php'));
    } catch (Throwable $e) {
        $db->rollBack();
        flash('error', 'Checkout failed. Please try again.');
    }
}

if ($appliedCoupon) {
    $result = apply_coupon($appliedCoupon, $subtotal);
    if ($result) {
        $discount = $result['discount'];
    }
}
$afterDiscount = max(0, $subtotal - $discount);
$tax = calculate_tax($afterDiscount);
$total = $afterDiscount + $tax;
$gateway = setting('payment_gateway', 'razorpay');

$pageTitle = 'Checkout';
require __DIR__ . '/includes/layout-header.php';
?>

<div class="container py-3 py-lg-4">
    <h1 class="h3 fw-bold mb-4">Checkout</h1>
    <?php if ($err = flash('error')): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h6 fw-bold mb-3">Payment via <?= e(ucfirst($gateway)) ?></h2>
                    <p class="text-muted small">Configure API keys in admin settings. Demo mode completes payment instantly.</p>
                </div>
            </div>
            <form method="post" id="checkoutForm">
                <?= csrf_field() ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <label class="form-label">Coupon Code</label>
                        <div class="input-group">
                            <input type="text" name="coupon_code" id="coupon_code" class="form-control" value="<?= e($appliedCoupon) ?>" placeholder="Enter code">
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-touch w-100 mt-3">
                    <i class="fas fa-lock me-2"></i>Pay <?= format_price($total) ?>
                </button>
            </form>
        </div>
        <div class="col-lg-5">
            <div class="checkout-summary">
                <h3 class="h6 fw-bold mb-3">Order Summary</h3>
                <?php foreach ($cart as $item): ?>
                <div class="d-flex justify-content-between small mb-2">
                    <span><?= e($item['title']) ?> × <?= (int)$item['qty'] ?></span>
                    <span><?= format_price($item['price'] * $item['qty']) ?></span>
                </div>
                <?php endforeach; ?>
                <hr>
                <div class="d-flex justify-content-between mb-1"><span>Subtotal</span><span><?= format_price($subtotal) ?></span></div>
                <?php if ($discount > 0): ?>
                <div class="d-flex justify-content-between mb-1 text-success"><span>Discount</span><span>-<?= format_price($discount) ?></span></div>
                <?php endif; ?>
                <div class="d-flex justify-content-between mb-1"><span><?= e(setting('tax_label', 'Tax')) ?> (<?= e(setting('tax_percent', '0')) ?>%)</span><span><?= format_price($tax) ?></span></div>
                <hr>
                <div class="d-flex justify-content-between fw-bold fs-5"><span>Total</span><span class="text-primary"><?= format_price($total) ?></span></div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/layout-footer.php'; ?>
