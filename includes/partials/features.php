<?php
/**
 * Açıq yaşıl fonda üstünlüklər kartları.
 * $label, $heading (istəyə görə), $items: [['icon' => ..., 'title' => ..., 'text' => ...], ...], $footer (HTML, istəyə görə)
 */
$heading ??= null;
$footer ??= null;
?>
<section class="features">
    <div class="features__box">
        <?php partial('section-label', ['text' => $label, 'center' => true, 'tag' => $heading ? 'p' : 'h2']) ?>
        <?php if ($heading): ?>
            <h2 class="section-heading"><?= e($heading) ?></h2>
        <?php endif; ?>

        <div class="features__grid features__grid--<?= min(count($items), 5) ?>">
            <?php foreach ($items as $item): ?>
                <article class="feature-card" data-reveal>
                    <?php if (!empty($item['icon'])): ?>
                        <img class="feature-card__icon" src="<?= asset($item['icon']) ?>" alt="" width="64" height="64" loading="lazy">
                    <?php endif; ?>
                    <h3 class="feature-card__title"><?= e($item['title']) ?></h3>
                    <p class="feature-card__text"><?= e($item['text']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>

        <?= $footer ?>
    </div>
</section>
