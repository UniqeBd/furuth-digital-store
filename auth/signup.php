<?php
require_once __DIR__ . '/../includes/init.php';

if (is_logged_in()) {
    redirect(base_url());
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter valid name and email.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $db = Database::connect();
        $stmt = $db->prepare('SELECT id FROM ' . db_table('registry') . ' WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already registered.';
        } else {
            $db->prepare('INSERT INTO ' . db_table('registry') . ' (name, email, password) VALUES (?,?,?)')
                ->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            $_SESSION['user_id'] = (int)$db->lastInsertId();
            flash('success', 'Account created successfully!');
            redirect(base_url());
        }
    }
}

$pageTitle = 'Sign Up';
$hideBottomNav = true;
require __DIR__ . '/../includes/layout-header.php';
?>

<div class="container">
    <div class="auth-card animate-in">
        <h1 class="h4 fw-bold text-center mb-4">Create Account</h1>
        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <form method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" required value="<?= e($_POST['name'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required value="<?= e($_POST['email'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required minlength="8">
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-touch w-100">Sign Up</button>
        </form>
        <p class="text-center mt-3 mb-0 small">Already have an account? <a href="<?= base_url('auth/login.php') ?>">Login</a></p>
    </div>
</div>

<?php require __DIR__ . '/../includes/layout-footer.php'; ?>
