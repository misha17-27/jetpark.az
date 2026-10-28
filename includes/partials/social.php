<?php
// $style: 'plain' (footer) və ya 'round' (Haqqımızda / Əlaqə səhifələri)
$style ??= 'round';
$links = $style === 'plain'
    ? ['facebook' => 'Facebook', 'instagram' => 'Instagram', 'whatsapp' => 'WhatsApp']
    : ['whatsapp' => 'WhatsApp', 'facebook-circle' => 'Facebook', 'instagram' => 'Instagram'];
$hrefs = [
    'facebook'        => config('facebook'),
    'facebook-circle' => config('facebook'),
    'instagram'       => config('instagram'),
    'whatsapp'        => 'https://api.whatsapp.com/send?phone=' . config('whatsapp'),
];
?>
<ul class="social social--<?= e($style) ?>">
    <?php foreach ($links as $iconName => $label): ?>
        <li><a href="<?= e($hrefs[$iconName]) ?>" target="_blank" rel="noopener" aria-label="<?= e($label) ?>"><?= icon($iconName) ?></a></li>
    <?php endforeach; ?>
</ul>
