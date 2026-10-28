<?php
/** Giriş və ilk quraşdırma səhifələri üçün şablon. @var string $title @var string $content */
$lang = admin_lang();
?><!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title) ?> — <?= e(config('site_name')) ?></title>
    <link rel="icon" href="<?= asset('img/favicon.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="<?= e(ADMIN_URL . 'assets/admin.css?v=' . filemtime(ROOT . '/admin/assets/admin.css')) ?>">
    <?php if (!empty($turnstile)): ?>
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <?php endif; ?>
</head>
<body class="auth">
<main class="auth-card">
    <div class="auth-card__head">
        <img src="<?= asset('img/logo-dark.svg') ?>" alt="<?= e(config('site_name')) ?>" width="175" height="36">
        <div class="lang-switch">
            <?php foreach (ADMIN_LANGS as $l): ?>
                <a href="?lang=<?= $l ?>" class="<?= $l === $lang ? 'is-active' : '' ?>"><?= strtoupper($l) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    <h1 class="auth-card__title"><?= e($title) ?></h1>
    <p class="auth-card__sub"><?= e($subtitle) ?></p>
    <?php foreach (admin_flash() as $f): ?>
        <div class="alert alert--<?= e($f['type']) ?>" role="status"><?= e($f['message']) ?></div>
    <?php endforeach; ?>
    <?= $content ?>
    <a class="auth-card__back" href="<?= e(url()) ?>">← <?= e(t('common.view_site')) ?></a>
</main>
</body>
</html>
