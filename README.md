<div align="center">

# ⚽ ALSSPORT - Sports Store E-Commerce Web Application

**A robust, responsive e-commerce web platform for sporting goods and athletic apparel, featuring product catalogs, cart management, customer testimonials, live support chat, and a full administrative dashboard.**

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
- **Storefront / Catalog:** `http://localhost/ALSSPORT/home.php`
- **Shopping Cart & Checkout:** `http://localhost/ALSSPORT/order.php`
- **Store Administration:** `http://localhost/ALSSPORT/admin.php`

> **Demo Credentials:**
> - **Store Administrator:** Email: `admin@alssport.com` | Password: `admin123`
> - **Customer Demo:** Email: `customer@example.com` | Password: `password123`

### 📸 System Showcase & Screenshots

| 🛒 Storefront & Catalog | 👟 Product Detail & Options |
|:---:|:---:|
| ![Storefront](docs/screenshots/01_storefront_home.svg) | ![Product Detail](docs/screenshots/02_product_details.svg) |
| **💳 Checkout & Cart Flow** | **📊 Store Administration Center** |
| ![Checkout](docs/screenshots/03_shopping_cart.svg) | ![Admin Panel](docs/screenshots/04_admin_dashboard.svg) |

### ⚡ Core Technical Features
- **Dynamic Product Catalog & Filtering:** Browse athletic shoes, apparel, and accessories with instant categorization, price range sorting, and brand discovery.
- **Asynchronous RESTful Endpoints (`/api/`):** Decoupled backend handlers for live chat (`api_chat.php`), newsletter subscriptions (`newsletter.php`), testimonials (`testimonials.php`), and wishlist management (`wishlist.php`).
- **Cart & Order Processing Wizard:** End-to-end purchasing workflow tracking customer information, applied discounts, order itemization, and mock payment verification.
- **Interactive Customer Engagement:** Integrated live chat messaging mechanism allowing direct interaction between store operators and shoppers.
- **Store Operations Management:** Comprehensive administrative control panel to manage catalog inventory, audit user orders, oversee customer reviews, and monitor traffic metrics.

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
3. Verify database settings in `config.php` and `db.php`.
4. Run via XAMPP and open `http://localhost/ALSSPORT/home.php`.

---

## 🇮🇷 نسخه فارسی

<div dir="rtl">

### 📌 درباره پروژه
**ALSSPORT** یک فروشگاه اینترنتی مدرن و کامل برای تجهیزات و پوشاک ورزشی است که با استفاده از **PHP استاندارد، معماری ماژولار و دیتابیس MySQL** پیاده‌سازی شده است.

### 🌐 مشخصات دمو و حساب‌های تستی
- **فروشگاه و ویترین محصولات:** `http://localhost/ALSSPORT/home.php`
- **سبد خرید و تسویه حساب:** `http://localhost/ALSSPORT/order.php`
- **پنل مدیریت فروشگاه:** `http://localhost/ALSSPORT/admin.php`

> **اطلاعات ورود دمو:**
> - **مدیر فروشگاه (Admin):** ایمیل: `admin@alssport.com` | کلمه عبور: `admin123`
> - **مشتری دمو (Customer):** ایمیل: `customer@example.com` | کلمه عبور: `password123`

### 📸 پیش‌نمایش بخش‌های فروشگاه

| 🛒 ویترین و کاتالوگ محصولات | 👟 صفحه محصول و مشخصات |
|:---:|:---:|
| ![ویترین فروشگاه](docs/screenshots/01_storefront_home.svg) | ![صفحه محصول](docs/screenshots/02_product_details.svg) |
| **💳 سبد خرید و فرآیند تسویه** | **📊 پنل مدیریت فروشگاه** |
| ![سبد خرید](docs/screenshots/03_shopping_cart.svg) | ![پنل ادمین](docs/screenshots/04_admin_dashboard.svg) |

### ⚡ قابلیت‌های کلیدی سامانه
- **کاتالوگ پویا با فیلتر هوشمند:** جستجو و دسته‌بندی بر اساس نوع کالا، محدوده قیمت و برند.
- **اندپوینت‌های ای‌پی‌آی ناهمگام (`/api/`):** پردازشگرهای مجزا برای سیستم چت آنلاین، عضویت در خبرنامه، ثبت نظرات و لیست علاقه‌مندی‌ها.
- **فرآیند کامل خرید و صدور فاکتور:** محاسبه خودکار تخفیف، ثبت جزییات سفارش و شبیه‌ساز تأیید پرداخت.
- **پنل مدیریت جامع:** نظارت بر موجودی انبار، رهگیری سفارشات کاربران و مدیریت نظرات مشتریان.
- **طراحی واکنش‌گرا و سریع:** بهینه‌سازی شده با CSS3 مدرن برای تجربه بی‌نقص در موبایل و دسکتاپ.

### 🚀 راهنمای نصب و راه‌اندازی لوکال
۱. کلون کردن مخزن پروژه:
```bash
git clone https://github.com/hzn806512-source/ALSSPORT.git
```
۲. ایجاد دیتابیس `alssport` در phpMyAdmin و ایمپورت کردن فایل `database/alssport.sql`.
۳. بررسی مشخصات اتصال لوکال در `config.php`.
۴. باز کردن آدرس `http://localhost/ALSSPORT/home.php` در مرورگر.

</div>