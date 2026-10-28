<?php
// Bütün sorğular bu fayldan keçir (.htaccess vasitəsilə). Ünvanlar WordPress versiyası ilə eynidir.

// Lokal test: php -S localhost:8000 index.php — mövcud faylları birbaşa göstər.
// Apache-də eyni qaydaları .htaccess təmin edir.
if (PHP_SAPI === 'cli-server') {
    $uri = urldecode((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    $file = __DIR__ . $uri;
    $private = preg_match('#^/(includes|pages|data|storage|\.claude)(/|$)#', $uri) || $uri === '/config.php';
    if ($uri === '/admin') {
        header('Location: /admin/', true, 301);
        return true;
    }
    if ($uri === '/admin/' || $uri === '/admin/index.php') {
        require __DIR__ . '/admin/index.php';
        return true;
    }
    if (!$private && is_file($file)) {
        if (in_array($uri, ['/send.php', '/admin.php'], true)) {
            require $file;
            return true;
        }
        if (pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
            return false;
        }
    }
}

require __DIR__ . '/includes/bootstrap.php';

$requestPath = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$path = trim(substr($requestPath, strlen(BASE_URL) - 1), '/');

// Köhnə WordPress ünvanları → yeni ünvanlar.
$redirects = [
    'category/blog'   => 'blog/',
    'author/7t1t1'    => 'blog/',
    'index.php'       => '',
    'feed'            => 'blog/',
    'sahibkarlar'     => 'sahibkarlar-ucun/',
    'sitemap_index.xml' => 'sitemap.xml',
    'wp-sitemap.xml'    => 'sitemap.xml',
    'page-sitemap.xml'  => 'sitemap.xml',
    'post-sitemap.xml'  => 'sitemap.xml',
];
if (isset($redirects[$path])) {
    redirect(url($redirects[$path]), 301);
}

if ($path === 'sitemap.xml') {
    require ROOT . '/includes/sitemap.php';
    return;
}

// WordPress kimi bütün səhifə ünvanları "/" ilə bitir.
if ($path !== '' && substr($requestPath, -1) !== '/') {
    $query = isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '' ? '?' . $_SERVER['QUERY_STRING'] : '';
    redirect(url($path . '/') . $query, 301);
}

$pages = [
    ''           => 'home',
    'haqqimizda' => 'haqqimizda',
    'faq'        => 'faq',
    'elaqe'      => 'elaqe',
    'blog'       => 'blog',
];

if (isset($pages[$path])) {
    render($pages[$path], ['page' => 1]);
    return;
}

$servicePages = [
    'sahibkarlar-ucun' => ['key' => 'owners', 'seoKey' => 'sahibkarlar', 'formType' => 'Sahibkar', 'source' => 'Sahibkarlar səhifəsi'],
    'suruculer'        => ['key' => 'drivers', 'seoKey' => 'suruculer', 'formType' => 'Sürücü', 'source' => 'Sürücülər səhifəsi'],
];
if (isset($servicePages[$path])) {
    render('service', $servicePages[$path] + ['slug' => $path]);
    return;
}

if (preg_match('#^blog/page/(\d+)$#', $path, $m)) {
    $page = (int) $m[1];
    if ($page <= 1) {
        redirect(url('blog/'), 301);
    }
    if ($page <= (int) ceil(count(posts()) / config('posts_per_page'))) {
        render('blog', ['page' => $page]);
        return;
    }
}

if ($post = find_post($path)) {
    render('post', ['post' => $post]);
    return;
}

render('404', [], 404);
