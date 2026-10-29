<?php
/**
 * Ümumi şablon. Səhifə faylından gələn dəyişənlər:
 * $content, $title (tam başlıq), $description, $og_image (assets/ daxilində), $active, $canonical, $og_type, $noindex
 */
$siteName    = config('site_name');
$fullTitle   = !empty($title) ? $title : $siteName;
$description = $description ?? '';
$canonical   = absolute_url($canonical ?? '');
$ogImage     = absolute_url('assets/' . ($og_image ?? 'img/home-car.webp'));
$active      = $active ?? '';

// Strukturlaşdırılmış məlumat: ana səhifədə sayt və şirkət, «Əlaqə»də şirkət, daxili səhifələrdə breadcrumb
// (yazılarda $crumb — yazının başlığı).
$jsonld = [];
if (empty($noindex)) {
    if ($canonical === absolute_url()) {
        $jsonld[] = ['@type' => 'WebSite', '@id' => absolute_url('#website'), 'name' => $siteName, 'url' => absolute_url(), 'inLanguage' => 'az'];
        $jsonld[] = business_jsonld();
    }
    if ($active === 'elaqe') {
        $jsonld[] = business_jsonld();
    }
    $navLabels = nav_items();
    if (isset($navLabels[$active])) {
        $crumbs = [['Ana səhifə', absolute_url()], [$navLabels[$active], absolute_url($active . '/')]];
        if (!empty($crumb)) {
            $crumbs[] = [$crumb, $canonical];
        }
        $jsonld[] = breadcrumb_jsonld($crumbs);
    }
}
?><!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>document.documentElement.classList.add('js');</script>
    <title><?= e($fullTitle) ?></title>
    <?php if ($description): ?>
    <meta name="description" content="<?= e($description) ?>">
    <?php endif; ?>
    <?php if (!empty($noindex)): ?>
    <meta name="robots" content="noindex">
    <?php else: ?>
    <meta name="robots" content="max-image-preview:large">
    <link rel="canonical" href="<?= e($canonical) ?>">
    <?php endif; ?>
    <meta property="og:locale" content="az_AZ">
    <meta property="og:type" content="<?= e($og_type ?? 'website') ?>">
    <meta property="og:site_name" content="<?= e($siteName) ?>">
    <meta property="og:title" content="<?= e($fullTitle) ?>">
    <?php if ($description): ?>
    <meta property="og:description" content="<?= e($description) ?>">
    <?php endif; ?>
    <meta property="og:url" content="<?= e($canonical) ?>">
    <meta property="og:image" content="<?= e($ogImage) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="theme-color" content="#1C1C1C">
    <link rel="icon" href="<?= asset('img/favicon.png') ?>" sizes="48x48" type="image/png">
    <link rel="apple-touch-icon" href="<?= asset('img/favicon.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Sora:wght@600;700&display=swap">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <?php foreach ($jsonld as $data): ?>
    <?= jsonld($data) ?>
    <?php endforeach; ?>
</head>
<body class="page-<?= e($active ?: 'home') ?>">
<a class="skip-link" href="#content">Məzmuna keç</a>

<header class="site-header">
    <div class="header-inner">
        <a class="logo" href="<?= url() ?>" aria-label="<?= e($siteName) ?> — ana səhifə">
            <img src="<?= asset('img/logo.svg') ?>" alt="<?= e($siteName) ?>" width="175" height="36">
        </a>
        <nav class="main-nav" aria-label="Əsas menyu">
            <ul>
                <?php foreach (nav_items() as $slug => $label): ?>
                    <li><a href="<?= url($slug . '/') ?>"<?= $active === $slug ? ' class="active" aria-current="page"' : '' ?>><?= e($label) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>
        <button class="menu-toggle" type="button" aria-controls="mobile-menu" aria-expanded="false" aria-label="Menyunu aç">
            <?= icon('bars') ?>
        </button>
    </div>
</header>

<div class="mobile-menu" id="mobile-menu" hidden>
    <div class="mobile-menu__backdrop" data-menu-close></div>
    <div class="mobile-menu__panel" role="dialog" aria-modal="true" aria-label="Menyu">
        <button class="mobile-menu__close" type="button" data-menu-close aria-label="Menyunu bağla"><?= icon('close') ?></button>
        <ul>
            <?php foreach (nav_items() as $slug => $label): ?>
                <li><a href="<?= url($slug . '/') ?>"<?= $active === $slug ? ' class="active" aria-current="page"' : '' ?>><?= e($label) ?></a></li>
            <?php endforeach; ?>
        </ul>
        <a class="mobile-menu__logo" href="<?= url() ?>"><img src="<?= asset('img/logo.svg') ?>" alt="<?= e($siteName) ?>" width="175" height="36"></a>
    </div>
</div>

<main id="content">
<?= $content ?>
</main>

<footer class="site-footer">
    <div class="footer-box">
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="<?= url() ?>"><img src="<?= asset('img/logo.svg') ?>" alt="<?= e($siteName) ?>" width="175" height="36" loading="lazy"></a>
            </div>
            <div class="footer-col">
                <h2 class="footer-title">Şirkət haqqında</h2>
                <ul class="footer-links">
                    <?php foreach (['haqqimizda' => 'Haqqımızda', 'blog' => 'Blog', 'faq' => 'Faq'] as $slug => $label): ?>
                        <li><a href="<?= url($slug . '/') ?>"<?= $active === $slug ? ' class="active"' : '' ?>><?= e($label) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="footer-col">
                <h2 class="footer-title">Xidmətlərimiz</h2>
                <ul class="footer-links">
                    <?php foreach (['sahibkarlar-ucun' => 'Sahibkarlar', 'suruculer' => 'Sürücülər'] as $slug => $label): ?>
                        <li><a href="<?= url($slug . '/') ?>"<?= $active === $slug ? ' class="active"' : '' ?>><?= e($label) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="footer-col">
                <h2 class="footer-title">Əlaqə</h2>
                <ul class="footer-contacts">
                    <li><a href="tel:<?= e(phone_link(config('phone'))) ?>"><?= circle_icon('phone') ?><?= e(config('phone')) ?></a></li>
                    <li><a href="mailto:<?= e(config('email')) ?>"><?= circle_icon('mail') ?><?= e(config('email')) ?></a></li>
                    <li><a href="<?= e(config('map_url')) ?>" target="_blank" rel="noopener"><?= circle_icon('pin') ?><?= e(config('address')) ?></a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <?php partial('social', ['style' => 'plain']) ?>
            <p class="copyright"><?= e(content('footer.copyright')) ?> Site by <a href="https://webline.az/" target="_blank" rel="noopener">Webline</a></p>
        </div>
    </div>
</footer>

<a class="whatsapp-float" href="https://api.whatsapp.com/send?phone=<?= e(config('whatsapp')) ?>" target="_blank" rel="noopener" aria-label="WhatsApp ilə yazın">
    <?= icon('whatsapp') ?>
</a>

<script src="<?= asset('js/main.js') ?>" defer></script>
</body>
</html>
