<?php
declare(strict_types=1);
require_once __DIR__.'/product.php';

const APP_ROOT = __DIR__;

function load_env_file(string $path): void {
    if (!is_file($path)) return;
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$key,$value] = array_map('trim', explode('=', $line, 2));
        if ($key === '') continue;
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value,1,-1);
        }
        $_ENV[$key] = $value;
        putenv($key.'='.$value);
    }
}

function envv(string $key, mixed $default = null): mixed {
    $v = $_ENV[$key] ?? getenv($key);
    if ($v === false || $v === null || $v === '') return $default;
    $low = strtolower((string)$v);
    if ($low === 'true') return true;
    if ($low === 'false') return false;
    if ($low === 'null') return null;
    return $v;
}

load_env_file(APP_ROOT.'/.env');
foreach(['private_uploads','backups','imports','logs'] as $storageName){$storageDir=APP_ROOT.'/storage/'.$storageName;if(!is_dir($storageDir))@mkdir($storageDir,0700,true);}
if (!is_file(APP_ROOT.'/.env') && !is_file(APP_ROOT.'/config.php')) {
    $script = basename((string)($_SERVER['SCRIPT_NAME'] ?? ''));
    if (!in_array($script, ['install.php','repair.php'], true)) { header('Location: install.php'); exit; }
}
$appEnv = (string)envv('APP_ENV','local');
$isProd = $appEnv === 'production';

ini_set('display_errors', $isProd ? '0' : '1');
ini_set('log_errors', '1');
ini_set('error_log', APP_ROOT.'/storage/logs/php-error.log');
error_reporting(E_ALL);

date_default_timezone_set((string)envv('APP_TIMEZONE','Asia/Tehran'));

$secureCookie = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || envv('SESSION_SECURE', false) === true;
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name((string)envv('SESSION_NAME',APP_SESSION_NAME));
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secureCookie,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

header('X-Content-Type-Options: nosniff');
$isEmbeddableLead=basename((string)($_SERVER['SCRIPT_NAME']??''))==='web-lead.php';if(!$isEmbeddableLead)header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header($isEmbeddableLead?"Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'; frame-ancestors *; form-action 'self'":"Content-Security-Policy: default-src 'self'; img-src 'self' data: blob:; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'; font-src 'self' data:; connect-src 'self'; frame-ancestors 'self'; base-uri 'self'; form-action 'self'");

$config = [
    'app_name' => (string)envv('APP_NAME',APP_PRODUCT_NAME),
    'mode' => in_array((string)envv('APP_MODE','hosted'),['offline','hosted'],true)?(string)envv('APP_MODE','hosted'):'hosted',
    'base_url' => (string)envv('APP_URL',''),
    'timezone' => (string)envv('APP_TIMEZONE','Asia/Tehran'),
    'db' => [
        'host' => (string)envv('DB_HOST','127.0.0.1'),
        'port' => (int)envv('DB_PORT',3306),
        'name' => (string)envv('DB_DATABASE',''),
        'user' => (string)envv('DB_USERNAME','root'),
        'pass' => (string)envv('DB_PASSWORD',''),
        'charset' => 'utf8mb4',
    ],
];

/* Backward compatibility for existing V7 config.php during upgrade. */
if ($config['db']['name'] === '' && is_file(APP_ROOT.'/config.php')) {
    $legacy = require APP_ROOT.'/config.php';
    if (is_array($legacy)) $config = array_replace_recursive($config, $legacy);
}

function db(): PDO {
    static $pdo = null;
    global $config;
    if ($pdo instanceof PDO) return $pdo;
    $d = $config['db'];
    $name = (string)($d['name'] ?? '');
    if ($name === '' || !preg_match('/^[A-Za-z0-9_]+$/', $name)) {
        throw new RuntimeException('Database is not configured. Copy .env.example to .env or run install.php.');
    }
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $d['host'], $d['port'], $name, $d['charset'] ?? 'utf8mb4');
    $pdo = new PDO($dsn, (string)$d['user'], (string)$d['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ]);
    $tz = new DateTimeZone((string)($config['timezone'] ?? 'Asia/Tehran'));
    $offsetSeconds = $tz->getOffset(new DateTimeImmutable('now', $tz));
    $offset = sprintf('%s%02d:%02d', $offsetSeconds < 0 ? '-' : '+', intdiv(abs($offsetSeconds), 3600), intdiv(abs($offsetSeconds) % 3600, 60));
    $pdo->exec('SET time_zone = '.$pdo->quote($offset));
    return $pdo;
}

function e(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

function json_response(array $data, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, private');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function client_ip(): string {
    return substr((string)($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 0, 64);
}

function current_user(): ?array {
    if (empty($_SESSION['user_id'])) return null;
    static $cache = '__unset__';
    if ($cache !== '__unset__') return $cache ?: null;
    $st = db()->prepare('SELECT u.id,u.company_id,u.name,u.email,u.phone,u.extension,u.role,u.title,u.lang,u.active,c.name company_name,c.logo_url,c.primary_color,c.accent_color FROM users u JOIN companies c ON c.id=u.company_id WHERE u.id=? AND u.active=1 LIMIT 1');
    $st->execute([(int)$_SESSION['user_id']]);
    $cache = $st->fetch() ?: null;
    return $cache;
}

function require_login(): array {
    $u = current_user();
    if (!$u) json_response(['ok'=>false,'error'=>'unauthorized'],401);
    return $u;
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return (string)$_SESSION['csrf'];
}

function verify_csrf(): void {
    $token = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf'] ?? ''));
    if ($token === '' || !hash_equals((string)($_SESSION['csrf'] ?? ''), $token)) json_response(['ok'=>false,'error'=>'csrf'],419);
}

function can(string $cap, ?array $u = null): bool {
    $u ??= current_user();
    if (!$u) return false;
    if (in_array($u['role'], ['super_admin','company_admin'], true)) return true;
    $map = [
        'manager'=>['view_all','edit_projects','edit_tasks','edit_sales','edit_customers','edit_procurement','reports','edit_followups','edit_quotes','manage_documents','communications','data_export'],
        'sales'=>['edit_sales','edit_customers','edit_tasks','edit_followups','edit_quotes','manage_documents','communications'],
        'project_manager'=>['edit_projects','edit_tasks','edit_procurement','reports','manage_documents'],
        'technical'=>['edit_tasks','edit_projects','manage_documents'],
        'finance'=>['reports','edit_sales','edit_quotes'],
        'employee'=>['edit_tasks','edit_followups'],
        'viewer'=>[],
    ];
    return in_array($cap, $map[$u['role']] ?? [], true);
}

function require_cap(string $cap): void {
    if (!can($cap)) json_response(['ok'=>false,'error'=>'forbidden'],403);
}

function body_json(): array {
    $raw = file_get_contents('php://input');
    if (strlen((string)$raw) > 2_000_000) json_response(['ok'=>false,'error'=>'payload_too_large'],413);
    $data = json_decode($raw ?: '{}', true);
    return is_array($data) ? $data : [];
}
function req(array $b, string $k, mixed $default = null): mixed { return array_key_exists($k,$b) ? $b[$k] : $default; }
function clean_status(string $s, array $allowed, string $default): string { return in_array($s,$allowed,true)?$s:$default; }

function tenant_ref(string $table, mixed $id, int $companyId, bool $required=false): ?int {
    $id=(int)$id;
    if(!$id){ if($required) json_response(['ok'=>false,'error'=>'reference_required','table'=>$table],422); return null; }
    $allowed=['users','customers','leads','opportunities','projects','tasks','suppliers','rfqs','shipments'];
    if(!in_array($table,$allowed,true)) throw new InvalidArgumentException('Invalid tenant ref table');
    $st=db()->prepare("SELECT id FROM {$table} WHERE id=? AND company_id=? LIMIT 1");
    $st->execute([$id,$companyId]);
    if(!$st->fetchColumn()) json_response(['ok'=>false,'error'=>'invalid_reference','table'=>$table],422);
    return $id;
}

function audit(string $action,string $entityType,?int $entityId,array $meta=[]): void {
    $u=current_user(); if(!$u)return;
    $safeMeta=$meta; unset($safeMeta['password'],$safeMeta['password_hash'],$safeMeta['token'],$safeMeta['secret']);
    $st=db()->prepare('INSERT INTO activity_logs(company_id,user_id,action,entity_type,entity_id,metadata,ip_address,created_at) VALUES(?,?,?,?,?,?,?,NOW())');
    $st->execute([$u['company_id'],$u['id'],$action,$entityType,$entityId,json_encode($safeMeta,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),client_ip()]);
}

function app_setting(string $key, ?string $default=null): ?string {
    $u=current_user(); if(!$u)return $default;
    $st=db()->prepare('SELECT setting_value FROM app_settings WHERE company_id=? AND setting_key=? LIMIT 1');
    $st->execute([$u['company_id'],$key]); $v=$st->fetchColumn(); return $v===false?$default:(string)$v;
}

function password_algo(): string|int|null {
    if (defined('PASSWORD_ARGON2ID')) return PASSWORD_ARGON2ID;
    return PASSWORD_DEFAULT;
}
function hash_password(string $password): string { return password_hash($password, password_algo()); }

function auth_rate_check(string $email, string $ip): array {
    $key = hash('sha256', strtolower(trim($email)).'|'.$ip);
    $st = db()->prepare('SELECT attempts,locked_until FROM login_attempts WHERE attempt_key=? LIMIT 1');
    $st->execute([$key]); $row=$st->fetch();
    if($row && $row['locked_until'] && strtotime($row['locked_until'])>time()) return [false,$key,(int)(strtotime($row['locked_until'])-time())];
    return [true,$key,0];
}
function auth_rate_fail(string $key, string $email, string $ip): void {
    $st=db()->prepare('INSERT INTO login_attempts(attempt_key,email,ip_address,attempts,last_attempt_at,locked_until) VALUES(?,?,?,1,NOW(),NULL) ON DUPLICATE KEY UPDATE attempts=IF(last_attempt_at<DATE_SUB(NOW(),INTERVAL 15 MINUTE),1,attempts+1),last_attempt_at=NOW(),locked_until=IF(IF(last_attempt_at<DATE_SUB(NOW(),INTERVAL 15 MINUTE),1,attempts+1)>=5,DATE_ADD(NOW(),INTERVAL 15 MINUTE),locked_until)');
    $st->execute([$key,strtolower(trim($email)),$ip]);
}
function auth_rate_success(string $key): void { db()->prepare('DELETE FROM login_attempts WHERE attempt_key=?')->execute([$key]); }

function api_bearer_user(): ?array {
    $auth=(string)($_SERVER['HTTP_AUTHORIZATION']??'');
    if(!preg_match('/^Bearer\s+(.+)$/i',$auth,$m)) return null;
    $hash=hash('sha256',$m[1]);
    $st=db()->prepare('SELECT t.id token_id,t.company_id,t.user_id,t.scopes,t.expires_at,u.name,u.email,u.role,u.title,u.lang,u.active,c.name company_name,c.logo_url,c.primary_color,c.accent_color FROM api_tokens t JOIN users u ON u.id=t.user_id JOIN companies c ON c.id=t.company_id WHERE t.token_hash=? AND t.revoked_at IS NULL AND (t.expires_at IS NULL OR t.expires_at>NOW()) AND u.active=1 LIMIT 1');
    $st->execute([$hash]); $u=$st->fetch();
    if($u) db()->prepare('UPDATE api_tokens SET last_used_at=NOW() WHERE id=?')->execute([$u['token_id']]);
    return $u ?: null;
}
