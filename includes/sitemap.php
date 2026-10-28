<?php
// /sitemap.xml — axtarış sistemləri üçün bütün səhifələrin siyahısı.

header('Content-Type: application/xml; charset=UTF-8');

$urls = [];
foreach (['', 'haqqimizda/', 'sahibkarlar-ucun/', 'suruculer/', 'blog/', 'faq/', 'elaqe/'] as $slug) {
    $urls[] = ['loc' => absolute_url($slug), 'lastmod' => null];
}
foreach (posts() as $post) {
    $urls[] = ['loc' => absolute_url($post['slug'] . '/'), 'lastmod' => date('Y-m-d', strtotime($post['date']))];
}

echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', "\n";
foreach ($urls as $u) {
    echo '  <url><loc>', e($u['loc']), '</loc>';
    if ($u['lastmod']) {
        echo '<lastmod>', $u['lastmod'], '</lastmod>';
    }
    echo "</url>\n";
}
echo '</urlset>', "\n";
