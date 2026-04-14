<?php
// Start session to manage login state
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if request ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Request ID is required']);
    exit;
}

// Include database connection
require_once 'db_connect.php';

// Get request details
$request_id = $_GET['id'];
$sql = "SELECT mr.*, u.name as user_name, u.email as user_email, u.phone as user_phone 
        FROM moving_requests mr 
        JOIN users u ON mr.user_id = u.id 
        WHERE mr.id = :request_id";

if ($stmt = $pdo->prepare($sql)) {
    $stmt->bindParam(":request_id", $request_id, PDO::PARAM_INT);
    
    if ($stmt->execute() && $request = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Format the HTML for the request details
        $html = '
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="text-gray-400 text-sm">Customer</p>
                    <p class="font-medium">' . htmlspecialchars($request['user_name']) . '</p>
                    <p class="text-sm text-gray-400">' . htmlspecialchars($request['user_email']) . '</p>
                </div>
                <div>
                    <p class="text-gray-400 text-sm">Phone</p>
                    <p class="font-medium">' . htmlspecialchars($request['user_phone'] ?? 'Not provided') . '</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="text-gray-400 text-sm">Service Type</p>
                    <p class="font-medium">' . htmlspecialchars($request['service_type']) . '</p>
                </div>
                <div>
                    <p class="text-gray-400 text-sm">Moving Date & Time</p>
                    <p class="font-medium">' . date('F d, Y', strtotime($request['moving_date'])) . '</p>
                    <p class="text-sm text-gray-400">' . htmlspecialchars($request['moving_time']) . '</p>
                </div>
            </div>
            
            <div class="mb-4">
                <p class="text-gray-400 text-sm">From Address</p>
                <p class="font-medium">' . htmlspecialchars($request['from_address']) . '</p>
            </div>
            
            <div class="mb-4">
                <p class="text-gray-400 text-sm">To Address</p>
                <p class="font-medium">' . htmlspecialchars($request['to_address']) . '</p>
            </div>
            
            <div class="mb-4">
                <p class="text-gray-400 text-sm">Items to Move</p>
                <p class="font-medium">' . nl2br(htmlspecialchars($request['items'])) . '</p>
            </div>';
            
        if (!empty($request['special_instructions'])) {
            $html .= '
            <div class="mb-4">
                <p class="text-gray-400 text-sm">Special Instructions</p>
                <p class="font-medium">' . nl2br(htmlspecialchars($request['special_instructions'])) . '</p>
            </div>';
        }
        
        $html .= '
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="text-gray-400 text-sm">Status</p>
                    <p class="font-medium">' . ucfirst(htmlspecialchars($request['status'])) . '</p>
                </div>
                <div>
                    <p class="text-gray-400 text-sm">Estimated Cost</p>
                    <p class="font-medium">$' . number_format($request['estimated_cost'], 2) . '</p>
                </div>
            </div>';
            
        if (!empty($request['admin_notes'])) {
            $html .= '
            <div class="mb-4">
                <p class="text-gray-400 text-sm">Admin Notes</p>
                <p class="font-medium">' . nl2br(htmlspecialchars($request['admin_notes'])) . '</p>
            </div>';
        }
        
        $html .= '
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="text-gray-400 text-sm">Created At</p>
                    <p class="font-medium">' . date('F d, Y g:i A', strtotime($request['created_at'])) . '</p>
                </div>';
                
        if (!empty($request['updated_at'])) {
            $html .= '
                <div>
                    <p class="text-gray-400 text-sm">Last Updated</p>
                    <p class="font-medium">' . date('F d, Y g:i A', strtotime($request['updated_at'])) . '</p>
                </div>';
        }
        
        $html .= '
            </div>
            
            <div class="mt-6 flex justify-end">
                <button id="update-from-details" class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg" 
                        data-id="' . $request['id'] . '" 
                        data-status="' . $request['status'] . '">
                    Update Status
                </button>
            </div>';
        
        echo json_encode(['success' => true, 'html' => $html]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Request not found']);
    }
    
    unset($stmt);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

// Close connection
unset($pdo);
?>