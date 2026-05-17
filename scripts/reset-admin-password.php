<?php
/**
 * One-time: reset operator password. Run: php scripts/reset-admin-password.php
 * Delete this file after use on production.
 */
require_once __DIR__ . '/../includes/init.php';

$email = 'admin@furuthdigital.com';
$password = 'Admin@123';

$db = Database::connect();
$hash = password_hash($password, PASSWORD_DEFAULT);
$db->prepare('UPDATE ' . db_table('console') . ' SET password = ? WHERE email = ?')->execute([$hash, $email]);

echo "Password updated for {$email}\n";
echo "Login with password: {$password}\n";
