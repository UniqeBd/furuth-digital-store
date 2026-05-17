<?php
require_once __DIR__ . '/../includes/init.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if (strlen($password) < 8 || $password !== $confirm) {
        $error = 'Password must be 8+ characters and match.';
    } else {
        $db = Database::connect();
        $stmt = $db->prepare('SELECT id FROM ' . db_table('registry') . ' WHERE reset_token = ? AND reset_expires > NOW()');
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        if ($user) {
            $db->prepare('UPDATE ' . db_table('registry') . ' SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?')
                ->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
            $success = true;
        } else {
            $error = 'Invalid or expired reset link.';
        }
    }
}

$pageTitle = 'Reset Password';
$hideBottomNav = true;
require __DIR__ . '/../includes/layout-header.php';
?>

<div class="container">
    <div class="auth-card animate-in">
        <h1 class="h4 fw-bold text-center mb-4">Reset Password</h1>
        <?php if ($success): ?>
        <div class="alert alert-success">Password updated. <a href="<?= base_url('auth/login.php') ?>">Login</a></div>
        <?php else: ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="token" value="<?= e($token) ?>">
            <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control" required minlength="8">
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-touch w-100">Reset Password</button>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../includes/layout-footer.php'; ?>
