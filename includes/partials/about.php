<?php
// "Haqqımızda" bloku. $cta: 'button' (ana səhifə) və ya 'social' (Haqqımızda səhifəsi)
$cta ??= 'button';
?>
<section class="about container">
    <div class="about__media" data-reveal="left">
        <img src="<?= asset(content('about.image')) ?>" alt="JetPark avtomobilləri Bakıda" width="967" height="1000" loading="lazy">
    </div>
    <div class="about__body" data-reveal="right">
        <?php partial('section-label', ['text' => content('about.label'), 'tag' => 'h2']) ?>
        <p class="about__lead"><?= rich_text(content('about.lead')) ?></p>
        <ul class="about__points">
            <li>
                <img src="<?= asset('icons/about-phone.svg') ?>" alt="" width="56" height="60" loading="lazy">
                <p><?= rich_text(content('about.point1')) ?></p>
            </li>
            <li>
                <img src="<?= asset('icons/about-car.svg') ?>" alt="" width="56" height="60" loading="lazy">
                <p><?= rich_text(content('about.point2')) ?></p>
            </li>
        </ul>
        <?php if ($cta === 'social'): ?>
            <?php partial('social', ['style' => 'round']) ?>
        <?php else: ?>
            <a class="btn btn--dark-green" href="<?= url('haqqimizda/') ?>"><?= e(content('about.button')) ?></a>
        <?php endif; ?>
    </div>
</section>
