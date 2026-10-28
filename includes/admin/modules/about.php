<?php
// "Haqqımızda" bloku (ana səhifədə və Haqqımızda səhifəsində).

$errors = [];
if (is_post()) {
    verify_csrf();
    $values = [
        'label'  => post_str('label', 80),
        'lead'   => post_str('lead', 400),
        'point1' => post_str('point1', 400),
        'point2' => post_str('point2', 400),
        'button' => post_str('button', 40),
    ];
    $values['image'] = handle_image_field('image', content('about.image'), $errors, '', 1400);
    content_save('about', $values);
    foreach ($errors as $err) {
        admin_flash('error', $err);
    }
    admin_flash('success', t('common.saved'));
    admin_redirect('about');
}

admin_page(t('nav.about'), 'about', function () {
    $hint = t('common.highlight_hint');
    ?>
    <form method="post" enctype="multipart/form-data" action="<?= e(admin_url('about')) ?>">
        <?= csrf_field() ?>
        <div class="card">
            <h2 class="card__title" style="margin-bottom:20px"><?= e(t('about.block')) ?></h2>
            <div class="form-grid">
                <?= field_text('label', t('common.label'), content('about.label'), ['maxlength' => 80]) ?>
                <?= field_text('button', t('common.button'), content('about.button'), ['maxlength' => 40]) ?>
                <div class="span-2"><?= field_textarea('lead', t('about.lead'), content('about.lead'), ['rows' => 3, 'hint' => $hint]) ?></div>
                <?= field_textarea('point1', t('about.point1'), content('about.point1'), ['rows' => 3]) ?>
                <?= field_textarea('point2', t('about.point2'), content('about.point2'), ['rows' => 3]) ?>
                <div class="span-2"><?= field_image('image', t('common.image'), content('about.image')) ?></div>
            </div>
        </div>
        <div class="form-actions form-actions--sticky"><button class="btn btn-primary" type="submit"><?= e(t('common.save')) ?></button></div>
    </form>
    <?php
}, [['href' => url('haqqimizda/'), 'label' => t('seo.open'), 'icon' => 'eye', 'blank' => true]]);
