<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'database.php';

// Check for cookies if session is not set
if (!isset($_SESSION['seller_id']) && isset($_COOKIE['seller_id'])) {
    $_SESSION['seller_id'] = $_COOKIE['seller_id'];
}
if (!isset($_SESSION['stall_id']) && isset($_COOKIE['stall_id'])) {
    $_SESSION['stall_id'] = $_COOKIE['stall_id'];
}

// Set default stall_id to 4 if not set
if (!isset($_SESSION['stall_id'])) {
    $_SESSION['stall_id'] = 4;
}

// Get stall ID from session
$stall_id = $_SESSION['stall_id'];

// Get filter parameters
$rating_filter = isset($_GET['rating']) ? intval($_GET['rating']) : null;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build the base query
$query = "SELECT r.* 
          FROM ratings r 
          WHERE 1=1";

$params = [];
$types = '';

// Add rating filter if set
if ($rating_filter) {
    $query .= " AND r.rating = ?";
    $params[] = $rating_filter;
    $types .= 'i';
}

// Add search filter if set
if ($search_query) {
    $query .= " AND (r.comment LIKE ? OR r.username LIKE ?)";
    $search_param = "%$search_query%";
    $params = array_merge($params, [$search_param, $search_param]);
    $types .= 'ss';
}

$query .= " ORDER BY r.created_at DESC";

try {
    // Execute the query with parameters if any
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Get rating statistics
    $stats_query = "SELECT 
        COUNT(*) as total_ratings,
        AVG(rating) as avg_rating,
        SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
        SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
        SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
        SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
        SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
        FROM ratings";
    
    $stats_result = $conn->query($stats_query);
    $rating_stats = $stats_result->fetch_assoc();
    
    // Calculate percentages for rating distribution
    $total = $rating_stats['total_ratings'];
    $rating_stats['five_star_pct'] = $total > 0 ? ($rating_stats['five_star'] / $total) * 100 : 0;
    $rating_stats['four_star_pct'] = $total > 0 ? ($rating_stats['four_star'] / $total) * 100 : 0;
    $rating_stats['three_star_pct'] = $total > 0 ? ($rating_stats['three_star'] / $total) * 100 : 0;
    $rating_stats['two_star_pct'] = $total > 0 ? ($rating_stats['two_star'] / $total) * 100 : 0;
    $rating_stats['one_star_pct'] = $total > 0 ? ($rating_stats['one_star'] / $total) * 100 : 0;
    
} catch (Exception $e) {
    $error = "An error occurred while fetching the feedback: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Feedback - EZ-ORDER</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles/feedback.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="Picture/logo2.png" alt="EZ-ORDER" class="sidebar-logo">
                <h2>EZ-ORDER</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="manage_orders.php" class="nav-item">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Manage Orders</span>
                </a>
                <a href="manage_products.php" class="nav-item">
                    <i class="fas fa-box"></i>
                    <span>Manage Products</span>
                </a>
                <a href="feedback.php" class="nav-item active">
                    <i class="fas fa-comments"></i>
                    <span>Feedback</span>
                </a>
                <a href="logout.php" class="nav-item logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="page-header">
                <div>
                    <h1>Customer Feedback</h1>
                    <p class="page-description">Manage and respond to customer reviews and ratings</p>
                </div>
                <div class="header-actions">
                    <div class="search-box">
                        <form method="GET" action="feedback.php" class="search-form">
                            <?php if ($rating_filter): ?>
                                <input type="hidden" name="rating" value="<?php echo $rating_filter; ?>">
                            <?php endif; ?>
                            <input type="text" name="search" placeholder="Search feedback..." value="<?php echo htmlspecialchars($search_query); ?>">
                            <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
                            <?php if ($search_query): ?>
                                <a href="?<?php echo $rating_filter ? 'rating=' . $rating_filter : ''; ?>" class="clear-search" title="Clear search">
                                    <i class="fas fa-times"></i>
                                </a>
                            <?php endif; ?>
                            <?php if ($rating_filter || $search_query): ?>
                                <a href="?" class="clear-filters" title="Clear all filters">
                                    <i class="fas fa-filter"></i> Clear Filters
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="dashboard-grid">
                <!-- Rating Summary Card -->
                <div class="rating-summary-card">
                    <div class="card-header">
                        <h3>Rating Overview</h3>
                        <div class="filter-dropdown">
                            <form method="GET" action="feedback.php" class="filter-form">
                                <?php if ($search_query): ?>
                                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_query); ?>">
                                <?php endif; ?>
                                <select name="rating" onchange="this.form.submit()">
                                    <option value="">All Ratings</option>
                                    <option value="5" <?php echo $rating_filter === 5 ? 'selected' : ''; ?>>5 Stars</option>
                                    <option value="4" <?php echo $rating_filter === 4 ? 'selected' : ''; ?>>4 Stars</option>
                                    <option value="3" <?php echo $rating_filter === 3 ? 'selected' : ''; ?>>3 Stars</option>
                                    <option value="2" <?php echo $rating_filter === 2 ? 'selected' : ''; ?>>2 Stars</option>
                                    <option value="1" <?php echo $rating_filter === 1 ? 'selected' : ''; ?>>1 Star</option>
                                </select>
                            </form>
                        </div>
                    </div>
                    <div class="rating-overview">
                        <div class="average-rating">
                            <div class="rating-number">
                                <?php echo number_format($rating_stats['avg_rating'] ?? 0, 1); ?>
                                <span class="rating-out-of">/5</span>
                            </div>
                            <div class="stars">
                                <?php
                                $avg_rating = $rating_stats['avg_rating'] ?? 0;
                                $full_stars = floor($avg_rating);
                                $has_half = ($avg_rating - $full_stars) >= 0.5;
                                $empty_stars = 5 - $full_stars - ($has_half ? 1 : 0);
                                
                                echo str_repeat('<i class="fas fa-star"></i>', $full_stars);
                                if ($has_half) {
                                    echo '<i class="fas fa-star-half-alt"></i>';
                                }
                                echo str_repeat('<i class="far fa-star"></i>', $empty_stars);
                                ?>
                            </div>
                            <div class="total-ratings">
                                Based on <?php echo $rating_stats['total_ratings'] ?? 0; ?> reviews
                            </div>
                        </div>
                        <div class="rating-distribution">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <div class="rating-bar">
                                    <div class="rating-label">
                                        <span><?php echo $i; ?> <i class="fas fa-star"></i></span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress" style="width: <?php echo ${'rating_stats'}["${i}_star_pct"] ?? 0; ?>%;"></div>
                                    </div>
                                    <div class="rating-count">
                                        <?php echo ${'rating_stats'}["${i}_star"] ?? 0; ?>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <!-- Feedback List -->
                <div class="feedback-list">
                    <div class="feedback-header">
                        <h3>Customer Reviews</h3>
                        <div class="sort-options">
                            <span>Sort by:</span>
                            <select>
                                <option>Newest First</option>
                                <option>Oldest First</option>
                                <option>Highest Rated</option>
                                <option>Lowest Rated</option>
                            </select>
                        </div>
                    </div>

                    <?php if ($result && $result->num_rows > 0): ?>
                        <div class="feedback-items">
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <div class="feedback-card">
                                    <div class="feedback-card-header">
                                        <div class="user-info">
                                            <div class="user-avatar">
                                                <?php echo strtoupper(substr($row['username'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="username"><?php echo htmlspecialchars($row['username']); ?></div>
                                                <?php if (!empty($row['order_number'])): ?>
                                                    <div class="order-number">Order #<?php echo htmlspecialchars($row['order_number']); ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="rating">
                                            <?php echo str_repeat('<i class="fas fa-star"></i>', $row['rating']); ?>
                                            <?php echo str_repeat('<i class="far fa-star"></i>', 5 - $row['rating']); ?>
                                            <span class="rating-date"><?php echo date('M j, Y', strtotime($row['created_at'])); ?></span>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($row['comment'])): ?>
                                        <div class="feedback-comment">
                                            <?php echo nl2br(htmlspecialchars($row['comment'])); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($row['seller_reply'])): ?>
                                        <div class="seller-reply">
                                            <div class="reply-header">
                                                <i class="fas fa-store"></i>
                                                <span>Seller's Response</span>
                                            </div>
                                            <p><?php echo nl2br(htmlspecialchars($row['seller_reply'])); ?></p>
                                            <div class="reply-date">
                                                <?php echo !empty($row['reply_date']) ? date('M j, Y', strtotime($row['reply_date'])) : ''; ?>
                                            </div>
                                        </div>

                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-feedback">
                            <i class="far fa-comment-dots"></i>
                            <h4>No feedback found</h4>
                            <p>There are no reviews matching your current filters.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Search box styles */
        .search-box {
            position: relative;
            max-width: 500px;
            margin-left: auto;
        }
        
        .search-form {
            display: flex;
            align-items: center;
            position: relative;
        }
        
        .search-form input[type="text"] {
            width: 100%;
            padding: 8px 40px 8px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            height: 40px;
        }
        
        .search-btn {
            position: absolute;
            right: 10px;
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            padding: 8px;
            font-size: 16px;
        }
        
        .clear-search {
            position: absolute;
            right: 40px;
            color: #999;
            text-decoration: none;
            padding: 8px;
            font-size: 14px;
        }
        
        .clear-filters {
            margin-left: 10px;
            padding: 8px 12px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 4px;
            color: #666;
            text-decoration: none;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            transition: all 0.2s;
        }
        
        .clear-filters:hover {
            background-color: #eee;
            color: #333;
        }
        
        .clear-filters i {
            margin-right: 5px;
        }
        
        /* Filter dropdown styles */
        .filter-dropdown select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: white;
            font-size: 14px;
            cursor: pointer;
            min-width: 150px;
        }
        
        .filter-dropdown select:focus {
            outline: none;
            border-color: #4a90e2;
            box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.2);
        }
        
        /* Active filter indicator */
        .filter-active {
            background-color: #e6f2ff;
            border-color: #4a90e2;
        }
    </style>
    
    <script>
        // Any additional JavaScript can go here
        document.addEventListener('DOMContentLoaded', function() {
            // Add active class to current rating filter
            const ratingFilter = <?php echo $rating_filter ?: 'null'; ?>;
            if (ratingFilter) {
                const ratingOption = document.querySelector(`select[name="rating"] option[value="${ratingFilter}"]`);
                if (ratingOption) {
                    ratingOption.selected = true;
                }
            }
        });
    </script>
</body>
</html> 