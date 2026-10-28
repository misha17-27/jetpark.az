<?php
// Mətnlər: ana səhifə blokları, forma, əlaqə, FAQ səhifəsi, footer.

$errors = [];
if (is_post()) {
    verify_csrf();
    content_save('home', [
        'hero_brand'       => post_str('home_hero_brand', 60),
        'hero_tagline'     => post_str('home_hero_tagline', 200),
        'hero_image'       => handle_image_field('home_hero_image', content('home.hero_image'), $errors, '', 1400),
        'features_label'   => post_str('home_features_label', 80),
        'features_heading' => post_str('home_features_heading', 150),
        'features_text'    => post_str('home_features_text', 400),
        'features_button'  => post_str('home_features_button', 40),
        'faq_label'        => post_str('home_faq_label', 80),
        'faq_heading'      => post_str('home_faq_heading', 150),
        'faq_count'        => max(1, min(20, (int) post_str('home_faq_count', 3))),
        'faq_button'       => post_str('home_faq_button', 40),
    ]);
    content_save('form', [
        'label'   => post_str('form_label', 60),
        'button'  => post_str('form_button', 40),
        'success' => post_str('form_success', 300),
    ]);
    content_save('contact', [
        'label'    => post_str('contact_label', 80),
        'heading'  => post_str('contact_heading', 150),
        'sub'      => post_str('contact_sub', 200),
        'show_map' => ($_POST['contact_show_map'] ?? '') === '1',
    ]);
    content_save('faq', ['label' => post_str('faq_label', 120)]);
    content_save('footer', ['copyright' => post_str('footer_copyright', 120)]);

    foreach ($errors as $err) {
        admin_flash('error', $err);
    }
    admin_flash('success', t('common.saved'));
    admin_redirect('texts');
}

admin_page(t('nav.texts'), 'texts', function () {
    $hint = t('common.highlight_hint');
    ?>
    <form method="post" enctype="multipart/form-data" action="<?= e(admin_url('texts')) ?>">
        <?= csrf_field() ?>
        <div class="card">
            <h2 class="card__title" style="margin-bottom:20px"><?= e(t('texts.home_hero')) ?></h2>
            <div class="form-grid">
                <?= field_text('home_hero_brand', t('texts.hero_brand'), content('home.hero_brand'), ['maxlength' => 60]) ?>
                <?= field_textarea('home_hero_tagline', t('texts.hero_tagline'), content('home.hero_tagline'), ['rows' => 2, 'maxlength' => 200, 'hint' => t('texts.hero_tagline_hint')]) ?>
                <div class="span-2"><?= field_image('home_hero_image', t('texts.hero_image'), content('home.hero_image')) ?></div>
            </div>
        </div>

        <div class="card">
            <h2 class="card__title" style="margin-bottom:20px"><?= e(t('texts.home_features')) ?></h2>
            <div class="form-grid">
                <?= field_text('home_features_label', t('common.label'), content('home.features_label'), ['maxlength' => 80]) ?>
                <?= field_text('home_features_heading', t('common.title'), content('home.features_heading'), ['maxlength' => 150]) ?>
                <?= field_textarea('home_features_text', t('common.text'), content('home.features_text'), ['rows' => 3, 'hint' => $hint]) ?>
                <?= field_text('home_features_button', t('common.button'), content('home.features_button'), ['maxlength' => 40]) ?>
            </div>
        </div>

        <div class="card">
            <h2 class="card__title" style="margin-bottom:20px"><?= e(t('texts.home_faq')) ?></h2>
            <div class="form-grid">
                <?= field_text('home_faq_label', t('common.label'), content('home.faq_label'), ['maxlength' => 80]) ?>
                <?= field_text('home_faq_heading', t('common.title'), content('home.faq_heading'), ['maxlength' => 150]) ?>
                <?= field_text('home_faq_count', t('texts.faq_count'), (string) content('home.faq_count', 5), ['type' => 'number', 'min' => 1, 'max' => 20]) ?>
                <?= field_text('home_faq_button', t('common.button'), content('home.faq_button'), ['maxlength' => 40]) ?>
            </div>
        </div>

        <div class="card">
            <h2 class="card__title" style="margin-bottom:20px"><?= e(t('texts.form')) ?></h2>
            <div class="form-grid">
                <?= field_text('form_label', t('common.label'), content('form.label'), ['maxlength' => 60]) ?>
                <?= field_text('form_button', t('common.button'), content('form.button'), ['maxlength' => 40]) ?>
                <div class="span-2"><?= field_textarea('form_success', t('texts.form_success'), content('form.success'), ['rows' => 2, 'maxlength' => 300, 'hint' => t('texts.form_success_hint')]) ?></div>
            </div>
        </div>

        <div class="card">
            <h2 class="card__title" style="margin-bottom:20px"><?= e(t('texts.contact')) ?></h2>
            <div class="form-grid">
                <?= field_text('contact_label', t('common.label'), content('contact.label'), ['maxlength' => 80]) ?>
                <?= field_text('contact_heading', t('texts.contact_heading'), content('contact.heading'), ['maxlength' => 150]) ?>
                <div class="span-2"><?= field_text('contact_sub', t('texts.contact_sub'), content('contact.sub'), ['maxlength' => 200]) ?></div>
                <div class="span-2"><?= field_checkbox('contact_show_map', t('texts.show_map'), (bool) content('contact.show_map')) ?></div>
            </div>
        </div>

        <div class="card">
            <div class="form-grid">
                <div>
                    <h2 class="card__title" style="margin-bottom:20px"><?= e(t('texts.faq_page')) ?></h2>
                    <?= field_text('faq_label', t('common.label'), content('faq.label'), ['maxlength' => 120]) ?>
                </div>
                <div>
                    <h2 class="card__title" style="margin-bottom:20px"><?= e(t('texts.footer')) ?></h2>
                    <?= field_text('footer_copyright', t('texts.copyright'), content('footer.copyright'), ['maxlength' => 120]) ?>
                </div>
            </div>
        </div>

        <div class="form-actions form-actions--sticky"><button class="btn btn-primary" type="submit"><?= e(t('common.save')) ?></button></div>
    </form>
    <?php
});
