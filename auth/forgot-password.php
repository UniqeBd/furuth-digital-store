<?php
require_once __DIR__ . '/../includes/init.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $email = trim($_POST['email'] ?? '');
    $db = Database::connect();
    $stmt = $db->prepare('SELECT id FROM ' . db_table('registry') . ' WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600);
        $db->prepare('UPDATE ' . db_table('registry') . ' SET reset_token = ?, reset_expires = ? WHERE id = ?')->execute([$token, $expires, $user['id']]);
        $link = base_url('auth/reset-password.php?token=' . $token);
        send_mail_simple($email, 'Reset Password', '<p>Click to reset: <a href="' . e($link) . '">' . e($link) . '</a></p>');
    }
    $message = 'If that email exists, a reset link has been sent.';
}

$pageTitle = 'Forgot Password';
$hideBottomNav = true;
require __DIR__ . '/../includes/layout-header.php';
?>

<div class="container">
    <div class="auth-card animate-in">
        <h1 class="h4 fw-bold text-center mb-4">Forgot Password</h1>
        <?php if ($message): ?><div class="alert alert-info"><?= e($message) ?></div><?php endif; ?>
        <form method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-touch w-100">Send Reset Link</button>
        </form>
        <p class="text-center mt-3 mb-0 small"><a href="<?= base_url('auth/login.php') ?>">Back to login</a></p>
    </div>
</div>

<?php require __DIR__ . '/../includes/layout-footer.php'; ?>
