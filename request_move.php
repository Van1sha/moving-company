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

// Define variables and initialize with empty values
$service_type = $from_address = $to_address = $moving_date = $moving_time = $special_instructions = $items = "";
$service_type_err = $from_address_err = $to_address_err = $moving_date_err = $moving_time_err = $items_err = "";
$success_msg = $error_msg = "";

// Helper functions for distance and cost calculation
function getCoordinatesFromAddress($address) {
    // In a real application, you would use Google Maps API or similar
    // For this example, we'll simulate coordinates
    
    // Remove any potentially harmful characters
    $clean_address = htmlspecialchars($address);
    
    // Simulate API call with random coordinates (for demonstration)
    // In production, use: https://maps.googleapis.com/maps/api/geocode/json?address=YOUR_ADDRESS&key=YOUR_API_KEY
    
    // Use a hash of the address to get consistent coordinates for the same address
    $hash = crc32($clean_address);
    srand($hash);
    
    // Random coordinates for demonstration, but consistent for the same address
    return [
        'lat' => (mt_rand(3000, 4500) / 100), // Random latitude between 30 and 45
        'lng' => (mt_rand(-12000, -7000) / 100) // Random longitude between -120 and -70
    ];
}

function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    // Haversine formula to calculate distance between two points
    $earth_radius = 6371; // Radius of the earth in km
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    $distance = $earth_radius * $c; // Distance in km
    
    return round($distance, 2);
}

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

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate service type
    if (empty(trim($_POST["service_type"]))) {
        $service_type_err = "Please select a service type.";
    } else {
        $service_type = trim($_POST["service_type"]);
    }
    
    // Validate from address
    if (empty(trim($_POST["from_address"]))) {
        $from_address_err = "Please enter the pickup address.";
    } else {
        $from_address = trim($_POST["from_address"]);
    }
    
    // Validate to address
    if (empty(trim($_POST["to_address"]))) {
        $to_address_err = "Please enter the delivery address.";
    } else {
        $to_address = trim($_POST["to_address"]);
    }
    
    // Validate moving date
    if (empty(trim($_POST["moving_date"]))) {
        $moving_date_err = "Please select a moving date.";
    } else {
        $moving_date = trim($_POST["moving_date"]);
        // Check if date is in the future
        if (strtotime($moving_date) < strtotime(date('Y-m-d'))) {
            $moving_date_err = "Please select a future date.";
        }
    }
    
    // Validate moving time
    if (empty(trim($_POST["moving_time"]))) {
        $moving_time_err = "Please select a moving time.";
    } else {
        $moving_time = trim($_POST["moving_time"]);
    }
    
    // Validate items - check if at least one item is provided
    if (empty(trim($_POST["items"] ?? ""))) {
        $items_err = "Please add at least one item to be moved.";
    } else {
        $items = trim($_POST["items"]);
    }
    
    // Get special instructions (optional)
    $special_instructions = trim($_POST["special_instructions"] ?? "");
    
    // Check input errors before processing
    if (empty($service_type_err) && empty($from_address_err) && empty($to_address_err) && 
        empty($moving_date_err) && empty($moving_time_err) && empty($items_err)) {
        
        // Get coordinates for distance calculation
        $from_coordinates = getCoordinatesFromAddress($from_address);
        $to_coordinates = getCoordinatesFromAddress($to_address);
        
        if ($from_coordinates && $to_coordinates) {
            // Calculate distance
            $distance = calculateDistance(
                $from_coordinates['lat'], 
                $from_coordinates['lng'], 
                $to_coordinates['lat'], 
                $to_coordinates['lng']
            );
            
            // Calculate estimated cost based on service type, distance and items
            $base_rate = getBaseRate($service_type);
            $distance_cost = $distance * 2; // $2 per km
            
            // Process items and calculate weight-based cost
            $items_array = [];
            $total_weight = 0;
            $fragile_count = 0;
            
            // For this demo, let's estimate:
            // - Each item weighs approximately 20kg
            // - 10% of items are fragile
            $item_lines = explode("\n", $items);
            $item_count = count(array_filter($item_lines, 'trim'));
            $total_weight = $item_count * 20;
            $fragile_count = ceil($item_count * 0.1);
            
            // Weight cost: $5 per 10kg
            $weight_cost = ceil($total_weight / 10) * 5;
            
            // Fragile items cost: $10 per fragile item
            $fragile_cost = $fragile_count * 10;
            
            // Calculate total estimated cost
            $estimated_cost = $base_rate + $distance_cost + $weight_cost + $fragile_cost;
            
            // Round to nearest $5
            $estimated_cost = ceil($estimated_cost / 5) * 5;
            
            // Store items as JSON - this was overwriting the previous items_json
            // $items_json = json_encode($items_array);
            
            // Now you can show the cost to the user and ask for confirmation
            $_SESSION['temp_request'] = [
                'service_type' => $service_type,
                'from_address' => $from_address,
                'to_address' => $to_address,
                'moving_date' => $moving_date,
                'moving_time' => $moving_time,
                'items' => $items_json,
                'special_instructions' => $special_instructions,
                'distance' => $distance,
                'estimated_cost' => $estimated_cost
            ];
            
            // Redirect to confirmation page
            header("Location: confirm_request.php");
            exit;
        } else {
            $error_msg = "Unable to calculate distance between addresses. Please check your addresses and try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request a Move - EasyMovers</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Flatpickr for Date/Time -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
    </style>
</head>
<body class="bg-dark min-h-screen">
    <!-- Navigation -->
    <nav class="bg-darker bg-opacity-90 py-4 shadow-md">
        <div class="container mx-auto px-6">
            <div class="flex items-center justify-between">
                <a href="index.php" class="text-3xl font-bold text-primary">EasyMovers</a>
                <div class="hidden md:flex space-x-8 items-center">
                    <a href="user_dashboard.php" class="text-gray-300 hover:text-primary transition-colors">Dashboard</a>
                    <a href="request_move.php" class="text-primary font-semibold">Request a Move</a>
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
            <a href="index.php" class="text-primary font-semibold py-2">Go to Home</a>
                <a href="request_move.php" class="text-primary font-semibold py-2">Request a Move</a>
                <a href="logout.php" class="text-gray-300 hover:text-primary transition-colors py-2">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-6 py-12">
        <h1 class="text-3xl font-bold mb-8 text-center">Request a Move</h1>
        
        <?php if (!empty($success_msg)): ?>
            <div class="bg-green-900 text-green-100 p-4 rounded-lg mb-8">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-2xl mr-3"></i>
                    <p><?php echo $success_msg; ?></p>
                </div>
                <div class="mt-4 text-center">
                    <a href="user_dashboard.php" class="bg-green-700 hover:bg-green-800 text-white font-medium py-2 px-4 rounded-lg inline-block">
                        View Your Requests
                    </a>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error_msg)): ?>
            <div class="bg-red-900 text-red-100 p-4 rounded-lg mb-8">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-2xl mr-3"></i>
                    <p><?php echo $error_msg; ?></p>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="max-w-3xl mx-auto bg-darker p-8 rounded-xl border border-gray-800 shadow-lg">
            <div class="mb-8">
                <h2 class="text-xl font-semibold mb-2">Moving Request Form</h2>
                <p class="text-gray-400">Fill out the form below to schedule your move. We'll contact you to confirm the details.</p>
            </div>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="service_type" class="block text-gray-300 mb-2">Service Type *</label>
                        <select id="service_type" name="service_type" class="w-full bg-dark border <?php echo (!empty($service_type_err)) ? 'border-red-500' : 'border-gray-700'; ?> rounded-lg py-3 px-4 text-white focus:outline-none focus:border-primary">
                            <option value="" <?php echo empty($service_type) ? 'selected' : ''; ?>>Select a service</option>
                            <option value="House Shifting" <?php echo ($service_type == "House Shifting") ? 'selected' : ''; ?>>House Shifting</option>
                            <option value="Furniture Moving" <?php echo ($service_type == "Furniture Moving") ? 'selected' : ''; ?>>Furniture Moving</option>
                            <option value="Office Relocation" <?php echo ($service_type == "Office Relocation") ? 'selected' : ''; ?>>Office Relocation</option>
                            <option value="Vehicle Transport" <?php echo ($service_type == "Vehicle Transport") ? 'selected' : ''; ?>>Vehicle Transport</option>
                            <option value="Custom Order" <?php echo ($service_type == "Custom Order") ? 'selected' : ''; ?>>Custom Order</option>
                        </select>
                        <?php if (!empty($service_type_err)): ?>
                            <p class="text-red-500 text-sm mt-1"><?php echo $service_type_err; ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label for="moving_date" class="block text-gray-300 mb-2">Moving Date *</label>
                        <input type="date" id="moving_date" name="moving_date" value="<?php echo $moving_date; ?>" class="w-full bg-dark border <?php echo (!empty($moving_date_err)) ? 'border-red-500' : 'border-gray-700'; ?> rounded-lg py-3 px-4 text-white focus:outline-none focus:border-primary">
                        <?php if (!empty($moving_date_err)): ?>
                            <p class="text-red-500 text-sm mt-1"><?php echo $moving_date_err; ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label for="moving_time" class="block text-gray-300 mb-2">Preferred Time *</label>
                    <select id="moving_time" name="moving_time" class="w-full bg-dark border <?php echo (!empty($moving_time_err)) ? 'border-red-500' : 'border-gray-700'; ?> rounded-lg py-3 px-4 text-white focus:outline-none focus:border-primary">
                        <option value="" <?php echo empty($moving_time) ? 'selected' : ''; ?>>Select a time</option>
                        <option value="Morning (8:00 AM - 12:00 PM)" <?php echo ($moving_time == "Morning (8:00 AM - 12:00 PM)") ? 'selected' : ''; ?>>Morning (8:00 AM - 12:00 PM)</option>
                        <option value="Afternoon (12:00 PM - 4:00 PM)" <?php echo ($moving_time == "Afternoon (12:00 PM - 4:00 PM)") ? 'selected' : ''; ?>>Afternoon (12:00 PM - 4:00 PM)</option>
                        <option value="Evening (4:00 PM - 8:00 PM)" <?php echo ($moving_time == "Evening (4:00 PM - 8:00 PM)") ? 'selected' : ''; ?>>Evening (4:00 PM - 8:00 PM)</option>
                    </select>
                    <?php if (!empty($moving_time_err)): ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo $moving_time_err; ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="mb-6">
                    <label for="from_address" class="block text-gray-300 mb-2">Pickup Address *</label>
                    <textarea id="from_address" name="from_address" rows="2" class="w-full bg-dark border <?php echo (!empty($from_address_err)) ? 'border-red-500' : 'border-gray-700'; ?> rounded-lg py-3 px-4 text-white focus:outline-none focus:border-primary"><?php echo $from_address; ?></textarea>
                    <?php if (!empty($from_address_err)): ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo $from_address_err; ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="mb-6">
                    <label for="to_address" class="block text-gray-300 mb-2">Delivery Address *</label>
                    <textarea id="to_address" name="to_address" rows="2" class="w-full bg-dark border <?php echo (!empty($to_address_err)) ? 'border-red-500' : 'border-gray-700'; ?> rounded-lg py-3 px-4 text-white focus:outline-none focus:border-primary"><?php echo $to_address; ?></textarea>
                    <?php if (!empty($to_address_err)): ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo $to_address_err; ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="mb-6">
                    <label for="items" class="block text-gray-300 mb-2">Items to be Moved *</label>
                    <textarea id="items" name="items" rows="4" class="w-full bg-dark border <?php echo (!empty($items_err)) ? 'border-red-500' : 'border-gray-700'; ?> rounded-lg py-3 px-4 text-white focus:outline-none focus:border-primary" placeholder="List the major items you need moved (e.g., furniture, appliances, boxes)"><?php echo $items; ?></textarea>
                    <?php if (!empty($items_err)): ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo $items_err; ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="mb-8">
                    <label for="special_instructions" class="block text-gray-300 mb-2">Special Instructions (Optional)</label>
                    <textarea id="special_instructions" name="special_instructions" rows="3" class="w-full bg-dark border border-gray-700 rounded-lg py-3 px-4 text-white focus:outline-none focus:border-primary" placeholder="Any special requirements or instructions for the movers"><?php echo $special_instructions; ?></textarea>
                </div>
                
                <div class="bg-gray-900 p-4 rounded-lg mb-6">
                    <h3 class="font-semibold mb-2">Important Information</h3>
                    <ul class="text-gray-400 text-sm list-disc list-inside space-y-1">
                        <li>A team member will contact you within 24 hours to confirm your request.</li>
                        <li>You can cancel your request up to 48 hours before the scheduled time.</li>
                        <li>Final pricing will be provided after confirmation of all details.</li>
                    </ul>
                </div>
                
                <button type="submit" class="neon-button bg-primary hover:bg-primary-dark text-white font-bold py-3 px-8 rounded-lg w-full">
                    Submit Request
                </button>
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
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // Mobile Menu Toggle
        const menuToggle = document.getElementById('menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');

        menuToggle.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
        
        // Initialize date picker
        flatpickr("#moving_date", {
            minDate: "today",
            dateFormat: "Y-m-d",
            disableMobile: "true"
        });
        
        // Form validation
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Get form fields
            const serviceType = document.getElementById('service_type');
            const fromAddress = document.getElementById('from_address');
            const toAddress = document.getElementById('to_address');
            const movingDate = document.getElementById('moving_date');
            const movingTime = document.getElementById('moving_time');
            const items = document.getElementById('items');
            
            // Reset error styles
            [serviceType, fromAddress, toAddress, movingDate, movingTime, items].forEach(field => {
                field.classList.remove('border-red-500');
                field.classList.add('border-gray-700');
            });
            
            // Validate service type
            if (serviceType.value === '') {
                serviceType.classList.remove('border-gray-700');
                serviceType.classList.add('border-red-500');
                isValid = false;
            }
            
            // Validate from address
            if (fromAddress.value.trim() === '') {
                fromAddress.classList.remove('border-gray-700');
                fromAddress.classList.add('border-red-500');
                isValid = false;
            }
            
            // Validate to address
            if (toAddress.value.trim() === '') {
                toAddress.classList.remove('border-gray-700');
                toAddress.classList.add('border-red-500');
                isValid = false;
            }
            
            // Validate moving date
            if (movingDate.value === '') {
                movingDate.classList.remove('border-gray-700');
                movingDate.classList.add('border-red-500');
                isValid = false;
            }
            
            // Validate moving time
            if (movingTime.value === '') {
                movingTime.classList.remove('border-gray-700');
                movingTime.classList.add('border-red-500');
                isValid = false;
            }
            
            // Validate items
            if (items.value.trim() === '') {
                items.classList.remove('border-gray-700');
                items.classList.add('border-red-500');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                window.scrollTo(0, 0);
            }
        });
    </script>
</body>
</html>
