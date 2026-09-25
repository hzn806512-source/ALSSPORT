<?php
// ============================================================
//  پنل مدیریت آلس اسپورت — نسخه ۴.۰ (بازطراحی کامل)
//  ساختار: Sidebar ثابت + نوار KPI + محتوای تب‌محور
// ============================================================
set_time_limit(600);
ini_set('max_execution_time', 600);
ini_set('post_max_size', '64M');
ini_set('upload_max_filesize', '64M');

include 'db.php';
require_once 'config.php';

// ============================================================
//  🔒 گارد امنیتی: فقط کاربر لاگین‌شده با is_admin=1 اجازه ورود داره
// ============================================================
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
$admin_check_stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
$admin_check_stmt->bind_param("i", $_SESSION['user_id']);
$admin_check_stmt->execute();
$admin_check_row = $admin_check_stmt->get_result()->fetch_assoc();
if (!$admin_check_row || intval($admin_check_row['is_admin']) !== 1) {
    header('Location: index.php');
    exit;
}

// ===== KPI ها =====
$kpi_products = $conn->query("SELECT COUNT(*) c FROM products")->fetch_assoc()['c'];
$kpi_orders = $conn->query("SELECT COUNT(*) c FROM orders")->fetch_assoc()['c'];
$kpi_pending = $conn->query("SELECT COUNT(*) c FROM orders WHERE status='pending'")->fetch_assoc()['c'];
$kpi_users = $conn->query("SELECT COUNT(*) c FROM users")->fetch_assoc()['c'];
$kpi_testimonials_pending = $conn->query("SELECT COUNT(*) c FROM testimonials WHERE status='pending'")->fetch_assoc()['c'];
// فقط سفارشات ارسال‌شده (status='sent') به عنوان فروش قطعی حساب میشن
$kpi_revenue = $conn->query("SELECT COALESCE(SUM(amount),0) s FROM orders WHERE status='sent'")->fetch_assoc()['s'];

$unread_count = 0;
$res_count = $conn->query("SELECT COUNT(DISTINCT user_id) as cnt FROM messages WHERE sender='user' AND is_read=0");
if ($res_count) {
    $unread_count = $res_count->fetch_assoc()['cnt'];
}

$pending_testimonials = [];
$testimonials_list_res = $conn->query("SELECT id, name, phone, message, rating, status, created_at FROM testimonials WHERE status IN ('pending','approved','rejected','deleted') ORDER BY created_at DESC LIMIT 50");
if ($testimonials_list_res) {
    while ($t = $testimonials_list_res->fetch_assoc()) {
        $pending_testimonials[] = $t;
    }
}

if (isset($_GET['refresh_testimonials']) && $_GET['refresh_testimonials'] == 1) {
    $kpi_testimonials_pending = $conn->query("SELECT COUNT(*) c FROM testimonials WHERE status='pending'")->fetch_assoc()['c'];
    $testimonials_list_res = $conn->query("SELECT id, name, phone, message, rating, status, created_at FROM testimonials WHERE status IN ('pending','approved','rejected','deleted') ORDER BY created_at DESC LIMIT 50");
    $pending_testimonials = [];
    if ($testimonials_list_res) {
        while ($t = $testimonials_list_res->fetch_assoc()) {
            $pending_testimonials[] = $t;
        }
    }
}

// ===== دسته‌بندی‌ها برای منوی انتخاب محصول =====
$all_categories = [];
$cat_list_res = $conn->query("SELECT id, name, icon FROM categories ORDER BY name ASC");
if ($cat_list_res) {
    while ($c = $cat_list_res->fetch_assoc()) {
        $all_categories[] = $c;
    }
}

// متغیرهای محصول
$edit_mode = false;
$edit_id = 0;
$p_name = "";
$p_desc = "";
$p_price = "";
$p_shipping = 0;
$p_image = "";
$p_colors = "";
$p_gallery = "[]";
$p_category_id = 0;

if (isset($_GET['edit_id'])) {
    $edit_mode = true;
    $edit_id = intval($_GET['edit_id']);
    $res = $conn->query("SELECT * FROM products WHERE id = $edit_id");
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $p_name = $row['name'];
        $p_desc = $row['description'];
        $p_price = $row['price'];
        $p_shipping = isset($row['shipping_cost']) ? $row['shipping_cost'] : 0;
        $p_image = $row['image'];
        $p_colors = $row['available_colors'];
        $p_gallery = $row['gallery_images'] ? $row['gallery_images'] : "[]";
        $p_category_id = isset($row['category_id']) ? intval($row['category_id']) : 0;
    }
}

// تابع آپلود
function uploadSingleFile($fileArray, $manualUrl)
{
    $final_path = "";

    if (!empty($manualUrl)) {
        return $manualUrl;
    }

    if (isset($fileArray) && $fileArray['error'] == 0) {
        $ext = strtolower(pathinfo($fileArray['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowed_exts, true)) {
            return "";
        }

        $has_cloudinary = !empty(CLOUDINARY_NAME) && !empty(CLOUDINARY_KEY) && !empty(CLOUDINARY_SECRET);
        if ($has_cloudinary) {
            try {
                $timestamp = time();
                $str = "timestamp=" . $timestamp . CLOUDINARY_SECRET;
                $sig = sha1($str);
                $cfile = new CURLFile($fileArray['tmp_name']);
                $post = ['file' => $cfile, 'api_key' => CLOUDINARY_KEY, 'timestamp' => $timestamp, 'signature' => $sig];
                $ch = curl_init("https://api.cloudinary.com/v1_1/" . CLOUDINARY_NAME . "/image/upload");
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 8);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                $res = json_decode(curl_exec($ch), true);
                curl_close($ch);
                if (isset($res['secure_url']) && !empty($res['secure_url'])) {
                    $final_path = $res['secure_url'];
                }
            } catch (Exception $e) {
            }
        }

        if (empty($final_path)) {
            $target_dir = UPLOAD_DIR;
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }

            $filename = uniqid('img_', true) . '.' . $ext;
            $local = $target_dir . $filename;
            if (move_uploaded_file($fileArray['tmp_name'], $local)) {
                $final_path = 'uploads/' . $filename;
            }
        }
    }

    return $final_path;
}

// ذخیره محصول
if (isset($_POST['save_product_btn'])) {
    $name = cleanInput($_POST['name']);
    $desc = cleanInput($_POST['description']);
    $price = intval($_POST['price']);
    $shipping = intval($_POST['shipping_cost']);
    $colors = cleanInput($_POST['final_colors_input']);

    $category_id = intval($_POST['category_id'] ?? 0);
    $category_id_param = $category_id > 0 ? $category_id : null;

    $final_image_path = $_POST['current_image_path'] ?? '';
    $up_main = uploadSingleFile($_FILES['image_file'], $_POST['image_url_manual']);
    if (!empty($up_main))
        $final_image_path = $up_main;
    if (empty($final_image_path))
        $final_image_path = "https://cdn-icons-png.flaticon.com/512/3159/3159614.png";

    $kept_gallery = [];
    $decoded_kept = json_decode($_POST['kept_gallery_json'] ?? '[]', true);
    if (is_array($decoded_kept))
        $kept_gallery = $decoded_kept;

    $new_gallery_urls = [];
    if (isset($_FILES['gallery_files']) && count($_FILES['gallery_files']['name']) > 0 && $_FILES['gallery_files']['name'][0] != "") {
        $count = count($_FILES['gallery_files']['name']);
        for ($i = 0; $i < $count; $i++) {
            $file_tmp = [
                'name' => $_FILES['gallery_files']['name'][$i],
                'type' => $_FILES['gallery_files']['type'][$i],
                'tmp_name' => $_FILES['gallery_files']['tmp_name'][$i],
                'error' => $_FILES['gallery_files']['error'][$i],
                'size' => $_FILES['gallery_files']['size'][$i]
            ];
            $url = uploadSingleFile($file_tmp, "");
            if (!empty($url))
                $new_gallery_urls[] = $url;
        }
    }
    $final_gallery = array_values(array_merge($kept_gallery, $new_gallery_urls));
    $gallery_json = json_encode($final_gallery, JSON_UNESCAPED_UNICODE);

    if (isset($_POST['is_edit']) && $_POST['is_edit'] == 1) {
        $id_to_update = intval($_POST['target_id']);
        $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, shipping_cost=?, image=?, available_colors=?, gallery_images=?, category_id=? WHERE id=?");
        $stmt->bind_param("ssiisssii", $name, $desc, $price, $shipping, $final_image_path, $colors, $gallery_json, $category_id_param, $id_to_update);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, shipping_cost, image, available_colors, gallery_images, category_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiisssi", $name, $desc, $price, $shipping, $final_image_path, $colors, $gallery_json, $category_id_param);
        $stmt->execute();
    }
    echo "<script>window.location.href='admin.php?tab=products';</script>";
    exit();
}

// ذخیره بک‌گراند
if (isset($_POST['save_bg_btn'])) {
    $bg_path = uploadSingleFile($_FILES['bg_file'], $_POST['bg_url_manual']);
    if (!empty($bg_path)) {
        $stmt = $conn->prepare("INSERT INTO backgrounds (image_url) VALUES (?)");
        $stmt->bind_param("s", $bg_path);
        $stmt->execute();
        echo "<script>window.location.href='admin.php?tab=backgrounds';</script>";
        exit();
    }
}

// حذف‌ها
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo "<script>window.location.href='admin.php?tab=products';</script>";
    exit();
}
if (isset($_GET['delete_bg_id'])) {
    $id = intval($_GET['delete_bg_id']);
    $stmt = $conn->prepare("DELETE FROM backgrounds WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo "<script>window.location.href='admin.php?tab=backgrounds';</script>";
    exit();
}

// ===== مدیریت نظرات مشتریان (تایید/رد/حذف) =====
if (isset($_GET['approve_testimonial_id'])) {
    $tid = intval($_GET['approve_testimonial_id']);
    $stmt = $conn->prepare("UPDATE testimonials SET status='approved' WHERE id = ?");
    $stmt->bind_param("i", $tid);
    $stmt->execute();
    echo "<script>window.location.href='admin.php?tab=dashboard';</script>";
    exit();
}
if (isset($_GET['reject_testimonial_id'])) {
    $tid = intval($_GET['reject_testimonial_id']);
    $stmt = $conn->prepare("UPDATE testimonials SET status='rejected' WHERE id = ?");
    $stmt->bind_param("i", $tid);
    $stmt->execute();

    $tstmt = $conn->prepare("SELECT user_id FROM testimonials WHERE id = ?");
    $tstmt->bind_param("i", $tid);
    $tstmt->execute();
    $trow = $tstmt->get_result()->fetch_assoc();
    $tstmt->close();

    if ($trow && !empty($trow['user_id'])) {
        $notify_uid = intval($trow['user_id']);
        $notify_msg = 'نظر شما از سمت ادمین سایت رد شد.';
        $mstmt = $conn->prepare("INSERT INTO messages (user_id, sender, message, is_read, created_at) VALUES (?, 'admin', ?, 0, NOW())");
        $mstmt->bind_param("is", $notify_uid, $notify_msg);
        $mstmt->execute();
        $mstmt->close();
    }

    echo "<script>window.location.href='admin.php?tab=dashboard';</script>";
    exit();
}
if (isset($_GET['delete_testimonial_id'])) {
    $tid = intval($_GET['delete_testimonial_id']);
    $stmt = $conn->prepare("UPDATE testimonials SET status='deleted' WHERE id = ?");
    $stmt->bind_param("i", $tid);
    $stmt->execute();
    echo "<script>window.location.href='admin.php?tab=dashboard';</script>";
    exit();
}

// ===== دسترسی پنل: افزودن/حذف دسترسی ادمین به کاربران =====
if (isset($_GET['grant_admin_id'])) {
    $gid = intval($_GET['grant_admin_id']);
    $conn->query("UPDATE users SET is_admin = 1 WHERE id = " . $gid);
    $qs = isset($_GET['q']) ? '&q=' . urlencode($_GET['q']) : '';
    echo "<script>window.location.href='admin.php?tab=access" . $qs . "';</script>";
    exit();
}
if (isset($_GET['revoke_admin_id'])) {
    $rid = intval($_GET['revoke_admin_id']);
    $qs = isset($_GET['q']) ? '&q=' . urlencode($_GET['q']) : '';
    if ($rid === intval($_SESSION['user_id'])) {
        echo "<script>alert('نمی‌توانید دسترسی مدیریت خودتان را حذف کنید.'); window.location.href='admin.php?tab=access" . $qs . "';</script>";
        exit();
    }
    $conn->query("UPDATE users SET is_admin = 0 WHERE id = " . $rid);
    echo "<script>window.location.href='admin.php?tab=access" . $qs . "';</script>";
    exit();
}

// تعیین تب فعال اولیه
$active_tab = $_GET['tab'] ?? 'dashboard';
if ($edit_mode)
    $active_tab = 'products';
$allowed_tabs = ['dashboard', 'products', 'categories', 'discounts', 'backgrounds', 'access'];
if (!in_array($active_tab, $allowed_tabs))
    $active_tab = 'dashboard';

// ===== داده‌های تب دسترسی پنل =====
$access_search = trim($_GET['q'] ?? '');
$users_list = [];
if ($active_tab === 'access') {
    if ($access_search !== '') {
        $like = '%' . $access_search . '%';
        $stmt = $conn->prepare("SELECT id, name, email, phone, is_admin FROM users WHERE name LIKE ? OR email LIKE ? OR phone LIKE ? ORDER BY name ASC");
        $stmt->bind_param("sss", $like, $like, $like);
        $stmt->execute();
        $users_list_res = $stmt->get_result();
    } else {
        $users_list_res = $conn->query("SELECT id, name, email, phone, is_admin FROM users ORDER BY name ASC LIMIT 200");
    }
    while ($u = $users_list_res->fetch_assoc()) {
        $users_list[] = $u;
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل مدیریت | آلس اسپورت</title>
    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/3159/3159614.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font/dist/font-face.css" rel="stylesheet"
        type="text/css" />
    <style>
        :root {
            --bg: #0b0f19;
            --panel: #121826;
            --panel-border: #1f2937;
            --sidebar-bg: #0d1220;
            --gold: #d4af37;
            --gold-dark: #b4932a;
            --text: #e5e7eb;
            --muted: #8b94a7;
            --danger: #ef4444;
            --success: #22c55e;
            --info: #4f46e5;
        }

        * {
            font-family: 'Vazir', sans-serif;
            box-sizing: border-box;
        }

        body {
            background: var(--bg);
            color: var(--text);
            margin: 0;
        }

        /* ===== Layout ===== */
        .admin-shell {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 260px;
            flex-shrink: 0;
            background: var(--sidebar-bg);
            border-left: 1px solid var(--panel-border);
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            z-index: 40;
            transition: transform .25s ease;
        }

        .sidebar-brand {
            padding: 20px 18px;
            border-bottom: 1px solid var(--panel-border);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-brand img {
            width: 30px;
            height: 30px;
        }

        .sidebar-nav {
            flex: 1;
            padding: 14px 10px;
            overflow-y: auto;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: 10px;
            color: var(--muted);
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            margin-bottom: 4px;
            transition: .18s;
            border: 1px solid transparent;
        }

        .sidebar-link:hover {
            background: #1a2233;
            color: #fff;
        }

        .sidebar-link.active {
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.16), rgba(212, 175, 55, 0.05));
            color: var(--gold);
            border-color: rgba(212, 175, 55, 0.25);
        }

        .sidebar-link .badge {
            margin-right: auto;
            background: var(--danger);
            color: #fff;
            font-size: 10px;
            padding: 1px 7px;
            border-radius: 9999px;
        }

        .sidebar-foot {
            padding: 14px;
            border-top: 1px solid var(--panel-border);
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .sidebar-foot a {
            font-size: 12px;
            color: var(--muted);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .sidebar-foot a:hover {
            color: #fff;
        }

        .main-area {
            flex: 1;
            min-width: 0;
        }

        .topbar {
            height: 64px;
            border-bottom: 1px solid var(--panel-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 22px;
            position: sticky;
            top: 0;
            background: rgba(11, 15, 25, 0.85);
            backdrop-filter: blur(10px);
            z-index: 30;
        }

        .topbar h1 {
            font-size: 16px;
            font-weight: 800;
            margin: 0;
        }

        .burger {
            display: none;
            background: none;
            border: none;
            color: #fff;
            font-size: 20px;
            cursor: pointer;
        }

        .content {
            padding: 22px;
            max-width: 1400px;
        }

        .admin-section {
            display: none;
        }

        .admin-section.active {
            display: block;
            animation: fadeIn .3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ===== KPI Cards ===== */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 14px;
            margin-bottom: 24px;
        }

        .kpi-card {
            background: var(--panel);
            border: 1px solid var(--panel-border);
            border-radius: 14px;
            padding: 16px 18px;
        }

        .kpi-card .kpi-label {
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
        }

        .kpi-card .kpi-value {
            color: #fff;
            font-size: 24px;
            font-weight: 800;
            margin-top: 6px;
        }

        /* ===== Panels / Cards ===== */
        .panel {
            background: var(--panel);
            border: 1px solid var(--panel-border);
            border-radius: 16px;
            padding: 20px;
        }

        .panel-title {
            font-size: 15px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--panel-border);
        }

        .admin-input {
            width: 100%;
            padding: 11px;
            background: #0b0f19;
            border: 1px solid #2a3244;
            color: #fff;
            border-radius: 9px;
            margin-top: 6px;
            font-size: 13px;
            transition: .2s;
        }

        .admin-input:focus {
            border-color: var(--gold);
            outline: none;
        }

        .admin-btn {
            width: 100%;
            padding: 11px;
            border: none;
            border-radius: 9px;
            cursor: pointer;
            font-weight: 800;
            font-size: 13px;
            margin-top: 12px;
            transition: .2s;
        }

        .btn-gold {
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            color: #111827;
        }

        .btn-gold:hover {
            filter: brightness(1.08);
        }

        .btn-info {
            background: linear-gradient(135deg, #4f46e5, #4338ca);
            color: #fff;
        }

        .btn-danger {
            background: #ef4444;
            color: #fff;
        }

        .order-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        .order-table th {
            color: var(--muted);
            text-align: right;
            padding: 10px 14px;
            font-size: 12px;
            font-weight: 700;
        }

        .order-table td {
            background: #0f1420;
        }

        .color-palette {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
            background: #0b0f19;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #2a3244;
        }

        .color-swatch {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid transparent;
            transition: .2s;
        }

        .color-swatch:hover {
            transform: scale(1.15);
            border-color: #fff;
        }

        .selected-colors-box {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            min-height: 38px;
            background: #1a2233;
            padding: 8px;
            border-radius: 8px;
            margin-top: 5px;
        }

        .color-tag {
            background: #0b0f19;
            color: #fff;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 11px;
            display: flex;
            align-items: center;
            gap: 5px;
            border: 1px solid #2a3244;
        }

        .color-tag span {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }

        .color-tag i {
            cursor: pointer;
            color: var(--danger);
            font-style: normal;
            font-weight: 800;
            margin-right: 3px;
        }

        #existing-gallery-grid img {
            width: 100%;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #2a3244;
        }

        .gallery-thumb-wrap {
            position: relative;
        }

        .remove-thumb-btn {
            position: absolute;
            top: -6px;
            right: -6px;
            background: var(--danger);
            color: #fff;
            border-radius: 9999px;
            width: 20px;
            height: 20px;
            border: none;
            font-size: 11px;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .4);
        }

        .product-mini-card {
            background: #0f1420;
            border: 1px solid #2a3244;
            border-radius: 12px;
            padding: 10px;
            position: relative;
        }

        .product-mini-card img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 8px;
            background: #1a2233;
        }

        .feature-alert {
            padding: 10px 14px;
            border-radius: 9px;
            font-size: 13px;
            margin-bottom: 14px;
            border: 1px solid;
        }

        .feature-alert.ok {
            background: rgba(34, 197, 94, .12);
            border-color: rgba(34, 197, 94, .4);
            color: #4ade80;
        }

        .feature-alert.err {
            background: rgba(239, 68, 68, .12);
            border-color: rgba(239, 68, 68, .4);
            color: #f87171;
        }

        .cat-row,
        .disc-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #0f1420;
            border: 1px solid #2a3244;
            padding: 10px 14px;
            border-radius: 10px;
            margin-bottom: 8px;
            font-size: 13px;
        }

        @media (max-width: 900px) {
            .sidebar {
                position: fixed;
                right: 0;
                top: 0;
                transform: translateX(100%);
                box-shadow: -10px 0 30px rgba(0, 0, 0, .5);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .burger {
                display: block;
            }

            .kpi-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>

<body>

    <div class="admin-shell">

        <!-- ===== Sidebar ===== -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <img src="https://cdn-icons-png.flaticon.com/512/3159/3159614.png" class="brightness-0 invert">
                <div>
                    <div style="font-weight:800; font-size:14px;">آلس <span style="color:var(--gold)">اسپورت</span>
                    </div>
                    <div style="font-size:10px; color:var(--muted);">پنل مدیریت</div>
                </div>
            </div>
            <nav class="sidebar-nav">
                <div class="sidebar-link <?php echo $active_tab === 'dashboard' ? 'active' : ''; ?>"
                    onclick="showSection('dashboard')">📊 <span>داشبورد</span></div>
                <div class="sidebar-link <?php echo $active_tab === 'products' ? 'active' : ''; ?>"
                    onclick="showSection('products')">👕 <span>محصولات</span></div>
                <div class="sidebar-link <?php echo $active_tab === 'categories' ? 'active' : ''; ?>"
                    onclick="showSection('categories')">📂 <span>دسته‌بندی‌ها</span></div>
                <div class="sidebar-link <?php echo $active_tab === 'discounts' ? 'active' : ''; ?>"
                    onclick="showSection('discounts')">🔥 <span>تخفیف‌ها</span></div>
                <div class="sidebar-link <?php echo $active_tab === 'backgrounds' ? 'active' : ''; ?>"
                    onclick="showSection('backgrounds')">🖼️ <span>پس‌زمینه</span></div>
                <div class="sidebar-link <?php echo $active_tab === 'access' ? 'active' : ''; ?>"
                    onclick="showSection('access')">🔑 <span>دسترسی پنل</span></div>
                <a href="admin_chat.php" class="sidebar-link" style="text-decoration:none;">💬 <span>پیام‌ها</span>
                    <?php if ($unread_count > 0): ?><span
                            class="badge"><?php echo $unread_count; ?></span><?php endif; ?>
                </a>
            </nav>
            <div class="sidebar-foot">
                <a href="index.php">🏪 مشاهده فروشگاه</a>
                <a href="auth.php?logout=true">🚪 خروج از حساب</a>
            </div>
        </aside>

        <!-- ===== Main Area ===== -->
        <div class="main-area">
            <div class="topbar">
                <div style="display:flex; align-items:center; gap:12px;">
                    <button class="burger"
                        onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
                    <h1 id="topbar-title">داشبورد</h1>
                </div>
                <div style="font-size:11px; color:var(--muted);">نسخه ۴.۰</div>
            </div>

            <div class="content">

                <!-- ================= DASHBOARD ================= -->
                <div class="admin-section <?php echo $active_tab === 'dashboard' ? 'active' : ''; ?>" id="section-dashboard">
                    <div class="kpi-grid">
                        <div class="kpi-card">
                            <div class="kpi-label">👕 محصولات</div>
                            <div class="kpi-value"><?php echo number_format($kpi_products); ?></div>
                        </div>
                        <div class="kpi-card">
                            <div class="kpi-label">📦 سفارشات کل</div>
                            <div class="kpi-value"><?php echo number_format($kpi_orders); ?></div>
                        </div>
                        <div class="kpi-card">
                            <div class="kpi-label">⏳ در انتظار</div>
                            <div class="kpi-value"><?php echo number_format($kpi_pending); ?></div>
                        </div>
                        <div class="kpi-card">
                            <div class="kpi-label">👤 کاربران</div>
                            <div class="kpi-value"><?php echo number_format($kpi_users); ?></div>
                        </div>
                        <div class="kpi-card">
                            <div class="kpi-label">💬 پیام نخوانده</div>
                            <div class="kpi-value"><?php echo number_format($unread_count); ?></div>
                        </div>
                        <div class="kpi-card">
                            <div class="kpi-label">� نظرات در انتظار</div>
                            <div class="kpi-value"><?php echo number_format($kpi_testimonials_pending); ?></div>
                        </div>
                        <div class="kpi-card">
                            <div class="kpi-label">💰 فروش قطعی (ارسال‌شده)</div>
                            <div class="kpi-value" style="font-size:16px;"><?php echo number_format($kpi_revenue); ?> ت
                            </div>
                        </div>
                    </div>

                    <div class="panel">
                        <div class="panel-title"
                            style="display:flex; justify-content:space-between; align-items:center;">
                            <span>📦 آخرین سفارشات</span>
                            <button onclick="fetchOrders()"
                                style="background:none;border:1px solid var(--gold);color:var(--gold);padding:4px 12px;border-radius:8px;font-size:11px;cursor:pointer;">بروزرسانی</button>
                        </div>
                        <div style="overflow-x:auto;">
                            <table class="order-table">
                                <thead>
                                    <tr>
                                        <th>محصول</th>
                                        <th>جزئیات/رنگ</th>
                                        <th>خریدار</th>
                                        <th>آدرس</th>
                                        <th>کل</th>
                                        <th>وضعیت</th>
                                        <th>عملیات</th>
                                    </tr>
                                </thead>
                                <tbody id="orders-table-body"></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="panel mt-6" id="testimonials-panel"
                        style="border:1px solid rgba(212,175,55,0.18); box-shadow:0 10px 30px rgba(0,0,0,0.28);">
                        <div class="panel-title"
                            style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; padding-bottom:10px; margin-bottom:12px;">
                            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                                <span style="font-size:16px; font-weight:800; color:#fff;">📝 مدیریت نظرات
                                    مشتریان</span>
                                <span
                                    style="font-size:11px; color:var(--muted); background:rgba(255,255,255,0.04); padding:4px 8px; border-radius:999px; border:1px solid rgba(255,255,255,0.08);">تأیید،
                                    رد یا حذف نظرها برای نمایش در «حرف مشتریان آلس»</span>
                            </div>
                            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                <a href="admin.php?tab=dashboard&refresh_testimonials=1#testimonials-panel"
                                    style="background:linear-gradient(135deg,#f8d46b,#f59e0b); color:#111827; padding:6px 12px; border-radius:10px; font-size:11px; font-weight:900; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">↻
                                    بروزرسانی دستی</a>
                            </div>
                        </div>
                        <div style="overflow-x:auto;">
                            <table class="order-table" style="min-width:900px;">
                                <thead>
                                    <tr>
                                        <th>نام</th>
                                        <th>شماره</th>
                                        <th>امتیاز</th>
                                        <th>متن نظر</th>
                                        <th>وضعیت</th>
                                        <th>تاریخ</th>
                                        <th>عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($pending_testimonials)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-gray-500 text-sm py-6">هنوز نظری برای
                                                بررسی وجود ندارد.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($pending_testimonials as $testimonial): ?>
                                            <?php
                                            $status_class = $testimonial['status'] === 'approved' ? 'text-green-400' : ($testimonial['status'] === 'rejected' ? 'text-red-400' : ($testimonial['status'] === 'deleted' ? 'text-gray-400' : 'text-yellow-400'));
                                            $status_title = $testimonial['status'] === 'approved' ? 'تأیید شده' : ($testimonial['status'] === 'rejected' ? 'رد شده' : ($testimonial['status'] === 'deleted' ? 'حذف شده' : 'در انتظار بررسی'));
                                            $rating = max(1, min(5, intval($testimonial['rating'] ?? 5)));
                                            $stars = str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
                                            ?>
                                            <tr style="background:rgba(15,20,32,0.92);">
                                                <td class="text-white text-xs font-bold" style="padding:14px 12px;">
                                                    <?php echo htmlspecialchars($testimonial['name']); ?></td>
                                                <td class="text-gray-300 text-xs" style="padding:14px 12px;">
                                                    <?php echo htmlspecialchars($testimonial['phone'] ?: '—'); ?></td>
                                                <td class="text-yellow-300 text-xs font-bold" style="padding:14px 12px;">
                                                    <?php echo $stars; ?></td>
                                                <td class="text-gray-300 text-xs max-w-[280px] break-words leading-6"
                                                    style="padding:14px 12px;">
                                                    «<?php echo htmlspecialchars($testimonial['message']); ?>»</td>
                                                <td class="text-xs font-bold <?php echo $status_class; ?>"
                                                    style="padding:14px 12px;"><?php echo $status_title; ?></td>
                                                <td class="text-gray-400 text-[10px]"
                                                    style="padding:14px 12px; white-space:nowrap;">
                                                    <?php echo htmlspecialchars(date('Y/m/d H:i', strtotime($testimonial['created_at']))); ?>
                                                </td>
                                                <td class="text-xs" style="padding:14px 12px;">
                                                    <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                                        <?php if ($testimonial['status'] === 'pending'): ?>
                                                            <a href="admin.php?tab=dashboard&approve_testimonial_id=<?php echo $testimonial['id']; ?>"
                                                                style="background:#16a34a; color:#fff; padding:5px 10px; border-radius:8px; text-decoration:none; font-weight:700;">تأیید</a>
                                                            <a href="admin.php?tab=dashboard&reject_testimonial_id=<?php echo $testimonial['id']; ?>"
                                                                style="background:#dc2626; color:#fff; padding:5px 10px; border-radius:8px; text-decoration:none; font-weight:700;">رد</a>
                                                        <?php elseif ($testimonial['status'] === 'approved'): ?>
                                                            <a href="admin.php?tab=dashboard&reject_testimonial_id=<?php echo $testimonial['id']; ?>"
                                                                style="background:#dc2626; color:#fff; padding:5px 10px; border-radius:8px; text-decoration:none; font-weight:700;">رد</a>
                                                        <?php elseif ($testimonial['status'] === 'rejected'): ?>
                                                            <a href="admin.php?tab=dashboard&approve_testimonial_id=<?php echo $testimonial['id']; ?>"
                                                                style="background:#16a34a; color:#fff; padding:5px 10px; border-radius:8px; text-decoration:none; font-weight:700;">تأیید</a>
                                                        <?php endif; ?>
                                                        <?php if ($testimonial['status'] !== 'deleted'): ?>
                                                            <a href="admin.php?tab=dashboard&delete_testimonial_id=<?php echo $testimonial['id']; ?>"
                                                                onclick="return confirm('این نظر حذف شود؟ بعد از حذف در بخش «حرف مشتریان آلس» نمایش داده نمی‌شود.')"
                                                                style="background:#374151; color:#fff; padding:5px 10px; border-radius:8px; text-decoration:none; font-weight:700;">حذف</a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ================= PRODUCTS ================= -->
                <div class="admin-section <?php echo $active_tab === 'products' ? 'active' : ''; ?>" id="section-products">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="lg:col-span-1">
                            <div class="panel">
                                <div class="panel-title">
                                    <?php echo $edit_mode ? '✏️ ویرایش محصول' : '➕ افزودن لباس جدید'; ?></div>
                                <form method="POST" enctype="multipart/form-data" id="productForm">
                                    <?php if ($edit_mode): ?>
                                        <input type="hidden" name="is_edit" value="1">
                                        <input type="hidden" name="target_id" value="<?php echo $edit_id; ?>">
                                        <input type="hidden" name="current_image_path"
                                            value="<?php echo htmlspecialchars($p_image); ?>">
                                    <?php endif; ?>

                                    <div>
                                        <label class="text-gray-400 text-xs">نام محصول</label>
                                        <input type="text" name="name" value="<?php echo htmlspecialchars($p_name); ?>"
                                            class="admin-input" required>
                                    </div>
                                    <div class="mt-3">
                                        <label class="text-gray-400 text-xs">توضیحات</label>
                                        <textarea name="description" rows="3" class="admin-input"
                                            required><?php echo htmlspecialchars($p_desc); ?></textarea>
                                    </div>
                                    <div class="mt-3">
                                        <label class="text-gray-400 text-xs">دسته‌بندی محصول</label>
                                        <select name="category_id" class="admin-input">
                                            <option value="0">— بدون دسته‌بندی —</option>
                                            <?php foreach ($all_categories as $cat): ?>
                                                <option value="<?php echo $cat['id']; ?>" <?php echo ($p_category_id == $cat['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars(($cat['icon'] ? $cat['icon'] . ' ' : '') . $cat['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if (empty($all_categories)): ?>
                                            <p class="text-[10px] mt-1" style="color:#fbbf24;">هنوز دسته‌بندی‌ای نساختید؛ از
                                                تب «دسته‌بندی‌ها» اضافه کنید.</p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 mt-3">
                                        <div><label class="text-gray-400 text-xs">قیمت (تومان)</label><input
                                                type="number" name="price" value="<?php echo $p_price; ?>"
                                                class="admin-input" required></div>
                                        <div><label class="text-xs font-bold" style="color:var(--gold);">هزینه
                                                ارسال</label><input type="number" name="shipping_cost"
                                                value="<?php echo $p_shipping; ?>" class="admin-input" placeholder="0">
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <label class="text-gray-400 text-xs font-bold block mb-2">رنگ‌های موجود</label>
                                        <input type="hidden" name="final_colors_input" id="final_colors_input"
                                            value="<?php echo htmlspecialchars($p_colors); ?>">
                                        <div id="selected-colors-display" class="selected-colors-box">
                                            <span class="text-xs text-gray-500 self-center w-full text-center">رنگی
                                                انتخاب نشده</span>
                                        </div>
                                        <div class="color-palette">
                                            <div class="color-swatch" style="background:#000000;"
                                                onclick="addColorTag('مشکی:#000000')"></div>
                                            <div class="color-swatch" style="background:#ffffff;"
                                                onclick="addColorTag('سفید:#ffffff')"></div>
                                            <div class="color-swatch" style="background:#ef4444;"
                                                onclick="addColorTag('قرمز:#ef4444')"></div>
                                            <div class="color-swatch" style="background:#3b82f6;"
                                                onclick="addColorTag('آبی:#3b82f6')"></div>
                                            <div class="color-swatch" style="background:#22c55e;"
                                                onclick="addColorTag('سبز:#22c55e')"></div>
                                            <div class="color-swatch" style="background:#eab308;"
                                                onclick="addColorTag('زرد:#eab308')"></div>
                                            <div class="color-swatch" style="background:#a855f7;"
                                                onclick="addColorTag('بنفش:#a855f7')"></div>
                                            <div class="color-swatch" style="background:#ea580c;"
                                                onclick="addColorTag('نارنجی:#ea580c')"></div>
                                            <div class="color-swatch" style="background:#ec4899;"
                                                onclick="addColorTag('صورتی:#ec4899')"></div>
                                            <div class="color-swatch" style="background:#6b7280;"
                                                onclick="addColorTag('طوسی:#6b7280')"></div>
                                            <div class="color-swatch" style="background:#d4af37;"
                                                onclick="addColorTag('طلایی:#d4af37')"></div>
                                            <div class="color-swatch" style="background:#78350f;"
                                                onclick="addColorTag('قهوه‌ای:#78350f')"></div>
                                            <div class="color-swatch" style="background:#1e3a8a;"
                                                onclick="addColorTag('سرمه‌ای:#1e3a8a')"></div>
                                            <div class="color-swatch" style="background:#fef3c7;border:1px solid #ccc"
                                                onclick="addColorTag('کرم:#fef3c7')"></div>
                                        </div>
                                        <div class="mt-2 flex gap-2 items-center">
                                            <input type="color" id="custom-color-picker"
                                                class="h-9 w-9 rounded cursor-pointer bg-transparent border-0 p-0">
                                            <input type="text" id="custom-color-name"
                                                class="admin-input !mt-0 !py-1 text-xs" placeholder="نام رنگ">
                                            <button type="button" onclick="addCustomColor()"
                                                class="bg-gray-700 text-white px-3 py-1 rounded text-xs border border-gray-500">افزودن</button>
                                        </div>
                                    </div>

                                    <div class="mt-4 p-3 rounded-lg border border-dashed border-gray-600">
                                        <label class="text-gray-400 text-xs block mb-2">1. تصویر اصلی</label>
                                        <?php if ($edit_mode && !empty($p_image)): ?>
                                            <img src="<?php echo $p_image; ?>"
                                                class="w-full h-28 object-cover rounded border border-gray-600 mb-2">
                                        <?php endif; ?>
                                        <input type="file" name="image_file" class="block w-full text-xs text-gray-400">
                                        <input type="text" name="image_url_manual"
                                            class="admin-input text-xs mt-2 text-center" placeholder="لینک مستقیم...">
                                    </div>

                                    <div class="mt-4 p-3 rounded-lg border border-dashed"
                                        style="border-color:rgba(212,175,55,.4);">
                                        <label class="text-xs block mb-2 font-bold" style="color:var(--gold);">2. گالری
                                            تصاویر</label>
                                        <div id="existing-gallery-grid" class="grid grid-cols-3 gap-2 mb-3"></div>
                                        <input type="hidden" name="kept_gallery_json" id="kept_gallery_json"
                                            value='<?php echo htmlspecialchars($p_gallery, ENT_QUOTES); ?>'>
                                        <input type="file" name="gallery_files[]" multiple
                                            class="block w-full text-xs text-gray-400">
                                        <p class="text-[10px] text-gray-500 mt-1">عکس‌هایی که ❌ نکنید نگه داشته میشن؛
                                            عکس جدید بهشون اضافه میشه.</p>
                                    </div>

                                    <button type="submit" name="save_product_btn"
                                        class="admin-btn btn-gold"><?php echo $edit_mode ? '💾 ذخیره تغییرات' : '🚀 انتشار محصول'; ?></button>
                                    <?php if ($edit_mode): ?><a href="admin.php?tab=products"
                                            class="block text-center text-gray-500 text-xs mt-3">انصراف</a><?php endif; ?>
                                </form>
                            </div>
                        </div>

                        <div class="lg:col-span-2">
                            <div class="panel">
                                <div class="panel-title">👕 محصولات موجود</div>
                                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4">
                                    <?php
                                    $prods = $conn->query("SELECT p.*, c.name AS cat_name FROM products p LEFT JOIN categories c ON p.category_id=c.id ORDER BY p.id DESC");
                                    if ($prods->num_rows > 0) {
                                        while ($p = $prods->fetch_assoc()) {
                                            $img = !empty($p['image']) ? $p['image'] : "https://cdn-icons-png.flaticon.com/512/3159/3159614.png";
                                            $catLabel = !empty($p['cat_name']) ? htmlspecialchars($p['cat_name']) : 'بدون دسته';
                                            echo "<div class='product-mini-card'>
                                            <img src='$img'>
                                            <div style='font-size:9px;color:#8b94a7;margin-bottom:4px;'>$catLabel</div>
                                            <div class='text-white text-xs font-bold truncate'>{$p['name']}</div>
                                            <div class='text-xs my-1' style='color:var(--gold);'>" . number_format($p['price']) . " ت</div>
                                            <div class='flex gap-1 mt-2'>
                                                <a href='admin.php?edit_id={$p['id']}' class='flex-1 text-center bg-blue-600 text-white text-[10px] py-1 rounded'>ویرایش</a>
                                                <a href='admin.php?delete_id={$p['id']}' onclick='return confirm(\"حذف؟\")' class='flex-1 text-center bg-red-600 text-white text-[10px] py-1 rounded'>حذف</a>
                                            </div>
                                        </div>";
                                        }
                                    } else {
                                        echo '<p class="col-span-full text-center text-gray-500 text-sm py-4">لیست خالی است.</p>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= CATEGORIES + DISCOUNTS (از admin_features.php) ================= -->
                <?php include 'admin_features.php'; ?>

                <!-- ================= BACKGROUNDS ================= -->
                <div class="admin-section <?php echo $active_tab === 'backgrounds' ? 'active' : ''; ?>"
                    id="section-backgrounds">
                    <div class="panel" style="max-width:520px;">
                        <div class="panel-title">🖼️ بک‌گراند متحرک فروشگاه</div>
                        <form method="POST" enctype="multipart/form-data">
                            <input type="file" name="bg_file" class="block w-full text-xs text-gray-400">
                            <input type="text" name="bg_url_manual" class="admin-input text-xs mt-2 text-center"
                                placeholder="لینک...">
                            <button type="submit" name="save_bg_btn" class="admin-btn btn-info">افزودن به
                                اسلایدر</button>
                        </form>
                        <div class="mt-4 grid grid-cols-4 gap-2">
                            <?php
                            $bgs = $conn->query("SELECT * FROM backgrounds ORDER BY id DESC");
                            while ($bg = $bgs->fetch_assoc()) {
                                echo "<div class='relative group'><img src='{$bg['image_url']}' class='w-full h-16 object-cover rounded'><a href='admin.php?delete_bg_id={$bg['id']}' onclick='return confirm(\"حذف؟\")' class='absolute inset-0 bg-red-500/80 hidden group-hover:flex items-center justify-center text-white text-xs font-bold rounded'>✕</a></div>";
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- ================= PANEL ACCESS ================= -->
                <?php include 'admin_access.php'; ?>

                <!-- ================= ACCESS (دسترسی پنل) ================= -->
                <div class="admin-section <?php echo $active_tab === 'access' ? 'active' : ''; ?>" id="section-access">
                    <div class="panel" style="max-width:900px;">
                        <div class="panel-title">🔑 دسترسی پنل مدیریت</div>

                        <form method="GET" style="display:flex; gap:8px; margin-bottom:18px;">
                            <input type="hidden" name="tab" value="access">
                            <input type="text" name="q" value="<?php echo htmlspecialchars($access_search); ?>"
                                placeholder="جستجو با نام، ایمیل یا شماره موبایل..." class="admin-input"
                                style="margin-top:0;">
                            <button type="submit" class="admin-btn btn-gold"
                                style="width:auto; white-space:nowrap; margin-top:0;">جستجو</button>
                            <?php if ($access_search !== ''): ?>
                                <a href="admin.php?tab=access" class="admin-btn"
                                    style="width:auto; white-space:nowrap; margin-top:0; background:#374151; color:#fff; display:flex; align-items:center; text-decoration:none;">پاک
                                    کردن</a>
                            <?php endif; ?>
                        </form>

                        <?php if (empty($users_list)): ?>
                            <p class="text-gray-500 text-sm">اگر کابربری یافت نشد صفحه را رفرشت کنید.</p>
                        <?php else: ?>
                            <?php foreach ($users_list as $u): ?>
                                <div class="cat-row">
                                    <div>
                                        <strong class="text-white"><?php echo htmlspecialchars($u['name']); ?></strong>
                                        <span class="text-gray-500 text-xs"> — <?php echo htmlspecialchars($u['email']); ?> —
                                            <?php echo htmlspecialchars($u['phone']); ?></span>
                                        <?php if (intval($u['is_admin']) === 1): ?>
                                            <span class="badge-featured"
                                                style="margin-right:8px; font-size:9px; padding:2px 8px;">ادمین</span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <?php if (intval($u['is_admin']) === 1): ?>
                                            <a href="admin.php?tab=access&revoke_admin_id=<?php echo $u['id']; ?>&q=<?php echo urlencode($access_search); ?>"
                                                onclick="return confirm('دسترسی مدیریت این کاربر حذف بشه؟')"
                                                class="bg-red-600 text-white text-[10px] px-3 py-1.5 rounded">حذف از
                                                دسترسی‌پذیران</a>
                                        <?php else: ?>
                                            <a href="admin.php?tab=access&grant_admin_id=<?php echo $u['id']; ?>&q=<?php echo urlencode($access_search); ?>"
                                                onclick="return confirm('این کاربر به پنل مدیریت دسترسی پیدا کنه؟')"
                                                class="bg-green-600 text-white text-[10px] px-3 py-1.5 rounded">افزودن به
                                                دسترسی‌پذیران</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        const sectionTitles = { dashboard: 'داشبورد', products: 'محصولات', categories: 'دسته‌بندی‌ها', discounts: 'تخفیف‌ها', backgrounds: 'پس‌زمینه', access: 'دسترسی پنل' };

        function showSection(name) {
            document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
            const target = document.getElementById('section-' + name);
            if (target) target.classList.add('active');
            const link = Array.from(document.querySelectorAll('.sidebar-link')).find(l => l.getAttribute('onclick') === `showSection('${name}')`);
            if (link) link.classList.add('active');
            document.getElementById('topbar-title').innerText = sectionTitles[name] || 'داشبورد';
            document.getElementById('sidebar').classList.remove('open');
            history.replaceState(null, '', '?tab=' + name);
        }

        // --- رنگ‌ها ---
        let selectedColors = [];
        window.addEventListener('DOMContentLoaded', () => {
            const initial = document.getElementById('final_colors_input')?.value;
            if (initial) {
                initial.split(',').forEach(p => {
                    p = p.trim();
                    if (p) selectedColors.push(p.includes(':') ? p : p + ':#000000');
                });
                renderColors();
            }
        });
        function addColorTag(c) { if (!selectedColors.includes(c)) { selectedColors.push(c); renderColors(); } }
        function addCustomColor() {
            const hex = document.getElementById('custom-color-picker').value;
            const name = document.getElementById('custom-color-name').value.trim();
            if (name) { addColorTag(`${name}:${hex}`); document.getElementById('custom-color-name').value = ''; }
            else alert('نام رنگ را بنویسید');
        }
        function removeColor(i) { selectedColors.splice(i, 1); renderColors(); }
        function renderColors() {
            const box = document.getElementById('selected-colors-display');
            const input = document.getElementById('final_colors_input');
            if (!box || !input) return;
            box.innerHTML = selectedColors.length === 0 ? '<span class="text-xs text-gray-500 self-center w-full text-center">رنگی انتخاب نشده</span>' : '';
            selectedColors.forEach((c, i) => {
                const [name, hex] = c.split(':');
                box.innerHTML += `<div class="color-tag"><span style="background:${hex || '#000'}"></span>${name}<i onclick="removeColor(${i})">×</i></div>`;
            });
            input.value = selectedColors.join(',');
        }

        // --- گالری ---
        let existingGalleryImages = [];
        (function initGallery() {
            try {
                const el = document.getElementById('kept_gallery_json');
                existingGalleryImages = el ? (JSON.parse(el.value || '[]') || []) : [];
                if (!Array.isArray(existingGalleryImages)) existingGalleryImages = [];
            } catch (e) { existingGalleryImages = []; }
            renderExistingGallery();
        })();
        function renderExistingGallery() {
            const grid = document.getElementById('existing-gallery-grid');
            if (!grid) return;
            grid.innerHTML = existingGalleryImages.length === 0
                ? '<p class="col-span-3 text-[10px] text-gray-500 text-center py-2">تصویری نیست</p>' : '';
            existingGalleryImages.forEach((url, idx) => {
                const wrap = document.createElement('div');
                wrap.className = 'gallery-thumb-wrap';
                wrap.innerHTML = `<img src="${url}"><button type="button" class="remove-thumb-btn" onclick="removeExistingGalleryImage(${idx})">×</button>`;
                grid.appendChild(wrap);
            });
            const hidden = document.getElementById('kept_gallery_json');
            if (hidden) hidden.value = JSON.stringify(existingGalleryImages);
        }
        function removeExistingGalleryImage(idx) {
            if (!confirm('این تصویر حذف بشه؟')) return;
            existingGalleryImages.splice(idx, 1);
            renderExistingGallery();
        }

        // --- سفارشات ---
        function fetchOrders() {
            const tbody = document.getElementById('orders-table-body');
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-gray-500 py-10 text-sm">درحال بارگذاری...</td></tr>';
            fetch('get_orders.php').then(r => r.text()).then(html => tbody.innerHTML = html);
        }
        function notifyUser(uId, oId, rId) {
            if (!confirm("ارسال شود؟")) return;
            const row = document.getElementById(rId); if (row) row.style.opacity = '0.5';
            const fd = new FormData(); fd.append('action', 'notify_delivery'); fd.append('user_id', uId); fd.append('order_id', oId);
            fetch('auth.php', { method: 'POST', body: fd }).then(r => r.json()).then(d => {
                if (d.status === 'success') { if (row) row.remove(); const tb = document.getElementById('orders-table-body'); if (tb.children.length === 0) fetchOrders(); }
                else { alert("Error: " + d.message); if (row) row.style.opacity = '1'; }
            });
        }
        fetchOrders();

        document.addEventListener('DOMContentLoaded', () => {
            const activeSection = document.querySelector('.admin-section.active');
            if (activeSection) {
                const name = activeSection.id.replace('section-', '');
                document.getElementById('topbar-title').innerText = sectionTitles[name] || 'داشبورد';
            }
        });
    </script>
</body>

</html>