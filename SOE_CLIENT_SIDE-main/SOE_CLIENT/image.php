<?php
require 'db_connection.php';

if (isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT image, image_type FROM menu_items WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($imageData, $imageType);

    if ($stmt->fetch()) {
        header("Content-Type: $imageType");
        echo $imageData;
    } else {
        http_response_code(404);
        echo "Image not found";
    }
}
?>
