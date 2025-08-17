<?php
require_once __DIR__ . '/../../src/bootstrap.php';

use App\Models\Product;
use App\Models\Category;

$productModel = new Product();
$categoryModel = new Category();

// Get featured products
$featuredProducts = $productModel->getFeatured(8);

// Get categories
$categories = $categoryModel->getFeatured(6);

// Get recent products
$recentProducts = $productModel->getRecent(8);

$title = 'Home - ' . config('app.app_name');
$description = 'Shop the latest products with fast shipping and great prices. Discover featured items and categories.';

ob_start();
?>

<!-- Hero Section -->
<section class="bg-gradient-to-r from-blue-600 to-blue-800 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <h1 class="text-4xl md:text-6xl font-bold leading-tight mb-6">
                    Shop the Best Products Online
                </h1>
                <p class="text-xl mb-8 text-blue-100">
                    Discover amazing products with fast shipping and unbeatable prices. 
                    Your satisfaction is our guarantee.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="<?= url('products') ?>" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors text-center">
                        Shop Now
                    </a>
                    <a href="<?= url('search') ?>" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition-colors text-center">
                        Browse Categories
                    </a>
                </div>
            </div>
            <div class="hidden lg:block">
                <img src="<?= asset('images/hero-image.jpg') ?>" alt="Shopping" class="rounded-lg shadow-2xl">
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shipping-fast text-blue-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Fast Shipping</h3>
                <p class="text-gray-600">Free shipping on orders over $<?= number_format(config('payment.free_shipping_threshold', 50), 0) ?></p>
            </div>
            
            <div class="text-center">
                <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shield-alt text-blue-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Secure Shopping</h3>
                <p class="text-gray-600">Your payment information is always protected</p>
            </div>
            
            <div class="text-center">
                <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-undo text-blue-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Easy Returns</h3>
                <p class="text-gray-600">30-day return policy on all items</p>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<?php if (!empty($categories)): ?>
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Shop by Category</h2>
            <p class="text-gray-600">Explore our wide range of product categories</p>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
            <?php foreach ($categories as $category): ?>
                <a href="<?= url("search?category={$category['slug']}") ?>" class="group text-center">
                    <div class="bg-white rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow group-hover:bg-blue-50">
                        <?php if ($category['image']): ?>
                            <img src="<?= asset('uploads/' . $category['image']) ?>" alt="<?= htmlspecialchars($category['name']) ?>" 
                                 class="w-16 h-16 mx-auto mb-4 rounded-full object-cover">
                        <?php else: ?>
                            <div class="w-16 h-16 mx-auto mb-4 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-tag text-blue-600 text-xl"></i>
                            </div>
                        <?php endif; ?>
                        <h3 class="font-semibold text-gray-900 group-hover:text-blue-600"><?= htmlspecialchars($category['name']) ?></h3>
                        <p class="text-sm text-gray-500"><?= $category['product_count'] ?> items</p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Featured Products Section -->
<?php if (!empty($featuredProducts)): ?>
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Featured Products</h2>
            <p class="text-gray-600">Hand-picked products just for you</p>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($featuredProducts as $product): ?>
                <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden group">
                    <a href="<?= url("product?slug={$product['slug']}") ?>">
                        <div class="aspect-w-1 aspect-h-1 bg-gray-200">
                            <?php if ($product['image']): ?>
                                <img src="<?= asset('uploads/' . $product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" 
                                     class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                            <?php else: ?>
                                <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                    <i class="fas fa-image text-gray-400 text-4xl"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2 group-hover:text-blue-600">
                                <?= htmlspecialchars($product['name']) ?>
                            </h3>
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <?php if ($product['sale_price']): ?>
                                        <span class="text-lg font-bold text-red-600">
                                            $<?= number_format($product['sale_price'], 2) ?>
                                        </span>
                                        <span class="text-sm text-gray-500 line-through">
                                            $<?= number_format($product['price'], 2) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-lg font-bold text-gray-900">
                                            $<?= number_format($product['price'], 2) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <button onclick="addToCart(<?= $product['id'] ?>)" 
                                        class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-cart-plus"></i>
                                </button>
                            </div>
                            
                            <?php if ($product['stock_status'] !== 'in_stock'): ?>
                                <p class="text-red-500 text-sm mt-2">Out of Stock</p>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-12">
            <a href="<?= url('products') ?>" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                View All Products
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Newsletter Section -->
<section class="py-16 bg-blue-600">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold text-white mb-4">Stay Updated</h2>
        <p class="text-blue-100 mb-8">Subscribe to our newsletter for the latest deals and product updates</p>
        
        <form class="max-w-md mx-auto flex flex-col sm:flex-row gap-4" onsubmit="subscribeNewsletter(event)">
            <input type="email" name="email" placeholder="Enter your email" required
                   class="flex-1 px-4 py-3 rounded-lg border-0 focus:ring-2 focus:ring-blue-300">
            <button type="submit" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                Subscribe
            </button>
        </form>
    </div>
</section>

<script>
// Add to cart functionality
function addToCart(productId, quantity = 1) {
    fetch('/api/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount();
            showNotification('Product added to cart!', 'success');
        } else {
            showNotification(data.message || 'Failed to add product to cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to add product to cart', 'error');
    });
}

// Newsletter subscription
function subscribeNewsletter(event) {
    event.preventDefault();
    const email = event.target.email.value;
    
    fetch('/api/newsletter/subscribe', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ email: email })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Successfully subscribed to newsletter!', 'success');
            event.target.reset();
        } else {
            showNotification(data.message || 'Failed to subscribe', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to subscribe', 'error');
    });
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../templates/layouts/main.php';
?>