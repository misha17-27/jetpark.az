<?php
/**
 * Səhifə mətnləri və şəkilləri — ilkin məzmun (admin paneldə "Mətnlər" və səhifə bölmələri).
 * Mətnlərdə **söz** — yaşıl rənglə vurğulanır, yeni sətir — sətir keçidi.
 * Şəkil yolları assets/ qovluğuna nisbətəndir.
 */

return [
    'home' => [
        'hero_brand'    => 'JetPark.az –',
        'hero_tagline'  => "Sürücülər və Sahibkarlar\nÜçün Etibarlı Taksi Parkı",
        'hero_image'    => 'img/home-car.webp',
        'features_label'   => 'Üstünlüklərimiz',
        'features_heading' => 'Ehtiyaclarınız üçün bənzərsiz keyfiyyət və xidmət',
        'features_text'    => 'JetPark ailəsinə qoşulmaqla, siz həm əməkdaşlıqdan maksimum fayda əldə edəcək, həm də işinizi peşəkarlara etibar edəcəksiniz.',
        'features_button'  => 'Ətraflı',
        'faq_label'   => 'FAQ',
        'faq_heading' => 'Tez tez verilən Suallar',
        'faq_count'   => 5,
        'faq_button'  => 'Daha çox',
    ],
    'about' => [
        'hero_title' => 'Haqqımızda',
        'hero_image' => 'img/hero-about.webp',
        'label'  => 'Haqqımızda',
        'lead'   => '**JetPark**, Azərbaycanda sürücülər və avtomobil sahibləri üçün xüsusi xidmətlər təklif edən **aparıcı taksi** parkıdır.',
        'point1' => 'Bizim əsas məqsədimiz sürücülər üçün yüksək gəlirli iş şəraiti, sahibkarlar üçün isə sabit və etibarlı gəlir mənbəyi təmin etməkdir.',
        'point2' => 'JetPark ailəsinə qoşulmaqla, siz həm əməkdaşlıqdan maksimum fayda əldə edəcək, həm də işinizi peşəkarlara etibar edəcəksiniz.',
        'image'  => 'img/about-collage.webp',
        'button' => 'Ətraflı',
    ],
    'owners' => [
        'hero_title'  => 'Sahibkarlar',
        'hero_image'  => 'img/hero-owners.webp',
        'label'       => 'Sahibkarlar üçün',
        'text'        => "**Sahibkarlar** Üçün Sabit Gəlir İmkanı.\n**JetPark**, avtomobil sahibləri üçün **sabit gəlir** təmin edən ideal partnyordur. Avtomobilinizi parkımıza təhvil verərək vaxtınıza və resurslarınıza qənaət edin.",
        'image'       => 'img/owners-handshake.webp',
        'image_alt'   => 'Sahibkarla əməkdaşlıq — əl sıxma',
        'cards_label' => 'Nə təklif edirik?',
        'cards' => [
            ['id' => 'own01', 'icon' => 'icons/stable-income.svg', 'title' => 'Stabil Gəlir', 'text' => 'Avtomobiliniz hər ay stabil gəlir gətirəcək, əlavə heç bir xərclə qarşılaşmayacaqsınız.'],
            ['id' => 'own02', 'icon' => 'icons/full-management.svg', 'title' => 'Tam İdarəetmə', 'text' => 'Avtomobilinizin texniki baxışı, dispetçer xidməti və əməliyyat idarəetməsi park tərəfindən həyata keçirilir.'],
            ['id' => 'own03', 'icon' => 'icons/professional.svg', 'title' => 'Təhlükəsizlik', 'text' => 'Avtomobillər yalnız peşəkar sürücülər tərəfindən idarə olunur.'],
        ],
        'note'       => '',
        'form_image' => 'img/owners-form.webp',
        'form_image_alt' => 'Sahibkar müqaviləni nəzərdən keçirir',
    ],
    'drivers' => [
        'hero_title'  => 'Sürücülər',
        'hero_image'  => 'img/hero-drivers.webp',
        'label'       => 'Sürücülər üçün',
        'text'        => "**Sürücülər** Üçün İş İmkanları:\nJetPark, sürücülər üçün **yüksək gəlir və rahat iş** şəraiti təqdim edir. Əgər siz peşəkar sürücüsünüzsə və sabit gəlir qazanmaq istəyirsinizsə, **JetPark** sizin üçün ideal seçimdir.",
        'image'       => 'img/drivers-content.jpg',
        'image_alt'   => 'JetPark sürücüsü avtomobilin yanında',
        'cards_label' => 'Nə təklif edirik?',
        'cards' => [
            ['id' => 'drv01', 'icon' => 'icons/high-income.svg', 'title' => 'Yüksək Gəlir', 'text' => 'Sürücülərimiz qazancın 50%-ni əldə edir.'],
            ['id' => 'drv02', 'icon' => 'icons/bonuses.svg', 'title' => 'Bonuslar və Motivasiyalar', 'text' => 'Hədəf gedişlərini tamamlayan sürücülər əlavə bonuslar qazanır.'],
            ['id' => 'drv03', 'icon' => 'icons/comfort.svg', 'title' => 'Rahat İş Şərtləri', 'text' => 'Günorta istirahət, dispetçer dəstəyi və rahat iş rejimi.'],
        ],
        'note'       => '***Peşəkar Dəstək:** Dispetçer xidməti və müştəri idarəetməsi park tərəfindən',
        'form_image' => 'img/drivers-form.webp',
        'form_image_alt' => 'Sürücü müraciət sənədini doldurur',
    ],
    'faq' => [
        'hero_title' => 'Faq',
        'hero_image' => 'img/hero-faq.webp',
        'label'      => 'Tez tez verilən suallar',
    ],
    'contact' => [
        'hero_title' => 'Əlaqə',
        'hero_image' => 'img/hero-contact.webp',
        'label'      => 'Bizimlə əlaqə',
        'heading'    => 'JetPark ailəsinə qoşulmaq istəyirsiniz?',
        'sub'        => 'Ətraflı məlumat üçün bizimlə əlaqə saxlayın',
        'show_map'   => true,
    ],
    'blog' => [
        'hero_title' => 'Blog',
        'hero_image' => 'img/hero-blog.webp',
    ],
    'form' => [
        'label'   => 'Müraciət formu',
        'button'  => 'Göndər',
        'success' => 'Təşəkkür edirik! WhatsApp-da açılan mesajı göndərin — tezliklə sizinlə əlaqə saxlayacağıq.',
    ],
    'footer' => [
        'copyright' => '© Jetpark.az.',
    ],
];
