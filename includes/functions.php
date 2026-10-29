<?php

/** Parametr: əvvəlcə admin paneldə saxlanılan dəyər (storage/data/settings.json), sonra config.php. */
function config(string $key, $default = null)
{
    static $base = null;
    $base ??= require ROOT . '/config.php';
    $settings = store_get('settings');
    if (isset($settings[$key]) && $settings[$key] !== '') {
        return $settings[$key];
    }
    return $base[$key] ?? $default;
}

/** Səhifə mətni: content('home.hero_brand') */
function content(string $path, $default = '')
{
    $saved = array_get(store_get('content'), $path);
    return $saved ?? array_get(store_defaults('content'), $path, $default);
}

/** SEO məlumatı: seo('haqqimizda') → ['title' => ..., 'description' => ..., 'og_image' => ...] */
function seo(string $page): array
{
    return array_merge(store_defaults('seo')[$page] ?? [], array_filter(store_get('seo')[$page] ?? [], fn ($v) => $v !== ''));
}

/** Səhifə faylında: extract(seo_vars('faq')); → $title, $description, $og_image */
function seo_vars(string $page): array
{
    $s = seo($page);
    return [
        'title'       => $s['title'] ?? '',
        'description' => $s['description'] ?? '',
        'og_image'    => $s['og_image'] ?? 'img/home-car.webp',
    ];
}

/**
 * Admin paneldən gələn sadə mətn: HTML təhlükəsizləşdirilir, **söz** vurğulanır, yeni sətir → <br>.
 */
function rich_text(string $text, string $class = 'text-green', string $br = '<br>'): string
{
    $html = e(trim($text));
    $html = preg_replace('/\*\*(.+?)\*\*/su', '<span class="' . $class . '">$1</span>', $html);
    return str_replace(["\r\n", "\n"], $br, $html);
}

/** Telefon nömrəsindən tel: linki üçün yalnız rəqəmlər və "+". */
function phone_link(string $phone): string
{
    return preg_replace('/[^\d+]/', '', $phone);
}

/**
 * Müraciətin WhatsApp linki: mesaj ziyarətçinin adından hazır yazılır və saytın WhatsApp nömrəsinə gedir.
 * Forma bu linki {name}, {surname}, {phone}, {type} yer tutucuları ilə alır, JS onları doldurur.
 */
function whatsapp_application_url(array $a): string
{
    $text = 'Salam! ' . config('site_name') . " saytından müraciət edirəm.\n"
        . 'Ad: ' . $a['name'] . ' ' . $a['surname'] . "\n"
        . 'Telefon: ' . $a['phone'] . "\n"
        . 'Növ: ' . $a['type'];
    return 'https://wa.me/' . preg_replace('/\D/', '', (string) config('whatsapp')) . '?text=' . rawurlencode($text);
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Saytın daxili linki: url('blog/') → /blog/ */
function url(string $path = ''): string
{
    return BASE_URL . ltrim($path, '/');
}

/** Tam ünvan (canonical, og:url, sitemap üçün). */
function absolute_url(string $path = ''): string
{
    return rtrim((string) config('site_url'), '/') . '/' . ltrim($path, '/');
}

/** Statik fayl linki; fayl dəyişəndə brauzer keşi avtomatik yenilənir. */
function asset(string $path): string
{
    $file = ROOT . '/assets/' . ltrim($path, '/');
    $version = is_file($file) ? '?v=' . filemtime($file) : '';
    return url('assets/' . ltrim($path, '/')) . $version;
}

/** Bütün yazılar (qaralamalar daxil), ən yenisi birinci. */
function all_posts(): array
{
    $posts = store_get('posts');
    usort($posts, fn ($a, $b) => strcmp($b['date'], $a['date']));
    return $posts;
}

/** Strukturlaşdırılmış məlumat (schema.org JSON-LD) üçün <script> bloku. */
function jsonld(array $data): string
{
    $data = ['@context' => 'https://schema.org'] + $data;
    return '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) . '</script>';
}

/** Şirkət haqqında Google üçün məlumat: ad, ünvan, telefon, xəritə, sosial şəbəkələr («Parametrlər»dən). */
function business_jsonld(): array
{
    $data = [
        '@type'       => 'LocalBusiness',
        '@id'         => absolute_url('#business'),
        'name'        => config('site_name'),
        'description' => seo('home')['description'] ?? '',
        'url'         => absolute_url(),
        'logo'        => absolute_url('assets/img/logo-dark.svg'),
        'image'       => absolute_url('assets/img/home-car.webp'),
        'telephone'   => config('phone'),
        'email'       => config('email'),
        'address'     => ['@type' => 'PostalAddress', 'streetAddress' => config('address_short'), 'addressLocality' => 'Bakı', 'addressCountry' => 'AZ'],
        'sameAs'      => array_values(array_filter([config('facebook'), config('instagram')])),
    ];
    if (preg_match('/[?&]q=(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/', (string) config('map_embed'), $m)) {
        $data['geo'] = ['@type' => 'GeoCoordinates', 'latitude' => (float) $m[1], 'longitude' => (float) $m[2]];
    }
    if (config('map_url')) {
        $data['hasMap'] = config('map_url');
    }
    return $data;
}

/** Breadcrumb: [['Ana səhifə', url], ['Blog', url], ...] — Google axtarışında ünvanın yerində göstərilir. */
function breadcrumb_jsonld(array $items): array
{
    $list = [];
    foreach (array_values($items) as $i => [$name, $url]) {
        $list[] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $name, 'item' => $url];
    }
    return ['@type' => 'BreadcrumbList', 'itemListElement' => $list];
}

/** Yazının brauzer başlığı (<title>): SEO başlıq, boşdursa «Yazının başlığı - JetPark». */
function post_seo_title(array $post): string
{
    $title = trim($post['seo_title'] ?? '');
    return $title !== '' ? $title : $post['title'] . ' - ' . config('site_name');
}

/** Saytda göstərilən yazılar: dərc olunmuş və tarixi çatmış. */
function posts(): array
{
    $now = date('Y-m-d H:i:s');
    return array_values(array_filter(all_posts(), fn ($p) => ($p['status'] ?? 'published') === 'published' && $p['date'] <= $now));
}

function find_post(string $slug): ?array
{
    foreach (posts() as $index => $post) {
        if ($post['slug'] === $slug) {
            return $post + ['index' => $index];
        }
    }
    return null;
}

function faq_items(?int $limit = null): array
{
    $items = store_get('faq');
    return $limit ? array_slice($items, 0, $limit) : $items;
}

function advantages(): array
{
    return store_get('advantages');
}

function az_date(string $date): string
{
    $months = ['Yanvar', 'Fevral', 'Mart', 'Aprel', 'May', 'İyun', 'İyul', 'Avqust', 'Sentyabr', 'Oktyabr', 'Noyabr', 'Dekabr'];
    $time = strtotime($date);
    return date('j', $time) . ' ' . $months[(int) date('n', $time) - 1] . ' ' . date('Y', $time);
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

/** Cloudflare-in IP diapazonları: https://www.cloudflare.com/ips/ */
const CLOUDFLARE_IPS = [
    '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22', '141.101.64.0/18',
    '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20', '197.234.240.0/22', '198.41.128.0/17',
    '162.158.0.0/15', '104.16.0.0/13', '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
    '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32', '2405:8100::/32',
    '2a06:98c0::/29', '2c0f:f248::/32',
];

/**
 * Ziyarətçinin IP ünvanı. Sayt Cloudflare arxasındadır: onda REMOTE_ADDR Cloudflare serveridir,
 * real ünvan isə CF-Connecting-IP başlığındadır. Başlığa yalnız sorğu Cloudflare-dən gələndə inanırıq.
 */
function client_ip(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $real = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? '';
    if (filter_var($real, FILTER_VALIDATE_IP) && ip_in_ranges($ip, CLOUDFLARE_IPS)) {
        return $real;
    }
    return $ip;
}

/** IP ünvanı CIDR diapazonlarından birinə düşürmü (IPv4 və IPv6). */
function ip_in_ranges(string $ip, array $ranges): bool
{
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return false;
    }
    $addr = inet_pton($ip);
    foreach ($ranges as $range) {
        [$subnet, $bits] = explode('/', $range);
        $net = inet_pton($subnet);
        if (strlen($net) !== strlen($addr)) {
            continue;
        }
        $bytes = intdiv((int) $bits, 8);
        $rest = (int) $bits % 8;
        if (substr($addr, 0, $bytes) !== substr($net, 0, $bytes)) {
            continue;
        }
        if ($rest === 0 || ((ord($addr[$bytes]) ^ ord($net[$bytes])) >> (8 - $rest)) === 0) {
            return true;
        }
    }
    return false;
}

/** Bir dəfəlik bildiriş (formadan sonra yönləndirmə zamanı). */
function flash(?string $key = null, ?array $value = null): ?array
{
    if ($value !== null) {
        $_SESSION['flash'][$key] = $value;
        return null;
    }
    $data = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $data;
}

function redirect(string $location, int $status = 302): void
{
    header('Location: ' . $location, true, $status);
    exit;
}

/**
 * Səhifəni ümumi şablonla göstərir.
 * Səhifə faylı $title, $description, $og_image, $active dəyişənlərini təyin edə bilər.
 */
function render(string $view, array $vars = [], int $status = 200): void
{
    http_response_code($status);
    extract($vars, EXTR_SKIP);
    ob_start();
    require ROOT . '/pages/' . $view . '.php';
    $content = ob_get_clean();
    require ROOT . '/includes/layout.php';
}

function partial(string $name, array $vars = []): void
{
    extract($vars, EXTR_SKIP);
    require ROOT . '/includes/partials/' . $name . '.php';
}

function nav_items(): array
{
    return [
        'haqqimizda'       => 'Haqqımızda',
        'sahibkarlar-ucun' => 'Sahibkarlar',
        'suruculer'        => 'Sürücülər',
        'blog'             => 'Blog',
        'faq'              => 'Faq',
        'elaqe'            => 'Əlaqə',
    ];
}
