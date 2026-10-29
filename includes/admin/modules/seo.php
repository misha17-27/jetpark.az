<?php
// Səhifələr: siyahı (ünvan, mətnin və SEO-nun vəziyyəti) və hər səhifənin redaktəsi —
// hero başlığı/şəkli, title, description, sosial şəkil. Səhifələrin mətni öz bölmələrində redaktə olunur.

// slug — səhifənin ünvanı; hero — hero məlumatının saxlandığı content bölməsi;
// edit — mətnin redaktə olunduğu admin bölməsi; text — səhifənin əsas mətni (boşdursa, "Mətn" boz olur).
$seoPages = [
    'home'        => ['slug' => '', 'hero' => null, 'edit' => 'texts', 'text' => fn () => content('home.hero_tagline')],
    'haqqimizda'  => ['slug' => 'haqqimizda/', 'hero' => 'about', 'edit' => 'about', 'text' => fn () => content('about.lead')],
    'sahibkarlar' => ['slug' => 'sahibkarlar-ucun/', 'hero' => 'owners', 'edit' => 'owners', 'text' => fn () => content('owners.text')],
    'suruculer'   => ['slug' => 'suruculer/', 'hero' => 'drivers', 'edit' => 'drivers', 'text' => fn () => content('drivers.text')],
    'faq'         => ['slug' => 'faq/', 'hero' => 'faq', 'edit' => 'faq', 'text' => fn () => faq_items()],
    'elaqe'       => ['slug' => 'elaqe/', 'hero' => 'contact', 'edit' => 'texts', 'text' => fn () => content('contact.heading')],
    'blog'        => ['slug' => 'blog/', 'hero' => 'blog', 'edit' => 'posts', 'text' => fn () => posts()],
];

// ---------- Səhifənin redaktəsi ----------
if ($action === 'edit') {
    $key = (string) ($_GET['page'] ?? '');
    if (!isset($seoPages[$key])) {
        admin_flash('error', t('common.not_found'));
        admin_redirect('seo');
    }
    $p = $seoPages[$key];

    if (is_post()) {
        verify_csrf();
        $errors = [];
        $entry = [
            'title'       => post_str('seo_title', 120),
            'description' => post_str('seo_description', 300),
            'og_image'    => (string) handle_image_field('seo_og_image', seo($key)['og_image'] ?? '', $errors, 'og', 1200),
        ];
        // Dəyişdirilməyən sahə ilkin mətnə bağlı qalır — sonradan yenilənən ilkin SEO da tətbiq olunur.
        $defaults = store_defaults('seo')[$key] ?? [];
        foreach ($entry as $field => $value) {
            if ($value === ($defaults[$field] ?? null)) {
                $entry[$field] = '';
            }
        }
        store_update('seo', function (array $all) use ($key, $entry) {
            $all[$key] = $entry;
            return $all;
        });
        if ($p['hero']) {
            content_save($p['hero'], [
                'hero_title' => post_str('hero_title', 80),
                'hero_image' => handle_image_field('hero_image', content($p['hero'] . '.hero_image'), $errors, 'hero', 2000),
            ]);
        }
        foreach ($errors as $err) {
            admin_flash('error', $err);
        }
        admin_flash('success', t('common.saved'));
        admin_redirect('seo', ['a' => 'edit', 'page' => $key]);
    }

    $actions = [['href' => url($p['slug']), 'label' => t('seo.open'), 'icon' => 'eye', 'blank' => true]];
    admin_page(t('page.' . $key), 'seo', function () use ($key, $p) {
        $s = seo($key);
        $defaults = store_defaults('seo')[$key] ?? [];
        ?>
        <p style="margin-bottom:18px"><a class="btn btn-ghost btn-sm" href="<?= e(admin_url('seo')) ?>">← <?= e(t('common.back')) ?></a></p>
        <form method="post" enctype="multipart/form-data" action="<?= e(admin_url('seo', ['a' => 'edit', 'page' => $key])) ?>">
            <?= csrf_field() ?>
            <p class="page-note">
                <span><?= e(t('seo.text_in', t('nav.' . $p['edit']))) ?></span>
                <a href="<?= e(admin_url($p['edit'])) ?>"><?= e(t('seo.go_text')) ?> →</a>
            </p>

            <?php if ($p['hero']): ?>
                <div class="card">
                    <div class="card__head"><h2 class="card__title"><?= e(t('common.hero')) ?></h2></div>
                    <div class="form-grid">
                        <?= field_text('hero_title', t('common.hero_title'), content($p['hero'] . '.hero_title'), ['maxlength' => 80]) ?>
                        <?= field_image('hero_image', t('common.hero_image'), content($p['hero'] . '.hero_image')) ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card seo-card">
                <div class="card__head">
                    <h2 class="card__title">SEO</h2>
                    <span class="url-pill">/<?= e($p['slug']) ?></span>
                </div>
                <p class="card__sub"><?= e(t('seo.hint')) ?></p>
                <div class="form-grid">
                    <div>
                        <div class="field">
                            <label for="seo-title"><?= e(t('seo.title')) ?></label>
                            <input id="seo-title" type="text" name="seo_title" value="<?= e($s['title'] ?? '') ?>" placeholder="<?= e($defaults['title'] ?? '') ?>" maxlength="120" data-counter="60" data-seo="title">
                        </div>
                        <div class="field">
                            <label for="seo-desc"><?= e(t('seo.description')) ?></label>
                            <textarea id="seo-desc" name="seo_description" rows="3" maxlength="300" placeholder="<?= e($defaults['description'] ?? '') ?>" data-counter="160" data-seo="description"><?= e($s['description'] ?? '') ?></textarea>
                        </div>
                        <div class="seo-preview" data-seo-preview>
                            <div class="seo-preview__title"></div>
                            <div class="seo-preview__url"><?= e(absolute_url($p['slug'])) ?></div>
                            <div class="seo-preview__desc"></div>
                        </div>
                    </div>
                    <?= field_image('seo_og_image', t('seo.og_image'), $s['og_image'] ?? '') ?>
                </div>
            </div>
            <div class="form-actions form-actions--sticky"><button class="btn btn-primary" type="submit"><?= e(t('common.save')) ?></button></div>
        </form>
        <?php
    }, $actions);
    return;
}

// ---------- Siyahı ----------
admin_page(t('nav.seo'), 'seo', function () use ($seoPages) {
    ?>
    <div class="card">
        <p class="card__intro"><?= e(t('seo.intro')) ?></p>
        <div class="table-wrap">
            <table class="table table--pages">
                <thead><tr>
                    <th><?= e(t('seo.col_page')) ?></th>
                    <th><?= e(t('seo.col_url')) ?></th>
                    <th class="col-dot"><?= e(t('common.text')) ?></th>
                    <th class="col-dot">SEO</th>
                    <th class="col-actions"></th>
                </tr></thead>
                <tbody>
                <?php foreach ($seoPages as $key => $p):
                    $hasText = (bool) $p['text']();
                    $state = seo_state(seo($key));
                    $editUrl = admin_url('seo', ['a' => 'edit', 'page' => $key]);
                    ?>
                    <tr>
                        <td><a class="row-title" href="<?= e($editUrl) ?>"><?= e(t('page.' . $key)) ?></a></td>
                        <td><span class="url-pill">/<?= e($p['slug']) ?></span></td>
                        <td class="col-dot"><?= status_dot($hasText ? 'ok' : 'empty', t($hasText ? 'seo.text_ok' : 'seo.text_empty')) ?></td>
                        <td class="col-dot"><?= status_dot($state, t('seo.state_' . $state)) ?></td>
                        <td class="col-actions">
                            <a class="btn btn-outline btn-sm" href="<?= e($editUrl) ?>"><?= e(t('common.edit')) ?></a>
                            <a class="btn btn-outline btn-sm" href="<?= e(url($p['slug'])) ?>" target="_blank" rel="noopener"><?= e(t('common.view')) ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
});
