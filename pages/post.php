<?php
$title = post_seo_title($post);
$description = $post['description'];
$og_image = $post['image'];
$og_type = 'article';
$active = 'blog';
$canonical = $post['slug'] . '/';
$crumb = $post['title'];

$all = posts();
$newer = $all[$post['index'] - 1] ?? null;
$older = $all[$post['index'] + 1] ?? null;
$shareUrl = rawurlencode(absolute_url($canonical));
$shareText = rawurlencode($post['title']);
?>
<section class="page-hero page-hero--post" style="--hero-image: url('<?= asset(content('blog.hero_image')) ?>')">
    <div class="container">
        <h1 class="page-hero__title page-hero__title--small"><?= e($post['title']) ?></h1>
    </div>
</section>

<article class="post container">
    <img class="post__image" src="<?= asset($post['image']) ?>" alt="<?= e($post['title']) ?>" width="1408" height="768" fetchpriority="high">
    <time class="post__date" datetime="<?= e(date('c', strtotime($post['date']))) ?>"><?= e(az_date($post['date'])) ?></time>
    <div class="post__content">
        <?= $post['content'] ?>
    </div>

    <ul class="share" aria-label="Paylaş">
        <li><a class="share__fb" href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>" target="_blank" rel="noopener" aria-label="Facebook-da paylaş"><?= icon('facebook') ?></a></li>
        <li><a class="share__wa" href="https://api.whatsapp.com/send?text=<?= $shareText ?>%20<?= $shareUrl ?>" target="_blank" rel="noopener" aria-label="WhatsApp-da paylaş"><?= icon('whatsapp') ?></a></li>
        <li><a class="share__in" href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $shareUrl ?>" target="_blank" rel="noopener" aria-label="LinkedIn-də paylaş"><?= icon('linkedin') ?></a></li>
    </ul>

    <nav class="post-nav" aria-label="Digər yazılar">
        <?php if ($older): ?>
            <a class="post-nav__link post-nav__link--prev" href="<?= url($older['slug'] . '/') ?>"><?= icon('angle-left') ?><span><?= e($older['title']) ?></span></a>
        <?php else: ?><span></span><?php endif; ?>
        <?php if ($newer): ?>
            <a class="post-nav__link post-nav__link--next" href="<?= url($newer['slug'] . '/') ?>"><span><?= e($newer['title']) ?></span><?= icon('angle-right') ?></a>
        <?php endif; ?>
    </nav>
</article>

<script type="application/ld+json"><?= json_encode([
    '@context'      => 'https://schema.org',
    '@type'         => 'BlogPosting',
    'headline'      => $post['title'],
    'dateModified'  => date('c', strtotime($post['updated_at'] ?? $post['date'])),
    'image'         => absolute_url('assets/' . $post['image']),
    'datePublished' => date('c', strtotime($post['date'])),
    'description'   => $post['description'],
    'mainEntityOfPage' => absolute_url($canonical),
    'author'        => ['@type' => 'Organization', 'name' => config('site_name'), 'url' => absolute_url()],
    'publisher'     => ['@type' => 'Organization', 'name' => config('site_name'), 'logo' => ['@type' => 'ImageObject', 'url' => absolute_url('assets/img/logo-dark.svg')]],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
