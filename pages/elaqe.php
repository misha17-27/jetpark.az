<?php
extract(seo_vars('elaqe'));
$active = 'elaqe';
$canonical = 'elaqe/';

partial('page-hero', ['heading' => content('contact.hero_title'), 'image' => content('contact.hero_image')]);
?>
<section class="contact">
    <div class="container">
        <?php partial('section-label', ['text' => content('contact.label'), 'center' => true, 'tag' => 'p']) ?>
        <h2 class="section-heading contact__heading"><?= e(content('contact.heading')) ?></h2>
        <p class="contact__sub"><?= e(content('contact.sub')) ?></p>

        <div class="contact__grid">
            <div class="contact-item">
                <p class="contact-item__label"><?= circle_icon('phone') ?>Telefon</p>
                <a class="contact-item__value" href="tel:<?= e(phone_link(config('phone'))) ?>"><?= e(config('phone')) ?></a>
            </div>
            <div class="contact-item">
                <p class="contact-item__label"><?= circle_icon('mail') ?>E-poçt</p>
                <a class="contact-item__value" href="mailto:<?= e(config('email')) ?>"><?= e(config('email')) ?></a>
            </div>
            <div class="contact-item">
                <p class="contact-item__label"><?= circle_icon('pin') ?>Ünvan</p>
                <a class="contact-item__value" href="<?= e(config('map_url')) ?>" target="_blank" rel="noopener"><?= e(config('address_short')) ?></a>
            </div>
            <div class="contact-item">
                <p class="contact-item__label contact-item__label--plain">Sosial Media linkləri</p>
                <?php partial('social', ['style' => 'round']) ?>
            </div>
        </div>
    </div>
</section>

<section class="form-section form-section--contact container">
    <div class="form-section__card">
        <?php partial('application-form', ['source' => 'Əlaqə səhifəsi']) ?>
    </div>
    <?php if (content('contact.show_map') && config('map_embed')): ?>
        <div class="form-section__map">
            <iframe title="JetPark ünvanı xəritədə" src="<?= e(config('map_embed')) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
        </div>
    <?php endif; ?>
</section>
