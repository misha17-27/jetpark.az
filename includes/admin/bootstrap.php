<?php
// Admin panelin başlanğıcı və marşrutlaşdırma.

require __DIR__ . '/i18n.php';
require __DIR__ . '/helpers.php';
require __DIR__ . '/media.php';
require __DIR__ . '/auth.php';

header('X-Robots-Tag: noindex, nofollow');
header('X-Frame-Options: DENY');
header('Cache-Control: no-store, private');
header('Referrer-Policy: same-origin');

/**
 * Bölmələr: marşrut => [fayl, yalnız administrator üçündür?]
 */
function admin_routes(): array
{
    return [
        'dashboard'    => ['dashboard', false],
        'applications' => ['applications', false],
        'posts'        => ['posts', false],
        'faq'          => ['faq', false],
        'advantages'   => ['advantages', false],
        'about'        => ['about', false],
        'owners'       => ['service', false],
        'drivers'      => ['service', false],
        'texts'        => ['texts', false],
        'seo'          => ['seo', false],
        'settings'     => ['settings', true],
        'users'        => ['users', true],
        'profile'      => ['profile', false],
    ];
}

function admin_dispatch(): void
{
    if (isset($_GET['lang'])) {
        set_admin_lang((string) $_GET['lang']);
        $back = $_SERVER['HTTP_REFERER'] ?? '';
        redirect(str_starts_with((string) parse_url($back, PHP_URL_PATH), ADMIN_URL) ? $back : ADMIN_URL, 303);
    }

    $route = (string) ($_GET['r'] ?? 'dashboard');

    // Hələ heç bir istifadəçi yoxdursa — ilk quraşdırma.
    if (!users()) {
        require __DIR__ . '/modules/setup.php';
        return;
    }
    if ($route === 'logout') {
        if (is_post()) {
            verify_csrf();
            admin_logout();
            admin_flash('success', t('login.logged_out'));
        }
        admin_redirect('login');
    }
    if (!current_user()) {
        require __DIR__ . '/modules/login.php';
        return;
    }
    if ($route === 'login') {
        admin_redirect('dashboard');
    }

    $routes = admin_routes();
    if (!isset($routes[$route])) {
        $route = 'dashboard';
    }
    [$module, $adminOnly] = $routes[$route];
    if ($adminOnly && !is_admin()) {
        admin_flash('error', t('common.forbidden'));
        admin_redirect('dashboard');
    }
    $action = (string) ($_GET['a'] ?? 'index');
    require __DIR__ . '/modules/' . $module . '.php';
}

/**
 * Səhifəni panel şablonu ilə göstərir. $view — HTML (ob_start ilə yığılmış), $title — başlıq.
 */
function admin_page(string $title, string $active, callable $body, array $headerActions = []): void
{
    ob_start();
    $body();
    $content = ob_get_clean();
    require __DIR__ . '/layout.php';
}

function new_applications_count(): int
{
    return count(array_filter(store_get('applications'), fn ($a) => ($a['status'] ?? 'new') === 'new'));
}
