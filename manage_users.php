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

// Get all users
$sql = "SELECT * FROM users ORDER BY created_at DESC";
$users = [];

if ($stmt = $pdo->prepare($sql)) {
    if ($stmt->execute()) {
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($stmt);
}

// Get user statistics
$stats = [
    'total_users' => count($users),
    'active_users' => 0,
    'new_users_this_month' => 0
];

// Calculate active users (users with at least one moving request)
$sql = "SELECT COUNT(DISTINCT user_id) as active_users FROM moving_requests";
if ($stmt = $pdo->prepare($sql)) {
    if ($stmt->execute()) {
        $row = $stmt->fetch();
        $stats['active_users'] = $row['active_users'];
    }
    unset($stmt);
}

// Calculate new users this month
$sql = "SELECT COUNT(*) as new_users FROM users WHERE created_at >= DATE_FORMAT(NOW() ,'%Y-%m-01')";
if ($stmt = $pdo->prepare($sql)) {
    if ($stmt->execute()) {
        $row = $stmt->fetch();
        $stats['new_users_this_month'] = $row['new_users'];
    }
    unset($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - EasyMovers Admin</title>
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
                    <a href="manage_requests.php" class="text-gray-300 hover:text-primary transition-colors">Manage Requests</a>
                    <a href="manage_users.php" class="text-primary border-b-2 border-primary transition-colors">Manage Users</a>
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
                <a href="manage_requests.php" class="text-gray-300 hover:text-primary transition-colors py-2">Manage Requests</a>
                <a href="manage_users.php" class="text-primary py-2">Manage Users</a>
                <a href="admin_logout.php" class="text-gray-300 hover:text-primary transition-colors py-2">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-6 py-8">
        <h1 class="text-3xl font-bold mb-8">Manage Users</h1>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Users -->
            <div class="bg-darker p-6 rounded-xl shadow-lg border border-gray-800">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-900 bg-opacity-30 text-purple-500">
                        <i class="fas fa-users text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-400 text-sm">Total Users</p>
                        <h3 class="text-2xl font-bold"><?php echo $stats['total_users']; ?></h3>
                    </div>
                </div>
            </div>
            
            <!-- Active Users -->
            <div class="bg-darker p-6 rounded-xl shadow-lg border border-gray-800">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-900 bg-opacity-30 text-green-500">
                        <i class="fas fa-user-check text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-400 text-sm">Active Users</p>
                        <h3 class="text-2xl font-bold"><?php echo $stats['active_users']; ?></h3>
                    </div>
                </div>
            </div>
            
            <!-- New Users This Month -->
            <div class="bg-darker p-6 rounded-xl shadow-lg border border-gray-800">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-900 bg-opacity-30 text-blue-500">
                        <i class="fas fa-user-plus text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-400 text-sm">New Users This Month</p>
                        <h3 class="text-2xl font-bold"><?php echo $stats['new_users_this_month']; ?></h3>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Users Table -->
        <div class="bg-darker rounded-xl shadow-lg overflow-hidden">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-900 text-left">
                        <th class="py-3 px-4 font-semibold">ID</th>
                        <th class="py-3 px-4 font-semibold">Name</th>
                        <th class="py-3 px-4 font-semibold">Email</th>
                        <th class="py-3 px-4 font-semibold">Phone</th>
                        <th class="py-3 px-4 font-semibold">Joined</th>
                        <th class="py-3 px-4 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="6" class="py-4 px-4 text-center text-gray-400">No users found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr class="border-t border-gray-800">
                                <td class="py-3 px-4">#<?php echo $user['id']; ?></td>
                                <td class="py-3 px-4"><?php echo htmlspecialchars($user['name']); ?></td>
                                <td class="py-3 px-4"><?php echo htmlspecialchars($user['email']); ?></td>
                                <td class="py-3 px-4"><?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></td>
                                <td class="py-3 px-4"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td class="py-3 px-4">
                                    <a href="view_user.php?id=<?php echo $user['id']; ?>" class="text-primary hover:underline">View Details</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
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
    </script>
</body>
</html>