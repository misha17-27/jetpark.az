<?php
// Şəkil yükləmə və HTML-in təhlükəsizləşdirilməsi.

const UPLOAD_MAX_BYTES = 5 * 1024 * 1024;

function upload_max_mb(): int
{
    $ini = ini_get('upload_max_filesize') ?: '8M';
    $iniBytes = (int) $ini * (['k' => 1024, 'm' => 1048576, 'g' => 1073741824][strtolower(substr($ini, -1))] ?? 1);
    return (int) floor(min(UPLOAD_MAX_BYTES, $iniBytes ?: UPLOAD_MAX_BYTES) / 1048576);
}

/**
 * $_FILES daxilindən faylı götürür. Yol: 'image_file' və ya ['cards', 'own01', 'icon_file'] (iç-içə adlar).
 */
function uploaded_file($path): ?array
{
    $path = (array) $path;
    $root = array_shift($path);
    if (empty($_FILES[$root])) {
        return null;
    }
    $file = [];
    foreach (['name', 'type', 'tmp_name', 'error', 'size'] as $k) {
        $v = $_FILES[$root][$k];
        foreach ($path as $p) {
            $v = $v[$p] ?? null;
        }
        $file[$k] = $v;
    }
    if (!is_int($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    return $file;
}

/**
 * Şəkli yoxlayıb assets/uploads/<qovluq>/ içinə saxlayır və assets/-ə nisbətən yolu qaytarır.
 * $maxWidth verilibsə (GD mövcud olduqda), böyük şəkillər kiçildilir.
 * Xəta olduqda RuntimeException.
 */
function save_uploaded_image(array $file, string $subdir = '', int $maxWidth = 2000): string
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE
            ? 'fayl çox böyükdür'
            : 'yükləmə xətası (' . $file['error'] . ')');
    }
    if ($file['size'] > UPLOAD_MAX_BYTES) {
        throw new RuntimeException('fayl ' . upload_max_mb() . ' MB-dan böyükdür');
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('fayl tapılmadı');
    }
    $info = @getimagesize($file['tmp_name']);
    $types = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
    if (!$info || !isset($types[$info[2]])) {
        throw new RuntimeException('yalnız JPG, PNG və WebP qəbul olunur');
    }
    $ext = $types[$info[2]];

    $subdir = $subdir !== '' ? trim($subdir, '/') : date('Y/m');
    $dir = ROOT . '/assets/uploads/' . $subdir;
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException('qovluq yaradıla bilmədi');
    }
    $base = slugify(pathinfo((string) $file['name'], PATHINFO_FILENAME)) ?: 'image';
    $name = substr($base, 0, 40) . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
    $target = $dir . '/' . $name;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new RuntimeException('fayl saxlanıla bilmədi');
    }
    @chmod($target, 0644);
    if ($info[0] > $maxWidth) {
        resize_image($target, $target, $maxWidth);
    }
    return 'uploads/' . $subdir . '/' . $name;
}

/** Şəkli eni $maxWidth olana qədər kiçildir. GD yoxdursa, olduğu kimi saxlayır. */
function resize_image(string $src, string $dst, int $maxWidth): bool
{
    if (!function_exists('imagecreatetruecolor')) {
        return $src === $dst || copy($src, $dst);
    }
    [$w, $h, $type] = @getimagesize($src) ?: [0, 0, 0];
    if ($w <= $maxWidth) {
        return $src === $dst || copy($src, $dst);
    }
    $loaders = [IMAGETYPE_JPEG => 'imagecreatefromjpeg', IMAGETYPE_PNG => 'imagecreatefrompng', IMAGETYPE_WEBP => 'imagecreatefromwebp'];
    if (!isset($loaders[$type]) || !function_exists($loaders[$type])) {
        return $src === $dst || copy($src, $dst);
    }
    $img = @$loaders[$type]($src);
    if (!$img) {
        return false;
    }
    $nh = (int) round($h * $maxWidth / $w);
    $out = imagecreatetruecolor($maxWidth, $nh);
    imagealphablending($out, false);
    imagesavealpha($out, true);
    imagecopyresampled($out, $img, 0, 0, 0, 0, $maxWidth, $nh, $w, $h);
    $ok = match ($type) {
        IMAGETYPE_JPEG => imagejpeg($out, $dst, 85),
        IMAGETYPE_PNG  => imagepng($out, $dst, 7),
        IMAGETYPE_WEBP => imagewebp($out, $dst, 82),
    };
    imagedestroy($img);
    imagedestroy($out);
    return $ok;
}

/** Bloq kartı üçün kiçik versiya (768px). Uğursuz olarsa, orijinalın yolunu qaytarır. */
function make_thumb(string $assetPath, int $width = 768): string
{
    $src = ROOT . '/assets/' . $assetPath;
    $info = pathinfo($assetPath);
    $thumb = $info['dirname'] . '/' . $info['filename'] . '-' . $width . '.' . $info['extension'];
    $size = @getimagesize($src);
    if ($size && $size[0] > $width && function_exists('imagecreatetruecolor') && resize_image($src, ROOT . '/assets/' . $thumb, $width)) {
        return $thumb;
    }
    return $assetPath;
}

/** assets/ daxilində mövcud şəkil faylına işarə edən təhlükəsiz yol? */
function is_asset_path(string $path): bool
{
    return (bool) preg_match('#^(img|uploads|icons)/[A-Za-z0-9._/-]+\.(webp|jpe?g|png|svg)$#', $path)
        && !str_contains($path, '..')
        && is_file(ROOT . '/assets/' . $path);
}

/** Yalnız admin panelin yüklədiyi faylları silir (assets/uploads daxilində). */
function delete_upload(?string $assetPath): void
{
    if ($assetPath && str_starts_with($assetPath, 'uploads/') && !str_contains($assetPath, '..')) {
        @unlink(ROOT . '/assets/' . $assetPath);
    }
}

/**
 * Sahə üçün şəkil yükləməni emal edir: yeni fayl → yeni yol; "sil" işarəsi → ''; əks halda köhnə dəyər.
 * Xəta mesajını $errors[$name]-ə yazır.
 */
function handle_image_field($name, ?string $current, array &$errors, string $subdir = '', int $maxWidth = 2000): ?string
{
    $path = (array) $name;
    $key = implode('.', $path);
    // Formada saxlanılan cari dəyər (məs. xəta ilə qayıdan formada əvvəl yüklənmiş şəkil).
    $posted = $_POST;
    foreach ($path as $k) {
        $posted = is_array($posted) ? ($posted[$k] ?? null) : null;
    }
    if (is_string($posted) && is_asset_path($posted)) {
        $current = $posted;
    }
    $filePath = $path;
    $filePath[count($filePath) - 1] .= '_file';
    $file = uploaded_file($filePath);
    if ($file) {
        try {
            return save_uploaded_image($file, $subdir, $maxWidth);
        } catch (RuntimeException $ex) {
            $errors[$key] = t('common.upload_error', $ex->getMessage());
            return $current;
        }
    }
    $removeKey = $path;
    $removeKey[count($removeKey) - 1] .= '_remove';
    $remove = $_POST;
    foreach ($removeKey as $k) {
        $remove = is_array($remove) ? ($remove[$k] ?? null) : null;
    }
    if ($remove === '1') {
        return '';
    }
    return $current;
}

/**
 * Redaktordan gələn HTML-i təhlükəsizləşdirir: yalnız icazəli teqlər və atributlar qalır.
 */
function sanitize_html(string $html): string
{
    $allowed = [
        'p' => [], 'br' => [], 'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [],
        'h2' => [], 'h3' => [], 'h4' => [], 'ul' => [], 'ol' => [], 'li' => [], 'blockquote' => [],
        'a' => ['href', 'target'],
    ];
    $html = trim($html);
    if ($html === '') {
        return '';
    }
    $doc = new DOMDocument('1.0', 'UTF-8');
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="UTF-8"><div id="__root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
    libxml_clear_errors();
    $root = $doc->getElementById('__root');
    if (!$root) {
        return '';
    }

    $walk = function (DOMNode $node) use (&$walk, $allowed, $doc) {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMText) {
                continue;
            }
            if (!$child instanceof DOMElement) {
                $node->removeChild($child); // şərhlər və s.
                continue;
            }
            $tag = strtolower($child->tagName);
            if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select', 'svg', 'math', 'template', 'noscript'], true)) {
                $node->removeChild($child);
                continue;
            }
            $walk($child);
            if ($tag === 'div' || $tag === 'span' || !isset($allowed[$tag])) {
                // Teqi silib məzmununu saxlayırıq (div → p kimi davranmasın deyə sətir keçidi).
                if ($tag === 'div' && $child->previousSibling) {
                    $node->insertBefore($doc->createElement('br'), $child);
                }
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }
            foreach (iterator_to_array($child->attributes) as $attr) {
                if (!in_array(strtolower($attr->name), $allowed[$tag], true)) {
                    $child->removeAttribute($attr->name);
                }
            }
            if ($tag === 'a') {
                $href = trim($child->getAttribute('href'));
                if (!preg_match('#^(https?://|mailto:|tel:|/|\#)#i', $href)) {
                    $child->removeAttribute('href');
                }
                if ($child->getAttribute('target') === '_blank') {
                    $child->setAttribute('rel', 'noopener');
                } else {
                    $child->removeAttribute('target');
                }
            }
        }
    };
    $walk($root);

    $out = '';
    foreach ($root->childNodes as $child) {
        $out .= $doc->saveHTML($child);
    }
    // Boş paraqrafları təmizləyirik.
    $out = preg_replace('#<p>(\s|&nbsp;|<br>)*</p>#u', '', $out);
    return trim($out);
}

/** HTML-dən qısa mətn (SEO təsviri üçün). */
function excerpt(string $html, int $len = 155): string
{
    $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags(str_replace(['</p>', '<br>'], ' ', $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    if (mb_strlen($text) <= $len) {
        return $text;
    }
    $cut = mb_substr($text, 0, $len);
    $space = mb_strrpos($cut, ' ');
    return rtrim(mb_substr($cut, 0, $space ?: $len), ' ,.;:') . '…';
}
