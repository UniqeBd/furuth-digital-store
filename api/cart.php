<?php
require_once __DIR__ . '/../includes/init.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false]);
    exit;
}

$action = $_POST['action'] ?? '';
$productId = (int)($_POST['product_id'] ?? 0);

if ($action === 'add' && $productId) {
    cart_add($productId);
    echo json_encode(['success' => true, 'count' => cart_count()]);
} else {
    echo json_encode(['success' => false]);
}
