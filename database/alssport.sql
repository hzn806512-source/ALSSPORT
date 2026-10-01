-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 18, 2026 at 12:05 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `alssport`
--

-- --------------------------------------------------------

--
-- Table structure for table `ai_knowledge`
--

CREATE TABLE `ai_knowledge` (
  `id` int(11) NOT NULL,
  `keyword` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ai_knowledge`
--

INSERT INTO `ai_knowledge` (`id`, `keyword`, `answer`, `created_at`) VALUES
(1, 'سلام', 'سلام و درود! به بوتیک لوکس آلس اسپورت خوش آمدید. چه کمکی در انتخاب استایل یا خرید به شما کنم؟', '2026-09-18 07:01:12'),
(2, 'چه لباس هایی وجود داره', 'از پرسش شما سپاسگزاریم! ما در آلس اسپورت جدیدترین پوشاک و اکسسوری‌های لوکس مردانه را ارائه می‌دهیم. پیشنهاد می‌کنیم صفحه محصولات را بررسی کنید یا سوال خود را با جزئیات بیشتر بپرسید تا سیستم آن را یاد بگیرد.', '2026-09-18 07:01:27'),
(3, 'چه محصولاتی دارین', 'از پرسش شما سپاسگزاریم! ما در آلس اسپورت جدیدترین پوشاک و اکسسوری‌های لوکس مردانه را ارائه می‌دهیم. پیشنهاد می‌کنیم صفحه محصولات را بررسی کنید یا سوال خود را با جزئیات بیشتر بپرسید تا سیستم آن را یاد بگیرد.', '2026-09-18 07:15:58');

-- --------------------------------------------------------

--
-- Table structure for table `backgrounds`
--

CREATE TABLE `backgrounds` (
  `id` int(6) UNSIGNED NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `backgrounds`
--

INSERT INTO `backgrounds` (`id`, `image_url`, `created_at`) VALUES
(5, 'uploads/69249706bd652.jpg', '2025-11-24 17:33:58');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`, `created_at`) VALUES
(1, 'کاپشن و ژاکت', 'kapshan-jacket', 'کاپشن‌ها، ژاکت‌ها و لباس گرم', '🧥', '2026-06-27 13:39:45'),
(2, 'تی‌شرت و پیراهن', 'tshirt-shirt', 'تی‌شرت‌ها، پیراهن‌ها و لباس تابستانی', '👕', '2026-06-27 13:39:45'),
(3, 'شلوار و دامن', 'pants-skirt', 'شلوار، دامن و لباس پایین‌تنه', '👖', '2026-06-27 13:39:45'),
(4, 'لباس‌ها', 'dresses', 'لباس‌های زنانه و مجلسی', '👗', '2026-06-27 13:39:45'),
(5, 'کفش', 'shoes', 'کفش‌های ورزشی و روزمره', '👟', '2026-06-27 13:39:45');

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT 'گفتگوی جدید',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `discounts`
--

CREATE TABLE `discounts` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `percentage` int(11) DEFAULT NULL CHECK (`percentage` > 0 and `percentage` <= 100),
  `description` varchar(255) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `discounts`
--

INSERT INTO `discounts` (`id`, `product_id`, `percentage`, `description`, `active`, `created_at`) VALUES
(1, 5, 5, 'حراج تابستانه', 1, '2026-07-09 06:59:41'),
(4, 10, 4, '', 1, '2026-07-23 01:34:33');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sender` enum('user','admin') NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `user_id`, `sender`, `message`, `is_read`, `created_at`) VALUES
(1, 1, 'user', 'سلام آقا غذای ما چی شد', 1, '2025-11-20 02:46:29'),
(2, 1, 'admin', 'در حال پخت هستیم', 1, '2025-11-20 04:01:22'),
(3, 1, 'user', 'سلام', 1, '2025-11-20 04:03:03'),
(4, 1, 'user', 'باشه مشکلی نیست', 1, '2025-11-20 04:09:53'),
(5, 1, 'admin', '**', 1, '2025-11-20 04:30:59'),
(6, 1, 'admin', 'خوبه', 1, '2025-11-20 04:53:08'),
(7, 1, 'user', 'Ok', 1, '2025-11-20 04:57:51'),
(8, 1, 'user', 'بازم دمت گرم', 1, '2025-11-20 04:58:09'),
(9, 1, 'admin', 'خواخش', 1, '2025-11-20 04:58:29'),
(10, 1, 'admin', 'خواهش', 1, '2025-11-20 04:59:33'),
(11, 1, 'user', '😴', 1, '2025-11-20 05:06:32'),
(12, 1, 'admin', 'عهههه خنده خنده', 1, '2025-11-20 05:09:35'),
(13, 1, 'user', 'زهر مار می خند', 1, '2025-11-20 05:12:19'),
(14, 1, 'admin', 'خری واقعا', 1, '2025-11-20 05:16:35'),
(15, 1, 'user', 'خودت خری', 1, '2025-11-20 05:24:03'),
(16, 1, 'admin', 'خری واقعا', 1, '2025-11-20 05:24:32'),
(17, 1, 'user', 'ببین ازت می‌خوام این کدهای پایینی که برات فرستادمو اون قسمتی که اربر چت می‌کنه با پشتیبانی به همین شماره کاربر داره پیام میده هر وقت که پیام میده اینور تو قسمت خود مشتری که داره پیام میده سرورش رفرش بشه چون که دیگه نیازی نباشه کاربر موقعی که داره چت می‌کنه با پشتیبانی برای اینکه بخواد پیام پشتیبانی رو ببینه صفحه خودشو رفرش نکنه و سرور رفرش بشه دقیقاً در زمانی که پشتیبانی یا سرآشپز دقیقاً پیامشو به همین شماره می‌فرسته', 1, '2025-11-20 05:34:35'),
(18, 1, 'user', 'سلام من نیما', 1, '2025-11-20 07:27:45'),
(19, 1, 'admin', 'آره میدونم', 1, '2025-11-20 07:28:20'),
(20, 1, 'user', 'Ievddb', 1, '2025-11-20 07:57:48'),
(21, 1, 'user', 'من نیما هستم و برادرم سینا است', 1, '2025-11-20 09:31:49'),
(22, 1, 'admin', 'آره میدونستم.', 1, '2025-11-20 09:32:22'),
(23, 2, 'user', 'سلام من گشنمه', 1, '2025-11-20 09:37:30'),
(24, 2, 'user', 'شما چه غذایی پیشنهاد میدین', 1, '2025-11-20 09:37:40'),
(25, 2, 'admin', 'باشه مشکلی نیست', 1, '2025-11-20 09:38:10'),
(26, 2, 'user', 'پیتزا خوبه یا برگر', 1, '2025-11-20 09:38:14'),
(27, 2, 'user', 'زود باش جواب بده دیگه', 1, '2025-11-20 09:38:29'),
(28, 2, 'admin', 'به نظر من پیتزا چون گرون تره', 1, '2025-11-20 09:38:30'),
(29, 2, 'user', 'من یک نفرم', 1, '2025-11-20 09:38:53'),
(30, 3, 'user', 'سلام', 1, '2025-11-20 10:46:05'),
(31, 2, 'admin', 'خری واقعا', 1, '2025-11-20 11:21:03'),
(32, 3, 'admin', 'سلام! پیک رسید 🛵. آقا غذاتون جلوی دربتون هست، لطفاً تحویل بگیرید. نوش جان!', 0, '2025-11-20 11:21:13'),
(33, 3, 'admin', 'در حال پخت هستیم', 1, '2025-11-20 11:27:14'),
(34, 3, 'user', 'آها باشه', 1, '2025-11-20 11:31:08'),
(35, 3, 'admin', 'سلام! پیک رسید 🛵. آقا غذاتون جلوی دربتون هست، لطفاً تحویل بگیرید. نوش جان!', 0, '2025-11-20 11:38:31'),
(36, 3, 'admin', 'سلام! پیک رسید 🛵. آقا غذاتون جلوی دربتون هست، لطفاً تحویل بگیرید. نوش جان!', 0, '2025-11-20 11:51:57'),
(37, 3, 'admin', 'سلام! پیک رسید 🛵. آقا غذاتون جلوی دربتون هست، لطفاً تحویل بگیرید. نوش جان!', 0, '2025-11-20 11:52:00'),
(38, 3, 'admin', 'سلام! پیک رسید 🛵. آقا غذاتون جلوی دربتون هست، لطفاً تحویل بگیرید. نوش جان!', 0, '2025-11-20 11:52:02'),
(39, 3, 'admin', 'سلام! پیک رسید 🛵. آقا غذاتون جلوی دربتون هست، لطفاً تحویل بگیرید. نوش جان!', 0, '2025-11-20 11:59:12'),
(40, 3, 'admin', 'سلام! پیک رسید 🛵. آقا غذاتون جلوی دربتون هست، لطفاً تحویل بگیرید. نوش جان!', 0, '2025-11-20 11:59:12'),
(41, 3, 'admin', 'سلام! پیک رسید 🛵. آقا غذاتون جلوی دربتون هست، لطفاً تحویل بگیرید. نوش جان!', 0, '2025-11-20 11:59:18'),
(42, 3, 'admin', 'سلام! پیک رسید 🛵. آقا غذاتون جلوی دربتون هست، لطفاً تحویل بگیرید. نوش جان!', 0, '2025-11-20 11:59:18'),
(43, 3, 'admin', 'سلام! پیک رسید 🛵. آقا غذاتون جلوی دربتون هست، لطفاً تحویل بگیرید. نوش جان!', 0, '2025-11-20 11:59:23'),
(44, 3, 'admin', 'سلام! پیک رسید 🛵. آقا غذاتون جلوی دربتون هست، لطفاً تحویل بگیرید. نوش جان!', 0, '2025-11-20 11:59:23'),
(45, 3, 'admin', 'سلام! پیک رسید 🛵. آقا غذاتون جلوی دربتون هست، لطفاً تحویل بگیرید. نوش جان!', 0, '2025-11-20 11:59:27'),
(46, 3, 'admin', 'سلام! پیک رسید 🛵. آقا غذاتون جلوی دربتون هست، لطفاً تحویل بگیرید. نوش جان!', 0, '2025-11-20 11:59:27'),
(47, 3, 'admin', 'سلام! پیک رسید 🛵. آقا غذاتون جلوی دربتون هست، لطفاً تحویل بگیرید. نوش جان!', 0, '2025-11-20 12:00:57'),
(48, 3, 'admin', 'سلام! پیک رسید 🛵. آقا غذاتون جلوی دربتون هست، لطفاً تحویل بگیرید. نوش جان!', 0, '2025-11-20 12:00:57'),
(49, 1, 'admin', 'سلام! پیک رسید 🛵. مشتری عزیز غذاتون جلوی دربتون هست، لطفاً تحویل بگیرید. نوش جان!', 0, '2025-11-21 04:06:41'),
(50, 3, 'admin', 'سلام! پیک رسید 🛵. مشتری عزیز غذاتون جلوی دربتون هست، لطفاً تحویل بگیرید. نوش جان!', 0, '2025-11-21 04:06:43'),
(51, 3, 'admin', 'سلام! پیک رسید 🛵. مشتری عزیز غذاتون جلوی دربتون هست، لطفاً تحویل بگیرید. نوش جان!', 0, '2025-11-21 04:06:46'),
(52, 5, 'user', 'Salam', 1, '2025-11-22 04:24:14'),
(53, 5, 'admin', 'Test', 1, '2025-11-22 09:42:15'),
(54, 5, 'admin', 'آفرین', 1, '2025-11-23 06:15:13'),
(55, 6, 'user', 'dyjyfhvjhku.y,tmfngdfbxvc', 1, '2025-11-23 08:21:10'),
(56, 5, 'admin', 'سلام! پیک رسید 🛵. مشتری عزیز غذاتون جلوی دربتون هست، لطفاً تحویل بگیرید. نوش جان!', 0, '2025-11-23 10:04:27'),
(57, 1, 'admin', 'سلام! پیک رسید 🛵. مشتری عزیز غذاتون جلوی دربتون هست، لطفاً تحویل بگیرید. نوش جان!', 0, '2025-11-23 10:04:35'),
(58, 1, 'user', 'سلام', 1, '2025-11-23 11:50:01'),
(59, 1, 'admin', 'سلام! پیک رسید 🛵. مشتری عزیز غذاتون جلوی دربتون هست، لطفاً تحویل بگیرید. نوش جان!', 0, '2025-11-23 11:51:30'),
(60, 1, 'admin', 'سلام! پیک رسید 🛵. مشتری عزیز غذاتون جلوی دربتون هست، لطفاً تحویل بگیرید. نوش جان!', 0, '2025-11-23 11:51:33'),
(61, 1, 'admin', 'خوبی', 1, '2025-11-23 11:52:11'),
(62, 1, 'user', 'سلامی مجدد', 1, '2025-11-23 23:24:24'),
(63, 1, 'admin', 'vhnh', 0, '2025-11-23 23:38:52'),
(64, 1, 'user', 'باشه', 1, '2025-11-24 04:28:34'),
(65, 1, 'admin', 'خوبه', 0, '2025-11-24 04:33:09'),
(66, 1, 'user', 'سلام عشقم', 1, '2025-11-24 10:49:06'),
(67, 1, 'admin', 'ممنونم', 0, '2025-11-24 10:49:34'),
(68, 1, 'admin', 'سلام', 0, '2025-11-24 12:49:52'),
(69, 1, 'user', '😍❤️💕👌🤡😈🤖💩🙊🙈🦄🐔🐲🐒🦍🦧🦮', 0, '2025-11-24 13:06:35'),
(70, 9, 'user', '⚠️ هشدار: تمام کلیدهای هوش مصنوعی (D-ID) منقضی شده‌اند یا اعتبار ندارند. لطفاً فایل api_handler.php را باز کرده و کلیدهای جدید اضافه کنید.', 1, '2025-11-25 01:34:49'),
(71, 9, 'user', '⚠️ هشدار: تمام کلیدهای هوش مصنوعی (D-ID) منقضی شده‌اند یا اعتبار ندارند. لطفاً فایل api_handler.php را باز کرده و کلیدهای جدید اضافه کنید.', 1, '2025-11-25 01:35:24'),
(72, 9, 'user', '⚠️ هشدار: تمام کلیدهای هوش مصنوعی (D-ID) منقضی شده‌اند یا اعتبار ندارند. لطفاً فایل api_handler.php را باز کرده و کلیدهای جدید اضافه کنید.', 1, '2025-11-25 01:37:25'),
(73, 9, 'user', '⚠️ هشدار: تمام کلیدهای هوش مصنوعی (D-ID) منقضی شده‌اند یا اعتبار ندارند. لطفاً فایل api_handler.php را باز کرده و کلیدهای جدید اضافه کنید.', 1, '2025-11-25 01:37:52'),
(74, 9, 'user', '⚠️ هشدار: تمام کلیدهای هوش مصنوعی (D-ID) منقضی شده‌اند یا اعتبار ندارند. لطفاً فایل api_handler.php را باز کرده و کلیدهای جدید اضافه کنید.', 1, '2025-11-25 01:38:04'),
(75, 9, 'user', '⚠️ هشدار: تمام کلیدهای هوش مصنوعی (D-ID) منقضی شده‌اند یا اعتبار ندارند. لطفاً فایل api_handler.php را باز کرده و کلیدهای جدید اضافه کنید.', 1, '2025-11-25 01:44:12'),
(76, 8, 'user', 'سلام', 1, '2025-11-25 06:59:45'),
(77, 8, 'admin', 'سلام', 0, '2025-11-25 07:00:03'),
(78, 8, 'user', 'چطوری', 1, '2025-11-26 08:29:50'),
(79, 8, 'user', 'hjvsdjcd', 1, '2025-11-26 22:12:01'),
(80, 8, 'admin', 'kjbjjjkbj\\', 0, '2025-11-26 22:12:54'),
(81, 8, 'user', 'Hdbgx', 1, '2025-11-29 21:17:22'),
(82, 8, 'user', 'سلام منم نیما', 1, '2025-11-30 08:53:59'),
(83, 8, 'user', 'b v vb', 1, '2025-12-10 11:47:53'),
(84, 8, 'user', 'Boom', 1, '2025-12-20 01:23:43'),
(85, 8, 'user', 'b v vb', 1, '2026-01-31 03:00:29'),
(86, 11, 'user', 'سلام', 1, '2026-06-20 16:52:15'),
(87, 18, 'user', 'سلام', 1, '2026-07-03 14:23:37'),
(88, 18, 'admin', 'سلام', 0, '2026-07-04 08:05:12'),
(89, 18, 'admin', 'چطور میتونم کمکتون کنم ؟', 0, '2026-07-04 08:05:30'),
(90, 19, 'user', 'سلام جیگر چطوری', 1, '2026-07-08 11:56:53'),
(91, 19, 'admin', 'سلام خوبم تو چطوری', 0, '2026-07-08 11:57:49'),
(92, 19, 'user', 'خوبم ممنون چه خبر کی میاین دلمون براتون تنگ شده', 1, '2026-07-08 11:58:41'),
(93, 19, 'admin', 'ممنونم من هم همین تور ولی یکم اوضا خوب نیست دیگه ببخشید', 0, '2026-07-09 10:28:03'),
(94, 19, 'user', 'آها اوکی مشکلی نیست هر وقت تونستید بیاید', 1, '2026-07-09 10:28:57'),
(95, 19, 'admin', 'باشه ممنون', 0, '2026-07-09 10:42:22'),
(96, 18, 'user', 'من به یه مشکل رسیدم', 1, '2026-07-17 14:36:54'),
(97, 18, 'user', 'مشکلمم اینه که نمیتونم خریدم رو به اتمام برسونم', 1, '2026-07-17 14:37:24'),
(98, 18, 'admin', 'سلام', 1, '2026-07-20 14:05:43'),
(99, 18, 'user', 'سلام چیگر', 1, '2026-07-20 14:06:02'),
(100, 18, 'admin', 'چطوری', 1, '2026-07-20 14:10:39'),
(101, 19, 'admin', 'سیب', 1, '2026-07-20 14:10:49'),
(102, 18, 'user', 'سلام', 1, '2026-07-20 17:10:41'),
(103, 18, 'user', 'چرا جواب نمیدید؟', 1, '2026-07-23 04:19:35'),
(104, 18, 'user', 'توت', 1, '2026-07-23 04:21:09'),
(105, 18, 'admin', 'سلام ببخشید تعداد پیام ها خیلی زیاده و من هم فقط یه ادمین هستم', 1, '2026-07-23 04:21:59'),
(106, 18, 'user', 'باشه پس کی میتونی جواب منو بدی', 1, '2026-07-23 05:06:53'),
(107, 18, 'admin', 'الان', 1, '2026-07-23 05:07:57'),
(108, 21, 'user', 'سلام', 1, '2026-07-23 13:13:44'),
(109, 21, 'admin', 'سلام', 1, '2026-07-23 14:02:48'),
(110, 21, 'admin', 'جونم', 1, '2026-07-23 15:55:56'),
(111, 23, 'user', 'شلام', 1, '2026-09-18 10:34:01'),
(112, 23, 'admin', 'سلام', 1, '2026-09-18 12:06:49');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter`
--

CREATE TABLE `newsletter` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `amount` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `payment_status` varchar(50) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'pending',
  `authority` varchar(255) DEFAULT NULL,
  `ref_id` varchar(255) DEFAULT NULL,
  `selected_color` varchar(50) DEFAULT NULL,
  `order_details_json` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `product_id`, `customer_name`, `phone`, `email`, `address`, `amount`, `quantity`, `payment_status`, `created_at`, `status`, `authority`, `ref_id`, `selected_color`, `order_details_json`) VALUES
(48, 8, 9, '????', '09120000001', 'demo.customer@example.com', 'dkvjkbvjxhcv hgc', 1050000, 1, '?? ?????? ??????', '2025-12-10 12:11:34', 'pending', NULL, NULL, NULL, '[{\"id\":\"9\",\"name\":\"?????\",\"color\":\"???\",\"qty\":1,\"price\":850000}]'),
(49, 8, 11, 'نیما', '09120000001', 'demo.customer@example.com', 'Fucdyfh🎟️🎫🎟️🤗🎫😄🤗🎟️🎫🤗', 195195564, 3, 'در انتظار پرداخت', '2025-12-20 01:27:09', 'pending', NULL, NULL, NULL, '[{\"id\":\"11\",\"name\":\"نيما حسین‌زاده\",\"color\":\"بنفش\",\"qty\":3,\"price\":65065065}]'),
(50, 18, 8, 'Nima81', '09120000001', 'demo.customer@example.com', 'ghghghghghgghgh', 750000, 1, 'در انتظار پرداخت', '2026-07-03 12:07:30', 'pending', NULL, NULL, NULL, '[{\"id\":\"8\",\"name\":\"کاپشن\",\"color\":\"طوسی\",\"qty\":1,\"price\":550000}]'),
(51, 18, 9, 'Nima81', '09120000001', 'demo.customer@example.com', 'llfjkcjkckbcjbkcvvbcvjkcjkbvxlkjv', 1050000, 1, 'در انتظار پرداخت', '2026-07-03 12:09:26', 'pending', NULL, NULL, NULL, '[{\"id\":\"9\",\"name\":\"کاپشن\",\"color\":\"سرمه‌ای\",\"qty\":1,\"price\":850000}]'),
(52, 0, 9, '', '', '', 'llfjkcjkckbcjbkcvvbcvjkcjkbvxlkjv', 1050000, 1, 'در انتظار پرداخت', '2026-07-03 13:02:48', 'pending', NULL, NULL, NULL, '[{\"id\":\"9\",\"name\":\"کاپشن\",\"color\":\"سرمه‌ای\",\"qty\":1,\"price\":850000}]'),
(53, 18, 9, 'Nima81', '09120000001', 'demo.customer@example.com', 'nnnnnnnnnnnnnnn', 1050000, 1, 'در انتظار پرداخت', '2026-07-03 13:07:03', 'pending', NULL, NULL, NULL, '[{\"id\":\"9\",\"name\":\"کاپشن\",\"color\":\"سرمه‌ای\",\"qty\":1,\"price\":850000}]'),
(54, 18, 9, 'Nima81', '09120000001', 'demo.customer@example.com', 'nnnnnnnnnnnnnnn', 1050000, 1, 'در انتظار پرداخت', '2026-07-03 13:12:44', 'pending', NULL, NULL, NULL, '[{\"id\":\"9\",\"name\":\"کاپشن\",\"color\":\"سرمه‌ای\",\"qty\":1,\"price\":850000}]'),
(55, 18, 9, 'Nima81', '09120000001', 'demo.customer@example.com', 'nnnnnnnnnnnnnnn', 1050000, 1, 'در انتظار پرداخت', '2026-07-03 13:13:23', 'pending', NULL, NULL, NULL, '[{\"id\":\"9\",\"name\":\"کاپشن\",\"color\":\"سرمه‌ای\",\"qty\":1,\"price\":850000}]'),
(56, 18, 8, 'Nima81', '09120000001', 'demo.customer@example.com', 'دددددددددددد', 750000, 1, 'در انتظار پرداخت', '2026-07-03 13:56:42', 'pending', NULL, NULL, NULL, '[{\"id\":\"8\",\"name\":\"کاپشن\",\"color\":\"طوسی\",\"qty\":1,\"price\":550000}]'),
(57, 18, 8, 'Nima81', '09120000001', 'demo.customer@example.com', 'دددددددددددد', 750000, 1, 'در انتظار پرداخت', '2026-07-03 14:15:49', 'pending', NULL, NULL, NULL, '[{\"id\":\"8\",\"name\":\"کاپشن\",\"color\":\"طوسی\",\"qty\":1,\"price\":550000}]'),
(58, 18, 5, 'Nima81', '09120000001', 'demo.customer@example.com', 'یمرمندرطتخهل الیبخاظکیابخخطبترخببدرتزت', 29500, 1, 'در انتظار پرداخت', '2026-07-17 11:58:14', 'pending', NULL, NULL, NULL, '[{\"id\":\"5\",\"name\":\"کاپشن\",\"color\":\"مشکی\",\"qty\":1,\"price\":9500}]'),
(59, 23, 9, 'علی حسین زاده', '09120000001', 'demo.customer@example.com', 'مدمددتنتنتذنتذتذذتذتتذاذاترترذاذت', 1050000, 1, 'در انتظار پرداخت', '2026-09-18 10:14:47', 'pending', NULL, NULL, NULL, '[{\"id\":\"9\",\"name\":\"کاپشن\",\"color\":\"سرمه‌ای\",\"qty\":1,\"price\":850000}]'),
(60, 23, 5, 'علی حسین زاده', '09120000001', 'demo.customer@example.com', 'خر خر خر حذذهرنراتدر', 29500, 1, 'در انتظار پرداخت', '2026-09-18 10:33:06', 'pending', NULL, NULL, NULL, '[{\"id\":\"5\",\"name\":\"کاپشن\",\"color\":\"مشکی\",\"qty\":1,\"price\":9500}]');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `price` int(11) NOT NULL,
  `image` text NOT NULL,
  `available_colors` text DEFAULT NULL,
  `gallery_images` longtext DEFAULT NULL,
  `shipping_cost` int(10) DEFAULT 0,
  `stock_quantity` int(11) DEFAULT 999,
  `on_sale` tinyint(1) DEFAULT 0,
  `featured` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `image`, `available_colors`, `gallery_images`, `shipping_cost`, `stock_quantity`, `on_sale`, `featured`) VALUES
(5, NULL, 'کاپشن', 'کیفیت آن واقعا بسیار خوب است', 10000, 'uploads/69242aa9222d6.png', 'مشکی:#000000,سبز:#22c55e,سرمه‌ای:#1e3a8a,کرم:#fef3c7,سفید:#ffffff,قرمز:#ef4444,آبی:#3b82f6,بنفش:#a855f7,زرد:#eab308,نارنجی:#ea580c,صورتی:#ec4899,طوسی:#6b7280,طلایی:#d4af37,قهوه‌ای:#78350f', '[\"uploads\\/69242aaa38ddd.png\",\"uploads\\/69242aab66bc3.png\"]', 20000, 999, 1, 0),
(6, NULL, 'کاپشن', 'بسار گرم و با کیفیت از آلمان', 100000, 'uploads/6924b41fae986.jpg', 'سفید:#ffffff,مشکی:#000000,سورمه ای:#0a003d,لجنی:#003d29', '[]', 20000, 999, 0, 0),
(7, 5, 'کاپشن', 'مهم نیست', 500000, 'uploads/6925c0be081ee.jpg', 'سورمه ای پر رنگ:#000242,مشکی:#000000', '[]', 200000, 999, 0, 0),
(8, NULL, 'کاپشن', 'مهمه', 550000, 'uploads/6925c1746b947.jpg', 'طوسی:#6b7280,مشکی:#000000,سرمه‌ای:#1e3a8a', '[]', 200000, 999, 0, 0),
(9, 3, 'کاپشن', 'اینم مهمه.\r\nنه مهم نیست.', 850000, 'uploads/6925c239280d7.jpg', 'سرمه‌ای:#1e3a8a,طوسی:#6b7280,مشکی:#000000,لجنی:#043800,کرم:#fef3c7', '[]', 200000, 999, 0, 0),
(10, 1, 'کاپشن', 'مهمه اما مهمه', 1500000, 'uploads/6925c37fdc9b2.png', 'لجنی:#003d0c,کرم:#fef3c7,مشکی:#000000', '[\"uploads\\/img_6a65cb3ecffe22.72014340.png\",\"uploads\\/img_6a65cb3ed292b6.10103552.png\",\"uploads\\/img_6a65cb3ed2f634.62465471.png\"]', 20000, 999, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(6) UNSIGNED NOT NULL,
  `product_id` int(6) UNSIGNED NOT NULL,
  `image_url` varchar(500) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recently_viewed`
--

CREATE TABLE `recently_viewed` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('pending','approved','rejected','deleted') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `rating` int(11) DEFAULT 5
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `user_id`, `name`, `phone`, `message`, `status`, `created_at`, `rating`) VALUES
(1, NULL, 'nima', '09120000001', 'بسیار عالی بسیار عالی', 'approved', '2026-07-23 10:27:26', 5),
(2, NULL, 'nima', '09120000001', 'نمنتدن', 'deleted', '2026-07-23 10:38:42', 5),
(3, NULL, 'nima', '09120000001', 'یبیبیذ', 'deleted', '2026-07-23 11:38:30', 5),
(5, NULL, 'nima2', '09120000001', 'خیلی عالی بود من که راضی بودم از همه خدماتشون', 'approved', '2026-07-23 12:37:22', 3),
(6, NULL, 'nima3', '09120000001', 'عالی', 'approved', '2026-07-23 14:25:31', 1),
(7, 22, 'ادمین', '09120000001', 'نه خوب نبود', 'approved', '2026-09-18 10:01:01', 4);

-- --------------------------------------------------------

--
-- Table structure for table `trending_items`
--

CREATE TABLE `trending_items` (
  `id` int(6) UNSIGNED NOT NULL,
  `title` varchar(100) NOT NULL,
  `subtitle` varchar(255) NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trending_items`
--

INSERT INTO `trending_items` (`id`, `title`, `subtitle`, `image_url`, `created_at`) VALUES
(1, 'Summer Vibe', 'کالکشن تابستانه با طراحی مینیمال', 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?q=80&w=2000', '2025-12-10 19:37:46'),
(2, 'Classic Men', 'کت و شلوارهای ایتالیایی دست‌دوز', 'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?q=80&w=2000', '2025-12-10 19:37:46');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_pic` varchar(500) DEFAULT 'https://cdn.jsdelivr.net/gh/microsoft/fluentui-emoji@latest/assets/Person/3D/person_3d.png',
  `verification_code` varchar(6) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `is_admin` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `phone`, `email`, `password`, `profile_pic`, `verification_code`, `is_verified`, `is_admin`, `created_at`) VALUES
(2, 'Sina', '09120000001', 'demo.customer@example.com', '$2y$10$tdA0JwRr29nR5QLzGf1jcevqeitQPCTTsK06TLpBiZi.T8aJMeNii', 'uploads/Profile_demo.jpg', NULL, 1, 0, '2025-11-20 09:36:31'),
(3, 'Nima', '093680548712', 'demo.customer@example.com', '$2y$10$XhyKcjpQ7keF/K5ikd8uHuWhSCc8KOW33TT080CNfCOoj0AlK.HZa', 'uploads/Profile_093680548712.png', NULL, 1, 0, '2025-11-20 10:44:20'),
(4, 'ال', '3521', 'ذبل', '$2y$10$V9eL1utY3O0B/stghb/xPesnOUR9EqaDjMVfXWC1mPOA93gz5C9ey', 'https://cdn.jsdelivr.net/gh/microsoft/fluentui-emoji@latest/assets/Person/3D/person_3d.png', '6619', 0, 0, '2025-11-20 12:02:16'),
(5, 'Yasin Sarooje', '09120000001', 'demo.customer@example.com', '$2y$10$OGIe3lsVswQibwoW7I0Dn.Bb.R29yUacyYKggW3cLPvtHi3W9wnz2', 'uploads/Profile_demo.jpg', NULL, 1, 0, '2025-11-22 04:23:58'),
(6, 'aref', '09120000001', 'demo.customer@example.com', '$2y$10$aL4NOjfUpTBKPLgR74JM7.5kklT5j5b2TMFLqkLBDs4DrGmHyFLjK', 'https://cdn.jsdelivr.net/gh/microsoft/fluentui-emoji@latest/assets/Person/3D/person_3d.png', NULL, 1, 0, '2025-11-23 08:20:35'),
(8, 'نیما', '09120000001', 'demo.customer@example.com', '$2y$10$L1bK4mt.W30P0X8TmOpUe.AHBAqZBaB/ITMxj4Yy/PcLNwVyoy2jW', 'uploads/Profile_8_1767619770.png', NULL, 1, 0, '2025-11-24 21:26:11'),
(9, 'System Alert', '0000000000', 'demo.customer@example.com', '123', 'https://cdn.jsdelivr.net/gh/microsoft/fluentui-emoji@latest/assets/Person/3D/person_3d.png', NULL, 1, 0, '2025-11-25 01:34:49'),
(10, 'کوروش جاوید', '09120000001', 'demo.customer@example.com', '$2y$10$2UVb0NucHtZe7TnLyi2jXefjPiOuNddUg2.eTpjjl0y./76OXAWmq', 'https://cdn.jsdelivr.net/gh/microsoft/fluentui-emoji@latest/assets/Person/3D/person_3d.png', '6553', 0, 0, '2025-12-10 08:51:04'),
(11, 'nima Nima2136', '09120000001', 'demo.customer@example.com', '$2y$10$/D/LPaMx2XMbawSp6YnAfeCGzir78BFYQvFkOKjGs8g3L.lFqA2zO', 'uploads/Profile_11_1781961726.png', NULL, 1, 0, '2026-06-20 16:47:33'),
(12, 'nima Nima2136', '09120000001', 'demo.customer@example.com', '$2y$10$jVHTCeXbS0ey9igRdmf3POXLT2xCYz2ywq1w1WYRTGGlNp62yzrGy', 'https://cdn.jsdelivr.net/gh/microsoft/fluentui-emoji@latest/assets/Person/3D/person_3d.png', '9743', 0, 0, '2026-06-23 07:20:00'),
(13, 'Nima2186', '09120000001', 'demo.customer@example.com', '$2y$10$QAl7IH9iVg8aeWYPPAu4R.csA1SIpEC2ZZeUwWoUoXjrDvUxoqI/u', 'https://cdn.jsdelivr.net/gh/microsoft/fluentui-emoji@latest/assets/Person/3D/person_3d.png', '7302', 0, 0, '2026-06-23 07:21:50'),
(14, 'nima Nima2136', '09120000001', 'demo.customer@example.com', '$2y$10$fK5BROSNf7JT0IINF/kh1ue4v.ZPdIJZrq1HsEHjkUDTzyfu.GAPi', 'https://cdn.jsdelivr.net/gh/microsoft/fluentui-emoji@latest/assets/Person/3D/person_3d.png', '5451', 0, 0, '2026-06-27 08:42:12'),
(15, 'nima Nima2136', '09120000001', 'demo.customer@example.com', '$2y$10$7IKp4WGbJYISq69/vV6tb.Su86yURl9myNNJhGqfhRHRTnYX9ztuC', 'https://cdn.jsdelivr.net/gh/microsoft/fluentui-emoji@latest/assets/Person/3D/person_3d.png', '5175', 0, 0, '2026-06-27 17:29:39'),
(16, 'Nima2186', '09120000001', 'demo.customer@example.com', '$2y$10$xkownUI3maeH.ODM//NlzO55cdslfeccq1jvnnhR0bPEZf3HCNYMu', 'https://cdn.jsdelivr.net/gh/microsoft/fluentui-emoji@latest/assets/Person/3D/person_3d.png', '3294', 0, 0, '2026-06-27 18:45:23'),
(17, 'Nima2186', '09120000001', 'demo.customer@example.com', '$2y$10$BNeBBDy/YfjZv.7KVcQaFea0S9akpPpfWCthHCL6Pz8FZv86xZTne', 'https://cdn.jsdelivr.net/gh/microsoft/fluentui-emoji@latest/assets/Person/3D/person_3d.png', '4715', 0, 0, '2026-06-27 22:35:05'),
(18, 'Nima Hossein zadeh', '09120000001', 'demo.customer@example.com', '$2y$10$JpjTF2MfjEavK9e0NUZ5Wub/aGQTUbdUN4B5xf8f2zzZ6sJsPLk5W', 'uploads/Profile_18_1784769912.png', NULL, 1, 0, '2026-07-03 11:53:37'),
(19, 'Nima25', '09120000001', 'demo.customer@example.com', '$2y$10$KUstypcrcFxhfEmimCzPWe4..QsdYCRE0uxR4CM6KW5tZt7vh9ka.', 'uploads/Profile_19_1783499193.png', NULL, 1, 0, '2026-07-08 11:00:24'),
(20, 'nima Nima2136', '09120000001', 'demo.customer@example.com', '$2y$10$KEcMBocpQysApXqn7X9jD.6YDJXjVlQN3Q2rG2TbhIjDvO8MFKmVy', 'uploads/Profile_20_1784181672.png', NULL, 1, 0, '2026-07-16 09:30:50'),
(21, 'دژهوت', '09120000001', 'demo.customer@example.com', '$2y$10$akDpWTuZUSDXK4bwzXk19e4v5xWllHOwgk9WCvbJXHYRqD0xY2buG', 'uploads/Profile_21_1784799814.png', NULL, 1, 0, '2026-07-23 13:13:08'),
(22, 'ادمین', '09120000001', 'demo.customer@example.com', '$2y$10$Bcu6SL0XGI9quPoFIEETj.5mo3a0Yg3x48CNndQvrgNzNAP7UF.pi', 'uploads/Profile_22_1785056819.png', NULL, 1, 1, '2026-07-26 12:28:14'),
(23, 'علی حسین زاده', '09120000001', 'demo.customer@example.com', '$2y$10$We9.UPdyeraxSuSFDZmjjuiz3Ka3b/6TEio.cLMNOWJkWA0cHM/.q', 'uploads/Profile_23_1789713857.png', NULL, 1, 0, '2026-09-18 10:13:55');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`id`, `user_id`, `product_id`, `created_at`) VALUES
(19, 21, 7, '2026-07-23 09:45:50'),
(25, 18, 10, '2026-07-31 18:31:38'),
(26, 23, 6, '2026-09-18 07:26:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ai_knowledge`
--
ALTER TABLE `ai_knowledge`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `keyword` (`keyword`);

--
-- Indexes for table `backgrounds`
--
ALTER TABLE `backgrounds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_created_at_bg` (`created_at`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `discounts`
--
ALTER TABLE `discounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_active` (`active`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id_msg` (`user_id`),
  ADD KEY `idx_sender` (`sender`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_created_at_msg` (`created_at`),
  ADD KEY `idx_user_read` (`user_id`,`is_read`),
  ADD KEY `idx_user_sender_read` (`user_id`,`sender`,`is_read`);

--
-- Indexes for table `newsletter`
--
ALTER TABLE `newsletter`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at_orders` (`created_at`),
  ADD KEY `idx_authority` (`authority`),
  ADD KEY `idx_user_status` (`user_id`,`status`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_price` (`price`),
  ADD KEY `idx_products_on_sale` (`on_sale`),
  ADD KEY `idx_products_stock` (`stock_quantity`),
  ADD KEY `idx_name_price` (`name`,`price`),
  ADD KEY `fk_product_category` (`category_id`),
  ADD KEY `idx_products_featured` (`featured`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_id` (`product_id`,`category_id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_category_id` (`category_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `recently_viewed`
--
ALTER TABLE `recently_viewed`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_viewed_at` (`viewed_at`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_id` (`product_id`,`user_id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_testimonials_user` (`user_id`);

--
-- Indexes for table `trending_items`
--
ALTER TABLE `trending_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_is_verified` (`is_verified`),
  ADD KEY `idx_created_at_users` (`created_at`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ai_knowledge`
--
ALTER TABLE `ai_knowledge`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `backgrounds`
--
ALTER TABLE `backgrounds`
  MODIFY `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `discounts`
--
ALTER TABLE `discounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT for table `newsletter`
--
ALTER TABLE `newsletter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recently_viewed`
--
ALTER TABLE `recently_viewed`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `trending_items`
--
ALTER TABLE `trending_items`
  MODIFY `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `discounts`
--
ALTER TABLE `discounts`
  ADD CONSTRAINT `discounts_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD CONSTRAINT `product_categories_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recently_viewed`
--
ALTER TABLE `recently_viewed`
  ADD CONSTRAINT `recently_viewed_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD CONSTRAINT `fk_testimonials_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
