<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../db.php';

$action = $_POST['action'] ?? $_GET['action'] ?? null;

switch ($action) {
    case 'add':
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'لطفا وارد شوید']);
            exit;
        }

        $product_id = intval($_POST['product_id'] ?? 0);
        $rating = intval($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');
        $user_id = $_SESSION['user_id'];

        if ($product_id <= 0 || $rating < 1 || $rating > 5) {
            echo json_encode(['status' => 'error', 'message' => 'داده‌های نامعتبر']);
            exit;
        }

        // Check if user already reviewed this product
        $check = $conn->prepare("SELECT id FROM reviews WHERE product_id = ? AND user_id = ?");
        $check->bind_param("ii", $product_id, $user_id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'قبلاً نظر دادی‌ای']);
            exit;
        }

        $stmt = $conn->prepare("
            INSERT INTO reviews (product_id, user_id, rating, comment, status)
            VALUES (?, ?, ?, ?, 'pending')
        ");
        $stmt->bind_param("iis", $product_id, $user_id, $rating, $comment);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'نظرت در انتظار تایید است']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'خطا']);
        }
        break;

    case 'get':
        $product_id = intval($_GET['product_id'] ?? 0);

        $stmt = $conn->prepare("
            SELECT r.*, u.name, u.profile_pic
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            WHERE r.product_id = ? AND r.status = 'approved'
            ORDER BY r.created_at DESC
        ");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $reviews = [];
        $total_rating = 0;
        $count = 0;

        while ($row = $result->fetch_assoc()) {
            $reviews[] = $row;
            $total_rating += $row['rating'];
            $count++;
        }

        echo json_encode([
            'status' => 'success',
            'reviews' => $reviews,
            'average_rating' => $count > 0 ? round($total_rating / $count, 1) : 0,
            'count' => $count
        ]);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'عملیات نامعتبر']);
}
?>
