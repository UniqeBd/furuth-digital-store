<?php
require_once __DIR__ . '/../includes/init.php';
unset($_SESSION['admin_id']);
redirect(panel_url('login.php'));
