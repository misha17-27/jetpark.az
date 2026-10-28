<?php
// Giriş səhifəsi.

$turnstile = turnstile_enabled();
$email = '';
$error = '';

if (is_post()) {
    verify_csrf();
    $email = post_str('email', 190);
    $password = (string) ($_POST['password'] ?? '');

    if ($minutes = login_locked_minutes()) {
        $error = t('login.locked', $minutes);
    } elseif ($turnstile && !turnstile_verify($_POST['cf-turnstile-response'] ?? null)) {
        $error = t('login.captcha');
    } else {
        $user = find_user_by_email($email);
        // İstifadəçi tapılmasa da hash yoxlanılır ki, cavab müddəti fərqlənməsin.
        $hash = $user['password'] ?? '$2y$10$GgeJwUWL1GOhExxJHKuLrOVDd3jDYRR9X.UY.BWYhRzPG/es5gyjO';
        if (password_verify($password, $hash) && $user) {
            login_clear_failures();
            if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                store_update('users', function (array $list) use ($user, $password) {
                    foreach ($list as &$u) {
                        if ($u['id'] === $user['id']) {
                            $u['password'] = password_hash($password, PASSWORD_DEFAULT);
                        }
                    }
                    return $list;
                });
                $user = find_user($user['id']);
            }
            admin_login($user);
            $next = (string) ($_SESSION['admin_next'] ?? '');
            unset($_SESSION['admin_next']);
            redirect(str_starts_with($next, ADMIN_URL) ? $next : admin_url(), 303);
        }
        login_register_failure();
        usleep(400000);
        $error = ($minutes = login_locked_minutes()) ? t('login.locked', $minutes) : t('login.failed');
    }
} elseif (($_GET['r'] ?? '') !== 'login' && ($_GET['r'] ?? '') !== '') {
    // Girişdən sonra istənilən səhifəyə qayıtmaq üçün.
    $_SESSION['admin_next'] = $_SERVER['REQUEST_URI'] ?? '';
}

$title = t('login.title');
$subtitle = t('login.subtitle');
ob_start();
?>
<form class="auth-form" method="post" action="<?= e(admin_url('login')) ?>" novalidate>
    <?= csrf_field() ?>
    <?php if ($error): ?><div class="alert alert--error" role="alert"><?= e($error) ?></div><?php endif; ?>
    <div class="field">
        <label for="email"><?= e(t('login.email')) ?></label>
        <input id="email" type="email" name="email" value="<?= e($email) ?>" autocomplete="username" required autofocus>
    </div>
    <div class="field">
        <label for="password"><?= e(t('login.password')) ?></label>
        <input id="password" type="password" name="password" autocomplete="current-password" required>
    </div>
    <?php if ($turnstile): ?>
        <div class="cf-turnstile" data-sitekey="<?= e(config('turnstile_site_key')) ?>" data-language="<?= admin_lang() ?>"></div>
    <?php endif; ?>
    <button class="btn btn-dark btn-block" type="submit"><?= e(t('login.submit')) ?></button>
</form>
<?php
$content = ob_get_clean();
require ROOT . '/includes/admin/auth-layout.php';
