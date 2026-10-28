<?php
// Saytın ilkin parametrləri. Bu dəyərlər admin paneldə (Parametrlər) dəyişdirilə bilər —
// orada saxlanılan dəyərlər storage/data/settings.json faylına yazılır və buradakılardan üstündür.

return [
    'site_name' => 'JetPark',
    // Canonical linklər, sitemap və sosial şəbəkə önizləmələri üçün (sonunda "/" olmadan).
    'site_url'  => 'https://jetpark.az',

    'phone'         => '+994 10 715 23 44',
    'whatsapp'      => '994107152344',
    'email'         => 'info@jetpark.az',
    'address'       => 'Bakı, Ağadadaş Qurbanov 40 F',
    'address_short' => 'Ağadadaş Qurbanov 40 F',
    'map_url'    => 'https://www.google.com/maps/place/40+A%C4%9Fadada%C5%9F+Qurbanov,+Bak%C4%B1/@40.3837694,49.8308066,17z/data=!3m1!4b1!4m6!3m5!1s0x40307d5909ce924b:0xa87f24451d12457f!8m2!3d40.3837694!4d49.8333815!16s%2Fg%2F11w1x15j3h',
    'map_embed'  => 'https://www.google.com/maps?q=40.3837694,49.8333815&z=16&output=embed',
    'facebook'   => 'https://www.facebook.com/profile.php?id=61571310985833',
    'instagram'  => 'https://www.instagram.com/uber_jetpark/',

    // Müraciət formu: müraciətlər bu ünvana göndərilir və admin paneldə "Müraciətlər" bölməsində görünür.
    'mail_to'   => 'info@jetpark.az',
    // Hostinqdəki domenə aid ünvan olmalıdır, əks halda məktublar spam-a düşə bilər.
    'mail_from' => 'no-reply@jetpark.az',

    'posts_per_page' => 12,

    // Cloudflare Turnstile (admin girişində bot qoruması). Boş qalarsa söndürülür.
    'turnstile_site_key'   => '',
    'turnstile_secret_key' => '',
];
