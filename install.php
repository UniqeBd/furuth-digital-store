<?php
/**
 * One-time installer: imports schema and sets admin password.
 * Delete this file after installation for security.
 */
$step = $_GET['step'] ?? 'form';
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = $_POST['host'] ?? 'localhost';
    $dbname = $_POST['dbname'] ?? 'furuth_digital';
    $user = $_POST['username'] ?? 'root';
    $pass = $_POST['password'] ?? '';
    $adminPass = $_POST['admin_password'] ?? 'Admin@123';

    try {
        $pdo = new PDO("mysql:host={$host};charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $sql = file_get_contents(__DIR__ . '/database/schema.sql');
        $pdo->exec($sql);

        $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $hash = password_hash($adminPass, PASSWORD_DEFAULT);
        $tables = require __DIR__ . '/config/tables.php';
        $pdo->prepare('UPDATE ' . $tables['console'] . ' SET password = ? WHERE email = ?')->execute([$hash, 'admin@furuthdigital.com']);

        $config = "<?php\nreturn [\n    'host' => " . var_export($host, true) . ",\n    'dbname' => " . var_export($dbname, true) . ",\n    'username' => " . var_export($user, true) . ",\n    'password' => " . var_export($pass, true) . ",\n    'charset' => 'utf8mb4',\n];\n";
        file_put_contents(__DIR__ . '/config/database.php', $config);

        foreach (['uploads/digital', 'uploads/branding', 'uploads/products'] as $dir) {
            if (!is_dir(__DIR__ . '/' . $dir)) {
                mkdir(__DIR__ . '/' . $dir, 0755, true);
            }
        }

        $success = true;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install Furuth Digital</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.3.2/mdb.min.css" rel="stylesheet">
</head>
<body class="p-4 bg-light">
<div class="container" style="max-width:520px">
    <h1 class="h4 mb-4">Furuth Digital Installer</h1>
    <?php if ($success): ?>
    <div class="alert alert-success">
        <strong>Installed!</strong><br>
        <a href="index.php">View Store</a><br>
        <p class="small mt-2 mb-0">Dashboard login URL is set in <code>config/app.php</code> (<code>panel_path</code>). Delete <code>install.php</code> now.</p>
    </div>
    <?php else: ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post" class="card p-4 shadow-sm">
        <div class="mb-2"><label class="form-label">DB Host</label><input name="host" class="form-control" value="localhost"></div>
        <div class="mb-2"><label class="form-label">Database Name</label><input name="dbname" class="form-control" value="furuth_digital"></div>
        <div class="mb-2"><label class="form-label">DB Username</label><input name="username" class="form-control" value="root"></div>
        <div class="mb-2"><label class="form-label">DB Password</label><input name="password" type="password" class="form-control"></div>
        <div class="mb-3"><label class="form-label">Admin Password</label><input name="admin_password" class="form-control" value="Admin@123"></div>
        <button class="btn btn-primary w-100">Install</button>
    </form>
    <?php endif; ?>
</div>
</body>
</html>
