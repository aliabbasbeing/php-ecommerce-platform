# PHP E-commerce Platform

A complete, production-ready PHP e-commerce platform with modern features, responsive design, and comprehensive admin controls.

## Features

### 🛍️ User-Facing Features
- **Responsive Design** - Mobile-optimized with Tailwind CSS
- **Product Catalog** - Advanced search, filtering, and categories
- **Shopping Cart** - AJAX-powered cart with real-time updates
- **User Authentication** - JWT-based with Google OAuth support
- **Order Management** - Complete checkout process with order tracking
- **User Dashboard** - Profile management, order history, wishlist
- **Reviews & Ratings** - Product reviews and rating system
- **Newsletter** - Email subscription and marketing
- **Multi-channel Notifications** - Email, WhatsApp, SMS support

### 🎛️ Admin Features
- **Dashboard** - Sales analytics and performance metrics
- **Product Management** - CRUD operations, inventory tracking
- **Order Management** - Process orders, update status, generate invoices
- **User Management** - Customer profiles and role management
- **Content Management** - Categories, coupons, settings
- **Reports** - Sales reports and analytics
- **Notification System** - Admin alerts and bulk messaging

### 🔧 Technical Features
- **PHP 8.x** with modern OOP architecture
- **MySQL 8.0** with optimized schema
- **JWT Authentication** with role-based access control
- **RESTful API** for frontend integration
- **Payment Integration** - Stripe, PayPal support
- **Email System** - SMTP with template support
- **File Upload** - Secure image and document handling
- **Security** - CSRF protection, SQL injection prevention
- **Performance** - Optimized queries, caching support
- **SEO-Friendly** - Clean URLs, meta tags

## Installation

### Requirements
- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server
- Composer
- Node.js (for frontend assets)

### Quick Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/aliabbasbeing/php-ecommerce-platform.git
   cd php-ecommerce-platform
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   # Edit .env with your database and other settings
   ```

4. **Set up database**
   ```bash
   # Create database
   mysql -u root -p -e "CREATE DATABASE ecommerce_platform"
   
   # Import schema
   mysql -u root -p ecommerce_platform < database/schema.sql
   
   # Import sample data (optional)
   mysql -u root -p ecommerce_platform < database/seeds.sql
   ```

5. **Configure web server**
   - Point document root to `/public` directory
   - Ensure URL rewriting is enabled
   - Set appropriate permissions for upload directories

6. **Build frontend assets**
   ```bash
   npm run build
   ```

### Configuration

#### Database
Edit `.env` file with your database credentials:
```env
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=ecommerce_platform
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

#### JWT Authentication
Set a secure JWT secret:
```env
JWT_SECRET=your-super-secret-jwt-key-change-this-in-production
```

#### Email Configuration
Configure SMTP settings:
```env
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
```

#### Payment Gateways
Add your payment gateway credentials:
```env
STRIPE_SECRET_KEY=sk_test_your_stripe_secret_key
STRIPE_PUBLISHABLE_KEY=pk_test_your_stripe_publishable_key
PAYPAL_CLIENT_ID=your_paypal_client_id
PAYPAL_CLIENT_SECRET=your_paypal_client_secret
```

## Usage

### Default Accounts
After importing sample data, you can use:
- **Admin**: admin@example.com / password
- **Customer**: john.doe@example.com / password

### API Endpoints
The platform provides RESTful API endpoints:
- `GET /api/products` - List products
- `POST /api/cart/add` - Add to cart
- `GET /api/cart/items` - Get cart items
- `POST /api/orders/create` - Create order
- `GET /api/auth/user` - Get authenticated user

### File Structure
```
php-ecommerce-platform/
├── config/              # Configuration files
├── public/              # Web root directory
│   ├── assets/          # CSS, JS, images
│   ├── uploads/         # User uploaded files
│   └── index.php        # Main entry point
├── admin/               # Admin panel
├── api/                 # API endpoints
├── src/                 # Application source code
│   ├── Controllers/     # Request handlers
│   ├── Models/          # Data models
│   ├── Services/        # Business logic
│   └── Middleware/      # Request middleware
├── templates/           # View templates
├── database/            # Database files
└── vendor/              # Dependencies
```

## Security

This platform implements several security measures:
- **CSRF Protection** - All forms protected with CSRF tokens
- **SQL Injection Prevention** - Prepared statements used throughout
- **XSS Protection** - Input sanitization and output escaping
- **Password Security** - Bcrypt hashing with salt
- **File Upload Security** - Type validation and secure storage
- **Rate Limiting** - API and form submission protection
- **HTTPS Enforcement** - SSL/TLS support
- **Security Headers** - Comprehensive security headers

## Performance

Optimization features include:
- **Database Indexing** - Optimized indexes for fast queries
- **Image Optimization** - Lazy loading and compression
- **Caching** - File-based caching system
- **Minification** - CSS/JS minification
- **CDN Ready** - Asset organization for CDN integration

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

For support and questions:
- Create an issue in the GitHub repository
- Check the documentation in the `/docs` folder
- Contact the development team

## Roadmap

Upcoming features:
- [ ] Multi-language support
- [ ] Advanced analytics dashboard
- [ ] Mobile app API
- [ ] Inventory management system
- [ ] Subscription/recurring payments
- [ ] Advanced SEO tools
- [ ] Social media integration
- [ ] AI-powered recommendations

---

Built with ❤️ using modern PHP and web technologies.