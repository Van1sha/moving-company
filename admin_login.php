<?php
// Start session to manage login state
session_start();

// Check if already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Include database connection
require_once 'db_connect.php';

// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Check if username is empty
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter username.";
    } else {
        $username = trim($_POST["username"]);
    }
    
    // Check if password is empty
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if (empty($username_err) && empty($password_err)) {
        // Prepare a select statement
        $sql = "SELECT id, username, password FROM admins WHERE username = :username";
        
        if ($stmt = $pdo->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            
            // Set parameters
            $param_username = $username;
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Check if username exists, if yes then verify password
                if ($stmt->rowCount() == 1) {
                    if ($row = $stmt->fetch()) {
                        $id = $row["id"];
                        $username = $row["username"];
                        $hashed_password = $row["password"];
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, start a new session
                            session_start();
                            
                            // Store data in session variables
                            $_SESSION["admin_loggedin"] = true;
                            $_SESSION["admin_id"] = $id;
                            $_SESSION["admin_username"] = $username;
                            
                            // Redirect user to dashboard
                            header("location: dashboard.php");
                            exit;
                        } else {
                            // Password is not valid
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else {
                    // Username doesn't exist
                    $login_err = "Invalid username or password.";
                }
            } else {
                $login_err = "Oops! Something went wrong. Please try again later.";
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
    <title>Admin Login - EasyMovers</title>
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
    </style>
</head>
<body class="bg-dark min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="text-center mb-8">
            <a href="index.php" class="text-4xl font-bold text-primary">EasyMovers</a>
            <p class="text-gray-400 mt-2">Admin Panel</p>
        </div>
        
        <div class="bg-darker p-8 rounded-xl shadow-lg border border-gray-800">
            <h2 class="text-2xl font-bold mb-6 text-center">Admin Login</h2>
            
            <?php if (!empty($login_err)): ?>
                <div class="bg-red-900 text-red-100 p-3 rounded-lg mb-4">
                    <?php echo $login_err; ?>
                </div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="mb-4">
                    <label for="username" class="block text-gray-300 mb-2">Username</label>
                    <input type="text" id="username" name="username" class="w-full bg-gray-800 border <?php echo (!empty($username_err)) ? 'border-red-500' : 'border-gray-700'; ?> rounded-lg py-3 px-4 text-white focus:outline-none focus:border-primary" value="<?php echo $username; ?>">
                    <?php if (!empty($username_err)): ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo $username_err; ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="mb-6">
                    <label for="password" class="block text-gray-300 mb-2">Password</label>
                    <input type="password" id="password" name="password" class="w-full bg-gray-800 border <?php echo (!empty($password_err)) ? 'border-red-500' : 'border-gray-700'; ?> rounded-lg py-3 px-4 text-white focus:outline-none focus:border-primary">
                    <?php if (!empty($password_err)): ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo $password_err; ?></p>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="w-full bg-primary hover:bg-primary-dark text-white font-bold py-3 px-4 rounded-lg transition duration-300">
                    Login
                </button>
            </form>
        </div>
        
        <div class="text-center mt-6">
            <a href="index.php" class="text-gray-400 hover:text-primary transition-colors">← Back to Website</a>
        </div>
    </div>
</body>
</html>