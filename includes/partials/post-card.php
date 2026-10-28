<?php $link = url($post['slug'] . '/'); ?>
<article class="post-card" data-reveal>
    <a class="post-card__image" href="<?= $link ?>" tabindex="-1" aria-hidden="true">
        <img src="<?= asset($post['thumb'] ?: $post['image']) ?>" alt="" width="768" height="419" loading="lazy">
    </a>
    <div class="post-card__body">
        <h2 class="post-card__title"><a href="<?= $link ?>"><?= e($post['title']) ?></a></h2>
        <a class="post-card__more" href="<?= $link ?>" aria-label="Oxu: <?= e($post['title']) ?>"><?= icon('angle-right') ?></a>
    </div>
</article>
