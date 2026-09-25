<?php
// ============================================================
// Rate Limiting and Security Functions
// ============================================================

class RateLimiter {
    private static $max_attempts = 5;
    private static $timeout = 3600; // 1 hour

    public static function check($identifier, $max_attempts = 5, $timeout = 3600) {
        $cache_file = __DIR__ . '/cache/' . md5($identifier) . '.cache';

        if (!is_dir(__DIR__ . '/cache')) {
            mkdir(__DIR__ . '/cache', 0755, true);
        }

        $attempts = 0;
        $last_attempt = 0;

        if (file_exists($cache_file)) {
            $data = unserialize(file_get_contents($cache_file));
            $attempts = $data['attempts'] ?? 0;
            $last_attempt = $data['timestamp'] ?? 0;

            // Reset if timeout has passed
            if (time() - $last_attempt > $timeout) {
                $attempts = 0;
            }
        }

        if ($attempts >= $max_attempts) {
            return false;
        }

        // Update cache
        $attempts++;
        $data = ['attempts' => $attempts, 'timestamp' => time()];
        file_put_contents($cache_file, serialize($data));

        return true;
    }

    public static function reset($identifier) {
        $cache_file = __DIR__ . '/cache/' . md5($identifier) . '.cache';
        if (file_exists($cache_file)) {
            unlink($cache_file);
        }
    }
}

// ============================================================
// Security Logging
// ============================================================

class SecurityLogger {
    public static function log($event, $details = []) {
        $log_file = __DIR__ . '/logs/security.log';

        if (!is_dir(__DIR__ . '/logs')) {
            mkdir(__DIR__ . '/logs', 0755, true);
        }

        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_id' => $_SESSION['user_id'] ?? null,
            'details' => $details,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];

        $log_line = json_encode($log_entry, JSON_UNESCAPED_UNICODE) . "\n";
        file_put_contents($log_file, $log_line, FILE_APPEND);
    }

    public static function logLoginAttempt($email, $success = false) {
        self::log('login_attempt', [
            'email' => $email,
            'success' => $success
        ]);
    }

    public static function logFileUpload($filename, $size, $success = false) {
        self::log('file_upload', [
            'filename' => $filename,
            'size' => $size,
            'success' => $success
        ]);
    }

    public static function logQueryExecution($query, $error = null) {
        if (DEBUG_MODE) {
            self::log('query_execution', [
                'query' => $query,
                'error' => $error
            ]);
        }
    }
}

// ============================================================
// Database Query Logging (optional)
// ============================================================

class QueryLogger {
    public static $queries = [];

    public static function add($query, $execution_time = 0) {
        if (DEBUG_MODE) {
            self::$queries[] = [
                'query' => $query,
                'time' => $execution_time,
                'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)
            ];
        }
    }

    public static function getAll() {
        return self::$queries;
    }

    public static function getTotalTime() {
        return array_sum(array_column(self::$queries, 'time'));
    }
}

// ============================================================
// Performance Helpers
// ============================================================

function getExecutionTime() {
    global $execution_start;
    return microtime(true) - ($execution_start ?? microtime(true));
}

$execution_start = microtime(true);

// ============================================================
// Email Validation + Sending
// ============================================================

class EmailHelper {
    public static function sendVerificationCode($email, $name, $code) {
        $subject = "کد تایید بوتیک پاریس";
        $message = "
سلام {$name}،

کد تایید شما: {$code}

این کد برای ۱۰ دقیقه معتبر است.

با تشکر
تیم بوتیک پاریس
        ";

        $headers = "From: " . MAIL_FROM . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        return @mail($email, $subject, $message, $headers);
    }

    public static function sendOrderNotification($email, $order_id, $amount) {
        $subject = "تایید سفارش شما - بوتیک پاریس";
        $message = "
سلام،

سفارش شما با کد {$order_id} ثبت شد.
مبلغ: " . number_format($amount) . " تومان

سفارش شما به‌زودی برای ارسال آماده خواهد شد.

کد رهگیری: {$order_id}

با تشکر
تیم بوتیک پاریس
        ";

        $headers = "From: " . MAIL_FROM . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        return @mail($email, $subject, $message, $headers);
    }
}

// ============================================================
// Database Connection Pooling Helper (Simple)
// ============================================================

function getDbConnection() {
    global $conn;
    if (!$conn || !$conn->ping()) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $conn->set_charset("utf8mb4");
    }
    return $conn;
}
?>
