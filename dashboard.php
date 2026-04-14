<?php
// Start session to manage login state
session_start();

// Check if admin is logged in, if not redirect to login page
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Include database connection
require_once 'db_connect.php';

// Get statistics for dashboard
$stats = [
    'total_requests' => 0,
    'pending_requests' => 0,
    'confirmed_requests' => 0,
    'completed_requests' => 0,
    'cancelled_requests' => 0,
    'total_users' => 0,
    'recent_requests' => []
];

// Get total requests
$sql = "SELECT COUNT(*) as total FROM moving_requests";
if ($stmt = $pdo->prepare($sql)) {
    if ($stmt->execute()) {
        $row = $stmt->fetch();
        $stats['total_requests'] = $row['total'];
    }
    unset($stmt);
}

// Get pending requests
$sql = "SELECT COUNT(*) as total FROM moving_requests WHERE status = 'pending'";
if ($stmt = $pdo->prepare($sql)) {
    if ($stmt->execute()) {
        $row = $stmt->fetch();
        $stats['pending_requests'] = $row['total'];
    }
    unset($stmt);
}

// Get confirmed requests
$sql = "SELECT COUNT(*) as total FROM moving_requests WHERE status = 'confirmed'";
if ($stmt = $pdo->prepare($sql)) {
    if ($stmt->execute()) {
        $row = $stmt->fetch();
        $stats['confirmed_requests'] = $row['total'];
    }
    unset($stmt);
}

// Get completed requests
$sql = "SELECT COUNT(*) as total FROM moving_requests WHERE status = 'completed'";
if ($stmt = $pdo->prepare($sql)) {
    if ($stmt->execute()) {
        $row = $stmt->fetch();
        $stats['completed_requests'] = $row['total'];
    }
    unset($stmt);
}

// Get cancelled requests
$sql = "SELECT COUNT(*) as total FROM moving_requests WHERE status = 'cancelled'";
if ($stmt = $pdo->prepare($sql)) {
    if ($stmt->execute()) {
        $row = $stmt->fetch();
        $stats['cancelled_requests'] = $row['total'];
    }
    unset($stmt);
}

// Get total users
$sql = "SELECT COUNT(*) as total FROM users";
if ($stmt = $pdo->prepare($sql)) {
    if ($stmt->execute()) {
        $row = $stmt->fetch();
        $stats['total_users'] = $row['total'];
    }
    unset($stmt);
}

// Get recent requests
$sql = "SELECT mr.*, u.name as user_name 
        FROM moving_requests mr 
        JOIN users u ON mr.user_id = u.id 
        ORDER BY mr.created_at DESC LIMIT 5";
if ($stmt = $pdo->prepare($sql)) {
    if ($stmt->execute()) {
        $stats['recent_requests'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - EasyMovers</title>
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
                    <a href="dashboard.php" class="text-primary border-b-2 border-primary transition-colors">Dashboard</a>
                    <a href="manage_requests.php" class="text-gray-300 hover:text-primary transition-colors">Manage Requests</a>
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
                <a href="dashboard.php" class="text-primary py-2">Dashboard</a>
                <a href="manage_requests.php" class="text-gray-300 hover:text-primary transition-colors py-2">Manage Requests</a>
                <a href="manage_users.php" class="text-gray-300 hover:text-primary transition-colors py-2">Manage Users</a>
                <a href="admin_logout.php" class="text-gray-300 hover:text-primary transition-colors py-2">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-6 py-8">
        <h1 class="text-3xl font-bold mb-8">Admin Dashboard</h1>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- Total Requests -->
            <div class="bg-darker p-6 rounded-xl shadow-lg border border-gray-800">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-900 bg-opacity-30 text-blue-500">
                        <i class="fas fa-truck-moving text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-400 text-sm">Total Requests</p>
                        <h3 class="text-2xl font-bold"><?php echo $stats['total_requests']; ?></h3>
                    </div>
                </div>
            </div>
            
            <!-- Pending Requests -->
            <div class="bg-darker p-6 rounded-xl shadow-lg border border-gray-800">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-900 bg-opacity-30 text-yellow-500">
                        <i class="fas fa-clock text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-400 text-sm">Pending Requests</p>
                        <h3 class="text-2xl font-bold"><?php echo $stats['pending_requests']; ?></h3>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="manage_requests.php?filter=pending" class="text-primary text-sm hover:underline">View all pending →</a>
                </div>
            </div>
            
            <!-- Confirmed Requests -->
            <div class="bg-darker p-6 rounded-xl shadow-lg border border-gray-800">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-900 bg-opacity-30 text-blue-500">
                        <i class="fas fa-check-circle text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-400 text-sm">Confirmed Requests</p>
                        <h3 class="text-2xl font-bold"><?php echo $stats['confirmed_requests']; ?></h3>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="manage_requests.php?filter=confirmed" class="text-primary text-sm hover:underline">View all confirmed →</a>
                </div>
            </div>
            
            <!-- Completed Requests -->
            <div class="bg-darker p-6 rounded-xl shadow-lg border border-gray-800">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-900 bg-opacity-30 text-green-500">
                        <i class="fas fa-flag-checkered text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-400 text-sm">Completed Requests</p>
                        <h3 class="text-2xl font-bold"><?php echo $stats['completed_requests']; ?></h3>
                    </div>
                </div>
            </div>
            
            <!-- Cancelled Requests -->
            <div class="bg-darker p-6 rounded-xl shadow-lg border border-gray-800">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-900 bg-opacity-30 text-red-500">
                        <i class="fas fa-ban text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-400 text-sm">Cancelled Requests</p>
                        <h3 class="text-2xl font-bold"><?php echo $stats['cancelled_requests']; ?></h3>
                    </div>
                </div>
            </div>
            
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
                <div class="mt-4">
                    <a href="manage_users.php" class="text-primary text-sm hover:underline">Manage users →</a>
                </div>
            </div>
        </div>
        
        <!-- Recent Requests -->
        <h2 class="text-xl font-semibold mb-4">Recent Requests</h2>
        <div class="bg-darker rounded-xl shadow-lg border border-gray-800 overflow-hidden">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-900 text-left">
                        <th class="py-3 px-4 font-semibold">ID</th>
                        <th class="py-3 px-4 font-semibold">Customer</th>
                        <th class="py-3 px-4 font-semibold">Service</th>
                        <th class="py-3 px-4 font-semibold">Date</th>
                        <th class="py-3 px-4 font-semibold">Status</th>
                        <th class="py-3 px-4 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($stats['recent_requests'])): ?>
                        <tr>
                            <td colspan="6" class="py-4 px-4 text-center text-gray-400">No recent requests found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($stats['recent_requests'] as $request): ?>
                            <tr class="border-t border-gray-800">
                                <td class="py-3 px-4">#<?php echo $request['id']; ?></td>
                                <td class="py-3 px-4"><?php echo htmlspecialchars($request['user_name']); ?></td>
                                <td class="py-3 px-4"><?php echo htmlspecialchars($request['service_type']); ?></td>
                                <td class="py-3 px-4"><?php echo date('M d, Y', strtotime($request['moving_date'])); ?></td>
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
                                <td class="py-3 px-4">
                                    <a href="manage_requests.php?view=<?php echo $request['id']; ?>" class="text-primary hover:underline">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-6 text-center">
            <a href="manage_requests.php" class="inline-block bg-primary hover:bg-primary-dark text-white font-bold py-2 px-6 rounded-lg transition duration-300">
                View All Requests
            </a>
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