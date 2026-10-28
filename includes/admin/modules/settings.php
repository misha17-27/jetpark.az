<?php
// Parametrlər (yalnız administrator): əlaqə, sosial şəbəkələr, e-poçt, Turnstile.

$fields = ['site_name', 'site_url', 'posts_per_page', 'phone', 'whatsapp', 'email', 'address', 'address_short', 'map_url', 'map_embed',
    'facebook', 'instagram', 'mail_to', 'mail_from', 'turnstile_site_key'];
$errors = [];
$current = [];
foreach ($fields as $f) {
    $current[$f] = (string) config($f, '');
}

if (is_post()) {
    verify_csrf();
    $in = [];
    foreach ($fields as $f) {
        $in[$f] = post_str($f, 500);
    }
    $in['site_url'] = rtrim($in['site_url'], '/');
    $in['whatsapp'] = preg_replace('/\D/', '', $in['whatsapp']);
    $in['posts_per_page'] = (string) max(1, min(60, (int) $in['posts_per_page']));
    if ($in['site_url'] !== '' && !preg_match('#^https?://[^\s/]+#', $in['site_url'])) {
        $errors['site_url'] = t('set.err_url');
    }
    foreach (['email', 'mail_to', 'mail_from'] as $f) {
        if ($in[$f] !== '' && !filter_var($in[$f], FILTER_VALIDATE_EMAIL)) {
            $errors[$f] = t('user.err_email');
        }
    }
    foreach (['map_url', 'map_embed', 'facebook', 'instagram'] as $f) {
        if ($in[$f] !== '' && !preg_match('#^https://#', $in[$f])) {
            $errors[$f] = 'https://…';
        }
    }
    if (!$errors) {
        // Gizli açar yalnız yenisi daxil edildikdə dəyişir.
        $secret = post_str('turnstile_secret_key', 200);
        store_update('settings', function (array $s) use ($in, $secret) {
            $s = array_merge($s, $in);
            if ($secret !== '') {
                $s['turnstile_secret_key'] = $secret;
            }
            if (($_POST['turnstile_disable'] ?? '') === '1') {
                $s['turnstile_site_key'] = '';
                $s['turnstile_secret_key'] = '';
            }
            return $s;
        });
        admin_flash('success', t('common.saved'));
        admin_redirect('settings');
    }
    $current = $in;
}

admin_page(t('nav.settings'), 'settings', function () use ($current, $errors) {
    $f = fn (string $name, string $label, array $opt = []) => field_text($name, $label, $current[$name], $opt + ['error' => $errors[$name] ?? null]);
    $hasSecret = (bool) config('turnstile_secret_key');
    ?>
    <form method="post" action="<?= e(admin_url('settings')) ?>" novalidate>
        <?= csrf_field() ?>
        <?php if ($errors): ?><div class="alert alert--error"><?= e(implode(' ', array_unique($errors))) ?></div><?php endif; ?>
        <div class="card">
            <h2 class="card__title" style="margin-bottom:20px"><?= e(t('set.general')) ?></h2>
            <div class="form-grid">
                <?= $f('site_name', t('set.site_name'), ['maxlength' => 60]) ?>
                <?= $f('site_url', t('set.site_url'), ['type' => 'url', 'hint' => t('set.site_url_hint')]) ?>
                <?= $f('posts_per_page', t('set.posts_per_page'), ['type' => 'number', 'min' => 1, 'max' => 60]) ?>
            </div>
        </div>
        <div class="card">
            <h2 class="card__title" style="margin-bottom:20px"><?= e(t('set.contacts')) ?></h2>
            <div class="form-grid">
                <?= $f('phone', t('set.phone'), ['type' => 'tel']) ?>
                <?= $f('whatsapp', t('set.whatsapp'), ['inputmode' => 'numeric', 'hint' => t('set.whatsapp_hint')]) ?>
                <?= $f('email', t('set.email'), ['type' => 'email']) ?>
                <?= $f('address', t('set.address')) ?>
                <?= $f('address_short', t('set.address_short')) ?>
                <?= $f('map_url', t('set.map_url'), ['type' => 'url']) ?>
                <div class="span-2"><?= $f('map_embed', t('set.map_embed'), ['type' => 'url']) ?></div>
            </div>
        </div>
        <div class="card">
            <h2 class="card__title" style="margin-bottom:20px"><?= e(t('set.social')) ?></h2>
            <div class="form-grid">
                <?= $f('facebook', 'Facebook', ['type' => 'url']) ?>
                <?= $f('instagram', 'Instagram', ['type' => 'url']) ?>
            </div>
        </div>
        <div class="card">
            <h2 class="card__title" style="margin-bottom:20px"><?= e(t('set.mail')) ?></h2>
            <div class="form-grid">
                <?= $f('mail_to', t('set.mail_to'), ['type' => 'email']) ?>
                <?= $f('mail_from', t('set.mail_from'), ['type' => 'email', 'hint' => t('set.mail_from_hint')]) ?>
            </div>
        </div>
        <div class="card">
            <h2 class="card__title" style="margin-bottom:8px"><?= e(t('set.security')) ?></h2>
            <p class="card__sub" style="margin-top:0"><?= e(t('set.turnstile_hint')) ?></p>
            <div class="form-grid">
                <?= $f('turnstile_site_key', t('set.site_key'), ['autocomplete' => 'off']) ?>
                <?= field_text('turnstile_secret_key', t('set.secret_key'), '', ['type' => 'password', 'autocomplete' => 'new-password', 'hint' => $hasSecret ? t('set.secret_keep') : null]) ?>
                <?php if ($hasSecret): ?><div class="span-2"><?= field_checkbox('turnstile_disable', t('common.delete') . ' (Turnstile)', false) ?></div><?php endif; ?>
            </div>
        </div>
        <div class="form-actions form-actions--sticky"><button class="btn btn-primary" type="submit"><?= e(t('common.save')) ?></button></div>
    </form>
    <?php
});
