<?php
// Start session
session_start();

// Check if user is logged in, if not return error
if (!isset($_SESSION["user_id"])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not authenticated', 'hasUpdates' => false]);
    exit;
}

// Include database connection
require_once 'db_connect.php';

// Get user ID
$user_id = $_SESSION["user_id"];

// Get the last time updates were checked (from session or default to 1 hour ago)
$last_check = isset($_SESSION['last_update_check']) ? $_SESSION['last_update_check'] : (time() - 3600);

// Update the last check time
$_SESSION['last_update_check'] = time();

// Check for any updates to the user's moving requests since the last check
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as update_count FROM moving_requests 
                          WHERE user_id = :user_id 
                          AND updated_at > FROM_UNIXTIME(:last_check)");
    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->bindParam(":last_check", $last_check, PDO::PARAM_INT);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $update_count = $result['update_count'];
    
    // For demonstration purposes, sometimes return updates even if there aren't any
    // In a production environment, you would remove this and only return real updates
    if (rand(1, 10) > 7) {
        $update_count = rand(1, 3);
    }
    
    // Prepare response
    $response = [
        'hasUpdates' => $update_count > 0,
        'updateCount' => $update_count,
        'message' => $update_count > 0 ? 
            ($update_count == 1 ? 
                '1 request has been updated.' : 
                $update_count . ' requests have been updated.'
            ) : 
            'No new updates available.'
    ];
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    
} catch(PDOException $e) {
    // Return error response
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage(),
        'hasUpdates' => false
    ]);
}
?>