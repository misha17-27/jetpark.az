<?php
// Profil: öz adını, e-poçtunu və parolunu dəyişmək.

$me = current_user();
$form = ['name' => $me['name'], 'email' => $me['email']];
$errors = [];

if (is_post()) {
    verify_csrf();
    $in = [
        'name'      => post_str('name', 80),
        'email'     => post_str('email', 190),
        'password'  => (string) ($_POST['password'] ?? ''),
        'password2' => (string) ($_POST['password2'] ?? ''),
    ];
    $errors = validate_user_input($in, $me, false);
    // E-poçt və ya parol dəyişirsə, hazırkı parol tələb olunur.
    $sensitive = $in['password'] !== '' || mb_strtolower($in['email']) !== mb_strtolower($me['email']);
    if ($sensitive && !password_verify((string) ($_POST['current_password'] ?? ''), $me['password'])) {
        $errors['current_password'] = t('user.err_current');
    }
    if (!$errors) {
        $record = $me;
        $record['name'] = $in['name'];
        $record['email'] = $in['email'];
        if ($in['password'] !== '') {
            $record['password'] = password_hash($in['password'], PASSWORD_DEFAULT);
        }
        store_update('users', function (array $list) use ($record) {
            foreach ($list as &$u) {
                if ($u['id'] === $record['id']) {
                    $u = $record;
                }
            }
            return $list;
        });
        $_SESSION['admin_pwv'] = password_version($record);
        admin_flash('success', t('common.saved'));
        admin_redirect('profile');
    }
    $form = $in;
}

admin_page(t('profile.title'), 'profile', function () use ($form, $errors, $me) {
    ?>
    <form class="card" method="post" action="<?= e(admin_url('profile')) ?>" style="max-width:760px" novalidate>
        <?= csrf_field() ?>
        <div class="form-grid">
            <?= field_text('name', t('user.name'), $form['name'], ['required' => true, 'error' => $errors['name'] ?? null, 'autocomplete' => 'name']) ?>
            <?= field_text('email', t('user.email'), $form['email'], ['type' => 'email', 'required' => true, 'error' => $errors['email'] ?? null, 'autocomplete' => 'username']) ?>
            <div class="span-2"><p class="field-label"><?= e(t('user.role')) ?>: <span class="badge badge--draft"><?= e(t('role.' . $me['role'])) ?></span></p></div>
        </div>
        <h2 class="card__title" style="margin:10px 0 18px"><?= e(t('profile.password')) ?></h2>
        <div class="form-grid">
            <div class="span-2"><?= field_text('current_password', t('user.current_password'), '', ['type' => 'password', 'autocomplete' => 'current-password', 'error' => $errors['current_password'] ?? null]) ?></div>
            <?= field_text('password', t('user.new_password'), '', ['type' => 'password', 'autocomplete' => 'new-password', 'error' => $errors['password'] ?? null, 'hint' => t('user.password_keep')]) ?>
            <?= field_text('password2', t('user.password2'), '', ['type' => 'password', 'autocomplete' => 'new-password', 'error' => $errors['password2'] ?? null]) ?>
        </div>
        <?= submit_bar() ?>
    </form>
    <?php
});
