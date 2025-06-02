<?php
session_start();

header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "password", "soe_clientside");
if ($conn->connect_error) {
    echo json_encode([]);
    exit;
}

$session_id = session_id();

$sql = "SELECT ci.menu_item_id AS id, mi.name, mi.price, ci.quantity 
        FROM cart_items ci 
        JOIN menu_items mi ON ci.menu_item_id = mi.id
        WHERE ci.session_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $session_id);
$stmt->execute();
$result = $stmt->get_result();

$cart = [];
while ($row = $result->fetch_assoc()) {
    $cart[] = $row;
}

echo json_encode($cart);
?>
