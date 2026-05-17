<?php
require_once __DIR__ . '/../includes/init.php';
require_admin(['super_admin']);
$db = Database::connect();

$keys = [
    'site_name', 'footer_text', 'currency', 'currency_symbol', 'tax_percent', 'tax_label',
    'payment_gateway', 'razorpay_key_id', 'razorpay_key_secret',
    'stripe_public_key', 'stripe_secret_key', 'paypal_client_id', 'paypal_secret',
    'download_expiry_hours', 'max_downloads', 'email_from',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    foreach ($keys as $key) {
        if (isset($_POST[$key])) {
            $db->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)')
                ->execute([$key, $_POST[$key]]);
        }
    }
    if (!empty($_FILES['site_logo']['name'])) {
        $dir = app_config('upload_path') . '/branding';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $path = '/uploads/branding/logo.' . pathinfo($_FILES['site_logo']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['site_logo']['tmp_name'], __DIR__ . '/..' . $path);
        $db->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)')
            ->execute(['site_logo', base_url(ltrim($path, '/'))]);
    }
    flash('success', 'Settings saved.');
    redirect(panel_url('settings.php'));
}

$vals = [];
foreach ($keys as $k) { $vals[$k] = setting($k, ''); }

$adminTitle = 'Settings';
require __DIR__ . '/../includes/admin-header.php';
?>

<form method="post" enctype="multipart/form-data" class="card p-4">
<?= csrf_field() ?>
<h2 class="h6 fw-bold mb-3">Branding</h2>
<div class="row g-3 mb-4">
<div class="col-md-6"><label class="form-label">Site Name</label><input name="site_name" class="form-control" value="<?= e($vals['site_name']) ?>"></div>
<div class="col-md-6"><label class="form-label">Logo</label><input type="file" name="site_logo" class="form-control"></div>
<div class="col-12"><label class="form-label">Footer Text</label><input name="footer_text" class="form-control" value="<?= e($vals['footer_text']) ?>"></div>
</div>
<h2 class="h6 fw-bold mb-3">Tax & Currency</h2>
<div class="row g-3 mb-4">
<div class="col-md-3"><label class="form-label">Currency</label><input name="currency" class="form-control" value="<?= e($vals['currency']) ?>"></div>
<div class="col-md-3"><label class="form-label">Symbol</label><input name="currency_symbol" class="form-control" value="<?= e($vals['currency_symbol']) ?>"></div>
<div class="col-md-3"><label class="form-label">Tax %</label><input name="tax_percent" class="form-control" value="<?= e($vals['tax_percent']) ?>"></div>
<div class="col-md-3"><label class="form-label">Tax Label</label><input name="tax_label" class="form-control" value="<?= e($vals['tax_label']) ?>"></div>
</div>
<h2 class="h6 fw-bold mb-3">Payment Gateway</h2>
<div class="mb-3"><label class="form-label">Gateway</label>
<select name="payment_gateway" class="form-select">
<option value="razorpay" <?= $vals['payment_gateway']==='razorpay'?'selected':'' ?>>Razorpay</option>
<option value="stripe" <?= $vals['payment_gateway']==='stripe'?'selected':'' ?>>Stripe</option>
<option value="paypal" <?= $vals['payment_gateway']==='paypal'?'selected':'' ?>>PayPal</option>
</select>
</div>
<div class="row g-3 mb-4">
<div class="col-md-6"><label>Razorpay Key ID</label><input name="razorpay_key_id" class="form-control" value="<?= e($vals['razorpay_key_id']) ?>"></div>
<div class="col-md-6"><label>Razorpay Secret</label><input name="razorpay_key_secret" type="password" class="form-control" value="<?= e($vals['razorpay_key_secret']) ?>"></div>
<div class="col-md-6"><label>Stripe Public</label><input name="stripe_public_key" class="form-control" value="<?= e($vals['stripe_public_key']) ?>"></div>
<div class="col-md-6"><label>Stripe Secret</label><input name="stripe_secret_key" type="password" class="form-control" value="<?= e($vals['stripe_secret_key']) ?>"></div>
<div class="col-md-6"><label>PayPal Client ID</label><input name="paypal_client_id" class="form-control" value="<?= e($vals['paypal_client_id']) ?>"></div>
<div class="col-md-6"><label>PayPal Secret</label><input name="paypal_secret" type="password" class="form-control" value="<?= e($vals['paypal_secret']) ?>"></div>
</div>
<h2 class="h6 fw-bold mb-3">Downloads & Email</h2>
<div class="row g-3 mb-4">
<div class="col-md-4"><label>Download expiry (hours)</label><input name="download_expiry_hours" class="form-control" value="<?= e($vals['download_expiry_hours']) ?>"></div>
<div class="col-md-4"><label>Max downloads</label><input name="max_downloads" class="form-control" value="<?= e($vals['max_downloads']) ?>"></div>
<div class="col-md-4"><label>From Email</label><input name="email_from" class="form-control" value="<?= e($vals['email_from']) ?>"></div>
</div>
<button class="btn btn-primary">Save Settings</button>
</form>

<?php require __DIR__ . '/../includes/admin-footer.php'; ?>
