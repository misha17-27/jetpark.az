<?php
// Müraciət formunun qəbulu: yoxlama → admin panel (storage/data/applications.json) → e-poçt bildirişi.
// Saytda JS WhatsApp-ı özü açır və bura arxa fonda yazır; JS olmadan buradan WhatsApp-a yönləndiririk.

require __DIR__ . '/includes/bootstrap.php';

$isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';

function respond(bool $ok, string $message, int $status = 200): void
{
    global $isAjax;

    if ($isAjax) {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['ok' => $ok, 'message' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $formId = preg_replace('/[^a-z0-9-]/', '', (string) ($_POST['form_id'] ?? ''));
    flash($formId, [
        'status'  => $ok ? 'success' : 'error',
        'message' => $message,
        'old'     => $ok ? [] : array_map('strval', array_intersect_key($_POST, array_flip(['name', 'surname', 'phone', 'type']))),
    ]);
    // Yalnız saytın öz səhifələrinə qaytarırıq.
    $back = (string) ($_POST['back'] ?? '');
    if (!str_starts_with($back, BASE_URL) || str_starts_with($back, '//')) {
        $back = url();
    }
    redirect(strtok($back, '#') . '#' . $formId, 303);
}

function clean_text(string $value, int $max): string
{
    $value = mb_scrub($value, 'UTF-8'); // yanlış kodlaşmalı baytları təmizləyir
    $value = trim((string) preg_replace('/\s+/u', ' ', strip_tags($value)));
    return mb_substr($value, 0, $max);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(url(), 303);
}

if (!hash_equals($_SESSION['csrf'] ?? '', (string) ($_POST['csrf'] ?? ''))) {
    respond(false, 'Sessiyanın vaxtı bitib. Səhifəni yeniləyib yenidən cəhd edin.', 419);
}

// Botlar üçün tələ: gizli sahə doludursa və ya forma çox tez göndərilibsə, sakitcə "uğurlu" cavab veririk.
$elapsed = time() - (int) ($_POST['ts'] ?? 0);
if (($_POST['website'] ?? '') !== '' || $elapsed < 3) {
    respond(true, 'Müraciətiniz qəbul edildi. Tezliklə sizinlə əlaqə saxlayacağıq.');
}

// Eyni brauzerdən 30 saniyədə bir müraciət.
if (isset($_SESSION['last_submit']) && time() - $_SESSION['last_submit'] < 30) {
    respond(false, 'Zəhmət olmasa, bir neçə saniyə sonra yenidən cəhd edin.', 429);
}

$name    = clean_text((string) ($_POST['name'] ?? ''), 60);
$surname = clean_text((string) ($_POST['surname'] ?? ''), 60);
$phone   = clean_text((string) ($_POST['phone'] ?? ''), 20);
$type    = in_array($_POST['type'] ?? '', ['Sahibkar', 'Sürücü'], true) ? $_POST['type'] : 'Sahibkar';
$source  = clean_text((string) ($_POST['source'] ?? ''), 60);

if (mb_strlen($name) < 2 || mb_strlen($surname) < 2) {
    respond(false, 'Ad və soyadı daxil edin.', 422);
}
$digits = preg_replace('/\D/', '', $phone);
if (!preg_match('/^[+\d\s()\-]+$/', $phone) || strlen($digits) < 7 || strlen($digits) > 15) {
    respond(false, 'Telefon nömrəsini düzgün daxil edin (məs. +994 50 123 45 67).', 422);
}

$application = [
    'id'         => new_id(),
    'created_at' => date('Y-m-d H:i:s'),
    'type'       => $type,
    'name'       => $name,
    'surname'    => $surname,
    'phone'      => $phone,
    'source'     => $source,
    'ip'         => client_ip(),
    'status'     => 'new',
    'note'       => '',
];

// 1) Admin paneldə saxlayırıq — e-poçt çatmasa belə müraciət itmir.
try {
    store_update('applications', function (array $list) use ($application) {
        array_unshift($list, $application);
        return $list;
    });
    $saved = true;
} catch (Throwable $ex) {
    error_log('JetPark: müraciət saxlanıla bilmədi: ' . $ex->getMessage());
    $saved = false;
}

// 2) E-poçt bildirişi.
$subject = 'Yeni müraciət (' . $type . '): ' . $name . ' ' . $surname;
$body = "Saytdan yeni müraciət daxil olub.\n\n"
    . "Növ: {$type}\n"
    . "Ad: {$name}\n"
    . "Soyad: {$surname}\n"
    . "Telefon: {$phone}\n"
    . "Səhifə: {$source}\n"
    . 'Tarix: ' . $application['created_at'] . "\n\n"
    . 'Bütün müraciətlər: ' . absolute_url('admin/?r=applications') . "\n";
$headers = [
    'From: ' . '=?UTF-8?B?' . base64_encode(config('site_name') . ' sayt') . '?= <' . config('mail_from') . '>',
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit',
];
$mailed = function_exists('mail') && @mail(
    (string) config('mail_to'),
    '=?UTF-8?B?' . base64_encode($subject) . '?=',
    $body,
    implode("\r\n", $headers)
);

if (!$saved && !$mailed) {
    error_log('JetPark: müraciət nə fayla yazıldı, nə də e-poçtla göndərildi: ' . json_encode($application, JSON_UNESCAPED_UNICODE));
    respond(false, 'Xəta baş verdi. Zəhmət olmasa, bizə ' . config('phone') . ' nömrəsi ilə zəng edin.', 500);
}

$_SESSION['last_submit'] = time();
if (!$isAjax) {
    redirect(whatsapp_application_url($application), 303);
}
respond(true, content('form.success'));
