<?php
// Müraciətlər: siyahı, filtr, ətraflı baxış, status, qeyd, silmə, CSV.

const APP_STATUSES = ['new', 'processing', 'done'];

function app_update(string $id, callable $fn): bool
{
    $found = false;
    store_update('applications', function (array $list) use ($id, $fn, &$found) {
        foreach ($list as $i => $a) {
            if ($a['id'] === $id) {
                $found = true;
                $result = $fn($a);
                if ($result === null) {
                    unset($list[$i]);
                } else {
                    $list[$i] = $result;
                }
            }
        }
        return array_values($list);
    });
    return $found;
}

function app_filtered(array $apps, array $f): array
{
    return array_values(array_filter($apps, function ($a) use ($f) {
        if ($f['status'] && $a['status'] !== $f['status']) {
            return false;
        }
        if ($f['type'] && $a['type'] !== $f['type']) {
            return false;
        }
        if ($f['q'] !== '') {
            $hay = mb_strtolower($a['name'] . ' ' . $a['surname'] . ' ' . $a['phone'] . ' ' . preg_replace('/\D/', '', $a['phone']));
            if (!str_contains($hay, mb_strtolower($f['q']))) {
                return false;
            }
        }
        return true;
    }));
}

$filters = [
    'status' => in_array($_GET['status'] ?? '', APP_STATUSES, true) ? $_GET['status'] : '',
    'type'   => in_array($_GET['type'] ?? '', ['Sahibkar', 'Sürücü'], true) ? $_GET['type'] : '',
    'q'      => trim(mb_substr((string) ($_GET['q'] ?? ''), 0, 60)),
];
$id = (string) ($_GET['id'] ?? '');

// ---------- Əməliyyatlar (POST) ----------
if (is_post()) {
    verify_csrf();
    $back = (string) ($_POST['back'] ?? '');
    $back = str_starts_with($back, ADMIN_URL) ? $back : admin_url('applications');

    if ($action === 'status' && in_array($_POST['status'] ?? '', APP_STATUSES, true)) {
        app_update($id, fn ($a) => ['status' => $_POST['status']] + $a);
        admin_flash('success', t('common.saved'));
    } elseif ($action === 'note') {
        app_update($id, fn ($a) => ['note' => post_str('note', 2000)] + $a);
        admin_flash('success', t('common.saved'));
    } elseif ($action === 'delete') {
        app_update($id, fn () => null);
        admin_flash('success', t('common.deleted'));
        $back = admin_url('applications');
    }
    redirect($back, 303);
}

// ---------- CSV ixracı ----------
if ($action === 'export') {
    $rows = app_filtered(store_get('applications'), $filters);
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="jetpark-muracietler-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    // $escape açıq verilir (PHP 8.4-də verilməsə xəbərdarlıq olur); '' — standart CSV.
    fputcsv($out, [t('app.date'), t('app.type'), t('user.name'), 'Soyad', t('app.phone'), t('app.source'), t('app.status'), t('app.note')], ';', '"', '');
    foreach ($rows as $a) {
        $line = [$a['created_at'], type_label($a['type']), $a['name'], $a['surname'], $a['phone'], $a['source'], t('status.' . $a['status']), $a['note'] ?? ''];
        // Excel-də formula kimi icra olunmasın.
        $line = array_map(fn ($v) => preg_match('/^[=+\-@]/', (string) $v) && !preg_match('/^\+?[\d\s()\-]+$/', (string) $v) ? "'" . $v : $v, $line);
        fputcsv($out, $line, ';', '"', '');
    }
    fclose($out);
    exit;
}

// ---------- Ətraflı baxış ----------
if ($action === 'view') {
    $app = null;
    foreach (store_get('applications') as $a) {
        if ($a['id'] === $id) {
            $app = $a;
        }
    }
    if (!$app) {
        admin_flash('error', t('common.not_found'));
        admin_redirect('applications');
    }
    // Açılan yeni müraciət avtomatik "Baxılır" olur.
    if ($app['status'] === 'new') {
        app_update($id, fn ($a) => ['status' => 'processing'] + $a);
        $app['status'] = 'processing';
    }
    $digits = preg_replace('/\D/', '', $app['phone']);
    $wa = str_starts_with($digits, '994') ? $digits : (str_starts_with($digits, '0') ? '994' . substr($digits, 1) : $digits);

    admin_page(t('app.details') . ': ' . $app['name'] . ' ' . $app['surname'], 'applications', function () use ($app, $wa) {
        $self = admin_url('applications', ['a' => 'view', 'id' => $app['id']]);
        ?>
        <p style="margin-bottom:18px"><a class="btn btn-ghost btn-sm" href="<?= e(admin_url('applications')) ?>">← <?= e(t('common.back')) ?></a></p>
        <div class="app-detail">
            <div class="card">
                <dl class="kv">
                    <div><span><?= e(t('app.name')) ?></span><b><?= e($app['name'] . ' ' . $app['surname']) ?></b></div>
                    <div><span><?= e(t('app.phone')) ?></span><b><a href="tel:<?= e(phone_link($app['phone'])) ?>"><?= e($app['phone']) ?></a></b></div>
                    <div><span><?= e(t('app.type')) ?></span><b><?= type_badge($app['type']) ?></b></div>
                    <div><span><?= e(t('app.source')) ?></span><b><?= e($app['source']) ?></b></div>
                    <div><span><?= e(t('app.date')) ?></span><b><?= e(format_dt($app['created_at'])) ?></b></div>
                    <div><span><?= e(t('app.status')) ?></span><b><?= status_badge($app['status']) ?></b></div>
                    <div><span><?= e(t('app.ip')) ?></span><b><?= e($app['ip'] ?: '—') ?></b></div>
                </dl>
                <div class="actions-row">
                    <a class="btn btn-primary" href="tel:<?= e(phone_link($app['phone'])) ?>"><?= admin_icon('phone') ?><?= e(t('app.call')) ?></a>
                    <a class="btn btn-outline" href="https://wa.me/<?= e($wa) ?>" target="_blank" rel="noopener"><?= admin_icon('message') ?><?= e(t('app.whatsapp')) ?></a>
                </div>
            </div>
            <div class="stack">
                <form class="card" method="post" action="<?= e(admin_url('applications', ['a' => 'status', 'id' => $app['id']])) ?>">
                    <?= csrf_field() ?><input type="hidden" name="back" value="<?= e($self) ?>">
                    <?= field_select('status', t('app.status'), $app['status'], array_combine(APP_STATUSES, array_map(fn ($s) => t('status.' . $s), APP_STATUSES))) ?>
                    <button class="btn btn-primary" type="submit"><?= e(t('common.save')) ?></button>
                </form>
                <form class="card" method="post" action="<?= e(admin_url('applications', ['a' => 'note', 'id' => $app['id']])) ?>">
                    <?= csrf_field() ?><input type="hidden" name="back" value="<?= e($self) ?>">
                    <?= field_textarea('note', t('app.note'), $app['note'] ?? '', ['rows' => 4, 'hint' => t('app.note_hint'), 'maxlength' => 2000]) ?>
                    <button class="btn btn-outline" type="submit"><?= e(t('common.save')) ?></button>
                </form>
                <form method="post" action="<?= e(admin_url('applications', ['a' => 'delete', 'id' => $app['id']])) ?>" data-confirm>
                    <?= csrf_field() ?>
                    <button class="btn btn-danger" type="submit"><?= admin_icon('trash') ?><?= e(t('common.delete')) ?></button>
                </form>
            </div>
        </div>
        <?php
    });
    return;
}

// ---------- Siyahı ----------
$all = store_get('applications');
$counts = ['' => count($all)];
foreach (APP_STATUSES as $s) {
    $counts[$s] = count(array_filter($all, fn ($a) => $a['status'] === $s));
}
$rows = app_filtered($all, $filters);
$perPage = 25;
$pages = max(1, (int) ceil(count($rows) / $perPage));
$page = min($pages, max(1, (int) ($_GET['p'] ?? 1)));
$rows = array_slice($rows, ($page - 1) * $perPage, $perPage);
$params = array_filter($filters, fn ($v) => $v !== '');

admin_page(t('nav.applications'), 'applications', function () use ($rows, $counts, $filters, $params, $page, $pages) {
    $self = admin_url('applications', $params + ($page > 1 ? ['p' => $page] : []));
    ?>
    <div class="card">
        <div class="toolbar">
            <nav class="tabs">
                <a href="<?= e(admin_url('applications', array_diff_key($params, ['status' => 1]))) ?>" class="<?= $filters['status'] === '' ? 'is-active' : '' ?>"><?= e(t('common.all')) ?><b><?= $counts[''] ?></b></a>
                <?php foreach (APP_STATUSES as $s): ?>
                    <a href="<?= e(admin_url('applications', ['status' => $s] + $params)) ?>" class="<?= $filters['status'] === $s ? 'is-active' : '' ?>"><?= e(t('status.' . $s)) ?><b><?= $counts[$s] ?></b></a>
                <?php endforeach; ?>
            </nav>
            <span class="spacer"></span>
            <form class="toolbar" method="get" action="<?= e(ADMIN_URL) ?>" style="margin:0">
                <input type="hidden" name="r" value="applications">
                <?php if ($filters['status']): ?><input type="hidden" name="status" value="<?= e($filters['status']) ?>"><?php endif; ?>
                <select name="type" onchange="this.form.submit()" aria-label="<?= e(t('app.type')) ?>">
                    <option value=""><?= e(t('app.type')) ?>: <?= e(t('common.all')) ?></option>
                    <option value="Sahibkar"<?= $filters['type'] === 'Sahibkar' ? ' selected' : '' ?>><?= e(t('app.type_owner')) ?></option>
                    <option value="Sürücü"<?= $filters['type'] === 'Sürücü' ? ' selected' : '' ?>><?= e(t('app.type_driver')) ?></option>
                </select>
                <input type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="<?= e(t('app.search_ph')) ?>">
                <button class="btn btn-outline" type="submit"><?= e(t('common.search')) ?></button>
            </form>
            <a class="btn btn-outline" href="<?= e(admin_url('applications', ['a' => 'export'] + $params)) ?>"><?= admin_icon('download') ?><?= e(t('app.export')) ?></a>
        </div>

        <?php if (!$rows): ?>
            <p class="empty"><?= e(t('dash.no_apps')) ?></p>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr>
                        <th><?= e(t('app.name')) ?></th><th><?= e(t('app.phone')) ?></th><th><?= e(t('app.type')) ?></th>
                        <th><?= e(t('app.source')) ?></th><th><?= e(t('app.date')) ?></th><th><?= e(t('app.status')) ?></th><th class="col-actions"></th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($rows as $a): ?>
                        <tr class="<?= $a['status'] === 'new' ? 'is-new' : '' ?>">
                            <td>
                                <a class="row-title" href="<?= e(admin_url('applications', ['a' => 'view', 'id' => $a['id']])) ?>"><?= e($a['name'] . ' ' . $a['surname']) ?></a>
                                <?php if (!empty($a['note'])): ?><span class="row-sub">✎ <?= e(mb_strimwidth($a['note'], 0, 60, '…')) ?></span><?php endif; ?>
                            </td>
                            <td class="nowrap"><a href="tel:<?= e(phone_link($a['phone'])) ?>"><?= e($a['phone']) ?></a></td>
                            <td><?= type_badge($a['type']) ?></td>
                            <td><?= e($a['source']) ?></td>
                            <td class="nowrap"><?= e(format_dt($a['created_at'])) ?></td>
                            <td>
                                <form class="inline-form" method="post" action="<?= e(admin_url('applications', ['a' => 'status', 'id' => $a['id']])) ?>">
                                    <?= csrf_field() ?><input type="hidden" name="back" value="<?= e($self) ?>">
                                    <select name="status" onchange="this.form.submit()" class="status-select" aria-label="<?= e(t('app.status')) ?>">
                                        <?php foreach (APP_STATUSES as $s): ?>
                                            <option value="<?= $s ?>"<?= $a['status'] === $s ? ' selected' : '' ?>><?= e(t('status.' . $s)) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </td>
                            <td class="col-actions">
                                <a class="icon-btn" href="<?= e(admin_url('applications', ['a' => 'view', 'id' => $a['id']])) ?>" title="<?= e(t('common.view')) ?>"><?= admin_icon('eye') ?></a>
                                <form class="inline-form" method="post" action="<?= e(admin_url('applications', ['a' => 'delete', 'id' => $a['id']])) ?>" data-confirm>
                                    <?= csrf_field() ?>
                                    <button class="icon-btn is-danger" type="submit" title="<?= e(t('common.delete')) ?>"><?= admin_icon('trash') ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?= pager($page, $pages, 'applications', $params) ?>
        <?php endif; ?>
    </div>
    <?php
});
