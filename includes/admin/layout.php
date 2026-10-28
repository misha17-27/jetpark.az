<?php
/** @var string $title @var string $active @var string $content @var array $headerActions */
$user = current_user();
$newCount = new_applications_count();
$nav = [
    t('nav.main') => [
        'dashboard'    => ['home', t('nav.dashboard')],
        'applications' => ['inbox', t('nav.applications'), $newCount],
    ],
    t('nav.content') => [
        'posts'      => ['file', t('nav.posts')],
        'faq'        => ['help', t('nav.faq')],
        'advantages' => ['star', t('nav.advantages')],
        'about'      => ['users', t('nav.about')],
        'owners'     => ['briefcase', t('nav.owners')],
        'drivers'    => ['car', t('nav.drivers')],
    ],
    t('nav.site') => array_filter([
        'texts'    => ['sliders', t('nav.texts')],
        'seo'      => ['globe', t('nav.seo')],
        'settings' => is_admin() ? ['settings', t('nav.settings')] : null,
        'users'    => is_admin() ? ['shield', t('nav.users')] : null,
    ]),
];
$lang = admin_lang();
?><!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title) ?> — <?= e(config('site_name')) ?> <?= e(t('panel.admin')) ?></title>
    <link rel="icon" href="<?= asset('img/favicon.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="<?= e(ADMIN_URL . 'assets/admin.css?v=' . filemtime(ROOT . '/admin/assets/admin.css')) ?>">
</head>
<body class="admin">
<aside class="sidebar" id="sidebar">
    <a class="sidebar__brand" href="<?= e(admin_url()) ?>">
        <img src="<?= asset('img/logo.svg') ?>" alt="<?= e(config('site_name')) ?>" width="140" height="29">
        <span><?= e(t('panel.admin')) ?></span>
    </a>
    <nav class="sidebar__nav">
        <?php foreach ($nav as $group => $items): ?>
            <p class="sidebar__group"><?= e($group) ?></p>
            <?php foreach ($items as $route => $item): ?>
                <a class="sidebar__link<?= $active === $route ? ' is-active' : '' ?>" href="<?= e(admin_url($route)) ?>">
                    <?= admin_icon($item[0]) ?><span><?= e($item[1]) ?></span>
                    <?php if (!empty($item[2])): ?><b class="sidebar__count"><?= (int) $item[2] ?></b><?php endif; ?>
                </a>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </nav>
    <div class="sidebar__user">
        <a class="sidebar__profile" href="<?= e(admin_url('profile')) ?>">
            <strong><?= e($user['name']) ?></strong>
            <span><?= e($user['email']) ?></span>
        </a>
        <span class="role-pill"><?= e(t('role.' . $user['role'])) ?></span>
        <div class="sidebar__bottom">
            <div class="lang-switch lang-switch--dark">
                <?php foreach (ADMIN_LANGS as $l): ?>
                    <a href="?lang=<?= $l ?>" class="<?= $l === $lang ? 'is-active' : '' ?>"><?= strtoupper($l) ?></a>
                <?php endforeach; ?>
            </div>
            <form method="post" action="<?= e(admin_url('logout')) ?>">
                <?= csrf_field() ?>
                <button class="sidebar__logout" type="submit">← <?= e(t('nav.logout')) ?></button>
            </form>
        </div>
    </div>
</aside>
<div class="sidebar-backdrop" data-sidebar-close></div>

<div class="main">
    <header class="topbar">
        <button class="topbar__menu" type="button" data-sidebar-open aria-label="Menu"><?= admin_icon('menu') ?></button>
        <h1 class="topbar__title"><?= e($title) ?></h1>
        <div class="topbar__actions">
            <?php foreach ($headerActions as $a): ?>
                <a class="btn <?= e($a['class'] ?? 'btn-outline') ?>" href="<?= e($a['href']) ?>"<?= !empty($a['blank']) ? ' target="_blank" rel="noopener"' : '' ?>><?= isset($a['icon']) ? admin_icon($a['icon']) : '' ?><?= e($a['label']) ?></a>
            <?php endforeach; ?>
            <a class="btn btn-outline" href="<?= e(url()) ?>" target="_blank" rel="noopener"><?= admin_icon('external') ?><?= e(t('common.view_site')) ?></a>
        </div>
    </header>

    <div class="content">
        <?php foreach (admin_flash() as $f): ?>
            <div class="alert alert--<?= e($f['type']) ?>" role="status"><?= e($f['message']) ?></div>
        <?php endforeach; ?>
        <?= $content ?>
    </div>
</div>

<script>window.ADMIN_I18N = <?= json_encode([
    'confirm' => t('common.confirm_delete'),
    'chars'   => t('seo.chars'),
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;</script>
<script src="<?= e(ADMIN_URL . 'assets/admin.js?v=' . filemtime(ROOT . '/admin/assets/admin.js')) ?>" defer></script>
</body>
</html>
