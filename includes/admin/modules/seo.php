<?php
// Səhifələr və SEO: hər səhifə üçün hero başlığı/şəkli, title, description, sosial şəkil.

// SEO açarı => [səhifə ünvanı, hero məlumatının saxlandığı content bölməsi]
$seoPages = [
    'home'        => ['', null],
    'haqqimizda'  => ['haqqimizda/', 'about'],
    'sahibkarlar' => ['sahibkarlar-ucun/', 'owners'],
    'suruculer'   => ['suruculer/', 'drivers'],
    'faq'         => ['faq/', 'faq'],
    'elaqe'       => ['elaqe/', 'contact'],
    'blog'        => ['blog/', 'blog'],
];

$errors = [];
if (is_post()) {
    verify_csrf();
    $seo = [];
    foreach ($seoPages as $key => [$slug, $section]) {
        $seo[$key] = [
            'title'       => post_str('seo_' . $key . '_title', 120),
            'description' => post_str('seo_' . $key . '_description', 300),
            'og_image'    => (string) handle_image_field('seo_' . $key . '_og_image', seo($key)['og_image'] ?? '', $errors, 'og', 1200),
        ];
        if ($section) {
            content_save($section, [
                'hero_title' => post_str('hero_' . $key . '_title', 80),
                'hero_image' => handle_image_field('hero_' . $key . '_image', content($section . '.hero_image'), $errors, 'hero', 2000),
            ]);
        }
    }
    store_put('seo', $seo);
    foreach ($errors as $err) {
        admin_flash('error', $err);
    }
    admin_flash('success', t('common.saved'));
    admin_redirect('seo');
}

admin_page(t('nav.seo'), 'seo', function () use ($seoPages) {
    $defaults = store_defaults('seo');
    ?>
    <form method="post" enctype="multipart/form-data" action="<?= e(admin_url('seo')) ?>">
        <?= csrf_field() ?>
        <p class="card__sub" style="margin:0 0 20px"><?= e(t('seo.hint')) ?></p>
        <?php foreach ($seoPages as $key => [$slug, $section]): $s = seo($key); ?>
            <div class="card seo-card">
                <div class="card__head">
                    <h2 class="card__title"><?= e(t('page.' . $key)) ?></h2>
                    <a class="btn btn-outline btn-sm" href="<?= e(url($slug)) ?>" target="_blank" rel="noopener"><?= admin_icon('eye') ?><?= e(t('seo.open')) ?></a>
                </div>
                <?php if ($section): ?>
                    <div class="form-grid">
                        <?= field_text('hero_' . $key . '_title', t('common.hero_title'), content($section . '.hero_title'), ['maxlength' => 80]) ?>
                        <?= field_image('hero_' . $key . '_image', t('common.hero_image'), content($section . '.hero_image')) ?>
                    </div>
                <?php endif; ?>
                <div class="form-grid">
                    <div>
                        <div class="field">
                            <label for="seo-<?= $key ?>-title"><?= e(t('seo.title')) ?></label>
                            <input id="seo-<?= $key ?>-title" type="text" name="seo_<?= $key ?>_title" value="<?= e($s['title'] ?? '') ?>" placeholder="<?= e($defaults[$key]['title'] ?? '') ?>" maxlength="120" data-counter="60" data-seo="title">
                        </div>
                        <div class="field">
                            <label for="seo-<?= $key ?>-desc"><?= e(t('seo.description')) ?></label>
                            <textarea id="seo-<?= $key ?>-desc" name="seo_<?= $key ?>_description" rows="3" maxlength="300" placeholder="<?= e($defaults[$key]['description'] ?? '') ?>" data-counter="160" data-seo="description"><?= e($s['description'] ?? '') ?></textarea>
                        </div>
                        <div class="seo-preview" data-seo-preview>
                            <div class="seo-preview__title"></div>
                            <div class="seo-preview__url"><?= e(absolute_url($slug)) ?></div>
                            <div class="seo-preview__desc"></div>
                        </div>
                    </div>
                    <?= field_image('seo_' . $key . '_og_image', t('seo.og_image'), $s['og_image'] ?? '') ?>
                </div>
            </div>
        <?php endforeach; ?>
        <div class="form-actions form-actions--sticky"><button class="btn btn-primary" type="submit"><?= e(t('common.save')) ?></button></div>
    </form>
    <?php
});
