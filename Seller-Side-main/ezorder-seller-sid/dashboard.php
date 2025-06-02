<?php
session_start();
require_once 'database.php';

// Set default stall_id to 4 if not set
if (!isset($_SESSION['stall_id'])) {
    $_SESSION['stall_id'] = 4;
}

// Check if seller is logged in
if (!isset($_SESSION['seller_id']) || !isset($_SESSION['stall_id'])) {
    header('Location: login.php');
    exit();
}

$seller_name = isset($_SESSION['seller_name']) ? $_SESSION['seller_name'] : 'Seller';
$stall_id = $_SESSION['stall_id'];

// Fetch dashboard statistics
$stats = [
    'total_orders' => 0,
    'today_revenue' => 0,
    'total_products' => 0,
    'avg_rating' => 0,
    'recent_orders' => [],
    'popular_items' => []
];

// Get total orders for this stall
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM `order` WHERE stall_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $stall_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['total_orders'] = $result->fetch_assoc()['total'] ?? 0;
        $stmt->close();
    } else {
        $stats['total_orders'] = 0;
        error_log("Error preparing total orders query: " . $conn->error);
    }
} catch (Exception $e) {
    $stats['total_orders'] = 0;
    error_log("Error fetching total orders: " . $e->getMessage());
}

// Get today's revenue for this stall
try {
    $today = date('Y-m-d');
    $stmt = $conn->prepare("SELECT SUM(price) as total FROM `order` WHERE stall_id = ? AND DATE(order_date) = ?");
    if ($stmt) {
        $stmt->bind_param("is", $stall_id, $today);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['today_revenue'] = $result->fetch_assoc()['total'] ?? 0;
        $stmt->close();
    } else {
        $stats['today_revenue'] = 0;
        error_log("Error preparing today's revenue query: " . $conn->error);
    }
} catch (Exception $e) {
    $stats['today_revenue'] = 0;
    error_log("Error fetching today's revenue: " . $e->getMessage());
}

// Get total products for this stall
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM products WHERE stall_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $stall_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['total_products'] = $result->fetch_assoc()['total'] ?? 0;
        $stmt->close();
    } else {
        $stats['total_products'] = 0;
        error_log("Error preparing total products query: " . $conn->error);
    }
} catch (Exception $e) {
    $stats['total_products'] = 0;
    error_log("Error fetching total products: " . $e->getMessage());
}

// Initialize rating variables
$avg_rating = 0;
$total_ratings = 0;
$overall_avg_rating = 0;
$total_overall_ratings = 0;

try {
    // Get average rating for this stall from ratings table
    $stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_ratings 
                           FROM ratings 
                           WHERE stall_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $stall_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $avg_rating = floatval($row['avg_rating']);
            $total_ratings = intval($row['total_ratings']);
        }
        $stmt->close();
    }
    
    // Get overall average rating across all stalls
    $result = $conn->query("SELECT AVG(rating) as overall_avg, COUNT(*) as total 
                           FROM ratings");
    if ($result && $row = $result->fetch_assoc()) {
        $overall_avg_rating = floatval($row['overall_avg']);
        $total_overall_ratings = intval($row['total']);
    }
} catch (Exception $e) {
    error_log("Error fetching rating data: " . $e->getMessage());
}

// Update stats array with rating information
$stats['avg_rating'] = $total_ratings > 0 ? number_format($avg_rating, 1) : 'N/A';
$stats['total_ratings'] = $total_ratings;
$stats['overall_avg_rating'] = $total_overall_ratings > 0 ? 
                              number_format($overall_avg_rating, 1) : 'N/A';
$stats['total_overall_ratings'] = $total_overall_ratings;

// Get recent orders (last 5)
try {
    $stmt = $conn->prepare("SELECT id, order_name, price, status, order_date 
                          FROM `order` 
                          ORDER BY order_date DESC 
                          LIMIT 5");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['recent_orders'] = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        $stats['recent_orders'] = [];
        error_log("Error preparing recent orders query: " . $conn->error);
    }
} catch (Exception $e) {
    $stats['recent_orders'] = [];
    error_log("Error fetching recent orders: " . $e->getMessage());
}

// Get popular items (top 5 by order count)
try {
    $stmt = $conn->prepare("
        SELECT p.product_name, p.price, COUNT(o.id) as order_count 
        FROM products p 
        LEFT JOIN `order` o ON p.product_id = o.product_id 
        WHERE p.stall_id = ?
        GROUP BY p.product_id 
        ORDER BY order_count DESC 
        LIMIT 5
    ");
    
    if ($stmt) {
        $stmt->bind_param("i", $stall_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['popular_items'] = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        $stats['popular_items'] = [];
        error_log("Error preparing popular items query: " . $conn->error);
    }
} catch (Exception $e) {
    // If there's an error with the join, just get top products
    $stmt = $conn->prepare("SELECT product_name, price FROM products ORDER BY product_id DESC LIMIT 5");
    $stmt->execute();
    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);
    foreach ($items as &$item) {
        $item['order_count'] = 'N/A';
    }
    $stats['popular_items'] = $items;
    $stmt->close();
}

// Close the database connection
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Ezorder Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles/dashboard.css">
    <style>
        /* Rating Display */
        .rating-display {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .rating-display .stars {
            color: #FFD700; /* Gold color for stars */
            font-size: 1.2em;
            letter-spacing: 2px;
        }
        
        .rating-display .rating-text {
            font-size: 1.1em;
            font-weight: 600;
            color: #333;
        }
        
        .rating-display .rating-text small {
            font-size: 0.8em;
            color: #666;
            font-weight: normal;
        }
        
        .stat-card .stat-number {
            font-size: 1.5em;
            font-weight: 700;
            margin-top: 5px;
        }
        
        /* Status badges */
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }
        
        .status-badge.pending {
            background-color: #fff3e0;
            color: #e65100;
        }
        
        .status-badge.preparing {
            background-color: #e3f2fd;
            color: #1565c0;
        }
        
        .status-badge.ready {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .status-badge.completed {
            background-color: #f1f8e9;
            color: #33691e;
        }
        
        .status-badge.cancelled {
            background-color: #ffebee;
            color: #c62828;
        }
        
        /* Table styles */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }
        
        th {
            font-weight: 600;
            color: #555;
            background-color: #f9f9f9;
        }
        
        tr:hover {
            background-color: #f9f9f9;
        }
        
        .no-data {
            text-align: center;
            color: #999;
            padding: 20px;
        }
        
        /* Card styles */
        .grid-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .card-header h2 {
            margin: 0;
            font-size: 1.1rem;
            color: #333;
        }
        
        .view-all {
            color: #4caf50;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .view-all:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
<div class="sidebar">
            <div class="sidebar-header">
                <img src="Picture/logo2.png" alt="EZ-ORDER" class="sidebar-logo">
                <h2>EZ-ORDER</h2>
</div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="manage_orders.php" class="nav-item">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Manage Transactions</span>
                </a>
                <a href="manage_products.php" class="nav-item">
                    <i class="fas fa-box"></i>
                    <span>Manage Products</span>
                </a>
                <a href="feedback.php" class="nav-item">
                    <i class="fas fa-comments"></i>
                    <span>Feedback</span>
                </a>
                <a href="logout.php" class="nav-item logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </div>

        <main class="main-content">
            <div class="top-bar">
                <h1>Dashboard Overview</h1>
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <span>Welcome, <?php echo htmlspecialchars($seller_name); ?></span>
                </div>
            </div>

            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Orders</h3>
                        <p class="stat-number"><?php echo number_format($stats['total_orders']); ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Today's Revenue</h3>
                        <p class="stat-number">₱<?php echo number_format($stats['today_revenue'], 2); ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Products</h3>
                        <p class="stat-number"><?php echo number_format($stats['total_products']); ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Your Rating</h3>
                        <div class="rating-display">
                            <div class="stars">
                                <?php 
                                $rating = $stats['avg_rating'] !== 'N/A' ? floatval($stats['avg_rating']) : 0;
                                $fullStars = floor($rating);
                                $hasHalfStar = ($rating - $fullStars) >= 0.5;
                                
                                // Full stars
                                for ($i = 0; $i < $fullStars && $i < 5; $i++) {
                                    echo '<i class="fas fa-star"></i>';
                                }
                                
                                // Half star
                                if ($hasHalfStar) {
                                    echo '<i class="fas fa-star-half-alt"></i>';
                                    $fullStars++; // Increment to account for half star
                                }
                                
                                // Empty stars
                                for ($i = $fullStars; $i < 5; $i++) {
                                    echo '<i class="far fa-star"></i>';
                                }
                                ?>
                            </div>
                            <div class="rating-text">
                                <?php 
                                echo $stats['avg_rating'] . '/5.0';
                                if ($stats['total_ratings'] > 0) {
                                    echo ' <small>(' . $stats['total_ratings'] . ' ratings)</small>';
                                }
                                ?>
                            </div>
                        </div>
                        
                        <?php if ($stats['overall_avg_rating'] !== 'N/A'): ?>
                        <div class="overall-rating" style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #ddd;">
                            <div style="font-size: 0.9em; color: #666; margin-bottom: 5px;">
                                <i class="fas fa-globe"></i> Overall Rating:
                            </div>
                            <div class="rating-display">
                                <div class="stars" style="font-size: 1em;">
                                    <?php 
                                    $overall_rating = floatval($stats['overall_avg_rating']);
                                    $fullStars = floor($overall_rating);
                                    $hasHalfStar = ($overall_rating - $fullStars) >= 0.5;
                                    
                                    // Full stars
                                    for ($i = 0; $i < $fullStars && $i < 5; $i++) {
                                        echo '<i class="fas fa-star"></i>';
                                    }
                                    
                                    // Half star
                                    if ($hasHalfStar) {
                                        echo '<i class="fas fa-star-half-alt"></i>';
                                        $fullStars++;
                                    }
                                    
                                    // Empty stars
                                    for ($i = $fullStars; $i < 5; $i++) {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                    ?>
                                </div>
                                <div class="rating-text" style="font-size: 0.95em;">
                                    <?php 
                                    echo $stats['overall_avg_rating'] . '/5.0';
                                    if ($stats['total_overall_ratings'] > 0) {
                                        echo ' <small>(' . $stats['total_overall_ratings'] . ' total ratings)</small>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="grid-card recent-orders">
                    <div class="card-header">
                        <h2>Recent Orders</h2>
                        <a href="manage_orders.php" class="view-all">View All</a>
                    </div>
                    <div class="card-content">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($stats['recent_orders']) > 0): ?>
                                    <?php foreach ($stats['recent_orders'] as $order): ?>
                                        <tr>
                                            <td>#<?php echo htmlspecialchars($order['id']); ?></td>
                                            <td><?php echo htmlspecialchars($order['order_name']); ?></td>
                                            <td>₱<?php echo number_format($order['price'], 2); ?></td>
                                            <td><span class="status-badge <?php echo strtolower(htmlspecialchars($order['status'])); ?>">
                                                <?php echo ucfirst(htmlspecialchars($order['status'])); ?>
                                            </span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="no-data">No recent orders</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="grid-card popular-items">
                    <div class="card-header">
                        <h2>Popular Items</h2>
                        <a href="manage_products.php" class="view-all">View All</a>
        </div>
                    <div class="card-content">
                        <table>
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Orders</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($stats['popular_items']) > 0): ?>
                                    <?php foreach ($stats['popular_items'] as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                            <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                            <td><?php echo number_format($item['order_count'] ?? 0); ?> orders</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="no-data">No products available</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
        </div>
    </div>
            </div>
        </main>
</div>
</body>
</html>
