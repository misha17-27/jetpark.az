<?php
$perPage = max(1, (int) config('posts_per_page'));
$all = posts();
$pages = max(1, (int) ceil(count($all) / $perPage));
$list = array_slice($all, ($page - 1) * $perPage, $perPage);

extract(seo_vars('blog'));
if ($page > 1) {
    // "Blog - JetPark" → "Blog — Səhifə 2 - JetPark"
    $title = preg_replace('/( - [^-]+)$/u', ' — Səhifə ' . $page . '$1', $title, 1, $replaced);
    if (!$replaced) {
        $title .= ' — Səhifə ' . $page;
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
