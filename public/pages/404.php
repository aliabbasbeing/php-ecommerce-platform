<?php
$title = '404 - Page Not Found';
$description = 'The page you are looking for could not be found.';

ob_start();
?>

<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full text-center">
        <div class="mb-8">
            <i class="fas fa-exclamation-triangle text-6xl text-gray-400 mb-4"></i>
            <h1 class="text-6xl font-bold text-gray-900 mb-4">404</h1>
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">Page Not Found</h2>
            <p class="text-gray-600 mb-8">
                Sorry, the page you are looking for doesn't exist or has been moved.
            </p>
        </div>
        
        <div class="space-y-4">
            <a href="<?= url() ?>" 
               class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-home mr-2"></i>
                Go Home
            </a>
            
            <div class="text-center">
                <a href="javascript:history.back()" class="text-blue-600 hover:text-blue-500">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Go Back
                </a>
            </div>
        </div>
        
        <div class="mt-12">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Links</h3>
            <div class="grid grid-cols-2 gap-4">
                <a href="<?= url('products') ?>" class="text-blue-600 hover:text-blue-500">
                    <i class="fas fa-shopping-bag mr-1"></i>
                    Products
                </a>
                <a href="<?= url('search') ?>" class="text-blue-600 hover:text-blue-500">
                    <i class="fas fa-search mr-1"></i>
                    Search
                </a>
                <a href="<?= url('contact') ?>" class="text-blue-600 hover:text-blue-500">
                    <i class="fas fa-envelope mr-1"></i>
                    Contact
                </a>
                <a href="<?= url('help') ?>" class="text-blue-600 hover:text-blue-500">
                    <i class="fas fa-question-circle mr-1"></i>
                    Help
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../templates/layouts/main.php';
?>