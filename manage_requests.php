<?php
// Start session to manage login state
session_start();

// Display errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if admin is logged in, if not redirect to login page
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Include database connection
require_once 'db_connect.php';

// Process request status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_id']) && isset($_POST['status'])) {
    $request_id = $_POST['request_id'];
    $status = $_POST['status'];
    $admin_notes = $_POST['admin_notes'] ?? '';
    
    try {
        // First, check if admin_notes column exists
        $check_column = $pdo->query("SHOW COLUMNS FROM moving_requests LIKE 'admin_notes'");
        $column_exists = ($check_column->rowCount() > 0);
        
        if ($column_exists) {
            // If column exists, use it in the update
            $sql = "UPDATE moving_requests SET status = :status, admin_notes = :admin_notes, updated_at = NOW() WHERE id = :request_id";
            
            if ($stmt = $pdo->prepare($sql)) {
                $stmt->bindParam(":status", $status, PDO::PARAM_STR);
                $stmt->bindParam(":admin_notes", $admin_notes, PDO::PARAM_STR);
                $stmt->bindParam(":request_id", $request_id, PDO::PARAM_INT);
                $stmt->execute();
            }
        } else {
            // If column doesn't exist, add it first
            $pdo->exec("ALTER TABLE moving_requests ADD COLUMN admin_notes TEXT NULL AFTER status");
            
            // Then perform the update
            $sql = "UPDATE moving_requests SET status = :status, admin_notes = :admin_notes, updated_at = NOW() WHERE id = :request_id";
            
            if ($stmt = $pdo->prepare($sql)) {
                $stmt->bindParam(":status", $status, PDO::PARAM_STR);
                $stmt->bindParam(":admin_notes", $admin_notes, PDO::PARAM_STR);
                $stmt->bindParam(":request_id", $request_id, PDO::PARAM_INT);
                $stmt->execute();
            }
        }
        
        // Get user email for notification
        $sql = "SELECT u.email, u.name, mr.*, u.id as user_id FROM moving_requests mr 
                JOIN users u ON mr.user_id = u.id 
                WHERE mr.id = :request_id";
        
        if ($stmt2 = $pdo->prepare($sql)) {
            $stmt2->bindParam(":request_id", $request_id, PDO::PARAM_INT);
            
            if ($stmt2->execute() && $row = $stmt2->fetch()) {
                // Send email notification (in a real app)
                $user_email = $row['email'];
                $user_name = $row['name'];
                $moving_date = date('F d, Y', strtotime($row['moving_date']));
                $user_id = $row['user_id'];
                
                // Check if user_notifications table exists, if not create it
                $check_table = $pdo->query("SHOW TABLES LIKE 'user_notifications'");
                if ($check_table->rowCount() == 0) {
                    $pdo->exec("CREATE TABLE user_notifications (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NOT NULL,
                        message TEXT NOT NULL,
                        related_to VARCHAR(50) NOT NULL,
                        related_id INT NOT NULL,
                        is_read BOOLEAN DEFAULT FALSE,
                        created_at DATETIME NOT NULL,
                        INDEX (user_id),
                        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                    )");
                }
                
                // Create a notification for the user dashboard
                $notification_message = "Your moving request for $moving_date has been updated to: " . ucfirst($status);
                if (!empty($admin_notes)) {
                    $notification_message .= ". Admin notes: " . $admin_notes;
                }
                
                $notification_sql = "INSERT INTO user_notifications (user_id, message, related_to, related_id, created_at) 
                                    VALUES (:user_id, :message, 'request', :request_id, NOW())";
                
                if ($notify_stmt = $pdo->prepare($notification_sql)) {
                    $notify_stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
                    $notify_stmt->bindParam(":message", $notification_message, PDO::PARAM_STR);
                    $notify_stmt->bindParam(":request_id", $request_id, PDO::PARAM_INT);
                    $notify_stmt->execute();
                    unset($notify_stmt);
                }
                
                // In a real application, you would send an actual email here
                // For now, we'll just log it
                error_log("Email would be sent to $user_email: Your moving request for $moving_date has been $status");
                
                // Set success message
                $_SESSION['success_msg'] = "Request status updated to '$status' successfully!";
            }
            unset($stmt2);
        }
        
        unset($stmt);
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Database error: " . $e->getMessage();
    }
    
    // Redirect to avoid form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Get all moving requests with user information
$sql = "SELECT mr.*, u.name as user_name, u.email as user_email 
        FROM moving_requests mr 
        JOIN users u ON mr.user_id = u.id 
        ORDER BY mr.created_at DESC";

$requests = [];
if ($stmt = $pdo->prepare($sql)) {
    if ($stmt->execute()) {
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Moving Requests - EasyMovers Admin</title>
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
<body class="bg-dark min-h-screen">
    <!-- Navigation -->
    <nav class="bg-darker bg-opacity-90 py-4 shadow-md">
        <div class="container mx-auto px-6">
            <div class="flex items-center justify-between">
                <a href="index.php" class="text-3xl font-bold text-primary">EasyMovers <span class="text-sm text-gray-400">Admin</span></a>
                <div class="hidden md:flex space-x-8 items-center">
                    <a href="dashboard.php" class="text-gray-300 hover:text-primary transition-colors">Dashboard</a>
                    <a href="manage_requests.php" class="text-primary border-b-2 border-primary transition-colors">Manage Requests</a>
                    <a href="manage_users.php" class="text-gray-300 hover:text-primary transition-colors">Manage Users</a>
                    <a href="admin_logout.php" class="text-gray-300 hover:text-primary transition-colors">Logout</a>
                </div>
                <button class="md:hidden text-white focus:outline-none" id="menu-toggle">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div class="md:hidden hidden bg-darker bg-opacity-95 w-full absolute top-16 left-0 p-4 rounded-b-lg z-50" id="mobile-menu">
            <div class="flex flex-col space-y-4">
                <a href="dashboard.php" class="text-gray-300 hover:text-primary transition-colors py-2">Dashboard</a>
                <a href="manage_requests.php" class="text-primary py-2">Manage Requests</a>
                <a href="manage_users.php" class="text-gray-300 hover:text-primary transition-colors py-2">Manage Users</a>
                <a href="admin_logout.php" class="text-gray-300 hover:text-primary transition-colors py-2">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-6 py-8">
        <h1 class="text-3xl font-bold mb-8">Manage Moving Requests</h1>
        
        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="bg-green-900 text-green-100 p-4 rounded-lg mb-6">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-2xl mr-3"></i>
                    <p><?php echo $_SESSION['success_msg']; ?></p>
                </div>
            </div>
            <?php unset($_SESSION['success_msg']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_msg'])): ?>
            <div class="bg-red-900 text-red-100 p-4 rounded-lg mb-6">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-2xl mr-3"></i>
                    <p><?php echo $_SESSION['error_msg']; ?></p>
                </div>
            </div>
            <?php unset($_SESSION['error_msg']); ?>
        <?php endif; ?>
        
        <!-- Filter Controls -->
        <div class="mb-6 flex flex-wrap gap-4">
            <button class="filter-btn active bg-primary text-white px-4 py-2 rounded-lg" data-filter="all">All Requests</button>
            <button class="filter-btn bg-gray-700 text-white px-4 py-2 rounded-lg" data-filter="pending">Pending</button>
            <button class="filter-btn bg-gray-700 text-white px-4 py-2 rounded-lg" data-filter="confirmed">Confirmed</button>
            <button class="filter-btn bg-gray-700 text-white px-4 py-2 rounded-lg" data-filter="completed">Completed</button>
            <button class="filter-btn bg-gray-700 text-white px-4 py-2 rounded-lg" data-filter="cancelled">Cancelled</button>
        </div>
        
        <!-- Requests Table -->
        <div class="overflow-x-auto bg-darker rounded-xl shadow-lg">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-900 text-left">
                        <th class="py-3 px-4 font-semibold">ID</th>
                        <th class="py-3 px-4 font-semibold">Customer</th>
                        <th class="py-3 px-4 font-semibold">Service Type</th>
                        <th class="py-3 px-4 font-semibold">Moving Date</th>
                        <th class="py-3 px-4 font-semibold">Status</th>
                        <th class="py-3 px-4 font-semibold">Est. Cost</th>
                        <th class="py-3 px-4 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($requests)): ?>
                        <tr>
                            <td colspan="7" class="py-4 px-4 text-center text-gray-400">No moving requests found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($requests as $request): ?>
                            <tr class="border-t border-gray-800 request-row" data-status="<?php echo strtolower($request['status']); ?>">
                                <td class="py-3 px-4">#<?php echo $request['id']; ?></td>
                                <td class="py-3 px-4">
                                    <div><?php echo htmlspecialchars($request['user_name']); ?></div>
                                    <div class="text-sm text-gray-400"><?php echo htmlspecialchars($request['user_email']); ?></div>
                                </td>
                                <td class="py-3 px-4"><?php echo htmlspecialchars($request['service_type']); ?></td>
                                <td class="py-3 px-4">
                                    <?php echo date('M d, Y', strtotime($request['moving_date'])); ?><br>
                                    <span class="text-sm text-gray-400"><?php echo htmlspecialchars($request['moving_time']); ?></span>
                                </td>
                                <td class="py-3 px-4">
                                    <?php
                                    $status_class = '';
                                    switch ($request['status']) {
                                        case 'pending':
                                            $status_class = 'bg-yellow-900 text-yellow-300';
                                            break;
                                        case 'confirmed':
                                            $status_class = 'bg-blue-900 text-blue-300';
                                            break;
                                        case 'completed':
                                            $status_class = 'bg-green-900 text-green-300';
                                            break;
                                        case 'cancelled':
                                            $status_class = 'bg-red-900 text-red-300';
                                            break;
                                        default:
                                            $status_class = 'bg-gray-700 text-gray-300';
                                    }
                                    ?>
                                    <span class="<?php echo $status_class; ?> py-1 px-2 rounded-full text-xs">
                                        <?php echo ucfirst($request['status']); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">$<?php echo number_format($request['estimated_cost'], 2); ?></td>
                                <td class="py-3 px-4">
                                    <div class="flex space-x-2">
                                        <button class="view-details-btn bg-primary hover:bg-primary-dark text-white px-3 py-1 rounded" 
                                                data-id="<?php echo $request['id']; ?>">
                                            View
                                        </button>
                                        <button class="update-status-btn bg-gray-700 hover:bg-gray-600 text-white px-3 py-1 rounded"
                                                data-id="<?php echo $request['id']; ?>"
                                                data-status="<?php echo $request['status']; ?>">
                                            Update
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- View Details Modal -->
    <div id="details-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-darker border border-gray-700 rounded-xl p-6 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">Request Details</h3>
                <button id="close-details-modal" class="text-gray-400 hover:text-white">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="request-details-content">
                <!-- Content will be loaded dynamically -->
                <div class="animate-pulse">
                    <div class="h-4 bg-gray-700 rounded w-3/4 mb-4"></div>
                    <div class="h-4 bg-gray-700 rounded w-1/2 mb-4"></div>
                    <div class="h-4 bg-gray-700 rounded w-5/6 mb-4"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Update Status Modal -->
    <div id="status-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-darker border border-gray-700 rounded-xl p-6 max-w-md w-full">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">Update Request Status</h3>
                <button id="close-status-modal" class="text-gray-400 hover:text-white">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="update-status-form" method="POST">
                <input type="hidden" name="request_id" id="status-request-id">
                
                <div class="mb-4">
                    <label for="status" class="block text-gray-400 mb-2">Status</label>
                    <select name="status" id="status" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-primary">
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="admin_notes" class="block text-gray-400 mb-2">Admin Notes</label>
                    <textarea name="admin_notes" id="admin_notes" rows="4" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-primary" placeholder="Add notes about this request..."></textarea>
                </div>
                
                <div class="flex justify-end">
                    <button type="button" id="cancel-status-update" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg mr-2">Cancel</button>
                    <button type="submit" class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg">Update Status</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="bg-darker py-6 mt-12">
        <div class="container mx-auto px-6">
            <p class="text-center text-gray-500">&copy; 2023 EasyMovers Admin Panel. All rights reserved.</p>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Mobile Menu Toggle
        const menuToggle = document.getElementById('menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');

        menuToggle.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
        
        // Filter Buttons
        const filterButtons = document.querySelectorAll('.filter-btn');
        const requestRows = document.querySelectorAll('.request-row');
        
        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all buttons
                filterButtons.forEach(btn => btn.classList.remove('active', 'bg-primary'));
                filterButtons.forEach(btn => btn.classList.add('bg-gray-700'));
                
                // Add active class to clicked button
                button.classList.add('active', 'bg-primary');
                button.classList.remove('bg-gray-700');
                
                const filter = button.getAttribute('data-filter');
                
                // Show/hide rows based on filter
                requestRows.forEach(row => {
                    if (filter === 'all' || row.getAttribute('data-status') === filter) {
                        row.classList.remove('hidden');
                    } else {
                        row.classList.add('hidden');
                    }
                });
            });
        });
        
        // View Details Modal
        const detailsModal = document.getElementById('details-modal');
        const closeDetailsModal = document.getElementById('close-details-modal');
        const detailsContent = document.getElementById('request-details-content');
        const viewDetailsButtons = document.querySelectorAll('.view-details-btn');
        
        viewDetailsButtons.forEach(button => {
            button.addEventListener('click', () => {
                const requestId = button.getAttribute('data-id');
                
                // Show loading state
                detailsContent.innerHTML = `
                    <div class="animate-pulse">
                        <div class="h-4 bg-gray-700 rounded w-3/4 mb-4"></div>
                        <div class="h-4 bg-gray-700 rounded w-1/2 mb-4"></div>
                        <div class="h-4 bg-gray-700 rounded w-5/6 mb-4"></div>
                    </div>
                `;
                
                // Show modal
                detailsModal.classList.remove('hidden');
                
                // Fetch request details
                fetch(`get_request_details.php?id=${requestId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            detailsContent.innerHTML = data.html;
                            
                            // Add event listener to update button in details
                            const updateFromDetails = document.getElementById('update-from-details');
                            if (updateFromDetails) {
                                updateFromDetails.addEventListener('click', () => {
                                    const id = updateFromDetails.getAttribute('data-id');
                                    const status = updateFromDetails.getAttribute('data-status');
                                    
                                    // Close details modal
                                    detailsModal.classList.add('hidden');
                                    
                                    // Open status modal with the same request
                                    openStatusModal(id, status);
                                });
                            }
                        } else {
                            detailsContent.innerHTML = `<p class="text-red-400">${data.message}</p>`;
                        }
                    })
                    .catch(error => {
                        detailsContent.innerHTML = `<p class="text-red-400">Error loading request details. Please try again.</p>`;
                        console.error('Error:', error);
                    });
            });
        });
        
        closeDetailsModal.addEventListener('click', () => {
            detailsModal.classList.add('hidden');
        });
        
        // Update Status Modal
        const statusModal = document.getElementById('status-modal');
        const closeStatusModal = document.getElementById('close-status-modal');
        const cancelStatusUpdate = document.getElementById('cancel-status-update');
        const updateStatusButtons = document.querySelectorAll('.update-status-btn');
        const statusRequestId = document.getElementById('status-request-id');
        const statusSelect = document.getElementById('status');
        
        function openStatusModal(id, currentStatus) {
            statusRequestId.value = id;
            statusSelect.value = currentStatus;
            statusModal.classList.remove('hidden');
        }
        
        updateStatusButtons.forEach(button => {
            button.addEventListener('click', () => {
                const requestId = button.getAttribute('data-id');
                const currentStatus = button.getAttribute('data-status');
                openStatusModal(requestId, currentStatus);
            });
        });
        
        closeStatusModal.addEventListener('click', () => {
            statusModal.classList.add('hidden');
        });
        
        cancelStatusUpdate.addEventListener('click', () => {
            statusModal.classList.add('hidden');
        });
        
        // Close modals when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target === detailsModal) {
                detailsModal.classList.add('hidden');
            }
            if (e.target === statusModal) {
                statusModal.classList.add('hidden');
            }
        });
    </script>
</body>
</html>