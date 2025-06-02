<?php
session_start();
require_once 'db_connection.php';

$session_id = session_id();

// Get the order ID from URL
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// Fetch order details including stall information
$query = "SELECT o.*, s.stall_name 
          FROM orders o
          JOIN order_items oi ON o.id = oi.order_id
          JOIN products p ON oi.product_id = p.product_id
          JOIN stall s ON p.stall_id = s.stall_id
          WHERE o.id = ?
          LIMIT 1";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die('Prepare failed: ' . $conn->error);
}
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order_details = $result->fetch_assoc();

// If no order found, redirect to dashboard
if (!$order_details) {
    header('Location: client_dashboard.php');
    exit;
}

// Format the date
$order_date = new DateTime($order_details['created_at']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Confirmation</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/view_cart.css">
    <style>
        .container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .header h1 {
            color: #333;
            font-size: 32px;
            margin: 0;
            border-left: 5px solid #1b4d4d;
            padding-left: 15px;
            margin-bottom: 30px;
        }

        .confirmation-message {
            text-align: center;
            padding: 40px 20px;
            background: #f8f9fa;
            border-radius: 12px;
            border-left: 5px solid #1b4d4d;
            margin-bottom: 30px;
            margin-left: 60px;
            height: 300px;
            width: 90%;
        }

        .confirmation-message h2 {
            color: #1b4d4d;
            font-size: 30px;
            margin-bottom: 15px;
        }

        .confirmation-message p {
            color: #666;
            font-size: 25px;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .order-details {
            background:rgb(204, 211, 218);
            padding: 20px;
            border-radius: 20px;
            text-align: center;
            margin-bottom: 30px;
            margin-left: 60px;
            height: 190px;
            width: 90%;
        }

        .order-number {
            font-size: 55px;
            color: #333;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .order-stall {
            color: #666;
            margin-bottom: 15px;
        }

        .order-datetime {
            color: #666;
            font-size: 14px;
        }

        .tracking-section {
            margin: 40px 0;
            padding: 20px;
            text-align: center;
        }

        .tracking-title {
            color: #333;
            font-size: 24px;
            margin-bottom: 40px;
            font-weight: 600;
        }

        .tracking-progress {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            max-width: 600px;
            margin: 0 auto;
            padding: 0 40px;
        }

        .tracking-line {
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #1b4d4d;
            transform: translateY(-50%);
            z-index: 1;
        }

        .tracking-step {
            width: 40px;
            height: 40px;
            background-color: #1b4d4d;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 2;
        }

        .tracking-step i {
            color: white;
            font-size: 18px;
        }

        .tracking-labels {
            display: flex;
            justify-content: space-between;
            max-width: 600px;
            margin: 15px auto 0;
            padding: 0 40px;
            list-style: none;
        }

        .tracking-labels li {
            flex: 1;
            text-align: center;
            color: #333;
            font-size: 14px;
            font-weight: 500;
            padding: 0 10px;
        }

        .back-to-home {
            display: inline-block;
            padding: 12px 25px;
            background-color: #1b4d4d;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            margin-top: 30px;
        }

        .back-to-home:hover {
            background-color: #2c7a7a;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(27, 77, 77, 0.2);
         }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="header-brand">
            <img src="Picture/logo1.png" alt="EZ-Order" class="header-logo">   
        </div>
    </header>

    <!-- Sidebar -->
        <div class="sidebar">
        <div class="logoo-wrapper">
            <img src="Picture/logo2.png" alt="EZ-Order Logo" class="sidebar-logo">
            <div class="logoo">EZ-ORDER</div>
            <div class="divider"></div> 
            <div class="tagline">"easy orders, zero hassle"</div>
        </div>
        <a href="client_dashboard.php">
            🏠 Home
        </a>
        <a href="view_cart.php">
            🛒 Cart
        </a>
        </div>

    <!-- Main Content -->
        <div class="content">
        <div class="main-content">
            <div class="header">
                <h1>Order Confirmation</h1>
            </div>

      
                <div class="order-details">
                    <div class="order-number">#<?php echo htmlspecialchars($order_id); ?></div>
                    <div class="order-stall"><?php echo htmlspecialchars($order_details['stall_name']); ?></div>
                    <div class="order-datetime">
                        <?php echo $order_date->format('F j, Y | g:i A'); ?>
                </div>
            </div>

            <div class="tracking-section">
                    <h2 class="tracking-title">Track Your Order</h2>
                <div class="tracking-progress">
                        <div class="tracking-line"></div>
                        <div class="tracking-step">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div class="tracking-step">
                            <i class="fas fa-utensils"></i>
                    </div>
                        <div class="tracking-step">
                            <i class="fas fa-check"></i>
                    </div>
                    </div>
                    <ul class="tracking-labels">
                        <li>Order Placed</li>
                        <li>Preparing</li>
                        <li>Ready for Pickup</li>
                    </ul>
                </div>

                <div class="confirmation-message">
                    <h2>Thank You for Your Order!</h2>
                    <p>Your order has been successfully placed and is being processed.</p>
                    <a href="client_dashboard.php" class="back-to-home">Return to Home</a>
                    </div>
            </div>
        </div>


    <script>
        function updateOrderStatus(status) {
            const steps = document.querySelectorAll('.tracking-step');
            
            steps.forEach((step, index) => {
                if (index <= status) {
                    step.style.backgroundColor = '#1b4d4d';
                } else {
                    step.style.backgroundColor = '#e0e0e0';
                }
            });
        }

        // Initialize with first step active
        updateOrderStatus(0);

        // Check for status updates every 10 seconds
        setInterval(() => {
            fetch(`check_order_status.php?order_id=<?php echo isset($_GET['order_id']) ? htmlspecialchars($_GET['order_id']) : ''; ?>`)
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        let stepNumber = 0;
                        switch(data.status) {
                            case 'preparing':
                                stepNumber = 1;
                                break;
                            case 'ready':
                                stepNumber = 2;
                                break;
                        }
                        updateOrderStatus(stepNumber);
                    }
                })
                .catch(error => console.error('Error:', error));
        }, 10000);
    </script>
</body>
</html>