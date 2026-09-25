<?php
include 'db.php';

header('Content-Type: application/json; charset=utf-8');

// ===== احراز هویت: باید لاگین باشد و is_admin = 1 =====
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'دسترسی غیرمجاز']);
    exit();
}

$stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$adminCheck = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$adminCheck || intval($adminCheck['is_admin']) !== 1) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'دسترسی غیرمجاز']);
    exit();
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

// ===== دریافت لیست مشتریانی که پیام دارند =====
if ($action === 'get_users_list') {
    $sql = "
        SELECT
            u.id,
            u.name,
            u.profile_pic,
            (SELECT m2.message FROM messages m2 WHERE m2.user_id = u.id ORDER BY m2.created_at DESC, m2.id DESC LIMIT 1) AS last_message,
            (SELECT m3.created_at FROM messages m3 WHERE m3.user_id = u.id ORDER BY m3.created_at DESC, m3.id DESC LIMIT 1) AS last_message_time,
            (SELECT COUNT(*) FROM messages m4 WHERE m4.user_id = u.id AND m4.sender = 'user' AND m4.is_read = 0) AS unread
        FROM users u
        WHERE EXISTS (SELECT 1 FROM messages m5 WHERE m5.user_id = u.id)
        ORDER BY last_message_time DESC
    ";

    $result = $conn->query($sql);
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    echo json_encode($users);
    exit();
}

// ===== دریافت مکالمه‌ی یک کاربر خاص =====
if ($action === 'get_conversation') {
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

    if ($user_id <= 0) {
        echo json_encode([]);
        exit();
    }

    $stmt = $conn->prepare("SELECT id, sender, message, created_at FROM messages WHERE user_id = ? ORDER BY created_at ASC, id ASC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    $stmt->close();

    // پیام‌های خوانده‌نشده‌ی کاربر را چون ادمین الان مکالمه را باز کرده، خوانده‌شده علامت بزن
    $markRead = $conn->prepare("UPDATE messages SET is_read = 1 WHERE user_id = ? AND sender = 'user' AND is_read = 0");
    $markRead->bind_param("i", $user_id);
    $markRead->execute();
    $markRead->close();

    echo json_encode($messages);
    exit();
}

// ===== ارسال پاسخ ادمین =====
if ($action === 'admin_reply') {
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $message = isset($_POST['message']) ? cleanInput($_POST['message']) : '';

    if ($user_id <= 0 || $message === '') {
        echo json_encode(['status' => 'error', 'message' => 'اطلاعات ناقص است']);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO messages (user_id, sender, message, is_read, created_at) VALUES (?, 'admin', ?, 1, NOW())");
    $stmt->bind_param("is", $user_id, $message);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'خطا در ذخیره پیام']);
    }
    $stmt->close();
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'درخواست نامعتبر']);