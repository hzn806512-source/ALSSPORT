<?php
/**
 * db.php — نسخه نهایی و ارگانیک
 */
if (defined('DB_INCLUDED')) return;
define('DB_INCLUDED', true);

require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    $cookie_params = [
        'lifetime' => SESSION_TIMEOUT ?? 3600,
        'path' => '/',
        'domain' => '',
        'secure' => COOKIE_SECURE ?? false,
        'httponly' => COOKIE_HTTPONLY ?? true,
        'samesite' => COOKIE_SAMESITE ?? 'Lax'
    ];
    session_set_cookie_params($cookie_params);
    session_start();
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        die("خطای اتصال به دیتابیس: " . $conn->connect_error);
    } else {
        die("خطای اتصال به پایگاه‌داده. لطفاً بعداً تلاش کنید.");
    }
}

$conn->set_charset("utf8mb4");

// آپدیت خودکار ساختار (در حالت دیباگ)
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    @$conn->query("CREATE TABLE IF NOT EXISTS backgrounds (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        image_url VARCHAR(500) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $columns_to_add = [
        'products' => [
            'gallery_images' => 'LONGTEXT DEFAULT NULL',
            'available_colors' => 'TEXT DEFAULT NULL'
        ],
        'orders' => [
            'status' => "VARCHAR(20) DEFAULT 'pending'",
            'authority' => "VARCHAR(255) NULL",
            'ref_id' => "VARCHAR(255) NULL",
            'order_details_json' => "LONGTEXT DEFAULT NULL"
        ],
        'testimonials' => [
            'rating' => 'INT DEFAULT 5'
        ]
    ];

    foreach ($columns_to_add as $table => $cols) {
        foreach ($cols as $col_name => $col_def) {
            $check = @$conn->query("SHOW COLUMNS FROM `$table` LIKE '$col_name'");
            if ($check && $check->num_rows == 0) {
                @$conn->query("ALTER TABLE `$table` ADD COLUMN `$col_name` $col_def");
            }
        }
    }
}

// توابع با محافظت کامل
if (!function_exists('cleanInput')) {
    function cleanInput($data) {
        if (is_array($data)) return array_map('cleanInput', $data);
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('executeQuery')) {
    function executeQuery($query, $params = [], $types = '') {
        global $conn;
        $stmt = $conn->prepare($query);
        if (!$stmt) return ['success' => false, 'error' => $conn->error];
        if (!empty($params)) $stmt->bind_param($types, ...$params);
        if (!$stmt->execute()) return ['success' => false, 'error' => $stmt->error];
        return ['success' => true, 'stmt' => $stmt];
    }
}

if (!function_exists('translateColor')) {
    function translateColor($persianColor) {
        $colors = [
            'قرمز' => '#ef4444', 'سرخ' => '#ef4444',
            'آبی' => '#3b82f6', 'سرمه ای' => '#1e3a8a', 'آبی آسمانی' => '#0ea5e9',
            'سبز' => '#22c55e', 'یشمی' => '#064e3b', 'لجنی' => '#3f6212',
            'زرد' => '#eab308', 'طلایی' => '#d4af37',
            'مشکی' => '#000000', 'سیاه' => '#000000',
            'سفید' => '#ffffff',
            'طوسی' => '#6b7280', 'خاکستری' => '#6b7280',
            'نوک مدادی' => '#374151',
            'قهوه ای' => '#78350f', 'کرم' => '#fef3c7', 'شتری' => '#d97706',
            'بنفش' => '#a855f7', 'یاسی' => '#d8b4fe',
            'صورتی' => '#ec4899', 'گلبهی' => '#fb7185',
            'نارنجی' => '#f97316', 'زرشکی' => '#7f1d1d'
        ];
        $clean = trim($persianColor);
        if (preg_match('/^#[a-f0-9]{6}$/i', $clean) || preg_match('/^[a-z]+$/i', $clean)) return $clean;
        return $colors[$clean] ?? '#000000';
    }
}
?>