<?php
// Admin panelin interfeys dilləri: Azərbaycan və rus.

const ADMIN_LANGS = ['az', 'ru'];

function admin_lang(): string
{
    $lang = $_SESSION['admin_lang'] ?? ($_COOKIE['admin_lang'] ?? 'az');
    return in_array($lang, ADMIN_LANGS, true) ? $lang : 'az';
}

function set_admin_lang(string $lang): void
{
    if (in_array($lang, ADMIN_LANGS, true)) {
        $_SESSION['admin_lang'] = $lang;
        setcookie('admin_lang', $lang, ['expires' => time() + 31536000, 'path' => ADMIN_URL, 'samesite' => 'Lax', 'httponly' => true]);
    }
}

/** Tərcümə: t('nav.dashboard'), t('posts.deleted', $title) — %s yerinə dəyər qoyulur. */
function t(string $key, ...$args): string
{
    static $dict = null;
    $dict ??= admin_dictionary();
    $text = $dict[$key][admin_lang() === 'ru' ? 1 : 0] ?? $key;
    return $args ? vsprintf($text, $args) : $text;
}

function admin_dictionary(): array
{
    return [
        // Ümumi
        'panel.title'        => ['İdarəetmə paneli', 'Панель управления'],
        'panel.admin'        => ['ADMİN', 'АДМИН'],
        'common.save'        => ['Yadda saxla', 'Сохранить'],
        'common.saved'       => ['Dəyişikliklər yadda saxlanıldı.', 'Изменения сохранены.'],
        'common.cancel'      => ['Ləğv et', 'Отмена'],
        'common.delete'      => ['Sil', 'Удалить'],
        'common.edit'        => ['Redaktə et', 'Изменить'],
        'common.add'         => ['Əlavə et', 'Добавить'],
        'common.back'        => ['Geri', 'Назад'],
        'common.view'        => ['Bax', 'Открыть'],
        'common.search'      => ['Axtar', 'Поиск'],
        'common.all'         => ['Hamısı', 'Все'],
        'common.actions'     => ['Əməliyyatlar', 'Действия'],
        'common.confirm_delete' => ['Silmək istədiyinizə əminsiniz?', 'Точно удалить?'],
        'common.deleted'     => ['Silindi.', 'Удалено.'],
        'common.not_found'   => ['Tapılmadı.', 'Не найдено.'],
        'common.view_site'   => ['Sayta bax', 'Открыть сайт'],
        'common.up'          => ['Yuxarı', 'Вверх'],
        'common.down'        => ['Aşağı', 'Вниз'],
        'common.image'       => ['Şəkil', 'Изображение'],
        'common.upload'      => ['Şəkil yüklə', 'Загрузить изображение'],
        'common.image_hint'  => ['JPG, PNG və ya WebP, maksimum %s MB', 'JPG, PNG или WebP, максимум %s МБ'],
        'common.remove_image' => ['Şəkli sil', 'Удалить изображение'],
        'common.icon'        => ['İkon', 'Иконка'],
        'common.upload_icon' => ['Öz ikonunu yüklə', 'Загрузить свою иконку'],
        'common.title'       => ['Başlıq', 'Заголовок'],
        'common.text'        => ['Mətn', 'Текст'],
        'common.label'       => ['Kiçik başlıq', 'Надзаголовок'],
        'common.button'      => ['Düymə mətni', 'Текст кнопки'],
        'common.add_row'     => ['Kart əlavə et', 'Добавить карточку'],
        'common.highlight_hint' => ['**söz** — yaşıl rənglə vurğulanır, yeni sətir — sətir keçidi.', '**слово** — выделяется зелёным, новая строка — перенос строки.'],
        'common.csrf'        => ['Sessiyanın vaxtı bitib, yenidən cəhd edin.', 'Сессия устарела, попробуйте ещё раз.'],
        'common.forbidden'   => ['Bu bölməyə girişiniz yoxdur.', 'У вас нет доступа к этому разделу.'],
        'common.empty'       => ['Hələ heç nə yoxdur.', 'Пока ничего нет.'],
        'common.yes'         => ['Bəli', 'Да'],
        'common.no'          => ['Xeyr', 'Нет'],
        'common.prev'        => ['Əvvəlki', 'Назад'],
        'common.next'        => ['Növbəti', 'Далее'],
        'common.hero'        => ['Səhifə başlığı (hero)', 'Шапка страницы (hero)'],
        'common.hero_title'  => ['Hero başlığı', 'Заголовок шапки'],
        'common.hero_image'  => ['Hero fon şəkli', 'Фон шапки'],
        'common.alt'         => ['Şəklin təsviri (alt)', 'Описание изображения (alt)'],
        'common.upload_error' => ['Şəkil yüklənmədi: %s', 'Изображение не загружено: %s'],

        // Naviqasiya
        'nav.main'           => ['ƏSAS', 'ОСНОВНОЕ'],
        'nav.content'        => ['MƏZMUN', 'КОНТЕНТ'],
        'nav.site'           => ['SAYT', 'САЙТ'],
        'nav.dashboard'      => ['İdarə paneli', 'Панель управления'],
        'nav.applications'   => ['Müraciətlər', 'Заявки'],
        'nav.posts'          => ['Bloq', 'Блог'],
        'nav.faq'            => ['FAQ', 'FAQ'],
        'nav.advantages'     => ['Üstünlüklər', 'Преимущества'],
        'nav.about'          => ['Haqqımızda', 'О нас'],
        'nav.owners'         => ['Sahibkarlar', 'Владельцам'],
        'nav.drivers'        => ['Sürücülər', 'Водителям'],
        'nav.texts'          => ['Mətnlər', 'Тексты'],
        'nav.seo'            => ['Səhifələr və SEO', 'Страницы и SEO'],
        'nav.settings'       => ['Parametrlər', 'Настройки'],
        'nav.users'          => ['İstifadəçilər', 'Пользователи'],
        'nav.profile'        => ['Profil', 'Профиль'],
        'nav.logout'         => ['Çıxış', 'Выход'],

        // Giriş
        'login.title'        => ['Panelə giriş', 'Вход в панель'],
        'login.subtitle'     => ['Saytın məzmununun idarə edilməsi', 'Управление содержимым сайта'],
        'login.email'        => ['E-poçt', 'E-mail'],
        'login.password'     => ['Parol', 'Пароль'],
        'login.submit'       => ['Daxil ol', 'Войти'],
        'login.failed'       => ['E-poçt və ya parol yanlışdır.', 'Неверный e-mail или пароль.'],
        'login.locked'       => ['Çox sayda uğursuz cəhd. %s dəqiqə sonra yenidən cəhd edin.', 'Слишком много попыток. Попробуйте через %s мин.'],
        'login.captcha'      => ['Bot yoxlamasını keçin.', 'Пройдите проверку на бота.'],
        'login.expired'      => ['Sessiyanın vaxtı bitdi, yenidən daxil olun.', 'Сессия истекла, войдите снова.'],
        'login.logged_out'   => ['Paneldən çıxdınız.', 'Вы вышли из панели.'],

        // İlk quraşdırma
        'setup.title'        => ['İlk quraşdırma', 'Первоначальная настройка'],
        'setup.subtitle'     => ['Panel üçün administrator hesabı yaradın', 'Создайте учётную запись администратора'],
        'setup.submit'       => ['Hesab yarat və daxil ol', 'Создать и войти'],

        // İstifadəçi sahələri
        'user.name'          => ['Ad', 'Имя'],
        'user.email'         => ['E-poçt', 'E-mail'],
        'user.role'          => ['Rol', 'Роль'],
        'user.password'      => ['Parol', 'Пароль'],
        'user.password2'     => ['Parolu təkrarlayın', 'Повторите пароль'],
        'user.password_keep' => ['Dəyişmək istəmirsinizsə boş saxlayın', 'Оставьте пустым, чтобы не менять'],
        'user.current_password' => ['Hazırkı parol', 'Текущий пароль'],
        'user.new_password'  => ['Yeni parol', 'Новый пароль'],
        'user.last_login'    => ['Son giriş', 'Последний вход'],
        'role.admin'         => ['Administrator', 'Администратор'],
        'role.editor'        => ['Redaktor', 'Редактор'],
        'role.hint'          => ['Redaktor məzmunu və müraciətləri idarə edir, parametrlərə və istifadəçilərə girişi yoxdur.', 'Редактор управляет контентом и заявками, но не видит настройки и пользователей.'],
        'user.err_name'      => ['Adı daxil edin.', 'Введите имя.'],
        'user.err_email'     => ['Düzgün e-poçt daxil edin.', 'Введите корректный e-mail.'],
        'user.err_email_taken' => ['Bu e-poçt artıq istifadə olunur.', 'Этот e-mail уже используется.'],
        'user.err_password'  => ['Parol ən azı 8 simvol olmalıdır.', 'Пароль должен быть не короче 8 символов.'],
        'user.err_password2' => ['Parollar eyni deyil.', 'Пароли не совпадают.'],
        'user.err_current'   => ['Hazırkı parol yanlışdır.', 'Текущий пароль неверный.'],
        'user.err_self'      => ['Öz hesabınızı silə bilməzsiniz.', 'Нельзя удалить свою учётную запись.'],
        'user.err_last_admin' => ['Ən azı bir administrator qalmalıdır.', 'Должен остаться хотя бы один администратор.'],
        'users.add'          => ['Yeni istifadəçi', 'Новый пользователь'],
        'users.edit'         => ['İstifadəçini redaktə et', 'Редактирование пользователя'],
        'profile.title'      => ['Mənim profilim', 'Мой профиль'],
        'profile.password'   => ['Parolu dəyiş', 'Сменить пароль'],

        // İdarə paneli
        'dash.new'           => ['YENİ MÜRACİƏTLƏR', 'НОВЫЕ ЗАЯВКИ'],
        'dash.month'         => ['BU AY', 'ЗА МЕСЯЦ'],
        'dash.total'         => ['CƏMİ MÜRACİƏT', 'ВСЕГО ЗАЯВОК'],
        'dash.posts'         => ['BLOQ YAZILARI', 'СТАТЕЙ В БЛОГЕ'],
        'dash.faq'           => ['FAQ SUALLARI', 'ВОПРОСОВ FAQ'],
        'dash.recent'        => ['Son müraciətlər', 'Последние заявки'],
        'dash.all'           => ['Hamısı', 'Все'],
        'dash.no_apps'       => ['Hələ müraciət yoxdur.', 'Заявок пока нет.'],
        'dash.by_type'       => ['Növ üzrə', 'По типу'],
        'dash.quick'         => ['Tez keçidlər', 'Быстрые ссылки'],
        'dash.new_post'      => ['Yeni yazı', 'Новая статья'],
        'dash.server'        => ['Server', 'Сервер'],
        'dash.php'           => ['PHP versiyası', 'Версия PHP'],
        'dash.storage'       => ['storage/ yazıla bilir', 'storage/ доступна для записи'],
        'dash.uploads'       => ['uploads/ yazıla bilir', 'uploads/ доступна для записи'],
        'dash.mail'          => ['mail() funksiyası', 'Функция mail()'],
        'dash.gd'            => ['Şəkil emalı (GD/WebP)', 'Обработка изображений (GD/WebP)'],
        'dash.disk'          => ['Boş disk sahəsi', 'Свободно на диске'],
        'dash.upload_max'    => ['Maks. fayl ölçüsü', 'Макс. размер файла'],
        'dash.data_size'     => ['Məlumat və yükləmələr', 'Данные и загрузки'],
        'dash.ok'            => ['Bəli', 'Да'],
        'dash.fail'          => ['Xeyr', 'Нет'],
        'dash.hello'         => ['Xoş gəldiniz, %s!', 'Добро пожаловать, %s!'],

        // Müraciətlər
        'app.type'           => ['Növ', 'Тип'],
        'app.name'           => ['Ad, soyad', 'Имя, фамилия'],
        'app.phone'          => ['Telefon', 'Телефон'],
        'app.source'         => ['Səhifə', 'Страница'],
        'app.date'           => ['Tarix', 'Дата'],
        'app.status'         => ['Status', 'Статус'],
        'app.note'           => ['Qeyd', 'Заметка'],
        'app.note_hint'      => ['Yalnız panel istifadəçiləri görür', 'Видна только пользователям панели'],
        'app.ip'             => ['IP ünvanı', 'IP-адрес'],
        'app.details'        => ['Müraciət', 'Заявка'],
        'app.call'           => ['Zəng et', 'Позвонить'],
        'app.whatsapp'       => ['WhatsApp-da yaz', 'Написать в WhatsApp'],
        'app.export'         => ['CSV yüklə', 'Скачать CSV'],
        'app.search_ph'      => ['Ad, soyad və ya telefon', 'Имя, фамилия или телефон'],
        'app.type_owner'     => ['Sahibkar', 'Владелец авто'],
        'app.type_driver'    => ['Sürücü', 'Водитель'],
        'status.new'         => ['Yeni', 'Новая'],
        'status.processing'  => ['Baxılır', 'В работе'],
        'status.done'        => ['Tamamlandı', 'Завершена'],
        'app.found'          => ['%s müraciət', 'Заявок: %s'],

        // Bloq
        'posts.add'          => ['Yeni yazı', 'Новая статья'],
        'posts.edit'         => ['Yazını redaktə et', 'Редактирование статьи'],
        'posts.title'        => ['Başlıq', 'Заголовок'],
        'posts.slug'         => ['Ünvan (slug)', 'Адрес (slug)'],
        'posts.slug_hint'    => ['Boş qalarsa başlıqdan avtomatik yaradılır', 'Если пусто — создаётся из заголовка'],
        'posts.content'      => ['Mətn', 'Текст статьи'],
        'posts.description'  => ['Qısa təsvir (SEO)', 'Краткое описание (SEO)'],
        'posts.description_hint' => ['Axtarış nəticələrində görünür. Boş qalarsa mətndən götürülür.', 'Показывается в поиске. Если пусто — берётся из текста.'],
        'posts.date'         => ['Dərc tarixi', 'Дата публикации'],
        'posts.status'       => ['Status', 'Статус'],
        'posts.published'    => ['Dərc olunub', 'Опубликована'],
        'posts.draft'        => ['Qaralama', 'Черновик'],
        'posts.scheduled'    => ['Planlaşdırılıb', 'Запланирована'],
        'posts.image'        => ['Əsas şəkil', 'Главное изображение'],
        'posts.err_title'    => ['Başlığı daxil edin.', 'Введите заголовок.'],
        'posts.err_slug'     => ['Bu ünvan artıq istifadə olunur.', 'Этот адрес уже занят.'],
        'posts.err_image'    => ['Əsas şəkli yükləyin.', 'Загрузите главное изображение.'],
        'posts.search_ph'    => ['Başlıq üzrə axtarış', 'Поиск по заголовку'],

        // FAQ
        'faq.add'            => ['Yeni sual', 'Новый вопрос'],
        'faq.edit'           => ['Sualı redaktə et', 'Редактирование вопроса'],
        'faq.question'       => ['Sual', 'Вопрос'],
        'faq.answer'         => ['Cavab', 'Ответ'],
        'faq.err'            => ['Sualı və cavabı daxil edin.', 'Введите вопрос и ответ.'],
        'faq.home_hint'      => ['Ana səhifədə ilk %s sual göstərilir (Mətnlər bölməsində dəyişmək olar).', 'На главной показываются первые %s вопросов (меняется в разделе «Тексты»).'],

        // Üstünlüklər və səhifələr
        'adv.hint'           => ['Ana səhifədə və "Haqqımızda" səhifəsində göstərilən kartlar.', 'Карточки на главной и на странице «О нас».'],
        'about.block'        => ['"Haqqımızda" bloku (ana səhifə və Haqqımızda səhifəsi)', 'Блок «О нас» (главная и страница «О нас»)'],
        'about.lead'         => ['Əsas cümlə', 'Главная фраза'],
        'about.point1'       => ['Birinci bənd', 'Первый пункт'],
        'about.point2'       => ['İkinci bənd', 'Второй пункт'],
        'service.intro'      => ['Giriş bloku', 'Вступительный блок'],
        'service.cards'      => ['"Nə təklif edirik?" kartları', 'Карточки «Что мы предлагаем»'],
        'service.note'       => ['Kartlardan sonra qeyd', 'Примечание под карточками'],
        'service.form_image' => ['Müraciət formunun yanındakı şəkil', 'Изображение рядом с формой'],

        // Mətnlər
        'texts.home_hero'    => ['Ana səhifə — yuxarı blok', 'Главная — первый экран'],
        'texts.hero_brand'   => ['Yaşıl başlıq', 'Зелёный заголовок'],
        'texts.hero_tagline' => ['Ağ başlıq', 'Белый заголовок'],
        'texts.hero_tagline_hint' => ['Yeni sətir — böyük ekranda sətir keçidi', 'Новая строка — перенос на больших экранах'],
        'texts.hero_image'   => ['Sağdakı şəkil', 'Изображение справа'],
        'texts.home_features' => ['Ana səhifə — üstünlüklər bloku', 'Главная — блок преимуществ'],
        'texts.home_faq'     => ['Ana səhifə — FAQ bloku', 'Главная — блок FAQ'],
        'texts.faq_count'    => ['Göstərilən sualların sayı', 'Сколько вопросов показывать'],
        'texts.form'         => ['Müraciət formu', 'Форма заявки'],
        'texts.form_success' => ['Uğurlu göndərmədən sonra mesaj', 'Сообщение после отправки'],
        'texts.contact'      => ['Əlaqə səhifəsi', 'Страница контактов'],
        'texts.contact_heading' => ['Başlıq', 'Заголовок'],
        'texts.contact_sub'  => ['Alt başlıq', 'Подзаголовок'],
        'texts.show_map'     => ['Formanın yanında xəritəni göstər', 'Показывать карту рядом с формой'],
        'texts.faq_page'     => ['FAQ səhifəsi', 'Страница FAQ'],
        'texts.footer'       => ['Footer', 'Подвал сайта'],
        'texts.copyright'    => ['Müəllif hüququ mətni', 'Текст копирайта'],

        // SEO
        'seo.hint'           => ['Brauzer başlığı, Google üçün təsvir və sosial şəbəkələrdə paylaşım şəkli.', 'Заголовок вкладки, описание для Google и картинка для соцсетей.'],
        'seo.title'          => ['SEO başlıq (title)', 'SEO-заголовок (title)'],
        'seo.description'    => ['SEO təsvir (description)', 'SEO-описание (description)'],
        'seo.og_image'       => ['Sosial şəbəkə şəkli', 'Картинка для соцсетей'],
        'seo.chars'          => ['simvol', 'символов'],
        'seo.open'           => ['Səhifəyə bax', 'Открыть страницу'],
        'page.home'          => ['Ana səhifə', 'Главная'],
        'page.haqqimizda'    => ['Haqqımızda', 'О нас'],
        'page.sahibkarlar'   => ['Sahibkarlar', 'Владельцам'],
        'page.suruculer'     => ['Sürücülər', 'Водителям'],
        'page.faq'           => ['FAQ', 'FAQ'],
        'page.elaqe'         => ['Əlaqə', 'Контакты'],
        'page.blog'          => ['Bloq', 'Блог'],

        // Parametrlər
        'set.general'        => ['Ümumi', 'Общие'],
        'set.site_name'      => ['Saytın adı', 'Название сайта'],
        'set.site_url'       => ['Saytın ünvanı', 'Адрес сайта'],
        'set.site_url_hint'  => ['Məs. https://jetpark.az — sitemap və paylaşım linkləri üçün', 'Напр. https://jetpark.az — для sitemap и ссылок в соцсетях'],
        'set.posts_per_page' => ['Bloqda bir səhifədə yazı sayı', 'Статей на странице блога'],
        'set.contacts'       => ['Əlaqə məlumatları', 'Контакты'],
        'set.phone'          => ['Telefon', 'Телефон'],
        'set.whatsapp'       => ['WhatsApp nömrəsi', 'Номер WhatsApp'],
        'set.whatsapp_hint'  => ['Yalnız rəqəmlər, məs. 994107152344', 'Только цифры, напр. 994107152344'],
        'set.email'          => ['E-poçt', 'E-mail'],
        'set.address'        => ['Ünvan (footer)', 'Адрес (в подвале)'],
        'set.address_short'  => ['Qısa ünvan (Əlaqə səhifəsi)', 'Короткий адрес (страница контактов)'],
        'set.map_url'        => ['Google Maps linki', 'Ссылка Google Maps'],
        'set.map_embed'      => ['Xəritənin embed linki', 'Ссылка для встраивания карты'],
        'set.social'         => ['Sosial şəbəkələr', 'Соцсети'],
        'set.mail'           => ['Müraciət bildirişləri', 'Уведомления о заявках'],
        'set.mail_to'        => ['Bildirişlər hansı e-poçta gəlsin', 'Куда присылать заявки'],
        'set.mail_from'      => ['Göndərən ünvan', 'Адрес отправителя'],
        'set.mail_from_hint' => ['Saytın domenində olmalıdır, məs. no-reply@jetpark.az', 'Должен быть на домене сайта, напр. no-reply@jetpark.az'],
        'set.security'       => ['Təhlükəsizlik (Cloudflare Turnstile)', 'Безопасность (Cloudflare Turnstile)'],
        'set.turnstile_hint' => ['Hər iki açar doldurularsa, giriş səhifəsində bot yoxlaması görünür.', 'Если заполнить оба ключа, на странице входа появится проверка на бота.'],
        'set.site_key'       => ['Site key', 'Site key'],
        'set.secret_key'     => ['Secret key', 'Secret key'],
        'set.secret_keep'    => ['Saxlanılıb. Dəyişmək üçün yenisini daxil edin.', 'Сохранён. Введите новый, чтобы изменить.'],
        'set.err_url'        => ['Saytın ünvanı http:// və ya https:// ilə başlamalıdır.', 'Адрес сайта должен начинаться с http:// или https://.'],
    ];
}
