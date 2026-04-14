<?php
// Start session to manage login state
session_start();

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once 'db_connect.php';

// Check if request ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: user_dashboard.php");
    exit;
}

$request_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Check if the request exists and belongs to the user
$sql = "SELECT * FROM moving_requests WHERE id = :id AND user_id = :user_id";
if ($stmt = $pdo->prepare($sql)) {
    $stmt->bindParam(":id", $request_id, PDO::PARAM_INT);
    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        if ($stmt->rowCount() == 1) {
            $request = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check if the request can be cancelled (only pending or confirmed requests)
            if ($request['status'] !== 'pending' && $request['status'] !== 'confirmed') {
                $_SESSION['error_msg'] = "This request cannot be cancelled because it is already " . $request['status'] . ".";
                header("Location: view_request.php?id=" . $request_id);
                exit;
            }
            
            // Update the request status to cancelled
            $update_sql = "UPDATE moving_requests SET status = 'cancelled', updated_at = NOW() WHERE id = :id";
            if ($update_stmt = $pdo->prepare($update_sql)) {
                $update_stmt->bindParam(":id", $request_id, PDO::PARAM_INT);
                
                if ($update_stmt->execute()) {
                    $_SESSION['success_msg'] = "Your request has been cancelled successfully.";
                    header("Location: user_dashboard.php");
                    exit;
                } else {
                    $_SESSION['error_msg'] = "Oops! Something went wrong. Please try again later.";
                    header("Location: view_request.php?id=" . $request_id);
                    exit;
                }
                
                unset($update_stmt);
            }
        } else {
            // Request not found or doesn't belong to the user
            header("Location: user_dashboard.php");
            exit;
        }
    } else {
        $_SESSION['error_msg'] = "Oops! Something went wrong. Please try again later.";
        header("Location: user_dashboard.php");
        exit;
    }
    
    unset($stmt);
} else {
    $_SESSION['error_msg'] = "Oops! Something went wrong. Please try again later.";
    header("Location: user_dashboard.php");
    exit;
}

// Close connection
unset($pdo);
?>