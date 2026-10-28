<?php
// İlk quraşdırma: hələ heç bir istifadəçi olmadıqda birinci administrator yaradılır.

$errors = [];
$in = ['name' => '', 'email' => ''];

if (is_post()) {
    verify_csrf();
    $in = [
        'name'      => post_str('name', 80),
        'email'     => post_str('email', 190),
        'password'  => (string) ($_POST['password'] ?? ''),
        'password2' => (string) ($_POST['password2'] ?? ''),
    ];
    $errors = validate_user_input($in, null, true);
    if (!$errors) {
        $user = [
            'id'         => new_id(),
            'name'       => $in['name'],
            'email'      => $in['email'],
            'password'   => password_hash($in['password'], PASSWORD_DEFAULT),
            'role'       => 'admin',
            'created_at' => date('Y-m-d H:i:s'),
            'last_login' => null,
        ];
        $created = false;
        store_update('users', function (array $list) use ($user, &$created) {
            if ($list) {
                return $list; // başqası artıq yaradıb
            }
            $created = true;
            return [$user];
        });
        if ($created) {
            admin_login($user);
        }
        admin_redirect('dashboard');
    }
}

$title = t('setup.title');
$subtitle = t('setup.subtitle');
ob_start();
?>
<form class="auth-form" method="post" action="<?= e(admin_url('setup')) ?>" novalidate>
    <?= csrf_field() ?>
    <?= field_text('name', t('user.name'), $in['name'], ['required' => true, 'error' => $errors['name'] ?? null, 'autocomplete' => 'name']) ?>
    <?= field_text('email', t('user.email'), $in['email'], ['type' => 'email', 'required' => true, 'error' => $errors['email'] ?? null, 'autocomplete' => 'username']) ?>
    <?= field_text('password', t('user.password'), '', ['type' => 'password', 'required' => true, 'error' => $errors['password'] ?? null, 'autocomplete' => 'new-password']) ?>
    <?= field_text('password2', t('user.password2'), '', ['type' => 'password', 'required' => true, 'error' => $errors['password2'] ?? null, 'autocomplete' => 'new-password']) ?>
    <button class="btn btn-dark btn-block" type="submit"><?= e(t('setup.submit')) ?></button>
</form>
<?php
$content = ob_get_clean();
require ROOT . '/includes/admin/auth-layout.php';
