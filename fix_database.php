<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once 'db_connect.php';

echo "<h2>Database Repair Tool</h2>";

// Check if moving_requests table has the necessary columns
try {
    // Check if table exists
    $sql = "SHOW TABLES LIKE 'moving_requests'";
    $result = $pdo->query($sql);
    
    if ($result->rowCount() > 0) {
        echo "✓ moving_requests table exists<br>";
        
        // Check columns
        $sql = "SHOW COLUMNS FROM moving_requests";
        $result = $pdo->query($sql);
        $columns = $result->fetchAll(PDO::FETCH_COLUMN);
        
        $required_columns = [
            'id', 'user_id', 'service_type', 'from_address', 'to_address', 
            'moving_date', 'moving_time', 'items', 'special_instructions', 
            'status', 'estimated_cost', 'admin_notes', 'created_at', 'updated_at'
        ];
        
        $missing_columns = array_diff($required_columns, $columns);
        
        if (!empty($missing_columns)) {
            echo "! Missing columns in moving_requests: " . implode(', ', $missing_columns) . "<br>";
            
            // Add missing columns
            foreach ($missing_columns as $column) {
                $sql = "";
                switch ($column) {
                    case 'admin_notes':
                        $sql = "ALTER TABLE moving_requests ADD COLUMN admin_notes TEXT NULL AFTER estimated_cost";
                        break;
                    case 'status':
                        $sql = "ALTER TABLE moving_requests ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER special_instructions";
                        break;
                    case 'updated_at':
                        $sql = "ALTER TABLE moving_requests ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
                        break;
                    // Add more cases for other columns if needed
                }
                
                if (!empty($sql)) {
                    try {
                        $pdo->exec($sql);
                        echo "✓ Added column '$column' to moving_requests<br>";
                    } catch (PDOException $e) {
                        echo "! Error adding column '$column': " . $e->getMessage() . "<br>";
                    }
                }
            }
        } else {
            echo "✓ All required columns exist in moving_requests<br>";
        }
    } else {
        echo "! moving_requests table does not exist. Creating...<br>";
        
        // Create the table
        $sql = "CREATE TABLE `moving_requests` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `service_type` varchar(50) NOT NULL,
          `from_address` text NOT NULL,
          `to_address` text NOT NULL,
          `moving_date` date NOT NULL,
          `moving_time` varchar(50) NOT NULL,
          `items` text NOT NULL,
          `special_instructions` text,
          `status` varchar(20) NOT NULL DEFAULT 'pending',
          `estimated_cost` decimal(10,2) NOT NULL,
          `admin_notes` text,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `user_id` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        try {
            $pdo->exec($sql);
            echo "✓ Created moving_requests table<br>";
        } catch (PDOException $e) {
            echo "! Error creating moving_requests table: " . $e->getMessage() . "<br>";
        }
    }
    
    // Check if admins table exists and has correct structure
    $sql = "SHOW TABLES LIKE 'admins'";
    $result = $pdo->query($sql);
    
    if ($result->rowCount() > 0) {
        echo "✓ admins table exists<br>";
        
        // Check if admin user exists
        $sql = "SELECT id FROM admins WHERE username = 'admin'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        
        if ($stmt->rowCount() == 0) {
            echo "! Admin user does not exist. Creating...<br>";
            
            // Create admin user
            $sql = "INSERT INTO admins (username, password) VALUES (:username, :password)";
            $stmt = $pdo->prepare($sql);
            
            // Set parameters
            $param_username = "admin";
            $param_password = password_hash("password", PASSWORD_DEFAULT);
            
            // Bind parameters
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            $stmt->bindParam(":password", $param_password, PDO::PARAM_STR);
            
            // Execute
            if ($stmt->execute()) {
                echo "✓ Admin user created successfully<br>";
                echo "Username: admin<br>";
                echo "Password: password<br>";
            } else {
                echo "! Error creating admin user<br>";
            }
        } else {
            echo "✓ Admin user exists<br>";
        }
    } else {
        echo "! admins table does not exist. Creating...<br>";
        
        // Create the table
        $sql = "CREATE TABLE `admins` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `username` varchar(50) NOT NULL,
          `password` varchar(255) NOT NULL,
          `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `username` (`username`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        try {
            $pdo->exec($sql);
            echo "✓ Created admins table<br>";
            
            // Create admin user
            $sql = "INSERT INTO admins (username, password) VALUES (:username, :password)";
            $stmt = $pdo->prepare($sql);
            
            // Set parameters
            $param_username = "admin";
            $param_password = password_hash("password", PASSWORD_DEFAULT);
            
            // Bind parameters
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            $stmt->bindParam(":password", $param_password, PDO::PARAM_STR);
            
            // Execute
            if ($stmt->execute()) {
                echo "✓ Admin user created successfully<br>";
                echo "Username: admin<br>";
                echo "Password: password<br>";
            } else {
                echo "! Error creating admin user<br>";
            }
        } catch (PDOException $e) {
            echo "! Error creating admins table: " . $e->getMessage() . "<br>";
        }
    }
    
    // Check if users table exists
    $sql = "SHOW TABLES LIKE 'users'";
    $result = $pdo->query($sql);
    
    if ($result->rowCount() > 0) {
        echo "✓ users table exists<br>";
    } else {
        echo "! users table does not exist. Creating...<br>";
        
        // Create the table
        $sql = "CREATE TABLE `users` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(100) NOT NULL,
          `email` varchar(100) NOT NULL,
          `password` varchar(255) NOT NULL,
          `phone` varchar(20) DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        try {
            $pdo->exec($sql);
            echo "✓ Created users table<br>";
        } catch (PDOException $e) {
            echo "! Error creating users table: " . $e->getMessage() . "<br>";
        }
    }
    
    echo "<h3>Database check complete!</h3>";
    echo "<p>You can now <a href='admin_login.php'>login as admin</a> with:</p>";
    echo "<p>Username: admin<br>Password: password</p>";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>