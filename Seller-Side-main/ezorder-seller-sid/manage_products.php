<?php
session_start();
require_once 'function.php';

// Check for cookies if session is not set
if (!isset($_SESSION['seller_id']) && isset($_COOKIE['seller_id'])) {
    $_SESSION['seller_id'] = $_COOKIE['seller_id'];
}
if (!isset($_SESSION['stall_id']) && isset($_COOKIE['stall_id'])) {
    $_SESSION['stall_id'] = $_COOKIE['stall_id'];
}

if (!isset($_SESSION['stall_id'])) {
    echo "<div class='alert error'>Stall not found. Please log in again.</div>";
    exit;
}

// Handle product deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $product_id = intval($_GET['delete']);
    $conn = new mysqli("localhost", "root", "", "ezorderdb");
    
    // First, get the product name for the success message
    $stmt = $conn->prepare("SELECT product_name FROM products WHERE product_id = ? AND stall_id = ?");
    $stmt->bind_param("ii", $product_id, $_SESSION['stall_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
    
    // Then delete the product
    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ? AND stall_id = ?");
    $stmt->bind_param("ii", $product_id, $_SESSION['stall_id']);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    
    if (isset($product['product_name'])) {
        $_SESSION['success_message'] = "Product '{$product['product_name']}' has been deleted successfully!";
    } else {
        $_SESSION['success_message'] = "Product has been deleted successfully!";
    }
    echo "<script>window.location.href = 'manage_products.php';</script>";
    exit();
}

// Handle product update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_product_id'])) {
    $product_id = intval($_POST['edit_product_id']);
    $product_name = $_POST['edit_product_name'];
    $price = $_POST['edit_price'];
    $description = $_POST['edit_description'];
    $product_path = $_POST['current_product_path'];
    $category = $_POST['edit_category'];



    // Handle new image upload
    if (isset($_FILES['edit_product_path']) && $_FILES['edit_product_path']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = "uploads/products/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $filename = basename($_FILES['edit_product_path']['name']);
        $product_path = $upload_dir . uniqid() . "_" . $filename;
        move_uploaded_file($_FILES['edit_product_path']['tmp_name'], $product_path);
        
        if ($product_path && file_exists($product_path)) {
            // Define the target directory in SOE_CLIENT_SIDE-main
            $client_dir = "../../SOE_CLIENT_SIDE-main/SOE_CLIENT/assets/images/";
            if (!is_dir($client_dir)) {
                mkdir($client_dir, 0777, true);
            }
            // Copy the file
            $client_path = $client_dir . basename($product_path);
            copy($product_path, $client_path);
        }
    }

    $conn = new mysqli("localhost", "root", "", "ezorderdb");
    $stmt = $conn->prepare("UPDATE products SET product_name=?, price=?, product_path=?, description=?, category=? WHERE product_id=? AND stall_id=?");
    $stmt->bind_param("sdsssii", $product_name, $price, $product_path, $description, $category, $product_id, $_SESSION['stall_id']);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    $_SESSION['success_message'] = "Product updated successfully!";
    echo "<script>window.location.href = 'manage_products.php';</script>";
    exit();
}

// Handle adding new products and updates
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $edit_mode = isset($_POST['edit_mode']) && $_POST['edit_mode'] == '1';
    $stall_id = $_SESSION['stall_id'];
    
    if ($edit_mode) {
        // Handle product update
        $product_id = intval($_POST['product_id']);
        $product_name = $_POST['product_name'][0];
        $price = floatval($_POST['price'][0]);
        $description = $_POST['description'][0];
        $category = $_POST['category'][0];
        $stall_id = $_SESSION['stall_id'];
        
        try {
            // Get current product path
            $conn = new mysqli("localhost", "root", "", "ezorderdb");
            $stmt = $conn->prepare("SELECT product_path FROM products WHERE product_id = ? AND stall_id = ?");
            $stmt->bind_param("ii", $product_id, $stall_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();
            $product_path = $product['product_path'];
            $stmt->close();
            
            // Handle new image upload if provided
            if (isset($_FILES['product_path']['name'][0]) && $_FILES['product_path']['error'][0] == UPLOAD_ERR_OK) {
                // Delete old image if exists
                if ($product_path && file_exists($product_path)) {
                    unlink($product_path);
                }
                
                // Upload new image
                $upload_dir = "uploads/products/";
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $filename = basename($_FILES['product_path']['name'][0]);
                $product_path = $upload_dir . uniqid() . "_" . $filename;
                move_uploaded_file($_FILES['product_path']['tmp_name'][0], $product_path);

                // Copy to client side
                if ($product_path && file_exists($product_path)) {
                    $client_dir = "../../SOE_CLIENT_SIDE-main/SOE_CLIENT/assets/images/";
                    if (!is_dir($client_dir)) {
                        mkdir($client_dir, 0777, true);
                    }
                    $client_path = $client_dir . basename($product_path);
                    copy($product_path, $client_path);
                }
            }
            
            // Update product in database
            $stmt = $conn->prepare("UPDATE products SET product_name = ?, price = ?, " . 
                                 ($product_path ? "product_path = ?, " : "") . 
                                 "description = ?, category = ? WHERE product_id = ? AND stall_id = ?");
            
            if ($product_path) {
                $stmt->bind_param("sdsssii", $product_name, $price, $product_path, $description, $category, $product_id, $stall_id);
            } else {
                $stmt->bind_param("sdssii", $product_name, $price, $description, $category, $product_id, $stall_id);
            }
            
            $stmt->execute();
            $stmt->close();
            $conn->close();
            
            $_SESSION['success_message'] = "Product updated successfully!";
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Error updating product: " . $e->getMessage();
        }
    } else {
        // Handle adding new products
        $conn = new mysqli("localhost", "root", "", "ezorderdb");
        foreach ($_POST['product_name'] as $i => $product_name) {
            $price = $_POST['price'][$i];
            $description = $_POST['description'][$i];
            $category = $_POST['category'][$i];

            // Handle product image upload
            $product_path = "";
            if (isset($_FILES['product_path']['name'][$i]) && $_FILES['product_path']['error'][$i] == UPLOAD_ERR_OK) {
                $upload_dir = "uploads/products/";
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $filename = basename($_FILES['product_path']['name'][$i]);
                $product_path = $upload_dir . uniqid() . "_" . $filename;
                move_uploaded_file($_FILES['product_path']['tmp_name'][$i], $product_path);

                // Copy to client side
                if ($product_path && file_exists($product_path)) {
                    $client_dir = "../../SOE_CLIENT_SIDE-main/SOE_CLIENT/assets/images/";
                    if (!is_dir($client_dir)) {
                        mkdir($client_dir, 0777, true);
                    }
                    $client_path = $client_dir . basename($product_path);
                    copy($product_path, $client_path);
                }
            }

            $stmt = $conn->prepare("INSERT INTO products (product_name, stall_id, price, product_path, description, category) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sidsss", $product_name, $stall_id, $price, $product_path, $description, $category);
            $stmt->execute();
            $stmt->close();
            
            $_SESSION['success_message'] = "Product added successfully!";
        }
        $conn->close();
    }
    
    // Redirect back to the products page
    echo "<script>window.location.href = 'manage_products.php';</script>";
    exit();
}

if (isset($_GET['publish']) && is_numeric($_GET['publish'])) {
    $product_id = intval($_GET['publish']);
    $conn = new mysqli("localhost", "root", "", "ezorderdb");
    $stmt = $conn->prepare("UPDATE products SET is_featured = 1 WHERE product_id = ? AND stall_id = ?");
    $stmt->bind_param("ii", $product_id, $_SESSION['stall_id']);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    $_SESSION['success_message'] = "Product published (featured) successfully!";
    echo "<script>window.location.href = 'manage_products.php';</script>";
    exit();
}
if (isset($_GET['unpublish']) && is_numeric($_GET['unpublish'])) {
    $product_id = intval($_GET['unpublish']);
    $conn = new mysqli("localhost", "root", "", "ezorderdb");
    $stmt = $conn->prepare("UPDATE products SET is_featured = 0 WHERE product_id = ? AND stall_id = ?");
    $stmt->bind_param("ii", $product_id, $_SESSION['stall_id']);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    $_SESSION['success_message'] = "Product unpublished successfully!";
    echo "<script>window.location.href = 'manage_products.php';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Products - EZ-ORDER</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles/manage_products.css">
    <style>
        /* Toast Notification Styles */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 15px 25px;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            display: flex;
            align-items: center;
            transform: translateX(120%);
            transition: transform 0.3s ease-in-out;
        }
        .toast.show {
            transform: translateX(0);
        }
        .toast i {
            margin-right: 10px;
            font-size: 20px;
        }
        .toast.success { background-color: #4CAF50; }
        .toast.error { background-color: #f44336; }
        .toast.info { background-color: #2196F3; }
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
                <a href="dashboard.php" class="nav-item">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="manage_orders.php" class="nav-item">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Manage Transactions</span>
                </a>
                <a href="manage_products.php" class="nav-item active">
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
    <h1>Manage Products</h1>
            </div>

    <div class="product-form-section">
                <div class="section-header">
                    <h2><i class="fas fa-plus-circle"></i> <span id="form-title">Add New Product</span></h2>
                    <input type="hidden" name="product_id" id="product_id" value="">
                </div>
                <form method="post" enctype="multipart/form-data" class="product-form" id="productForm">
                    <input type="hidden" name="edit_mode" id="edit_mode" value="0">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="product_name">
                                <i class="fas fa-tag"></i>
                                Product Name
                            </label>
                            <input type="text" name="product_name[]" id="product_name" placeholder="Enter product name" required>
                        </div>
                        <div class="form-group">
                            <label for="price">
                                <i class="fas fa-money-bill"></i>
                                Price
                            </label>
                            <div class="price-input">
                                <span class="currency">₱</span>
                                <input type="number" name="price[]" id="price" step="0.01" placeholder="0.00" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="category">
                                <i class="fas fa-folder"></i>
                                Category
                    </label>
                            <select name="category[]" id="category" required>
                        <option value="">Select Category</option>
                                <option value="Food">Food</option>
                                <option value="Beverage">Beverage</option>
                                <option value="Snack">Snack</option>
                                <option value="Other">Other</option>
                    </select>
                </div>
                        <div class="form-group upload-group">
                            <label for="product_image">
                                <i class="fas fa-image"></i>
                                Product Image
                            </label>
                            <div class="upload-area" id="uploadArea">
                                <input type="file" name="product_path[]" id="product_image" class="file-input" accept="image/*" required>
                                <label for="product_image" class="upload-label">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Choose a file or drag it here</span>
                                </label>
                                <div class="preview-container" style="display: none;">
                                    <img id="preview" src="#" alt="Preview">
                                    <button type="button" class="remove-preview" onclick="removePreview()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label for="description">
                            <i class="fas fa-align-left"></i>
                            Description
                        </label>
                        <textarea name="description[]" id="description" placeholder="Enter product description" required></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="update_product" id="updateBtn" class="btn-primary" style="display: none;">
                            <i class="fas fa-save"></i>
                            Update Product
                        </button>
                        <button type="submit" name="add_products" id="addBtn" class="btn-primary">
                            <i class="fas fa-plus"></i>
                            Add Product
                        </button>
                        <button type="button" class="btn-secondary" onclick="addProductForm()">
                            <i class="fas fa-plus-circle"></i>
                            Add Another
                        </button>
            </div>
        </form>
    </div>

            <div class="products-section">
    <h2>Your Products</h2>
                <div class="products-table">
    <table>
                        <thead>
        <tr>
            <th>Product Name</th>
            <th>Price</th>
            <th>Image</th>
            <th>Description</th>
            <th>Category</th>
            <th>Available</th>
            <th>Actions</th>
        </tr>
                        </thead>
                        <tbody>
        <?php
            $conn = new mysqli("localhost", "root", "", "ezorderdb");
                            $stmt = $conn->prepare("SELECT * FROM products WHERE stall_id = ?");
                            $stmt->bind_param("i", $_SESSION['stall_id']);
            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
                                echo "<td>₱" . number_format($row['price'], 2) . "</td>";
                                echo "<td><img src='" . htmlspecialchars($row['product_path']) . "' alt='" . htmlspecialchars($row['product_name']) . "' class='product-image'></td>";
                                echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                                echo "<td>" . ($row['is_featured'] ? 'Yes' : 'No') . "</td>";
                                echo "<td class='product-actions'>";
                                echo "<button class='edit-btn' onclick='editProduct(" . json_encode($row) . ")'><i class='fas fa-edit'></i> Edit</button>";
                                echo "<button class='delete-btn' onclick='deleteProduct(" . $row['product_id'] . ", \"" . addslashes($row['product_name']) . "\")'><i class='fas fa-trash'></i> Delete</button>";
                                if ($row['is_featured']) {
                                    echo "<button class='unpublish-btn' onclick='unpublishProduct(" . $row['product_id'] . ")'><i class='fas fa-eye-slash'></i> Unpublish</button>";
                    } else {
                                    echo "<button class='edit-btn' onclick='publishProduct(" . $row['product_id'] . ")'><i class='fas fa-eye'></i> Publish</button>";
                    }
                    echo "</td>";
                    echo "</tr>";
            }
            $stmt->close();
            $conn->close();
        ?>
                        </tbody>
    </table>
</div>
            </div>
        </main>
    </div>
    
    <!-- Toast Notification Container -->
    <div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 1000;">
        <!-- Toast will be inserted here by JavaScript -->
    </div>

    <script>
        // Toast Notification Function
        function showToast(message, type = 'success') {
            // Create new toast element
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = `
                <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'check-circle'}"></i>
                <span>${message}</span>
            `;
            
            // Add to container
            const container = document.getElementById('toast-container');
            container.appendChild(toast);
            
            // Trigger reflow to enable animation
            void toast.offsetWidth;
            
            // Show the toast
            toast.classList.add('show');
            
            // Hide and remove the toast after 5 seconds
            setTimeout(() => {
                toast.classList.remove('show');
                // Wait for the hide animation to complete before removing
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 3000);
        }
        
        // Show any existing message on page load
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_SESSION['success_message'])): ?>
                showToast('<?php echo addslashes($_SESSION['success_message']); ?>', 'success');
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
        });
    // Function to handle product editing
    function editProduct(product) {
        console.log('Editing product:', product);
        
        // Set form title and mode
        document.getElementById('form-title').textContent = 'Edit Product';
        document.getElementById('edit_mode').value = '1';
        
        // Fill form with product data
        document.getElementById('product_id').value = product.product_id;
        document.querySelector('input[name="product_name[]"]').value = product.product_name;
        document.querySelector('input[name="price[]"]').value = product.price;
        document.querySelector('select[name="category[]"]').value = product.category;
        document.querySelector('textarea[name="description[]"]').value = product.description;
        
        // Show update button, hide add button
        document.getElementById('updateBtn').style.display = 'inline-block';
        document.getElementById('addBtn').style.display = 'none';
        
        // Make image not required in edit mode
        document.querySelector('input[name="product_path[]"]').required = false;
        
        // Scroll to form
        document.querySelector('.product-form-section').scrollIntoView({ behavior: 'smooth' });
        
        // Show current image preview if exists
        if (product.product_path) {
            const previewContainer = document.querySelector('.preview-container');
            const previewImg = previewContainer.querySelector('img');
            previewImg.src = product.product_path;
            previewContainer.style.display = 'block';
            document.querySelector('.upload-label').style.display = 'none';
        }
    }
    
    // Function to reset form to add new product mode
    function resetForm() {
        document.querySelector('.product-form').reset();
        document.getElementById('form-title').textContent = 'Add New Product';
        document.getElementById('edit_mode').value = '0';
        document.getElementById('updateBtn').style.display = 'none';
        document.getElementById('addBtn').style.display = 'inline-block';
        document.getElementById('product_id').value = '';
        
        // Reset image preview and make it required
        const previewContainer = document.querySelector('.preview-container');
        const fileInput = document.querySelector('input[name="product_path[]"]');
        previewContainer.style.display = 'none';
        document.querySelector('.upload-label').style.display = 'flex';
        fileInput.value = '';
        fileInput.required = true;
    }
    
    function addProductForm() {
        const form = document.querySelector('.product-form');
        const newProduct = document.createElement('div');
        newProduct.className = 'product-entry';
        newProduct.innerHTML = `
            <div class="form-group">
                <label for="product_name">Product Name</label>
                <input type="text" name="product_name[]" required>
            </div>
            <div class="form-group">
                <label for="price">Price</label>
                <input type="number" name="price[]" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description[]" required></textarea>
            </div>
            <div class="form-group">
                <label for="category">Category</label>
        <select name="category[]" required>
            <option value="">Select Category</option>
                    <option value="Food">Food</option>
                    <option value="Beverage">Beverage</option>
                    <option value="Snack">Snack</option>
                    <option value="Other">Other</option>
        </select>
            </div>
            <div class="form-group">
                <label class="choose-image-btn">
                    <i class="fas fa-upload"></i> Choose Image
                    <input type="file" name="product_path[]" accept="image/*" required>
                </label>
            </div>
        `;
        form.insertBefore(newProduct, form.lastElementChild);
    }

    function deleteProduct(productId, productName = '') {
        if (confirm('Are you sure you want to delete ' + (productName ? '\"' + productName + '\"' : 'this product') + '? This action cannot be undone.')) {
            window.location.href = '?delete=' + productId;
        }
    }

    function publishProduct(productId) {
        if (confirm('Are you sure you want to publish this product?')) {
            window.location.href = `manage_products.php?publish=${productId}`;
        }
    }

    function unpublishProduct(productId) {
        if (confirm('Are you sure you want to unpublish this product?')) {
            window.location.href = `manage_products.php?unpublish=${productId}`;
        }
    }

    function editProduct(product) {
        // Implementation for edit modal
        console.log('Edit product:', product);
        // Add your edit modal implementation here
    }

    // Image preview functionality
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('product_image');
    const previewContainer = document.querySelector('.preview-container');
    const preview = document.getElementById('preview');
    const uploadLabel = document.querySelector('.upload-label');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        uploadArea.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, unhighlight, false);
    });

    function highlight() {
        uploadArea.classList.add('highlight');
    }

    function unhighlight() {
        uploadArea.classList.remove('highlight');
    }

    uploadArea.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        fileInput.files = files;
        handleFiles(files);
    }

    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });

    function handleFiles(files) {
        if (files.length > 0) {
            const file = files[0];
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    previewContainer.style.display = 'block';
                    uploadLabel.style.display = 'none';
                }
                reader.readAsDataURL(file);
            }
        }
    }

    function removePreview() {
        fileInput.value = '';
        previewContainer.style.display = 'none';
        uploadLabel.style.display = 'flex';
    }

    // Form validation and submission
    // Handle form submission
    document.getElementById('productForm').addEventListener('submit', function(e) {
        const editMode = document.getElementById('edit_mode').value === '1';
        const requiredFields = this.querySelectorAll('[required]');
        let isValid = true;

        // Only validate required fields if not in edit mode or if field is not empty
        requiredFields.forEach(field => {
            if (editMode && field.name === 'product_path[]') {
                return; // Skip image validation in edit mode if no new image is uploaded
            }
            if (!field.value) {
                isValid = false;
                field.classList.add('error');
            } else {
                field.classList.remove('error');
            }
        });

        if (!isValid) {
            e.preventDefault();a
            alert('Please fill in all required fields');
        }
    });
</script>
</body>
</html>