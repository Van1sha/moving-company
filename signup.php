<?php
// Start session
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: user_dashboard.php");
    exit;
}

// Include database connection and mail helper
require_once 'db_connect.php';
require_once 'mail_helper.php';

// Define variables and initialize with empty values
$name = $email = $password = $confirm_password = $phone = "";
$name_err = $email_err = $password_err = $confirm_password_err = $phone_err = "";
$success_msg = $error_msg = "";
$show_otp_form = false;

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Check if OTP verification form is submitted
    if (isset($_POST["verify_otp"])) {
        $entered_otp = trim($_POST["otp"]);
        
        if (empty($entered_otp)) {
            $error_msg = "Please enter the OTP.";
        } elseif (!isset($_SESSION["otp"]) || !isset($_SESSION["temp_user_data"])) {
            $error_msg = "OTP session expired. Please try again.";
        } elseif ($entered_otp != $_SESSION["otp"]) {
            $error_msg = "Invalid OTP. Please try again.";
        } else {
            // OTP is valid, proceed with registration
            $user_data = $_SESSION["temp_user_data"];
            
            // Prepare an insert statement
            $sql = "INSERT INTO users (name, email, password, phone) VALUES (:name, :email, :password, :phone)";
             
            if ($stmt = $pdo->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(":name", $param_name, PDO::PARAM_STR);
                $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
                $stmt->bindParam(":password", $param_password, PDO::PARAM_STR);
                $stmt->bindParam(":phone", $param_phone, PDO::PARAM_STR);
                
                // Set parameters
                $param_name = $user_data["name"];
                $param_email = $user_data["email"];
                $param_password = $user_data["password"];
                $param_phone = $user_data["phone"];
                
                // Attempt to execute the prepared statement
                // In the OTP verification section, update the success message and redirect
                if ($stmt->execute()) {
                    // Send welcome email
                    sendWelcomeEmail($param_email, $param_name);
                    
                    // Clear session variables
                    unset($_SESSION["otp"]);
                    unset($_SESSION["temp_user_data"]);
                    
                    // Set success message and redirect to login page
                    $_SESSION["signup_success"] = "Account created successfully! You can now log in with your credentials.";
                    header("location: login.php");
                    exit;
                } else {
                    $error_msg = "Oops! Something went wrong. Please try again later.";
                }

                // Close statement
                unset($stmt);
            }
        }
        
        // Show OTP form again if there was an error
        if (!empty($error_msg)) {
            $show_otp_form = true;
        }
    } else {
        // Regular signup form processing
        
        // Validate name
        if (empty(trim($_POST["name"]))) {
            $name_err = "Please enter your name.";
        } else {
            $name = trim($_POST["name"]);
        }
        
        // Validate email
        if (empty(trim($_POST["email"]))) {
            $email_err = "Please enter your email.";
        } else {
            // Prepare a select statement
            $sql = "SELECT id FROM users WHERE email = :email";
            
            if ($stmt = $pdo->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
                
                // Set parameters
                $param_email = trim($_POST["email"]);
                
                // Attempt to execute the prepared statement
                if ($stmt->execute()) {
                    if ($stmt->rowCount() > 0) {
                        $email_err = "This email is already taken.";
                    } else {
                        $email = trim($_POST["email"]);
                    }
                } else {
                    $error_msg = "Oops! Something went wrong. Please try again later.";
                }

                // Close statement
                unset($stmt);
            }
        }
        
        // Validate phone
        if (empty(trim($_POST["phone"]))) {
            $phone_err = "Please enter your phone number.";
        } else {
            $phone = trim($_POST["phone"]);
        }
        
        // Validate password
        if (empty(trim($_POST["password"]))) {
            $password_err = "Please enter a password.";     
        } elseif (strlen(trim($_POST["password"])) < 6) {
            $password_err = "Password must have at least 6 characters.";
        } else {
            $password = trim($_POST["password"]);
        }
        
        // Validate confirm password
        if (empty(trim($_POST["confirm_password"]))) {
            $confirm_password_err = "Please confirm password.";     
        } else {
            $confirm_password = trim($_POST["confirm_password"]);
            if (empty($password_err) && ($password != $confirm_password)) {
                $confirm_password_err = "Password did not match.";
            }
        }
        
        // Check input errors before proceeding
        if (empty($name_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err) && empty($phone_err)) {
            // Generate OTP
            $otp = sprintf("%06d", mt_rand(100000, 999999));
            
            // Store OTP and user data in session
            $_SESSION["otp"] = $otp;
            $_SESSION["temp_user_data"] = [
                "name" => $name,
                "email" => $email,
                "password" => password_hash($password, PASSWORD_DEFAULT),
                "phone" => $phone
            ];
            
            // Send OTP email
            if (sendOTPEmail($email, $name, $otp)) {
                $success_msg = "We've sent a verification code to your email. Please check and enter the code below.";
                $show_otp_form = true;
            } else {
                // FALLBACK FOR INFINITYFREE: Directly register the user since SMTP port 587 is blocked
                $sql = "INSERT INTO users (name, email, password, phone) VALUES (:name, :email, :password, :phone)";
                if ($stmt = $pdo->prepare($sql)) {
                    $stmt->bindParam(":name", $name, PDO::PARAM_STR);
                    $stmt->bindParam(":email", $email, PDO::PARAM_STR);
                    $stmt->bindParam(":password", $hashed_password, PDO::PARAM_STR);
                    $stmt->bindParam(":phone", $phone, PDO::PARAM_STR);
                    
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    if ($stmt->execute()) {
                        $_SESSION["signup_success"] = "Account created successfully! You can now log in.";
                        header("location: login.php");
                        exit;
                    } else {
                        $error_msg = "Database error. Did you import easymovers.sql into phpMyAdmin?";
                    }
                    unset($stmt);
                } else {
                    $error_msg = "Failed to create account. Please try again.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - EasyMovers</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#6C63FF',
                        secondary: '#FF6584',
                        dark: '#121212',
                        darker: '#0a0a0a',
                    },
                    fontFamily: {
                        poppins: ['Poppins', 'sans-serif'],
                    },
                },
            },
        }
    </script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #121212;
            color: #f8f9fa;
        }
        
        .neon-button {
            position: relative;
            overflow: hidden;
            transition: 0.5s;
            z-index: 1;
        }
        
        .neon-button:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #6C63FF;
            z-index: -1;
            transition: 0.5s;
            opacity: 1;
        }
        
        .neon-button:hover:before {
            opacity: 0;
            transform: scale(0.5, 0.5);
        }
        
        .neon-button:after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #6C63FF;
            z-index: -2;
            transition: 0.5s;
            opacity: 0;
            filter: blur(30px);
        }
        
        .neon-button:hover:after {
            opacity: 1;
            transform: scale(1.2, 1.2);
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #121212 0%, #1e1e1e 100%);
        }
        
        /* OTP input styling */
        .otp-input {
            width: 50px;
            height: 50px;
            text-align: center;
            font-size: 1.5rem;
        }
    </style>
</head>
<body class="bg-dark min-h-screen">
    <!-- Navigation -->
    <nav class="bg-darker bg-opacity-95 backdrop-blur-md py-4">
        <div class="container mx-auto px-6">
            <div class="flex items-center justify-between">
                <a href="index.php" class="text-3xl font-bold text-primary">EasyMovers</a>
                <div class="hidden md:flex space-x-8 items-center">
                    <a href="index.php" class="text-gray-300 hover:text-primary transition-colors">Home</a>
                    <a href="login.php" class="text-gray-300 hover:text-primary transition-colors">Login</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Signup Form -->
    <div class="container mx-auto px-6 py-12">
        <div class="max-w-md mx-auto bg-darker p-8 rounded-xl border border-gray-800 shadow-lg">
            <h2 class="text-3xl font-bold text-center mb-8">Create an Account</h2>
            
            <?php if (!empty($success_msg)): ?>
                <div class="bg-green-900 text-green-100 p-4 rounded-lg mb-6">
                    <?php echo $success_msg; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_msg)): ?>
                <div class="bg-red-900 text-red-100 p-4 rounded-lg mb-6">
                    <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($show_otp_form): ?>
                <!-- OTP Verification Form -->
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="mb-6">
                        <label for="otp" class="block text-gray-300 mb-2">Enter Verification Code</label>
                        <input type="text" id="otp" name="otp" maxlength="6" class="w-full bg-dark border border-gray-700 rounded-lg py-3 px-4 text-white focus:outline-none focus:border-primary">
                        <p class="text-gray-400 mt-2 text-sm">We've sent a 6-digit code to your email address. Please check your inbox (and spam folder).</p>
                    </div>
                    
                    <input type="hidden" name="verify_otp" value="1">
                    
                    <button type="submit" class="neon-button bg-primary hover:bg-primary-dark text-white font-bold py-3 px-8 rounded-lg w-full mb-4">
                        Verify & Create Account
                    </button>
                    
                    <p class="text-center text-gray-400">
                        Didn't receive the code? <a href="signup.php" class="text-primary hover:underline">Try again</a>
                    </p>
                </form>
            <?php else: ?>
                <!-- Regular Signup Form -->
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="mb-6">
                        <label for="name" class="block text-gray-300 mb-2">Full Name</label>
                        <input type="text" id="name" name="name" value="<?php echo $name; ?>" class="w-full bg-dark border <?php echo (!empty($name_err)) ? 'border-red-500' : 'border-gray-700'; ?> rounded-lg py-3 px-4 text-white focus:outline-none focus:border-primary">
                        <span class="text-red-500 text-sm"><?php echo $name_err; ?></span>
                    </div>
                    
                    <div class="mb-6">
                        <label for="email" class="block text-gray-300 mb-2">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo $email; ?>" class="w-full bg-dark border <?php echo (!empty($email_err)) ? 'border-red-500' : 'border-gray-700'; ?> rounded-lg py-3 px-4 text-white focus:outline-none focus:border-primary">
                        <span class="text-red-500 text-sm"><?php echo $email_err; ?></span>
                    </div>
                    
                    <div class="mb-6">
                        <label for="phone" class="block text-gray-300 mb-2">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo $phone; ?>" class="w-full bg-dark border <?php echo (!empty($phone_err)) ? 'border-red-500' : 'border-gray-700'; ?> rounded-lg py-3 px-4 text-white focus:outline-none focus:border-primary">
                        <span class="text-red-500 text-sm"><?php echo $phone_err; ?></span>
                    </div>
                    
                    <div class="mb-6">
                        <label for="password" class="block text-gray-300 mb-2">Password</label>
                        <input type="password" id="password" name="password" class="w-full bg-dark border <?php echo (!empty($password_err)) ? 'border-red-500' : 'border-gray-700'; ?> rounded-lg py-3 px-4 text-white focus:outline-none focus:border-primary">
                        <span class="text-red-500 text-sm"><?php echo $password_err; ?></span>
                    </div>
                    
                    <div class="mb-6">
                        <label for="confirm_password" class="block text-gray-300 mb-2">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="w-full bg-dark border <?php echo (!empty($confirm_password_err)) ? 'border-red-500' : 'border-gray-700'; ?> rounded-lg py-3 px-4 text-white focus:outline-none focus:border-primary">
                        <span class="text-red-500 text-sm"><?php echo $confirm_password_err; ?></span>
                    </div>
                    
                    <button type="submit" class="neon-button bg-primary hover:bg-primary-dark text-white font-bold py-3 px-8 rounded-lg w-full mb-4">
                        Sign Up
                    </button>
                    
                    <p class="text-center text-gray-400">
                        Already have an account? <a href="login.php" class="text-primary hover:underline">Login here</a>
                    </p>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="bg-darker py-8 mt-12">
        <div class="container mx-auto px-6">
            <p class="text-center text-gray-500">&copy; 2023 EasyMovers. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>