<?php
$title = 'Register - ' . config('app.app_name');
$description = 'Create a new account to start shopping and enjoy exclusive benefits.';

ob_start();
?>

<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Create your account
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Already have an account?
                <a href="<?= url('login') ?>" class="font-medium text-blue-600 hover:text-blue-500">
                    Sign in here
                </a>
            </p>
        </div>
        
        <form class="mt-8 space-y-6" action="<?= url('register') ?>" method="POST" data-validate>
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                        <input id="first_name" name="first_name" type="text" autocomplete="given-name" required
                               class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                               placeholder="First name" value="<?= old('first_name') ?>">
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                        <input id="last_name" name="last_name" type="text" autocomplete="family-name" required
                               class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                               placeholder="Last name" value="<?= old('last_name') ?>">
                    </div>
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                           placeholder="Email address" value="<?= old('email') ?>">
                </div>
                
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number (Optional)</label>
                    <input id="phone" name="phone" type="tel" autocomplete="tel"
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                           placeholder="Phone number" value="<?= old('phone') ?>">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                           placeholder="Password (min. 8 characters)">
                    <p class="mt-1 text-sm text-gray-500">Must be at least 8 characters long</p>
                </div>
                
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                           data-confirm="password"
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                           placeholder="Confirm password">
                </div>
            </div>

            <div class="flex items-center">
                <input id="terms" name="terms" type="checkbox" required
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="terms" class="ml-2 block text-sm text-gray-900">
                    I agree to the 
                    <a href="<?= url('terms') ?>" class="text-blue-600 hover:text-blue-500">Terms of Service</a>
                    and 
                    <a href="<?= url('privacy') ?>" class="text-blue-600 hover:text-blue-500">Privacy Policy</a>
                </label>
            </div>

            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-user-plus text-blue-500 group-hover:text-blue-400"></i>
                    </span>
                    Create Account
                </button>
            </div>

            <!-- Social Registration -->
            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-gray-50 text-gray-500">Or register with</span>
                    </div>
                </div>

                <div class="mt-6">
                    <a href="<?= url('auth/google') ?>" 
                       class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <i class="fab fa-google text-red-500 mr-2"></i>
                        Google
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Password strength indicator
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strength = calculatePasswordStrength(password);
    showPasswordStrength(strength);
});

function calculatePasswordStrength(password) {
    let score = 0;
    
    if (password.length >= 8) score++;
    if (password.match(/[a-z]/)) score++;
    if (password.match(/[A-Z]/)) score++;
    if (password.match(/[0-9]/)) score++;
    if (password.match(/[^a-zA-Z0-9]/)) score++;
    
    return score;
}

function showPasswordStrength(score) {
    const colors = ['#ef4444', '#f59e0b', '#10b981', '#10b981', '#10b981'];
    const labels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
    
    let indicator = document.getElementById('password-strength');
    if (!indicator) {
        indicator = document.createElement('div');
        indicator.id = 'password-strength';
        indicator.className = 'mt-2 text-sm';
        document.getElementById('password').parentNode.appendChild(indicator);
    }
    
    if (score > 0) {
        indicator.innerHTML = `
            <div class="flex items-center space-x-2">
                <div class="flex-1 bg-gray-200 rounded-full h-2">
                    <div class="h-2 rounded-full transition-all duration-300" 
                         style="width: ${(score / 5) * 100}%; background-color: ${colors[score - 1]}"></div>
                </div>
                <span style="color: ${colors[score - 1]}">${labels[score - 1]}</span>
            </div>
        `;
    } else {
        indicator.innerHTML = '';
    }
}

// Handle form submission with AJAX
document.querySelector('form[data-validate]').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    
    // Show loading state
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating Account...';
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Account created successfully! Please check your email for verification.', 'success');
            setTimeout(() => {
                window.location.href = '/login';
            }, 2000);
        } else {
            if (data.errors) {
                // Show validation errors
                Object.keys(data.errors).forEach(field => {
                    const fieldElement = document.getElementById(field);
                    if (fieldElement) {
                        showFieldError(fieldElement, data.errors[field][0]);
                    }
                });
            } else {
                showNotification(data.message || 'Registration failed', 'error');
            }
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../templates/layouts/main.php';
?>