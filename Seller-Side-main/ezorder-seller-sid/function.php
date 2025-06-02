<?php 
include 'database.php';

function db_connect() {
    global $conn;
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    return $conn;
}

function seller_login($email, $password, $remember) {
    $conn = new mysqli("localhost", "root", "", "ezorderdb");
    $stmt = $conn->prepare("SELECT id, password, status FROM seller WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($id, $hashed_password, $status);
    if ($stmt->fetch()) {
        if (password_verify($password, $hashed_password)) {
            $_SESSION['seller_id'] = $id;
            // Get stall_id
            $stmt->close();
            $stmt2 = $conn->prepare("SELECT stall_id FROM stall WHERE id = ?");
            $stmt2->bind_param("i", $id);
            $stmt2->execute();
            $stmt2->bind_result($stall_id);
            if ($stmt2->fetch()) {
                $_SESSION['stall_id'] = $stall_id;
                // Set cookies if remember is checked
                if ($remember) {
                    setcookie('seller_id', $id, time() + (86400 * 30), "/"); // 30 days
                    setcookie('stall_id', $stall_id, time() + (86400 * 30), "/");
                }
            }
            $stmt2->close();
            $conn->close();
            if ($status === 'approved') {
                return 'approved';
            } else if ($status === 'pending') {
                return 'pending';
            }
        }
    }
    return false;
}

// Register seller and stall
function register_seller_and_stall($first_name, $middle_name, $last_name, $email, $password, $resume_path, $stall_name, $stall_filepath, $phone_number) {
    $conn = db_connect();
    $conn->autocommit(false);
    $success = false;

    try {
        // Insert seller
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO seller (first_name, middle_name, last_name, email, password, resume_path) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed for seller: " . $conn->error);
        }
        $stmt->bind_param("ssssss", $first_name, $middle_name, $last_name, $email, $hashed_password, $resume_path);
        if (!$stmt->execute()) {
            throw new Exception("Seller registration failed: " . $stmt->error);
        }
        $seller_id = $conn->insert_id;
        $stmt->close();

        // Insert stall
        $stmt2 = $conn->prepare("INSERT INTO stall (stall_name, stall_filepath, phone_number, id) VALUES (?, ?, ?, ?)");
        if (!$stmt2) {
            throw new Exception("Prepare failed for stall: " . $conn->error);
        }
        $stmt2->bind_param("sssi", $stall_name, $stall_filepath, $phone_number, $seller_id);
        if (!$stmt2->execute()) {
            throw new Exception("Stall registration failed: " . $stmt2->error);
        }
        $stmt2->close();

        $conn->commit();
        $success = true;
    } catch (Exception $e) {
        $conn->rollback();
        error_log($e->getMessage());
        echo "<div class='alert error'>".$e->getMessage()."</div>"; // Show error for debugging
        $success = false;
    }
    $conn->autocommit(true);
    $conn->close();
    return $success;
}
?>