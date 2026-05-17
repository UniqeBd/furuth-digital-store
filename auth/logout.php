<?php
require_once __DIR__ . '/../includes/init.php';
unset($_SESSION['user_id']);
redirect(base_url('auth/login.php'));
