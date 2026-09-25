<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
/* ==========================================================================
   home.php — لندینگ‌پیج اصلی آلس اسپورت (بوتیک لوکس مردانه)
   ========================================================================== */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!file_exists(__DIR__ . '/db.php')) {
    die('خطا: فایل db.php پیدا نشد.');
}
include 'db.php';

if (!isset($conn) || !($conn instanceof mysqli) || $conn->connect_errno) {
    die('خطا در اتصال به دیتابیس.');
}

if (!file_exists(__DIR__ . '/header.php')) {
    die('خطا: فایل header.php پیدا نشد.');
}
include 'header.php';

try {
    $categories = [];
    $catRes = $conn->query("SELECT id, name, icon FROM categories ORDER BY id ASC");
    if ($catRes) {
        while ($c = $catRes->fetch_assoc()) {
            $categories[] = $c;
        }
    }

    $wishlist_ids = [];
    if (isset($_SESSION['user_id'])) {
        $wu = intval($_SESSION['user_id']);
        $wres = $conn->query("SELECT product_id FROM wishlist WHERE user_id = $wu");
        while ($wres && $wrow = $wres->fetch_assoc()) {
            $wishlist_ids[] = (int) $wrow['product_id'];
        }
    }

    $discountJoin = "LEFT JOIN discounts d ON d.product_id = p.id AND d.active = 1";

    $latest = [];
    $r = $conn->query("SELECT p.*, d.percentage AS discount_percent FROM products p $discountJoin ORDER BY p.id DESC LIMIT 8");
    while ($r && $row = $r->fetch_assoc()) {
        $latest[] = $row;
    }

    $signature = [];
    $r = $conn->query("SELECT p.*, d.percentage AS discount_percent FROM products p $discountJoin WHERE p.featured = 1 ORDER BY p.id DESC LIMIT 3");
    while ($r && $row = $r->fetch_assoc()) {
        $signature[] = $row;
    }
    if (empty($signature)) {
        $r = $conn->query("SELECT p.*, d.percentage AS discount_percent FROM products p $discountJoin ORDER BY p.price DESC LIMIT 3");
        while ($r && $row = $r->fetch_assoc()) {
            $signature[] = $row;
        }
    }

    $exclusive = [];
    $r = $conn->query("SELECT p.*, d.percentage AS discount_percent FROM products p $discountJoin WHERE p.on_sale = 1 ORDER BY p.id DESC LIMIT 4");
    while ($r && $row = $r->fetch_assoc()) {
        $exclusive[] = $row;
    }
    if (empty($exclusive)) {
        $r = $conn->query("SELECT p.*, d.percentage AS discount_percent FROM products p $discountJoin ORDER BY p.id DESC LIMIT 4");
        while ($r && $row = $r->fetch_assoc()) {
            $exclusive[] = $row;
        }
    }

    $essentials = [];
    $r = $conn->query("SELECT p.*, d.percentage AS discount_percent FROM products p $discountJoin ORDER BY p.stock_quantity DESC LIMIT 4");
    while ($r && $row = $r->fetch_assoc()) {
        $essentials[] = $row;
    }

    $accCatId = 0;
    foreach ($categories as $c) {
        $hasAcc = function_exists('mb_strpos') ? mb_strpos($c['name'], 'اکسسوری') !== false : strpos($c['name'], 'اکسسوری') !== false;
        if ($hasAcc) {
            $accCatId = $c['id'];
            break;
        }
    }
    $accessories = [];
    if ($accCatId) {
        $r = $conn->query("SELECT p.*, d.percentage AS discount_percent FROM products p $discountJoin WHERE p.category_id = $accCatId ORDER BY p.id DESC LIMIT 4");
        while ($r && $row = $r->fetch_assoc()) {
            $accessories[] = $row;
        }
    }
    if (empty($accessories)) {
        $accessories = array_slice($latest, 0, 4);
    }

    $testimonials = [];
    $tres = $conn->query("
        SELECT t.id, t.message, t.rating, t.created_at, t.name AS legacy_name,
               u.name AS user_name, u.profile_pic
        FROM testimonials t
        LEFT JOIN users u ON t.user_id = u.id
        WHERE t.status = 'approved'
        ORDER BY t.created_at DESC
        LIMIT 9
    ");
    if ($tres) {
        while ($row = $tres->fetch_assoc()) {
            $testimonials[] = $row;
        }
    }

    $current_user = null;
    if (isset($_SESSION['user_id'])) {
        $cu_uid = intval($_SESSION['user_id']);
        $custmt = $conn->prepare("SELECT name, profile_pic FROM users WHERE id = ?");
        $custmt->bind_param("i", $cu_uid);
        $custmt->execute();
        $current_user = $custmt->get_result()->fetch_assoc();
        $custmt->close();
    }

    function als_final_price($row)
    {
        $has_discount = !empty($row['on_sale']) && !empty($row['discount_percent']);
        $final = $row['price'];
        if ($has_discount) {
            $final = intval($row['price'] - ($row['price'] * $row['discount_percent'] / 100));
        }
        return [$has_discount, $final];
    }

    $IMG_HERO = "https://images.unsplash.com/photo-1593029976568-fe9c5d6e4317?w=1920&q=80&auto=format&fit=crop";
    $IMG_STYLE = "https://images.unsplash.com/photo-1611799298578-0ed239e049fe?w=1400&q=80&auto=format&fit=crop";
    $IMG_CRAFT = "https://images.unsplash.com/photo-1762417421173-6a9766a5b41d?w=1400&q=80&auto=format&fit=crop";
    $IMG_BOUTIQUE = "https://images.unsplash.com/photo-1772570824145-e996a55204fb?w=1200&h=1400&q=80&auto=format&fit=crop";
    $IG_GRID = [
        "https://images.unsplash.com/photo-1593029976568-fe9c5d6e4317?w=500&h=500&q=80&auto=format&fit=crop",
        "https://images.unsplash.com/photo-1772570824145-e996a55204fb?w=500&h=500&q=80&auto=format&fit=crop",
        "https://images.unsplash.com/photo-1611799298578-0ed239e049fe?w=500&h=500&q=80&auto=format&fit=crop",
        "https://images.unsplash.com/photo-1762417421173-6a9766a5b41d?w=500&h=500&q=80&auto=format&fit=crop",
    ];

    function svg_heart($filled = false)
    {
        $fill = $filled ? 'currentColor' : 'none';
        return '<svg width="16" height="16" viewBox="0 0 24 24" fill="' . $fill . '" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7.2-4.4-9.7-8.8C.7 8.7 1.8 5 5.4 4.5c2.1-.3 3.9.8 4.8 2.3.9-1.5 2.7-2.6 4.8-2.3C18.6 5 19.7 8.7 18.1 12.2 15.6 16.6 12 21 12 21Z"></path></svg>';
    }
} catch (Exception $e) {
    // Fail gracefully
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>آلس اسپورت | بوتیک لوکس پوشاک مردانه</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font/dist/font-face.css" rel="stylesheet">
    <style>
        body { font-family: 'Vazir', sans-serif; background: #0b1120; color: #f3f4f6; }
        .gold-btn { background: linear-gradient(135deg, #f0d060, #d4af37); color: #111; font-weight: bold; border-radius: 12px; transition: 0.3s; }
        .gold-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(212,175,55,0.3); }
        .als-card { background: #161f30; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; overflow: hidden; transition: 0.3s; }
        .als-card:hover { transform: translateY(-6px); border-color: #d4af37; box-shadow: 0 20px 40px rgba(212,175,55,0.15); }
        .wishlist-heart {
            position: absolute;
            top: 8px;
            left: 8px;
            z-index: 5;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(6px);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            cursor: pointer;
            transition: transform .3s ease, background-color .3s linear, border-color .3s linear;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        .wishlist-heart:hover { background: rgba(239, 68, 68, 0.85); transform: scale(1.12); }
        .wishlist-heart.active { background: #ef4444; border-color: rgba(255, 255, 255, .3); }
        .wishlist-heart.pop { animation: heartPop .5s ease; }
        @keyframes heartPop { 0%{transform:scale(1);} 35%{transform:scale(1.35);} 60%{transform:scale(.94);} 100%{transform:scale(1);} }
        .wishlist-heart::before { content:''; position:absolute; inset:0; border-radius:50%; border:2px solid rgba(239,68,68,.8); opacity:0; pointer-events:none; }
        .wishlist-heart.pop::before { animation: heartRing .6s ease; }
        @keyframes heartRing { from{opacity:.9; transform:scale(1);} to{opacity:0; transform:scale(2.1);} }
    </style>
</head>
<body class="bg-[#0b1120] text-gray-100 min-h-screen">

    <!-- Hero Section -->
    <section class="relative min-h-[80vh] flex items-center justify-center text-center px-4 overflow-hidden" style="background: linear-gradient(rgba(11,17,32,0.85), rgba(11,17,32,0.95)), url('<?php echo $IMG_HERO; ?>') center/cover no-repeat;">
        <div class="max-w-4xl mx-auto z-10 py-16">
            <span class="inline-block text-[#d4af37] font-bold text-sm tracking-widest uppercase mb-4 px-4 py-1.5 rounded-full bg-[#d4af37]/10 border border-[#d4af37]/20">مجموعه انحصاری مردانه</span>
            <h1 class="text-4xl md:text-6xl font-black tracking-tight mb-6 leading-tight">پوشاک لوکس و فاخر <span class="text-[#d4af37]">آلس اسپورت</span></h1>
            <p class="text-gray-300 text-base md:text-lg max-w-2xl mx-auto mb-8 leading-relaxed">ترکیبی بی‌نظیر از استایل مدرن، اصالت و دوخت عالی برای آقایان شیک‌پوش. از لباس‌های ورزشی تا استایل رسمی و کژوال.</p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="index.php" class="gold-btn px-8 py-4 rounded-xl text-base shadow-lg">مشاهده محصولات</a>
                <a href="#featured" class="px-8 py-4 rounded-xl text-base border border-white/20 hover:border-[#d4af37] hover:text-[#d4af37] transition">جدیدترین کلکسیون</a>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="py-16 px-4 max-w-7xl mx-auto">
        <div class="text-center mb-12">
            <h2 class="text-2xl md:text-3xl font-black mb-3">دسته‌بندی‌های اصلی</h2>
            <p class="text-gray-400 text-sm">پوشاک و اکسسوری متناسب با هر سبک و سلیقه</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <?php foreach($categories as $cat): ?>
                <a href="index.php?cat=<?php echo $cat['id']; ?>" class="bg-[#161f30] border border-white/10 p-6 rounded-2xl text-center hover:border-[#d4af37] transition group">
                    <div class="text-3xl mb-3 group-hover:scale-110 transition duration-300"><?php echo htmlspecialchars($cat['icon'] ?? '👔'); ?></div>
                    <h3 class="font-bold text-base group-hover:text-[#d4af37] transition"><?php echo htmlspecialchars($cat['name']); ?></h3>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Featured Products Section -->
<section id="featured" class="py-16 px-4 max-w-7xl mx-auto">
    <div class="flex justify-between items-end mb-12">
        <div>
            <span class="text-[#d4af37] text-xs font-bold tracking-widest uppercase">پیشنهاد ویژه</span>
            <h2 class="text-2xl md:text-3xl font-black mt-1">جدیدترین محصولات</h2>
        </div>
        <a href="index.php" class="text-sm text-[#d4af37] hover:underline font-bold">مشاهده همه &larr;</a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-6">
        <?php foreach($latest as $p): 
            list($has_disc, $final_price) = als_final_price($p);
            $is_fav = in_array((int)$p['id'], $wishlist_ids);
        ?>
            <div class="als-card relative flex flex-col">
                <div class="wishlist-heart <?php echo $is_fav ? 'active' : ''; ?>" role="button" tabindex="0"
                    aria-label="افزودن به علاقه‌مندی"
                    onclick="toggleWishlist(<?php echo $p['id']; ?>, this)">
                    <?php echo $is_fav ? '❤️' : '🤍'; ?>
                </div>
                <div class="relative aspect-square overflow-hidden bg-[#0b1120]">
                    <img src="<?php echo htmlspecialchars(!empty($p['image']) ? $p['image'] : 'https://images.unsplash.com/photo-1593029976568-fe9c5d6e4317?w=500&q=80'); ?>" 
                         alt="<?php echo htmlspecialchars($p['name']); ?>" 
                         class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    <?php if($has_disc): ?>
                        <span class="absolute top-3 right-3 bg-red-600 text-white text-xs font-bold px-2.5 py-1 rounded-full"><?php echo $p['discount_percent']; ?>% تخفیف</span>
                    <?php endif; ?>
                </div>
                <div class="p-5 flex flex-col flex-grow justify-between">
                    <div>
                        <h3 class="font-bold text-base mb-2 line-clamp-1"><?php echo htmlspecialchars($p['name']); ?></h3>
                        <p class="text-gray-400 text-xs line-clamp-2 mb-4"><?php echo htmlspecialchars($p['description']); ?></p>
                    </div>
                    <div class="flex items-center justify-between mt-auto">
                        <div>
                            <?php if($has_disc): ?>
                                <span class="text-gray-400 text-xs line-through block"><?php echo number_format($p['price']); ?> تومان</span>
                            <?php endif; ?>
                            <span class="font-black text-[#d4af37] text-sm md:text-base"><?php echo number_format($final_price); ?> <span class="text-xs font-normal">تومان</span></span>
                        </div>
                        <a href="order.php?id=<?php echo $p['id']; ?>" class="gold-btn px-4 py-2 text-xs rounded-xl">خرید</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-16 px-4 max-w-7xl mx-auto" id="testimonials">
    <div class="text-center mb-12">
        <span class="text-[#d4af37] text-xs font-bold tracking-widest uppercase">حرف مشتریان</span>
        <h2 class="text-2xl md:text-3xl font-black mt-1">نظرات کاربران آلس اسپورت</h2>
    </div>

    <?php if (!empty($testimonials)): ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
        <?php foreach ($testimonials as $t):
            $t_display_name = !empty($t['user_name']) ? $t['user_name'] : ($t['legacy_name'] ?: 'کاربر آلس اسپورت');
            $t_avatar = !empty($t['profile_pic']) ? $t['profile_pic'] : 'https://cdn.jsdelivr.net/gh/microsoft/fluentui-emoji@latest/assets/Person/3D/person_3d.png';
            $t_rating = max(1, min(5, intval($t['rating'] ?? 5)));
            $t_stars = str_repeat('★', $t_rating) . str_repeat('☆', 5 - $t_rating);
        ?>
            <div class="als-card p-6 flex flex-col">
                <div class="flex items-center gap-3 mb-4">
                    <img src="<?php echo htmlspecialchars($t_avatar); ?>" alt="<?php echo htmlspecialchars($t_display_name); ?>" class="w-11 h-11 rounded-full object-cover border border-white/10 flex-shrink-0">
                    <div class="min-w-0">
                        <h4 class="font-bold text-sm truncate"><?php echo htmlspecialchars($t_display_name); ?></h4>
                        <span class="text-yellow-300 text-xs"><?php echo $t_stars; ?></span>
                    </div>
                </div>
                <p class="text-gray-300 text-sm leading-7 flex-grow">«<?php echo htmlspecialchars($t['message']); ?>»</p>
                <span class="text-gray-500 text-[10px] mt-4"><?php echo htmlspecialchars(date('Y/m/d', strtotime($t['created_at']))); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
        <p class="text-center text-gray-500 text-sm mb-12">هنوز نظری ثبت نشده است. اولین نفر باشید!</p>
    <?php endif; ?>

    <div class="max-w-xl mx-auto als-card p-6">
        <h3 class="font-bold text-base mb-4 text-center">نظر خود را ثبت کنید</h3>
        <?php if ($current_user): ?>
            <div id="testimonialFormWrap">
                <div class="flex items-center gap-3 mb-4">
                    <img src="<?php echo htmlspecialchars(!empty($current_user['profile_pic']) ? $current_user['profile_pic'] : 'https://cdn.jsdelivr.net/gh/microsoft/fluentui-emoji@latest/assets/Person/3D/person_3d.png'); ?>" class="w-10 h-10 rounded-full object-cover border border-white/10">
                    <span class="font-bold text-sm"><?php echo htmlspecialchars($current_user['name']); ?></span>
                </div>
                <div class="flex gap-1 mb-4 justify-center" id="starPicker" dir="ltr">
                    <?php for ($si = 1; $si <= 5; $si++): ?>
                        <span class="star-pick text-2xl cursor-pointer text-gray-600" data-val="<?php echo $si; ?>">★</span>
                    <?php endfor; ?>
                </div>
                <textarea id="testimonialMessage" rows="3" maxlength="500" placeholder="تجربه خود از آلس اسپورت را بنویسید..." class="w-full bg-[#0b1120] border border-white/10 rounded-xl p-3 text-sm text-gray-200 focus:outline-none focus:border-[#d4af37] transition"></textarea>
                <button onclick="submitTestimonial()" class="gold-btn w-full py-3 rounded-xl text-sm mt-4">ثبت نظر</button>
                <p id="testimonialFeedback" class="text-center text-xs mt-3"></p>
            </div>
        <?php else: ?>
            <p class="text-center text-gray-400 text-sm mb-4">برای ثبت نظر ابتدا وارد حساب کاربری خود شوید.</p>
            <button onclick="if(typeof openAuthModal==='function'){openAuthModal();}else{window.location.href='index.php';}" class="gold-btn w-full py-3 rounded-xl text-sm">ورود / ثبت‌نام</button>
        <?php endif; ?>
    </div>
</section>
<script>
(function(){
    var selectedRating = 0;
    var stars = document.querySelectorAll('.star-pick');
    stars.forEach(function(s){
        s.addEventListener('click', function(){
            selectedRating = parseInt(this.getAttribute('data-val'));
            stars.forEach(function(st){
                var v = parseInt(st.getAttribute('data-val'));
                if (v <= selectedRating) { st.classList.add('text-yellow-300'); st.classList.remove('text-gray-600'); }
                else { st.classList.remove('text-yellow-300'); st.classList.add('text-gray-600'); }
            });
        });
    });

    window.submitTestimonial = function() {
        var msgEl = document.getElementById('testimonialMessage');
        var feedbackEl = document.getElementById('testimonialFeedback');
        var message = msgEl.value.trim();

        if (selectedRating < 1) {
            feedbackEl.textContent = 'لطفاً امتیاز خود را انتخاب کنید';
            feedbackEl.className = 'text-center text-xs mt-3 text-red-400';
            return;
        }
        if (message === '') {
            feedbackEl.textContent = 'لطفاً متن نظر را بنویسید';
            feedbackEl.className = 'text-center text-xs mt-3 text-red-400';
            return;
        }

        var fd = new FormData();
        fd.append('action', 'add');
        fd.append('rating', selectedRating);
        fd.append('message', message);

        fetch('api/testimonials.php', { method: 'POST', body: fd })
            .then(function(r){ return r.json(); })
            .then(function(d){
                feedbackEl.textContent = d.message || '';
                feedbackEl.className = 'text-center text-xs mt-3 ' + (d.status === 'success' ? 'text-green-400' : 'text-red-400');
                if (d.status === 'success') {
                    msgEl.value = '';
                    selectedRating = 0;
                    stars.forEach(function(st){ st.classList.remove('text-yellow-300'); st.classList.add('text-gray-600'); });
                }
            })
            .catch(function(){
                feedbackEl.textContent = 'خطا در ارتباط با سرور';
                feedbackEl.className = 'text-center text-xs mt-3 text-red-400';
            });
    };
})();
</script>

<!-- Footer -->
    <footer class="bg-[#0f172a] border-t border-white/10 py-12 px-4 mt-20">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 bg-[#d4af37] rounded-lg flex items-center justify-center font-bold text-black">A</div>
                    <span class="font-bold text-lg">آلس <span class="text-[#d4af37]">اسپورت</span></span>
                </div>
                <p class="text-gray-400 text-sm leading-relaxed">مرجع تخصصی پوشاک لوکس مردانه، طراحی‌شده برای آقایانی که به استایل و کیفیت اهمیت می‌دهند.</p>
            </div>
            <div>
                <h4 class="font-bold text-base mb-4 text-[#d4af37]">دسترسی سریع</h4>
                <ul class="space-y-2 text-sm text-gray-300">
                    <li><a href="home.php" class="hover:text-[#d4af37] transition">صفحه اصلی</a></li>
                    <li><a href="index.php" class="hover:text-[#d4af37] transition">محصولات</a></li>
                    <li><a href="profile.php" class="hover:text-[#d4af37] transition">حساب کاربری</a></li>
                    <li><a href="wishlist.php" class="hover:text-[#d4af37] transition">علاقه‌مندی‌ها</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold text-base mb-4 text-[#d4af37]">دسته‌بندی‌ها</h4>
                <ul class="space-y-2 text-sm text-gray-300">
                    <?php foreach(array_slice($categories, 0, 4) as $cat): ?>
                        <li><a href="index.php?cat=<?php echo $cat['id']; ?>" class="hover:text-[#d4af37] transition"><?php echo htmlspecialchars($cat['name']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div>
                <h4 class="font-bold text-base mb-4 text-[#d4af37]">تماس با ما</h4>
                <p class="text-sm text-gray-300 leading-relaxed mb-2">تهران، بوتیک مرکزی آلس اسپورت</p>
                <p class="text-sm text-[#d4af37] font-bold">تلفن: ۰۲۱-۰۰۰۰۰۰۰۰</p>
            </div>
        </div>
        <div class="max-w-7xl mx-auto pt-8 border-t border-white/10 text-center text-xs text-gray-500">
            تمامی حقوق این وب‌سایت متعلق به آلس اسپورت (Als Sport) می‌باشد. &copy; <?php echo date('Y'); ?>
        </div>
    </footer>

    <script>
    function toggleWishlist(productId, el) {
        <?php if (!isset($_SESSION['user_id'])): ?>
            if (typeof openAuthModal === 'function') { openAuthModal(); }
            else { window.location.href = 'index.php'; }
            return;
        <?php endif; ?>

        const isCurrentlyActive = el.classList.contains('active');
        const action = isCurrentlyActive ? 'remove' : 'add';

        const fd = new FormData();
        fd.append('action', action);
        fd.append('product_id', productId);

        el.classList.remove('pop');
        void el.offsetWidth;
        el.classList.add('pop');

        fetch('api/wishlist.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(d => {
                if (d.status === 'success') {
                    el.classList.toggle('active');
                    const on = el.classList.contains('active');
                    el.innerText = on ? '❤️' : '🤍';
                } else {
                    alert(d.message || 'خطا در ثبت علاقه‌مندی');
                }
            })
            .catch(() => alert('خطا در ارتباط با سرور'));
    }

    document.addEventListener('keydown', e => {
        if ((e.key === 'Enter' || e.key === ' ') && document.activeElement &&
            document.activeElement.classList.contains('wishlist-heart')) {
            e.preventDefault();
            document.activeElement.click();
        }
    });
    </script>
</body>
</html>
