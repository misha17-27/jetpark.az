<?php
// "Üstünlüklərimiz" kartları.

if (is_post()) {
    verify_csrf();
    $errors = [];
    $cards = read_cards('cards', $errors);
    store_put('advantages', $cards);
    foreach ($errors as $err) {
        admin_flash('error', $err);
    }
    admin_flash('success', t('common.saved'));
    admin_redirect('advantages');
}

admin_page(t('nav.advantages'), 'advantages', function () {
    ?>
    <form method="post" enctype="multipart/form-data" action="<?= e(admin_url('advantages')) ?>">
        <?= csrf_field() ?>
        <div class="card">
            <p class="card__sub" style="margin-top:0"><?= e(t('adv.hint')) ?></p>
            <?= cards_editor('cards', advantages()) ?>
        </div>
        <div class="form-actions form-actions--sticky"><button class="btn btn-primary" type="submit"><?= e(t('common.save')) ?></button></div>
    </form>
    <?php
}, [['href' => url(), 'label' => t('page.home'), 'icon' => 'eye', 'blank' => true]]);
