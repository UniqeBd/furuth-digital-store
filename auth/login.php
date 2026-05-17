<?php
require_once __DIR__ . '/../includes/init.php';

if (is_logged_in()) {
    redirect(base_url());
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $db = Database::connect();
    $stmt = $db->prepare('SELECT * FROM ' . db_table('registry') . ' WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && !$user['is_blocked'] && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $redirect = $_GET['redirect'] ?? base_url();
        redirect($redirect);
    }
    $error = 'Invalid email or password.';
}

$pageTitle = 'Login';
$hideBottomNav = true;
require __DIR__ . '/../includes/layout-header.php';
?>

<div class="container">
    <div class="auth-card animate-in">
        <h1 class="h4 fw-bold text-center mb-4">Welcome Back</h1>
        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <form method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required value="<?= e($_POST['email'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3 text-end">
                <a href="<?= base_url('auth/forgot-password.php') ?>" class="small">Forgot password?</a>
            </div>
            <button type="submit" class="btn btn-primary btn-touch w-100">Login</button>
        </form>
        <p class="text-center mt-3 mb-0 small">Don't have an account? <a href="<?= base_url('auth/signup.php') ?>">Sign up</a></p>
    </div>
</div>

<?php require __DIR__ . '/../includes/layout-footer.php'; ?>
