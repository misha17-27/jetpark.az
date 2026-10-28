<?php
/**
 * Sadə fayl əsaslı saxlama (verilənlər bazası tələb olunmur).
 * Hər kolleksiya storage/data/<ad>.json faylındadır. Fayl hələ yoxdursa,
 * ilkin məzmun data/<ad>.php faylından götürülür.
 */

function store_path(string $name): string
{
    return ROOT . '/storage/data/' . $name . '.json';
}

function store_defaults(string $name)
{
    $file = ROOT . '/data/' . $name . '.php';
    return is_file($file) ? require $file : [];
}

/** Bir sorğu ərzində eyni faylı təkrar oxumamaq üçün keş. */
function &store_cache(): array
{
    static $cache = [];
    return $cache;
}

function store_get(string $name)
{
    $cache = &store_cache();
    if (array_key_exists($name, $cache)) {
        return $cache[$name];
    }
    $file = store_path($name);
    if (is_file($file)) {
        $data = json_decode((string) file_get_contents($file), true);
        if (is_array($data)) {
            return $cache[$name] = $data;
        }
        error_log('JetPark: ' . $file . ' oxuna bilmədi, ilkin məzmun istifadə olunur.');
    }
    return $cache[$name] = store_defaults($name);
}

/**
 * Faylı kilidləyərək oxuyur, $fn ilə dəyişir və yazır (eyni anda iki yazı bir-birini silməsin).
 * $fn yeni dəyəri qaytarır.
 */
function store_update(string $name, callable $fn)
{
    $file = store_path($name);
    if (!is_dir(dirname($file)) && !mkdir(dirname($file), 0775, true) && !is_dir(dirname($file))) {
        throw new RuntimeException('storage/data qovluğu yaradıla bilmədi');
    }
    $fh = fopen($file, 'c+');
    if (!$fh || !flock($fh, LOCK_EX)) {
        throw new RuntimeException('Fayl kilidlənə bilmədi: ' . $file);
    }
    try {
        $raw = stream_get_contents($fh);
        $current = $raw !== '' ? json_decode($raw, true) : null;
        if (!is_array($current)) {
            $current = store_defaults($name);
        }
        $new = $fn($current);
        $json = json_encode($new, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        if ($json === false) {
            throw new RuntimeException('JSON yaradıla bilmədi: ' . json_last_error_msg());
        }
        ftruncate($fh, 0);
        rewind($fh);
        fwrite($fh, $json);
        fflush($fh);
        $cache = &store_cache();
        $cache[$name] = $new;
    } finally {
        flock($fh, LOCK_UN);
        fclose($fh);
    }
    return $new;
}

function store_put(string $name, $data): void
{
    store_update($name, fn () => $data);
}

function new_id(): string
{
    return bin2hex(random_bytes(6));
}

/** Nöqtəli yol ilə massivdən dəyər: array_get($a, 'home.hero_brand') */
function array_get(array $data, string $path, $default = null)
{
    foreach (explode('.', $path) as $key) {
        if (!is_array($data) || !array_key_exists($key, $data)) {
            return $default;
        }
        $data = $data[$key];
    }
    return $data;
}
