<?php
session_start();
include 'db.php';
include 'security-helpers.php';

header('Content-Type: application/json');

function sendError($message) {
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit();
}

// CSRF check
if (isset($_POST['csrf_token'])) {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        sendError('درخواست غیرمعتبر است');
    }
}

// --- 1. ثبت‌نام کامل و جدید ---
if (isset($_POST['action']) && $_POST['action'] == 'register_full') {
    $name = cleanInput($_POST['name'] ?? '');
    $phone = cleanInput($_POST['phone'] ?? '');
    $email = cleanInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($name) || empty($phone) || empty($email) || empty($password)) {
        sendError('تمام فیلدها الزامی هستند');
    }

    if (!Validator::email($email)) {
        sendError('ایمیل معتبر نیست');
    }

    if (!Validator::phone($phone)) {
        sendError('شماره موبایل معتبر نیست');
    }

    if (!Validator::minLength($password, 6)) {
        sendError('رمز عبور حداقل ۶ حرف باید باشد');
    }

    // بررسی تکراری نبودن ایمیل
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        sendError('این ایمیل قبلاً ثبت‌نام کرده است');
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $is_verified = 1; // مستقیم تایید شود تا کاربر به مشکل نخورد

    $stmt = $conn->prepare("INSERT INTO users (name, phone, email, password, is_verified, profile_pic) VALUES (?, ?, ?, ?, ?, 'https://cdn.jsdelivr.net/gh/microsoft/fluentui-emoji@latest/assets/Person/3D/person_3d.png')");
    $stmt->bind_param("ssssi", $name, $phone, $email, $password_hash, $is_verified);

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_phone'] = $phone;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_pic'] = 'https://cdn.jsdelivr.net/gh/microsoft/fluentui-emoji@latest/assets/Person/3D/person_3d.png';

        echo json_encode(['status' => 'success', 'message' => 'ثبت‌نام با موفقیت انجام شد و وارد شدید']);
    } else {
        sendError('خطا در ثبت‌نام کاربر در پایگاه داده');
    }
    exit();
}

// --- 2. تایید هوشمند آدرس ---
if (isset($_POST['action']) && $_POST['action'] == 'validate_address') {
    $address = cleanInput($_POST['address'] ?? '');
    
    // الگوریتم اعتبارسنجی آدرس جهت جلوگیری از چرت و پرت نوشتن
    if (strlen($address) < 10) {
        sendError('آدرس بسیار کوتاه است. لطفاً خیابان، پلاک و پلاک یا واحد را با جزئیات وارد کنید.');
    }

    // بررسی کلمات نامربوط یا کاراکترهای تکراری بی‌معنی
    $gibberish_patterns = ['asdf', 'qwer', 'xxxx', '1111', '12345678', 'test', 'تست', 'چرت', 'الکی'];
    $lower = mb_strtolower($address);
    foreach ($gibberish_patterns as $pat) {
        if ($lower === $pat || strpos($lower, 'asdfgh') !== false) {
            sendError('آدرس وارد شده معتبر به نظر نمی‌رسد. لطفاً آدرس دقیق پستی خود را وارد کنید.');
        }
    }

    // بررسی اینکه حتماً شامل کلمات کلیدی آدرس باشد یا طول کافی داشته باشد
    echo json_encode(['status' => 'success', 'message' => 'آدرس معتبر است']);
    exit();
}
?>