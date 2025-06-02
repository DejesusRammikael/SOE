<?php
require_once 'db_connection.php';



$stall_id = isset($_GET['stall_id']) ? (int)$_GET['stall_id'] : 0;
if ($stall_id <= 0) {
    die("Invalid stall ID.");
}

$stmt = $conn->prepare("SELECT name FROM stalls WHERE id = ?");
$stmt->bind_param("i", $stall_id);
$stmt->execute();
$stmt->bind_result($stall_name);
if (!$stmt->fetch()) {
    die("Stall not found.");
}
$stmt->close();

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $menu_item_id = isset($_POST['menu_item_id']) ? (int)$_POST['menu_item_id'] : 0;
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $review = isset($_POST['review']) ? trim($_POST['review']) : "";

    $stmt = $conn->prepare("SELECT COUNT(*) FROM menu_items WHERE id = ? AND stall_id = ?");
    $stmt->bind_param("ii", $menu_item_id, $stall_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($menu_item_id <= 0 || $rating < 1 || $rating > 5 || $count == 0) {
        $message = "Please select a valid menu item from this stall and provide a rating 1-5.";
    } else {
        $stmt = $conn->prepare("INSERT INTO ratings (menu_item_id, rating, review, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $menu_item_id, $rating, $review);
        if ($stmt->execute()) {
            $message = "Thank you for your review!";
        } else {
            $message = "Error saving review: " . $conn->error;
        }
        $stmt->close();
    }
}

// Get menu items with their average ratings
$menu_items = [];
$stmt = $conn->prepare("
    SELECT 
        mi.id,
        mi.name,
        mi.image,
        COALESCE(AVG(r.rating), 0) AS avg_rating,
        COUNT(r.id) AS review_count
    FROM menu_items mi
    LEFT JOIN ratings r ON mi.id = r.menu_item_id
    WHERE mi.stall_id = ?
    GROUP BY mi.id
    ORDER BY mi.name
");

$stmt->bind_param("i", $stall_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $row['avg_rating'] = round($row['avg_rating'], 1); 
    $menu_items[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Reviews & Ratings - <?= htmlspecialchars($stall_name) ?></title>
    <link rel="stylesheet" href="css/view_cart.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .content {
            padding: 90px 30px 30px;
            margin-left: 280px;
            background: #f8fafc;
        }

        .reviews-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 70px;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #1e3c72;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
            background: rgba(30, 60, 114, 0.05);
        }

        .back-button:hover {
            transform: translateX(-5px);
            background: rgba(30, 60, 114, 0.1);
        }

        .stall-title {
            color: #1e3c72;
            font-size: 32px;
            margin: 0;
            font-weight: 700;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .menu-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }

        .menu-card:hover {
            transform: translateY(-5px);
        }

        .menu-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .menu-content {
            padding: 20px;
        }

        .menu-name {
            font-size: 18px;
            font-weight: 600;
            color: #1e3c72;
            margin-bottom: 10px;
        }

        .rating-summary {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .stars {
            color: #ffd700;
            font-size: 18px;
        }

        .review-count {
            color: #64748b;
            font-size: 14px;
        }

        .review-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .review-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(42, 82, 152, 0.2);
        }

        /* Review Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            position: relative;
        }

        .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            color: #64748b;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close-modal:hover {
            color: #1e3c72;
        }

        .rating-group {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 10px;
            margin: 20px 0;
        }

        .rating-group input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .star-label {
            font-size: 30px;
            color: #ddd;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .rating-group input[type="radio"]:checked ~ label {
            color: #ffd700;
        }

        .rating-group label:hover,
        .rating-group label:hover ~ label {
            color: #ffd700;
        }

        .rating-group input[type="radio"]:focus + label {
            outline: 1px dotted #ffd700;
        }

        textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e8f0fe;
            border-radius: 8px;
            margin: 15px 0;
            min-height: 100px;
            resize: vertical;
        }

        .submit-review-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-review-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(42, 82, 152, 0.2);
        }

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }

        .success {
            background-color: #ecfdf5;
            color: #065f46;
        }

        .error {
            background-color: #fef2f2;
            color: #991b1b;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 15px;
            color: #fff;
            text-decoration: none;
            padding: 12px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
            margin-bottom: 8px;
        }

        .sidebar a i {
            font-size: 16px;
            min-width: 20px;
            text-align: center;
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.1);
        }
    </style>
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
        <h2>🍽 Stalls</h2>
        <?php 
        $stall_query = "SELECT * FROM stalls";
        $stall_result = $conn->query($stall_query);
        while($stall_item = $stall_result->fetch_assoc()): ?>
            <a href="stall_dashboard.php?stall_id=<?= $stall_item['id'] ?>">
                <i class="fas fa-store"></i> <?= htmlspecialchars($stall_item['name']) ?>
            </a>
        <?php endwhile; ?>
    </div>

    <!-- Main Content -->
    <div class="content">
        <div class="reviews-container">
            <div class="page-header">
                <h1 class="stall-title">Reviews & Ratings - <?= htmlspecialchars($stall_name) ?></h1>
                <a href="stall_dashboard.php?stall_id=<?= $stall_id ?>" class="back-button">
                    <i class="fas fa-arrow-left"></i> Back to Stall
                </a>
            </div>

            <?php if ($message): ?>
                <div class="message <?= strpos($message, 'Thank you') === 0 ? 'success' : 'error' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div class="menu-grid">
                <?php foreach ($menu_items as $item): ?>
                    <div class="menu-card">
                        <img src="assets/images/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="menu-image">
                        <div class="menu-content">
                            <h3 class="menu-name"><?= htmlspecialchars($item['name']) ?></h3>
                            <div class="rating-summary">
                                <div class="stars">
                                    <?php
                                    $avg_rating = round($item['avg_rating']);
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo $i <= $avg_rating ? '★' : '☆';
                                    }
                                    ?>
                                </div>
                                <span class="review-count">(<?= $item['review_count'] ?> reviews)</span>
                            </div>
                            <button class="review-btn" onclick="openReviewModal(<?= $item['id'] ?>, '<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>')">
                                Write a Review
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Review Modal -->
    <div id="reviewModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeReviewModal()">&times;</span>
            <h2 id="modalItemName" style="text-align: center; color: #1e3c72; margin-bottom: 20px;"></h2>
            <form id="reviewForm" method="post">
                <input type="hidden" id="menu_item_id" name="menu_item_id">
                <div class="rating-group">
                    <input type="radio" name="rating" value="5" id="star5" required>
                    <label for="star5" class="star-label" title="5 stars">★</label>
                    
                    <input type="radio" name="rating" value="4" id="star4" required>
                    <label for="star4" class="star-label" title="4 stars">★</label>
                    
                    <input type="radio" name="rating" value="3" id="star3" required>
                    <label for="star3" class="star-label" title="3 stars">★</label>
                    
                    <input type="radio" name="rating" value="2" id="star2" required>
                    <label for="star2" class="star-label" title="2 stars">★</label>
                    
                    <input type="radio" name="rating" value="1" id="star1" required>
                    <label for="star1" class="star-label" title="1 star">★</label>
                </div>
                <textarea name="review" placeholder="Share your experience with this item... (optional)"></textarea>
                <button type="submit" class="submit-review-btn">Submit Review</button>
            </form>
        </div>
    </div>

    <script>
        function openReviewModal(itemId, itemName) {
            document.getElementById('modalItemName').textContent = `Rate ${itemName}`;
            document.getElementById('menu_item_id').value = itemId;
            document.getElementById('reviewModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
            document.getElementById('reviewForm').reset();
        }

        function closeReviewModal() {
            document.getElementById('reviewModal').style.display = 'none';
            document.body.style.overflow = '';
            document.getElementById('reviewForm').reset();
        }

        // Close modal when clicking outside
        document.getElementById('reviewModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeReviewModal();
            }
        });

        // Form validation
        document.getElementById('reviewForm').addEventListener('submit', function(e) {
            const rating = document.querySelector('input[name="rating"]:checked');
            if (!rating) {
                e.preventDefault();
                alert('Please select a rating');
            }
        });

        // Prevent modal close when clicking inside the modal content
        document.querySelector('.modal-content').addEventListener('click', function(e) {
            e.stopPropagation();
        });
    </script>
</body>
</html>
 