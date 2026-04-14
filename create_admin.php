<?php
// Display all PHP errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once 'db_connect.php';

// Check if admin table exists, if not create it
$sql = "CREATE TABLE IF NOT EXISTS `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($pdo->exec($sql) !== false) {
    echo "Admin table created successfully.<br>";
} else {
    echo "Error creating admin table.<br>";
    exit;
}

// Check if admin user already exists
$sql = "SELECT id FROM admins WHERE username = 'admin'";
$stmt = $pdo->prepare($sql);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    echo "Admin user already exists.<br>";
} else {
    // Create admin user
    $sql = "INSERT INTO admins (username, password) VALUES (:username, :password)";
    $stmt = $pdo->prepare($sql);
    
    // Set parameters
    $param_username = "admin";
    $param_password = password_hash("password", PASSWORD_DEFAULT); // Creates a proper password hash
    
    // Bind parameters
    $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
    $stmt->bindParam(":password", $param_password, PDO::PARAM_STR);
    
    // Execute
    if ($stmt->execute()) {
        echo "Admin user created successfully.<br>";
        echo "Username: admin<br>";
        echo "Password: password<br>";
    } else {
        echo "Error creating admin user.<br>";
    }
}

echo "<p>You can now <a href='admin_login.php'>login as admin</a>.</p>";
echo "<p><strong>Important:</strong> For security reasons, please delete this file after use.</p>";
?>