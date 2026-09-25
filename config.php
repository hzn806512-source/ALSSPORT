<?php
// ============================================================
// تنظیمات امنیتی و محیطی
// ============================================================

// خواندن متغیرهای محیطی یا استفاده از مقادیر پیش‌فرض
if (!function_exists('getConfigValue')) {
    function getConfigValue($key, $default = '') {
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return $_ENV[$key];
        }
        $value = getenv($key);
        if ($value !== false && $value !== '') {
            return $value;
        }
        return $default;
    }
}

// ============================================================
// تنظیمات دیتابیس
// ============================================================
define('DB_HOST', getConfigValue('DB_HOST', 'localhost'));
define('DB_USER', getConfigValue('DB_USER', 'root'));
define('DB_PASS', getConfigValue('DB_PASS', ''));
define('DB_NAME', getConfigValue('DB_NAME', 'if0_39948816_paris'));

// ============================================================
// تنظیمات Cloudinary
// ============================================================
define('CLOUDINARY_NAME', getConfigValue('CLOUDINARY_NAME', 'dldqhucgq'));
define('CLOUDINARY_KEY', getConfigValue('CLOUDINARY_KEY', ''));
define('CLOUDINARY_SECRET', getConfigValue('CLOUDINARY_SECRET', ''));

// ============================================================
// تنظیمات درگاه پرداخت
// ============================================================
define('ZARINPAL_MERCHANT', getConfigValue('ZARINPAL_MERCHANT', 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx'));

// ============================================================
// تنظیمات ایمیل
// ============================================================
define('MAIL_FROM', getConfigValue('MAIL_FROM', 'noreply@pizzaparis.xo.je'));
define('MAIL_HOST', getConfigValue('MAIL_HOST', ''));

// ============================================================
// تنظیمات Session
// ============================================================
define('SESSION_TIMEOUT', 3600); // ۱ ساعت
define('COOKIE_SECURE', false); // در production باید true باشد
define('COOKIE_HTTPONLY', true);
define('COOKIE_SAMESITE', 'Lax');

// ============================================================
// تنظیمات Upload
// ============================================================
define('MAX_UPLOAD_SIZE', 64 * 1024 * 1024); // 64MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('UPLOAD_DIR', __DIR__ . '/uploads/');

// ============================================================
// تنظیمات امنیتی کلی
// ============================================================
define('DEBUG_MODE', getConfigValue('DEBUG_MODE', false));
define('APP_ENV', getConfigValue('APP_ENV', 'production'));

// تنظیمات Header امنیتی
if (!DEBUG_MODE) {
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
} else {
    ini_set('display_errors', 1);
}

// ============================================================
// تنظیمات Session امنیتی
// ============================================================
// Note: These are now handled in db.php using session_set_cookie_params()
// ini_set('session.cookie_secure', COOKIE_SECURE ? '1' : '0');
// ini_set('session.cookie_httponly', COOKIE_HTTPONLY ? '1' : '0');
// ini_set('session.cookie_samesite', COOKIE_SAMESITE);
// ini_set('session.gc_maxlifetime', SESSION_TIMEOUT);

// ============================================================
// تابع صفحه‌سازی (Pagination Helper)
// ============================================================
function paginate($total, $per_page = 12, $page = 1) {
    $page = max(1, intval($page));
    $total_pages = ceil($total / $per_page);
    $page = min($page, max(1, $total_pages));

    return [
        'page' => $page,
        'per_page' => $per_page,
        'total' => $total,
        'total_pages' => $total_pages,
        'offset' => ($page - 1) * $per_page,
        'has_prev' => $page > 1,
        'has_next' => $page < $total_pages
    ];
}

// ============================================================
// تابع تولید CSRF Token
// ============================================================
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// تابع بررسی CSRF Token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ============================================================
// تابع Validation
// ============================================================
class Validator {
    private static $errors = [];

    public static function email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function phone($phone) {
        return preg_match('/^09\d{9}$/', $phone) !== false;
    }

    public static function url($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    public static function minLength($str, $min) {
        return strlen($str) >= $min;
    }

    public static function maxLength($str, $max) {
        return strlen($str) <= $max;
    }

    public static function matches($str, $pattern) {
        return preg_match($pattern, $str) !== false;
    }

    public static function addError($field, $message) {
        self::$errors[$field] = $message;
    }

    public static function getErrors() {
        return self::$errors;
    }

    public static function hasErrors() {
        return count(self::$errors) > 0;
    }

    public static function clearErrors() {
        self::$errors = [];
    }
}

// ============================================================
// تابع محافظت علیه XSS
// ============================================================
function sanitizeOutput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// ============================================================
// تابع ریتریو آرایه‌ی GET/POST با ایمنی
// ============================================================
function getParam($key, $default = '', $type = 'string') {
    $value = $_REQUEST[$key] ?? $default;

    switch ($type) {
        case 'int':
            return intval($value);
        case 'float':
            return floatval($value);
        case 'bool':
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        default:
            return strval($value);
    }
}
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');                    // در XAMPP معمولاً خالی است
define('DB_NAME', 'if0_39948816_paris');            // ← نام دیتابیس (اگر تغییر دادید اینجا هم تغییر دهید)

// ==================== تنظیمات سشن ====================
define('SESSION_TIMEOUT', 3600 * 24 * 7); // ۷ روز
define('COOKIE_SECURE', false);           // در لوکال: false | در سرور واقعی با HTTPS: true
define('COOKIE_HTTPONLY', true);
define('COOKIE_SAMESITE', 'Lax');

// ==================== حالت دیباگ ====================
define('DEBUG_MODE', true);               // در محیط واقعی حتماً false کنید

// ==================== آدرس پایه پروژه ====================
define('BASE_URL', 'http://localhost/ALSSPORT/');
?>

