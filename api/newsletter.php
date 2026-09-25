<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../db.php';

$email = trim($_POST['email'] ?? '');
$name = trim($_POST['name'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'ایمیل نامعتبر است']);
    exit;
}

$stmt = $conn->prepare("
    INSERT INTO newsletter (email, name)
    VALUES (?, ?)
    ON DUPLICATE KEY UPDATE name = ?
");
$stmt->bind_param("sss", $email, $name, $name);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'با تشکر! اشتراک شما ثبت شد']);
} else {
    if (strpos($conn->error, 'Duplicate') !== false) {
        echo json_encode(['status' => 'success', 'message' => 'قبلاً اشتراک داشتی']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'خطا']);
    }
}
?>
