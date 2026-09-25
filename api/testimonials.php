<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');
require_once '../db.php';

$action = $_POST['action'] ?? $_GET['action'] ?? null;

switch ($action) {
    case 'add':
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'لطفا وارد شوید']);
            exit;
        }

        $user_id = intval($_SESSION['user_id']);
        $rating = intval($_POST['rating'] ?? 0);
        $message = trim($_POST['message'] ?? '');

        if ($rating < 1 || $rating > 5 || $message === '') {
            echo json_encode(['status' => 'error', 'message' => 'داده‌های نامعتبر']);
            exit;
        }
        if (mb_strlen($message) > 500) {
            echo json_encode(['status' => 'error', 'message' => 'متن نظر طولانی است']);
            exit;
        }

        $ustmt = $conn->prepare("SELECT name, phone FROM users WHERE id = ?");
        $ustmt->bind_param("i", $user_id);
        $ustmt->execute();
        $user = $ustmt->get_result()->fetch_assoc();
        $ustmt->close();

        if (!$user) {
            echo json_encode(['status' => 'error', 'message' => 'کاربر پیدا نشد']);
            exit;
        }

        $check = $conn->prepare("SELECT id FROM testimonials WHERE user_id = ? AND status = 'pending'");
        $check->bind_param("i", $user_id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'شما یک نظر در انتظار بررسی دارید']);
            exit;
        }
        $check->close();

        $stmt = $conn->prepare("INSERT INTO testimonials (user_id, name, phone, message, rating, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("isssi", $user_id, $user['name'], $user['phone'], $message, $rating);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'نظر شما ثبت شد و پس از تایید نمایش داده می‌شود']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'خطا در ثبت نظر']);
        }
        $stmt->close();
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'عملیات نامعتبر']);
}