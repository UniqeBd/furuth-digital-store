<?php
/** @var string $adminTitle */
$adminTitle = $adminTitle ?? 'Dashboard';
$admin = current_admin();
$siteName = setting('site_name', 'Furuth Digital');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($adminTitle) ?> | <?= e($siteName) ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.3.2/mdb.min.css" rel="stylesheet">
    <link href="<?= asset_url('css/style.css') ?>" rel="stylesheet">
</head>
<body class="admin-body">
<aside class="admin-sidebar" id="adminSidebar">
    <div class="px-4 py-3 border-bottom border-secondary">
        <a href="<?= panel_url('index.php') ?>" class="text-white text-decoration-none fw-bold">
            <i class="fas fa-bolt me-2"></i><?= e($siteName) ?>
        </a>
        <small class="d-block text-muted mt-1">Control Panel</small>
    </div>
    <nav class="mt-2">
        <a href="<?= panel_url('index.php') ?>" class="<?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>"><i class="fas fa-chart-line"></i> Dashboard</a>
        <a href="<?= panel_url('products.php') ?>"><i class="fas fa-box"></i> Products</a>
        <a href="<?= panel_url('orders.php') ?>"><i class="fas fa-receipt"></i> Orders</a>
        <a href="<?= panel_url('users.php') ?>"><i class="fas fa-users"></i> Members</a>
        <a href="<?= panel_url('coupons.php') ?>"><i class="fas fa-tag"></i> Coupons</a>
        <a href="<?= panel_url('reports.php') ?>"><i class="fas fa-chart-bar"></i> Reports</a>
        <a href="<?= panel_url('support.php') ?>"><i class="fas fa-headset"></i> Support</a>
        <a href="<?= panel_url('faq.php') ?>"><i class="fas fa-question-circle"></i> FAQ</a>
        <?php if ($admin && $admin['role'] === 'super_admin'): ?>
        <a href="<?= panel_url('settings.php') ?>"><i class="fas fa-cog"></i> Settings</a>
        <?php endif; ?>
        <hr class="border-secondary mx-3">
        <a href="<?= base_url() ?>" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a>
        <a href="<?= panel_url('logout.php') ?>"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
</aside>
<main class="admin-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 fw-bold mb-0"><?= e($adminTitle) ?></h1>
        <span class="text-muted small"><?= e($admin['name'] ?? '') ?> (<?= e($admin['role'] ?? '') ?>)</span>
    </div>
    <?php if ($err = flash('error')): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>
    <?php if ($ok = flash('success')): ?><div class="alert alert-success"><?= e($ok) ?></div><?php endif; ?>
