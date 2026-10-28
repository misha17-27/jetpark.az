<?php
// İdarə paneli: statistika, son müraciətlər, tez keçidlər, server vəziyyəti.

$apps = store_get('applications');
$monthStart = date('Y-m-01 00:00:00');
$newCount = count(array_filter($apps, fn ($a) => ($a['status'] ?? 'new') === 'new'));
$monthCount = count(array_filter($apps, fn ($a) => $a['created_at'] >= $monthStart));
$byType = ['Sahibkar' => 0, 'Sürücü' => 0];
foreach ($apps as $a) {
    if (isset($byType[$a['type']])) {
        $byType[$a['type']]++;
    }
}
$recent = array_slice($apps, 0, 6);
$total = count($apps);

$uploadsDir = ROOT . '/assets/uploads';
$gd = function_exists('gd_info') ? gd_info() : [];
$server = [
    t('dash.php')        => [PHP_VERSION, version_compare(PHP_VERSION, '8.0', '>=')],
    t('dash.storage')    => [is_writable(ROOT . '/storage') ? t('dash.ok') : t('dash.fail'), is_writable(ROOT . '/storage')],
    t('dash.uploads')    => [is_writable($uploadsDir) ? t('dash.ok') : t('dash.fail'), is_writable($uploadsDir)],
    t('dash.mail')       => [function_exists('mail') ? t('dash.ok') : t('dash.fail'), function_exists('mail')],
    t('dash.gd')         => [!empty($gd['WebP Support']) ? t('dash.ok') : t('dash.fail'), !empty($gd['WebP Support'])],
    t('dash.upload_max') => [upload_max_mb() . ' MB', null],
    t('dash.data_size')  => [format_bytes(dir_size(ROOT . '/storage') + dir_size($uploadsDir)), null],
];
$free = @disk_free_space(ROOT);
if ($free) {
    $server[t('dash.disk')] = [format_bytes($free), null];
}

admin_page(t('nav.dashboard'), 'dashboard', function () use ($apps, $newCount, $monthCount, $total, $byType, $recent, $server) {
    ?>
    <div class="stats">
        <a class="stat stat--accent" href="<?= e(admin_url('applications', ['status' => 'new'])) ?>"><strong><?= $newCount ?></strong><span><?= e(t('dash.new')) ?></span></a>
        <a class="stat" href="<?= e(admin_url('applications')) ?>"><strong><?= $monthCount ?></strong><span><?= e(t('dash.month')) ?></span></a>
        <a class="stat" href="<?= e(admin_url('applications')) ?>"><strong><?= $total ?></strong><span><?= e(t('dash.total')) ?></span></a>
        <a class="stat" href="<?= e(admin_url('posts')) ?>"><strong><?= count(all_posts()) ?></strong><span><?= e(t('dash.posts')) ?></span></a>
        <a class="stat" href="<?= e(admin_url('faq')) ?>"><strong><?= count(faq_items()) ?></strong><span><?= e(t('dash.faq')) ?></span></a>
    </div>

    <div class="dash-grid">
        <div class="card">
            <div class="card__head">
                <h2 class="card__title"><?= e(t('dash.recent')) ?></h2>
                <a class="btn btn-outline btn-sm" href="<?= e(admin_url('applications')) ?>"><?= e(t('dash.all')) ?> →</a>
            </div>
            <?php if (!$recent): ?>
                <p class="empty"><?= e(t('dash.no_apps')) ?></p>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th><?= e(t('app.name')) ?></th><th><?= e(t('app.phone')) ?></th><th><?= e(t('app.type')) ?></th><th><?= e(t('app.status')) ?></th><th><?= e(t('app.date')) ?></th></tr></thead>
                        <tbody>
                        <?php foreach ($recent as $a): ?>
                            <tr class="<?= $a['status'] === 'new' ? 'is-new' : '' ?>">
                                <td><a class="row-title" href="<?= e(admin_url('applications', ['a' => 'view', 'id' => $a['id']])) ?>"><?= e($a['name'] . ' ' . $a['surname']) ?></a></td>
                                <td class="nowrap"><?= e($a['phone']) ?></td>
                                <td><?= type_badge($a['type']) ?></td>
                                <td><?= status_badge($a['status']) ?></td>
                                <td class="nowrap"><?= e(format_dt($a['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="stack">
            <div class="card">
                <h2 class="card__title" style="margin-bottom:20px"><?= e(t('dash.by_type')) ?></h2>
                <?php if (!$apps): ?>
                    <p class="empty" style="padding:24px 0"><?= e(t('dash.no_apps')) ?></p>
                <?php else: ?>
                    <div class="bars">
                        <?php $max = max(1, max($byType)); $i = 0; foreach ($byType as $type => $n): ?>
                            <div class="bar">
                                <div class="bar__head"><span><?= e(type_label($type)) ?></span><b><?= $n ?></b></div>
                                <div class="bar__track"><div class="bar__fill<?= $i++ ? ' bar__fill--dark' : '' ?>" style="width: <?= round($n / $max * 100) ?>%"></div></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card">
                <h2 class="card__title" style="margin-bottom:20px"><?= e(t('dash.quick')) ?></h2>
                <div class="quick">
                    <a href="<?= e(admin_url('posts', ['a' => 'edit'])) ?>"><?= admin_icon('plus') ?><?= e(t('dash.new_post')) ?></a>
                    <a href="<?= e(admin_url('faq', ['a' => 'edit'])) ?>"><?= admin_icon('help') ?><?= e(t('faq.add')) ?></a>
                    <a href="<?= e(admin_url('texts')) ?>"><?= admin_icon('sliders') ?><?= e(t('nav.texts')) ?></a>
                    <a href="<?= e(admin_url('seo')) ?>"><?= admin_icon('globe') ?><?= e(t('nav.seo')) ?></a>
                    <?php if (is_admin()): ?>
                        <a href="<?= e(admin_url('settings')) ?>"><?= admin_icon('settings') ?><?= e(t('nav.settings')) ?></a>
                    <?php endif; ?>
                    <a href="<?= e(url()) ?>" target="_blank" rel="noopener"><?= admin_icon('external') ?><?= e(t('common.view_site')) ?></a>
                </div>
            </div>

            <div class="card">
                <h2 class="card__title" style="margin-bottom:20px"><?= admin_icon('server') ?> <?= e(t('dash.server')) ?></h2>
                <div class="kv">
                    <?php foreach ($server as $label => [$value, $ok]): ?>
                        <div><span><?= e($label) ?></span><b class="<?= $ok === null ? '' : ($ok ? 'ok' : 'fail') ?>"><?= e($value) ?></b></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php
});
