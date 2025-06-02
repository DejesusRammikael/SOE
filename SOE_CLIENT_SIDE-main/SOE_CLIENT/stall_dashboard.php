<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_connection.php';

// Check if stall_id is set and valid


$stall_id = isset($_GET['stall_id']) ? (int)$_GET['stall_id'] : 0;

// Get stall info
$stmt = $conn->prepare("SELECT * FROM stall WHERE stall_id = ?");
$stmt->bind_param("i", $stall_id);
$stmt->execute();
$stall_result = $stmt->get_result();
$stall = $stall_result->fetch_assoc();

if (!$stall) {
    echo "<div style='margin:50px auto;text-align:center;color:#c00;font-size:1.5em;'>Stall not found.</div>";
    exit;
}


// For featured items
$stmt = $conn->prepare("
    SELECT p.*, s.stall_name 
    FROM products AS p
    JOIN stall AS s ON p.stall_id = s.stall_id
    WHERE p.is_featured = 1 
      AND p.available = 1 
      AND p.stall_id = ? 
    ORDER BY p.category
");
$stmt->bind_param("i", $stall_id);
$stmt->execute();
$featured_result = $stmt->get_result();

// For regular items
$stmt = $conn->prepare("
    SELECT p.*, s.stall_name 
    FROM products AS p
    JOIN stall AS s ON p.stall_id = s.stall_id
    WHERE p.stall_id = ? 
    ORDER BY p.category, p.product_name
");
$stmt->bind_param("i", $stall_id);
$stmt->execute();
$regular_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($stall['stall_name']) ?> - Stall Menu</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/view_cart.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        
        body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 20px;
}
.back-btn {
    margin-top: -160px;
    display: inline-block;
    margin-bottom: 10px;
    padding: 8px 16px;
    background-color: #2c3e50;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}
.stall-header {
    margin-top: -90px;
}


.content {
    flex: 1;
    margin-left: 280px;
    padding: 90px 30px 30px;
}


.featured-section {
    margin-top: -160px;
    margin-left: -220px;
    position: relative;
    width: 100%;
    max-width: 1500px;
    padding: 40px 20px;
    overflow: hidden;
}

.featured-carousel {
    position: relative;
    width: 100%;
    height: 550px;
    margin: 0 auto;
}

.featured-card {
    position: absolute;
    top: -20px;
    left: 0;
    width: 100%;
    height: 590px;
    opacity: 0;
    visibility: hidden;
    transform: translateX(-100%);
    transition: all 0.5s ease;
    border-radius: 20px;
    overflow: hidden;
    background-size: cover;
    background-position: center;
}

.featured-card.active {
    opacity: 1;
    visibility: visible;
    transform: translateX(0);
    z-index: 2;
}

.featured-card.prev {
    opacity: 0;
    visibility: hidden;
    transform: translateX(-100%);
    z-index: 1;
}

.featured-card.next {
    opacity: 0;
    visibility: hidden;
    transform: translateX(100%);
    z-index: 1;
}

/* Dark gradient overlay */
.featured-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to right, rgba(0,0,0,0.7), rgba(0,0,0,0.2));
    z-index: 1;
}

/* Content styling */
.featured-content {
    position: absolute;
    left: 0;
    bottom: 0;
    z-index: 2;
    width: 45%;
    padding: 3rem;
    background: linear-gradient(90deg, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.6) 80%, transparent 100%);
    color: #fff;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    text-shadow: 1px 1px 4px rgba(0,0,0,0.5);
}

.featured-content h3 {
    font-size: 2.8rem;
    font-weight: 700;
    margin-bottom: 1rem;
    line-height: 1.2;
    color: #4b5563;
    transform: translateY(20px);
    opacity: 0;
    animation: slideUp 0.8s forwards;
}

.featured-content p {
    font-size: 1.1rem;
    margin-bottom: 1.5rem;
    line-height: 1.6;
    color: rgb(207, 207, 207);
    transform: translateY(20px);
    opacity: 0;
    animation: slideUp 0.8s 0.2s forwards;
}

.featured-content strong {
    font-size: 2.2rem;
    font-weight: 700;
    color:rgb(42, 92, 209);
    margin-bottom: 1.5rem;
    display: block;
    transform: translateY(20px);
    opacity: 0;
    animation: slideUp 0.8s 0.4s forwards;
}

.featured-content .category {
    margin-bottom: -20px;
    display: inline-block;
    width: 120px;
    text-align: center;
    padding: 6px 10px;
    background-color: rgba(211, 248, 1, 0.8);
    backdrop-filter: blur(5px);
    color:rgb(41, 49, 63);
    font-size: 0.9rem;
    border-radius: 20px;
    font-weight: 500;
    transform: translateY(20px);
    opacity: 0;
    animation: slideUp 0.8s 0.6s forwards;
}


.featured-content .featured-btn {
    transform: translateY(20px);
    opacity: 0;
    animation: slideUp 0.8s 0.8s forwards;
}

.featured-content .add-to-cart-btn {
    background:rgb(233, 255, 33);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 25px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 10px;
    width: auto;
}

.featured-content .add-to-cart-btn:hover {
    background:rgb(231, 228, 41);
    transform: translateY(-2px);
}

.featured-content .add-to-cart-btn i {
    font-size: 1.2rem;
}

@keyframes slideUp {
    from {
        transform: translateY(20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Responsive adjustments */
@media (max-width: 1200px) {
    .featured-content {
        width: 55%;
    }
    
    .featured-content h3 {
        font-size: 2.4rem;
    }
}

@media (max-width: 768px) {
    .featured-content {
        width: 100%;
        background: linear-gradient(0deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.6) 80%, transparent 100%);
    }
    
    .featured-content h3 {
        font-size: 2rem;
    }
    
    .featured-content p {
        font-size: 1rem;
    }
    
    .featured-content strong {
        font-size: 1.8rem;
    }
}


.menu-section {
    margin-top: 60px;
    margin-left: -290px;
}
.menu-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}
.menu-title {
    color: #1e3c72;
    font-size: 35px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.search-container {
    position: relative;
    width: 340px;
}
.search-box input {
    width: 100%;
    padding: 12px 20px;
    padding-left: 40px;
    border: 2px solid #e8f0fe;
    border-radius: 10px;
    font-size: 15px;
    transition: all 0.3s ease;
}
.search-box input:focus {
    border-color: #2a5298;
    outline: none;
    box-shadow: 0 0 0 3px rgba(42, 82, 152, 0.1);
}
.search-box i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #1e3c72;
}
.menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 30px;
    margin-top: 20px;
}


.card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(233, 214, 42, 0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    transform-origin: center bottom;
    padding: 15px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
}

.card img {
    width: 100%;
    height: 220px;
    object-fit: cover;
    border-radius: 10px;
} 

.card-content {
    padding: 20px;
}

.card h3 {
    color:rgb(43, 46, 53);
    margin-bottom: 8px;
    font-size: 18px;
}

.card p {
    color: #666;
    font-size: 14px;
    margin-bottom: 15px;
    line-height: 1.4;
}

.category-tag {
    display: inline-block;
    padding: 4px 12px;
    background:rgba(238, 255, 84, 0.99);
    color: #1e3c72;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    margin-top: 5px;
}


.card strong {
    color:rgb(83, 109, 153);
    font-size: 20px;
    display: block;
    margin-bottom: 15px;
}

.quantity-control {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 15px;
    margin: 20px 0;
}

.quantity-control button {
    background-color:rgb(34, 64, 121);
    color: white;
    border: none;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    font-size: 18px;
    cursor: pointer;
    transition: background-color 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.quantity-control button:hover {
    background-color:rgb(45, 104, 158);
}

.qty-display {
    width: 40px;
    height: 35px;
    background-color: #f2f2f2;
    border: 1px solid #ccc;
    border-radius: 6px;
    text-align: center;
    line-height: 35px;
    font-size: 16px;
    font-weight: 600;
    user-select: none;
}

.add-to-cart-btn {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 8px;
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    color: white;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.add-to-cart-btn:hover {
    transform: translateY(-2px);
}

.add-to-cart-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
    transform: none;
}

.cart-button {
    position: fixed;
    top: 10px;
    right: 30px;
    padding: 12px 25px;
    background: white;
    color: #1e3c72;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    z-index: 1001;
}

.cart-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
}

.cart-button.bounce {
    animation: cartBounce 0.5s ease;
}

.cart-button.shake {
    animation: cartShake 0.5s ease;
}

/* Cart icon styles */
.cart-icon {
    font-size: 1.2em;
    transition: transform 0.3s ease;
}

.cart-button:hover .cart-icon {
    transform: scale(1.1);
}

.cart-button {
    position: fixed;
    top: 10px;
    right: 30px;
    padding: 12px 25px;
    background: white;
    color: #1e3c72;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: transform 0.2s ease;
    z-index: 1001;
}

#cartBtn {
    position: fixed;
    top: 10px;
    right: 30px;
    padding: 12px 25px;
    background: white;
    color: #1e3c72;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: transform 0.2s ease;
    z-index: 1001;
}

#cartBtn:hover {
    transform: translateY(-2px);
}

.cart-modal {
    position: fixed;
    top: 80px;
    right: 30px;
    width: 350px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.15);
    z-index: 1001;
    padding: 20px;
}

.cart-modal h3 {
    color: #1e3c72;
    margin-top: -8px;
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.cart-item {
    padding: 12px 0;
    border-bottom: 1px solid #e8f0fe;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.cart-item:last-child {
    border-bottom: none;
}

.close-cart {
    color: #1e3c72;
    cursor: pointer;
    font-size: 24px;
}

.cart-total {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 2px solid #e8f0fe;
    text-align: right;
    color: #1e3c72;
    font-weight: bold;
    font-size: 18px;
}

.view-cart-btn {
    display: block;
    width: 100%;
    padding: 12px;
    margin-top: 15px;
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 900;
    cursor: pointer;
    text-align: center;
    text-decoration: none;
}

.view-cart-btn:hover {
    opacity: 0.9;
}

.card {
    animation: fadeIn 3.5s ease backwards;
}

.card:nth-child(n) {
    animation-delay: calc(0.1s * var(--i, 0));
        }
.checkout-btn {
    display: block;
    width: 100%;
    margin-top: 10px;
    padding: 10px;
    background-color: #2c3e50;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.checkout-btn:hover {
    background-color: #34495e;
}

.toast-notification {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    background-color:rgb(224, 228, 31);
    color: white;
    padding: 12px 24px;
    border-radius: 4px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    z-index: 1000;
   
}

.toast-notification.show {
    opacity: 1;
    visibility: visible;
    transform: translateX(-50%) translateY(0);
}

.admin-header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 60px;
    background-color: #0f4c75;
    color: white;
    display: flex;
    align-items: center;
    padding: 0 20px;
    z-index: 1000;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.header-brand {
    display: flex;
    align-items: center;
}

.header-logo {
    margin-left: -10px;
    margin-top: 5px;
    height: 60px;
    object-fit: contain;
}

.sidebar {
    width: 240px;
    background-color: rgba(0, 43, 92, 0.9);
    border-radius: 10px;
    margin-top: 10px;
    color: white;
    height: 100vh;
    padding: 20px;
    position: fixed;
    top: 60px;
    left: 0;
}

.sidebar .divider {
    border: none;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
    margin: 1rem auto;
    width: 90%;
}

.logoo-wrapper {
    text-align: center;
    padding: 20px 0;
}

.logoo-wrapper img {
    position: relative;
    display: inline-block;
    margin-left: -10px;
    height: 150px;
    width: 190px;
}

.logoo {
    color: white;
    font-size: 24px;
    font-weight: bold;
    margin: 10px 0;
}

.tagline {
    color: rgba(255, 255, 255, 0.8);
    font-size: 14px;
    font-style: italic;
    margin-bottom: 20px;
}

.sidebar h2 {
    font-size: 20px;
    margin: 20px 0 15px 0;
    color: white;
}

.sidebar a {
    color: white;
    text-decoration: none;
    display: flex;
    align-items: center;
    padding: 10px;
    margin-bottom: 8px;
    border-radius: 8px;
    transition: background 0.3s;
    font-size: 15px;
}

.sidebar a:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.tagline {
    font-style: italic;
    color: rgba(255,255,255,0.8);
    font-size: 14px;
}


.stall-info-container {
    margin-top: -80px;
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
}

.stall-info {
    flex: 1;
}

.stall-info h1 {
    font-size: 32px;
    color: #1e3c72;
    margin: 0;
    margin-bottom: 10px;
}

.stall-description {
    color: #666;
    font-size: 16px;
    margin-bottom: 0;
}

.review-btn {
    display: flex;
    align-items: center;       
    justify-content: center;    
    width: 150px;
    height: 40px;
    background: rgb(31, 99, 163);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    white-space: nowrap;
    margin-left: 1.25rem;
    font-size: 1rem;
}


.review-btn:hover {
    background:rgb(39, 102, 184);
    transform: translateY(-2px);
}

.category-filter {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.category-btn {
    padding: 8px 16px;
    border: 2px solid #1e3c72;
    background: white;
    color: #1e3c72;
    border-radius: 20px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}

.category-btn:hover {
    background: rgba(30, 60, 114, 0.1);
}

.category-btn.active {
    background: #1e3c72;
    color: white;
}

.search-box {
    position: relative;
    display: flex;
    align-items: center;
    max-width: 400px;
    margin: 0 auto;
}

.search-box i {
    position: absolute;
    left: 15px;
    color: #888;
}

.search-box input {
    width: 100%;
    padding: 10px 10px 10px 35px;
    border: 1px solid #ccc;
    border-radius: 20px;
}

.visually-hidden {
    position: absolute;
    left: -9999px;
    top: auto;
    width: 1px;
    height: 1px;
    overflow: hidden;
}
.featured-content {
    padding: 15px;
    background: transparent;
    position: relative;
    z-index: 2;
}

.featured-content h3 {
    margin-left: -10px;
    font-size: 30px;
    color:rgb(43, 46, 53);
}

.featured-content p {
    margin-top: -20px;
    margin-left: 5px;
    font-size: 16px;
    color:rgb(59, 67, 82);
    margin-bottom: 4px;
    line-height: 1.5;
}
.category {
    display: inline-block;
    padding: 4px 12px;
    background:rgba(238, 255, 84, 0.99);
    color: #1e3c72;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    margin-top: 5px;
}

.featured-content strong {
    margin-left: 7px;
    font-size: 28px;
    color:rgb(51, 83, 141);
}

.quantity-controls {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 15px;
    margin: 20px 0;
}

.quantity-controls button {
    background-color: #1b4d4d;
    color: white;
    border: none;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    font-size: 18px;
    cursor: pointer;
    transition: background-color 0.3s;
    display: flex;
    align-items: center;
    margin-bottom: 5px;
    justify-content: center;
}

.quantity-controls button:hover {
    background-color: #2c7a7a;
}

.qty-display{
    font-size: 18px;
    font-weight: bold;
    color: #333;
    min-width: 30px;
    text-align: center;
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
}

.cart-modal {
    position: fixed;
    top: 80px;
    right: 30px;
    width: 350px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.15);
    z-index: 1001;
    padding: 20px;
}

.cart-modal h3 {
    color: #1e3c72;
    margin-bottom: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.cart-item {
    padding: 12px 0;
    border-bottom: 1px solid #e8f0fe;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.cart-item:last-child {
    border-bottom: none;
}

.close-cart {
    color: #1e3c72;
    cursor: pointer;
    font-size: 24px;
}

.cart-total {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 2px solid #e8f0fe;
    text-align: right;
    color: #1e3c72;
    font-weight: bold;
    font-size: 18px;
}

.view-cart-btn {
    display: block;
    width: 100%;
    padding: 12px;
    margin-top: 15px;
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 900;
    cursor: pointer;
    text-align: center;
    text-decoration: none;
}

.view-cart-btn:hover {
    opacity: 0.9;
}

.card {
    animation: fadeIn 3.5s ease backwards;
}

.card:nth-child(n) {
    animation-delay: calc(0.1s * var(--i, 0));
}


#no-results-message {
    margin-top: -85px;
    margin-bottom: 50px;
    display: none;
    text-align: center;
    color: #4b5563;
    font-size: 18px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    transition: all 0.3s ease-in-out;
}

#no-results-message i {
    font-size: 56px;
    color:rgb(21, 77, 168); 
    margin-bottom: 20px;
    display: block;
    animation: bounce 1.5s infinite;
}

@keyframes bounce {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-8px);
    }
}

.menu-grid {
    position: relative;
    min-height: 200px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 30px;
    margin-top: 20px;
}


.confirm-modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    text-align: center;
    max-width: 400px;
    width: 90%;
    z-index: 1003;
}

.confirm-modal h3 {
    color:rgb(69, 74, 83);
    font-size: 24px;
    margin-bottom: 15px;
}

.confirm-modal p {
    color: #666;
    font-size: 16px;
    margin-bottom: 25px;
    line-height: 1.5;
}

.modal-buttons {
    display: flex;
    justify-content: center;
    gap: 15px;
}

.modal-btn {
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s;
}

.modal-btn:hover {
    transform: translateY(-2px);
}

.cancel-modal-btn {
    background: #f8f9fa;
    color:rgb(67, 74, 87);
}

.confirm-modal-btn {
    background: #ffd700;
    color: #333;
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

.card-content strong {
    display: block;
    font-size: 1.4rem;
    color: #1e3c72;
    margin: 10px 0;
}

.card-content .stall {
    display: flex;
    align-items: center;
    color: #666;
    font-size: 0.9rem;
    margin: 8px 0;
}

.card-content .category-tag {
    display: inline-block;
    padding: 4px 12px;
    background: rgba(238, 255, 84, 0.99);
    color: #1e3c72;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.quantity-control {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin: 15px 0;
}

.quantity-control button {
    width: 35px;
    height: 35px;
    background-color: #1e3c72;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 20px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.quantity-control button:hover {
    background-color: #2a5298;
}

.qty-display {
    width: 40px;
    height: 35px;
    background-color: #f2f2f2;
    border: 1px solid #ddd;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.add-to-cart-btn {
    width: 100%;
    padding: 12px;
    background-color: #1e3c72;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.add-to-cart-btn:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}

.add-to-cart-btn:not(:disabled):hover {
    background-color: #2a5298;
}

.item-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin: 10px 0;
}

.category-tag {
    display: inline-block;
    padding: 4px 12px;
    background: rgba(238, 255, 84, 0.99);
    color: #1e3c72;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.featured-tag {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 12px;
    background: #ffd700;
    color: #1e3c72;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.unavailable-tag {
    display: inline-block;
    padding: 4px 12px;
    background: #ff4444;
    color: white;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.price {
    display: block;
    font-size: 1.4rem;
    color: #1e3c72;
    margin: 10px 0;
}

.out-of-stock-btn {
    width: 100%;
    padding: 12px;
    background-color: #ff4444;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 600;
    cursor: not-allowed;
    opacity: 0.8;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.no-items-message {
    grid-column: 1 / -1;
    text-align: center;
    padding: 40px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.no-items-message i {
    font-size: 3rem;
    color: #1e3c72;
    margin-bottom: 15px;
}

.no-items-message p {
    color: #666;
    font-size: 1.1rem;
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
        $stall_query = "SELECT * FROM stall";
        $stall_result = $conn->query($stall_query);
        while($stall_item = $stall_result->fetch_assoc()): ?>
            <a href="stall_dashboard.php?stall_id=<?= $stall_item['stall_id'] ?>">
                <i class="fas fa-store"></i> <?= htmlspecialchars($stall_item['stall_name']) ?>
            </a>
        <?php endwhile; ?>
    </div>

    <!-- Main Content -->
    <div class="content">
        <div class="header">
            <a href="client_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <!-- Stall Info Container -->
        <div class="stall-info-container">
            <div class="stall-info">
                <h1><?= htmlspecialchars($stall['stall_name']) ?></h1>
                <p class="stall-description"><?= htmlspecialchars($stall['description'] ?? 'Specialty foods and beverages.') ?></p>
            </div>
    <a href="ratings.php?stall_id=<?= $stall_id ?>" class="review-btn">Review & Ratings</a>
</div>

             <!-- Main Content -->
<div class="content">
        <!-- Featured Section -->
        <div class="featured-section">
    <div class="featured-carousel">
        <?php 
        $featured_count = 0;
        while($item = $featured_result->fetch_assoc()): 
            $featured_count++;
        ?>
            <div class="featured-card" style="background-image: url('<?= (!empty($item['product_path']) && file_exists($item['product_path'])) ? htmlspecialchars($item['product_path']) : 'assets/images/default_product.png' ?>')" data-index="<?= $featured_count - 1 ?>">
                <div class="featured-content">
                    <h3><?= htmlspecialchars($item['product_name']) ?></h3>
                    <p><?= htmlspecialchars($item['description']) ?> - <span class="category"><?= htmlspecialchars($item['category']) ?></span></p>
                    <span class="stall"><i class="fas fa-location-dot" style="color:rgb(216, 198, 39); margin-right: 5px; margin-top: -10px;"></i><?= htmlspecialchars($item['stall_name']) ?></span>
                    <strong>₱<?= number_format($item['price'], 2) ?></strong>
                    <div class="featured-btn">
                        <button class="add-to-cart-btn" data-id="<?= $item['product_id'] ?>">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

        <!-- Menu Section -->
        <div class="menu-section">
            <div class="menu-header">
                <h2 class="menu-title"><i class="fas fa-utensils"></i> All Menu Items</h2>
                <div class="search-container">
                    <div class="category-filter">
                        <button class="category-btn active" data-category="all">All</button>
                        <button class="category-btn" data-category="Meals">Meals</button>
                        <button class="category-btn" data-category="Beverage">Beverages</button>
                        <button class="category-btn" data-category="Desserts">Desserts</button>
                    </div>

                <div class="search-box">
                    <label for="searchInput" class="visually-hidden">Search Menu Items</label>
                    <i class="fas fa-search"></i>
                    <input 
                        type="text" 
                        id="searchInput" 
                        placeholder="Search menu items..." 
                        aria-label="Search menu items"
                    >
                </div>
                
                </div>
            </div>

    <div class="menu-grid">
        <?php 
        $hasItems = false;
        while($item = $regular_result->fetch_assoc()): 
            $hasItems = true;
        ?>
            <div class="card" data-id="<?= $item['product_id'] ?>" data-category="<?= htmlspecialchars($item['category']) ?>">
                <img src="<?= (!empty($item['product_path']) && file_exists($item['product_path'])) ? htmlspecialchars($item['product_path']) : 'assets/images/default_product.png' ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
                <div class="card-content">
                    <h3><?= htmlspecialchars($item['product_name']) ?></h3>
                    <p><?= isset($item['description']) ? htmlspecialchars($item['description']) : 'No description' ?></p>
                    <div class="item-meta">
                        <span class="category-tag"><?= htmlspecialchars($item['category']) ?></span>
                        <?php if ($item['is_featured']): ?>
                            <span class="featured-tag"><i class="fas fa-star"></i> Featured</span>
                        <?php endif; ?>
                        <?php if (!$item['available']): ?>
                            <span class="unavailable-tag">Not Available</span>
                        <?php endif; ?>
                    </div>
                    <strong class="price">₱<?= number_format($item['price'], 2) ?></strong>

                    <?php if ($item['available']): ?>
                        <div class="quantity-control">
                            <button class="decrement-btn" data-id="<?= $item['product_id'] ?>">−</button>
                            <div class="qty-display" id="qty-<?= $item['product_id'] ?>">0</div>
                            <button class="increment-btn" data-id="<?= $item['product_id'] ?>">+</button>
                        </div>

                        <button class="add-to-cart-btn" data-id="<?= $item['product_id'] ?>" disabled>
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                    <?php else: ?>
                        <button class="out-of-stock-btn" disabled>
                            <i class="fas fa-ban"></i> Out of Stock
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>

        <?php if (!$hasItems): ?>
            <div class="no-items-message">
                <i class="fas fa-utensils"></i>
                <p>No menu items available for this stall.</p>
            </div>
        <?php endif; ?>
    </div>

    <div id="no-results-message" style="display: none;">
        <i class="fas fa-search"></i>
        No menu items found matching your search.
    </div>
</div>

<!-- Cart Implementation -->
<div class="modal-overlay" id="modalOverlay" style="display: none;"></div>
<button class="cart-button" id="cartBtn">🛒 View Cart (0)</button>

<div class="cart-modal" id="cartModal" style="display:none;">
    <h3>EZ Tray<span class="close-cart" id="closeCart">&times;</span></h3>
    <div id="cartItems"></div>
    <div style="margin-top: 15px; text-align: right;">
        <strong>Total: ₱<span id="cartTotal">0.00</span></strong>
        <button onclick="window.location.href='view_cart.php'" style="display: block; width: 100%; margin-top: 10px; padding: 10px; background-color:rgb(47, 66, 150); color: white; border: none; border-radius: 6px; cursor: pointer;">Go to Cart to Checkout</button>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal-overlay" id="confirmModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1002;">
        <div class="confirm-modal">
            <h3>Remove Item</h3>
            <p>Are you sure you want to remove <span id="itemName"></span> from your cart?</p>
            <div class="modal-buttons">
                <button class="modal-btn cancel-modal-btn" onclick="closeConfirmModal()">Cancel</button>
                <button class="modal-btn confirm-modal-btn" onclick="confirmRemove()">Remove</button>
            </div>
    </div>
</div>

<script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM Content Loaded'); // Debug log
            
            // Get all necessary elements
            const categoryBtns = document.querySelectorAll('.category-btn');
            const searchInput = document.getElementById('searchInput');
            const noResultsMessage = document.getElementById('no-results-message');
            
            console.log('Category buttons found:', categoryBtns.length); // Debug log
            
            
            function filterItems() {
                const selectedCategory = document.querySelector('.category-btn.active')?.getAttribute('data-category') || 'all';
                const searchTerm = searchInput.value.toLowerCase().trim();

                // Handle only .card (regular menu items)
                const menuItems = document.querySelectorAll('.card');
                let visibleCount = 0;

                menuItems.forEach(item => {
                    const itemCategory = item.getAttribute('data-category');
                    const name = item.querySelector('h3')?.textContent.toLowerCase() || '';
                    const description = item.querySelector('p')?.textContent.toLowerCase() || '';

                    const matchesCategory = selectedCategory === 'all' || itemCategory === selectedCategory;
                    const matchesSearch = searchTerm === '' || name.includes(searchTerm) || description.includes(searchTerm);

                    if (matchesCategory && matchesSearch) {
                        item.style.display = '';
                        visibleCount++;
                    } else {
                        item.style.display = 'none';
                    }
                });

                // Always show featured items regardless of filter
                const featuredItems = document.querySelectorAll('.featured-card');
                featuredItems.forEach(item => {
                    item.style.display = '';
                });

                // Show/hide 'No results' message
                noResultsMessage.style.display = visibleCount === 0 ? 'block' : 'none';
            }
   
            // Add click event listeners to category buttons
            categoryBtns.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    console.log('Category button clicked:', btn.getAttribute('data-category')); // Debug log
                    
                    // Remove active class from all buttons
                    categoryBtns.forEach(b => b.classList.remove('active'));
                    
                    // Add active class to clicked button
                    btn.classList.add('active');
                    
                    // Filter items
                    filterItems();
                });
            });
            
            // Add input event listener to search box
            if (searchInput) {
                searchInput.addEventListener('input', () => {
                    console.log('Search input changed:', searchInput.value); // Debug log
                    filterItems();
                });
            }
            
            // Set initial active category
            const defaultCategory = document.querySelector('.category-btn[data-category="all"]');
            if (defaultCategory) {
                defaultCategory.classList.add('active');
            }
            
            // Initial filtering
            filterItems();
        });

    const cartBtn = document.getElementById('cartBtn');
    const cartModal = document.getElementById('cartModal');
    const cartItemsDiv = document.getElementById('cartItems');
    const cartTotalSpan = document.getElementById('cartTotal');
    const closeCartBtn = document.getElementById('closeCart');
    
    function updateAddToCartBtn(id) {
        const qty = parseInt(document.getElementById(`qty-${id}`).textContent);
        const btn = document.querySelector(`.add-to-cart-btn[data-id="${id}"]`);
        btn.disabled = qty <= 0;
    }

        let itemToRemove = null;
        let itemToRemoveName = '';

        function showConfirmModal(menuItemId, itemName) {
            itemToRemove = menuItemId;
            itemToRemoveName = itemName;
            document.getElementById('itemName').textContent = itemName;
            document.getElementById('confirmModal').style.display = 'block';
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').style.display = 'none';
            document.body.style.overflow = ''; // Restore scrolling
            itemToRemove = null;
            itemToRemoveName = '';
        }

        function confirmRemove() {
            if (itemToRemove) {
                removeItemFromCart(itemToRemove);
                closeConfirmModal();
            }
        }

        async function removeItemFromCart(menuItemId) {
            try {
                const response = await fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `menu_item_id=${menuItemId}&quantity=0`
                });

                const result = await response.json();
                if (result.success) {
                    await loadCart();
                    // Show success message
                    const toast = document.createElement('div');
                    toast.style.cssText = `
                        position: fixed;
                        top: 20px;
                        left: 50%;
                        transform: translateX(-50%);
                        background-color:rgb(224, 228, 31);
                        color: white;
                        padding: 12px 24px;
                        border-radius: 4px;
                        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
                        z-index: 1000;
                    `;
                    toast.textContent = `${itemToRemoveName} removed from cart`;
                    document.body.appendChild(toast);
                    setTimeout(() => toast.remove(), 3000);
                } else {
                    alert('Failed to remove item: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Could not connect to server');
            }
        }
    
    async function loadCart() {
        try {
            const response = await fetch('get_cart.php');
            const data = await response.json();

            if (data.success) {
                cartItemsDiv.innerHTML = '';
                let total = 0, count = 0;
                
                data.items.forEach(item => {
                    item.price = parseFloat(item.price);
                    total += item.price * item.qty;
                        count += parseInt(item.qty);

                    const div = document.createElement('div');
                    div.classList.add('cart-item');
                    div.innerHTML = `
                            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; padding: 0 10px;">
                                <div style="margin-left: 5px;">
                                <strong>${item.name}</strong><br>
                                ₱${item.price.toFixed(2)} x ${item.qty}
                            </div>
                                <button onclick="showConfirmModal(${item.menu_item_id}, '${item.name.replace(/'/g, "\\'")}')" class="remove-btn">
                                    <i class="fas fa-trash"></i>
                                </button>
                        </div>
                    `;
                    cartItemsDiv.appendChild(div);
                });

                cartTotalSpan.textContent = total.toFixed(2);
                    cartBtn.innerHTML = `🛒 View Cart (${count})`;
                    
                    if (count > 0) {
                        cartBtn.classList.add('has-items');
                    } else {
                        cartBtn.classList.remove('has-items');
                    }
            } else {
                console.error('Error fetching cart data:', data.message || 'Unknown error');
                    cartBtn.innerHTML = `🛒 View Cart (0)`;
            }
        } catch (error) {
            console.error('Error loading cart:', error);
                cartBtn.innerHTML = `🛒 View Cart (0)`;
        }
    }
    
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', async () => {
            const id = button.getAttribute('data-id');
            const card = button.closest('.card, .featured-card'); // Get the parent card
            const name = card.querySelector('h3').textContent;

            let quantity = 1; // Default quantity to 1

            // Check if quantity controls exist (for regular items)
            const qtyDisplay = card.querySelector('.quantity-display');
            if (qtyDisplay) {
                quantity = parseInt(qtyDisplay.textContent);
            }

            if (quantity > 0) {
                try {
                        // First, get the current cart to check for existing items
                        const cartResponse = await fetch('get_cart.php');
                        const cartData = await cartResponse.json();
                        
                        // Check if item already exists in cart
                        let existingQty = 0;
                        if (cartData.success && cartData.items) {
                            const existingItem = cartData.items.find(item => item.menu_item_id === id);
                            if (existingItem) {
                                existingQty = parseInt(existingItem.qty);
                                console.log(`Item already in cart with quantity: ${existingQty}`);
                            }
                        }

                        // Add new quantity to existing quantity
                        const totalQty = existingQty + quantity;
                        console.log(`Adding to cart: ${id} with total quantity ${totalQty}`);

                    const response = await fetch('add_to_cart.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                            body: `menu_item_id=${id}&quantity=${totalQty}`
                    });

                    console.log('Response status:', response.status);

                    if (!response.ok) {
                         console.error('HTTP error!', response.status, response.statusText);
                         alert('HTTP error: ' + response.status);
                         return;
                    }

                    const result = await response.json();

                    console.log('add_to_cart.php response:', result);

                    if(result.success) {
                        console.log('Item added successfully, reloading cart...');
                            // Show success message with combined quantity
                        const toast = document.createElement('div');
                        toast.style.cssText = `
                            position: fixed;
                            top: 20px;
                            left: 50%;
                            transform: translateX(-50%);
                            background-color:rgb(192, 202, 49);
                            color: rgb(69, 74, 83);
                            padding: 12px 24px;
                            border-radius: 4px;
                            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
                            z-index: 1000;
                        `;
                            toast.textContent = existingQty > 0 
                                ? `Updated ${name} quantity to ${totalQty} in cart!`
                                : `Added ${quantity} x ${name} to cart!`;
                        document.body.appendChild(toast);
                        setTimeout(() => toast.remove(), 3000);

                        // Reset quantity display & disable add button (only for regular items)
                         if (qtyDisplay) {
                            qtyDisplay.textContent = '0';
                            updateAddToCartBtn(id);
                         }

                        // Reload cart display
                        await loadCart();
                        console.log('Cart reloaded.');

                    } else {
                        console.error('Server reported failure:', result.message);
                        alert('Failed to add to cart: ' + result.message);
                    }
                } catch (error) {
                    console.error('Error in fetch or processing response:', error);
                    alert('Could not connect to server or process response');
                }
            }
        });
    });

        cartBtn.addEventListener('click', async () => {
            if(cartModal.style.display === 'block'){
                cartModal.style.display = 'none';
            } else {
                await loadCart();
                cartModal.style.display = 'block';
            }
        });

        closeCartBtn.addEventListener('click', () => {
            cartModal.style.display = 'none';
        });
        
        window.addEventListener('click', e => {
            if (e.target === cartModal) {
                cartModal.style.display = 'none';
            }
        });
        
        document.querySelectorAll('.add-to-cart-btn').forEach(button => {
            updateAddToCartBtn(button.getAttribute('data-id'));
        });

        loadCart();

        // Initialize featured carousel
        const featuredCards = document.querySelectorAll('.featured-carousel .featured-card');
        let currentFeatured = 0;

        function showFeaturedCard(index) {
            console.log('Showing featured card:', index); // Debug log
            featuredCards.forEach((card, i) => {
                card.classList.remove('active');
                if (i === index) {
                    card.classList.add('active');
                }
            });
        }

        if (featuredCards.length > 0) {
            console.log('Found featured cards:', featuredCards.length); // Debug log
            showFeaturedCard(currentFeatured);
            
            // Auto-rotate featured cards
            setInterval(() => {
                currentFeatured = (currentFeatured + 1) % featuredCards.length;
                showFeaturedCard(currentFeatured);
            }, 5000); // Change slide every 5 seconds
        } else {
            console.log('No featured cards found'); // Debug log
        }

        // Add event listeners for increment and decrement buttons
    document.querySelectorAll('.increment-btn').forEach(button => {
        button.addEventListener('click', () => {
            const id = button.getAttribute('data-id');
            const qtyDisplay = document.getElementById(`qty-${id}`);
                const currentQty = parseInt(qtyDisplay.textContent);
                qtyDisplay.textContent = currentQty + 1;
            updateAddToCartBtn(id);
        });
    });
    
    document.querySelectorAll('.decrement-btn').forEach(button => {
        button.addEventListener('click', () => {
            const id = button.getAttribute('data-id');
            const qtyDisplay = document.getElementById(`qty-${id}`);
                const currentQty = parseInt(qtyDisplay.textContent);
                if (currentQty > 0) {
                    qtyDisplay.textContent = currentQty - 1;
                updateAddToCartBtn(id);
            }
        });
    });

        const style = document.createElement('style');
        style.textContent = `
            .cart-button.has-items {
                background: #1e3c72;
                color: white;
                animation: pulse 2s infinite;
            }
            
            @keyframes pulse {
                0% {
                    transform: scale(1);
                }
                50% {
                    transform: scale(1.05);
                }
                100% {
                    transform: scale(1);
                }
            }
        `;
        document.head.appendChild(style);

        // Update the remove button style
        const removeButtonStyle = document.createElement('style');
        removeButtonStyle.textContent = `
            .remove-btn {
                background: transparent;
                color:rgb(201, 174, 21);
                border: none;
                padding: 8px;
                border-radius: 4px;
                cursor: pointer;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .remove-btn:hover {
                transform: scale(1.1);
                color:rgb(218, 204, 81);
            }
            
            .remove-btn i {
                font-size: 16px;
            }

            .cart-item {
                padding: 8px 0;
                border-bottom: 1px solid #e8f0fe;
            }

            .cart-item:last-child {
                border-bottom: none;
            }
        `;
        document.head.appendChild(removeButtonStyle);

        // Close modal when clicking outside
        document.getElementById('confirmModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeConfirmModal();
            }
        });
</script>
</body>
</html>