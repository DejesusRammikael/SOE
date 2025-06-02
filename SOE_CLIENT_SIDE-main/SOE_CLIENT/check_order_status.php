<?php
require_once 'db_connection.php';

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$status = null;

if ($order_id > 0) {
    $stmt = $conn->prepare("SELECT status FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->bind_result($status);
    $stmt->fetch();
    $stmt->close();
}

header('Content-Type: application/json');
echo json_encode(['status' => $status]);

$conn->close();
?>