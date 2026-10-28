<?php
// Bütün səhifələr üçün ümumi başlanğıc: parametrlər, sessiya və köməkçi funksiyalar.

declare(strict_types=1);

define('ROOT', dirname(__DIR__));

// Xətalar ziyarətçiyə göstərilmir, yalnız server loguna yazılır (lokal serverdə göstərilir).
ini_set('display_errors', PHP_SAPI === 'cli-server' ? '1' : '0');
ini_set('log_errors', '1');

mb_internal_encoding('UTF-8');
date_default_timezone_set('Asia/Baku');

// Sayt alt qovluqda yerləşərsə (məs. example.com/jetpark/), linklər avtomatik düzəlir.
// Admin panel (admin/index.php) bu sabiti özü təyin edir.
if (!defined('BASE_URL')) {
    define('BASE_URL', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/') . '/');
}

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

require ROOT . '/includes/store.php';
require ROOT . '/includes/functions.php';
require ROOT . '/includes/icons.php';
