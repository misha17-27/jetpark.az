<?php
// İstifadəçilər (yalnız administrator).

$id = (string) ($_GET['id'] ?? '');
$roles = ['admin' => t('role.admin'), 'editor' => t('role.editor')];

if ($action === 'delete' && is_post()) {
    verify_csrf();
    $target = find_user($id);
    $admins = array_filter(users(), fn ($u) => $u['role'] === 'admin');
    if (!$target) {
        admin_flash('error', t('common.not_found'));
    } elseif ($target['id'] === current_user()['id']) {
        admin_flash('error', t('user.err_self'));
    } elseif ($target['role'] === 'admin' && count($admins) <= 1) {
        admin_flash('error', t('user.err_last_admin'));
    } else {
        store_update('users', fn (array $list) => array_values(array_filter($list, fn ($u) => $u['id'] !== $id)));
        admin_flash('success', t('common.deleted'));
    }
    admin_redirect('users');
}

if ($action === 'edit') {
    $user = $id !== '' ? find_user($id) : null;
    if ($id !== '' && !$user) {
        admin_flash('error', t('common.not_found'));
        admin_redirect('users');
    }
    $isNew = !$user;
    $form = ['name' => $user['name'] ?? '', 'email' => $user['email'] ?? '', 'role' => $user['role'] ?? 'editor'];
    $errors = [];

    if (is_post()) {
        verify_csrf();
        $in = [
            'name'      => post_str('name', 80),
            'email'     => post_str('email', 190),
            'role'      => isset($roles[$_POST['role'] ?? '']) ? $_POST['role'] : 'editor',
            'password'  => (string) ($_POST['password'] ?? ''),
            'password2' => (string) ($_POST['password2'] ?? ''),
        ];
        $errors = validate_user_input($in, $user, $isNew);
        $admins = array_filter(users(), fn ($u) => $u['role'] === 'admin');
        if (!$isNew && $user['role'] === 'admin' && $in['role'] !== 'admin' && count($admins) <= 1) {
            $errors['role'] = t('user.err_last_admin');
        }
        if (!$errors) {
            $record = ($user ?? ['id' => new_id(), 'created_at' => date('Y-m-d H:i:s'), 'last_login' => null]);
            $record['name'] = $in['name'];
            $record['email'] = $in['email'];
            $record['role'] = $in['role'];
            if ($in['password'] !== '') {
                $record['password'] = password_hash($in['password'], PASSWORD_DEFAULT);
            }
            store_update('users', function (array $list) use ($record, $isNew) {
                if ($isNew) {
                    $list[] = $record;
                    return $list;
                }
                foreach ($list as &$u) {
                    if ($u['id'] === $record['id']) {
                        $u = $record;
                    }
                }
                return $list;
            });
            // Öz parolunu dəyişdikdə sessiya davam etsin.
            if ($record['id'] === current_user()['id']) {
                $_SESSION['admin_pwv'] = password_version($record);
            }
            admin_flash('success', t('common.saved'));
            admin_redirect('users');
        }
        $form = $in;
    }

    admin_page($isNew ? t('users.add') : t('users.edit'), 'users', function () use ($form, $errors, $isNew, $roles, $id) {
        ?>
        <p style="margin-bottom:18px"><a class="btn btn-ghost btn-sm" href="<?= e(admin_url('users')) ?>">← <?= e(t('common.back')) ?></a></p>
        <form class="card" method="post" action="<?= e(admin_url('users', ['a' => 'edit', 'id' => $id])) ?>" style="max-width:760px" novalidate>
            <?= csrf_field() ?>
            <div class="form-grid">
                <?= field_text('name', t('user.name'), $form['name'], ['required' => true, 'error' => $errors['name'] ?? null]) ?>
                <?= field_text('email', t('user.email'), $form['email'], ['type' => 'email', 'required' => true, 'error' => $errors['email'] ?? null, 'autocomplete' => 'off']) ?>
                <div class="span-2"><?= field_select('role', t('user.role'), $form['role'], $roles, ['hint' => t('role.hint')]) ?>
                    <?php if (isset($errors['role'])): ?><p class="field-error" style="margin-top:-12px;margin-bottom:16px"><?= e($errors['role']) ?></p><?php endif; ?></div>
                <?= field_text('password', t('user.password'), '', ['type' => 'password', 'autocomplete' => 'new-password', 'error' => $errors['password'] ?? null, 'hint' => $isNew ? null : t('user.password_keep')]) ?>
                <?= field_text('password2', t('user.password2'), '', ['type' => 'password', 'autocomplete' => 'new-password', 'error' => $errors['password2'] ?? null]) ?>
            </div>
            <?= submit_bar('users') ?>
        </form>
        <?php
    });
    return;
}

$list = users();
admin_page(t('nav.users'), 'users', function () use ($list) {
    $me = current_user()['id'];
    ?>
    <div class="card">
        <div class="toolbar">
            <span class="spacer"></span>
            <a class="btn btn-primary" href="<?= e(admin_url('users', ['a' => 'edit'])) ?>"><?= admin_icon('plus') ?><?= e(t('users.add')) ?></a>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th><?= e(t('user.name')) ?></th><th><?= e(t('user.role')) ?></th><th><?= e(t('user.last_login')) ?></th><th class="col-actions"></th></tr></thead>
                <tbody>
                <?php foreach ($list as $u): ?>
                    <tr>
                        <td>
                            <a class="row-title" href="<?= e(admin_url('users', ['a' => 'edit', 'id' => $u['id']])) ?>"><?= e($u['name']) ?></a>
                            <span class="row-sub"><?= e($u['email']) ?></span>
                        </td>
                        <td><span class="badge badge--<?= $u['role'] === 'admin' ? 'new' : 'draft' ?>"><?= e(t('role.' . $u['role'])) ?></span></td>
                        <td class="nowrap"><?= e(format_dt($u['last_login'] ?? null)) ?></td>
                        <td class="col-actions">
                            <a class="icon-btn" href="<?= e(admin_url('users', ['a' => 'edit', 'id' => $u['id']])) ?>" title="<?= e(t('common.edit')) ?>"><?= admin_icon('edit') ?></a>
                            <?php if ($u['id'] !== $me): ?>
                                <form class="inline-form" method="post" action="<?= e(admin_url('users', ['a' => 'delete', 'id' => $u['id']])) ?>" data-confirm>
                                    <?= csrf_field() ?>
                                    <button class="icon-btn is-danger" type="submit" title="<?= e(t('common.delete')) ?>"><?= admin_icon('trash') ?></button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
});
