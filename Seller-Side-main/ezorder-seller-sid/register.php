<!DOCTYPE html>
<html>
<head>
    <title>Sign Up on Ezorder</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/register.css">
</head>
<body>
    <div class="container">
        <div class="register-container">
            <div class="logo-section">
                <div class="logo-content">
                    <img src="Picture/logo2.png" alt="EZ-ORDER" class="logo">
                    <h2>EZ-ORDER</h2>
                    <p>Order. Eat. Enjoy</p>
                </div>
            </div>
            <div class="form-section">
                <div class="form-wrapper">
                    <p class="sign-up-text">Sign Up</p>
                    <?php
                    session_start();
                    require_once 'function.php';
                    $register_message = "";

                    if ($_SERVER["REQUEST_METHOD"] == "POST") {
                        // Collect form data
                        $first_name = trim($_POST['first_name']);
                        $middle_name = trim($_POST['middle_name']);
                        $last_name = trim($_POST['last_name']);
                        $email = trim($_POST['email']);
                        $password = $_POST['password'];
                        $confirm_password = $_POST['confirm_password'];
                        $store_name = trim($_POST['store_name']);
                        $contact = trim($_POST['contact']);

                        // File uploads
                        $resume_path = "";
                        $stall_logo_path = "";

                        // Validate passwords
                        if ($password !== $confirm_password) {
                            $register_message = "<div class='alert error'>Passwords do not match!</div>";
                        } else if (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
                            $register_message = "<div class='alert error'>Resume upload failed!</div>";
                        } else {
                            // Handle resume upload
                            $resume_dir = "uploads/resumes/";
                            if (!is_dir($resume_dir)) {
                                mkdir($resume_dir, 0777, true);
                            }
                            $resume_filename = uniqid() . "_" . basename($_FILES['resume']['name']);
                            $resume_path = $resume_dir . $resume_filename;
                            if (!move_uploaded_file($_FILES['resume']['tmp_name'], $resume_path)) {
                                $register_message = "<div class='alert error'>Failed to save resume file.</div>";
                            } else {
                                // Handle store logo upload (optional)
                                if (isset($_FILES['store_logo']) && $_FILES['store_logo']['error'] === UPLOAD_ERR_OK) {
                                    $logo_dir = "uploads/stalls/";
                                    if (!is_dir($logo_dir)) {
                                        mkdir($logo_dir, 0777, true);
                                    }
                                    $logo_filename = uniqid() . "_" . basename($_FILES['store_logo']['name']);
                                    $stall_logo_path = $logo_dir . $logo_filename;
                                    if (!move_uploaded_file($_FILES['store_logo']['tmp_name'], $stall_logo_path)) {
                                        $stall_logo_path = ""; // If upload fails, leave blank
                                    }
                                }

                                // Register seller and stall
                                $success = register_seller_and_stall(
                                    $first_name,
                                    $middle_name,
                                    $last_name,
                                    $email,
                                    $password,
                                    $resume_path,
                                    $store_name,
                                    $stall_logo_path,
                                    $contact
                                );

                                if ($success) {
                                    header("Location: login.php");
                                    exit();
                                } else {
                                    $register_message = "<div class='alert error'>Registration failed. Email might already be in use.</div>";
                                }
                            }
                        }
                    }
                    if ($register_message) {
                        echo $register_message;
                    }
                    ?>
                    <form action="register.php" method="post" class="register-form" enctype="multipart/form-data">
                        <div class="form-group">
                            <input type="text" id="first_name" name="first_name" placeholder="First Name" required>
                        </div>
                        <div class="form-group">
                            <input type="text" id="middle_name" name="middle_name" placeholder="Middle Name (optional)">
                        </div>
                        <div class="form-group">
                            <input type="text" id="last_name" name="last_name" placeholder="Last Name" required>
                        </div>
                        <div class="form-group">
                            <input type="email" id="email" name="email" placeholder="Email" required>
                        </div>
                        <div class="form-group">
                            <input type="password" id="password" name="password" placeholder="Password" required>
                        </div>
                        <div class="form-group">
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                        </div>
                        <div class="form-group">
                            <label class="choose-image-btn" style="display:block;">
                                <i class="fas fa-upload"></i> Upload Resume (PDF)
                                <input type="file" id="resume" name="resume" accept=".pdf" required style="display:none;" onchange="showFileName('resume')">
                            </label>
                            <span id="resume-file-name" style="font-size: 0.9em; color: #555;"></span>
                        </div>
                        <div class="form-group">
                            <input type="text" id="store_name" name="store_name" placeholder="Store Name" required>
                        </div>
                        <div class="form-group">
                            <label class="choose-image-btn" style="display:block;">
                                <i class="fas fa-upload"></i> Upload Store Logo (optional)
                                <input type="file" id="store_logo" name="store_logo" accept="image/*" style="display:none;" onchange="showLogoPreview()">
                            </label>
                            <span id="store-logo-file-name" style="font-size: 0.9em; color: #555;"></span>
                            <div id="store-logo-preview" style="margin-top: 8px;"></div>
                        </div>
                        <div class="form-group">
                            <input type="number" id="contact" name="contact" placeholder="Contact Number" required>
                        </div>
                        <button type="submit" class="sign-up-button">SIGN UP</button>
                        <div class="login-link">
                            <p>Already have an account? <a href="login.php">Sign In</a></p>
                        </div>
                    </form>
                    <script>
                    function showFileName(inputId) {
                        const input = document.getElementById(inputId);
                        const fileNameSpan = document.getElementById(inputId + '-file-name');
                        if (input.files.length > 0) {
                            fileNameSpan.textContent = input.files[0].name;
                        } else {
                            fileNameSpan.textContent = '';
                        }
                    }

                    function showLogoPreview() {
                        const input = document.getElementById('store_logo');
                        const fileNameSpan = document.getElementById('store-logo-file-name');
                        const previewDiv = document.getElementById('store-logo-preview');
                        fileNameSpan.textContent = '';
                        previewDiv.innerHTML = '';
                        if (input.files.length > 0) {
                            fileNameSpan.textContent = input.files[0].name;
                            const file = input.files[0];
                            if (file.type.startsWith('image/')) {
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    const img = document.createElement('img');
                                    img.src = e.target.result;
                                    img.style.maxWidth = '120px';
                                    img.style.maxHeight = '120px';
                                    img.style.display = 'block';
                                    img.style.marginTop = '6px';
                                    previewDiv.appendChild(img);
                                };
                                reader.readAsDataURL(file);
                            }
                        }
                    }
                    </script>
                </div>
            </div>
        </div>
    </div>
</body>
</html>