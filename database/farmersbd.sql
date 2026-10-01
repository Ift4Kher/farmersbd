-- ============================================================
-- FarmersBD — Complete Database Schema + Seed Data
-- Encoding: utf8mb4 / utf8mb4_unicode_ci
-- MySQL 8.0+
-- ============================================================

SET NAMES 'utf8mb4';
SET CHARACTER SET utf8mb4;
SET foreign_key_checks = 0;
SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

CREATE DATABASE IF NOT EXISTS `farmersbd`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `farmersbd`;

-- ── Users ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`         VARCHAR(150)    NOT NULL,
    `mobile`       VARCHAR(15)     NOT NULL,
    `email`        VARCHAR(191)    NOT NULL,
    `password`     VARCHAR(255)    NOT NULL,
    `address`      TEXT            DEFAULT NULL,
    `district`     VARCHAR(100)    DEFAULT NULL,
    `avatar`       VARCHAR(255)    DEFAULT NULL,
    `is_active`    TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_email`  (`email`),
    UNIQUE KEY `uq_users_mobile` (`mobile`),
    KEY `idx_users_is_active` (`is_active`),
    KEY `idx_users_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Admins ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `admins` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`         VARCHAR(150)    NOT NULL,
    `email`        VARCHAR(191)    NOT NULL,
    `password`     VARCHAR(255)    NOT NULL,
    `is_active`    TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_admins_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Fish ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `fish` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`            VARCHAR(200)    NOT NULL,
    `scientific_name` VARCHAR(200)    DEFAULT NULL,
    `category`        VARCHAR(100)    DEFAULT NULL,
    `water_type`      VARCHAR(100)    DEFAULT NULL,
    `slug`         VARCHAR(220)    NOT NULL,
    `image`        VARCHAR(255)    DEFAULT NULL,
    `description`  TEXT            DEFAULT NULL,
    `habitat`      TEXT            DEFAULT NULL,
    `farming_tips` TEXT            DEFAULT NULL,
    `sort_order`   INT             NOT NULL DEFAULT 0,
    `is_active`    TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_fish_slug` (`slug`),
    KEY `idx_fish_is_active` (`is_active`),
    KEY `idx_fish_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Diseases ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `diseases` (
    `id`               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`             VARCHAR(200)    NOT NULL,
    `scientific_name`  VARCHAR(200)    DEFAULT NULL,
    `slug`             VARCHAR(220)    NOT NULL,
    `image`            VARCHAR(255)    DEFAULT NULL,
    `description`      TEXT            DEFAULT NULL,
    `symptoms`         TEXT            DEFAULT NULL,
    `causes`           TEXT            DEFAULT NULL,
    `prevention`       TEXT            DEFAULT NULL,
    `treatment`        TEXT            DEFAULT NULL,
    `affected_fish`    TEXT            DEFAULT NULL,
    `ai_label`         VARCHAR(200)    DEFAULT NULL COMMENT 'Matches AI model output label',
    `is_active`        TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_diseases_slug` (`slug`),
    KEY `idx_diseases_is_active` (`is_active`),
    KEY `idx_diseases_ai_label` (`ai_label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Disease ↔ Product (related products) ────────────────────
CREATE TABLE IF NOT EXISTS `disease_products` (
    `disease_id`   INT UNSIGNED NOT NULL,
    `product_id`   INT UNSIGNED NOT NULL,
    PRIMARY KEY (`disease_id`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Product Categories ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS `product_categories` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`         VARCHAR(200)    NOT NULL,
    `slug`         VARCHAR(220)    NOT NULL,
    `description`  TEXT            DEFAULT NULL,
    `image`        VARCHAR(255)    DEFAULT NULL,
    `sort_order`   INT             NOT NULL DEFAULT 0,
    `is_active`    TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_product_categories_slug` (`slug`),
    KEY `idx_pc_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Products ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `products` (
    `id`             INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    `category_id`    INT UNSIGNED      DEFAULT NULL,
    `name`           VARCHAR(255)      NOT NULL,
    `slug`           VARCHAR(280)      NOT NULL,
    `sku`            VARCHAR(100)      DEFAULT NULL,
    `image`          VARCHAR(255)      DEFAULT NULL,
    `description`    TEXT              DEFAULT NULL,
    `usage`          TEXT              DEFAULT NULL,
    `price`          DECIMAL(10,2)     NOT NULL DEFAULT 0.00,
    `discount_price` DECIMAL(10,2)     DEFAULT NULL,
    `stock`          INT               NOT NULL DEFAULT 0,
    `unit`           VARCHAR(50)       DEFAULT NULL,
    `is_featured`    TINYINT(1)        NOT NULL DEFAULT 0,
    `is_active`      TINYINT(1)        NOT NULL DEFAULT 1,
    `created_at`     DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_products_slug` (`slug`),
    KEY `idx_products_category` (`category_id`),
    KEY `idx_products_is_featured` (`is_featured`),
    KEY `idx_products_is_active` (`is_active`),
    KEY `idx_products_price` (`price`),
    CONSTRAINT `fk_products_category`
        FOREIGN KEY (`category_id`) REFERENCES `product_categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Carts ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `carts` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`      INT UNSIGNED    DEFAULT NULL,
    `session_id`   VARCHAR(128)    DEFAULT NULL,
    `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_carts_user_id` (`user_id`),
    KEY `idx_carts_session_id` (`session_id`),
    CONSTRAINT `fk_carts_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Cart Items ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `cart_items` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `cart_id`      INT UNSIGNED    NOT NULL,
    `product_id`   INT UNSIGNED    NOT NULL,
    `quantity`     INT             NOT NULL DEFAULT 1,
    `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_cart_product` (`cart_id`, `product_id`),
    KEY `idx_ci_cart_id` (`cart_id`),
    KEY `idx_ci_product_id` (`product_id`),
    CONSTRAINT `fk_ci_cart`
        FOREIGN KEY (`cart_id`) REFERENCES `carts`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ci_product`
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Orders ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `orders` (
    `id`               INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    `order_number`     VARCHAR(30)       NOT NULL,
    `user_id`          INT UNSIGNED      DEFAULT NULL,
    `customer_name`    VARCHAR(150)      NOT NULL,
    `customer_mobile`  VARCHAR(20)       NOT NULL,
    `customer_email`   VARCHAR(191)      DEFAULT NULL,
    `address`          TEXT              NOT NULL,
    `district`         VARCHAR(100)      NOT NULL,
    `upazila`          VARCHAR(100)      DEFAULT NULL,
    `notes`            TEXT              DEFAULT NULL,
    `subtotal`         DECIMAL(12,2)     NOT NULL DEFAULT 0.00,
    `shipping_cost`    DECIMAL(10,2)     NOT NULL DEFAULT 0.00,
    `total`            DECIMAL(12,2)     NOT NULL DEFAULT 0.00,
    `payment_method`   ENUM('cod','sslcommerz') NOT NULL DEFAULT 'cod',
    `payment_status`   ENUM('pending','paid','failed','cancelled') NOT NULL DEFAULT 'pending',
    `order_status`     ENUM('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
    `notes_admin`      TEXT              DEFAULT NULL,
    `created_at`       DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_orders_number` (`order_number`),
    KEY `idx_orders_user_id` (`user_id`),
    KEY `idx_orders_payment_method` (`payment_method`),
    KEY `idx_orders_payment_status` (`payment_status`),
    KEY `idx_orders_order_status` (`order_status`),
    KEY `idx_orders_created_at` (`created_at`),
    CONSTRAINT `fk_orders_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Order Items ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `order_items` (
    `id`           INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    `order_id`     INT UNSIGNED      NOT NULL,
    `product_id`   INT UNSIGNED      DEFAULT NULL,
    `product_name` VARCHAR(255)      NOT NULL,
    `unit_price`   DECIMAL(10,2)     NOT NULL,
    `quantity`     INT               NOT NULL DEFAULT 1,
    `subtotal`     DECIMAL(12,2)     NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_oi_order_id` (`order_id`),
    KEY `idx_oi_product_id` (`product_id`),
    CONSTRAINT `fk_oi_order`
        FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_oi_product`
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Payments ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `payments` (
    `id`               INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    `order_id`         INT UNSIGNED      NOT NULL,
    `tran_id`          VARCHAR(150)      DEFAULT NULL COMMENT 'SSLCOMMERZ transaction ID',
    `val_id`           VARCHAR(150)      DEFAULT NULL COMMENT 'SSLCOMMERZ validation ID',
    `amount`           DECIMAL(12,2)     NOT NULL,
    `currency`         VARCHAR(10)       NOT NULL DEFAULT 'BDT',
    `payment_method`   VARCHAR(100)      DEFAULT NULL,
    `status`           VARCHAR(50)       DEFAULT NULL,
    `gateway_response` TEXT              DEFAULT NULL COMMENT 'Full JSON response from gateway',
    `created_at`       DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_payments_order_id` (`order_id`),
    KEY `idx_payments_tran_id` (`tran_id`),
    KEY `idx_payments_val_id` (`val_id`),
    CONSTRAINT `fk_payments_order`
        FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── AI Diagnoses ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `ai_diagnoses` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`       INT UNSIGNED    DEFAULT NULL,
    `image_path`    VARCHAR(255)    NOT NULL,
    `prediction`    VARCHAR(255)    DEFAULT NULL COMMENT 'AI model label output',
    `confidence`    DECIMAL(5,4)    DEFAULT NULL COMMENT '0.0000 to 1.0000',
    `disease_id`    INT UNSIGNED    DEFAULT NULL COMMENT 'Matched disease record',
    `api_response`  TEXT            DEFAULT NULL COMMENT 'Full API response JSON',
    `ip_address`    VARCHAR(45)     DEFAULT NULL,
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ai_user_id` (`user_id`),
    KEY `idx_ai_disease_id` (`disease_id`),
    KEY `idx_ai_created_at` (`created_at`),
    CONSTRAINT `fk_ai_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_ai_disease`
        FOREIGN KEY (`disease_id`) REFERENCES `diseases`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Consultations ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `consultations` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`      INT UNSIGNED    NOT NULL,
    `subject`      VARCHAR(255)    NOT NULL,
    `problem`      VARCHAR(500)    NOT NULL,
    `description`  TEXT            DEFAULT NULL,
    `image`        VARCHAR(255)    DEFAULT NULL,
    `preferred_date` DATE          DEFAULT NULL,
    `admin_reply`  TEXT            DEFAULT NULL,
    `replied_at`   DATETIME        DEFAULT NULL,
    `status`       ENUM('pending','answered','closed') NOT NULL DEFAULT 'pending',
    `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_consultations_user_id` (`user_id`),
    KEY `idx_consultations_status` (`status`),
    KEY `idx_consultations_created_at` (`created_at`),
    CONSTRAINT `fk_consultations_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Blog Categories ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `blog_categories` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`         VARCHAR(200)    NOT NULL,
    `slug`         VARCHAR(220)    NOT NULL,
    `is_active`    TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_blog_cat_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Blogs ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `blogs` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `category_id`  INT UNSIGNED    DEFAULT NULL,
    `title`        VARCHAR(400)    NOT NULL,
    `slug`         VARCHAR(420)    NOT NULL,
    `excerpt`      TEXT            DEFAULT NULL,
    `content`      LONGTEXT        DEFAULT NULL,
    `image`        VARCHAR(255)    DEFAULT NULL,
    `is_featured`  TINYINT(1)      NOT NULL DEFAULT 0,
    `is_active`    TINYINT(1)      NOT NULL DEFAULT 1,
    `meta_title`   VARCHAR(255)    DEFAULT NULL,
    `meta_desc`    TEXT            DEFAULT NULL,
    `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_blogs_slug` (`slug`),
    KEY `idx_blogs_category` (`category_id`),
    KEY `idx_blogs_is_featured` (`is_featured`),
    KEY `idx_blogs_is_active` (`is_active`),
    KEY `idx_blogs_created_at` (`created_at`),
    CONSTRAINT `fk_blogs_category`
        FOREIGN KEY (`category_id`) REFERENCES `blog_categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── FAQs ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `faqs` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `question`     VARCHAR(500)    NOT NULL,
    `answer`       TEXT            NOT NULL,
    `sort_order`   INT             NOT NULL DEFAULT 0,
    `is_active`    TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_faqs_sort_order` (`sort_order`),
    KEY `idx_faqs_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Newsletter Subscribers ───────────────────────────────────
CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `email`        VARCHAR(191)    NOT NULL,
    `is_active`    TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_newsletter_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Contact Messages ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `contact_messages` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`         VARCHAR(150)    NOT NULL,
    `email`        VARCHAR(191)    NOT NULL,
    `mobile`       VARCHAR(20)     DEFAULT NULL,
    `subject`      VARCHAR(255)    NOT NULL,
    `message`      TEXT            NOT NULL,
    `is_read`      TINYINT(1)      NOT NULL DEFAULT 0,
    `ip_address`   VARCHAR(45)     DEFAULT NULL,
    `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_contact_is_read` (`is_read`),
    KEY `idx_contact_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Site Settings (key-value) ────────────────────────────────
CREATE TABLE IF NOT EXISTS `site_settings` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `setting_key`  VARCHAR(100)    NOT NULL,
    `setting_value` TEXT           DEFAULT NULL,
    `updated_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_settings_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Homepage Content ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `homepage_content` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `section`      VARCHAR(100)    NOT NULL,
    `item_key`     VARCHAR(100)    NOT NULL,
    `item_value`   TEXT            DEFAULT NULL,
    `updated_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_homepage_section_key` (`section`, `item_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Site Typography ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `site_typography` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `prop_name`    VARCHAR(100)    NOT NULL COMMENT 'CSS custom property name e.g. --font-size-h1',
    `prop_value`   VARCHAR(50)     NOT NULL COMMENT 'Value e.g. 42px, 1.6, Inter',
    `label`        VARCHAR(150)    NOT NULL COMMENT 'Human-readable label for admin UI',
    `updated_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_typography_prop` (`prop_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Hero Slides ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `hero_slides` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `image`        VARCHAR(255)    NOT NULL,
    `title`        VARCHAR(300)    DEFAULT NULL,
    `subtitle`     TEXT            DEFAULT NULL,
    `btn_text`     VARCHAR(100)    DEFAULT NULL,
    `btn_url`      VARCHAR(500)    DEFAULT NULL,
    `sort_order`   INT             NOT NULL DEFAULT 0,
    `is_active`    TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_hero_sort_order` (`sort_order`),
    KEY `idx_hero_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED DATA
-- ============================================================

-- ── Default Admin ────────────────────────────────────────────
-- Password: Admin@1234  (bcrypt hash)
INSERT INTO `admins` (`name`, `email`, `password`) VALUES
('সুপার অ্যাডমিন', 'admin@farmersbd.com', '$2y$12$ZZ.gE2XJpr9XSuzN/.cBoeN9AYCF5RRG.mlgUsz2QnO1XoJZjo0dO');

-- ── Site Settings ────────────────────────────────────────────
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('site_name',         'FarmersBD'),
('site_tagline',      'স্মার্ট মাছ চাষের আধুনিক সমাধান'),
('site_logo',         ''),
('site_favicon',      ''),
('site_phone',        '01979-606212'),
('site_email',        'Info.Farmersbd@gmail.com'),
('site_address',      'ঢাকা, বাংলাদেশ'),
('facebook_url',      'https://www.facebook.com/share/1YaDdNybXm/'),
('youtube_url',       ''),
('twitter_url',       ''),
('instagram_url',     ''),
('footer_text',       '© FarmersBD। সর্বস্বত্ব সংরক্ষিত।'),
('cod_enabled',       '1'),
('sslcommerz_enabled','1'),
('shipping_cost',     '60'),
('meta_title',        'FarmersBD - স্মার্ট মাছ চাষের আধুনিক সমাধান'),
('meta_description',  'FarmersBD - বাংলাদেশের সেরা স্মার্ট ফিশ ফার্মিং প্ল্যাটফর্ম। AI রোগ নির্ণয়, অ্যাকোয়া মেডিসিন, বিশেষজ্ঞ পরামর্শ।'),
('meta_keywords',     'মাছ চাষ, মাছের রোগ, aqua medicine, fish farming, bangladesh'),
('og_image',          ''),
('google_analytics',  '');

-- ── Homepage Content ─────────────────────────────────────────
INSERT INTO `homepage_content` (`section`, `item_key`, `item_value`) VALUES
('services', 'title', 'আমাদের সেবাসমূহ'),
('services', 'subtitle', 'মাছ চাষকে আরও সহজ ও লাভজনক করতে আমাদের সকল সেবা'),
('fish', 'title', 'জনপ্রিয় মাছ'),
('fish', 'subtitle', 'বাংলাদেশের সবচেয়ে জনপ্রিয় চাষযোগ্য মাছের তথ্য'),
('ai', 'title', 'AI দিয়ে মাছের রোগ নির্ণয় করুন'),
('ai', 'subtitle', 'আপনার মাছের ছবি আপলোড করুন এবং কৃত্রিম বুদ্ধিমত্তার সাহায্যে তাৎক্ষণিক রোগ নির্ণয় করুন।'),
('ai', 'btn_text', 'AI দিয়ে রোগ নির্ণয় করুন'),
('pharmacy', 'title', 'অ্যাকোয়া ফার্মেসি'),
('pharmacy', 'subtitle', 'মাছের সকল ওষুধ ও স্বাস্থ্য পণ্য এক জায়গায়'),
('products', 'title', 'বিশেষ পণ্যসমূহ'),
('products', 'subtitle', 'আমাদের বিশেষভাবে বাছাই করা সেরা পণ্যগুলো দেখুন'),
('blogs', 'title', 'সাম্প্রতিক ব্লগ'),
('blogs', 'subtitle', 'মাছ চাষ সম্পর্কিত সর্বশেষ তথ্য ও টিপস'),
('why_us', 'title', 'কেন FarmersBD বেছে নেবেন?'),
('why_us', 'subtitle', 'আমরা মাছ চাষীদের জন্য সর্বোচ্চ মানের সেবা নিশ্চিত করি'),
('why_us', 'point1', 'বিশেষজ্ঞ পরামর্শদাতা দল'),
('why_us', 'point2', 'AI-চালিত রোগ নির্ণয় ব্যবস্থা'),
('why_us', 'point3', 'সার্বক্ষণিক গ্রাহক সেবা'),
('why_us', 'point4', 'দ্রুত ও নিরাপদ ডেলিভারি'),
('why_us', 'point5', 'বিশ্বস্ত ও মানসম্পন্ন পণ্য'),
('why_us', 'point6', 'সাশ্রয়ী মূল্যে সেরা সেবা'),
('consultation', 'title', 'বিশেষজ্ঞ পরামর্শ নিন'),
('consultation', 'subtitle', 'আমাদের অভিজ্ঞ বিশেষজ্ঞ দলের সাথে যোগাযোগ করুন'),
('consultation', 'btn_text', 'পরামর্শ নিন'),
('newsletter', 'title', 'নিউজলেটার সাবস্ক্রাইব করুন'),
('newsletter', 'subtitle', 'সর্বশেষ তথ্য, টিপস এবং অফার পেতে আমাদের নিউজলেটার সাবস্ক্রাইব করুন।');

-- ── Site Typography ───────────────────────────────────────────
INSERT INTO `site_typography` (`prop_name`, `prop_value`, `label`) VALUES
('--font-size-h1',          '2.5rem',    'H1 ফন্ট সাইজ'),
('--font-size-h2',          '2rem',      'H2 ফন্ট সাইজ'),
('--font-size-h3',          '1.5rem',    'H3 ফন্ট সাইজ'),
('--font-size-h4',          '1.25rem',   'H4 ফন্ট সাইজ'),
('--font-size-body',        '1rem',      'বডি ফন্ট সাইজ'),
('--font-size-navbar',      '0.95rem',   'নেভবার ফন্ট সাইজ'),
('--font-size-btn',         '0.9rem',    'বাটন ফন্ট সাইজ'),
('--font-size-footer',      '0.9rem',    'ফুটার ফন্ট সাইজ'),
('--font-size-card-title',  '1.1rem',    'কার্ড শিরোনাম ফন্ট সাইজ'),
('--font-size-card-desc',   '0.875rem',  'কার্ড বিবরণ ফন্ট সাইজ'),
('--font-size-small',       '0.8rem',    'ছোট টেক্সট ফন্ট সাইজ'),
('--font-family-primary',   '"Hind Siliguri", "Noto Sans Bengali", sans-serif', 'প্রাথমিক ফন্ট'),
('--line-height-base',      '1.7',       'লাইন হাইট'),
('--font-weight-normal',    '400',       'নরমাল ফন্ট ওয়েট'),
('--font-weight-medium',    '500',       'মিডিয়াম ফন্ট ওয়েট'),
('--font-weight-bold',      '700',       'বোল্ড ফন্ট ওয়েট');

-- ── Hero Slides ──────────────────────────────────────────────
INSERT INTO `hero_slides` (`image`, `title`, `subtitle`, `btn_text`, `btn_url`, `sort_order`, `is_active`) VALUES
('assets/images/hero/slide1.jpg',
 'মাছ চাষে আধুনিক প্রযুক্তির ব্যবহার',
 'সঠিক রোগ নির্ণয় ও ব্যবস্থাপনার মাধ্যমে আপনার মাছের উৎপাদন বাড়ান।',
 'AI দিয়ে রোগ নির্ণয় করুন', '/farmersbd/ai/', 1, 1),

('assets/images/hero/slide2.jpg',
 'সেরা অ্যাকোয়া মেডিসিন এখন আপনার হাতের মুঠোয়',
 'বিশ্বস্ত ব্র্যান্ডের সকল মাছের ওষুধ ও স্বাস্থ্য পণ্য অর্ডার করুন।',
 'এখনই কিনুন', '/farmersbd/products/', 2, 1),

('assets/images/hero/slide3.jpg',
 'বিশেষজ্ঞ পরামর্শ নিন যেকোনো সমস্যায়',
 'আমাদের অভিজ্ঞ মৎস্য বিশেষজ্ঞ দল আপনার পাশে আছেন সবসময়।',
 'পরামর্শ নিন', '/farmersbd/consultation/create.php', 3, 1);

-- ── Product Categories ────────────────────────────────────────
INSERT INTO `product_categories` (`name`, `slug`, `description`, `sort_order`) VALUES
('রোগের চিকিৎসা',    'disease-treatment',  'মাছের বিভিন্ন রোগের চিকিৎসায় ব্যবহৃত ওষুধ',    1),
('গ্রোথ বুস্টার',    'growth-booster',     'মাছের দ্রুত বৃদ্ধির জন্য পুষ্টিকর সম্পূরক',     2),
('ওয়াটার ট্রিটমেন্ট', 'water-treatment',  'পুকুরের পানির মান উন্নয়নে ব্যবহৃত পণ্য',        3),
('ভিটামিন ও মিনারেল', 'vitamins-minerals', 'মাছের স্বাস্থ্য রক্ষায় ভিটামিন ও খনিজ পদার্থ', 4),
('অন্যান্য পণ্য',    'other-products',     'অন্যান্য অ্যাকোয়া পণ্য ও সরঞ্জাম',              5);

-- ── Products ─────────────────────────────────────────────────
INSERT INTO `products` (`category_id`, `name`, `slug`, `sku`, `description`, `price`, `discount_price`, `stock`, `is_featured`, `is_active`) VALUES
(1, 'অ্যান্টিবায়োটিক ফিশ মেড', 'antibiotic-fish-med', 'AFM-001',
 'মাছের ব্যাকটেরিয়াজনিত রোগ নিরাময়ে কার্যকর অ্যান্টিবায়োটিক। ব্যাকটেরিয়াল হেমোরেজিক সেপ্টিসেমিয়া, ফিন রট, এবং টেইল রট রোগে ব্যবহার্য।',
 250.00, 220.00, 100, 1, 1),

(1, 'ফাঙ্গাস নাশক পাউডার', 'fungus-powder', 'FNP-002',
 'মাছের ছত্রাকজনিত রোগ প্রতিরোধ ও নিরাময়ে কার্যকর। স্যাপ্রোলেগনিয়া ও অন্যান্য ছত্রাক সংক্রমণে ব্যবহার্য।',
 180.00, NULL, 75, 1, 1),

(1, 'প্যারাসাইট কিলার লিকুইড', 'parasite-killer', 'PKL-003',
 'মাছের পরজীবী রোগ নির্মূলে শক্তিশালী তরল ওষুধ। আইকথায়োফথিরিয়াস, কস্টিয়া, ট্রাইকোডিনা রোগে কার্যকর।',
 320.00, 290.00, 50, 0, 1),

(2, 'প্রোবায়োটিক গ্রোথ প্লাস', 'probiotic-growth-plus', 'PGP-004',
 'মাছের দ্রুত বৃদ্ধির জন্য উন্নত প্রোবায়োটিক সম্পূরক। হজম শক্তি বাড়ায় ও রোগ প্রতিরোধ ক্ষমতা উন্নত করে।',
 450.00, 400.00, 120, 1, 1),

(2, 'ফিশ গ্রো ম্যাক্স', 'fish-grow-max', 'FGM-005',
 'মাছের ওজন ও আকার দ্রুত বাড়াতে বিশেষ খাদ্য সম্পূরক। প্রোটিন, অ্যামিনো এসিড ও বিশেষ এনজাইমের সমন্বয়।',
 550.00, NULL, 80, 1, 1),

(3, 'পুকুর প্রস্তুতি কিট', 'pond-preparation-kit', 'PPK-006',
 'পুকুর প্রস্তুতির জন্য সম্পূর্ণ কিট। চুন, সার ও জীবাণুনাশক একসাথে। ১ বিঘা পুকুরের জন্য যথেষ্ট।',
 650.00, 600.00, 45, 0, 1),

(3, 'অ্যালগি কন্ট্রোল প্লাস', 'algae-control-plus', 'ACP-007',
 'পুকুরে অতিরিক্ত শেওলা নিয়ন্ত্রণে কার্যকর পণ্য। মাছের জন্য নিরাপদ ও পরিবেশবান্ধব।',
 280.00, NULL, 60, 0, 1),

(3, 'অ্যামোনিয়া রিমুভার', 'ammonia-remover', 'AMR-008',
 'পুকুরের পানিতে অতিরিক্ত অ্যামোনিয়া দূর করে। পানির pH ব্যালেন্স রক্ষা করে।',
 350.00, 320.00, 40, 0, 1),

(4, 'ফিশ ভিটামিন সি প্লাস', 'fish-vitamin-c', 'FVC-009',
 'মাছের রোগ প্রতিরোধ ক্ষমতা বাড়াতে ভিটামিন সি ও মাল্টিভিটামিন কমপ্লেক্স।',
 220.00, 200.00, 150, 1, 1),

(4, 'ক্যালসিয়াম মিনারেল মিক্স', 'calcium-mineral-mix', 'CMM-010',
 'মাছের হাড় ও শরীর গঠনে প্রয়োজনীয় ক্যালসিয়াম ও খনিজ পদার্থের সমন্বয়।',
 190.00, NULL, 90, 0, 1),

(1, 'ওরাল রিহাইড্রেশন সল্ট ফর ফিশ', 'oral-rehydration-fish', 'ORF-011',
 'মাছের পানি স্বল্পতা ও ইলেক্ট্রোলাইট ভারসাম্য রক্ষায় বিশেষ ওষুধ।',
 160.00, NULL, 200, 0, 1),

(2, 'অর্গানিক ফিড অ্যাডিটিভ', 'organic-feed-additive', 'OFA-012',
 'মাছের খাবারের সাথে মেশানো প্রাকৃতিক উপাদান। হজম ক্ষমতা ও পুষ্টি শোষণ বাড়ায়।',
 380.00, 350.00, 70, 0, 1),

(3, 'বায়ো ফিলটার মিডিয়া', 'bio-filter-media', 'BFM-013',
 'পুকুর ও ট্যাংকের পানি বিশুদ্ধকরণে জৈব ফিল্টার মিডিয়া। ক্ষতিকর ব্যাকটেরিয়া নাশ করে।',
 420.00, NULL, 35, 0, 1),

(4, 'মাল্টিভিটামিন ড্রপস', 'multivitamin-drops', 'MVD-014',
 'মাছের সামগ্রিক স্বাস্থ্য রক্ষায় তরল মাল্টিভিটামিন। ১০০ মিলি বোতলে।',
 250.00, 230.00, 85, 1, 1),

(5, 'ফিশ নেট প্রো', 'fish-net-pro', 'FNP-015',
 'উচ্চমানের ডুরেবল ফিশিং নেট। মাছ ধরা, পরীক্ষা ও স্থানান্তরের জন্য।',
 750.00, 700.00, 25, 0, 1);

-- ── Popular Fish ─────────────────────────────────────────────
INSERT INTO `fish` (`name`, `slug`, `description`, `habitat`, `farming_tips`, `sort_order`) VALUES
('রুই মাছ', 'rui', 'রুই বাংলাদেশের সবচেয়ে জনপ্রিয় মিঠা পানির মাছ। এটি প্রোটিনসমৃদ্ধ এবং সারা দেশে ব্যাপকভাবে চাষ করা হয়।', 'মিঠা পানির পুকুর, নদী ও জলাশয়।', 'রুই মাছ চাষে ভালো পরিণত ফলনের জন্য পুকুরে পর্যাপ্ত অক্সিজেন ও সুষম খাবার নিশ্চিত করুন।', 1),
('কাতলা মাছ', 'katla', 'কাতলা একটি দ্রুত বর্ধনশীল মাছ। বাংলাদেশে এটি একটি জনপ্রিয় চাষকৃত মাছ।', 'বড় পুকুর, নদী ও হাওর।', 'কাতলা মাছ পানির উপরিভাগে খায়, তাই ভাসমান খাবার দিন।', 2),
('তেলাপিয়া', 'tilapia', 'তেলাপিয়া একটি অত্যন্ত দ্রুত বর্ধনশীল ও রোগ প্রতিরোধক্ষম মাছ। স্বল্প খরচে অধিক উৎপাদন সম্ভব।', 'পুকুর, ডোবা, ধানক্ষেত ও যেকোনো জলাশয়।', 'তেলাপিয়া কম যত্নে বেশি উৎপাদন দেয়। ঘন চাষে ভালো ফলন পাওয়া যায়।', 3),
('পাঙ্গাস মাছ', 'pangash', 'পাঙ্গাস বাণিজ্যিকভাবে সবচেয়ে বেশি চাষকৃত মাছগুলোর একটি। এর চাষ অত্যন্ত লাভজনক।', 'বড় পুকুর ও বিলে।', 'পাঙ্গাস চাষে নিয়মিত পানি পরিবর্তন ও বায়ু সরবরাহ নিশ্চিত করুন।', 4),
('কই মাছ', 'koi', 'কই একটি দেশীয় মাছ যা কম অক্সিজেনেও বাঁচতে পারে। পুষ্টিগুণে ভরপুর।', 'ছোট পুকুর, খাল ও বিভিন্ন জলাশয়।', 'কই মাছ ঘন চাষে অত্যন্ত ভালো ফলন দেয়।', 5),
('শিং মাছ', 'shing', 'শিং একটি জনপ্রিয় দেশীয় মাছ। এর বাজারমূল্য অনেক বেশি এবং চাষাবাদ লাভজনক।', 'পুকুর, নদী ও জলাভূমি।', 'শিং মাছ রাতে বেশি খায়, তাই রাতে খাবার দেওয়া ভালো।', 6),
('মাগুর মাছ', 'magur', 'মাগুর একটি পুষ্টিগুণসম্পন্ন মাছ যা রোগীদের জন্য বিশেষভাবে উপকারী।', 'পুকুর, খাল, বিল ও ধানক্ষেত।', 'মাগুর মাছ ঘন চাষে ভালো ফলন দেয়। নিয়মিত খাবার ও পানির গুণমান পরীক্ষা করুন।', 7),
('গলদা চিংড়ি', 'golda-chingri', 'গলদা চিংড়ি একটি উচ্চমূল্যের রপ্তানিযোগ্য মাছ। এর চাষ অত্যন্ত লাভজনক।', 'লোনা ও মিষ্টি মিশ্রিত পানির পুকুর ও নদী।', 'চিংড়ি চাষে পানির লবণাক্ততা ও তাপমাত্রা নিয়ন্ত্রণ অত্যন্ত গুরুত্বপূর্ণ।', 8);

-- ── Diseases ─────────────────────────────────────────────────
INSERT INTO `diseases` (`name`, `slug`, `description`, `symptoms`, `causes`, `prevention`, `treatment`, `affected_fish`, `ai_label`) VALUES
(
 'ব্যাকটেরিয়াল হেমোরেজিক সেপ্টিসেমিয়া',
 'bacterial-hemorrhagic-septicemia',
 'এটি মাছের একটি মারাত্মক ব্যাকটেরিয়াজনিত রোগ। Aeromonas hydrophila ব্যাকটেরিয়া এই রোগের প্রধান কারণ।',
 'শরীরে লাল দাগ, আঁশ উঠে যাওয়া, পেট ফোলা, চোখ ফোলা, মাছ মরে যাওয়া।',
 'পুকুরের অতিরিক্ত জৈব পদার্থ, পানির দূষণ, অক্সিজেনের অভাব, অতিরিক্ত ঘনত্বে মাছ চাষ।',
 'পুকুরের পানি পরিষ্কার রাখুন, নিয়মিত চুন প্রয়োগ করুন, অক্সিজেন সরবরাহ নিশ্চিত করুন।',
 'অ্যান্টিবায়োটিক (Oxytetracycline) প্রয়োগ করুন। আক্রান্ত মাছ সরিয়ে ফেলুন। পুকুরে চুন প্রয়োগ করুন।',
 'রুই, কাতলা, পাঙ্গাস, তেলাপিয়া',
 'bacterial_hemorrhagic_septicemia'
),
(
 'ফিন রট ও টেইল রট',
 'fin-rot-tail-rot',
 'এই রোগে মাছের পাখনা ও লেজ পচে যায়। ব্যাকটেরিয়া ও ছত্রাকের কারণে হতে পারে।',
 'পাখনা ও লেজের কিনারা সাদা বা ধূসর হয়ে যাওয়া, ধীরে ধীরে পচে যাওয়া, মাছের দুর্বলতা।',
 'পানির দুর্বল মান, আঘাত, উচ্চ ব্যাকটেরিয়া লোড, দুর্বল পুষ্টি।',
 'পানির মান ভালো রাখুন, মাছ যেন আঘাত না পায় তা নিশ্চিত করুন।',
 'Potassium permanganate বা নুন দিয়ে স্নান করান। অ্যান্টিবায়োটিক প্রয়োগ করুন।',
 'সব ধরনের মাছ',
 'fin_rot'
),
(
 'সাদা দাগ রোগ (White Spot)',
 'white-spot-ich',
 'এটি Ichthyophthirius multifiliis পরজীবী দ্বারা সৃষ্ট একটি সাধারণ মাছের রোগ।',
 'শরীরে সাদা সাদা দাগ বা বিন্দু, ত্বক ঘষে ঘষে চলা, শ্বাসকষ্ট, ক্ষুধামন্দা।',
 'দূষিত পানি, নতুন মাছের মাধ্যমে সংক্রমণ, ঠান্ডা পানি।',
 'নতুন মাছ কোয়ারেন্টাইনে রাখুন, পানির তাপমাত্রা স্থিতিশীল রাখুন।',
 'তাপমাত্রা ধীরে ধীরে বাড়ান (28-30°C)। Formalin বা Malachite Green প্রয়োগ করুন।',
 'সব ধরনের মাছ',
 'white_spot_ich'
),
(
 'ছত্রাক সংক্রমণ (Saprolegnia)',
 'saprolegnia-fungus',
 'Saprolegnia ছত্রাক দ্বারা সৃষ্ট রোগ। সাধারণত আহত বা দুর্বল মাছে আক্রমণ করে।',
 'শরীরে সুতোর মতো সাদা বা ধূসর ছত্রাক বৃদ্ধি, আঁশ ক্ষয়, আঘাতের স্থানে সংক্রমণ।',
 'ঠান্ডা পানি, আহত মাছ, দুর্বল রোগ প্রতিরোধ ক্ষমতা।',
 'মাছের আঘাত প্রতিরোধ করুন, পানির গুণমান বজায় রাখুন।',
 'Potassium permanganate বা ফাঙ্গাস-নাশক ওষুধ প্রয়োগ করুন।',
 'রুই, কাতলা, সব ধরনের মাছ',
 'saprolegnia_fungus'
),
(
 'ড্রপসি (Dropsy / পেট ফোলা)',
 'dropsy',
 'ড্রপসি মাছের একটি সাধারণ রোগ যেখানে মাছের পেট অস্বাভাবিকভাবে ফুলে যায়।',
 'পেট অস্বাভাবিকভাবে ফুলে যাওয়া, আঁশ পাখার মতো খাড়া হয়ে যাওয়া, মাছের নড়াচড়া কমে যাওয়া।',
 'ব্যাকটেরিয়া সংক্রমণ, কিডনির সমস্যা, পানির দুর্বল মান।',
 'পানির মান নিয়মিত পরীক্ষা করুন, পুকুরে অতিরিক্ত মাছ না রাখুন।',
 'তেমন কার্যকর চিকিৎসা নেই। আক্রান্ত মাছ আলাদা করুন। অ্যান্টিবায়োটিক চেষ্টা করা যায়।',
 'সব ধরনের মাছ',
 'dropsy'
),
(
 'কলামনারিস রোগ',
 'columnaris',
 'Flavobacterium columnare ব্যাকটেরিয়া দ্বারা সৃষ্ট রোগ। দ্রুত ছড়িয়ে পড়ে।',
 'শরীরে সাদা বা ধূসর আবরণ, পাখনা নষ্ট হওয়া, কানকো পচে যাওয়া।',
 'উচ্চ তাপমাত্রা, অপর্যাপ্ত অক্সিজেন, দুর্বল পানির মান।',
 'পানির তাপমাত্রা নিয়ন্ত্রণে রাখুন, পর্যাপ্ত অক্সিজেন নিশ্চিত করুন।',
 'লবণ স্নান, Oxytetracycline বা Erythromycin প্রয়োগ করুন।',
 'সব ধরনের মাছ, বিশেষত রুই ও কাতলা',
 'columnaris'
),
(
 'অ্যাঙ্কর ওয়ার্ম (Anchor Worm)',
 'anchor-worm',
 'Lernaea ক্রাস্টাসিয়ান পরজীবী দ্বারা সৃষ্ট রোগ। মাছের চামড়ায় আটকে থাকে।',
 'মাছের শরীরে দৃশ্যমান কৃমি, লাল ক্ষত, মাছের অস্বস্তি ও ঘষাঘষি।',
 'দূষিত পানি, পুকুরে উপদ্রুত মাছের সংযোজন।',
 'নিয়মিত পানি পরিবর্তন, নতুন মাছ কোয়ারেন্টাইনে রাখুন।',
 'Trichlorfon বা Dimilin প্রয়োগ করুন। পরজীবী টুইজার দিয়ে সরান।',
 'রুই, কাতলা, কার্প জাতীয় মাছ',
 'anchor_worm'
),
(
 'পপ আই (Pop Eye / চোখ ফোলা)',
 'pop-eye',
 'মাছের চোখ অস্বাভাবিকভাবে ফুলে বাইরে বেরিয়ে আসে।',
 'এক বা উভয় চোখ ফুলে যাওয়া, চোখের চারপাশে রক্তক্ষরণ।',
 'ব্যাকটেরিয়া সংক্রমণ, আঘাত, পানির দুর্বল মান।',
 'পানির গুণমান বজায় রাখুন, মাছের আঘাত এড়ান।',
 'Kanamycin বা Ampicillin অ্যান্টিবায়োটিক প্রয়োগ করুন। পানি পরিবর্তন করুন।',
 'সব ধরনের মাছ',
 'pop_eye'
),
(
 'গিল রট (Gill Rot)',
 'gill-rot',
 'মাছের ফুলকা পচে যাওয়ার রোগ। শ্বাস-প্রশ্বাসে সমস্যা হয়।',
 'মাছ পানির উপরে ভেসে শ্বাস নেওয়া, ফুলকা বাদামী বা ধূসর হয়ে যাওয়া।',
 'পানিতে অতিরিক্ত জৈব পদার্থ, কম অক্সিজেন, ব্যাকটেরিয়া বা ছত্রাক।',
 'পানির মান নিয়মিত পরীক্ষা করুন, পর্যাপ্ত বায়ু সরবরাহ নিশ্চিত করুন।',
 'Potassium permanganate স্নান, চুন প্রয়োগ, অ্যান্টিফাঙ্গাল ওষুধ।',
 'রুই, কাতলা, পাঙ্গাস',
 'gill_rot'
),
(
 'EUS (Epizootic Ulcerative Syndrome)',
 'eus-epizootic-ulcerative',
 'EUS একটি ছত্রাকজনিত রোগ যা বাংলাদেশে মাছের মহামারি ঘটায়।',
 'শরীরে গভীর লাল ঘা বা আলসার, আঁশ উঠে যাওয়া, মাছের নিষ্ক্রিয়তা।',
 'Aphanomyces invadans ছত্রাক, বন্যার পর পানির দ্রুত পরিবর্তন।',
 'বন্যার পর পুকুরে চুন প্রয়োগ করুন, মাছের ঘনত্ব নিয়ন্ত্রণ করুন।',
 'চুন প্রয়োগ (200 kg/হেক্টর), Formalin বা Copper sulphate ব্যবহার।',
 'রুই, কাতলা, মৃগেল, সব দেশীয় মাছ',
 'eus_syndrome'
);

-- ── Blog Categories ───────────────────────────────────────────
INSERT INTO `blog_categories` (`name`, `slug`) VALUES
('রোগ ও চিকিৎসা', 'disease-treatment'),
('মাছ চাষ পদ্ধতি', 'farming-methods'),
('পুকুর ব্যবস্থাপনা', 'pond-management'),
('পুষ্টি ও খাদ্য', 'nutrition-food'),
('বাজার ও ব্যবসা', 'market-business');

-- ── Blog Posts ────────────────────────────────────────────────
INSERT INTO `blogs` (`category_id`, `title`, `slug`, `excerpt`, `content`, `is_featured`, `is_active`) VALUES
(
 1,
 'বর্ষাকালে মাছের রোগ ও প্রতিকার',
 'borshakal-macher-rog-o-protikar',
 'বর্ষাকালে পানির গুণমান দ্রুত পরিবর্তন হওয়ার কারণে মাছের রোগবালাই বেড়ে যায়। এই সময়ে সঠিক পরিচর্যাই পারে আপনার মাছকে রক্ষা করতে।',
 '<p>বর্ষাকাল মাছ চাষীদের জন্য একটি চ্যালেঞ্জিং সময়। এই সময়ে পানির তাপমাত্রা, pH এবং অক্সিজেনের মাত্রায় দ্রুত পরিবর্তন হয়, যা মাছকে রোগপ্রবণ করে তোলে।</p><h3>সাধারণ রোগগুলো</h3><ul><li>EUS বা আলসার রোগ</li><li>ব্যাকটেরিয়াল হেমোরেজিক সেপ্টিসেমিয়া</li><li>ছত্রাক সংক্রমণ</li></ul><h3>প্রতিরোধমূলক ব্যবস্থা</h3><p>বর্ষার আগে পুকুরে চুন প্রয়োগ করুন। নিয়মিত পানির মান পরীক্ষা করুন। প্রয়োজনে বিশেষজ্ঞের পরামর্শ নিন।</p>',
 1, 1
),
(
 3,
 'পুকুর প্রস্তুতির সঠিক নিয়ম',
 'pukur-prostutir-sothik-niom',
 'মাছ চাষে সাফল্যের প্রথম ধাপ হলো পুকুর সঠিকভাবে প্রস্তুত করা। সঠিক পুকুর প্রস্তুতি মাছের বৃদ্ধি ও স্বাস্থ্য নিশ্চিত করে।',
 '<p>একটি সফল মাছ চাষের জন্য পুকুর প্রস্তুতি অত্যন্ত গুরুত্বপূর্ণ। সঠিকভাবে পুকুর প্রস্তুত না করলে মাছের বৃদ্ধি বাধাগ্রস্ত হয় এবং রোগবালাই বাড়ে।</p><h3>পুকুর শুকানো</h3><p>প্রথমে পুকুরের পানি সম্পূর্ণ নামিয়ে তলদেশ শুকান।</p><h3>চুন প্রয়োগ</h3><p>প্রতি শতকে 1-2 কেজি চুন প্রয়োগ করুন।</p><h3>সার প্রয়োগ</h3><p>জৈব ও অজৈব সার প্রয়োগ করে প্রাকৃতিক খাদ্য তৈরি করুন।</p>',
 1, 1
),
(
 2,
 'মাছের খাদ্য ব্যবস্থাপনা',
 'macher-khaddo-byavasthapona',
 'মাছের সঠিক খাদ্য ব্যবস্থাপনা উৎপাদন খরচ কমিয়ে মুনাফা বাড়ায়। কোন মাছকে কতটুকু ও কখন খাওয়াবেন তা জানুন।',
 '<p>মাছের সঠিক পরিমাণে খাবার দেওয়া মাছ চাষের একটি গুরুত্বপূর্ণ দিক। অতিরিক্ত খাবার পানিকে দূষিত করে এবং কম খাবার মাছের বৃদ্ধি বাধা দেয়।</p><h3>খাবারের পরিমাণ</h3><p>মাছের মোট ওজনের ২-৩% পরিমাণ খাবার দিন।</p><h3>খাওয়ানোর সময়</h3><p>সকাল ও বিকালে দুইবার খাবার দেওয়া সবচেয়ে ভালো।</p>',
 0, 1
),
(
 2,
 'উৎপাদন বাড়ানোর কৌশল',
 'utpadan-baranor-kaushol',
 'সঠিক প্রযুক্তি ও পদ্ধতি ব্যবহার করে মাছের উৎপাদন দ্বিগুণ বা তিনগুণ করা সম্ভব। জানুন আধুনিক মাছ চাষের কৌশল।',
 '<p>আধুনিক প্রযুক্তি ব্যবহার করে মাছের উৎপাদন উল্লেখযোগ্যভাবে বাড়ানো সম্ভব। সঠিক পদ্ধতি অনুসরণ করলে একই জায়গায় দ্বিগুণ উৎপাদন পাওয়া যায়।</p><h3>উন্নত জাত ব্যবহার</h3><p>উন্নত জাতের মাছের পোনা ব্যবহার করুন।</p><h3>বায়ু সরবরাহ</h3><p>পুকুরে পর্যাপ্ত বায়ু সরবরাহ নিশ্চিত করতে এয়ারেটর ব্যবহার করুন।</p>',
 0, 1
),
(
 3,
 'শীতকালীন মাছ চাষ ব্যবস্থাপনা',
 'shitkalin-mach-chas-byavasthapona',
 'শীতকালে মাছের বৃদ্ধি ধীর হয়ে পড়ে এবং রোগের ঝুঁকি বাড়ে। সঠিক শীতকালীন ব্যবস্থাপনায় মাছকে সুস্থ রাখুন।',
 '<p>শীতকালে পানির তাপমাত্রা কমে যায় এবং মাছের বিপাক ক্রিয়া কমে যায়। এই সময়ে বিশেষ যত্ন না নিলে মাছের স্বাস্থ্য ক্ষতিগ্রস্ত হতে পারে।</p><h3>খাবার কমানো</h3><p>শীতকালে মাছের খাবার চাহিদা কমে, তাই খাবারের পরিমাণ কমিয়ে দিন।</p><h3>পানির গভীরতা বাড়ান</h3><p>ঠান্ডা থেকে রক্ষা পেতে পানির গভীরতা বাড়ান।</p>',
 1, 1
);

-- ── FAQs ─────────────────────────────────────────────────────
INSERT INTO `faqs` (`question`, `answer`, `sort_order`) VALUES
('FarmersBD কী?', 'FarmersBD হলো বাংলাদেশের একটি স্মার্ট ফিশ ফার্মিং প্ল্যাটফর্ম যেখানে AI-চালিত মাছের রোগ নির্ণয়, অ্যাকোয়া মেডিসিন ক্রয়, বিশেষজ্ঞ পরামর্শ এবং মাছ চাষ সম্পর্কিত সকল তথ্য পাওয়া যায়।', 1),
('AI রোগ নির্ণয় কীভাবে কাজ করে?', 'আপনার মাছের ছবি আপলোড করুন। আমাদের AI সিস্টেম ছবি বিশ্লেষণ করে রোগ নির্ণয় করবে এবং সম্ভাব্য চিকিৎসার পরামর্শ দেবে।', 2),
('কীভাবে পণ্য অর্ডার করবেন?', 'পণ্য নির্বাচন করুন → কার্টে যোগ করুন → চেকআউটে যান → ডেলিভারি তথ্য দিন → পেমেন্ট করুন → অর্ডার নিশ্চিত।', 3),
('কোন পেমেন্ট পদ্ধতি সমর্থিত?', 'ক্যাশ অন ডেলিভারি এবং SSLCommerz (বিকাশ, নগদ, ডেবিট/ক্রেডিট কার্ড) সমর্থিত।', 4),
('ডেলিভারি কতদিনে পাবেন?', 'ঢাকার মধ্যে ১-২ কার্যদিবস এবং ঢাকার বাইরে ৩-৫ কার্যদিবসে ডেলিভারি দেওয়া হয়।', 5),
('পরামর্শ সেবা কীভাবে নেবেন?', 'আপনার সমস্যার বিবরণ দিয়ে পরামর্শ ফর্ম পূরণ করুন। আমাদের বিশেষজ্ঞ দল ২৪-৪৮ ঘণ্টার মধ্যে উত্তর দেবেন।', 6),
('অর্ডার বাতিল করা যাবে কি?', 'হ্যাঁ, অর্ডার শিপড হওয়ার আগে বাতিল করা যাবে। অ্যাকাউন্টে লগইন করে অর্ডার বিস্তারিত থেকে বাতিল করুন অথবা আমাদের সাথে যোগাযোগ করুন।', 7),
('রিটার্ন পলিসি কী?', 'পণ্য পাওয়ার ৭ দিনের মধ্যে ক্ষতিগ্রস্ত বা ভুল পণ্য ফেরত দেওয়া যাবে। আমাদের গ্রাহক সেবা দলের সাথে যোগাযোগ করুন।', 8);

SET foreign_key_checks = 1;
