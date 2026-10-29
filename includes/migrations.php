<?php
// Serverdəki məlumatların bir dəfəlik yenilənməsi. Sayt yeniləndikdən (deploy) sonra ilk sorğuda işləyir,
// görülənlər storage/data/migrations.json-da qeyd olunur. Admin paneldə əl ilə dəyişdirilmiş məlumatlara toxunulmur.

const MIGRATIONS = [
    '2026-10-29-seo' => 'migrate_2026_10_29_seo',
];

function run_migrations(): void
{
    foreach (array_diff_key(MIGRATIONS, store_get('migrations')) as $id => $migrate) {
        try {
            $migrate();
            store_update('migrations', function (array $done) use ($id) {
                $done[$id] = date('Y-m-d H:i:s');
                return $done;
            });
        } catch (Throwable $e) {
            error_log('JetPark: miqrasiya ' . $id . ' alınmadı: ' . $e->getMessage());
            return;
        }
    }
}

/**
 * Yeni SEO mətnləri (data/seo.php, data/posts.php). Admin paneldən saxlanmış, amma dəyişdirilməmiş köhnə
 * ilkin mətnlər yeniləri ilə əvəzlənir; əl ilə yazılmış mətnlər olduğu kimi qalır.
 */
function migrate_2026_10_29_seo(): void
{
    // Köhnə ilkin mətnlər (bu yenilənmədən əvvəlki data/seo.php və data/posts.php).
    $oldPages = [
        'home' => [
            'title'       => 'Sürücülər və Sahibkarlar Üçün Etibarlı Taksi Parkı',
            'description' => 'JetPark — Bakıda sürücülər üçün yüksək gəlirli iş, avtomobil sahibləri üçün isə sabit gəlir təmin edən etibarlı taksi parkı. Müraciət formunu doldurun.',
        ],
        'haqqimizda' => [
            'title'       => 'Haqqımızda - JetPark',
            'description' => 'JetPark Azərbaycanda sürücülər və avtomobil sahibləri üçün xüsusi xidmətlər təklif edən aparıcı taksi parkıdır. Stabil gəlir, peşəkarlıq və şəffaf əməkdaşlıq.',
        ],
        'sahibkarlar' => [
            'title'       => 'Sahibkarlar - JetPark',
            'description' => 'Avtomobilinizi JetPark taksi parkına verin və hər ay sabit gəlir əldə edin. Texniki baxış, dispetçer xidməti və idarəetmə park tərəfindən həyata keçirilir.',
        ],
        'suruculer' => [
            'title'       => 'Sürücülər - JetPark',
            'description' => 'JetPark-da sürücü kimi işləyin: qazancın 50%-i sizindir, hədəf bonusları, rahat iş şəraiti və dispetçer dəstəyi. Müraciət formunu doldurun.',
        ],
        'faq' => [
            'title'       => 'Faq - JetPark',
            'description' => 'JetPark haqqında tez-tez verilən suallar: əməkdaşlıq şərtləri, sürücü tələbləri, avtomobil seçimi, gəlir bölgüsü və təlimlər.',
        ],
        'elaqe' => [
            'title'       => 'Əlaqə - JetPark',
            'description' => 'JetPark ilə əlaqə: telefon +994 10 715 23 44, e-poçt info@jetpark.az, ünvan: Bakı, Ağadadaş Qurbanov 40 F. Müraciət formunu doldurun.',
        ],
        'blog' => [
            'title'       => 'Blog - JetPark',
            'description' => 'JetPark bloqu: taksi sürücüləri və avtomobil sahibləri üçün faydalı məqalələr, məsləhətlər və gəlir artırma yolları.',
        ],
    ];
    $oldPostDescriptions = [
        'post01' => 'Doğru taksi parkını seçmək üçün gəlir bölgüsü, iş şərtləri və müştəri bazası kimi amillərə diqqət yetirin. JetPark, şəffaf şərtləri və yüksək qazanc…',
        'post02' => 'JetPark, sahibkarlar üçün sabit gəlir və risklərin minimuma endirilməsi baxımından ideal tərəfdaşdır. Platforma müştəri bazasını genişləndirmək və…',
        'post03' => 'Avtomobilinizi taksi parkına verərkən, müqavilə şərtlərini diqqətlə oxuyun. Gəlir bölgüsü, texniki baxım və sığorta ilə bağlı detalları yoxlayın. JetPark…',
        'post04' => 'JetPark ilə əməkdaşlıq sahibkarlar üçün az riskli və gəlirli bir modeldir. Avtomobilin texniki baxımı və sığorta təminatı sistemin əsas üstünlüklərindən…',
        'post05' => 'Taksi parkına qoşulmaq avtomobil sahiblərinə sabit gəlir gətirə bilər. Avtomobilinizi JetPark kimi etibarlı bir platformaya təhvil verərək gəlir əldə edə…',
        'post06' => 'Müştəri şikayətlərini sakit və peşəkar şəkildə dinləmək vacibdir. Şikayətləri həll etmək üçün effektiv bir sistem yaradın. Sərnişinlərdən gələn rəy və…',
        'post07' => 'Motivasiya mükafatlandırma sistemi ilə artırıla bilər. Yüksək gəlir əldə edən sürücülərə bonuslar təklif edin. Sürücülərin iş şəraitinin…',
        'post08' => 'Gəlir bölgüsü sistemini anlamaq əməkdaşlığın şəffaflığı üçün vacibdir. JetPark kimi platformalarda gəlir bölgüsü ədalətli şəkildə qurulur. Sahibkar və…',
        'post09' => 'Tıxaclara düşməmək üçün alternativ marşrutları əvvəlcədən planlaşdırmaq vacibdir. GPS cihazlarından və ya mobil tətbiqlərdən istifadə edərək şəhərin tıxac…',
        'post10' => 'Avtomobilinizin texniki vəziyyətini qorumaq üçün mütəmadi texniki baxış etdirin. Yağ dəyişmələrinə, təkərlərin vəziyyətinə və əyləc sisteminə xüsusi…',
        'post11' => 'Müştərilərlə nəzakətli və mehriban ünsiyyət müştəri məmnuniyyətini artırır. Avtomobilin təmizliyinə diqqət yetirmək və sərnişinlərin ehtiyaclarına…',
        'post12' => 'Təhlükəsizlik hər şeydən önəmlidir. Yolda diqqətli olmaq, yol nişanlarına əməl etmək və sürət həddini aşmamaq sürücülər üçün vacib qaydalardır.…',
        'post13' => 'Effektiv iş qrafiki qurmaq sürücülərin həm qazancını artırır, həm də yorğunluğu azaldır. Sürücülər pik saatlarda daha çox işləyərək sərnişin tapmaq…',
    ];

    // Səhifələr: köhnə ilkin mətn boşaldılır — data/seo.php-dəki yeni mətn tətbiq olunur.
    if (is_file(store_path('seo'))) {
        store_update('seo', function (array $all) use ($oldPages) {
            foreach ($oldPages as $key => $old) {
                foreach ($old as $field => $text) {
                    if (($all[$key][$field] ?? null) === $text) {
                        $all[$key][$field] = '';
                    }
                }
            }
            return $all;
        });
    }

    // Yazılar: SEO başlıq əlavə olunur (başlıq dəyişdirilməyibsə), köhnə avtomatik təsvir yenisi ilə əvəzlənir.
    if (is_file(store_path('posts'))) {
        $seed = array_column(store_defaults('posts'), null, 'id');
        store_update('posts', function (array $list) use ($seed, $oldPostDescriptions) {
            foreach ($list as &$p) {
                $new = $seed[$p['id']] ?? null;
                if (!$new) {
                    continue;
                }
                if (($p['seo_title'] ?? '') === '' && $p['title'] === $new['title']) {
                    $p['seo_title'] = $new['seo_title'];
                }
                if (($p['description'] ?? '') === ($oldPostDescriptions[$p['id']] ?? null)) {
                    $p['description'] = $new['description'];
                }
            }
            unset($p);
            return $list;
        });
    }
}
