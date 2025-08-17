<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? config('app.app_name') ?></title>
    <meta name="description" content="<?= $description ?? 'Complete e-commerce platform with modern features' ?>">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3B82F6',
                        secondary: '#64748B',
                    }
                }
            }
        }
    </script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm" x-data="{ mobileMenuOpen: false, cartOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="<?= url() ?>" class="text-2xl font-bold text-primary">
                        <?= config('app.app_name') ?>
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="<?= url() ?>" class="text-gray-700 hover:text-primary">Home</a>
                    <a href="<?= url('products') ?>" class="text-gray-700 hover:text-primary">Products</a>
                    <a href="<?= url('search') ?>" class="text-gray-700 hover:text-primary">Categories</a>
                    <a href="<?= url('contact') ?>" class="text-gray-700 hover:text-primary">Contact</a>
                </div>

                <!-- Right side -->
                <div class="flex items-center space-x-4">
                    <!-- Search -->
                    <div class="hidden md:block">
                        <form action="<?= url('search') ?>" method="GET" class="relative">
                            <input type="text" name="q" placeholder="Search products..." 
                                   class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        </form>
                    </div>

                    <!-- Cart -->
                    <button @click="cartOpen = true" class="relative text-gray-700 hover:text-primary">
                        <i class="fas fa-shopping-cart text-xl"></i>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center" id="cart-count">
                            0
                        </span>
                    </button>

                    <!-- User Account -->
                    <?php if (isset($_SESSION['user'])): ?>
                        <div class="relative" x-data="{ userMenuOpen: false }">
                            <button @click="userMenuOpen = !userMenuOpen" class="flex items-center space-x-2 text-gray-700 hover:text-primary">
                                <i class="fas fa-user"></i>
                                <span><?= $_SESSION['user']['first_name'] ?></span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            
                            <div x-show="userMenuOpen" x-transition @click.away="userMenuOpen = false" 
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                                <a href="<?= url('account') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user mr-2"></i>My Account
                                </a>
                                <a href="<?= url('account/orders') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-box mr-2"></i>My Orders
                                </a>
                                <a href="<?= url('logout') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?= url('login') ?>" class="text-gray-700 hover:text-primary">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <a href="<?= url('register') ?>" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                            Register
                        </a>
                    <?php endif; ?>

                    <!-- Mobile menu button -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div x-show="mobileMenuOpen" x-transition class="md:hidden bg-white border-t">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="<?= url() ?>" class="block px-3 py-2 text-gray-700">Home</a>
                <a href="<?= url('products') ?>" class="block px-3 py-2 text-gray-700">Products</a>
                <a href="<?= url('search') ?>" class="block px-3 py-2 text-gray-700">Categories</a>
                <a href="<?= url('contact') ?>" class="block px-3 py-2 text-gray-700">Contact</a>
                
                <!-- Mobile search -->
                <div class="px-3 py-2">
                    <form action="<?= url('search') ?>" method="GET" class="relative">
                        <input type="text" name="q" placeholder="Search products..." 
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if ($successMessage = get_flash('success')): ?>
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 mx-4 mt-4 rounded-lg" x-data="{ show: true }" x-show="show" x-transition>
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?= htmlspecialchars($successMessage) ?>
                </div>
                <button @click="show = false" class="text-green-600 hover:text-green-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($errorMessage = get_flash('error')): ?>
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 mx-4 mt-4 rounded-lg" x-data="{ show: true }" x-show="show" x-transition>
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?= htmlspecialchars($errorMessage) ?>
                </div>
                <button @click="show = false" class="text-red-600 hover:text-red-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main>
        <?= $content ?? '' ?>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4"><?= config('app.app_name') ?></h3>
                    <p class="text-gray-300">Your trusted e-commerce platform for all your shopping needs.</p>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="<?= url() ?>" class="text-gray-300 hover:text-white">Home</a></li>
                        <li><a href="<?= url('products') ?>" class="text-gray-300 hover:text-white">Products</a></li>
                        <li><a href="<?= url('contact') ?>" class="text-gray-300 hover:text-white">Contact</a></li>
                        <li><a href="<?= url('about') ?>" class="text-gray-300 hover:text-white">About</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-4">Customer Service</h4>
                    <ul class="space-y-2">
                        <li><a href="<?= url('help') ?>" class="text-gray-300 hover:text-white">Help Center</a></li>
                        <li><a href="<?= url('returns') ?>" class="text-gray-300 hover:text-white">Returns</a></li>
                        <li><a href="<?= url('shipping') ?>" class="text-gray-300 hover:text-white">Shipping Info</a></li>
                        <li><a href="<?= url('privacy') ?>" class="text-gray-300 hover:text-white">Privacy Policy</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-4">Connect</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-300 hover:text-white"><i class="fab fa-facebook text-xl"></i></a>
                        <a href="#" class="text-gray-300 hover:text-white"><i class="fab fa-twitter text-xl"></i></a>
                        <a href="#" class="text-gray-300 hover:text-white"><i class="fab fa-instagram text-xl"></i></a>
                        <a href="#" class="text-gray-300 hover:text-white"><i class="fab fa-linkedin text-xl"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-300">
                <p>&copy; <?= date('Y') ?> <?= config('app.app_name') ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Shopping Cart Sidebar -->
    <div x-show="cartOpen" x-transition class="fixed inset-0 z-50">
        <div class="absolute inset-0 bg-black bg-opacity-50" @click="cartOpen = false"></div>
        <div class="absolute right-0 top-0 h-full w-96 bg-white shadow-xl">
            <div class="p-4 border-b">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold">Shopping Cart</h3>
                    <button @click="cartOpen = false">
                        <i class="fas fa-times text-gray-500"></i>
                    </button>
                </div>
            </div>
            
            <div class="p-4" id="cart-items">
                <!-- Cart items will be loaded here -->
                <p class="text-gray-500 text-center">Your cart is empty</p>
            </div>
            
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t bg-white">
                <div class="mb-4">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span id="cart-subtotal">$0.00</span>
                    </div>
                </div>
                <a href="<?= url('cart') ?>" class="block w-full bg-primary text-white text-center py-3 rounded-lg hover:bg-blue-600 mb-2">
                    View Cart
                </a>
                <a href="<?= url('checkout') ?>" class="block w-full bg-gray-800 text-white text-center py-3 rounded-lg hover:bg-gray-900">
                    Checkout
                </a>
            </div>
        </div>
    </div>

    <!-- Custom JavaScript -->
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>