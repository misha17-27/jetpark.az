<?php
extract(seo_vars('faq'));
$active = 'faq';
$canonical = 'faq/';
$items = faq_items();

partial('page-hero', ['heading' => content('faq.hero_title'), 'image' => content('faq.hero_image')]);
?>
<section class="faq-section faq-section--page container">
    <?php partial('section-label', ['text' => content('faq.label'), 'center' => true, 'tag' => 'h2']) ?>
    <?php partial('faq-list', ['items' => $items]) ?>
</section>

<script type="application/ld+json"><?= json_encode([
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => array_map(fn ($item) => [
        '@type'          => 'Question',
        'name'           => $item['q'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => trim(html_entity_decode(strip_tags(str_replace(['<br>', '</p>', '</li>'], "\n", $item['a'])), ENT_QUOTES | ENT_HTML5, 'UTF-8'))],
    ], $items),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
