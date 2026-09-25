<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../db.php';

$product_id = intval($_POST['product_id'] ?? 0);
$user_id = $_SESSION['user_id'] ?? null;

if ($product_id <= 0) {
    echo json_encode(['status' => 'error']);
    exit;
}

if ($user_id) {
    // Delete old view records (keep only 50)
    $conn->query("
        DELETE FROM recently_viewed
        WHERE user_id = $user_id
        AND id NOT IN (
            SELECT id FROM (
                SELECT id FROM recently_viewed
                WHERE user_id = $user_id
                ORDER BY viewed_at DESC
                LIMIT 50
            ) as t
        )
    ");

    // Insert/update view
    $stmt = $conn->prepare("
        INSERT INTO recently_viewed (user_id, product_id, viewed_at)
        VALUES (?, ?, NOW())
        ON DUPLICATE KEY UPDATE viewed_at = NOW()
    ");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
}

echo json_encode(['status' => 'success']);
?>
