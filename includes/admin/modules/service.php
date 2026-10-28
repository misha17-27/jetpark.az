<?php
// "Sahibkarlar" və "Sürücülər" səhifələrinin məzmunu (eyni quruluş).

$section = $route === 'drivers' ? 'drivers' : 'owners';
$pageUrl = $section === 'drivers' ? 'suruculer/' : 'sahibkarlar-ucun/';
$c = fn (string $field, $default = '') => content($section . '.' . $field, $default);

$errors = [];
if (is_post()) {
    verify_csrf();
    $values = [
        'label'          => post_str('label', 80),
        'text'           => post_str('text', 1000),
        'image_alt'      => post_str('image_alt', 150),
        'cards_label'    => post_str('cards_label', 80),
        'note'           => post_str('note', 300),
        'form_image_alt' => post_str('form_image_alt', 150),
    ];
    $values['image'] = handle_image_field('image', $c('image'), $errors, '', 1400);
    $values['form_image'] = handle_image_field('form_image', $c('form_image'), $errors, '', 1600);
    $values['cards'] = read_cards('cards', $errors);
    content_save($section, $values);
    foreach ($errors as $err) {
        admin_flash('error', $err);
    }
    admin_flash('success', t('common.saved'));
    admin_redirect($route);
}

admin_page(t('nav.' . $route), $route, function () use ($c, $route) {
    $hint = t('common.highlight_hint');
    ?>
    <form method="post" enctype="multipart/form-data" action="<?= e(admin_url($route)) ?>">
        <?= csrf_field() ?>
        <div class="card">
            <h2 class="card__title" style="margin-bottom:20px"><?= e(t('service.intro')) ?></h2>
            <div class="form-grid">
                <div class="span-2"><?= field_text('label', t('common.label'), $c('label'), ['maxlength' => 80]) ?></div>
                <div class="span-2"><?= field_textarea('text', t('common.text'), $c('text'), ['rows' => 5, 'hint' => $hint]) ?></div>
                <?= field_image('image', t('common.image'), $c('image')) ?>
                <?= field_text('image_alt', t('common.alt'), $c('image_alt'), ['maxlength' => 150]) ?>
            </div>
        </div>

        <div class="card">
            <h2 class="card__title" style="margin-bottom:20px"><?= e(t('service.cards')) ?></h2>
            <?= field_text('cards_label', t('common.label'), $c('cards_label'), ['maxlength' => 80]) ?>
            <?= cards_editor('cards', $c('cards', [])) ?>
            <div style="margin-top:22px"><?= field_textarea('note', t('service.note'), $c('note'), ['rows' => 2, 'hint' => $hint]) ?></div>
        </div>

        <div class="card">
            <div class="form-grid">
                <?= field_image('form_image', t('service.form_image'), $c('form_image'), ['removable' => true]) ?>
                <?= field_text('form_image_alt', t('common.alt'), $c('form_image_alt'), ['maxlength' => 150]) ?>
            </div>
        </div>

        <div class="form-actions form-actions--sticky"><button class="btn btn-primary" type="submit"><?= e(t('common.save')) ?></button></div>
    </form>
    <?php
}, [['href' => url($pageUrl), 'label' => t('seo.open'), 'icon' => 'eye', 'blank' => true]]);
