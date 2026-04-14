<?php
// Start session to manage login state
session_start();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EasyMovers - Move Anything, Anytime, Anywhere</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
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
            scroll-behavior: smooth;
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

        .service-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(108, 99, 255, 0.2);
        }

        .testimonial-card {
            transition: transform 0.3s ease;
        }

        .testimonial-card:hover {
            transform: scale(1.05);
        }

        .section-divider {
            height: 5px;
            background: linear-gradient(90deg, rgba(108, 99, 255, 0) 0%, rgba(108, 99, 255, 1) 50%, rgba(108, 99, 255, 0) 100%);
            margin: 0 auto;
            width: 50%;
        }

        html, body {
  height: 100%;
}
    </style>
</head>
<body class="bg-dark min-h-screen relative overflow-x-hidden">
    <!-- Navigation -->
    <nav class="fixed w-full z-50 bg-darker bg-opacity-80 backdrop-blur-md">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <a href="#" class="text-3xl font-bold text-primary">EasyMovers</a>
                <div class="hidden md:flex space-x-8 items-center">
                    <!-- <a href="#home" class="text-gray-300 hover:text-primary transition-colors">Home</a>
                    <a href="#how-it-works" class="text-gray-300 hover:text-primary transition-colors">How It Works</a>
                    <a href="#services" class="text-gray-300 hover:text-primary transition-colors">Services</a>
                    <a href="#why-us" class="text-gray-300 hover:text-primary transition-colors">Why Choose Us</a>
                    <a href="#contact" class="text-gray-300 hover:text-primary transition-colors">Contact</a> -->

                    <?php if ($isLoggedIn): ?>
                    <!-- Show when logged in -->
                    <a href="user_dashboard.php" class="neon-button bg-primary hover:bg-primary-dark text-white font-bold py-2 px-4 rounded-lg ml-4">
                        Dashboard
                    </a>
                    <a href="logout.php" class="text-gray-300 hover:text-primary transition-colors ml-4">
                        Logout
                    </a>
                    <?php else: ?>
                    <!-- Show when logged out -->
                    <a href="login.php" class="text-gray-300 hover:text-primary transition-colors ml-4">
                        Login
                    </a>
                    <a href="signup.php" class="neon-button bg-primary hover:bg-primary-dark text-white font-bold py-2 px-4 rounded-lg ml-4">
                        Sign Up
                    </a>
                    <?php endif; ?>

                    <!-- Dropdown Menu -->
                    <div class="relative ml-4 group">
                        <button id="desktop-menu-toggle" class="text-gray-300 hover:text-primary transition-colors flex items-center">
                            <span class="mr-1">Menu</span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <div id="desktop-dropdown" class="absolute right-0 mt-2 w-48 bg-darker border border-gray-800 rounded-lg shadow-lg hidden group-hover:block">
                            <a href="#home" class="block px-4 py-2 text-gray-300 hover:bg-gray-800 hover:text-primary">Home</a>
                            <a href="#how-it-works" class="block px-4 py-2 text-gray-300 hover:bg-gray-800 hover:text-primary">How It Works</a>
                            <a href="#services" class="block px-4 py-2 text-gray-300 hover:bg-gray-800 hover:text-primary">Services</a>
                            <a href="#why-us" class="block px-4 py-2 text-gray-300 hover:bg-gray-800 hover:text-primary">Why Choose Us</a>
                            <a href="#contact" class="block px-4 py-2 text-gray-300 hover:bg-gray-800 hover:text-primary">Contact</a>
                        </div>
                    </div>
                </div>
                <button class="md:hidden text-white focus:outline-none" id="menu-toggle">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div class="md:hidden hidden bg-darker bg-opacity-95 w-full absolute top-16 left-0 p-4 rounded-b-lg" id="mobile-menu">
            <div class="flex flex-col space-y-4">
                <a href="#home" class="text-gray-300 hover:text-primary transition-colors py-2">Home</a>
                <a href="#how-it-works" class="text-gray-300 hover:text-primary transition-colors py-2">How It Works</a>
                <a href="#services" class="text-gray-300 hover:text-primary transition-colors py-2">Services</a>
                <a href="#why-us" class="text-gray-300 hover:text-primary transition-colors py-2">Why Choose Us</a>
                <a href="#contact" class="text-gray-300 hover:text-primary transition-colors py-2">Contact</a>

                <?php if ($isLoggedIn): ?>
                <!-- Show when logged in (mobile) -->
                <div class="border-t border-gray-700 my-2 pt-2"></div>
                <a href="user_dashboard.php" class="text-primary font-semibold hover:text-primary-dark transition-colors py-2">Dashboard</a>
                <a href="logout.php" class="text-gray-300 hover:text-primary transition-colors py-2">Logout</a>
                <?php else: ?>
                <!-- Show when logged out (mobile) -->
                <div class="border-t border-gray-700 my-2 pt-2"></div>
                <a href="login.php" class="text-gray-300 hover:text-primary transition-colors py-2">Login</a>
                <a href="signup.php" class="text-primary font-semibold hover:text-primary-dark transition-colors py-2">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="min-h-screen flex items-center gradient-bg">
        <div class="container mx-auto px-6 py-24">
            <div class="flex flex-col md:flex-row items-center">
                <div class="md:w-1/2 mb-12 md:mb-0" data-aos="fade-right">
                    <h1 class="text-5xl md:text-6xl font-bold mb-6">
                        <span class="text-white">Easy</span><span class="text-primary">Movers</span>
                    </h1>
                    <p class="text-2xl md:text-3xl text-gray-300 mb-8">Move Anything, Anytime, Anywhere</p>
                    <p class="text-gray-400 mb-8 text-lg">Your trusted partner for all logistics and moving solutions. We make relocating simple, affordable, and stress-free.</p>
                    <button class="neon-button bg-primary hover:bg-primary-dark text-white font-bold py-3 px-8 rounded-full text-lg">
                        Get Started
                    </button>
                </div>
                <div class="md:w-1/2" data-aos="fade-left">
                    <img src="https://cdn.pixabay.com/photo/2018/04/06/13/46/poly-3295856_1280.png" alt="Moving Illustration" class="w-full h-auto">
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-20 gradient-bg">
        <div class="container mx-auto px-6">
            <h2 class="text-4xl font-bold text-center mb-16" data-aos="fade-up">How It Works</h2>
            <div class="flex flex-col md:flex-row justify-between items-center space-y-12 md:space-y-0">
                <div class="md:w-1/3 text-center px-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="bg-darker rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-6 border-2 border-primary">
                        <i class="fas fa-calendar-check text-4xl text-primary"></i>
                    </div>
                    <h3 class="text-2xl font-semibold mb-4">1. Book</h3>
                    <p class="text-gray-400">Schedule your move through our app or website. Tell us what you need moved, when, and where.</p>
                </div>
                <div class="md:w-1/3 text-center px-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="bg-darker rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-6 border-2 border-primary">
                        <i class="fas fa-truck text-4xl text-primary"></i>
                    </div>
                    <h3 class="text-2xl font-semibold mb-4">2. Move</h3>
                    <p class="text-gray-400">Our professional team arrives on time, handles your items with care, and loads them securely.</p>
                </div>
                <div class="md:w-1/3 text-center px-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="bg-darker rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-6 border-2 border-primary">
                        <i class="fas fa-box-open text-4xl text-primary"></i>
                    </div>
                    <h3 class="text-2xl font-semibold mb-4">3. Delivered</h3>
                    <p class="text-gray-400">Your belongings are transported safely and delivered to your destination right on schedule.</p>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    <!-- Services Section -->
    <section id="services" class="py-20 gradient-bg">
        <div class="container mx-auto px-6">
            <h2 class="text-4xl font-bold text-center mb-16" data-aos="fade-up">Services We Offer</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="service-card bg-darker p-8 rounded-xl border border-gray-800" data-aos="fade-up" data-aos-delay="100">
                    <div class="text-primary text-4xl mb-4">
                        <i class="fas fa-home"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">House Shifting</h3>
                    <p class="text-gray-400">Complete home relocation services with packing, loading, transport, and unpacking.</p>
                </div>
                <div class="service-card bg-darker p-8 rounded-xl border border-gray-800" data-aos="fade-up" data-aos-delay="200">
                    <div class="text-primary text-4xl mb-4">
                        <i class="fas fa-couch"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Furniture Moving</h3>
                    <p class="text-gray-400">Specialized handling of furniture items with proper protection and care.</p>
                </div>
                <div class="service-card bg-darker p-8 rounded-xl border border-gray-800" data-aos="fade-up" data-aos-delay="300">
                    <div class="text-primary text-4xl mb-4">
                        <i class="fas fa-building"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Office Relocation</h3>
                    <p class="text-gray-400">Efficient business moves with minimal downtime and professional equipment handling.</p>
                </div>
                <div class="service-card bg-darker p-8 rounded-xl border border-gray-800" data-aos="fade-up" data-aos-delay="400">
                    <div class="text-primary text-4xl mb-4">
                        <i class="fas fa-car"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Vehicle Transport</h3>
                    <p class="text-gray-400">Safe and secure transportation of cars, motorcycles, and other vehicles.</p>
                </div>
                <div class="service-card bg-darker p-8 rounded-xl border border-gray-800" data-aos="fade-up" data-aos-delay="500">
                    <div class="text-primary text-4xl mb-4">
                        <i class="fas fa-box"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Custom Orders</h3>
                    <p class="text-gray-400">Tailored moving solutions for unique items or special requirements.</p>
                </div>
                <div class="service-card bg-darker p-8 rounded-xl border border-gray-800" data-aos="fade-up" data-aos-delay="600">
                    <div class="text-primary text-4xl mb-4">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Storage Solutions</h3>
                    <p class="text-gray-400">Secure, climate-controlled storage facilities for short or long-term needs.</p>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    <!-- Why Choose Us Section -->
    <section id="why-us" class="py-20 gradient-bg">
        <div class="container mx-auto px-6">
            <h2 class="text-4xl font-bold text-center mb-16" data-aos="fade-up">Why Choose Us?</h2>

            <!-- Features -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-20">
                <div class="text-center" data-aos="fade-up" data-aos-delay="100">
                    <div class="bg-darker rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-6 border-2 border-primary">
                        <i class="fas fa-map-marked-alt text-2xl text-primary"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Real-Time Tracking</h3>
                    <p class="text-gray-400">Track your belongings in real-time through our mobile app.</p>
                </div>
                <div class="text-center" data-aos="fade-up" data-aos-delay="200">
                    <div class="bg-darker rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-6 border-2 border-primary">
                        <i class="fas fa-dollar-sign text-2xl text-primary"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Affordable Pricing</h3>
                    <p class="text-gray-400">Competitive rates with no hidden fees or surprises.</p>
                </div>
                <div class="text-center" data-aos="fade-up" data-aos-delay="300">
                    <div class="bg-darker rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-6 border-2 border-primary">
                        <i class="fas fa-headset text-2xl text-primary"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">24/7 Support</h3>
                    <p class="text-gray-400">Our customer service team is always available to assist you.</p>
                </div>
            </div>

            <!-- Testimonials -->
            <h3 class="text-2xl font-semibold text-center mb-10" data-aos="fade-up">What Our Customers Say</h3>
            <div class="flex flex-col md:flex-row space-y-8 md:space-y-0 md:space-x-8">
                <div class="testimonial-card bg-darker p-6 rounded-xl border border-gray-800 md:w-1/3" data-aos="fade-up" data-aos-delay="100">
                    <div class="flex items-center mb-4">
                        <div class="text-yellow-400 mr-2">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <span class="text-gray-400">5.0</span>
                    </div>
                    <p class="text-gray-300 mb-4">"EasyMovers made my house move completely stress-free. The team was professional, on time, and handled my belongings with care."</p>
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center mr-3">
                            <span class="text-white font-bold">JD</span>
                        </div>
                        <div>
                            <p class="font-semibold">John Doe</p>
                            <p class="text-gray-400 text-sm">House Moving</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card bg-darker p-6 rounded-xl border border-gray-800 md:w-1/3" data-aos="fade-up" data-aos-delay="200">
                    <div class="flex items-center mb-4">
                        <div class="text-yellow-400 mr-2">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <span class="text-gray-400">5.0</span>
                    </div>
                    <p class="text-gray-300 mb-4">"We used EasyMovers for our office relocation, and they were incredible. Everything was organized, efficient, and completed ahead of schedule."</p>
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-secondary rounded-full flex items-center justify-center mr-3">
                            <span class="text-white font-bold">JS</span>
                        </div>
                        <div>
                            <p class="font-semibold">Jane Smith</p>
                            <p class="text-gray-400 text-sm">Office Relocation</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card bg-darker p-6 rounded-xl border border-gray-800 md:w-1/3" data-aos="fade-up" data-aos-delay="300">
                    <div class="flex items-center mb-4">
                        <div class="text-yellow-400 mr-2">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                        <span class="text-gray-400">4.5</span>
                    </div>
                    <p class="text-gray-300 mb-4">"I needed to transport my car across the country, and EasyMovers made it simple. The real-time tracking gave me peace of mind throughout the process."</p>
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center mr-3">
                            <span class="text-white font-bold">RJ</span>
                        </div>
                        <div>
                            <p class="font-semibold">Robert Johnson</p>
                            <p class="text-gray-400 text-sm">Vehicle Transport</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    <!-- Contact Section -->
    <section id="contact" class="py-20 gradient-bg">
        <div class="container mx-auto px-6">
            <h2 class="text-4xl font-bold text-center mb-16" data-aos="fade-up">Get In Touch</h2>
            <div class="flex flex-col md:flex-row">
                <div class="md:w-1/2 mb-12 md:mb-0 pr-0 md:pr-8" data-aos="fade-right">
                    <h3 class="text-2xl font-semibold mb-6">Request a Quote</h3>
                    <p class="text-gray-400 mb-8">Fill out the form and our team will get back to you within 24 hours with a customized quote for your moving needs.</p>
                    <div class="flex items-center mb-6">
                        <div class="bg-primary rounded-full w-10 h-10 flex items-center justify-center mr-4">
                            <i class="fas fa-phone text-white"></i>
                        </div>
                        <p class="text-gray-300">+1 (555) 123-4567</p>
                    </div>
                    <div class="flex items-center mb-6">
                        <div class="bg-primary rounded-full w-10 h-10 flex items-center justify-center mr-4">
                            <i class="fas fa-envelope text-white"></i>
                        </div>
                        <p class="text-gray-300">info@easymovers.com</p>
                    </div>
                    <div class="flex items-center">
                        <div class="bg-primary rounded-full w-10 h-10 flex items-center justify-center mr-4">
                            <i class="fas fa-map-marker-alt text-white"></i>
                        </div>
                        <p class="text-gray-300">123 Moving Street, Transport City, TC 12345</p>
                    </div>
                </div>
                <div class="md:w-1/2" data-aos="fade-left">
                    <form class="bg-darker p-8 rounded-xl border border-gray-800">
                        <div class="mb-6">
                            <label for="name" class="block text-gray-300 mb-2">Full Name</label>
                            <input type="text" id="name" class="w-full bg-dark border border-gray-700 rounded-lg py-3 px-4 text-white focus:outline-none focus:border-primary">
                        </div>
                        <div class="mb-6">
                            <label for="email" class="block text-gray-300 mb-2">Email Address</label>
                            <input type="email" id="email" class="w-full bg-dark border border-gray-700 rounded-lg py-3 px-4 text-white focus:outline-none focus:border-primary">
                        </div>
                        <div class="mb-6">
                            <label for="phone" class="block text-gray-300 mb-2">Phone Number</label>
                            <input type="tel" id="phone" class="w-full bg-dark border border-gray-700 rounded-lg py-3 px-4 text-white focus:outline-none focus:border-primary">
                        </div>
                        <div class="mb-6">
                            <label for="service" class="block text-gray-300 mb-2">Service Needed</label>
                            <select id="service" class="w-full bg-dark border border-gray-700 rounded-lg py-3 px-4 text-white focus:outline-none focus:border-primary">
                                <option value="">Select a service</option>
                                <option value="house">House Shifting</option>
                                <option value="furniture">Furniture Moving</option>
                                <option value="office">Office Relocation</option>
                                <option value="vehicle">Vehicle Transport</option>
                                <option value="custom">Custom Order</option>
                            </select>
                        </div>
                        <div class="mb-6">
                            <label for="message" class="block text-gray-300 mb-2">Message</label>
                            <textarea id="message" rows="4" class="w-full bg-dark border border-gray-700 rounded-lg py-3 px-4 text-white focus:outline-none focus:border-primary"></textarea>
                        </div>
                        <button type="submit" class="neon-button bg-primary hover:bg-primary-dark text-white font-bold py-3 px-8 rounded-lg w-full">
                            Request a Quote
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-darker py-12">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between mb-12">
                <div class="mb-8 md:mb-0">
                    <a href="#" class="text-3xl font-bold text-primary mb-4 inline-block">EasyMovers</a>
                    <p class="text-gray-400 max-w-xs">Your trusted partner for all logistics and moving solutions. We make relocating simple, affordable, and stress-free.</p>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-8">
                    <div>
                        <h4 class="text-white font-semibold mb-4">Quick Links</h4>
                        <ul class="space-y-2">
                            <li><a href="#home" class="text-gray-400 hover:text-primary transition-colors">Home</a></li>
                            <li><a href="#how-it-works" class="text-gray-400 hover:text-primary transition-colors">How It Works</a></li>
                            <li><a href="#services" class="text-gray-400 hover:text-primary transition-colors">Services</a></li>
                            <li><a href="#why-us" class="text-gray-400 hover:text-primary transition-colors">Why Choose Us</a></li>
                            <li><a href="#contact" class="text-gray-400 hover:text-primary transition-colors">Contact</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold mb-4">Services</h4>
                        <ul class="space-y-2">
                            <li><a href="#" class="text-gray-400 hover:text-primary transition-colors">House Shifting</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-primary transition-colors">Furniture Moving</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-primary transition-colors">Office Relocation</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-primary transition-colors">Vehicle Transport</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-primary transition-colors">Custom Orders</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold mb-4">Connect With Us</h4>
                        <div class="flex space-x-4">
                            <a href="#" class="text-gray-400 hover:text-primary transition-colors">
                                <i class="fab fa-facebook-f text-xl"></i>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-primary transition-colors">
                                <i class="fab fa-twitter text-xl"></i>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-primary transition-colors">
                                <i class="fab fa-instagram text-xl"></i>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-primary transition-colors">
                                <i class="fab fa-linkedin-in text-xl"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-8">
                <p class="text-center text-gray-500">&copy; 2023 EasyMovers. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <!-- Main JavaScript -->
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        // Mobile Menu Toggle
        const menuToggle = document.getElementById('menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');

        menuToggle.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        // Smooth Scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();

                // Close mobile menu if open
                if (!mobileMenu.classList.contains('hidden')) {
                    mobileMenu.classList.add('hidden');
                }

                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Navbar Background Change on Scroll
        window.addEventListener('scroll', function() {
            const nav = document.querySelector('nav');
            if (window.scrollY > 50) {
                nav.classList.add('bg-opacity-95');
                nav.classList.remove('bg-opacity-80');
            } else {
                nav.classList.add('bg-opacity-80');
                nav.classList.remove('bg-opacity-95');
            }
        });

        // Form Validation
        const contactForm = document.querySelector('form');
        if (contactForm) {
            contactForm.addEventListener('submit', function(e) {
                e.preventDefault();

                // Simple validation
                const name = document.getElementById('name').value;
                const email = document.getElementById('email').value;
                const phone = document.getElementById('phone').value;
                const service = document.getElementById('service').value;
                const message = document.getElementById('message').value;

                if (!name || !email || !phone || !service || !message) {
                    alert('Please fill in all fields');
                    return;
                }

                // Email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    alert('Please enter a valid email address');
                    return;
                }

                // Here you would typically send the form data to your server
                alert('Thank you for your inquiry! We will contact you shortly.');
                this.reset();
            });
        }

        // Add hover effects to service cards
        const serviceCards = document.querySelectorAll('.service-card');
        serviceCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px)';
                this.style.boxShadow = '0 10px 30px rgba(108, 99, 255, 0.2)';
                this.style.borderColor = '#6C63FF';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
                this.style.borderColor = '#1f1f1f';
            });
        });

        // Animated counter for stats (if you add them later)
        function animateCounter(el, start, end, duration) {
            let startTime = null;

            function animation(currentTime) {
                if (!startTime) startTime = currentTime;
                const timeElapsed = currentTime - startTime;
                const progress = Math.min(timeElapsed / duration, 1);
                const value = Math.floor(progress * (end - start) + start);

                el.textContent = value;

                if (timeElapsed < duration) {
                    requestAnimationFrame(animation);
                } else {
                    el.textContent = end;
                }
            }

            requestAnimationFrame(animation);
        }

        // Intersection Observer for lazy loading and animations
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.service-card, .testimonial-card').forEach(el => {
            observer.observe(el);
        });

        // Desktop Menu Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const desktopMenuToggle = document.getElementById('desktop-menu-toggle');
            const desktopDropdown = document.getElementById('desktop-dropdown');

            if (desktopMenuToggle && desktopDropdown) {
                desktopMenuToggle.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    desktopDropdown.classList.toggle('hidden');
                });

                // Close the dropdown when clicking outside
                document.addEventListener('click', (e) => {
                    if (!desktopMenuToggle.contains(e.target) && !desktopDropdown.contains(e.target)) {
                        desktopDropdown.classList.add('hidden');
                    }
                });

                // Make sure links in the dropdown work
                desktopDropdown.querySelectorAll('a').forEach(link => {
                    link.addEventListener('click', () => {
                        desktopDropdown.classList.add('hidden');
                    });
                });
            }
        });
    </script>
</body>
</html>