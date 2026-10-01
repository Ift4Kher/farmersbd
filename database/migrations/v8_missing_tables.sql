-- ============================================================
-- FarmersBD V8 — Missing Tables Migration
-- Run this to create all tables needed by new modules
-- ============================================================
SET NAMES 'utf8mb4';
SET foreign_key_checks = 0;

-- ── Combos ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `combos` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`           VARCHAR(255) NOT NULL,
    `slug`           VARCHAR(280) NOT NULL,
    `description`    TEXT DEFAULT NULL,
    `image`          VARCHAR(255) DEFAULT NULL,
    `original_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `combo_price`    DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `discount_pct`   DECIMAL(5,2) DEFAULT NULL,
    `free_delivery`  TINYINT(1) NOT NULL DEFAULT 0,
    `is_active`      TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order`     INT NOT NULL DEFAULT 0,
    `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_combos_slug` (`slug`),
    KEY `idx_combos_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Combo Items ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `combo_items` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `combo_id`   INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `quantity`   INT NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    KEY `idx_ci_combo` (`combo_id`),
    KEY `idx_ci_product` (`product_id`),
    CONSTRAINT `fk_coi_combo` FOREIGN KEY (`combo_id`) REFERENCES `combos`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_coi_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Lead Recovery (Incomplete Orders) ───────────────────────
CREATE TABLE IF NOT EXISTS `lead_recovery` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `customer_name` VARCHAR(150) DEFAULT NULL,
    `phone`         VARCHAR(20) DEFAULT NULL,
    `email`         VARCHAR(191) DEFAULT NULL,
    `address`       TEXT DEFAULT NULL,
    `district`      VARCHAR(100) DEFAULT NULL,
    `cart_data`     TEXT DEFAULT NULL COMMENT 'JSON snapshot of cart',
    `cart_total`    DECIMAL(12,2) DEFAULT NULL,
    `status`        ENUM('new','contacted','no_answer','follow_up','converted','lost') NOT NULL DEFAULT 'new',
    `notes`         TEXT DEFAULT NULL,
    `follow_up_at`  DATETIME DEFAULT NULL,
    `converted_order_id` INT UNSIGNED DEFAULT NULL,
    `ip_address`    VARCHAR(45) DEFAULT NULL,
    `session_id`    VARCHAR(128) DEFAULT NULL,
    `source`        VARCHAR(100) DEFAULT 'checkout_abandon',
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_lr_status` (`status`),
    KEY `idx_lr_phone` (`phone`),
    KEY `idx_lr_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Courier Shipments ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS `shipments` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id`        INT UNSIGNED NOT NULL,
    `courier_name`    VARCHAR(150) NOT NULL,
    `tracking_number` VARCHAR(100) DEFAULT NULL,
    `tracking_url`    VARCHAR(500) DEFAULT NULL,
    `shipped_at`      DATE DEFAULT NULL,
    `delivered_at`    DATE DEFAULT NULL,
    `status`          ENUM('pending','processing','shipped','in_transit','delivered','cancelled','returned') NOT NULL DEFAULT 'pending',
    `notes`           TEXT DEFAULT NULL,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_sh_order_id` (`order_id`),
    KEY `idx_sh_status` (`status`),
    KEY `idx_sh_tracking` (`tracking_number`),
    CONSTRAINT `fk_sh_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Blocklist (Fraud) ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS `blocklist` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `type`       ENUM('phone','ip','email') NOT NULL DEFAULT 'phone',
    `value`      VARCHAR(191) NOT NULL,
    `reason`     TEXT DEFAULT NULL,
    `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_blocklist` (`type`, `value`),
    KEY `idx_bl_type` (`type`),
    KEY `idx_bl_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Landing Pages ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `landing_pages` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title`       VARCHAR(300) NOT NULL,
    `slug`        VARCHAR(320) NOT NULL,
    `hero_image`  VARCHAR(255) DEFAULT NULL,
    `hero_heading` VARCHAR(300) DEFAULT NULL,
    `hero_sub`    TEXT DEFAULT NULL,
    `cta_text`    VARCHAR(100) DEFAULT NULL,
    `cta_link`    VARCHAR(500) DEFAULT NULL,
    `content`     LONGTEXT DEFAULT NULL,
    `products`    TEXT DEFAULT NULL COMMENT 'JSON array of product IDs',
    `countdown_at` DATETIME DEFAULT NULL,
    `is_active`   TINYINT(1) NOT NULL DEFAULT 0,
    `meta_title`  VARCHAR(255) DEFAULT NULL,
    `meta_desc`   TEXT DEFAULT NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_lp_slug` (`slug`),
    KEY `idx_lp_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Static Pages (About, Privacy, etc.) ─────────────────────
CREATE TABLE IF NOT EXISTS `pages` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title`      VARCHAR(300) NOT NULL,
    `slug`       VARCHAR(320) NOT NULL,
    `content`    LONGTEXT DEFAULT NULL,
    `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `meta_title` VARCHAR(255) DEFAULT NULL,
    `meta_desc`  TEXT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_pages_slug` (`slug`),
    KEY `idx_pages_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Menu Items ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `menu_items` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `menu`       VARCHAR(50) NOT NULL DEFAULT 'main' COMMENT 'main, footer, mobile',
    `label`      VARCHAR(150) NOT NULL,
    `url`        VARCHAR(500) NOT NULL,
    `parent_id`  INT UNSIGNED DEFAULT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `target`     VARCHAR(20) NOT NULL DEFAULT '_self',
    `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_mi_menu` (`menu`),
    KEY `idx_mi_parent` (`parent_id`),
    KEY `idx_mi_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Traffic Logs ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `traffic_logs` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ip_address`     VARCHAR(45) DEFAULT NULL,
    `user_agent`     TEXT DEFAULT NULL,
    `device_type`    VARCHAR(50) DEFAULT NULL,
    `page_url`       VARCHAR(500) DEFAULT NULL,
    `referral_source` VARCHAR(255) DEFAULT NULL,
    `session_id`     VARCHAR(128) DEFAULT NULL,
    `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_tl_ip` (`ip_address`),
    KEY `idx_tl_device` (`device_type`),
    KEY `idx_tl_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Inventory History ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS `inventory_history` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id`  INT UNSIGNED NOT NULL,
    `type`        ENUM('increase','decrease','sale','return','adjustment') NOT NULL DEFAULT 'adjustment',
    `quantity`    INT NOT NULL,
    `before_qty`  INT NOT NULL DEFAULT 0,
    `after_qty`   INT NOT NULL DEFAULT 0,
    `reason`      VARCHAR(255) DEFAULT NULL,
    `order_id`    INT UNSIGNED DEFAULT NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ih_product` (`product_id`),
    KEY `idx_ih_order` (`order_id`),
    CONSTRAINT `fk_ih_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── WhatsApp/SMS Templates ──────────────────────────────────
CREATE TABLE IF NOT EXISTS `message_templates` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(100) NOT NULL,
    `type`       VARCHAR(50) NOT NULL DEFAULT 'whatsapp',
    `content`    TEXT NOT NULL,
    `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_mt_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Alter products: add missing columns ─────────────────────
ALTER TABLE `products`
    ADD COLUMN IF NOT EXISTS `deleted_at`    DATETIME DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `views`         INT NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS `sold_count`    INT NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS `badge`         VARCHAR(50) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `short_desc`    TEXT DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `gallery`       TEXT DEFAULT NULL COMMENT 'JSON array of images',
    ADD COLUMN IF NOT EXISTS `weight`        DECIMAL(8,3) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `product_type`  ENUM('simple','variable') NOT NULL DEFAULT 'simple',
    ADD COLUMN IF NOT EXISTS `variations`    TEXT DEFAULT NULL COMMENT 'JSON array';

-- ── Alter orders: add extended status support ────────────────
-- We expand the order_status ENUM
ALTER TABLE `orders`
    MODIFY COLUMN `order_status` ENUM('pending','confirmed','processing','shipped','delivered','cancelled','returned','hold','fraud') NOT NULL DEFAULT 'pending',
    ADD COLUMN IF NOT EXISTS `deleted_at` DATETIME DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `coupon_code` VARCHAR(50) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `discount_amount` DECIMAL(10,2) DEFAULT NULL;

-- ── Alter product_categories: add parent, soft delete ────────
ALTER TABLE `product_categories`
    ADD COLUMN IF NOT EXISTS `parent_id`  INT UNSIGNED DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `deleted_at` DATETIME DEFAULT NULL;

-- ── Alter blogs: add soft delete ────────────────────────────
ALTER TABLE `blogs`
    ADD COLUMN IF NOT EXISTS `deleted_at` DATETIME DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `tags`       VARCHAR(500) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `author`     VARCHAR(150) DEFAULT NULL;

-- ── Admin Notifications (already exists per header.php) ──────
CREATE TABLE IF NOT EXISTS `admin_notifications` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `type`       VARCHAR(50) NOT NULL DEFAULT 'info',
    `message`    TEXT NOT NULL,
    `link`       VARCHAR(500) DEFAULT NULL,
    `is_read`    TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_an_is_read` (`is_read`),
    KEY `idx_an_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Seed: Default WhatsApp/SMS Templates ────────────────────
INSERT IGNORE INTO `message_templates` (`name`, `type`, `content`) VALUES
('অর্ডার নিশ্চিতকরণ', 'whatsapp', 'আস-সালামু আলাইকুম {customer_name} ভাই/আপু,\n\nআপনার অর্ডার #{order_number} সফলভাবে গৃহীত হয়েছে! ✅\n\nমোট পরিমাণ: ৳{total}\nপেমেন্ট পদ্ধতি: {payment_method}\n\nআমরা শীঘ্রই আপনার অর্ডার প্রক্রিয়া করব।\n\nধন্যবাদ,\nFarmersBD 🐟'),
('অর্ডার প্রক্রিয়াকরণ', 'whatsapp', 'আস-সালামু আলাইকুম {customer_name} ভাই/আপু,\n\nআপনার অর্ডার #{order_number} প্রক্রিয়া করা হচ্ছে। 🔄\n\nআমরা আপনার পণ্য প্যাক করে শীঘ্রই পাঠিয়ে দেব।\n\nধন্যবাদ,\nFarmersBD 🐟'),
('শিপমেন্ট', 'whatsapp', 'আস-সালামু আলাইকুম {customer_name} ভাই/আপু,\n\nআপনার অর্ডার #{order_number} পাঠিয়ে দেওয়া হয়েছে! 🚚\n\nট্র্যাকিং নম্বর: {tracking_number}\n\nধন্যবাদ,\nFarmersBD 🐟'),
('ডেলিভারি সম্পন্ন', 'whatsapp', 'আস-সালামু আলাইকুম {customer_name} ভাই/আপু,\n\nআপনার অর্ডার #{order_number} সফলভাবে পৌঁছে গেছে! 🎉\n\nআপনার মতামত আমাদের জন্য অমূল্য। রিভিউ দিয়ে আমাদের উৎসাহিত করুন।\n\nধন্যবাদ,\nFarmersBD 🐟'),
('বাতিল', 'whatsapp', 'আস-সালামু আলাইকুম {customer_name} ভাই/আপু,\n\nআপনার অর্ডার #{order_number} বাতিল করা হয়েছে। ❌\n\nকোনো প্রশ্ন থাকলে আমাদের সাথে যোগাযোগ করুন।\n\nধন্যবাদ,\nFarmersBD 🐟'),
('লিড রিকভারি', 'whatsapp', 'আস-সালামু আলাইকুম {customer_name} ভাই/আপু,\n\nআপনি FarmersBD-তে কিছু পণ্য কার্টে রেখেছিলেন। আপনার অর্ডার সম্পন্ন করতে কি কোনো সাহায্য দরকার?\n\nআমাদের সাথে যোগাযোগ করুন অথবা এখনই অর্ডার করুন!\n\nধন্যবাদ,\nFarmersBD 🐟');

-- ── Seed: Default Pages ──────────────────────────────────────
INSERT IGNORE INTO `pages` (`title`, `slug`, `content`, `is_active`, `sort_order`) VALUES
('আমাদের সম্পর্কে', 'about', '<h2>FarmersBD সম্পর্কে</h2><p>FarmersBD বাংলাদেশের একটি আধুনিক মৎস্য চাষ প্ল্যাটফর্ম।</p>', 1, 1),
('গোপনীয়তা নীতি', 'privacy-policy', '<h2>গোপনীয়তা নীতি</h2><p>আমরা আপনার তথ্য সুরক্ষিত রাখি।</p>', 1, 2),
('ব্যবহারের শর্তাবলী', 'terms-conditions', '<h2>ব্যবহারের শর্তাবলী</h2><p>FarmersBD ব্যবহারের শর্তাবলী।</p>', 1, 3),
('রিটার্ন ও রিফান্ড নীতি', 'return-refund', '<h2>রিটার্ন ও রিফান্ড নীতি</h2><p>পণ্য পাওয়ার ৭ দিনের মধ্যে ক্ষতিগ্রস্ত পণ্য ফেরত দেওয়া যাবে।</p>', 1, 4),
('ডেলিভারি নীতি', 'delivery-policy', '<h2>ডেলিভারি নীতি</h2><p>ঢাকার মধ্যে ১-২ দিন, বাইরে ৩-৫ দিন।</p>', 1, 5);

-- ── Seed: Default Menu Items ─────────────────────────────────
INSERT IGNORE INTO `menu_items` (`menu`, `label`, `url`, `sort_order`) VALUES
('main', 'হোম', '/', 1),
('main', 'পণ্যসমূহ', '/products/', 2),
('main', 'মাছের তথ্য', '/fish/', 3),
('main', 'রোগ নির্ণয়', '/ai/', 4),
('main', 'ব্লগ', '/blog/', 5),
('main', 'যোগাযোগ', '/contact/', 6),
('footer', 'আমাদের সম্পর্কে', '/page/about', 1),
('footer', 'গোপনীয়তা নীতি', '/page/privacy-policy', 2),
('footer', 'ব্যবহারের শর্তাবলী', '/page/terms-conditions', 3),
('footer', 'রিটার্ন নীতি', '/page/return-refund', 4);

-- ── Seed: Extended site settings for new modules ────────────
INSERT IGNORE INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('delivery_inside_dhaka', '60'),
('delivery_outside_dhaka', '120'),
('free_delivery_min_order', '0'),
('order_protection_enabled', '1'),
('duplicate_order_check', '1'),
('order_freq_limit_per_day', '3'),
('announcement_bar', ''),
('announcement_bar_enabled', '0'),
('floating_cart_enabled', '1'),
('floating_cart_position', 'right'),
('shop_btn_cart_text', 'কার্টে যোগ করুন'),
('shop_btn_buy_text', 'এখনই কিনুন'),
('maintenance_mode', '0'),
('maintenance_message', 'সাইটটি রক্ষণাবেক্ষণের জন্য সাময়িকভাবে বন্ধ আছে।'),
('maintenance_countdown', ''),
('primary_color', '#0d9488'),
('secondary_color', '#f59e0b'),
('notification_sound', '1'),
('low_stock_threshold', '10'),
('whatsapp_number', ''),
('seo_title', 'FarmersBD'),
('seo_description', ''),
('seo_keywords', '');

SET foreign_key_checks = 1;
