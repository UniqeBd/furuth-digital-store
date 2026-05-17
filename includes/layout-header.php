<?php
/** @var string $pageTitle */
/** @var string $bodyClass */
$pageTitle = $pageTitle ?? '';
$bodyClass = $bodyClass ?? '';
$siteName = setting('site_name', app_config('name'));
$logo = setting('site_logo', '');
$cartCount = cart_count();
$user = current_user();
$isAuthPage = strpos($_SERVER['PHP_SELF'] ?? '', '/auth/') !== false;
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= e($theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#6366f1">
    <title><?= page_title($pageTitle) ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.3.2/mdb.min.css" rel="stylesheet">
    <link href="<?= asset_url('css/style.css') ?>" rel="stylesheet">
</head>
<body class="app-body <?= e($bodyClass) ?> theme-<?= e($theme) ?>">

<!-- Mobile AppBar -->
<header class="mobile-appbar d-lg-none">
    <div class="appbar-inner">
        <a href="<?= base_url() ?>" class="appbar-logo">
            <?php if ($logo): ?>
                <img src="<?= e($logo) ?>" alt="<?= e($siteName) ?>" height="32">
            <?php else: ?>
                <i class="fas fa-bolt text-primary"></i>
                <span><?= e($siteName) ?></span>
            <?php endif; ?>
        </a>
        <div class="appbar-actions">
            <button type="button" class="btn-icon theme-toggle" aria-label="Toggle theme" title="Theme">
                <i class="fas fa-<?= $theme === 'dark' ? 'sun' : 'moon' ?>"></i>
            </button>
            <?php if (!$isAuthPage): ?>
            <a href="<?= base_url('cart.php') ?>" class="btn-icon cart-badge-wrap" aria-label="Cart">
                <i class="fas fa-shopping-cart"></i>
                <?php if ($cartCount > 0): ?><span class="cart-badge"><?= $cartCount ?></span><?php endif; ?>
            </a>
            <?php endif; ?>
            <button type="button" class="btn-icon" data-mdb-toggle="offcanvas" data-mdb-target="#mobileMenu" aria-label="Menu">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
</header>

<!-- Desktop Navbar -->
<nav class="desktop-navbar d-none d-lg-block navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= base_url() ?>">
            <?php if ($logo): ?>
                <img src="<?= e($logo) ?>" alt="" height="36" class="me-2"><?= e($siteName) ?>
            <?php else: ?>
                <i class="fas fa-bolt text-primary me-2"></i><?= e($siteName) ?>
            <?php endif; ?>
        </a>
        <ul class="navbar-nav mx-auto gap-1">
            <li class="nav-item"><a class="nav-link" href="<?= base_url() ?>">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('products.php') ?>">Products</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('faq.php') ?>">FAQ</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('contact.php') ?>">Contact</a></li>
        </ul>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-link theme-toggle p-2" aria-label="Theme">
                <i class="fas fa-<?= $theme === 'dark' ? 'sun' : 'moon' ?>"></i>
            </button>
            <a href="<?= base_url('cart.php') ?>" class="btn btn-outline-primary position-relative">
                <i class="fas fa-shopping-cart me-1"></i> Cart
                <?php if ($cartCount > 0): ?><span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $cartCount ?></span><?php endif; ?>
            </a>
            <?php if ($user): ?>
                <a href="<?= base_url('profile.php') ?>" class="btn btn-primary"><i class="fas fa-user me-1"></i><?= e($user['name']) ?></a>
            <?php else: ?>
                <a href="<?= base_url('auth/login.php') ?>" class="btn btn-outline-primary">Login</a>
                <a href="<?= base_url('auth/signup.php') ?>" class="btn btn-primary">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Mobile offcanvas menu -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="mobileMenu">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title"><?= e($siteName) ?></h5>
        <button type="button" class="btn-close" data-mdb-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <nav class="mobile-menu-nav">
            <a href="<?= base_url() ?>"><i class="fas fa-home"></i> Home</a>
            <a href="<?= base_url('products.php') ?>"><i class="fas fa-box"></i> Products</a>
            <a href="<?= base_url('cart.php') ?>"><i class="fas fa-shopping-cart"></i> Cart <?php if ($cartCount): ?>(<?= $cartCount ?>)<?php endif; ?></a>
            <a href="<?= base_url('orders.php') ?>"><i class="fas fa-download"></i> My Orders</a>
            <a href="<?= base_url('faq.php') ?>"><i class="fas fa-question-circle"></i> FAQ</a>
            <a href="<?= base_url('contact.php') ?>"><i class="fas fa-envelope"></i> Contact</a>
            <?php if ($user): ?><a href="<?= base_url('ticket.php') ?>"><i class="fas fa-ticket-alt"></i> Support</a><?php endif; ?>
            <hr>
            <?php if ($user): ?>
                <a href="<?= base_url('profile.php') ?>"><i class="fas fa-user"></i> Profile</a>
                <a href="<?= base_url('auth/logout.php') ?>"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="<?= base_url('auth/login.php') ?>"><i class="fas fa-sign-in-alt"></i> Login</a>
                <a href="<?= base_url('auth/signup.php') ?>"><i class="fas fa-user-plus"></i> Sign Up</a>
            <?php endif; ?>
        </nav>
    </div>
</div>

<main class="app-main <?= $isAuthPage ? 'auth-main' : '' ?>">
