<div align="center">

# 👔 ALSSPORT - Men's Fashion Boutique E-Commerce Platform

**A modern, responsive online boutique for men's clothing and apparel, featuring dynamic product catalogs, size selections, cart management, customer testimonials, live support chat, and a full administrative dashboard.**

[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](#)
[![MySQL Database](https://img.shields.io/badge/MySQL-5.7%2B%20%7C%20MariaDB-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](#)
[![Architecture](https://img.shields.io/badge/Architecture-Modular%20PHP%20%2B%20REST%20APIs-0284c7?style=for-the-badge)](#)
[![License: MIT](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](#)
[![Security Audited](https://img.shields.io/badge/Security-Sanitized%20%26%20Protected-emerald?style=for-the-badge)](#)

</div>

---

## 🌐 Languages / زبان‌ها
- [🇺🇸 English Version](#-english-documentation)
- [🇮🇷 نسخه فارسی](#-نسخه-فارسی)

---

## 🇺🇸 English Documentation

### 🌐 Live Demo & Quick Access
- **Boutique Storefront:** `http://localhost/ALSSPORT/home.php`
- **Shopping Cart & Checkout:** `http://localhost/ALSSPORT/order.php`
- **Store Administration:** `http://localhost/ALSSPORT/admin.php`

> **Demo Credentials:**
> - **Store Administrator:** Email: `admin@alssport.com` | Password: `admin123`
> - **Customer Demo:** Email: `customer@example.com` | Password: `password123`

### 📸 System Showcase & Screenshots

| 👔 Boutique Storefront & Catalog | 👕 Product Detail & Sizing |
|:---:|:---:|
| ![Storefront](docs/screenshots/01_storefront_home.svg) | ![Product Detail](docs/screenshots/02_product_details.svg) |
| **💳 Checkout & Cart Flow** | **📊 Boutique Administration Center** |
| ![Checkout](docs/screenshots/03_shopping_cart.svg) | ![Admin Panel](docs/screenshots/04_admin_dashboard.svg) |

### ⚡ Core Technical Features
- **Men's Fashion Catalog & Filtering:** Browse formal suits, casual shirts, jackets, trousers, and accessories with instant category filtering, price range sorting, and brand discovery.
- **Garment Options & Sizing:** Detailed product views offering size options (S, M, L, XL, XXL), color previews, and inventory availability indicators.
- **Asynchronous RESTful Endpoints (`/api/`):** Decoupled backend handlers for live chat (`api_chat.php`), newsletter subscriptions (`newsletter.php`), testimonials (`testimonials.php`), and wishlist management (`wishlist.php`).
- **Cart & Order Processing Wizard:** End-to-end purchasing workflow tracking customer delivery details, coupon codes, order itemization, and mock payment verification.
- **Interactive Customer Engagement:** Integrated live chat messaging mechanism allowing direct interaction between boutique operators and shoppers.
- **Boutique Operations Management:** Comprehensive administrative control panel to manage clothing inventory, audit user orders, oversee customer reviews, and monitor traffic metrics.

### 🛠️ Tech Stack
| Domain | Technology / Specification |
|:---|:---|
| **Backend Language** | PHP 8.0+ (Modular, OOP Helpers, REST JSON Endpoints) |
| **Database** | MySQL / MariaDB (Prepared Statements, Relational Schema) |
| **API Architecture** | RESTful JSON endpoints with Fetch API / AJAX integration |
| **Frontend** | HTML5, Modern Responsive CSS3, Vanilla JavaScript (ES6+) |
| **Security Layer** | Input Sanitization, Session-Based Role Authentication, Rate Limiting |
| **Web Server** | Apache (mod_rewrite, `.htaccess` security headers) |

### 🚀 Installation & Local Setup
1. Clone the repository:
   ```bash
   git clone https://github.com/hzn806512-source/ALSSPORT.git
   cd ALSSPORT
   ```
2. Import database schema into MySQL:
   ```bash
   mysql -u root -p alssport < database/alssport.sql
   ```
3. Verify database settings in `config.php` and `db.php`:
   ```php
   $db_host = "localhost";
   $db_name = "alssport";
   $db_user = "root";
   $db_pass = "";
   ```
4. Run via XAMPP and open `http://localhost/ALSSPORT/home.php`.

---

## 🇮🇷 نسخه فارسی

<div dir="rtl">

### 📌 درباره پروژه
**ALSSPORT** یک وب‌اپلیکیشن فروشگاهی کامل و مدرن برای **بوتیک و پوشاک مردانه** است که با معماری ماژولار در **PHP و پایگاه‌داده MySQL** طراحی و پیاده‌سازی شده است. این سامانه فرآیند خرید انواع کت، شلوار، پیراهن و اکسسوری‌های مردانه را به همراه پنل مدیریت یکپارچه فراهم می‌سازد.

### 🌐 مشخصات دمو و حساب‌های تستی
- **ویترین و کاتالوگ بوتیک:** `http://localhost/ALSSPORT/home.php`
- **سبد خرید و تسویه حساب:** `http://localhost/ALSSPORT/order.php`
- **پنل مدیریت فروشگاه:** `http://localhost/ALSSPORT/admin.php`

> **اطلاعات ورود دمو:**
> - **مدیر بوتیک (Admin):** ایمیل: `admin@alssport.com` | کلمه عبور: `admin123`
> - **مشتری دمو (Customer):** ایمیل: `customer@example.com` | کلمه عبور: `password123`

### 📸 پیش‌نمایش بخش‌های فروشگاه

| 👔 ویترین و کاتالوگ پوشاک مردانه | 👕 صفحه محصول و انتخاب سایز |
|:---:|:---:|
| ![ویترین فروشگاه](docs/screenshots/01_storefront_home.svg) | ![صفحه محصول](docs/screenshots/02_product_details.svg) |
| **💳 سبد خرید و فرآیند تسویه** | **📊 پنل مدیریت بوتیک** |
| ![سبد خرید](docs/screenshots/03_shopping_cart.svg) | ![پنل ادمین](docs/screenshots/04_admin_dashboard.svg) |

### ⚡ قابلیت‌های کلیدی سامانه
- **کاتالوگ هوشمند لباس مردانه:** دسته‌بندی دقیق انواع کت و شلوار، پیراهن‌های رسمی و کژوال، با قابلیت فیلتر بر اساس سایز، رنگ و قیمت.
- **سیستم انتخاب سایز و موجودی انبار:** نمایش سایزهای استاندارد پوشاک (S تا XXL) و هشدار اتمام موجودی.
- **اندپوینت‌های ای‌پی‌آی ناهمگام (`/api/`):** ارتباط بلادرنگ مشتریان از طریق چت آنلاین، عضویت در خبرنامه، ثبت امتیاز و نظرات کالا و لیست علاقه‌مندی‌ها.
- **فرآیند تسویه حساب و صدور فاکتور:** محاسبه تخفیف‌ها، ثبت آدرس و اطلاعات خریدار و شبیه‌ساز پرداخت موفق.
- **پنل مدیریت انبار و سفارشات:** داشبورد متمرکز برای افزودن لباس‌های جدید، مدیریت موجودی و رهگیری وضعیت سفارشات مشتریان.
- **طراحی واکنش‌گرا و سریع:** استایل‌بندی مدرن CSS3 برای عملکرد سریع و روان روی گوشی‌های موبایل و رایانه.

### 🚀 راهنمای نصب و راه‌اندازی لوکال
۱. کلون کردن مخزن:
```bash
git clone https://github.com/hzn806512-source/ALSSPORT.git
```
۲. ایجاد پایگاه داده `alssport` در phpMyAdmin و ایمپورت کردن فایل `database/alssport.sql`.
۳. اطمینان از تنظیمات اتصال دیتابیس لوکال در `config.php`.
۴. باز کردن آدرس `http://localhost/ALSSPORT/home.php` در مرورگر.

</div>

---

## 📄 License
This project is open-source under the [MIT License](LICENSE).