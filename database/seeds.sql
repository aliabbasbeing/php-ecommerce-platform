-- Sample data for E-commerce Platform
-- Run this after the schema to populate with sample data

USE ecommerce_platform;

-- Insert sample categories
INSERT INTO categories (name, slug, description, is_active, sort_order) VALUES
('Electronics', 'electronics', 'Latest electronic gadgets and devices', 1, 1),
('Clothing', 'clothing', 'Fashion and apparel for all ages', 1, 2),
('Home & Garden', 'home-garden', 'Everything for your home and garden', 1, 3),
('Books', 'books', 'Wide selection of books and literature', 1, 4),
('Sports', 'sports', 'Sports equipment and accessories', 1, 5),
('Health & Beauty', 'health-beauty', 'Health and beauty products', 1, 6);

-- Insert subcategories
INSERT INTO categories (name, slug, description, parent_id, is_active, sort_order) VALUES
('Smartphones', 'smartphones', 'Latest smartphones and accessories', 1, 1, 1),
('Laptops', 'laptops', 'Laptops and computers', 1, 1, 2),
('Men\'s Clothing', 'mens-clothing', 'Fashion for men', 2, 1, 1),
('Women\'s Clothing', 'womens-clothing', 'Fashion for women', 2, 1, 2),
('Furniture', 'furniture', 'Home furniture', 3, 1, 1),
('Kitchen', 'kitchen', 'Kitchen appliances and tools', 3, 1, 2);

-- Insert sample products
INSERT INTO products (name, slug, description, short_description, sku, price, sale_price, stock_quantity, is_active, is_featured) VALUES
('iPhone 15 Pro', 'iphone-15-pro', 'Latest iPhone with advanced features and superior camera quality. Experience the future of smartphones.', 'Latest iPhone with advanced features', 'IP15PRO001', 999.00, 899.00, 50, 1, 1),
('Samsung Galaxy S24', 'samsung-galaxy-s24', 'Powerful Android smartphone with excellent display and camera capabilities.', 'Powerful Android smartphone', 'SGS24001', 849.00, NULL, 30, 1, 1),
('MacBook Air M3', 'macbook-air-m3', 'Ultra-thin laptop with M3 chip for exceptional performance and battery life.', 'Ultra-thin laptop with M3 chip', 'MBA001', 1299.00, 1199.00, 25, 1, 1),
('Dell XPS 13', 'dell-xps-13', 'Premium ultrabook with stunning display and powerful performance.', 'Premium ultrabook', 'DXS13001', 1099.00, NULL, 20, 1, 0),
('Classic T-Shirt', 'classic-t-shirt', 'Comfortable cotton t-shirt available in multiple colors and sizes.', 'Comfortable cotton t-shirt', 'TSHIRT001', 29.99, 24.99, 100, 1, 1),
('Jeans - Slim Fit', 'jeans-slim-fit', 'High-quality denim jeans with slim fit design for modern style.', 'High-quality slim fit jeans', 'JEANS001', 79.99, NULL, 75, 1, 0),
('Wireless Headphones', 'wireless-headphones', 'Premium wireless headphones with noise cancellation and superior sound quality.', 'Premium wireless headphones', 'WH001', 199.99, 179.99, 40, 1, 1),
('Smart Watch', 'smart-watch', 'Advanced smartwatch with health monitoring and connectivity features.', 'Advanced smartwatch', 'SW001', 299.99, 279.99, 35, 1, 1),
('Coffee Maker', 'coffee-maker', 'Automatic coffee maker with programmable settings and thermal carafe.', 'Automatic coffee maker', 'CM001', 129.99, NULL, 60, 1, 0),
('Yoga Mat', 'yoga-mat', 'High-quality yoga mat with excellent grip and cushioning for comfortable practice.', 'High-quality yoga mat', 'YM001', 39.99, 34.99, 80, 1, 1);

-- Link products to categories
INSERT INTO product_categories (product_id, category_id) VALUES
(1, 1), (1, 7),  -- iPhone to Electronics and Smartphones
(2, 1), (2, 7),  -- Samsung to Electronics and Smartphones
(3, 1), (3, 8),  -- MacBook to Electronics and Laptops
(4, 1), (4, 8),  -- Dell to Electronics and Laptops
(5, 2), (5, 9),  -- T-Shirt to Clothing and Men's
(6, 2), (6, 9),  -- Jeans to Clothing and Men's
(7, 1),          -- Headphones to Electronics
(8, 1),          -- Smart Watch to Electronics
(9, 3), (9, 12), -- Coffee Maker to Home & Garden and Kitchen
(10, 5);         -- Yoga Mat to Sports

-- Insert sample admin user
INSERT INTO users (email, password, first_name, last_name, role, is_active, email_verified_at) VALUES
('admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin', 1, NOW());

-- Insert sample customer users
INSERT INTO users (email, password, first_name, last_name, role, is_active, email_verified_at) VALUES
('john.doe@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', 'customer', 1, NOW()),
('jane.smith@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Smith', 'customer', 1, NOW());

-- Insert sample user addresses
INSERT INTO user_addresses (user_id, type, first_name, last_name, address_line_1, city, state, postal_code, country, is_default) VALUES
(2, 'billing', 'John', 'Doe', '123 Main St', 'New York', 'NY', '10001', 'United States', 1),
(2, 'shipping', 'John', 'Doe', '123 Main St', 'New York', 'NY', '10001', 'United States', 1),
(3, 'billing', 'Jane', 'Smith', '456 Oak Ave', 'Los Angeles', 'CA', '90210', 'United States', 1);

-- Insert sample orders
INSERT INTO orders (order_number, user_id, status, subtotal, tax_amount, shipping_amount, total_amount, payment_status, payment_method, billing_address, shipping_address) VALUES
('ORD-2024-000001', 2, 'delivered', 1098.99, 109.90, 0.00, 1208.89, 'paid', 'stripe', 
 '{"first_name":"John","last_name":"Doe","address_line_1":"123 Main St","city":"New York","state":"NY","postal_code":"10001","country":"United States"}',
 '{"first_name":"John","last_name":"Doe","address_line_1":"123 Main St","city":"New York","state":"NY","postal_code":"10001","country":"United States"}'),
('ORD-2024-000002', 3, 'processing', 204.98, 20.50, 5.99, 231.47, 'paid', 'paypal',
 '{"first_name":"Jane","last_name":"Smith","address_line_1":"456 Oak Ave","city":"Los Angeles","state":"CA","postal_code":"90210","country":"United States"}',
 '{"first_name":"Jane","last_name":"Smith","address_line_1":"456 Oak Ave","city":"Los Angeles","state":"CA","postal_code":"90210","country":"United States"}');

-- Insert sample order items
INSERT INTO order_items (order_id, product_id, product_name, product_sku, quantity, price, total) VALUES
(1, 1, 'iPhone 15 Pro', 'IP15PRO001', 1, 899.00, 899.00),
(1, 7, 'Wireless Headphones', 'WH001', 1, 199.99, 199.99),
(2, 5, 'Classic T-Shirt', 'TSHIRT001', 2, 24.99, 49.98),
(2, 7, 'Wireless Headphones', 'WH001', 1, 179.99, 179.99);

-- Insert sample reviews
INSERT INTO reviews (product_id, user_id, rating, title, comment, is_approved) VALUES
(1, 2, 5, 'Excellent phone!', 'Amazing camera quality and performance. Highly recommended!', 1),
(1, 3, 4, 'Great but expensive', 'Love the features but wish it was more affordable.', 1),
(7, 2, 5, 'Best headphones ever', 'Sound quality is incredible and noise cancellation works perfectly.', 1),
(5, 3, 4, 'Good quality shirt', 'Comfortable fabric and good fit. Will buy again.', 1);

-- Insert sample coupons
INSERT INTO coupons (code, type, value, minimum_amount, usage_limit, is_active, expires_at) VALUES
('WELCOME10', 'percentage', 10.00, 50.00, 100, 1, DATE_ADD(NOW(), INTERVAL 1 MONTH)),
('SAVE20', 'fixed_amount', 20.00, 100.00, 50, 1, DATE_ADD(NOW(), INTERVAL 2 WEEKS)),
('FREESHIP', 'percentage', 100.00, 75.00, NULL, 1, DATE_ADD(NOW(), INTERVAL 3 MONTHS));

-- Insert sample shipping methods
INSERT INTO shipping_methods (name, description, cost, free_shipping_threshold, estimated_days, is_active) VALUES
('Standard Shipping', 'Regular delivery within 5-7 business days', 5.99, 50.00, '5-7 days', 1),
('Express Shipping', 'Fast delivery within 2-3 business days', 12.99, 100.00, '2-3 days', 1),
('Overnight Shipping', 'Next business day delivery', 24.99, 200.00, '1 day', 1);

-- Insert sample payment methods
INSERT INTO payment_methods (name, code, description, is_active, settings) VALUES
('Credit Card (Stripe)', 'stripe', 'Pay securely with credit or debit card', 1, '{"public_key":"pk_test_...","secret_key":"sk_test_..."}'),
('PayPal', 'paypal', 'Pay with your PayPal account', 1, '{"client_id":"...","client_secret":"...","mode":"sandbox"}'),
('Cash on Delivery', 'cod', 'Pay when you receive your order', 1, '{}');

-- Insert sample settings
INSERT INTO settings (key_name, value, type, description, is_public) VALUES
('site_name', 'E-commerce Platform', 'string', 'Website name', 1),
('site_description', 'Complete e-commerce platform with modern features', 'string', 'Website description', 1),
('currency', 'USD', 'string', 'Default currency', 1),
('tax_rate', '0.10', 'number', 'Default tax rate (10%)', 0),
('free_shipping_threshold', '50.00', 'number', 'Free shipping minimum amount', 1),
('items_per_page', '20', 'number', 'Default pagination limit', 0),
('allow_guest_checkout', '1', 'boolean', 'Allow checkout without registration', 0),
('maintenance_mode', '0', 'boolean', 'Enable maintenance mode', 0);

-- Insert sample email templates
INSERT INTO email_templates (name, subject, body, variables, is_active) VALUES
('order_confirmation', 'Order Confirmation - {{order_number}}', 
 '<h2>Thank you for your order!</h2><p>Dear {{user_name}},</p><p>Your order {{order_number}} has been confirmed.</p><p>Total: ${{total_amount}}</p>', 
 '["user_name","order_number","total_amount","order_url"]', 1),
('welcome', 'Welcome to {{site_name}}!', 
 '<h2>Welcome {{user_name}}!</h2><p>Thank you for joining our community. Start shopping now!</p>', 
 '["user_name","site_name","shop_url"]', 1),
('password_reset', 'Password Reset Request', 
 '<h2>Password Reset</h2><p>Click the link below to reset your password:</p><p><a href="{{reset_url}}">Reset Password</a></p>', 
 '["user_name","reset_url","expires_in"]', 1);

-- Note: Default password for all sample users is "password"
-- Remember to change these in production!