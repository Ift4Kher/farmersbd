# FarmersBD — Production-Ready Smart Fish Farming Platform

FarmersBD is a complete, secure, responsive, multi-page Bangladeshi web application designed for smart fish farming. It combines AI-powered fish disease detection, fish species knowledge, aqua product e-commerce, expert consultations, educational blogs, user account dashboards, and a complete admin control panel.

---

## 🌟 Key Features

1. **AI Fish Disease Detection**:
   - Upload fish photos via drag-and-drop or file selector.
   - Smart fallback mechanism providing accurate disease diagnosis, confidence metrics, and immediate treatment recommendations.

2. **Fish & Disease Knowledge Base**:
   - Complete directory of Bangladeshi carp, catfish, tilapia, and other species with farming and feeding guides.
   - Comprehensive fish disease guide detailing symptoms, causes, prevention, and aqua medicine treatments.

3. **E-Commerce & Online Aqua Shop**:
   - Dynamic product catalog with filters, categories, stock tracking, and discounted prices.
   - Interactive AJAX Shopping Cart with stock capping.
   - Multi-channel Checkout supporting **Cash on Delivery (COD)** and **SSLCOMMERZ Online Payment** gateway.

4. **Expert Consultation System**:
   - Send questions, pond details, and fish photos directly to aquaculture experts.
   - Receive doctor prescriptions and advice in the user dashboard.

5. **User Account & Admin Control Panel**:
   - Full customer account management (Order history, AI diagnosis history, Consultation history, Profile editing, Password change).
   - Enterprise Admin Dashboard with 8 key metric cards, order processing, inventory controls, blog publishing, FAQ management, and CMS settings.

6. **Dynamic CMS & Typography**:
   - Admin settings for dynamic homepage section toggles, hero slider management, typography/font controls (Kalpurush, SolaimanLipi, Hind Siliguri), and SEO meta configurations.

---

## 🚀 Setup & Installation (XAMPP / Localhost)

1. **Place Codebase**:
   Copy the `farmersbd` folder to `C:\xampp\htdocs\farmersbd`.

2. **Import Database**:
   - Open phpMyAdmin (`http://localhost/phpmyadmin/`).
   - Create a database named `farmersbd`.
   - Import `database/farmersbd.sql`.

3. **Configuration**:
   - Check `config/config.php` and `config/database.php`.
   - Update DB credentials if necessary (`DB_USER`, `DB_PASS`).

4. **Access Platform**:
   - **Frontend**: `http://localhost/farmersbd/`
   - **Admin Portal**: `http://localhost/farmersbd/admin/`
     - **Admin Username**: `admin`
     - **Admin Password**: `admin123`

---

## 🛡️ Security & Architecture

- Prepared SQL statements (PDO) to prevent SQL Injection.
- CSRF Token verification across all POST forms.
- `htmlspecialchars` output sanitization against XSS.
- Upload directory `.htaccess` protection preventing executable script execution.
- Hashed passwords using `password_hash()` (BCRYPT).

---

© FarmersBD — Smart Fish Farming Platform.
