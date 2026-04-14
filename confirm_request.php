<?php
// Start session to manage login state
session_start();

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if there's a temporary request in the session
if (!isset($_SESSION['temp_request']) || empty($_SESSION['temp_request'])) {
    header("Location: request_move.php");
    exit;
}

// Get the temporary request data
$request = $_SESSION['temp_request'];

// Make sure items is not null
if (!isset($request['items']) || $request['items'] === null) {
    $request['items'] = json_encode(['text' => 'No items specified']);
}

// Process form submission (confirm request)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm'])) {
    // Include database connection
    require_once 'db_connect.php';
    
    // Prepare an insert statement
    $sql = "INSERT INTO moving_requests (user_id, service_type, from_address, to_address, moving_date, moving_time, items, special_instructions, status, estimated_cost, created_at, updated_at) 
            VALUES (:user_id, :service_type, :from_address, :to_address, :moving_date, :moving_time, :items, :special_instructions, 'pending', :estimated_cost, NOW(), NOW())";
    
    if ($stmt = $pdo->prepare($sql)) {
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":user_id", $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->bindParam(":service_type", $request['service_type'], PDO::PARAM_STR);
        $stmt->bindParam(":from_address", $request['from_address'], PDO::PARAM_STR);
        $stmt->bindParam(":to_address", $request['to_address'], PDO::PARAM_STR);
        $stmt->bindParam(":moving_date", $request['moving_date'], PDO::PARAM_STR);
        $stmt->bindParam(":moving_time", $request['moving_time'], PDO::PARAM_STR);
        $stmt->bindParam(":items", $request['items'], PDO::PARAM_STR);
        $stmt->bindParam(":special_instructions", $request['special_instructions'], PDO::PARAM_STR);
        $stmt->bindParam(":estimated_cost", $request['estimated_cost'], PDO::PARAM_STR);
        
        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            // Request saved successfully
            $success_msg = "Your moving request has been submitted successfully! We'll contact you shortly to confirm the details.";
            
            // Clear the temporary request
            unset($_SESSION['temp_request']);
            
            // Redirect to dashboard with success message
            $_SESSION['success_msg'] = $success_msg;
            header("Location: user_dashboard.php");
            exit;
        } else {
            $error_msg = "Oops! Something went wrong. Please try again later.";
        }
        
        // Close statement
        unset($stmt);
    }
    
    // Close connection
    unset($pdo);
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modify'])) {
    // User wants to modify the request, redirect back to request form
    header("Location: request_move.php");
    exit;
}

// Parse items from JSON
$items = json_decode($request['items'], true) ?: [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Your Move - EasyMovers</title>
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
        <h1 class="text-3xl font-bold mb-8 text-center">Confirm Your Move</h1>
        
        <?php if (!empty($error_msg)): ?>
            <div class="bg-red-900 text-red-100 p-4 rounded-lg mb-8">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-2xl mr-3"></i>
                    <p><?php echo $error_msg; ?></p>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="max-w-4xl mx-auto">
            <!-- Price Estimate Card -->
            <div class="bg-primary bg-opacity-10 border border-primary rounded-xl p-6 mb-8">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold text-primary">Price Estimate</h2>
                    <div class="text-3xl font-bold text-primary">$<?php echo number_format($request['estimated_cost'], 2); ?></div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    <div>
                        <p class="text-gray-400 text-sm">Distance</p>
                        <p class="font-medium"><?php echo $request['distance']; ?> km</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Service Type</p>
                        <p class="font-medium"><?php echo htmlspecialchars($request['service_type']); ?></p>
                    </div>
                </div>
                
                <div class="bg-darker bg-opacity-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-300">This is an estimated price based on the information provided. The final price may vary based on actual conditions on the moving day.</p>
                </div>
            </div>
            
            <!-- Request Details -->
            <div class="bg-darker p-8 rounded-xl border border-gray-800 shadow-lg mb-8">
                <h2 class="text-xl font-semibold mb-6">Moving Details</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
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
                </div>
                
                <div class="mb-6">
                    <p class="text-gray-400 text-sm mb-1">Pickup Address</p>
                    <p class="font-medium"><?php echo nl2br(htmlspecialchars($request['from_address'])); ?></p>
                </div>
                
                <div class="mb-6">
                    <p class="text-gray-400 text-sm mb-1">Delivery Address</p>
                    <p class="font-medium"><?php echo nl2br(htmlspecialchars($request['to_address'])); ?></p>
                </div>
                
                <?php if (!empty($request['special_instructions'])): ?>
                <div class="mb-6">
                    <p class="text-gray-400 text-sm mb-1">Special Instructions</p>
                    <p class="font-medium"><?php echo nl2br(htmlspecialchars($request['special_instructions'])); ?></p>
                </div>
                <?php endif; ?>
                
                <!-- Items to Move section -->
                <div class="border-t border-gray-800 my-6"></div>
                
                <h3 class="text-lg font-semibold mb-4">Items to Move</h3>
                
                <?php 
                // Parse items from JSON
                $items_data = json_decode($request['items'], true);
                
                // Check if items is in the new format (with 'text' key)
                if (isset($items_data['text'])) {
                    // Display text format
                    echo '<div class="bg-gray-900 p-4 rounded-lg">';
                    echo '<p>' . nl2br(htmlspecialchars($items_data['text'])) . '</p>';
                    echo '</div>';
                } elseif (is_array($items_data) && !empty($items_data)) {
                    // Display structured format
                    echo '<div class="overflow-x-auto">';
                    echo '<table class="w-full">';
                    echo '<thead class="bg-gray-900">';
                    echo '<tr>';
                    echo '<th class="py-2 px-4 text-left">Item</th>';
                    echo '<th class="py-2 px-4 text-left">Type</th>';
                    echo '<th class="py-2 px-4 text-left">Weight (kg)</th>';
                    echo '<th class="py-2 px-4 text-left">Quantity</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';
                    
                    foreach ($items_data as $item) {
                        echo '<tr class="border-t border-gray-800">';
                        echo '<td class="py-3 px-4">' . htmlspecialchars($item['name']) . '</td>';
                        echo '<td class="py-3 px-4">';
                        
                        if ($item['type'] == 'fragile') {
                            echo '<span class="bg-red-900 text-red-300 py-1 px-2 rounded-full text-xs">Fragile</span>';
                        } elseif ($item['type'] == 'heavy') {
                            echo '<span class="bg-blue-900 text-blue-300 py-1 px-2 rounded-full text-xs">Heavy</span>';
                        } else {
                            echo '<span class="bg-gray-800 text-gray-300 py-1 px-2 rounded-full text-xs">Regular</span>';
                        }
                        
                        echo '</td>';
                        echo '<td class="py-3 px-4">' . htmlspecialchars($item['weight']) . '</td>';
                        echo '<td class="py-3 px-4">' . htmlspecialchars($item['quantity']) . '</td>';
                        echo '</tr>';
                    }
                    
                    echo '</tbody>';
                    echo '</table>';
                    echo '</div>';
                } else {
                    echo '<p class="text-gray-400">No specific items listed.</p>';
                }
                ?>
            </div>
            
            <!-- Confirmation Form -->
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
                    <button type="submit" name="confirm" class="neon-button bg-primary hover:bg-primary-dark text-white font-bold py-3 px-8 rounded-lg flex-1">
                        Confirm Request
                    </button>
                    <button type="submit" name="modify" class="bg-transparent border border-gray-600 hover:border-gray-500 text-white font-bold py-3 px-8 rounded-lg flex-1">
                        Modify Request
                    </button>
                </div>
            </form>
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