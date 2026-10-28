<?php
extract(seo_vars('haqqimizda'));
$active = 'haqqimizda';
$canonical = 'haqqimizda/';

partial('page-hero', ['heading' => content('about.hero_title'), 'image' => content('about.hero_image')]);
partial('about', ['cta' => 'social']);
partial('features', [
    'label'   => content('home.features_label'),
    'heading' => content('home.features_heading'),
    'items'   => advantages(),
]);
