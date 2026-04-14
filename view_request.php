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

// Fetch the request details
$sql = "SELECT * FROM moving_requests WHERE id = :id AND user_id = :user_id";
if ($stmt = $pdo->prepare($sql)) {
    $stmt->bindParam(":id", $request_id, PDO::PARAM_INT);
    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        if ($stmt->rowCount() == 1) {
            $request = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            // Request not found or doesn't belong to the user
            header("Location: user_dashboard.php");
            exit;
        }
    } else {
        echo "Oops! Something went wrong. Please try again later.";
        exit;
    }
    
    unset($stmt);
} else {
    echo "Oops! Something went wrong. Please try again later.";
    exit;
}

// Create status timeline based on current status
$status_timeline = [
    'pending' => [
        'completed' => true,
        'date' => date('M d, Y', strtotime($request['created_at'])),
        'description' => 'Your request has been submitted and is awaiting confirmation.'
    ],
    'confirmed' => [
        'completed' => $request['status'] == 'confirmed' || $request['status'] == 'in_progress' || $request['status'] == 'completed',
        'date' => $request['status'] == 'confirmed' || $request['status'] == 'in_progress' || $request['status'] == 'completed' ? date('M d, Y', strtotime($request['updated_at'] ?? $request['created_at'])) : '',
        'description' => 'Your request has been confirmed. Our team will arrive at the scheduled time.'
    ],
    'in_progress' => [
        'completed' => $request['status'] == 'in_progress' || $request['status'] == 'completed',
        'date' => $request['status'] == 'in_progress' || $request['status'] == 'completed' ? date('M d, Y', strtotime($request['updated_at'] ?? $request['created_at'])) : '',
        'description' => 'Our team is currently working on your move.'
    ],
    'completed' => [
        'completed' => $request['status'] == 'completed',
        'date' => $request['status'] == 'completed' ? date('M d, Y', strtotime($request['updated_at'] ?? $request['created_at'])) : '',
        'description' => 'Your move has been completed successfully. Thank you for choosing EasyMovers!'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Request #<?php echo $request['id']; ?> - EasyMovers</title>
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
        
        .timeline-connector {
            position: absolute;
            left: 19px;
            top: 24px;
            bottom: 0;
            width: 2px;
            background-color: #6C63FF;
            z-index: 0;
        }
        
        .status-badge {
            display: inline-block;
            border-radius: 9999px;
            font-weight: 500;
        }
        
        .status-pending {
            background-color: rgba(234, 179, 8, 0.2);
            color: #fbbf24;
            border: 1px solid rgba(234, 179, 8, 0.3);
        }
        
        .status-confirmed {
            background-color: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }
        
        .status-in_progress {
            background-color: rgba(168, 85, 247, 0.2);
            color: #c084fc;
            border: 1px solid rgba(168, 85, 247, 0.3);
        }
        
        .status-completed {
            background-color: rgba(34, 197, 94, 0.2);
            color: #4ade80;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }
        
        .status-cancelled {
            background-color: rgba(239, 68, 68, 0.2);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        
        .timeline-connector {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 20px;
            width: 2px;
            background-color: #374151;
            transform: translateX(-50%);
        }
    </style>
</head>
<body class="bg-dark min-h-screen flex flex-col">
    <!-- Navigation -->
    <nav class="bg-darker bg-opacity-90 py-4 shadow-md">
        <div class="container mx-auto px-6">
            <div class="flex items-center justify-between">
                <a href="index.php" class="text-3xl font-bold text-primary">EasyMovers</a>
                <div class="hidden md:flex space-x-8 items-center">
                    <a href="user_dashboard.php" class="text-gray-300 hover:text-primary transition-colors">Dashboard</a>
                    <a href="request_move.php" class="text-gray-300 hover:text-primary transition-colors">Request a Move</a>
                    <a href="support.php" class="text-gray-300 hover:text-primary transition-colors">Support</a>
                    <a href="logout.php" class="text-gray-300 hover:text-primary transition-colors">Logout</a>
                </div>
                <button class="md:hidden text-white focus:outline-none" id="menu-toggle">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div class="md:hidden hidden bg-darker bg-opacity-95 w-full absolute top-16 left-0 p-4 rounded-b-lg z-50" id="mobile-menu">
            <div class="flex flex-col space-y-4">
                <a href="user_dashboard.php" class="text-gray-300 hover:text-primary transition-colors py-2">Dashboard</a>
                <a href="request_move.php" class="text-gray-300 hover:text-primary transition-colors py-2">Request a Move</a>
                <a href="support.php" class="text-gray-300 hover:text-primary transition-colors py-2">Support</a>
                <a href="logout.php" class="text-gray-300 hover:text-primary transition-colors py-2">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-6 py-12">
        <div class="mb-8">
            <a href="user_dashboard.php" class="text-gray-400 hover:text-primary transition-colors flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
        </div>
        
        <div class="flex flex-col md:flex-row justify-between items-start mb-8">
            <div>
                <h1 class="text-3xl font-bold mb-2">Request #<?php echo $request['id']; ?></h1>
                <p class="text-gray-400">Created on <?php echo date('F d, Y', strtotime($request['created_at'])); ?></p>
            </div>
            <div class="mt-4 md:mt-0">
                <span class="status-badge status-<?php echo $request['status']; ?> text-sm px-4 py-2">
                    <?php 
                        $status_labels = [
                            'pending' => 'Pending',
                            'confirmed' => 'Confirmed',
                            'in_progress' => 'In Progress',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled'
                        ];
                        echo $status_labels[$request['status']] ?? ucfirst($request['status']);
                    ?>
                </span>
                
                <?php if ($request['status'] === 'pending'): ?>
                    <a href="cancel_request.php?id=<?php echo $request['id']; ?>" class="ml-4 text-red-500 hover:text-red-600" onclick="return confirm('Are you sure you want to cancel this request?');">
                        Cancel Request
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Request Details -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
            <!-- Left Column: Service Details -->
            <div class="md:col-span-2">
                <div class="bg-darker p-6 rounded-xl border border-gray-800 shadow-lg mb-8">
                    <h2 class="text-xl font-semibold mb-6">Service Details</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-gray-400 text-sm mb-1">Service Type</p>
                            <p class="font-medium"><?php echo htmlspecialchars($request['service_type']); ?></p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm mb-1">Moving Date</p>
                            <p class="font-medium"><?php echo date('F d, Y', strtotime($request['moving_date'])); ?></p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm mb-1">Preferred Time</p>
                            <p class="font-medium"><?php echo htmlspecialchars($request['moving_time']); ?></p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm mb-1">Estimated Cost</p>
                            <p class="font-medium">$<?php echo number_format($request['estimated_cost'] ?? 0, 2); ?></p>
                        </div>
                    </div>
                    
                    <div class="border-t border-gray-800 my-6"></div>
                    
                    <div class="mb-6">
                        <p class="text-gray-400 text-sm mb-1">Pickup Address</p>
                        <p class="font-medium"><?php echo nl2br(htmlspecialchars($request['from_address'])); ?></p>
                    </div>
                    
                    <div class="mb-6">
                        <p class="text-gray-400 text-sm mb-1">Delivery Address</p>
                        <p class="font-medium"><?php echo nl2br(htmlspecialchars($request['to_address'])); ?></p>
                    </div>
                    
                    <?php if (!empty($request['special_instructions'])): ?>
                    <div>
                        <p class="text-gray-400 text-sm mb-1">Special Instructions</p>
                        <p class="font-medium"><?php echo nl2br(htmlspecialchars($request['special_instructions'])); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Items to Move -->
                <div class="bg-darker p-6 rounded-xl border border-gray-800 shadow-lg mb-8">
                    <h2 class="text-xl font-semibold mb-6">Items to Move</h2>
                    
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-lg mr-4">
                                <i class="fas fa-box text-primary"></i>
                            </div>
                            <div>
                                <p class="font-medium">Items List</p>
                                <p class="text-gray-400"><?php echo nl2br(htmlspecialchars($request['items'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column: Status Timeline and Actions -->
            <div class="md:col-span-1">
                <!-- Status Timeline -->
                <div class="bg-darker p-6 rounded-xl border border-gray-800 shadow-lg mb-8">
                    <h2 class="text-xl font-semibold mb-6">Status Timeline</h2>
                    
                    <div class="relative">
                        <?php if ($request['status'] !== 'cancelled'): ?>
                            <div class="timeline-connector"></div>
                        <?php endif; ?>
                        
                        <div class="space-y-8">
                            <?php foreach ($status_timeline as $status => $data): ?>
                                <div class="flex relative z-10">
                                    <div class="flex-shrink-0">
                                        <?php if ($data['completed']): ?>
                                            <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center">
                                                <i class="fas fa-check text-white"></i>
                                            </div>
                                        <?php else: ?>
                                            <div class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center">
                                                <div class="w-3 h-3 rounded-full bg-gray-600"></div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium">
                                            <?php 
                                                $status_labels = [
                                                    'pending' => 'Request Submitted',
                                                    'confirmed' => 'Request Confirmed',
                                                    'in_progress' => 'Move In Progress',
                                                    'completed' => 'Move Completed'
                                                ];
                                                echo $status_labels[$status] ?? ucfirst($status);
                                            ?>
                                        </h3>
                                        <?php if (!empty($data['date'])): ?>
                                            <p class="text-gray-400 text-sm mb-1"><?php echo $data['date']; ?></p>
                                        <?php endif; ?>
                                        <p class="text-gray-400"><?php echo $data['description']; ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if ($request['status'] === 'cancelled'): ?>
                                <div class="flex relative z-10">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 rounded-full bg-red-500 flex items-center justify-center">
                                            <i class="fas fa-times text-white"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium">Request Cancelled</h3>
                                        <p class="text-gray-400 text-sm mb-1"><?php echo date('M d, Y', strtotime($request['updated_at'] ?? $request['created_at'])); ?></p>
                                        <p class="text-gray-400">This request has been cancelled.</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="bg-darker p-6 rounded-xl border border-gray-800 shadow-lg">
                    <h2 class="text-xl font-semibold mb-6">Actions</h2>
                    
                    <div class="space-y-4">
                        <?php if ($request['status'] === 'pending'): ?>
                            <a href="cancel_request.php?id=<?php echo $request['id']; ?>" class="block w-full bg-red-900 hover:bg-red-800 text-white font-medium py-3 px-4 rounded-lg text-center" onclick="return confirm('Are you sure you want to cancel this request?');">
                                <i class="fas fa-times mr-2"></i> Cancel Request
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($request['status'] === 'confirmed'): ?>
                            <a href="reschedule_request.php?id=<?php echo $request['id']; ?>" class="block w-full bg-primary hover:bg-primary-dark text-white font-medium py-3 px-4 rounded-lg text-center">
                                <i class="fas fa-calendar-alt mr-2"></i> Reschedule
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($request['status'] === 'completed'): ?>
                            <a href="review.php?id=<?php echo $request['id']; ?>" class="block w-full bg-primary hover:bg-primary-dark text-white font-medium py-3 px-4 rounded-lg text-center">
                                <i class="fas fa-star mr-2"></i> Leave a Review
                            </a>
                        <?php endif; ?>
                        
                        <a href="support.php?request_id=<?php echo $request['id']; ?>" class="block w-full bg-gray-800 hover:bg-gray-700 text-white font-medium py-3 px-4 rounded-lg text-center">
                            <i class="fas fa-headset mr-2"></i> Contact Support
                        </a>
                        
                        <a href="user_dashboard.php" class="block w-full bg-transparent border border-gray-700 hover:border-gray-600 text-white font-medium py-3 px-4 rounded-lg text-center">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="bg-darker py-8 mt-12">
        <div class="container mx-auto px-6">
            <p class="text-center text-gray-500">&copy; 2023 EasyMovers. All rights reserved.</p>
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
    </script>
</body>
</html>

<?php
// Helper function to get base rate
function getBaseRate($service_type) {
    // Base rates for different service types
    $rates = [
        'House Shifting' => 200,
        'Furniture Moving' => 150,
        'Office Relocation' => 300,
        'Vehicle Transport' => 250,
        'Custom Order' => 175
    ];
    
    return $rates[$service_type] ?? 150; // Default to 150 if service type not found
}

// Add this to the existing view_request.php file where appropriate
// This would typically go in the right column or after the service details section
?>

<!-- Cost Breakdown Section -->
<div class="bg-darker p-6 rounded-xl border border-gray-800 shadow-lg mb-8">
    <h2 class="text-xl font-semibold mb-6">Cost Breakdown</h2>
    
    <div class="space-y-4">
        <div class="flex justify-between items-center pb-3 border-b border-gray-800">
            <span class="text-gray-400">Base Rate (<?php echo htmlspecialchars($request['service_type']); ?>)</span>
            <span class="font-medium">$<?php echo number_format(getBaseRate($request['service_type']), 2); ?></span>
        </div>
        
        <?php if (isset($request['distance'])): ?>
        <div class="flex justify-between items-center pb-3 border-b border-gray-800">
            <span class="text-gray-400">Distance Cost (<?php echo $request['distance']; ?> km)</span>
            <span class="font-medium">$<?php echo number_format($request['distance'] * 2, 2); ?></span>
        </div>
        <?php endif; ?>
        
        <?php
        $items = json_decode($request['items'] ?? '[]', true);
        $total_weight = 0;
        $fragile_count = 0;
        
        if (is_array($items)) {
            foreach ($items as $item) {
                $total_weight += ($item['weight'] ?? 0) * ($item['quantity'] ?? 1);
                if (isset($item['type']) && $item['type'] == 'fragile') {
                    $fragile_count += $item['quantity'] ?? 1;
                }
            }
        }
        
        $weight_cost = ceil($total_weight / 10) * 5;
        $fragile_cost = $fragile_count * 10;
        ?>
        
        <?php if ($total_weight > 0): ?>
        <div class="flex justify-between items-center pb-3 border-b border-gray-800">
            <span class="text-gray-400">Weight Cost (<?php echo $total_weight; ?> kg)</span>
            <span class="font-medium">$<?php echo number_format($weight_cost, 2); ?></span>
        </div>
        <?php endif; ?>
        
        <?php if ($fragile_count > 0): ?>
        <div class="flex justify-between items-center pb-3 border-b border-gray-800">
            <span class="text-gray-400">Fragile Items (<?php echo $fragile_count; ?> items)</span>
            <span class="font-medium">$<?php echo number_format($fragile_cost, 2); ?></span>
        </div>
        <?php endif; ?>
        
        <div class="flex justify-between items-center pt-2 text-lg font-semibold">
            <span>Total Estimated Cost</span>
            <span class="text-primary">$<?php echo number_format($request['estimated_cost'] ?? 0, 2); ?></span>
        </div>
    </div>
</div>

<!-- Items Details Section -->
<div class="bg-darker p-6 rounded-xl border border-gray-800 shadow-lg mb-8">
    <h2 class="text-xl font-semibold mb-6">Items Details</h2>
    
    <?php
    $items = json_decode($request['items'] ?? '[]', true);
    if (!empty($items) && is_array($items)):
    ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-900">
                    <tr>
                        <th class="py-2 px-4 text-left">Item</th>
                        <th class="py-2 px-4 text-left">Type</th>
                        <th class="py-2 px-4 text-left">Weight (kg)</th>
                        <th class="py-2 px-4 text-left">Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr class="border-t border-gray-800">
                        <td class="py-3 px-4"><?php echo htmlspecialchars($item['name'] ?? 'Unknown Item'); ?></td>
                        <td class="py-3 px-4">
                            <?php if (isset($item['type'])): ?>
                                <?php if ($item['type'] == 'fragile'): ?>
                                    <span class="bg-red-900 text-red-300 py-1 px-2 rounded-full text-xs">Fragile</span>
                                <?php elseif ($item['type'] == 'heavy'): ?>
                                    <span class="bg-blue-900 text-blue-300 py-1 px-2 rounded-full text-xs">Heavy</span>
                                <?php else: ?>
                                    <span class="bg-gray-800 text-gray-300 py-1 px-2 rounded-full text-xs">Regular</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="bg-gray-800 text-gray-300 py-1 px-2 rounded-full text-xs">Regular</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-4"><?php echo htmlspecialchars($item['weight'] ?? 'N/A'); ?></td>
                        <td class="py-3 px-4"><?php echo htmlspecialchars($item['quantity'] ?? 1); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-gray-400">No specific items listed or items are in legacy format.</p>
        
        <?php if (!empty($request['items']) && !is_array(json_decode($request['items'], true))): ?>
            <div class="mt-4 p-4 bg-gray-900 rounded-lg">
                <p class="text-sm"><?php echo nl2br(htmlspecialchars($request['items'])); ?></p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Map Preview (if you want to add this feature) -->
<?php if (isset($request['from_address']) && isset($request['to_address'])): ?>
<div class="bg-darker p-6 rounded-xl border border-gray-800 shadow-lg mb-8">
    <h2 class="text-xl font-semibold mb-6">Route Preview</h2>
    
    <div class="aspect-w-16 aspect-h-9 rounded-lg overflow-hidden">
        <div class="bg-gray-900 flex items-center justify-center h-64">
            <div class="text-center">
                <i class="fas fa-map-marked-alt text-4xl text-gray-600 mb-3"></i>
                <p class="text-gray-400">Route from:</p>
                <p class="font-medium mb-2"><?php echo htmlspecialchars(substr($request['from_address'], 0, 50) . (strlen($request['from_address']) > 50 ? '...' : '')); ?></p>
                <p class="text-gray-400">To:</p>
                <p class="font-medium"><?php echo htmlspecialchars(substr($request['to_address'], 0, 50) . (strlen($request['to_address']) > 50 ? '...' : '')); ?></p>
                
                <?php if (isset($request['distance'])): ?>
                <div class="mt-4">
                    <span class="bg-primary bg-opacity-20 text-primary py-1 px-3 rounded-full">
                        <i class="fas fa-route mr-1"></i> <?php echo $request['distance']; ?> km
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="mt-4 text-center">
        <p class="text-sm text-gray-400">For a detailed route, please use Google Maps or a similar navigation app.</p>
    </div>
</div>
<?php endif; ?>
                