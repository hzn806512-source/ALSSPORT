<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'لطفا وارد شوید']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? null;
$user_id = $_SESSION['user_id'];

switch ($action) {
    case 'add':
        $product_id = intval($_POST['product_id'] ?? 0);
        if ($product_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'محصول نامعتبر']);
            exit;
        }

        $stmt = $conn->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $product_id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'به علاقه‌مندی‌ها اضافه شد']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'خطا در اضافه کردن']);
        }
        break;

    case 'remove':
        $product_id = intval($_POST['product_id'] ?? 0);

        $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'از علاقه‌مندی‌ها حذف شد']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'خطا']);
        }
        break;

    case 'check':
        $product_id = intval($_GET['product_id'] ?? 0);

        $stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        echo json_encode([
            'status' => 'success',
            'in_wishlist' => $result->num_rows > 0
        ]);
        break;

    case 'list':
        $stmt = $conn->prepare("
            SELECT p.*, d.percentage
            FROM wishlist w
            JOIN products p ON w.product_id = p.id
            LEFT JOIN discounts d ON p.id = d.product_id AND d.active = 1
            WHERE w.user_id = ?
            ORDER BY w.created_at DESC
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }

        echo json_encode(['status' => 'success', 'items' => $items]);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'عملیات نامعتبر']);
}
?>
