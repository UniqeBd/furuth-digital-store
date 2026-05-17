<?php
require_once __DIR__ . '/../includes/init.php';

if (is_admin_logged_in()) {
    redirect(panel_url('index.php'));
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $db = Database::connect();
    $stmt = $db->prepare('SELECT * FROM ' . db_table('console') . ' WHERE email = ?');
    $stmt->execute([$email]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        redirect(panel_url('index.php'));
    }
    $error = 'Invalid credentials.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.3.2/mdb.min.css" rel="stylesheet">
    <link href="<?= asset_url('css/style.css') ?>" rel="stylesheet">
</head>
<body class="admin-body d-flex align-items-center justify-content-center min-vh-100">
    <div class="auth-card" style="max-width:400px">
        <h1 class="h4 fw-bold text-center mb-4">Sign In</h1>
        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <form method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required value="admin@furuthdigital.com">
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>
</body>
</html>
