<?php
// Admin panelin köməkçi funksiyaları: marşrutlar, formalar, bildirişlər.

function admin_url(string $route = 'dashboard', array $params = []): string
{
    $query = http_build_query(array_merge(['r' => $route], $params));
    return ADMIN_URL . '?' . $query;
}

function admin_redirect(string $route = 'dashboard', array $params = []): void
{
    redirect(admin_url($route, $params), 303);
}

function admin_flash(?string $type = null, ?string $message = null): ?array
{
    if ($type !== null) {
        $_SESSION['admin_flash'][] = ['type' => $type, 'message' => $message];
        return null;
    }
    $all = $_SESSION['admin_flash'] ?? [];
    unset($_SESSION['admin_flash']);
    return $all;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/** POST sorğularında CSRF tokenini yoxlayır. */
function verify_csrf(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', (string) ($_POST['csrf'] ?? ''))) {
        admin_flash('error', t('common.csrf'));
        redirect($_SERVER['REQUEST_URI'] ?? ADMIN_URL, 303);
    }
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function post_str(string $key, int $max = 5000): string
{
    $value = $_POST[$key] ?? '';
    if (!is_string($value)) {
        return '';
    }
    $value = mb_scrub(str_replace("\r\n", "\n", $value), 'UTF-8');
    return mb_substr(trim($value), 0, $max);
}

/** Azərbaycan hərflərini latın ekvivalentinə çevirib URL üçün slug yaradır. */
function slugify(string $text): string
{
    $map = ['ə' => 'e', 'Ə' => 'e', 'ı' => 'i', 'I' => 'i', 'İ' => 'i', 'ö' => 'o', 'Ö' => 'o', 'ü' => 'u', 'Ü' => 'u',
        'ğ' => 'g', 'Ğ' => 'g', 'ş' => 's', 'Ş' => 's', 'ç' => 'c', 'Ç' => 'c',
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
        'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
        'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ы' => 'y', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
        'ъ' => '', 'ь' => ''];
    $text = strtr($text, $map);
    $text = mb_strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim(mb_substr($text, 0, 90), '-');
}

function format_dt(?string $date, bool $withTime = true): string
{
    if (!$date) {
        return '—';
    }
    return date($withTime ? 'd.m.Y H:i' : 'd.m.Y', strtotime($date));
}

function format_bytes(float $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, $i ? 1 : 0) . ' ' . $units[$i];
}

function dir_size(string $dir): int
{
    if (!is_dir($dir)) {
        return 0;
    }
    $size = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)) as $file) {
        $size += $file->getSize();
    }
    return $size;
}

function admin_icon(string $name): string
{
    // Lucide ikonları (ISC lisenziyası), 24x24, xətli.
    static $paths = [
        'home'     => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M10 21v-6h4v6"/>',
        'inbox'    => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'file'     => '<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><path d="M14 3v6h6"/><path d="M8 13h8M8 17h5"/>',
        'help'     => '<circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 1 1 3.5 2.3c-.6.3-1 .9-1 1.6V14"/><path d="M12 17.5h.01"/>',
        'star'     => '<path d="m12 3 2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9z"/>',
        'users'    => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M21.5 20a6.5 6.5 0 0 0-4-6"/>',
        'briefcase' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 13h18"/>',
        'car'      => '<path d="M5 17h14v-5l-2-5H7l-2 5z"/><path d="M5 12h14"/><circle cx="8" cy="17" r="2"/><circle cx="16" cy="17" r="2"/>',
        'sliders'  => '<path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3M1 14h6M9 8h6M17 16h6"/>',
        'globe'    => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>',
        'shield'   => '<path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6z"/><path d="m9 12 2 2 4-4"/>',
        'user'     => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'external' => '<path d="M7 17 17 7M8 7h9v9"/>',
        'logout'   => '<path d="M15 4h4v16h-4M10 17l-5-5 5-5M5 12h11"/>',
        'plus'     => '<path d="M12 5v14M5 12h14"/>',
        'trash'    => '<path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/>',
        'edit'     => '<path d="M4 20h4L19 9l-4-4L4 16z"/><path d="m13.5 6.5 4 4"/>',
        'eye'      => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
        'arrow-up' => '<path d="m6 15 6-6 6 6"/>',
        'arrow-down' => '<path d="m6 9 6 6 6-6"/>',
        'download' => '<path d="M12 3v12M7 10l5 5 5-5M4 21h16"/>',
        'phone'    => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/>',
        'message'  => '<path d="M21 12a8.5 8.5 0 0 1-12.4 7.6L3 21l1.4-5.4A8.5 8.5 0 1 1 21 12z"/>',
        'menu'     => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'layers'   => '<path d="m12 3 9 5-9 5-9-5z"/><path d="m3 13 9 5 9-5"/>',
        'server'   => '<rect x="3" y="4" width="18" height="7" rx="2"/><rect x="3" y="13" width="18" height="7" rx="2"/><path d="M7 7.5h.01M7 16.5h.01"/>',
    ];
    return '<svg class="ai" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? '') . '</svg>';
}

// ---------- Form sahələri ----------

function field_text(string $name, string $label, ?string $value, array $opt = []): string
{
    $id = 'f-' . preg_replace('/[^a-z0-9]+/i', '-', $name);
    $type = $opt['type'] ?? 'text';
    $attrs = '';
    foreach (['placeholder', 'maxlength', 'min', 'max', 'step', 'autocomplete', 'pattern', 'inputmode'] as $a) {
        if (isset($opt[$a])) {
            $attrs .= ' ' . $a . '="' . e((string) $opt[$a]) . '"';
        }
    }
    if (!empty($opt['required'])) {
        $attrs .= ' required';
    }
    if (!empty($opt['counter'])) {
        $attrs .= ' data-counter="' . (int) $opt['counter'] . '"';
    }
    $html = '<div class="field' . (!empty($opt['error']) ? ' has-error' : '') . '"><label for="' . $id . '">' . e($label) . '</label>';
    if (!empty($opt['textarea'])) {
        $html .= '<textarea id="' . $id . '" name="' . e($name) . '" rows="' . (int) ($opt['rows'] ?? 3) . '"' . $attrs . '>' . e($value) . '</textarea>';
    } else {
        $html .= '<input id="' . $id . '" type="' . e($type) . '" name="' . e($name) . '" value="' . e($value) . '"' . $attrs . '>';
    }
    if (!empty($opt['error'])) {
        $html .= '<p class="field-error">' . e($opt['error']) . '</p>';
    }
    if (!empty($opt['hint'])) {
        $html .= '<p class="field-hint">' . e($opt['hint']) . '</p>';
    }
    return $html . '</div>';
}

function field_textarea(string $name, string $label, ?string $value, array $opt = []): string
{
    return field_text($name, $label, $value, $opt + ['textarea' => true]);
}

function field_select(string $name, string $label, string $value, array $options, array $opt = []): string
{
    $id = 'f-' . preg_replace('/[^a-z0-9]+/i', '-', $name);
    $html = '<div class="field"><label for="' . $id . '">' . e($label) . '</label><select id="' . $id . '" name="' . e($name) . '">';
    foreach ($options as $val => $text) {
        $html .= '<option value="' . e((string) $val) . '"' . ((string) $val === $value ? ' selected' : '') . '>' . e($text) . '</option>';
    }
    $html .= '</select>';
    if (!empty($opt['hint'])) {
        $html .= '<p class="field-hint">' . e($opt['hint']) . '</p>';
    }
    return $html . '</div>';
}

function field_checkbox(string $name, string $label, bool $checked): string
{
    return '<label class="check"><input type="hidden" name="' . e($name) . '" value="0"><input type="checkbox" name="' . e($name) . '" value="1"' . ($checked ? ' checked' : '') . '><span>' . e($label) . '</span></label>';
}

/** Şəkil sahəsi: önizləmə + yeni fayl yükləmək. $value — assets/ daxilində yol. */
function field_image(string $name, string $label, ?string $value, array $opt = []): string
{
    $id = 'f-' . preg_replace('/[^a-z0-9]+/i', '-', $name);
    $preview = $value ? asset($value) : '';
    $html = '<div class="field field-image' . (!empty($opt['error']) ? ' has-error' : '') . '"><label for="' . $id . '">' . e($label) . '</label>';
    $html .= '<div class="image-picker' . ($opt['small'] ?? false ? ' image-picker--small' : '') . '">';
    $html .= '<div class="image-picker__preview">' . ($preview ? '<img src="' . e($preview) . '" alt="">' : '<span>' . admin_icon('layers') . '</span>') . '</div>';
    $html .= '<div class="image-picker__body">';
    $html .= '<input type="hidden" name="' . e($name) . '" value="' . e($value) . '">';
    $html .= '<input id="' . $id . '" type="file" name="' . e($name) . '_file" accept="image/jpeg,image/png,image/webp" data-preview>';
    $html .= '<p class="field-hint">' . e(t('common.image_hint', upload_max_mb())) . '</p>';
    if (!empty($opt['removable']) && $value) {
        $html .= field_checkbox($name . '_remove', t('common.remove_image'), false);
    }
    if (!empty($opt['error'])) {
        $html .= '<p class="field-error">' . e($opt['error']) . '</p>';
    }
    return $html . '</div></div></div>';
}

function submit_bar(?string $cancelRoute = null): string
{
    $html = '<div class="form-actions"><button class="btn btn-primary" type="submit">' . e(t('common.save')) . '</button>';
    if ($cancelRoute) {
        $html .= '<a class="btn btn-ghost" href="' . e(admin_url($cancelRoute)) . '">' . e(t('common.cancel')) . '</a>';
    }
    return $html . '</div>';
}

function status_badge(string $status): string
{
    return '<span class="badge badge--' . e($status) . '">' . e(t('status.' . $status)) . '</span>';
}

/** SEO vəziyyəti: ok; long — Google başlığı (60) və ya təsviri (160 simvol) kəsəcək; empty. */
function seo_state(array $s): string
{
    $title = trim($s['title'] ?? '');
    $description = trim($s['description'] ?? '');
    if ($title === '' || $description === '') {
        return 'empty';
    }
    return mb_strlen($title) > 60 || mb_strlen($description) > 160 ? 'long' : 'ok';
}

/** Rəngli nöqtə (yaşıl / sarı / boz), mənası ipucunda. */
function status_dot(string $state, string $label): string
{
    return '<span class="dot dot--' . e($state) . '" role="img" aria-label="' . e($label) . '" title="' . e($label) . '"></span>';
}

/** Səhifə mətnlərini yadda saxlayır: content_save('about', ['lead' => ...]) */
function content_save(string $section, array $values): void
{
    store_update('content', function (array $all) use ($section, $values) {
        $all[$section] = array_merge($all[$section] ?? [], $values);
        return $all;
    });
}

function type_label(string $type): string
{
    return $type === 'Sürücü' ? t('app.type_driver') : t('app.type_owner');
}

function type_badge(string $type): string
{
    return '<span class="badge badge--' . ($type === 'Sürücü' ? 'driver' : 'owner') . '">' . e(type_label($type)) . '</span>';
}

/** Səhifələmə linkləri. */
function pager(int $page, int $pages, string $route, array $params = []): string
{
    if ($pages <= 1) {
        return '';
    }
    $html = '<nav class="pager">';
    for ($i = 1; $i <= $pages; $i++) {
        $html .= $i === $page
            ? '<span class="is-current">' . $i . '</span>'
            : '<a href="' . e(admin_url($route, $params + ['p' => $i])) . '">' . $i . '</a>';
    }
    return $html . '</nav>';
}

/** Kart redaktoru (ikon + başlıq + mətn), sətirləri əlavə etmək/silmək/yerini dəyişmək olur. */
function cards_editor(string $name, array $cards): string
{
    $row = function (string $key, array $card) use ($name): string {
        $icons = icon_library();
        if (!empty($card['icon']) && !in_array($card['icon'], $icons, true)) {
            $icons[] = $card['icon'];
        }
        $current = $card['icon'] ?? ($icons[0] ?? '');
        $base = $name . '[' . $key . ']';
        $opts = '';
        foreach ($icons as $icon) {
            $label = preg_replace('/\.(svg|png|webp|jpe?g)$/', '', basename($icon));
            $opts .= '<option value="' . e($icon) . '" data-src="' . e(asset($icon)) . '"' . ($icon === $current ? ' selected' : '') . '>' . e($label) . '</option>';
        }
        return '<div class="rep-row">'
            . '<div><span class="field-label">' . e(t('common.icon')) . '</span>'
            . '<div class="icon-select"><img src="' . e($current ? asset($current) : '') . '" alt=""><select name="' . e($base) . '[icon]">' . $opts . '</select></div>'
            . '<input type="file" name="' . e($base) . '[icon_file]" accept="image/png,image/webp,image/jpeg" data-preview title="' . e(t('common.upload_icon')) . '">'
            . '<p class="field-hint">' . e(t('common.upload_icon')) . '</p></div>'
            . '<div>'
            . field_text($base . '[title]', t('common.title'), $card['title'] ?? '', ['maxlength' => 80])
            . field_textarea($base . '[text]', t('common.text'), $card['text'] ?? '', ['rows' => 2, 'maxlength' => 300])
            . '</div>'
            . '<div class="rep-tools">'
            . '<button class="icon-btn" type="button" data-move="up" title="' . e(t('common.up')) . '">' . admin_icon('arrow-up') . '</button>'
            . '<button class="icon-btn" type="button" data-move="down" title="' . e(t('common.down')) . '">' . admin_icon('arrow-down') . '</button>'
            . '<button class="icon-btn is-danger" type="button" data-remove title="' . e(t('common.delete')) . '">' . admin_icon('trash') . '</button>'
            . '</div></div>';
    };

    $html = '<div data-repeater><div class="repeater">';
    foreach ($cards as $card) {
        $html .= $row(preg_replace('/[^a-z0-9]/i', '', $card['id'] ?? new_id()), $card);
    }
    $html .= '</div><template>' . $row('__KEY__', ['title' => '', 'text' => '']) . '</template>';
    $html .= '<p style="margin-top:14px"><button class="btn btn-outline" type="button" data-add>' . admin_icon('plus') . e(t('common.add_row')) . '</button></p></div>';
    return $html;
}

/** Kart redaktorundan gələn məlumatları oxuyur (sıra formadakı kimi saxlanılır). */
function read_cards(string $name, array &$errors): array
{
    $cards = [];
    foreach ((array) ($_POST[$name] ?? []) as $key => $row) {
        if (!is_array($row) || !preg_match('/^[a-z0-9]{1,32}$/i', (string) $key)) {
            continue;
        }
        $title = mb_substr(trim((string) ($row['title'] ?? '')), 0, 80);
        $text = mb_substr(trim(str_replace("\r\n", "\n", (string) ($row['text'] ?? ''))), 0, 300);
        $icon = (string) ($row['icon'] ?? '');
        if (!in_array($icon, icon_library(), true) && !is_asset_path($icon)) {
            $icon = '';
        }
        $file = uploaded_file([$name, $key, 'icon_file']);
        if ($file) {
            try {
                $icon = save_uploaded_image($file, 'icons', 256);
            } catch (RuntimeException $ex) {
                $errors[] = t('common.upload_error', $ex->getMessage());
            }
        }
        if ($title === '' && $text === '') {
            continue;
        }
        $cards[] = ['id' => str_starts_with((string) $key, 'n') ? new_id() : (string) $key, 'icon' => $icon, 'title' => $title, 'text' => $text];
    }
    return $cards;
}

/** Mövcud ikonlar: assets/icons və yüklənmiş ikonlar. */
function icon_library(): array
{
    $icons = [];
    foreach (glob(ROOT . '/assets/icons/*.svg') as $f) {
        if (!str_starts_with(basename($f), 'about-')) {
            $icons[] = 'icons/' . basename($f);
        }
    }
    foreach (glob(ROOT . '/assets/uploads/icons/*.{png,webp,jpg,jpeg}', GLOB_BRACE) ?: [] as $f) {
        $icons[] = 'uploads/icons/' . basename($f);
    }
    return $icons;
}
