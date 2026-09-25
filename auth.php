<?php
session_start();
include 'db.php';
include 'security-helpers.php';

header('Content-Type: application/json');

function sendError($message)
{
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit();
}

// تحقق CSRF Token
if (isset($_POST['csrf_token'])) {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        sendError('درخواست غیرمعتبر است');
    }
}

// --- 1. درخواست ثبت نام (ارسال کد تایید) ---
if (isset($_POST['action']) && $_POST['action'] == 'register_request') {
    // Rate limiting
    $register_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if (!RateLimiter::check("register_" . $register_ip, 5, 3600)) {
        SecurityLogger::log('register_limit_exceeded', ['ip' => $register_ip]);
        // sendError('تعداد تلاش‌های ثبت‌نام بیش‌ازحد است. بعداً دوباره تلاش کنید.');
    }
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

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $verify_code = rand(1000, 9999);

    $stmt = $conn->prepare("SELECT id, is_verified FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $check = $stmt->get_result();

    if ($check->num_rows > 0) {
        $user = $check->fetch_assoc();
        if ($user['is_verified'] == 1) {
            sendError('این ایمیل قبلاً ثبت شده است');
        }
        $stmt = $conn->prepare("UPDATE users SET name=?, phone=?, password=?, verification_code=? WHERE email=?");
        $stmt->bind_param("sssss", $name, $phone, $password_hash, $verify_code, $email);
    } else {
        $is_verified = 0;
        $stmt = $conn->prepare("INSERT INTO users (name, phone, email, password, verification_code, is_verified) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $name, $phone, $email, $password_hash, $verify_code, $is_verified);
    }

    if ($stmt->execute()) {
        if (APP_ENV === 'development') {
            // در حالت توسعه، کد hardcoded است
            $verify_code = 1234;
            echo json_encode([
                'status' => 'success',
                'message' => "حالت توسعه: کد تایید = 1234"
            ]);
        } else {
            $subject = "کد تایید بوتیک پاریس";
            $message = "کد تایید شما: $verify_code";
            $headers = "From: " . MAIL_FROM . "\r\nContent-Type: text/plain; charset=UTF-8";
            @mail($email, $subject, $message, $headers);

            echo json_encode([
                'status' => 'success',
                'message' => "کد تایید به ایمیل $email ارسال شد",
                'otp' => $verify_code
            ]);
        }
    } else {
        sendError('خطای ثبت‌نام');
    }
    exit();
}

// --- 2. تایید کد ایمیل ---
if (isset($_POST['action']) && $_POST['action'] == 'verify_code') {
    $email = cleanInput($_POST['email'] ?? '');
    $code = cleanInput($_POST['code'] ?? '');

    if (empty($email) || empty($code)) {
        sendError('فیلدهای مورد نیاز ناقص هستند');
    }

    $stmt = $conn->prepare("SELECT id, name, phone, profile_pic FROM users WHERE email=? AND verification_code=?");
    $stmt->bind_param("ss", $email, $code);
    $stmt->execute();
    $check = $stmt->get_result();

    if ($check->num_rows > 0) {
        $user = $check->fetch_assoc();

        $stmt = $conn->prepare("UPDATE users SET is_verified=1, verification_code=NULL WHERE id=?");
        $stmt->bind_param("i", $user['id']);
        $stmt->execute();

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_phone'] = $user['phone'];
        $_SESSION['user_email'] = $email;
        $_SESSION['user_pic'] = $user['profile_pic'] ?? '';

        echo json_encode(['status' => 'success']);
    } else {
        sendError('کد تایید غلط است');
    }
    exit();
}

// --- 3. ورود ---
if (isset($_POST['action']) && $_POST['action'] == 'login') {
    $login_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    // Rate limiting
    if (!RateLimiter::check("login_" . $login_ip, 5, 3600)) {
        SecurityLogger::log('login_limit_exceeded', ['ip' => $login_ip]);
        sendError('تعداد تلاش‌های ورود بیش‌ازحد است. بعداً دوباره تلاش کنید.');
    }

    $email = cleanInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        sendError('ایمیل و رمز عبور الزامی هستند');
    }

    $stmt = $conn->prepare("SELECT id, name, phone, password, is_verified, profile_pic FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $user = $res->fetch_assoc();

        if (!password_verify($password, $user['password'])) {
            SecurityLogger::logLoginAttempt($email, false);
            sendError('رمز عبور اشتباه است');
        }

        if ($user['is_verified'] == 0) {
            sendError('حساب تایید نشده است');
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_phone'] = $user['phone'];
        $_SESSION['user_email'] = $email;
        $_SESSION['user_pic'] = $user['profile_pic'] ?? 'https://cdn.jsdelivr.net/gh/microsoft/fluentui-emoji@latest/assets/Person/3D/person_3d.png';

        SecurityLogger::logLoginAttempt($email, true);
        RateLimiter::reset("login_" . $login_ip);

        echo json_encode(['status' => 'success']);
    } else {
        SecurityLogger::logLoginAttempt($email, false);
        sendError('کاربری با این ایمیل یافت نشد');
    }
    exit();
}

// --- 4. آپلود عکس پروفایل ---
if (isset($_POST['action']) && $_POST['action'] == 'upload_profile') {
    if (!isset($_SESSION['user_id'])) {
        sendError('ابتدا وارد شوید');
    }

    if (!isset($_FILES['profile_img']) || $_FILES['profile_img']['error'] != 0) {
        sendError('فایلی انتخاب نشده است');
    }

    $file = $_FILES['profile_img'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        SecurityLogger::logFileUpload($file['name'], $file['size'], false);
        sendError('نوع فایل غیرمعتبر است');
    }

    if ($file['size'] > MAX_UPLOAD_SIZE) {
        SecurityLogger::logFileUpload($file['name'], $file['size'], false);
        sendError('حجم فایل بیشتر از ۶۴ مگابایت است');
    }

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    $filename = "Profile_" . $_SESSION['user_id'] . "_" . time() . "." . $ext;
    $target_file = UPLOAD_DIR . $filename;

    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        $uid = $_SESSION['user_id'];
        $relative_path = 'uploads/' . $filename;

        $stmt = $conn->prepare("UPDATE users SET profile_pic=? WHERE id=?");
        $stmt->bind_param("si", $relative_path, $uid);
        $stmt->execute();

        $_SESSION['user_pic'] = $relative_path;
        SecurityLogger::logFileUpload($filename, $file['size'], true);
        echo json_encode(['status' => 'success', 'url' => $relative_path]);
    } else {
        SecurityLogger::logFileUpload($filename, $file['size'], false);
        sendError('خطا در بارگذاری فایل');
    }
    exit();
}

// --- 5. ارسال پیام ---
if (isset($_POST['action']) && $_POST['action'] == 'send_msg') {
    if (!isset($_SESSION['user_id'])) {
        sendError('ابتدا وارد شوید');
    }

    $msg = cleanInput($_POST['message'] ?? '');
    if (empty($msg) || strlen($msg) > 500) {
        sendError('پیام نامعتبر است');
    }

    $uid = $_SESSION['user_id'];
    $sender = 'user';

    $stmt = $conn->prepare("INSERT INTO messages (user_id, sender, message, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iss", $uid, $sender, $msg);
    $stmt->execute();

    echo json_encode(['status' => 'success']);
    exit();
}
// --- 4.5 ویرایش نام و رمز عبور ---
if (isset($_POST['action']) && $_POST['action'] == 'update_profile') {
    if (!isset($_SESSION['user_id'])) {
        sendError('ابتدا وارد شوید');
    }

    $uid = $_SESSION['user_id'];
    $name = cleanInput($_POST['name'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $current_password = $_POST['current_password'] ?? '';

    if (empty($name) || !Validator::minLength($name, 2)) {
        sendError('نام معتبر نیست');
    }

    // اگر رمز جدید وارد شده، رمز فعلی باید تایید شود
    if (!empty($new_password)) {
        if (empty($current_password)) {
            sendError('برای تغییر رمز عبور، رمز فعلی را وارد کنید');
        }
        if (!Validator::minLength($new_password, 6)) {
            sendError('رمز عبور جدید حداقل ۶ حرف باید باشد');
        }

        $stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $res = $stmt->get_result();
        $u = $res->fetch_assoc();

        if (!$u || !password_verify($current_password, $u['password'])) {
            sendError('رمز عبور فعلی اشتباه است');
        }

        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET name=?, password=? WHERE id=?");
        $stmt->bind_param("ssi", $name, $new_hash, $uid);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name=? WHERE id=?");
        $stmt->bind_param("si", $name, $uid);
    }

    if ($stmt->execute()) {
        $_SESSION['user_name'] = $name;
        echo json_encode(['status' => 'success', 'message' => 'اطلاعات با موفقیت به‌روزرسانی شد']);
    } else {
        sendError('خطا در بروزرسانی اطلاعات');
    }
    exit();
}

// --- 8. ثبت پیام مشتری (فرم «برای ما پیام بگذارید» در صفحه اصلی) ---
if (isset($_POST['action']) && $_POST['action'] == 'submit_testimonial') {
    $name = cleanInput($_POST['name'] ?? '');
    $phone = cleanInput($_POST['phone'] ?? '');
    $message = cleanInput($_POST['message'] ?? '');
    $rating = max(1, min(5, intval($_POST['rating'] ?? 5)));

    if (empty($name) || empty($message)) {
        sendError('نام و متن پیام الزامی هستند');
    }
    if (strlen($message) > 1000) {
        sendError('متن پیام بیش از حد طولانی است');
    }

    $stmt = $conn->prepare("INSERT INTO testimonials (name, phone, message, rating, status) VALUES (?, ?, ?, ?, 'pending')");
    $stmt->bind_param("sssi", $name, $phone, $message, $rating);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'پیام شما ثبت شد و پس از بررسی نمایش داده می‌شود. ممنون از شما 🙏']);
    } else {
        sendError('خطا در ثبت پیام');
    }
    exit();
}

// --- 6. خروج ---
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// --- 7. اطلاع‌رسانی تحویل (ادمین) ---
if (isset($_POST['action']) && $_POST['action'] == 'notify_delivery') {
    $user_id = intval($_POST['user_id'] ?? 0);
    $order_id = intval($_POST['order_id'] ?? 0);

    if ($user_id <= 0 || $order_id <= 0) {
        sendError('داده‌های نامعتبر');
    }

    $message = "سفارش شما (کد $order_id) ارسال شد 🛵. لطفاً آماده تحویل باشید.";

    $stmt = $conn->prepare("INSERT INTO messages (user_id, sender, message, is_read, created_at) VALUES (?, 'admin', ?, 0, NOW())");
    $stmt->bind_param("is", $user_id, $message);
    $stmt->execute();

    $stmt = $conn->prepare("UPDATE orders SET status='sent' WHERE id=?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    echo json_encode(['status' => 'success']);
    exit();
}
?>