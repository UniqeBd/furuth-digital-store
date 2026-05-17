<?php
require_once __DIR__ . '/../includes/init.php';
header('Content-Type: application/json');

$code = $_GET['code'] ?? '';
$subtotal = (float)($_GET['subtotal'] ?? 0);
$result = apply_coupon($code, $subtotal);

if ($result) {
    echo json_encode([
        'valid' => true,
        'discount' => $result['discount'],
        'discount_formatted' => format_price($result['discount']),
    ]);
} else {
    echo json_encode(['valid' => false, 'message' => 'Invalid or expired coupon']);
}
