<?php
/**
 * Sahibkarlar və Sürücülər səhifələri (eyni quruluş).
 * $key: 'owners' | 'drivers', $seoKey, $slug, $formType, $source
 */
extract(seo_vars($seoKey));
$active = $slug;
$canonical = $slug . '/';
$c = fn (string $field, $default = '') => content($key . '.' . $field, $default);
$note = trim((string) $c('note'));

partial('page-hero', ['heading' => $c('hero_title'), 'image' => $c('hero_image')]);
?>
<section class="intro container">
    <div class="intro__media" data-reveal="left">
        <img src="<?= asset($c('image')) ?>" alt="<?= e($c('image_alt')) ?>" width="1000" height="666" loading="lazy">
    </div>
    <div class="intro__body" data-reveal="right">
        <?php partial('section-label', ['text' => $c('label'), 'tag' => 'h2']) ?>
        <p class="intro__text"><?= rich_text($c('text')) ?></p>
    </div>
</section>

<?php partial('features', [
    'label'  => $c('cards_label'),
    'items'  => $c('cards', []),
    'footer' => $note !== '' ? '<p class="features__note">' . rich_text($note, 'text-mint') . '</p>' : null,
]) ?>

<section class="form-section container">
    <div class="form-section__card">
        <?php partial('application-form', ['type' => $formType, 'source' => $source]) ?>
    </div>
    <?php if ($c('form_image')): ?>
        <div class="form-section__media" data-reveal="right">
            <img src="<?= asset($c('form_image')) ?>" alt="<?= e($c('form_image_alt')) ?>" width="1408" height="768" loading="lazy">
        </div>
    <?php endif; ?>
</section>
