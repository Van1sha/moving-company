<?php
// Use Environment variables for Docker/local, or fallback to your InfinityFree details
$host = getenv('DB_HOST') ?: 'sql100.infinityfree.com';
$dbname = getenv('DB_NAME') ?: 'if0_41579619_easymovers';
$username = getenv('DB_USER') ?: 'if0_41579619';
$password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'kfvHZFsh8z198vG';
$port = getenv('DB_PORT') ?: '3306';

// Create connection
try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}
?>
