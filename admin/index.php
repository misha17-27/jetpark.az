<?php
// Admin panelin giriş nöqtəsi: /admin/?r=bölmə

define('BASE_URL', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/admin/index.php', 2)), '/') . '/');
define('ADMIN_URL', BASE_URL . 'admin/');

require dirname(__DIR__) . '/includes/bootstrap.php';
require ROOT . '/includes/admin/bootstrap.php';

admin_dispatch();
