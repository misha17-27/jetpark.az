<?php
extract(seo_vars('home'));
$taglineParts = explode("\n", str_replace("\r\n", "\n", trim(content('home.hero_tagline'))));
?>
<section class="home-hero">
    <div class="home-hero__left">
        <div class="home-hero__intro">
            <p class="eyebrow"><img src="<?= asset('img/label-line.png') ?>" alt="JetPark" width="381" height="25"></p>
            <h1 class="home-hero__title">
                <span class="home-hero__brand"><?= e(content('home.hero_brand')) ?></span>
                <span class="home-hero__tagline">
                    <img class="home-hero__badge" src="<?= asset('img/badge.svg') ?>" alt="" width="130" height="127">
                    <span><?= implode('<br class="br-wide"> ', array_map('e', $taglineParts)) ?></span>
                </span>
            </h1>
        </div>
        <div class="home-hero__form">
            <?php partial('application-form', ['source' => 'Ana səhifə']) ?>
        </div>
    </div>
    <div class="home-hero__media">
        <img src="<?= asset(content('home.hero_image')) ?>" alt="JetPark taksisi Bakıda, Alov Qüllələrinin fonunda" width="673" height="863" fetchpriority="high">
    </div>
</section>

<?php partial('about', ['cta' => 'button']) ?>

<?php partial('features', [
    'label'   => content('home.features_label'),
    'heading' => content('home.features_heading'),
    'items'   => advantages(),
    'footer'  => '<div class="features__cta"><p>' . rich_text(content('home.features_text')) . '</p><a class="btn btn--dark-green" href="' . url('elaqe/') . '">' . e(content('home.features_button')) . '</a></div>',
]) ?>

<?php $faqCount = max(1, (int) content('home.faq_count', 5)); ?>
<section class="faq-section container">
    <?php partial('section-label', ['text' => content('home.faq_label'), 'center' => true, 'tag' => 'p']) ?>
    <h2 class="section-heading"><?= e(content('home.faq_heading')) ?></h2>
    <?php partial('faq-list', ['items' => faq_items($faqCount)]) ?>
    <?php if (count(faq_items()) > $faqCount): ?>
        <div class="text-center">
            <a class="btn btn--dark-green btn--icon" href="<?= url('faq/') ?>"><?= e(content('home.faq_button')) ?> <?= icon('arrow-right') ?></a>
        </div>
    <?php endif; ?>
</section>
