<?php
$perPage = max(1, (int) config('posts_per_page'));
$all = posts();
$pages = max(1, (int) ceil(count($all) / $perPage));
$list = array_slice($all, ($page - 1) * $perPage, $perPage);

extract(seo_vars('blog'));
if ($page > 1) {
    // Növbəti səhifələrin öz başlığı və təsviri olur ki, birinci səhifəni təkrarlamasın.
    $title = content('blog.hero_title') . ' — Səhifə ' . $page . ' | ' . config('site_name');
    $description = 'Səhifə ' . $page . '. ' . $description;
    if (mb_strlen($description) > 160) {
        $description = mb_substr($description, 0, (int) mb_strrpos(mb_substr($description, 0, 159), ' ')) . '…';
    }
}
$active = 'blog';
$canonical = $page > 1 ? 'blog/page/' . $page . '/' : 'blog/';

partial('page-hero', ['heading' => content('blog.hero_title'), 'image' => content('blog.hero_image')]);
?>
<section class="blog container">
    <div class="blog__grid">
        <?php foreach ($list as $post): ?>
            <?php partial('post-card', ['post' => $post]) ?>
        <?php endforeach; ?>
    </div>

    <?php if ($pages > 1): ?>
        <nav class="pagination" aria-label="Səhifələr">
            <?php for ($i = 1; $i <= $pages; $i++): ?>
                <?php $href = $i === 1 ? url('blog/') : url('blog/page/' . $i . '/'); ?>
                <?php if ($i === $page): ?>
                    <span class="pagination__item is-current" aria-current="page"><?= $i ?></span>
                <?php else: ?>
                    <a class="pagination__item" href="<?= $href ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
</section>
