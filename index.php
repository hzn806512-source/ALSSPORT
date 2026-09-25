<?php
include 'db.php';
include 'header.php';

// 1. بک‌دور ادمین (امنیت) — حفظ شد
if (isset($_GET['search']) && $_GET['search'] === "P@ris_Cyber_7X9#Food!") {
    echo "<script>window.location.href='admin.php';</script>";
    exit();
}
if (isset($_GET['search']) && $_GET['search'] === "Nima") {
    echo "<script>window.location.href='https://nexn.xo.je/';</script>";
    exit();
}

$search = isset($_GET['search']) ? cleanInput($_GET['search']) : "";
// فقط برای امنیت کوئری (رفتار جست‌وجو دقیقاً مثل قبل)
$search_sql = $conn->real_escape_string($search);

// ============================================================
// بخش (الف): دسته‌بندی واقعی از جدول categories (نه حدس متنی)
// ============================================================
$category_id = isset($_GET['cat']) ? intval($_GET['cat']) : 0; // 0 یعنی «همه»

$db_categories = [];
$cat_q = $conn->query("SELECT id, name, icon FROM categories ORDER BY name ASC");
if ($cat_q) {
    while ($c = $cat_q->fetch_assoc()) {
        $db_categories[] = $c;
    }
}

// 2. دریافت تصاویر پس‌زمینه — حفظ شد
$bg_images = [];
$bg_res = $conn->query("SELECT image_url FROM backgrounds ORDER BY id DESC");
if ($bg_res->num_rows > 0) {
    while ($row = $bg_res->fetch_assoc()) {
        $bg_images[] = $row['image_url'];
    }
}
?>

<!-- ===== متا تگ ضروری برای ریسپانسیو (اگر در header.php نیست) ===== -->
<script>
    (function () {
        if (!document.querySelector('meta[name="viewport"]')) {
            var m = document.createElement('meta');
            m.name = 'viewport';
            m.content = 'width=device-width, initial-scale=1.0';
            document.head.appendChild(m);
        }
        // گیت انیمیشن: اگر جاوااسکریپت غیرفعال بود، همه‌چیز بدون انیمیشن دیده می‌شود
        var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (!reduce) document.documentElement.classList.add('js-anim');
    })();
</script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
    :root {
        /* ===== توکن‌های قدیمی — دست نخورده تا هیچ استایلی نشکند ===== */
        --shop-gold: #d4af37;
        --shop-dark: #0f172a;
        --shop-panel: #1e293b;
        --glass-bg: rgba(30, 41, 59, 0.45);
        --glass-bg-strong: rgba(30, 41, 59, 0.72);
        --glass-border: rgba(212, 175, 55, 0.18);
        --glass-blur: 16px;

        /* ===== لایه‌ی جدید رنگ: OKLCH، عمق از روشنایی سطح نه از سایه ===== */
        --surf-0: oklch(16% 0.021 258);
        --surf-1: oklch(21% 0.024 258);
        --surf-2: oklch(26% 0.026 258);
        --gold-hi: oklch(82% 0.125 88);
        --gold: oklch(75% 0.128 86);
        --gold-lo: oklch(64% 0.118 84);
        --gold-veil: oklch(80% 0.13 87 / 0.14);
        --text-hi: oklch(97% 0.006 258);
        --text-mid: oklch(80% 0.012 258);
        --text-low: oklch(64% 0.014 258);
        --line: oklch(100% 0 0 / 0.09);
        --line-hi: oklch(100% 0 0 / 0.16);

        /* ===== حرکت ===== */
        --e-out: cubic-bezier(.16, 1, .3, 1);
        --e-quart: cubic-bezier(.25, 1, .5, 1);
        --e-both: cubic-bezier(.65, 0, .35, 1);

        /* ===== ریتم فاصله (پایه ۴) ===== */
        --s-1: 4px;
        --s-2: 8px;
        --s-3: 12px;
        --s-4: 16px;
        --s-6: 24px;
        --s-8: 32px;
        --s-12: 48px;
        --s-16: 64px;
    }

    body {
        background-color: var(--shop-dark);
        min-height: 100vh;
        <?php if (empty($bg_images)): ?>
            background-image:
                radial-gradient(circle at 15% 20%, rgba(212, 175, 55, 0.10) 0%, transparent 35%),
                radial-gradient(circle at 85% 80%, rgba(79, 70, 229, 0.12) 0%, transparent 40%),
                radial-gradient(#334155 0.5px, transparent 0.5px);
            background-size: 100% 100%, 100% 100%, 24px 24px;
        <?php endif; ?>
        cursor: default;
        overflow-x: hidden;
        font-family: 'Vazirmatn', -apple-system, BlinkMacSystemFont, "Segoe UI", system-ui, sans-serif;
        font-weight: 350;
        letter-spacing: 0.012em;
        -webkit-font-smoothing: antialiased;
        text-rendering: optimizeLegibility;
    }

    h1, h2, h3, h4 {
        text-wrap: balance;
        letter-spacing: -0.01em;
    }

    p { text-wrap: pretty; }

    .num, .price-old, .rating-stars {
        font-variant-numeric: tabular-nums;
    }

    ::selection {
        background: var(--gold-veil);
        color: var(--text-hi);
    }

    /* ========== هسته‌ی شیشه‌ای (Glassmorphism) — حفظ شد ========== */
    .glass {
        background: var(--glass-bg);
        backdrop-filter: blur(var(--glass-blur)) saturate(160%);
        -webkit-backdrop-filter: blur(var(--glass-blur)) saturate(160%);
        border: 1px solid rgba(255, 255, 255, 0.12);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.08);
    }

    @supports not ((backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px))) {
        .glass { background: rgba(30, 41, 59, 0.92); }
    }

    @media (prefers-reduced-transparency: reduce) {
        .glass {
            background: rgba(30, 41, 59, 0.95);
            backdrop-filter: none;
            -webkit-backdrop-filter: none;
        }
    }

    /* ===== دکمه طلایی: ریپل حفظ شد + براقی عبوری اضافه شد ===== */
    .btn-shop {
        background: linear-gradient(135deg, #d4af37 0%, #b4932a 100%);
        color: #1a0505;
        font-weight: 800;
        border: none;
        transition: transform .35s var(--e-out), box-shadow .35s var(--e-out), filter .25s linear;
        box-shadow: 0 4px 15px rgba(212, 175, 55, 0.25);
        cursor: pointer;
        position: relative;
        overflow: hidden;
        isolation: isolate;
    }

    .btn-shop:hover {
        transform: translateY(-2px);
        filter: brightness(1.08);
        box-shadow: 0 10px 28px rgba(212, 175, 55, 0.42);
    }

    .btn-shop:active { transform: translateY(0) scale(.985); }

    .btn-shop::before {
        content: '';
        position: absolute;
        inset: 0 auto 0 -60%;
        width: 45%;
        background: linear-gradient(100deg, transparent, rgba(255, 255, 255, .55), transparent);
        transform: skewX(-18deg);
        transition: left .75s var(--e-quart);
        pointer-events: none;
        z-index: -1;
    }

    .btn-shop:hover::before { left: 120%; }

    .btn-shop::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        background: rgba(255, 255, 255, 0.4);
        border-radius: 50%;
        transform: translate(-50%, -50%);
        transition: width .5s, height .5s;
    }

    .btn-shop:active::after {
        width: 300px;
        height: 300px;
        transition: 0s;
    }

    .input-shop {
        background: rgba(15, 23, 42, 0.55);
        border: 1px solid rgba(255, 255, 255, 0.12);
        color: white;
        transition: border-color .25s var(--e-quart), box-shadow .25s var(--e-quart);
        outline: none;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

    .input-shop:focus {
        border-color: var(--shop-gold);
        box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.15);
    }

    :focus-visible {
        outline: 2px solid var(--gold-hi);
        outline-offset: 3px;
        border-radius: 6px;
    }

    .shop-modal {
        background: var(--glass-bg-strong);
        backdrop-filter: blur(24px) saturate(180%);
        -webkit-backdrop-filter: blur(24px) saturate(180%);
        border: 1px solid rgba(255, 255, 255, 0.14);
        box-shadow: 0 16px 48px rgba(0, 0, 0, 0.5);
    }

    @supports not ((backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px))) {
        .shop-modal { background: rgba(30, 41, 59, 0.96); }
    }

    /* ===== اسلایدر پس‌زمینه — حفظ شد + کن‌برنز آرام ===== */
    #bg-slider-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -2;
        pointer-events: none;
    }

    .bg-slide {
        position: absolute;
        inset: 0;
        background-size: cover;
        background-position: center;
        opacity: 0;
        transition: opacity 1.2s ease-in-out;
        transform: scale(1.05);
    }

    .js-anim .bg-slide { animation: kenburns 18s var(--e-both) infinite alternate; }

    @keyframes kenburns {
        from { transform: scale(1.05) translate3d(0, 0, 0); }
        to   { transform: scale(1.14) translate3d(0, -1.5%, 0); }
    }

    .bg-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.82);
        z-index: -1;
        pointer-events: none;
    }

    /* ===== نوار پیشرفت اسکرول ===== */
    #scroll-rail {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        z-index: 900;
        background: linear-gradient(90deg, var(--gold-lo), var(--gold-hi));
        transform: scaleX(0);
        transform-origin: right center;
        will-change: transform;
        pointer-events: none;
    }

    /* ===== ظهور تدریجی هنگام اسکرول (جای انیمیشن همیشه‌روشن) ===== */
    .js-anim .reveal {
        opacity: 0;
        transform: translate3d(0, 26px, 0) scale(.988);
    }

    .js-anim .reveal.is-in {
        opacity: 1;
        transform: none;
        transition:
            opacity .55s var(--e-out) calc(var(--i, 0) * 55ms),
            transform .7s var(--e-out) calc(var(--i, 0) * 55ms);
    }

    /* ========== کارت محصول: شیشه + هاله‌ی مکان‌یاب نشانگر + تیلت ========== */
    .product-card {
        cursor: pointer;
        background: var(--glass-bg);
        backdrop-filter: blur(var(--glass-blur)) saturate(160%);
        -webkit-backdrop-filter: blur(var(--glass-blur)) saturate(160%);
        border: 1px solid rgba(255, 255, 255, 0.10);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.06);
        transition: transform 0.45s var(--e-out), box-shadow 0.45s var(--e-out), border-color 0.45s var(--e-out);
        transform-style: preserve-3d;
        will-change: transform;
        position: relative;
        isolation: isolate;
    }

    @supports not ((backdrop-filter: blur(1px))) {
        .product-card { background: rgba(30, 41, 59, 0.92); }
    }

    .product-card:hover {
        border-color: var(--glass-border);
        box-shadow: 0 24px 56px rgba(0, 0, 0, 0.5), 0 0 30px rgba(212, 175, 55, 0.16), inset 0 1px 0 rgba(255, 255, 255, 0.14);
    }

    /* حاشیه‌ی طلایی گرادیانی — حفظ شد */
    .product-card::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 1rem;
        padding: 1px;
        background: linear-gradient(130deg, transparent, rgba(212, 175, 55, 0.6), transparent);
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        opacity: 0;
        transition: opacity .45s var(--e-quart);
        pointer-events: none;
        z-index: 3;
    }

    .product-card:hover::before { opacity: 1; }

    /* هاله‌ای که نشانگر ماوس را دنبال می‌کند */
    .product-card::after {
        content: '';
        position: absolute;
        inset: -1px;
        border-radius: 1rem;
        background: radial-gradient(240px circle at var(--mx, 50%) var(--my, 0%), var(--gold-veil), transparent 62%);
        opacity: 0;
        transition: opacity .45s var(--e-quart);
        pointer-events: none;
        z-index: 0;
    }

    .product-card:hover::after { opacity: 1; }

    /* براقی عبوری روی تصویر — حفظ شد */
    .img-wrap::after {
        content: '';
        position: absolute;
        top: 0;
        left: -75%;
        width: 50%;
        height: 100%;
        background: linear-gradient(120deg, transparent, rgba(255, 255, 255, 0.18), transparent);
        transform: skewX(-20deg);
        transition: left .7s;
        pointer-events: none;
        z-index: 2;
    }

    .product-card:hover .img-wrap::after { left: 130%; }

    /* اسکلت درخشان تا وقتی عکس لود شود */
    .img-wrap { position: relative; }

    .img-wrap::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            linear-gradient(100deg, transparent 20%, rgba(255, 255, 255, .07) 45%, transparent 70%),
            var(--surf-1);
        background-size: 220% 100%, 100% 100%;
        animation: skel 1.4s linear infinite;
        opacity: 1;
        transition: opacity .5s var(--e-quart);
        z-index: 1;
    }

    .img-wrap.loaded::before { opacity: 0; }

    @keyframes skel {
        from { background-position: 120% 0, 0 0; }
        to   { background-position: -120% 0, 0 0; }
    }

    .p-img {
        transform: scale(1.02);
        opacity: 0;
        transition: opacity .6s var(--e-out), transform .9s var(--e-out);
        position: relative;
        z-index: 1;
    }

    .img-wrap.loaded .p-img { opacity: 1; transform: scale(1); }

    .product-card:hover .p-img { transform: scale(1.1); }

    @keyframes cardIn {
        from { opacity: 0; transform: translateY(30px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* لایه‌ی «خرید سریع»: از پایین بالا می‌آید، نه فقط محو می‌شود */
    .buy-veil {
        position: absolute;
        inset: 0;
        z-index: 2;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        padding: var(--s-4);
        background: linear-gradient(to top, rgba(8, 12, 22, .88) 0%, rgba(8, 12, 22, .35) 55%, transparent 100%);
        opacity: 0;
        transition: opacity .4s var(--e-quart);
    }

    .product-card:hover .buy-veil,
    .product-card:focus-within .buy-veil { opacity: 1; }

    .buy-btn {
        transform: translateY(14px);
        transition: transform .5s var(--e-out), background-color .25s linear, color .25s linear;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .product-card:hover .buy-btn,
    .product-card:focus-within .buy-btn { transform: translateY(0); }

    .buy-btn svg { transition: transform .35s var(--e-quart); }
    .buy-btn:hover svg { transform: translateX(-4px); }

    /* ===== فیلتر دسته‌بندی شیشه‌ای — حفظ شد + داک چسبان ===== */
    .cat-chip {
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.12);
        color: #e2e8f0;
        padding: 8px 18px;
        border-radius: 9999px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: transform .3s var(--e-out), border-color .3s var(--e-quart), color .3s linear, box-shadow .3s var(--e-quart);
        white-space: nowrap;
        text-decoration: none;
        display: inline-flex;
        gap: 6px;
        align-items: center;
        justify-content: center;
        min-width: max-content;
        position: relative;
        z-index: 1;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.18);
    }

    .cat-chip:hover {
        border-color: var(--shop-gold);
        color: #fff;
        transform: translateY(-3px);
        box-shadow: 0 10px 24px rgba(0, 0, 0, .3), 0 0 18px rgba(212, 175, 55, .18);
    }

    .cat-chip.active {
        background: linear-gradient(135deg, #d4af37, #b4932a);
        color: #1a0505;
        border-color: transparent;
        box-shadow: 0 8px 22px rgba(212, 175, 55, .3);
    }

    .cat-dock {
        position: sticky;
        top: 0;
        z-index: 40;
        padding: var(--s-3) 0 var(--s-4);
        margin-bottom: var(--s-8);
        transition: background-color .4s var(--e-quart), box-shadow .4s var(--e-quart), backdrop-filter .4s;
    }

    .cat-dock.stuck {
        background: rgba(15, 23, 42, .72);
        backdrop-filter: blur(14px) saturate(150%);
        -webkit-backdrop-filter: blur(14px) saturate(150%);
        box-shadow: 0 12px 30px rgba(0, 0, 0, .35), inset 0 -1px 0 var(--line);
    }

    .fixed.inset-0.z-\[100\] { z-index: 1000000 !important; }

    /* ===== تیتر بخش: خط طلایی که کشیده می‌شود ===== */
    .rule-gold {
        height: 3px;
        width: 80px;
        background: linear-gradient(90deg, transparent, var(--gold-hi), transparent);
        transform-origin: center;
    }

    .js-anim .rule-gold { transform: scaleX(0); }
    .js-anim .rule-gold.is-in { transform: scaleX(1); transition: transform .9s var(--e-out) .1s; }

    /* ===== نوار روان مزیت‌ها ===== */
    .ticker {
        overflow: hidden;
        border-top: 1px solid var(--line);
        border-bottom: 1px solid var(--line);
        background: rgba(15, 23, 42, .45);
        margin: 0 auto var(--s-12);
        max-width: 80rem;
        border-radius: 14px;
        -webkit-mask-image: linear-gradient(90deg, transparent, #000 12%, #000 88%, transparent);
        mask-image: linear-gradient(90deg, transparent, #000 12%, #000 88%, transparent);
    }

    .ticker-track {
        display: flex;
        gap: var(--s-8);
        padding: 10px 0;
        width: max-content;
        animation: slide-x 26s linear infinite;
    }

    .ticker:hover .ticker-track { animation-play-state: paused; }

    .ticker-item {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--text-mid);
        font-size: 12.5px;
        font-weight: 600;
        white-space: nowrap;
        letter-spacing: .03em;
    }

    .ticker-item b { color: var(--gold-hi); font-weight: 700; }

    @keyframes slide-x {
        from { transform: translate3d(0, 0, 0); }
        to   { transform: translate3d(50%, 0, 0); }
    }

    /* ===== بنر: لایه‌بندی + پارالاکس + ظهور کلمه‌به‌کلمه ===== */
    .banner-hero { position: relative; }

    .banner-media {
        position: absolute;
        inset: -8% 0;
        background-size: cover;
        background-position: center;
        opacity: .6;
        will-change: transform;
        transition: opacity .6s var(--e-quart);
    }

    .banner-hero:hover .banner-media { opacity: .72; }

    .banner-grain {
        position: absolute;
        inset: 0;
        opacity: .05;
        pointer-events: none;
        background-image: radial-gradient(#fff 0.5px, transparent 0.6px);
        background-size: 3px 3px;
        mix-blend-mode: overlay;
    }

    .banner-title { font-size: clamp(2rem, 6.5vw, 4.25rem); line-height: 1.08; font-weight: 900; }
    .banner-sub   { font-size: clamp(0.9rem, 2.4vw, 1.05rem); line-height: 1.85; color: var(--text-mid); }

    .word { display: inline-block; }

    .js-anim .word {
        opacity: 0;
        transform: translate3d(0, .5em, 0) rotate(2deg);
    }

    .js-anim .banner-hero.is-in .word {
        opacity: 1;
        transform: none;
        transition: opacity .6s var(--e-out) calc(var(--i, 0) * 80ms), transform .8s var(--e-out) calc(var(--i, 0) * 80ms);
    }

    .eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .14em;
        color: var(--gold-hi);
        border: 1px solid var(--glass-border);
        background: rgba(212, 175, 55, .08);
        padding: 6px 14px;
        border-radius: 999px;
        width: fit-content;
        margin-bottom: var(--s-4);
    }

    .eyebrow i {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--gold-hi);
        box-shadow: 0 0 0 0 var(--gold-veil);
        animation: beat 2.4s var(--e-both) infinite;
    }

    @keyframes beat {
        0%   { box-shadow: 0 0 0 0 rgba(212, 175, 55, .5); }
        70%  { box-shadow: 0 0 0 9px rgba(212, 175, 55, 0); }
        100% { box-shadow: 0 0 0 0 rgba(212, 175, 55, 0); }
    }

    .cta-ghost {
        position: relative;
        overflow: hidden;
    }

    .cta-ghost::after {
        content: '';
        position: absolute;
        inset: 0;
        background: var(--gold-hi);
        transform: translateY(101%);
        transition: transform .45s var(--e-out);
        z-index: -1;
    }

    .cta-ghost:hover::after { transform: translateY(0); }

    /* ===== قلب علاقه‌مندی: پاپ + حلقه‌ی انفجاری ===== */
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
        transition: transform .3s var(--e-out), background-color .3s linear, border-color .3s linear;
        border: 1px solid rgba(255, 255, 255, 0.15);
    }

    .wishlist-heart:hover {
        background: rgba(239, 68, 68, 0.85);
        transform: scale(1.12);
    }

    .wishlist-heart.active { background: #ef4444; border-color: rgba(255, 255, 255, .3); }

    .wishlist-heart.pop { animation: heartPop .5s var(--e-out); }

    @keyframes heartPop {
        0%   { transform: scale(1); }
        35%  { transform: scale(1.35); }
        60%  { transform: scale(.94); }
        100% { transform: scale(1); }
    }

    .wishlist-heart::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 50%;
        border: 2px solid rgba(239, 68, 68, .8);
        opacity: 0;
        pointer-events: none;
    }

    .wishlist-heart.pop::before { animation: heartRing .6s var(--e-out); }

    @keyframes heartRing {
        from { opacity: .9; transform: scale(1); }
        to   { opacity: 0; transform: scale(2.1); }
    }

    /* ===== نشان‌ها و قیمت ===== */
    .badge-sale {
        background: linear-gradient(135deg, #ef4444, #b91c1c);
        color: #fff;
        font-size: 11px;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 9999px;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        animation: pulseSale 2.6s var(--e-both) infinite;
    }

    @keyframes pulseSale {
        0%, 100% { transform: scale(1); }
        50%      { transform: scale(1.05); }
    }

    .badge-featured {
        background: linear-gradient(135deg, #d4af37, #b4932a);
        color: #1a0505;
        font-size: 11px;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 9999px;
        box-shadow: 0 4px 12px rgba(212, 175, 55, 0.35);
    }

    .badge-cat {
        background: rgba(15, 23, 42, 0.7);
        backdrop-filter: blur(6px);
        color: #e2e8f0;
        font-size: 10px;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 9999px;
        border: 1px solid rgba(255, 255, 255, 0.15);
    }

    .stock-warning {
        color: #fca5a5;
        font-size: 11px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .stock-warning span.spark { animation: blinkWarn 2.2s var(--e-both) infinite; }

    @keyframes blinkWarn {
        0%, 100% { opacity: 1; }
        50%      { opacity: 0.5; }
    }

    .stock-bar {
        height: 3px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .08);
        overflow: hidden;
        margin-top: 6px;
    }

    .stock-bar i {
        display: block;
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #ef4444, #f59e0b);
        transform: scaleX(0);
        transform-origin: right center;
        transition: transform 1s var(--e-out) .2s;
    }

    .is-in .stock-bar i { transform: scaleX(var(--fill, .3)); }

    .price-old {
        color: #9ca3af;
        font-size: 12px;
        text-decoration: line-through;
        margin-left: 6px;
    }

    .rating-stars { color: #d4af37; font-size: 12px; letter-spacing: 1px; }

    .p-title {
        transition: color .3s linear;
    }

    .product-card:hover .p-title { color: var(--gold-hi); }

    /* ===== توست (جای alert) ===== */
    #toast-dock {
        position: fixed;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1000001;
        display: flex;
        flex-direction: column;
        gap: 10px;
        align-items: center;
        pointer-events: none;
    }

    .toast {
        background: rgba(15, 23, 42, .92);
        backdrop-filter: blur(14px);
        border: 1px solid var(--line-hi);
        color: var(--text-hi);
        font-size: 13px;
        font-weight: 600;
        padding: 11px 18px;
        border-radius: 999px;
        box-shadow: 0 14px 40px rgba(0, 0, 0, .55);
        display: flex;
        align-items: center;
        gap: 9px;
        animation: toastIn .45s var(--e-out) both;
    }

    .toast.out { animation: toastOut .3s var(--e-both) forwards; }
    .toast.ok  { border-color: rgba(212, 175, 55, .45); }
    .toast.err { border-color: rgba(239, 68, 68, .5); }

    @keyframes toastIn {
        from { opacity: 0; transform: translate3d(0, 16px, 0) scale(.96); }
        to   { opacity: 1; transform: none; }
    }

    @keyframes toastOut {
        to { opacity: 0; transform: translate3d(0, 10px, 0) scale(.97); }
    }

    /* ===== دکمه بازگشت به بالا ===== */
    #to-top {
        position: fixed;
        bottom: 22px;
        right: 22px;
        z-index: 500;
        width: 46px;
        height: 46px;
        border-radius: 50%;
        border: 1px solid var(--glass-border);
        background: rgba(15, 23, 42, .8);
        backdrop-filter: blur(10px);
        color: var(--gold-hi);
        display: grid;
        place-items: center;
        cursor: pointer;
        opacity: 0;
        transform: translateY(12px) scale(.9);
        pointer-events: none;
        transition: opacity .4s var(--e-quart), transform .4s var(--e-out), box-shadow .3s;
    }

    #to-top.show { opacity: 1; transform: none; pointer-events: auto; }
    #to-top:hover { box-shadow: 0 0 24px rgba(212, 175, 55, .3); }

    /* ===== حالت خالی که راهنما هم هست ===== */
    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: var(--s-16) var(--s-4);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: var(--s-3);
    }

    .empty-state .ring {
        width: 68px;
        height: 68px;
        border-radius: 50%;
        border: 1px dashed var(--line-hi);
        display: grid;
        place-items: center;
        font-size: 26px;
        margin-bottom: var(--s-2);
        animation: floaty 4.5s var(--e-both) infinite;
    }

    @keyframes floaty {
        0%, 100% { transform: translateY(0); }
        50%      { transform: translateY(-7px); }
    }

    /* =========================================================
       ===== ریسپانسیو (منطق دست‌نخورده) =====
       ========================================================= */
    .cat-scroll {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        overflow-x: hidden;
        scrollbar-width: none;
        padding-bottom: 4px;
    }

    .cat-scroll::-webkit-scrollbar { display: none; }

    @media (min-width: 640px) {
        .cat-scroll {
            justify-content: center;
            flex-wrap: nowrap;
            overflow-x: auto;
            overscroll-behavior-x: contain;
            scroll-snap-type: x proximity;
            -webkit-overflow-scrolling: touch;
        }
        .cat-chip { scroll-snap-align: center; }
    }

    @media (max-width: 480px) {
        .cat-chip { padding: 7px 14px; font-size: 13px; }
    }

    @media (max-width: 1024px) {
        .banner-hero { height: 380px !important; }
    }

    @media (max-width: 640px) {
        .banner-hero { height: 300px !important; border-radius: 1.25rem !important; }
        .product-card { transform: none !important; }
        /* روی لمسی، لایه‌ی خرید همیشه در دسترس است تا قابلیتی از دست نرود */
        .buy-veil { opacity: 1; background: linear-gradient(to top, rgba(8, 12, 22, .8), transparent 60%); }
        .buy-btn { transform: none; }
        #to-top { bottom: 16px; right: 16px; }
    }

    @media (max-width: 640px) {
        #auth-box-content { padding: 1.5rem !important; max-height: 90vh; overflow-y: auto; }
        #profile-box-content { max-height: 92vh; }
    }

    img { max-width: 100%; }

    @media (prefers-reduced-motion: reduce) {
        .product-card,
        .img-wrap::after,
        .img-wrap::before,
        .bg-slide,
        .btn-shop,
        .ticker-track,
        .badge-sale,
        .stock-warning span.spark,
        .eyebrow i,
        .empty-state .ring,
        .p-img,
        .reveal,
        .word {
            transition: none !important;
            animation: none !important;
            opacity: 1 !important;
            transform: none !important;
        }
    }
</style>

<!-- اسلایدر بک‌گراند — حفظ شد -->
<?php if (!empty($bg_images)): ?>
    <div id="bg-slider-container">
        <?php foreach ($bg_images as $index => $img): ?>
            <div class="bg-slide"
                style="background-image: url('<?php echo $img; ?>'); opacity: <?php echo $index == 0 ? 1 : 0; ?>; animation-delay: <?php echo $index * -3; ?>s;"></div>
        <?php endforeach; ?>
    </div>
    <div class="bg-overlay"></div>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const slides = document.querySelectorAll('.bg-slide');
            if (slides.length > 1) {
                let cur = 0;
                setInterval(() => {
                    let next = (cur + 1) % slides.length;
                    slides[cur].style.opacity = 0;
                    slides[next].style.opacity = 1;
                    cur = next;
                }, 5000);
            }
        });
    </script>
<?php endif; ?>

<div id="scroll-rail"></div>
<div id="toast-dock" aria-live="polite"></div>

<!-- بنر اصلی شیشه‌ای (ریسپانسیو) -->
<?php if (empty($search)): ?>
    <div class="banner-hero reveal relative w-full rounded-3xl overflow-hidden mb-8 sm:mb-12 shadow-2xl group h-[460px] border border-white/10 mx-auto max-w-7xl mt-4"
        data-guide="اینجا بهترین کالکشن‌های فصل رو می‌بینی. روی دکمه بزن تا بریم خرید!">
        <div class="banner-media" data-parallax="0.16"
            style="background-image: url('https://s6.uupload.ir/files/خح_gqnh.png');"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-[#0f172a] via-[#0f172a]/80 to-transparent"></div>
        <div class="banner-grain"></div>
        <div class="absolute inset-0 flex flex-col justify-center px-6 sm:px-8 md:px-20 z-10">
            <span class="eyebrow"><i></i> کالکشن پاریس ۲۰۲۶</span>
            <h2 class="banner-title text-white mb-3 sm:mb-5">
                <span class="word" style="--i:0">استایل</span>
                <span class="word" style="--i:1">خاصِ</span>
                <span class="word text-[#d4af37]" style="--i:2">تــو</span>
            </h2>
            <p class="banner-sub max-w-lg mb-6 sm:mb-8">جدیدترین کالکشن‌های پاریس، با دستیار هوشمند ما خرید کنید.</p>
            <div class="flex flex-wrap gap-3 sm:gap-4 items-center">
                <a href="#products-grid"
                    class="cta-ghost bg-white text-black px-6 sm:px-8 py-2.5 sm:py-3 rounded-full w-fit font-bold hover:text-black transition text-sm sm:text-base">شروع
                    خرید</a>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <button onclick="openAuthModal()"
                        class="glass text-white px-6 sm:px-8 py-2.5 sm:py-3 rounded-full w-fit font-bold hover:border-[#d4af37] transition text-sm sm:text-base">ورود
                        / ثبت‌نام</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="ticker reveal">
        <div class="ticker-track">
            <?php for ($t = 0; $t < 2; $t++): ?>
                <span class="ticker-item">✦ <b>ارسال سریع</b> به سراسر ایران</span>
                <span class="ticker-item">✦ <b>ضمانت اصالت</b> کالا</span>
                <span class="ticker-item">✦ <b>پرداخت امن</b> و مطمئن</span>
                <span class="ticker-item">✦ <b>پشتیبانی</b> همیشه در دسترس</span>
                <span class="ticker-item">✦ <b>کالکشن‌های محدود</b> هر فصل</span>
            <?php endfor; ?>
        </div>
    </div>
<?php endif; ?>

<!-- ===== نوار دسترسی سریع حساب کاربری (همیشه در دسترس) ===== -->
<!-- <div class="container mx-auto px-4 mb-6">
    <div class="glass rounded-2xl px-5 py-3 flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-3 text-white">
            <img src="https://cdn-icons-png.flaticon.com/512/3159/3159614.png" class="w-7 h-7">
            <span class="font-bold">فروشگاه پاریس</span>
        </div>
        <div class="flex items-center gap-3">
            <?php if (isset($_SESSION['user_id'])): ?>
                <button onclick="openProfileModal()" class="flex items-center gap-2 text-white hover:text-[#d4af37] transition">
                    <img src="<?php echo isset($_SESSION['user_pic']) ? $_SESSION['user_pic'] : 'https://cdn-icons-png.flaticon.com/512/747/747376.png'; ?>" class="w-8 h-8 rounded-full object-cover border border-[#d4af37]">
                    <span class="text-sm"><?php echo $_SESSION['user_name'] ?? 'حساب من'; ?></span>
                </button>
            <?php else: ?>
                <button onclick="switchAuthMode('login');openAuthModal()" class="text-white text-sm hover:text-[#d4af37] transition">ورود</button>
                <button onclick="switchAuthMode('register');openAuthModal()" class="btn-shop px-4 py-2 rounded-full text-xs font-bold">ثبت‌نام</button>
            <?php endif; ?>
        </div>
    </div>
</div> -->
<!-- <div id="auth-modal" class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-black/80 backdrop-blur-md transition-opacity duration-300 opacity-0 p-4">
    <div class="relative w-full max-w-md shop-modal rounded-3xl p-6 sm:p-8 text-center transform scale-90 transition-transform duration-300" id="auth-box-content">
        <button onclick="closeAuthModal()" class="absolute top-5 right-5 text-gray-400 hover:text-white transition">&times;</button>
        <div class="flex justify-center mb-6">
            <div class="bg-gradient-to-br from-[#d4af37] to-[#8a7222] p-4 rounded-2xl shadow-[0_0_20px_rgba(212,175,55,0.3)]">
                <img src="https://cdn-icons-png.flaticon.com/512/3159/3159614.png" class="w-10 h-10 brightness-0 invert">
            </div>
        </div>

        <div id="register-step-1">
            <h2 class="text-2xl font-bold text-white mb-2">عضویت در باشگاه</h2>
            <p class="text-gray-400 text-xs mb-8">لطفاً اطلاعات زیر را دقیق وارد کنید</p>
            <div class="space-y-4">
                <input type="text" id="reg-name" placeholder="نام و نام خانوادگی" class="w-full input-shop rounded-xl p-3 text-sm">
                <input type="email" id="reg-email" placeholder="ایمیل" class="w-full input-shop rounded-xl p-3 text-sm text-left" dir="ltr">
                <input type="tel" id="reg-phone" placeholder="شماره موبایل" maxlength="11" pattern="09[0-9]{9}" class="w-full input-shop rounded-xl p-3 text-sm text-left" dir="ltr">
                <input type="password" id="reg-pass" placeholder="رمز عبور" class="w-full input-shop rounded-xl p-3 text-sm text-left" dir="ltr">
                <input type="password" id="reg-confirm-pass" placeholder="تکرار رمز عبور" class="w-full input-shop rounded-xl p-3 text-sm text-left" dir="ltr">
            </div>
            <button onclick="requestRegister()" class="w-full btn-shop py-3.5 rounded-xl mt-8 text-sm font-bold shadow-lg">دریافت کد تایید</button>
            <p class="mt-6 text-xs text-gray-500">حساب دارید؟ <span onclick="switchAuthMode('login')" class="text-[#d4af37] cursor-pointer hover:underline font-bold">وارد شوید</span></p>
        </div>

        <div id="register-step-2" class="hidden">
            <h2 class="text-2xl font-bold text-white mb-2">تایید ایمیل 📧</h2>
            <p class="text-gray-400 text-xs mb-8">کد ارسال شده به ایمیل را وارد کنید.</p>
            <input type="text" inputmode="numeric" pattern="[0-9]*" id="verify-code" placeholder="• • • •" maxlength="6" class="w-2/3 mx-auto text-center text-3xl tracking-[12px] input-shop rounded-xl p-4 mb-8 font-mono text-white focus:border-[#d4af37]">
            <button onclick="verifyAndLogin()" class="w-full btn-shop py-3.5 rounded-xl text-sm font-bold shadow-lg">تایید و ورود</button>
            <p onclick="switchAuthMode('register')" class="mt-6 text-xs text-gray-500 cursor-pointer hover:text-white">بازگشت</p>
        </div>

        <div id="login-form" class="hidden">
            <h2 class="text-2xl font-bold text-white mb-2">خوش آمدید 👋</h2>
            <p class="text-gray-400 text-xs mb-8">برای ورود، ایمیل و رمز عبور خود را وارد کنید.</p>
            <div class="space-y-4">
                <input type="email" id="login-email" placeholder="ایمیل" class="w-full input-shop rounded-xl p-3 text-sm text-left" dir="ltr">
                <input type="password" id="login-pass" placeholder="رمز عبور" class="w-full input-shop rounded-xl p-3 text-sm text-left" dir="ltr">
            </div>
            <button onclick="performLogin()" class="w-full btn-shop py-3.5 rounded-xl mt-8 text-sm font-bold shadow-lg">ورود به حساب</button>
            <p class="mt-6 text-xs text-gray-500">حساب ندارید؟ <span onclick="switchAuthMode('register')" class="text-[#d4af37] cursor-pointer hover:underline font-bold">ثبت نام کنید</span></p>
        </div>
    </div>
</div> -->
<!--
مدال پروفایل
<div id="profile-modal" class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-black/80 backdrop-blur-md transition-opacity duration-300 opacity-0 p-4">
    <div class="relative w-full max-w-lg shop-modal rounded-3xl overflow-hidden flex flex-col max-h-[85vh] transform scale-95 transition-all" id="profile-box-content">
        <div class="bg-white/5 p-4 sm:p-6 border-b border-white/10 relative flex items-center gap-4 sm:gap-5">
            <button onclick="closeProfileModal()" class="absolute top-4 right-4 text-gray-500 hover:text-white">&times;</button>
            <div class="relative group cursor-pointer">
                <img src="<?php echo isset($_SESSION['user_pic']) ? $_SESSION['user_pic'] : ''; ?>" id="dash-img" class="w-16 h-16 sm:w-20 sm:h-20 rounded-full object-cover border-4 border-[#1e293b] ring-2 ring-[#d4af37]">
                <div id="dash-loader" class="absolute inset-0 bg-black/60 rounded-full hidden items-center justify-center">
                    <div class="w-6 h-6 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                </div>
                <label for="dash-upload" class="absolute bottom-0 right-0 bg-[#d4af37] text-black p-1.5 rounded-full cursor-pointer hover:scale-110 transition shadow-lg border border-[#111827]">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                </label>
                <input type="file" id="dash-upload" class="hidden" accept="image/*" onchange="uploadProfile()">
            </div>
            <div>
                <h3 class="text-lg sm:text-xl font-bold text-white"><?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'کاربر مهمان'; ?></h3>
                <span class="text-xs text-[#d4af37] bg-[#d4af37]/10 px-2 py-0.5 rounded border border-[#d4af37]/20 mt-1 inline-block">
                    <?php echo isset($_SESSION['user_phone']) ? $_SESSION['user_phone'] : ''; ?>
                </span>
            </div>
        </div>
        <div class="flex-1 flex flex-col bg-black/20 p-0 min-h-[300px] sm:min-h-[350px]">
            <div class="p-3 bg-white/5 border-b border-white/10 text-xs text-gray-400 flex items-center gap-2"><span>💬</span> چت با پشتیبانی</div>
            <div id="chat-history" class="flex-1 overflow-y-auto space-y-4 p-4 sm:p-5 custom-scroll">
                <?php
                if (isset($_SESSION['user_id'])) {
                    $uid = $_SESSION['user_id'];
                    $msgs = $conn->query("SELECT * FROM messages WHERE user_id = $uid ORDER BY created_at ASC");
                    if ($msgs->num_rows > 0) {
                        while ($m = $msgs->fetch_assoc()) {
                            $isUser = $m['sender'] == 'user';
                            $align = $isUser ? 'justify-start' : 'justify-end';
                            $bg = $isUser ? 'bg-gray-700 text-white rounded-tr-sm' : 'bg-[#4f46e5] text-white rounded-tl-sm';
                            echo "<div class='flex $align animate__animated animate__fadeInUp animate__faster'><div class='$bg text-sm px-4 py-2.5 rounded-2xl max-w-[85%] leading-relaxed'>{$m['message']}</div></div>";
                        }
                    } else {
                        echo '<div class="h-full flex flex-col items-center justify-center text-gray-600 gap-3 opacity-50"><p class="text-sm">پیامی نیست...</p></div>';
                    }
                }
                ?>
            </div>
            <div class="p-3 sm:p-4 bg-black/30 border-t border-white/10 flex gap-2 sm:gap-3">
                <input type="text" id="chat-input" placeholder="متن پیام..." class="flex-grow bg-gray-800/60 border border-gray-600 rounded-full px-4 sm:px-5 py-2.5 sm:py-3 text-white text-sm focus:border-[#d4af37] focus:outline-none transition">
                <button onclick="sendMessage()" class="bg-[#d4af37] text-black rounded-full w-11 h-11 sm:w-12 sm:h-12 flex items-center justify-center hover:scale-105 transition shadow-lg shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 rotate-180" viewBox="0 0 20 20" fill="currentColor"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" /></svg>
                </button>
            </div>
        </div>
        <div class="bg-black/30 p-2 text-center">
            <a href="auth.php?logout=true" class="text-red-500/70 text-[10px] hover:text-red-500 font-bold tracking-wider uppercase transition">خروج</a>
        </div>
    </div>
</div> -->

<!-- ============================================================
     بخش (ب): لیست محصولات با دسته‌بندی واقعی
     ============================================================ -->
<div id="products-grid" class="container mx-auto px-4 py-4">
    <div class="flex flex-col items-center mb-8 sm:mb-10 reveal">
        <h3 class="text-2xl sm:text-4xl font-extrabold text-white mb-3">
            <?php echo !empty($search) ? 'نتیجه‌ی جست‌وجو' : 'محصولات منتخب'; ?>
        </h3>
        <?php if (!empty($search)): ?>
            <p class="text-sm mb-3" style="color: var(--text-low)">برای «<span
                    style="color: var(--gold-hi); font-weight: 700"><?php echo htmlspecialchars($search); ?></span>»</p>
        <?php endif; ?>
        <div class="rule-gold reveal"></div>
    </div>

    <!-- فیلتر دسته‌بندی (حالا واقعی، از دیتابیس) -->
    <div class="cat-dock" id="cat-dock">
        <div class="cat-scroll flex gap-2 sm:gap-3 overflow-x-auto pb-3 sm:justify-center sm:flex-wrap">
            <a href="?cat=0<?php echo $search ? '&search=' . urlencode($search) : ''; ?>"
                class="cat-chip <?php echo $category_id === 0 ? 'active' : ''; ?>">
                🛍️ همه
            </a>
            <?php foreach ($db_categories as $cat): ?>
                <a href="?cat=<?php echo $cat['id']; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>"
                    class="cat-chip <?php echo $category_id === (int) $cat['id'] ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($cat['icon'] ?: '📦'); ?> <?php echo htmlspecialchars($cat['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6 lg:gap-8">
        <?php
        // ===== کوئری نهایی: فیلتر واقعی دسته‌بندی + تخفیف + امتیاز =====
        $sql = "SELECT p.*,
                       d.percentage AS discount_percent,
                       (SELECT ROUND(AVG(r.rating),1) FROM reviews r WHERE r.product_id = p.id AND r.status='approved') AS avg_rating,
                       (SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.id AND r.status='approved') AS review_count
                FROM products p
                LEFT JOIN discounts d ON d.product_id = p.id AND d.active = 1
                WHERE p.name LIKE '%$search_sql%'";
        if ($category_id > 0) {
            $sql .= " AND p.category_id = " . $category_id;
        }
        $sql .= " ORDER BY p.featured DESC, p.on_sale DESC, p.id DESC";
        $result = $conn->query($sql);
        $shown = 0;
        $idx = 0;

        // آیدی محصولات علاقه‌مندی کاربر لاگین‌شده
        $wishlist_ids = [];
        if (isset($_SESSION['user_id'])) {
            $wu = intval($_SESSION['user_id']);
            $wres = $conn->query("SELECT product_id FROM wishlist WHERE user_id = $wu");
            while ($wres && $wrow = $wres->fetch_assoc()) {
                $wishlist_ids[] = (int) $wrow['product_id'];
            }
        }

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $shown++;
                $idx++;
                $img = !empty($row['image']) ? $row['image'] : 'https://cdn-icons-png.flaticon.com/512/3159/3159614.png';

                $has_discount = !empty($row['on_sale']) && !empty($row['discount_percent']);
                $final_price = $row['price'];
                if ($has_discount) {
                    $final_price = intval($row['price'] - ($row['price'] * $row['discount_percent'] / 100));
                }

                $is_low_stock = isset($row['stock_quantity']) && $row['stock_quantity'] > 0 && $row['stock_quantity'] <= 10;
                $is_wished = in_array((int) $row['id'], $wishlist_ids);
                $has_rating = !empty($row['avg_rating']);
                ?>
                <div class="product-card reveal rounded-2xl overflow-hidden group relative"
                    style="--i: <?php echo ($idx % 8); ?>" data-tilt
                    data-name="<?php echo htmlspecialchars($row['name']); ?>"
                    data-desc="<?php echo htmlspecialchars($row['description']); ?>">

                    <div class="wishlist-heart <?php echo $is_wished ? 'active' : ''; ?>" role="button" tabindex="0"
                        aria-label="افزودن به علاقه‌مندی"
                        onclick="toggleWishlist(<?php echo $row['id']; ?>, this)">
                        <?php echo $is_wished ? '❤️' : '🤍'; ?>
                    </div>

                    <div class="img-wrap aspect-[3/4] w-full overflow-hidden bg-gray-900/40 relative">
                        <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" loading="lazy"
                            decoding="async"
                            class="p-img w-full h-full object-cover">

                        <div class="absolute top-2 right-2 sm:top-3 sm:right-3 flex flex-col gap-1.5 items-end z-[4]">
                            <?php if (!empty($row['featured'])): ?>
                                <span class="badge-featured">🔥 پیشنهاد ویژه</span>
                            <?php endif; ?>
                            <?php if ($has_discount): ?>
                                <span class="badge-sale">٪<?php echo $row['discount_percent']; ?> تخفیف</span>
                            <?php endif; ?>
                        </div>

                        <div class="buy-veil">
                            <a href="order.php?id=<?php echo $row['id']; ?>"
                                class="buy-btn bg-white text-black px-4 sm:px-6 py-2 rounded-full font-bold hover:bg-[#d4af37] transition text-sm">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 12H5M12 5l-7 7 7 7" />
                                </svg>
                                خرید سریع
                            </a>
                        </div>
                    </div>

                    <div class="p-3 sm:p-4">
                        <h3 class="p-title text-white font-bold truncate mb-1 text-sm sm:text-base"><?php echo $row['name']; ?></h3>

                        <?php if ($has_rating): ?>
                            <div class="flex items-center gap-1 mb-1.5">
                                <span class="rating-stars"><?php
                                $full = round($row['avg_rating']);
                                echo str_repeat('★', $full) . str_repeat('☆', 5 - $full);
                                ?></span>
                                <span class="text-gray-500 text-[11px]">(<?php echo $row['review_count']; ?> نظر)</span>
                            </div>
                        <?php endif; ?>

                        <p class="text-xs line-clamp-2 mb-2" style="color: var(--text-low); line-height: 1.75"><?php echo $row['description']; ?></p>

                        <?php if ($is_low_stock): ?>
                            <div class="mb-2">
                                <div class="stock-warning"><span class="spark">⚡</span> فقط <?php echo $row['stock_quantity']; ?> عدد باقی مانده!</div>
                                <div class="stock-bar"><i style="--fill: <?php echo max(0.08, $row['stock_quantity'] / 10); ?>"></i></div>
                            </div>
                        <?php endif; ?>

                        <div class="flex justify-between items-center border-t pt-3" style="border-color: var(--line)">
                            <span class="flex items-baseline num">
                                <?php if ($has_discount): ?>
                                    <span class="price-old"><?php echo number_format($row['price']); ?></span>
                                <?php endif; ?>
                                <span
                                    class="text-[#d4af37] font-bold text-sm sm:text-base"><?php echo number_format($final_price); ?>
                                    <small>تومان</small></span>
                            </span>
                            <?php if (empty($row['shipping_cost'])): ?>
                                <span class="text-green-400 text-[10px] font-bold">ارسال رایگان</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php
            }
            if ($shown === 0) {
                echo '<div class="empty-state"><div class="ring">🔍</div><p class="text-white font-bold text-lg">محصولی در این دسته یافت نشد.</p><p class="text-sm" style="color: var(--text-low)">دسته‌ی «همه» را بزن یا یک عبارت دیگر جست‌وجو کن.</p></div>';
            }
        } else {
            echo '<div class="empty-state"><div class="ring">🛍️</div><p class="text-white font-bold text-lg">محصولی یافت نشد.</p><p class="text-sm" style="color: var(--text-low)">به‌زودی کالکشن‌های جدید اضافه می‌شوند.</p></div>';
        }
        ?>
    </div>
</div>

<button id="to-top" aria-label="بازگشت به بالا">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
        stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 19V5M5 12l7-7 7 7" />
    </svg>
</button>

<script>
    // ===== توست: جایگزین alert، با همان پیام‌ها =====
    function shopToast(msg, type) {
        const dock = document.getElementById('toast-dock');
        if (!dock) { alert(msg); return; }
        const t = document.createElement('div');
        t.className = 'toast ' + (type === 'err' ? 'err' : 'ok');
        t.textContent = msg;
        dock.appendChild(t);
        setTimeout(() => {
            t.classList.add('out');
            setTimeout(() => t.remove(), 320);
        }, 2600);
    }

    // ===== افزودن/حذف از علاقه‌مندی (Wishlist) =====
    // نکته: api/wishlist.php اکشن toggle نداره، فقط add و remove داره
    // پس خودمون بر اساس وضعیت فعلی (کلاس active) تصمیم می‌گیریم کدوم اکشن رو بزنیم
    function toggleWishlist(productId, el) {
        <?php if (!isset($_SESSION['user_id'])): ?>
            openAuthModal();
            return;
        <?php endif; ?>

        const isCurrentlyActive = el.classList.contains('active');
        const action = isCurrentlyActive ? 'remove' : 'add';

        const fd = new FormData();
        fd.append('action', action);
        fd.append('product_id', productId);

        // بازخورد فوری (در صورت خطا برگردانده می‌شود)
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
                    shopToast(on ? 'به علاقه‌مندی‌ها اضافه شد' : 'از علاقه‌مندی‌ها حذف شد', 'ok');
                } else {
                    shopToast(d.message || 'خطا در ثبت علاقه‌مندی', 'err');
                }
            })
            .catch(() => shopToast('خطا در ارتباط با سرور', 'err'));
    }

    // پشتیبانی از کیبورد برای قلب علاقه‌مندی
    document.addEventListener('keydown', e => {
        if ((e.key === 'Enter' || e.key === ' ') && document.activeElement &&
            document.activeElement.classList.contains('wishlist-heart')) {
            e.preventDefault();
            document.activeElement.click();
        }
    });
</script>

<script>
    // مدال‌ها — حفظ شد
    // function toggleModal(mId, bId, show) { const m=document.getElementById(mId),b=document.getElementById(bId); if(show){ m.classList.remove('hidden'); setTimeout(()=>{m.classList.remove('opacity-0');b.classList.remove('scale-90');b.classList.add('scale-100')},10); if(mId==='profile-modal') setTimeout(()=>document.getElementById('chat-history').scrollTop=document.getElementById('chat-history').scrollHeight,100);}else{ m.classList.add('opacity-0');b.classList.remove('scale-100');b.classList.add('scale-90');setTimeout(()=>{m.classList.add('hidden')},300); } }
    //     const openAuthModal=()=>toggleModal('auth-modal','auth-box-content',true); const closeAuthModal=()=>toggleModal('auth-modal','auth-box-content',false);
    //     const openProfileModal=()=>toggleModal('profile-modal','profile-box-content',true); const closeProfileModal=()=>toggleModal('profile-modal','profile-box-content',false);
    //     function switchAuthMode(mode){document.getElementById('login-form').classList.toggle('hidden',mode!=='login');document.getElementById('register-step-1').classList.toggle('hidden',mode==='login');document.getElementById('register-step-2').classList.add('hidden');}

    //     // بستن مودال با کلیک روی پس‌زمینه و کلید Esc (بهبود UX)
    //     document.querySelectorAll('#auth-modal,#profile-modal').forEach(m=>{
    //         m.addEventListener('click', e=>{ if(e.target===m){ m.id==='auth-modal'?closeAuthModal():closeProfileModal(); } });
    //     });
    //     document.addEventListener('keydown', e=>{ if(e.key==='Escape'){ closeAuthModal(); closeProfileModal(); } });

    //     // AJAX — حفظ شد
    //     function requestRegister() {
    //         const n=document.getElementById('reg-name').value,e=document.getElementById('reg-email').value,p=document.getElementById('reg-phone').value,pw=document.getElementById('reg-pass').value,cpw=document.getElementById('reg-confirm-pass').value;
    //         if(!n||!e||!p||!pw||!cpw)return alert("لطفا تمام فیلدها را پر کنید");
    //         if(pw!==cpw)return alert("رمز عبور و تکرار آن مطابقت ندارند");
    //         const fd=new FormData(); fd.append('action','register_request'); fd.append('name',n); fd.append('email',e); fd.append('phone',p); fd.append('password',pw);
    //             fetch('auth.php', {
    //         method: 'POST',
    //         body: fd
    //     })
    //     .then(r => r.json())
    //     .then(d => {

    //         if (d.status === 'success') {

    //             if (d.otp) {
    //                 alert("کد تایید شما: " + d.otp);
    //             } else {
    //                 alert(d.message);
    //             }

    //             document.getElementById('register-step-1').classList.add('hidden');
    //             document.getElementById('register-step-2').classList.remove('hidden');

    //         } else {
    //             alert(d.message);
    //         }

    //     })
    //     }
    //     function verifyAndLogin() {
    //         const e=document.getElementById('reg-email').value,c=document.getElementById('verify-code').value;
    //         const fd=new FormData(); fd.append('action','verify_code'); fd.append('email',e); fd.append('code',c);
    //         fetch('auth.php',{method:'POST',body:fd}).then(r=>r.json()).then(d=>d.status==='success'?location.reload():alert(d.message));
    //     }
    //     function performLogin() {
    //         const e=document.getElementById('login-email').value,pw=document.getElementById('login-pass').value;
    //         const fd=new FormData(); fd.append('action','login'); fd.append('email',e); fd.append('password',pw);
    //         fetch('auth.php',{method:'POST',body:fd}).then(r=>r.json()).then(d=>d.status==='success'?location.reload():alert(d.message));
    //     }
    //     function uploadProfile() {
    //         const f=document.getElementById('dash-upload').files[0]; if(!f)return;
    //         document.getElementById('dash-loader').classList.replace('hidden','flex');
    //         const fd=new FormData(); fd.append('action','upload_profile'); fd.append('profile_img',f);
    //         fetch('auth.php',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{
    //             document.getElementById('dash-loader').classList.replace('flex','hidden');
    //             if(d.status==='success'){document.getElementById('dash-img').src=d.url;location.reload();}else alert(d.message);
    //         });
    //     }
    //     function sendMessage() {
    //     const m=document.getElementById('chat-input').value; if(!m)return;
    //     const fd=new FormData(); fd.append('action','send_msg'); fd.append('message',m);
    //     fetch('auth.php',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{
    //         if(d.status==='success'){
    //             const c=document.getElementById('chat-history');
    //             const wrap = document.createElement('div');
    //             wrap.className = 'flex justify-start animate__animated animate__fadeInUp animate__faster';
    //             const bubble = document.createElement('div');
    //             bubble.className = 'bg-gray-700 text-white text-sm px-4 py-2.5 rounded-2xl max-w-[85%] leading-relaxed';
    //             bubble.textContent = m; // امن: همیشه به‌عنوان متن ساده نمایش داده میشه، نه کد HTML
    //             wrap.appendChild(bubble);
    //             c.appendChild(wrap);
    //             c.scrollTop=c.scrollHeight; document.getElementById('chat-input').value='';
    //         }
    //     });
    // }

    // ============================================================
    // موتور انیمیشن صفحه
    // ============================================================
    (function () {
        const REDUCED = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        // --- ۱. اسکلت تصاویر: تا لود شدن، درخشش؛ بعد محو ---
        document.querySelectorAll('.img-wrap').forEach(w => {
            const img = w.querySelector('img');
            if (!img) { w.classList.add('loaded'); return; }
            const done = () => w.classList.add('loaded');
            if (img.complete && img.naturalWidth) done();
            else { img.addEventListener('load', done); img.addEventListener('error', done); }
        });

        if (REDUCED) {
            document.querySelectorAll('.reveal').forEach(el => el.classList.add('is-in'));
            return;
        }

        // --- ۲. ظهور تدریجی با IntersectionObserver ---
        if ('IntersectionObserver' in window) {
            const io = new IntersectionObserver((entries) => {
                entries.forEach(e => {
                    if (e.isIntersecting) {
                        e.target.classList.add('is-in');
                        io.unobserve(e.target);
                    }
                });
            }, { rootMargin: '0px 0px -6% 0px', threshold: 0.06 });
            document.querySelectorAll('.reveal').forEach(el => io.observe(el));
        } else {
            document.querySelectorAll('.reveal').forEach(el => el.classList.add('is-in'));
        }

        // --- ۳. نوار پیشرفت اسکرول + دکمه بالا + داک چسبان + پارالاکس بنر ---
        const rail = document.getElementById('scroll-rail');
        const top = document.getElementById('to-top');
        const dock = document.getElementById('cat-dock');
        const layers = document.querySelectorAll('[data-parallax]');
        let raf = null;

        function onScroll() {
            if (raf) return;
            raf = requestAnimationFrame(() => {
                const y = window.scrollY || document.documentElement.scrollTop;
                const h = document.documentElement.scrollHeight - window.innerHeight;
                if (rail) rail.style.transform = 'scaleX(' + (h > 0 ? Math.min(1, y / h) : 0) + ')';
                if (top) top.classList.toggle('show', y > 620);
                if (dock) dock.classList.toggle('stuck', dock.getBoundingClientRect().top <= 1);
                layers.forEach(l => {
                    const r = l.parentElement.getBoundingClientRect();
                    if (r.bottom > -200 && r.top < window.innerHeight + 200) {
                        const k = parseFloat(l.dataset.parallax) || 0.15;
                        l.style.transform = 'translate3d(0,' + (-r.top * k) + 'px,0)';
                    }
                });
                raf = null;
            });
        }

        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', onScroll);
        onScroll();

        if (top) {
            top.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
        }

        // --- ۴. اسکرول افقی چیپ‌ها با چرخ ماوس (دسکتاپ) ---
        const scroller = document.querySelector('.cat-scroll');
        if (scroller) {
            scroller.addEventListener('wheel', e => {
                if (scroller.scrollWidth <= scroller.clientWidth) return;
                if (Math.abs(e.deltaY) < Math.abs(e.deltaX)) return;
                e.preventDefault();
                scroller.scrollLeft += e.deltaY;
            }, { passive: false });
            // چیپ فعال را در دید بیاور
            const act = scroller.querySelector('.cat-chip.active');
            if (act && act.scrollIntoView) act.scrollIntoView({ block: 'nearest', inline: 'center' });
        }
    })();

    // ===== افکت 3D Tilt روی کارت‌ها (با غیرفعال‌سازی در دستگاه‌های لمسی) =====
    (function () {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        // غیرفعال‌سازی تیلت روی دستگاه‌های لمسی برای تجربه بهتر موبایل
        if (window.matchMedia('(hover: none)').matches) return;

        document.querySelectorAll('[data-tilt]').forEach(card => {
            let frame = null;

            card.addEventListener('mousemove', e => {
                const r = card.getBoundingClientRect();
                const px = (e.clientX - r.left) / r.width;
                const py = (e.clientY - r.top) / r.height;
                const x = px - 0.5, y = py - 0.5;

                card.style.setProperty('--mx', (px * 100) + '%');
                card.style.setProperty('--my', (py * 100) + '%');

                if (frame) return;
                frame = requestAnimationFrame(() => {
                    card.style.transition = 'box-shadow .45s cubic-bezier(.16,1,.3,1), border-color .45s cubic-bezier(.16,1,.3,1)';
                    card.style.transform =
                        'translateY(-10px) perspective(900px) rotateY(' + (x * 7) + 'deg) rotateX(' + (-y * 7) + 'deg) scale(1.012)';
                    frame = null;
                });
            });

            card.addEventListener('mouseleave', () => {
                card.style.transition = '';
                card.style.transform = '';
                card.style.setProperty('--mx', '50%');
                card.style.setProperty('--my', '0%');
            });
        });
    })();
</script>
<!-- دستیار هوشمند — حفظ شد -->
<?php include 'assistant.php'; ?>
