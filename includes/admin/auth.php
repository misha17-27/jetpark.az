<?php
// İstifadəçilər, giriş/çıxış, rollar və giriş cəhdlərinin məhdudlaşdırılması.

const ADMIN_IDLE_TIMEOUT = 4 * 3600;  // 4 saat fəaliyyətsizlikdən sonra çıxış
const LOGIN_MAX_ATTEMPTS = 5;
const LOGIN_LOCK_SECONDS = 15 * 60;

function users(): array
{
    return store_get('users');
}

function find_user(string $id): ?array
{
    foreach (users() as $u) {
        if ($u['id'] === $id) {
            return $u;
        }
    }
    return null;
}

function find_user_by_email(string $email): ?array
{
    $email = mb_strtolower(trim($email));
    foreach (users() as $u) {
        if (mb_strtolower($u['email']) === $email) {
            return $u;
        }
    }
    return null;
}

function current_user(): ?array
{
    static $user = false;
    if ($user !== false) {
        return $user;
    }
    $id = $_SESSION['admin_user'] ?? null;
    if (!$id) {
        return $user = null;
    }
    if (time() - ($_SESSION['admin_seen'] ?? 0) > ADMIN_IDLE_TIMEOUT) {
        admin_logout();
        admin_flash('error', t('login.expired'));
        return $user = null;
    }
    $found = find_user($id);
    // Parol dəyişdirildikdən sonra digər sessiyalar etibarsız olur.
    if (!$found || ($_SESSION['admin_pwv'] ?? '') !== password_version($found)) {
        admin_logout();
        return $user = null;
    }
    $_SESSION['admin_seen'] = time();
    return $user = $found;
}

function password_version(array $user): string
{
    return substr(hash('sha256', $user['password']), 0, 16);
}

function is_admin(): bool
{
    return (current_user()['role'] ?? '') === 'admin';
}

function admin_login(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['admin_user'] = $user['id'];
    $_SESSION['admin_pwv'] = password_version($user);
    $_SESSION['admin_seen'] = time();
    store_update('users', function (array $list) use ($user) {
        foreach ($list as &$u) {
            if ($u['id'] === $user['id']) {
                $u['last_login'] = date('Y-m-d H:i:s');
            }
        }
        return $list;
    });
}

function admin_logout(): void
{
    unset($_SESSION['admin_user'], $_SESSION['admin_pwv'], $_SESSION['admin_seen']);
    session_regenerate_id(true);
}

// ---------- Giriş cəhdlərinin məhdudlaşdırılması (IP üzrə) ----------

function login_attempt_key(): string
{
    return hash('sha256', client_ip() ?: 'unknown');
}

/** Bloklanıbsa qalan dəqiqələri qaytarır, əks halda 0. */
function login_locked_minutes(): int
{
    $rec = store_get('login_attempts')[login_attempt_key()] ?? null;
    if ($rec && ($rec['locked_until'] ?? 0) > time()) {
        return (int) ceil(($rec['locked_until'] - time()) / 60);
    }
    return 0;
}

function login_register_failure(): void
{
    $key = login_attempt_key();
    store_update('login_attempts', function (array $all) use ($key) {
        $now = time();
        // Köhnə qeydləri təmizləyirik.
        $all = array_filter($all, fn ($r) => ($r['last'] ?? 0) > $now - 86400);
        $rec = $all[$key] ?? ['count' => 0, 'locked_until' => 0];
        if (($rec['locked_until'] ?? 0) < $now && ($rec['last'] ?? 0) < $now - LOGIN_LOCK_SECONDS) {
            $rec['count'] = 0;
        }
        $rec['count']++;
        $rec['last'] = $now;
        if ($rec['count'] >= LOGIN_MAX_ATTEMPTS) {
            $rec['locked_until'] = $now + LOGIN_LOCK_SECONDS;
            $rec['count'] = 0;
        }
        $all[$key] = $rec;
        return $all;
    });
}

function login_clear_failures(): void
{
    $key = login_attempt_key();
    store_update('login_attempts', function (array $all) use ($key) {
        unset($all[$key]);
        return $all;
    });
}

// ---------- Cloudflare Turnstile ----------

function turnstile_enabled(): bool
{
    return config('turnstile_site_key') && config('turnstile_secret_key');
}

function turnstile_verify(?string $token): bool
{
    if (!$token) {
        return false;
    }
    $payload = http_build_query([
        'secret'   => config('turnstile_secret_key'),
        'response' => $token,
        'remoteip' => client_ip(),
    ]);
    $context = stream_context_create(['http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => $payload,
        'timeout' => 8,
    ]]);
    $raw = @file_get_contents('https://challenges.cloudflare.com/turnstile/v0/siteverify', false, $context);
    $data = $raw ? json_decode($raw, true) : null;
    return !empty($data['success']);
}

// ---------- İstifadəçi məlumatlarının yoxlanması ----------

/** Xətaların siyahısını qaytarır. $editing — redaktə olunan istifadəçi (yeni üçün null). */
function validate_user_input(array $in, ?array $editing, bool $passwordRequired): array
{
    $errors = [];
    if (mb_strlen(trim($in['name'] ?? '')) < 2) {
        $errors['name'] = t('user.err_name');
    }
    $email = trim($in['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = t('user.err_email');
    } elseif (($other = find_user_by_email($email)) && (!$editing || $other['id'] !== $editing['id'])) {
        $errors['email'] = t('user.err_email_taken');
    }
    $pass = (string) ($in['password'] ?? '');
    if ($pass !== '' || $passwordRequired) {
        if (mb_strlen($pass) < 8) {
            $errors['password'] = t('user.err_password');
        } elseif ($pass !== (string) ($in['password2'] ?? '')) {
            $errors['password2'] = t('user.err_password2');
        }
    }
    return $errors;
}
