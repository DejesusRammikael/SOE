<?php
// filepath: c:\xampp\htdocs\ezorder\SOE_CLIENT_SIDE-main\SOE_CLIENT\add_to_cart.php
session_start();
require_once 'db_connection.php';

$session_id = session_id();
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;

if ($product_id <= 0 || $quantity < 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Check if item already in cart
$stmt = $conn->prepare("SELECT quantity FROM cart_items WHERE session_id = ? AND product_id = ?");
$stmt->bind_param("si", $session_id, $product_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Update or remove
    if ($quantity == 0) {
        $del = $conn->prepare("DELETE FROM cart_items WHERE session_id = ? AND product_id = ?");
        $del->bind_param("si", $session_id, $product_id);
        $del->execute();
        $del->close();
    } else {
        $upd = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE session_id = ? AND product_id = ?");
        $upd->bind_param("isi", $quantity, $session_id, $product_id);
        $upd->execute();
        $upd->close();
    }
} else {
    if ($quantity > 0) {
        $ins = $conn->prepare("INSERT INTO cart_items (session_id, product_id, quantity) VALUES (?, ?, ?)");
        $ins->bind_param("sii", $session_id, $product_id, $quantity);
        $ins->execute();
        $ins->close();
    }
}
$stmt->close();

echo json_encode(['success' => true]);
?>
