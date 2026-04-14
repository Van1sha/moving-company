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
$email = $password = "";
$email_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Check if email is empty
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
    }
    
    // Check if password is empty
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if (empty($email_err) && empty($password_err)) {
        // Prepare a select statement
        $sql = "SELECT id, name, email, password FROM users WHERE email = :email";
        
        if ($stmt = $pdo->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
            
            // Set parameters
            $param_email = $email;
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Check if email exists, if yes then verify password
                if ($stmt->rowCount() == 1) {
                    if ($row = $stmt->fetch()) {
                        $id = $row["id"];
                        $name = $row["name"];
                        $email = $row["email"];
                        $hashed_password = $row["password"];
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, start a new session
                            session_start();
                            
                            // Store data in session variables
                            $_SESSION["user_id"] = $id;
                            $_SESSION["user_name"] = $name;
                            $_SESSION["user_email"] = $email;
                            
                            // Send login notification email
                            $login_time = date('Y-m-d H:i:s');
                            $ip_address = $_SERVER['REMOTE_ADDR'];
                            $browser = $_SERVER['HTTP_USER_AGENT'];
                            
                            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                            
                            try {
                                // Server settings
                                $mail->isSMTP();
                                // In the login.php file, find the section where the login notification email is sent
                                $mail->Host       = 'smtp.gmail.com';
                                $mail->SMTPAuth   = true;
                                $mail->Username   = 'easymovers.trust@gmail.com'; // Updated email
                                $mail->Password   = 'kgoi knri xaqr qrea'; // Updated app password
                                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                                $mail->Port       = 587;
                                
                                // Recipients
                                $mail->setFrom('easymovers.trust@gmail.com', 'EasyMovers'); // Updated email
                                $mail->addAddress($email, $name);
                                
                                // Content
                                $mail->isHTML(true);
                                $mail->Subject = 'New Login to Your EasyMovers Account';
                                $mail->Body    = '
                                    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 5px;">
                                        <div style="text-align: center; margin-bottom: 20px;">
                                            <h1 style="color: #6C63FF;">EasyMovers</h1>
                                        </div>
                                        <div style="padding: 20px; background-color: #f9f9f9; border-radius: 5px;">
                                            <h2 style="margin-top: 0;">New Login Alert</h2>
                                            <p>Hello ' . $name . ',</p>
                                            <p>We detected a new login to your EasyMovers account.</p>
                                            <div style="margin: 20px 0; padding: 15px; background-color: #f0f0f0; border-radius: 5px;">
                                                <p><strong>Time:</strong> ' . $login_time . '</p>
                                                <p><strong>IP Address:</strong> ' . $ip_address . '</p>
                                                <p><strong>Browser:</strong> ' . $browser . '</p>
                                            </div>
                                            <p>If this was you, you can safely ignore this email.</p>
                                            <p>If you did not log in, please secure your account by changing your password immediately.</p>
                                        </div>
                                        <div style="margin-top: 20px; text-align: center; color: #666;">
                                            <p>&copy; 2023 EasyMovers. All rights reserved.</p>
                                        </div>
                                    </div>
                                ';
                                
                                $mail->send();
                            } catch (Exception $e) {
                                // Email sending failed, but we'll still allow login
                            }
                            } // Close class_exists check
                            
                            // Redirect user to dashboard
                            header("location: user_dashboard.php");
                        } else {
                            // Password is not valid
                            $login_err = "Invalid email or password.";
                        }
                    }
                } else {
                    // Email doesn't exist
                    $login_err = "Invalid email or password.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            unset($stmt);
        }
    }
    
    // Close connection
    unset($pdo);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EasyMovers</title>
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
                    <a href="signup.php" class="text-gray-300 hover:text-primary transition-colors">Sign Up</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Login Form -->
    <div class="container mx-auto px-6 py-12">
        <div class="max-w-md mx-auto bg-darker p-8 rounded-xl border border-gray-800 shadow-lg">
            <h2 class="text-3xl font-bold text-center mb-8">Welcome Back</h2>
            
            <?php if (isset($_SESSION["signup_success"])): ?>
                <div class="bg-green-900 text-green-100 p-4 rounded-lg mb-6">
                    <?php 
                        echo $_SESSION["signup_success"]; 
                        unset($_SESSION["signup_success"]); // Clear the message after displaying
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($login_err)): ?>
                <div class="bg-red-900 text-red-100 p-4 rounded-lg mb-6">
                    <?php echo $login_err; ?>
                </div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="mb-6">
                    <label for="email" class="block text-gray-300 mb-2">Email Address</label>
                    <input type="email" id="email" name="email" value="<?php echo $email; ?>" class="w-full bg-dark border <?php echo (!empty($email_err)) ? 'border-red-500' : 'border-gray-700'; ?> rounded-lg py-3 px-4 text-white focus:outline-none focus:border-primary">
                    <span class="text-red-500 text-sm"><?php echo $email_err; ?></span>
                </div>
                
                <div class="mb-6">
                    <label for="password" class="block text-gray-300 mb-2">Password</label>
                    <input type="password" id="password" name="password" class="w-full bg-dark border <?php echo (!empty($password_err)) ? 'border-red-500' : 'border-gray-700'; ?> rounded-lg py-3 px-4 text-white focus:outline-none focus:border-primary">
                    <span class="text-red-500 text-sm"><?php echo $password_err; ?></span>
                </div>
                
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <input type="checkbox" id="remember" name="remember" class="mr-2 bg-dark border border-gray-700 rounded">
                        <label for="remember" class="text-gray-300">Remember me</label>
                    </div>
                    <a href="#" class="text-primary hover:underline">Forgot Password?</a>
                </div>
                
                <button type="submit" class="neon-button bg-primary hover:bg-primary-dark text-white font-bold py-3 px-8 rounded-lg w-full mb-4">
                    Login
                </button>
                
                <p class="text-center text-gray-400">
                    Don't have an account? <a href="signup.php" class="text-primary hover:underline">Sign up here</a>
                </p>
            </form>
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