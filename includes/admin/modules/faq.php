<?php
// FAQ: siyahı, sıralama, yeni sual, redaktə, silmə.

$id = (string) ($_GET['id'] ?? '');

if (is_post() && in_array($action, ['delete', 'up', 'down'], true)) {
    verify_csrf();
    store_update('faq', function (array $list) use ($id, $action) {
        $list = array_values($list);
        foreach ($list as $i => $item) {
            if ($item['id'] !== $id) {
                continue;
            }
            if ($action === 'delete') {
                unset($list[$i]);
            } elseif ($action === 'up' && $i > 0) {
                [$list[$i - 1], $list[$i]] = [$list[$i], $list[$i - 1]];
            } elseif ($action === 'down' && $i < count($list) - 1) {
                [$list[$i + 1], $list[$i]] = [$list[$i], $list[$i + 1]];
            }
            break;
        }
        return array_values($list);
    });
    if ($action === 'delete') {
        admin_flash('success', t('common.deleted'));
    }
    admin_redirect('faq');
}

if ($action === 'edit') {
    $item = null;
    foreach (faq_items() as $f) {
        if ($f['id'] === $id) {
            $item = $f;
        }
    }
    if ($id !== '' && !$item) {
        admin_flash('error', t('common.not_found'));
        admin_redirect('faq');
    }
    $isNew = !$item;
    $item ??= ['id' => '', 'q' => '', 'a' => ''];
    $error = '';

    if (is_post()) {
        verify_csrf();
        $item['q'] = post_str('q', 300);
        $item['a'] = sanitize_html((string) ($_POST['a'] ?? ''));
        if ($item['q'] === '' || trim(strip_tags($item['a'])) === '') {
            $error = t('faq.err');
        } else {
            if ($isNew) {
                $item['id'] = new_id();
            }
            $saved = $item;
            store_update('faq', function (array $list) use ($saved, $isNew) {
                if ($isNew) {
                    $list[] = $saved;
                    return $list;
                }
                foreach ($list as &$f) {
                    if ($f['id'] === $saved['id']) {
                        $f = $saved;
                    }
                }
                return $list;
            });
            admin_flash('success', t('common.saved'));
            admin_redirect('faq');
        }
    }

    admin_page($isNew ? t('faq.add') : t('faq.edit'), 'faq', function () use ($item, $error) {
        ?>
        <p style="margin-bottom:18px"><a class="btn btn-ghost btn-sm" href="<?= e(admin_url('faq')) ?>">← <?= e(t('common.back')) ?></a></p>
        <form class="card" method="post" action="<?= e(admin_url('faq', ['a' => 'edit', 'id' => $item['id']])) ?>" style="max-width:900px">
            <?= csrf_field() ?>
            <?php if ($error): ?><div class="alert alert--error"><?= e($error) ?></div><?php endif; ?>
            <?= field_text('q', t('faq.question'), $item['q'], ['maxlength' => 300, 'required' => true]) ?>
            <div class="field">
                <label for="f-a"><?= e(t('faq.answer')) ?></label>
                <textarea id="f-a" name="a" data-editor data-editor-small><?= e($item['a']) ?></textarea>
            </div>
            <?= submit_bar('faq') ?>
        </form>
        <?php
    });
    return;
}

$items = faq_items();
admin_page(t('nav.faq'), 'faq', function () use ($items) {
    ?>
    <div class="card">
        <div class="toolbar">
            <p class="field-hint" style="margin:0"><?= e(t('faq.home_hint', (int) content('home.faq_count', 5))) ?></p>
            <span class="spacer"></span>
            <a class="btn btn-primary" href="<?= e(admin_url('faq', ['a' => 'edit'])) ?>"><?= admin_icon('plus') ?><?= e(t('faq.add')) ?></a>
        </div>
        <?php if (!$items): ?>
            <p class="empty"><?= e(t('common.empty')) ?></p>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>#</th><th><?= e(t('faq.question')) ?></th><th class="col-actions"></th></tr></thead>
                    <tbody>
                    <?php $last = count($items) - 1; foreach (array_values($items) as $i => $f): ?>
                        <tr>
                            <td style="width:40px"><b><?= $i + 1 ?></b></td>
                            <td>
                                <a class="row-title" href="<?= e(admin_url('faq', ['a' => 'edit', 'id' => $f['id']])) ?>"><?= e($f['q']) ?></a>
                                <span class="row-sub"><?= e(excerpt($f['a'], 110)) ?></span>
                            </td>
                            <td class="col-actions">
                                <?php foreach (['up' => 'arrow-up', 'down' => 'arrow-down'] as $dir => $ico): ?>
                                    <form class="inline-form" method="post" action="<?= e(admin_url('faq', ['a' => $dir, 'id' => $f['id']])) ?>">
                                        <?= csrf_field() ?>
                                        <button class="icon-btn" type="submit" title="<?= e(t('common.' . $dir)) ?>"<?= ($dir === 'up' && $i === 0) || ($dir === 'down' && $i === $last) ? ' disabled' : '' ?>><?= admin_icon($ico) ?></button>
                                    </form>
                                <?php endforeach; ?>
                                <a class="icon-btn" href="<?= e(admin_url('faq', ['a' => 'edit', 'id' => $f['id']])) ?>" title="<?= e(t('common.edit')) ?>"><?= admin_icon('edit') ?></a>
                                <form class="inline-form" method="post" action="<?= e(admin_url('faq', ['a' => 'delete', 'id' => $f['id']])) ?>" data-confirm>
                                    <?= csrf_field() ?>
                                    <button class="icon-btn is-danger" type="submit" title="<?= e(t('common.delete')) ?>"><?= admin_icon('trash') ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <?php
});
