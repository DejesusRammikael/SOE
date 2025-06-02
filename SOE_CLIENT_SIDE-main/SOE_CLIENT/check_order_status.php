<?php
require_once 'db_connection.php';



$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id <= 0) {
    echo json_encode(['error' => 'Invalid order ID']);
    exit;
}

// Fetch the current order status
$query = "SELECT status FROM orders WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if ($order) {
    echo json_encode(['status' => $order['status']]);
} else {
    echo json_encode(['error' => 'Order not found']);
}

$conn->close();
?> 