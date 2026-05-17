<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$token = $_GET['token'] ?? '';
if (!$token) {
    flash('error', 'Invalid download link.');
    redirect(base_url('orders.php'));
}

$db = Database::connect();
$stmt = $db->prepare('
    SELECT dt.*, p.file_path, p.title
    FROM download_tokens dt
    JOIN products p ON p.id = dt.product_id
    WHERE dt.token = ? AND dt.user_id = ?
');
$stmt->execute([$token, $_SESSION['user_id']]);
$row = $stmt->fetch();

if (!$row) {
    flash('error', 'Download not found.');
    redirect(base_url('orders.php'));
}

if ($row['expires_at'] && strtotime($row['expires_at']) < time()) {
    flash('error', 'Download link has expired.');
    redirect(base_url('orders.php'));
}

if ($row['download_count'] >= $row['max_downloads']) {
    flash('error', 'Download limit reached.');
    redirect(base_url('orders.php'));
}

$filePath = $row['file_path'];
$uploadBase = realpath(app_config('upload_path') ?: (__DIR__ . '/uploads'));
$fullPath = $filePath ? realpath($uploadBase . '/digital/' . basename($filePath)) : false;

if (!$fullPath || !is_file($fullPath)) {
    // Demo file when no upload exists
    $demoDir = $uploadBase . '/digital';
    if (!is_dir($demoDir)) {
        mkdir($demoDir, 0755, true);
    }
    $demoFile = $demoDir . '/demo-' . $row['product_id'] . '.txt';
    if (!is_file($demoFile)) {
        file_put_contents($demoFile, "Furuth Digital - Demo download for: " . $row['title'] . "\nThank you for your purchase!\n");
    }
    $fullPath = $demoFile;
}

$db->prepare('UPDATE download_tokens SET download_count = download_count + 1 WHERE id = ?')->execute([$row['id']]);

$filename = basename($fullPath);
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($fullPath));
readfile($fullPath);
exit;
