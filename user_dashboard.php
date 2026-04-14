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

// Get user information
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? '';

// Get user's moving requests
$sql = "SELECT * FROM moving_requests WHERE user_id = :user_id ORDER BY created_at DESC";
$requests = [];

if ($stmt = $pdo->prepare($sql)) {
    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($stmt);
}

// Get user's notifications
$sql = "SELECT * FROM user_notifications WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 10";
$notifications = [];

if ($stmt = $pdo->prepare($sql)) {
    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($stmt);
}

// Mark notifications as read
if (!empty($notifications)) {
    $sql = "UPDATE user_notifications SET is_read = TRUE WHERE user_id = :user_id AND is_read = FALSE";
    if ($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->execute();
        unset($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - EasyMovers</title>
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
                <a href="index.php" class="text-3xl font-bold text-primary">EasyMovers</a>
                <div class="hidden md:flex space-x-8 items-center">
                    <a href="user_dashboard.php" class="text-primary border-b-2 border-primary transition-colors">Dashboard</a>
                    <a href="request_move.php" class="text-gray-300 hover:text-primary transition-colors">Request a Move</a>
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
                <a href="user_dashboard.php" class="text-primary py-2">Dashboard</a>
                <a href="request_move.php" class="text-gray-300 hover:text-primary transition-colors py-2">Request a Move</a>
                <a href="profile.php" class="text-gray-300 hover:text-primary transition-colors py-2">My Profile</a>
                <a href="logout.php" class="text-gray-300 hover:text-primary transition-colors py-2">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-6 py-8">
        <h1 class="text-3xl font-bold mb-8">Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>

        <!-- Notifications Section -->
        <?php if (!empty($notifications)): ?>
        <div class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">Recent Notifications</h2>
            <div class="bg-darker rounded-xl p-4">
                <?php foreach ($notifications as $notification): ?>
                <div class="border-b border-gray-800 py-3 last:border-b-0">
                    <p class="text-gray-300"><?php echo htmlspecialchars($notification['message']); ?></p>
                    <p class="text-sm text-gray-500 mt-1"><?php echo date('M d, Y h:i A', strtotime($notification['created_at'])); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">Quick Actions</h2>
            <!-- <div class="grid grid-cols-1 md:grid-cols-3 gap-4"> -->
                 <div class="w-full">
                <a href="request_move.php" class="bg-darker hover:bg-gray-800 p-6 rounded-xl border border-gray-800 flex flex-col items-center justify-center text-center transition duration-300">
                    <div class="p-3 rounded-full bg-primary bg-opacity-20 text-primary mb-3">
                        <i class="fas fa-truck-moving text-2xl"></i>
                    </div>
                    <h3 class="font-semibold mb-2">Request a Move</h3>
                    <p class="text-sm text-gray-400">Schedule your next moving service</p>
                </a>

                <!-- <a href="profile.php" class="bg-darker hover:bg-gray-800 p-6 rounded-xl border border-gray-800 flex flex-col items-center justify-center text-center transition duration-300">
                    <div class="p-3 rounded-full bg-blue-900 bg-opacity-20 text-blue-500 mb-3">
                        <i class="fas fa-user-edit text-2xl"></i>
                    </div>
                    <h3 class="font-semibold mb-2">Update Profile</h3>
                    <p class="text-sm text-gray-400">Manage your personal information</p>
                </a> -->

                <!-- <a href="#contact" class="bg-darker hover:bg-gray-800 p-6 rounded-xl border border-gray-800 flex flex-col items-center justify-center text-center transition duration-300">
                    <div class="p-3 rounded-full bg-green-900 bg-opacity-20 text-green-500 mb-3">
                        <i class="fas fa-headset text-2xl"></i>
                    </div>
                    <h3 class="font-semibold mb-2">Contact Support</h3>
                    <p class="text-sm text-gray-400">Need help? Reach out to our team</p>
                </a> -->
            </div>
        </div>

        <!-- Moving Requests Section -->
        <div class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">Your Moving Requests</h2>

            <?php if (empty($requests)): ?>
            <div class="bg-darker rounded-xl p-6 text-center">
                <p class="text-gray-400 mb-4">You don't have any moving requests yet.</p>
                <a href="request_move.php" class="bg-primary hover:bg-primary-dark text-white px-6 py-2 rounded-lg transition duration-300">Request a Move</a>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto bg-darker rounded-xl">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-900 text-left">
                            <th class="py-3 px-4 font-semibold">ID</th>
                            <th class="py-3 px-4 font-semibold">Service Type</th>
                            <th class="py-3 px-4 font-semibold">Moving Date</th>
                            <th class="py-3 px-4 font-semibold">Status</th>
                            <th class="py-3 px-4 font-semibold">Est. Cost</th>
                            <th class="py-3 px-4 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request): ?>
                        <tr class="border-t border-gray-800">
                            <td class="py-3 px-4">#<?php echo $request['id']; ?></td>
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
                                <a href="view_request.php?id=<?php echo $request['id']; ?>" class="bg-primary hover:bg-primary-dark text-white px-3 py-1 rounded">
                                    View Details
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-darker py-6 mt-12">
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