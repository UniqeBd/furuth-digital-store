<?php

function app_config(string $key = null)
{
    static $config;
    if ($config === null) {
        $config = require __DIR__ . '/../config/app.php';
    }
    return $key === null ? $config : ($config[$key] ?? null);
}

/** @param 'registry'|'console' $key */
function db_table(string $key): string
{
    static $tables;
    if ($tables === null) {
        $tables = require __DIR__ . '/../config/tables.php';
    }
    if (!isset($tables[$key])) {
        throw new InvalidArgumentException('Unknown table key: ' . $key);
    }
    return $tables[$key];
}

function panel_path(): string
{
    return trim(app_config('panel_path') ?: 'x7p9k2-console', '/');
}

function panel_url(string $file = 'login.php'): string
{
    return base_url(panel_path() . '/' . ltrim($file, '/'));
}

function base_url(string $path = ''): string
{
    static $base;
    if ($base === null) {
        $cfg = app_config('url');
        if ($cfg) {
            $base = rtrim($cfg, '/');
        } else {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
            $panel = preg_quote(panel_path(), '#');
            $dir = preg_replace('#/(' . $panel . '|auth|api|includes|scripts)(/.*)?$#', '', $dir);
            $base = rtrim($scheme . '://' . $host . $dir, '/');
        }
    }
    return $base . ($path ? '/' . ltrim($path, '/') : '');
}

function asset_url(string $path): string
{
    return base_url('assets/' . ltrim($path, '/'));
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function e(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function slugify(string $text): string
{
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = trim($text, '-');
    return strtolower($text ?: 'item');
}

function flash(string $key, ?string $message = null)
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return;
    }
    $msg = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $msg;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function setting(string $key, ?string $default = null): ?string
{
    static $cache = [];
    if (!isset($cache[$key])) {
        try {
            $db = Database::connect();
            $stmt = $db->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
            $stmt->execute([$key]);
            $row = $stmt->fetch();
            $cache[$key] = $row ? $row['setting_value'] : $default;
        } catch (Throwable $e) {
            return $default;
        }
    }
    return $cache[$key] ?? $default;
}

function format_price(float $amount): string
{
    $symbol = setting('currency_symbol', '₹');
    return $symbol . number_format($amount, 2);
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function current_user(): ?array
{
    if (!is_logged_in()) {
        return null;
    }
    static $user;
    if ($user === null) {
        $db = Database::connect();
        $stmt = $db->prepare('SELECT id, name, email, is_blocked FROM ' . db_table('registry') . ' WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;
    }
    return $user;
}

function require_login(): void
{
    if (!is_logged_in()) {
        flash('error', 'Please log in to continue.');
        redirect(base_url('auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI'])));
    }
    $user = current_user();
    if ($user && $user['is_blocked']) {
        session_destroy();
        flash('error', 'Your account has been blocked.');
        redirect(base_url('auth/login.php'));
    }
}

function is_admin_logged_in(): bool
{
    return !empty($_SESSION['admin_id']);
}

function current_admin(): ?array
{
    if (!is_admin_logged_in()) {
        return null;
    }
    static $admin;
    if ($admin === null) {
        $db = Database::connect();
        $stmt = $db->prepare('SELECT id, name, email, role FROM ' . db_table('console') . ' WHERE id = ?');
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch() ?: null;
    }
    return $admin;
}

function require_admin(array $roles = []): void
{
    if (!is_admin_logged_in()) {
        redirect(panel_url('login.php'));
    }
    if ($roles) {
        $admin = current_admin();
        if (!$admin || !in_array($admin['role'], $roles, true)) {
            flash('error', 'You do not have permission for this action.');
            redirect(panel_url('index.php'));
        }
    }
}

function cart_get(): array
{
    return $_SESSION['cart'] ?? [];
}

function cart_count(): int
{
    $cart = cart_get();
    return array_sum(array_column($cart, 'qty'));
}

function cart_add(int $productId, int $qty = 1): void
{
    $db = Database::connect();
    $stmt = $db->prepare('SELECT id, title, price, status FROM products WHERE id = ? AND status = "active"');
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    if (!$product) {
        return;
    }
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $key = (string)$productId;
    if (isset($_SESSION['cart'][$key])) {
        $_SESSION['cart'][$key]['qty'] += $qty;
    } else {
        $_SESSION['cart'][$key] = [
            'id'    => $product['id'],
            'title' => $product['title'],
            'price' => (float)$product['price'],
            'qty'   => $qty,
        ];
    }
}

function cart_remove(int $productId): void
{
    unset($_SESSION['cart'][(string)$productId]);
}

function cart_clear(): void
{
    $_SESSION['cart'] = [];
}

function cart_subtotal(): float
{
    $total = 0;
    foreach (cart_get() as $item) {
        $total += $item['price'] * $item['qty'];
    }
    return round($total, 2);
}

function calculate_tax(float $subtotal): float
{
    $pct = (float)(setting('tax_percent', '0') ?: 0);
    return round($subtotal * ($pct / 100), 2);
}

function apply_coupon(string $code, float $subtotal): ?array
{
    $db = Database::connect();
    $stmt = $db->prepare('SELECT * FROM coupons WHERE code = ? AND is_active = 1');
    $stmt->execute([strtoupper(trim($code))]);
    $coupon = $stmt->fetch();
    if (!$coupon) {
        return null;
    }
    if ($coupon['expires_at'] && strtotime($coupon['expires_at']) < time()) {
        return null;
    }
    if ($coupon['usage_limit'] && $coupon['used_count'] >= $coupon['usage_limit']) {
        return null;
    }
    if ($subtotal < (float)$coupon['min_order']) {
        return null;
    }
    $discount = $coupon['type'] === 'percentage'
        ? round($subtotal * ($coupon['value'] / 100), 2)
        : min((float)$coupon['value'], $subtotal);
    return ['coupon' => $coupon, 'discount' => $discount];
}

function generate_order_number(): string
{
    return 'FD' . strtoupper(bin2hex(random_bytes(4))) . date('ymd');
}

function create_download_token(int $userId, int $productId, int $orderId): string
{
    $db = Database::connect();
    $token = bin2hex(random_bytes(32));
    $hours = (int)(setting('download_expiry_hours', '72') ?: 72);
    $expires = $hours > 0 ? date('Y-m-d H:i:s', time() + $hours * 3600) : null;
    $max = (int)(setting('max_downloads', '10') ?: 10);
    $stmt = $db->prepare('INSERT INTO download_tokens (user_id, product_id, order_id, token, expires_at, max_downloads) VALUES (?,?,?,?,?,?)');
    $stmt->execute([$userId, $productId, $orderId, $token, $expires, $max]);
    return $token;
}

function send_mail_simple(string $to, string $subject, string $body): bool
{
    $from = setting('email_from', 'noreply@furuthdigital.com');
    $headers = "From: {$from}\r\nContent-Type: text/html; charset=UTF-8\r\n";
    return @mail($to, $subject, $body, $headers);
}

function page_title(string $title = ''): string
{
    $site = setting('site_name', app_config('name'));
    return $title ? e($title) . ' | ' . e($site) : e($site);
}

function active_nav(string $page): string
{
    $current = basename($_SERVER['PHP_SELF'], '.php');
    $map = ['index' => 'home', 'products' => 'products', 'cart' => 'cart', 'profile' => 'profile', 'orders' => 'profile'];
    $nav = $map[$current] ?? $current;
    return $nav === $page ? 'active' : '';
}
