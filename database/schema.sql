-- Furuth Digital Store - MySQL Schema
CREATE DATABASE IF NOT EXISTS furuth_digital CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE furuth_digital;

-- Storefront accounts (obfuscated table name)
CREATE TABLE fd_x7k9m2_registry (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_blocked TINYINT(1) DEFAULT 0,
    reset_token VARCHAR(64) NULL,
    reset_expires DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Dashboard operators (obfuscated table name)
CREATE TABLE fd_q4p8n1_console (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'editor') DEFAULT 'editor',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Categories
CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Products
CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NULL,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    file_path VARCHAR(500) NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    is_featured TINYINT(1) DEFAULT 0,
    sales_count INT UNSIGNED DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE product_screenshots (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Coupons
CREATE TABLE coupons (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    type ENUM('flat', 'percentage') NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    min_order DECIMAL(10,2) DEFAULT 0,
    usage_limit INT UNSIGNED NULL,
    used_count INT UNSIGNED DEFAULT 0,
    expires_at DATETIME NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Orders
CREATE TABLE orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    order_number VARCHAR(32) NOT NULL UNIQUE,
    subtotal DECIMAL(10,2) NOT NULL,
    discount DECIMAL(10,2) DEFAULT 0,
    tax_amount DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    coupon_id INT UNSIGNED NULL,
    currency VARCHAR(10) DEFAULT 'INR',
    payment_gateway VARCHAR(30) NULL,
    transaction_id VARCHAR(120) NULL,
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES fd_x7k9m2_registry(id),
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE SET NULL
);

CREATE TABLE order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    product_title VARCHAR(200) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Download tokens
CREATE TABLE download_tokens (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    order_id INT UNSIGNED NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NULL,
    download_count INT UNSIGNED DEFAULT 0,
    max_downloads INT UNSIGNED DEFAULT 10,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES fd_x7k9m2_registry(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Settings (key-value)
CREATE TABLE settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT
);

-- FAQ
CREATE TABLE faqs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(500) NOT NULL,
    answer TEXT NOT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Testimonials
CREATE TABLE testimonials (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    role VARCHAR(120) NULL,
    content TEXT NOT NULL,
    avatar VARCHAR(500) NULL,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0
);

-- Support tickets
CREATE TABLE support_tickets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('open', 'in_progress', 'closed') DEFAULT 'open',
    admin_reply TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES fd_x7k9m2_registry(id) ON DELETE SET NULL
);

-- Contact messages
CREATE TABLE contact_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Default settings
INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'Furuth Digital'),
('site_logo', ''),
('footer_text', '© 2026 Furuth Digital. All rights reserved.'),
('currency', 'INR'),
('currency_symbol', '₹'),
('tax_percent', '18'),
('tax_label', 'GST'),
('payment_gateway', 'razorpay'),
('razorpay_key_id', ''),
('razorpay_key_secret', ''),
('stripe_public_key', ''),
('stripe_secret_key', ''),
('paypal_client_id', ''),
('paypal_secret', ''),
('download_expiry_hours', '72'),
('max_downloads', '10'),
('email_from', 'noreply@furuthdigital.com'),
('smtp_enabled', '0');

-- Default operator — password: Admin@123 (re-run install.php to reset)
INSERT INTO fd_q4p8n1_console (name, email, password, role) VALUES
('Super Admin', 'admin@furuthdigital.com', '$2y$10$7DvTgeB33USBQX8dviCKiuKM3J3QAINLRG23M/jUWPjkSyp4OlQkS', 'super_admin');

-- Sample categories
INSERT INTO categories (name, slug) VALUES
('Templates', 'templates'),
('Graphics', 'graphics'),
('Software', 'software'),
('Courses', 'courses');

-- Sample products
INSERT INTO products (category_id, title, slug, description, price, status, is_featured, sales_count) VALUES
(1, 'Premium UI Kit', 'premium-ui-kit', 'A complete Material Design UI kit with 200+ components for web and mobile.', 49.99, 'active', 1, 120),
(2, 'Icon Pack Pro', 'icon-pack-pro', '5000+ vector icons in SVG and PNG formats for any project.', 29.99, 'active', 1, 85),
(3, 'E-commerce Dashboard', 'ecommerce-dashboard', 'Full admin dashboard template with charts, tables, and dark mode.', 79.99, 'active', 1, 64),
(4, 'Web Dev Masterclass', 'web-dev-masterclass', 'Complete video course covering HTML, CSS, JavaScript, and PHP.', 99.99, 'active', 0, 42);

-- Sample FAQs
INSERT INTO faqs (question, answer, sort_order) VALUES
('How do I download my purchase?', 'After payment, go to Orders in your profile. Click Download on any purchased product. Links expire after the configured period for security.', 1),
('What payment methods are accepted?', 'We support Razorpay, Stripe, and PayPal depending on admin configuration.', 2),
('Can I get a refund?', 'Refunds are handled case-by-case. Contact support with your order number.', 3),
('Are products licensed for commercial use?', 'Unless stated otherwise, all digital products include a commercial license.', 4);

-- Sample testimonials
INSERT INTO testimonials (name, role, content, is_active, sort_order) VALUES
('Sarah Chen', 'Freelance Designer', 'Amazing quality templates. Saved me weeks of work!', 1, 1),
('Marcus Johnson', 'Startup Founder', 'The dashboard template paid for itself on day one.', 1, 2),
('Priya Sharma', 'Developer', 'Clean code, great documentation. Highly recommend Furuth Digital.', 1, 3);
