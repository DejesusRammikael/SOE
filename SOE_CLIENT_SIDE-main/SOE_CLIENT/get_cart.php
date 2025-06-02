<?php
session_start();
require_once 'db_connection.php';

$session_id = session_id();

$query = "SELECT ci.id, ci.product_id, ci.quantity as qty, p.product_name as name, p.price 
          FROM cart_items ci 
          JOIN products p ON ci.product_id = p.product_id 
          WHERE ci.session_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $session_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

echo json_encode([
    'success' => true,
    'items' => $items
]);
?>