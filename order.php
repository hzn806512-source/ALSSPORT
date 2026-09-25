<?php
include 'db.php';
include 'header.php';

// بررسی لاگین
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('برای خرید لباس لطفاً ابتدا وارد حساب کاربری خود شوید.'); window.location.href='index.php';</script>";
    exit();
}

// بررسی محصول
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$main_id = intval($_GET['id']);

// ===== دریافت محصول اصلی به همراه تخفیف فعال (اگر وجود داشته باشد) =====
$result_main = $conn->query("
    SELECT p.*, d.percentage AS discount_percent
    FROM products p
    LEFT JOIN discounts d ON d.product_id = p.id AND d.active = 1
    WHERE p.id = $main_id
");

if ($result_main->num_rows == 0) {
    header("Location: index.php");
    exit();
}

$prod = $result_main->fetch_assoc();
$shipping_cost = isset($prod['shipping_cost']) ? intval($prod['shipping_cost']) : 0;

// ===== محاسبه‌ی قیمت نهایی محصول اصلی با احتساب تخفیف =====
$main_has_discount = !empty($prod['on_sale']) && !empty($prod['discount_percent']);
$main_original_price = intval($prod['price']);
$main_final_price = $main_has_discount
    ? intval($main_original_price - ($main_original_price * $prod['discount_percent'] / 100))
    : $main_original_price;

// 1. پردازش گالری تصاویر
$gallery = [];
if (!empty($prod['image'])) {
    $gallery[] = $prod['image'];
}
if (!empty($prod['gallery_images'])) {
    $extra_imgs = json_decode($prod['gallery_images'], true);
    if (is_array($extra_imgs)) {
        $gallery = array_merge($gallery, $extra_imgs);
    }
}
$gallery = array_unique($gallery);
if (empty($gallery)) {
    $gallery[] = "https://cdn-icons-png.flaticon.com/512/3159/3159614.png";
}

// 2. پردازش رنگ‌ها
$colors_parsed = [];
if (!empty($prod['available_colors'])) {
    $raw_colors = explode(',', $prod['available_colors']);
    foreach ($raw_colors as $rc) {
        $rc = trim($rc);
        if (empty($rc))
            continue;

        if (strpos($rc, ':') !== false) {
            $parts = explode(':', $rc);
            $colors_parsed[] = ['name' => $parts[0], 'hex' => $parts[1]];
        } else {
            $colors_parsed[] = ['name' => $rc, 'hex' => translateColor($rc)];
        }
    }
}

// ===== محصولات پیشنهادی، هم با تخفیف فعال (اگر داشته باشند) =====
$result_others = $conn->query("
    SELECT p.*, d.percentage AS discount_percent
    FROM products p
    LEFT JOIN discounts d ON d.product_id = p.id AND d.active = 1
    WHERE p.id != $main_id
    LIMIT 4
");

$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
?>

<style>
    :root {
        --shop-gold: #d4af37;
        --shop-gold-light: #f0d375;
        --shop-gold-soft: rgba(212, 175, 55, 0.12);
        --shop-dark: #111827;
        --shop-panel: #1f2937;
        --shop-border: #2c3644;
    }

    * {
        box-sizing: border-box;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(14px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes shimmer {
        0% {
            background-position: -200% 0;
        }

        100% {
            background-position: 200% 0;
        }
    }

    @keyframes softPulse {

        0%,
        100% {
            box-shadow: 0 0 0 0 rgba(212, 175, 55, 0.35);
        }

        50% {
            box-shadow: 0 0 0 8px rgba(212, 175, 55, 0);
        }
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    .op-fade {
        animation: fadeInUp 0.5s ease both;
    }

    .op-fade-1 {
        animation-delay: 0.05s;
    }

    .op-fade-2 {
        animation-delay: 0.12s;
    }

    .op-fade-3 {
        animation-delay: 0.2s;
    }

    /* پنل‌های شیشه‌ای لوکس */
    .op-panel {
        background: linear-gradient(180deg, rgba(31, 41, 55, 0.92) 0%, rgba(25, 32, 44, 0.92) 100%);
        backdrop-filter: blur(14px);
        border: 1px solid var(--shop-border);
        border-radius: 20px;
        box-shadow: 0 24px 48px -20px rgba(0, 0, 0, 0.55);
    }

    .op-eyebrow {
        font-size: 11px;
        font-weight: 700;
        color: var(--shop-gold);
        text-transform: uppercase;
        letter-spacing: 0.12em;
        margin-bottom: 6px;
        display: block;
    }

    .op-section-title {
        font-size: 14px;
        font-weight: 700;
        color: #e5e7eb;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .op-section-title::before {
        content: '';
        width: 4px;
        height: 16px;
        background: linear-gradient(180deg, var(--shop-gold-light), var(--shop-gold));
        border-radius: 3px;
        display: inline-block;
    }

    /* گالری - محصول لوکس */
    .op-main-image {
        aspect-ratio: 3 / 4;
        border-radius: 18px;
        overflow: hidden;
        background: radial-gradient(circle at 50% 30%, #1a2332 0%, #0d1420 100%);
        border: 1px solid var(--shop-border);
        position: relative;
    }

    .op-main-image img {
        transition: transform 0.6s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.35s ease;
    }

    .op-main-image:hover img {
        transform: scale(1.045);
    }

    .op-main-image::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, transparent 60%, rgba(0, 0, 0, 0.35) 100%);
        pointer-events: none;
    }

    .thumb-img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 10px;
        border: 1.5px solid var(--shop-border);
        cursor: pointer;
        opacity: 0.5;
        transition: all 0.25s ease;
        flex-shrink: 0;
    }

    .thumb-img.active {
        border-color: var(--shop-gold);
        opacity: 1;
        box-shadow: 0 0 0 1px var(--shop-gold);
    }

    .thumb-img:hover {
        opacity: 1;
        transform: translateY(-2px);
    }

    /* عنوان و قیمت */
    .op-title {
        font-size: 1.5rem;
        font-weight: 800;
        color: #fff;
        line-height: 1.4;
        margin-bottom: 10px;
    }

    .op-price-row {
        display: flex;
        align-items: baseline;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 16px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--shop-border);
    }

    .op-price-main {
        font-size: 1.6rem;
        font-weight: 800;
        background: linear-gradient(135deg, var(--shop-gold-light), var(--shop-gold));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    .discount-badge {
        display: inline-flex;
        align-items: center;
        background: linear-gradient(135deg, #ef4444, #b91c1c);
        color: #fff;
        font-size: 11px;
        font-weight: 800;
        padding: 4px 11px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(185, 28, 28, 0.35);
    }

    .price-old-line {
        color: #6b7280;
        font-size: 13px;
        text-decoration: line-through;
    }

    .extra-price-old {
        color: #6b7280;
        font-size: 9px;
        text-decoration: line-through;
        margin-left: 4px;
    }

    .op-desc {
        color: #9ca3af;
        font-size: 13.5px;
        line-height: 1.9;
    }

    /* رنگ‌ها */
    .color-option {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        cursor: pointer;
        border: 2px solid var(--shop-dark);
        position: relative;
        box-shadow: 0 0 0 1.5px var(--shop-border);
        transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .color-option:hover {
        transform: scale(1.1);
    }

    .color-radio:checked+.color-option {
        box-shadow: 0 0 0 2px var(--shop-dark), 0 0 0 4px var(--shop-gold);
        transform: scale(1.12);
    }

    .color-radio:checked+.color-option::after {
        content: '✓';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: #fff;
        font-size: 14px;
        font-weight: bold;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.6);
    }

    .op-color-label {
        font-size: 11px;
        color: #9ca3af;
        margin-top: 8px;
        transition: opacity 0.2s ease;
    }

    /* تعداد */
    .qty-stepper {
        display: flex;
        align-items: center;
        border: 1px solid var(--shop-border);
        border-radius: 12px;
        overflow: hidden;
        background: rgba(0, 0, 0, 0.2);
    }

    .qty-btn {
        width: 38px;
        height: 38px;
        background: transparent;
        color: #e5e7eb;
        border: none;
        cursor: pointer;
        font-weight: 700;
        font-size: 16px;
        transition: all 0.2s ease;
    }

    .qty-btn:hover {
        background: var(--shop-gold);
        color: #111827;
    }

    .qty-btn:active {
        transform: scale(0.92);
    }

    #qty-display {
        width: 42px;
        text-align: center;
        color: #fff;
        font-weight: 800;
        font-size: 15px;
    }

    /* Complete the look */
    .extra-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        background: rgba(17, 24, 39, 0.55);
        border: 1px solid var(--shop-border);
        border-radius: 14px;
        padding: 10px;
        transition: all 0.25s ease;
    }

    .extra-card:hover {
        border-color: rgba(212, 175, 55, 0.4);
        transform: translateY(-2px);
    }

    .extra-card img {
        width: 46px;
        height: 46px;
        border-radius: 10px;
        object-fit: cover;
        border: 1px solid var(--shop-border);
    }

    .extra-stepper {
        display: flex;
        align-items: center;
        gap: 6px;
        background: rgba(0, 0, 0, 0.25);
        border-radius: 8px;
        padding: 3px;
    }

    .extra-stepper button {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 15px;
        font-weight: 800;
        width: 22px;
        height: 22px;
        border-radius: 6px;
        color: #9ca3af;
        transition: all 0.2s ease;
    }

    .extra-stepper button:hover {
        background: var(--shop-gold);
        color: #111827;
    }

    .extra-stepper button.plus {
        color: var(--shop-gold);
    }

    /* فاکتور / چک‌اوت */
    .invoice-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: rgba(17, 24, 39, 0.6);
        border: 1px solid var(--shop-border);
        border-radius: 12px;
        padding: 10px 12px;
        font-size: 13px;
        animation: fadeIn 0.3s ease;
    }

    .invoice-extra-row {
        display: flex;
        justify-content: space-between;
        color: #9ca3af;
        font-size: 12px;
        padding: 2px 6px;
        animation: fadeIn 0.3s ease;
    }

    .op-total-box {
        background: linear-gradient(135deg, rgba(212, 175, 55, 0.14), rgba(212, 175, 55, 0.04));
        border: 1px solid rgba(212, 175, 55, 0.35);
        border-radius: 14px;
        padding: 14px 16px;
        margin-top: 14px;
    }

    .op-total-value {
        font-size: 1.5rem;
        font-weight: 900;
        background: linear-gradient(135deg, var(--shop-gold-light), var(--shop-gold));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    .op-address-box textarea {
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .op-address-box textarea:focus {
        box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.15);
    }

    /* دکمه پرداخت لوکس */
    .submit-btn {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, var(--shop-gold-light) 0%, var(--shop-gold) 50%, #b4932a 100%);
        background-size: 200% auto;
        color: #111827;
        width: 100%;
        padding: 16px;
        border-radius: 14px;
        font-size: 1rem;
        font-weight: 800;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 12px 28px rgba(212, 175, 55, 0.25);
        letter-spacing: 0.01em;
    }

    .submit-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 60%;
        height: 100%;
        background: linear-gradient(120deg, transparent, rgba(255, 255, 255, 0.35), transparent);
        transform: skewX(-20deg);
        transition: left 0.6s ease;
    }

    .submit-btn:hover:not(:disabled)::before {
        left: 130%;
    }

    .submit-btn:hover:not(:disabled) {
        background-position: right center;
        transform: translateY(-2px);
        box-shadow: 0 16px 34px rgba(212, 175, 55, 0.4);
    }

    .submit-btn:active:not(:disabled) {
        transform: translateY(0) scale(0.99);
    }

    .submit-btn:disabled {
        opacity: 0.65;
        cursor: not-allowed;
        filter: grayscale(0.4);
    }

    /* وضعیت بررسی آدرس با AI */
    #ai-status-box {
        margin-top: 12px;
        padding: 12px 14px;
        border-radius: 12px;
        font-size: 12px;
        line-height: 1.8;
        display: none;
        align-items: flex-start;
        gap: 10px;
        animation: fadeIn 0.3s ease;
    }

    .ai-loading {
        display: flex !important;
        background: var(--shop-gold-soft);
        color: var(--shop-gold-light);
        border: 1px solid rgba(212, 175, 55, 0.35);
    }

    .ai-success {
        display: flex !important;
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid rgba(16, 185, 129, 0.4);
        color: #34d399;
    }

    .ai-error {
        display: flex !important;
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.4);
        color: #f87171;
    }

    .op-spinner {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        border: 2px solid rgba(212, 175, 55, 0.25);
        border-top-color: var(--shop-gold);
        animation: spin 0.7s linear infinite;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .op-sticky {
        position: static;
    }

    @media (min-width: 1024px) {
        .op-sticky {
            position: sticky;
            top: 6rem;
        }
    }

    @media (max-width: 640px) {
        .op-title {
            font-size: 1.25rem;
        }

        .op-price-main {
            font-size: 1.35rem;
        }

        .op-total-value {
            font-size: 1.25rem;
        }

        .op-panel {
            border-radius: 16px;
        }
    }
</style>

<div class="container mx-auto px-4 py-6 md:py-10 max-w-6xl">

    <a href="index.php"
        class="inline-flex items-center gap-2 mb-5 text-gray-400 hover:text-[#d4af37] transition text-sm">
        <span>←</span> بازگشت به فروشگاه
    </a>

    <form id="orderForm" action="submit_order.php" method="POST">
        <input type="hidden" name="main_product_id" value="<?php echo $prod['id']; ?>">
        <input type="hidden" name="order_details" id="final-json">
        <input type="hidden" name="final_total_amount" id="final-total-input">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <div class="lg:col-span-7 space-y-6">

                <div class="op-panel p-5 md:p-6 op-fade op-fade-1">
                    <div class="flex flex-col md:flex-row gap-6">

                        <div class="w-full md:w-1/2 shrink-0">
                            <div class="op-main-image mb-3">
                                <img id="main-img" src="<?php echo $gallery[0]; ?>"
                                    class="w-full h-full object-contain">
                            </div>
                            <?php if (count($gallery) > 1): ?>
                                <div class="flex gap-2 overflow-x-auto pb-1 custom-scroll">
                                    <?php foreach ($gallery as $idx => $img): ?>
                                        <img src="<?php echo $img; ?>" onclick="changeImage('<?php echo $img; ?>', this)"
                                            class="thumb-img <?php echo $idx == 0 ? 'active' : ''; ?>">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="flex-1">
                            <span class="op-eyebrow">محصول اورجینال</span>
                            <h1 class="op-title"><?php echo $prod['name']; ?></h1>

                            <div class="op-price-row">
                                <?php if ($main_has_discount): ?>
                                    <span class="discount-badge">٪<?php echo $prod['discount_percent']; ?> تخفیف</span>
                                    <span class="price-old-line"><?php echo number_format($main_original_price); ?></span>
                                <?php endif; ?>
                                <span class="op-price-main"><?php echo number_format($main_final_price); ?></span>
                                <span class="text-xs text-gray-500">تومان</span>
                            </div>

                            <p class="op-desc mb-6 h-24 overflow-y-auto custom-scroll">
                                <?php echo $prod['description']; ?></p>

                            <?php if (!empty($colors_parsed)): ?>
                                <div class="mb-5">
                                    <div class="op-section-title">انتخاب رنگ</div>
                                    <div class="flex flex-wrap gap-3">
                                        <?php foreach ($colors_parsed as $idx => $cp): ?>
                                            <label class="relative group text-center" title="<?php echo $cp['name']; ?>">
                                                <input type="radio" name="selected_color"
                                                    value="<?php echo $cp['name']; ?>:<?php echo $cp['hex']; ?>"
                                                    class="color-radio hidden" <?php echo $idx == 0 ? 'checked' : ''; ?>
                                                    onchange="updateFactor()">
                                                <div class="color-option" style="background-color: <?php echo $cp['hex']; ?>;">
                                                </div>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <input type="hidden" name="selected_color" value="استاندارد:#000">
                            <?php endif; ?>

                            <div
                                class="flex items-center justify-between bg-black/20 border border-[var(--shop-border)] p-3 rounded-xl">
                                <span class="text-gray-400 text-sm">تعداد سفارش</span>
                                <div class="qty-stepper">
                                    <button type="button" class="qty-btn" onclick="changeQty(-1)">−</button>
                                    <span id="qty-display">1</span>
                                    <button type="button" class="qty-btn" onclick="changeQty(1)">+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </div>

            <div class="lg:col-span-5">
                <div class="op-panel p-5 md:p-6 op-sticky op-fade op-fade-3">
                    <div class="op-section-title">فاکتور نهایی</div>

                    <div class="space-y-2 mb-2" id="invoice-list"></div>

                    <div
                        class="flex justify-between text-gray-400 text-xs pt-2 border-t border-[var(--shop-border)] mt-2">
                        <span>هزینه ارسال (پست پیشتاز)</span>
                        <span><?php echo number_format($shipping_cost); ?> تومان</span>
                    </div>

                    <div class="op-total-box flex items-center justify-between">
                        <span class="text-gray-300 text-sm font-bold">مبلغ قابل پرداخت</span>
                        <span class="op-total-value"><span id="total-price">0</span> <span
                                class="text-xs text-gray-400 font-normal">تومان</span></span>
                    </div>

                    <div class="mt-5 op-address-box">
                        <label class="text-xs text-[#d4af37] font-bold block mb-1.5">آدرس دقیق پستی <span
                                class="text-red-500">*</span></label>
                        <textarea id="address-input" name="address" rows="3"
                            class="w-full bg-black/25 border border-[var(--shop-border)] text-white rounded-xl p-3 focus:border-[#d4af37] focus:outline-none text-sm"
                            placeholder="استان، شهر، خیابان، کوچه، پلاک..."></textarea>
                        <div id="ai-status-box"></div>
                    </div>

                    <button type="button" onclick="checkAddressAndPay()" class="submit-btn" id="pay-btn">
                        تکمیل و پرداخت سفارش
                    </button>
                </div>
            </div>

        </div>
    </form>
</div>

<script>
    const mainProd = {
        id: "<?php echo $prod['id']; ?>",
        name: "<?php echo $prod['name']; ?>",
        price: <?php echo $main_final_price; ?>
    };
    const shippingCost = <?php echo $shipping_cost; ?>;

    const SAMBANOVA_API_KEY = "6add4815-fd20-484b-b785-b809533a0874";

    let currentQty = 1;
    let extras = {};

    function changeImage(src, el) {
        const mainImg = document.getElementById('main-img');
        mainImg.style.opacity = 0;
        setTimeout(() => {
            mainImg.src = src;
            mainImg.style.opacity = 1;
        }, 150);
        document.querySelectorAll('.thumb-img').forEach(t => t.classList.remove('active'));
        el.classList.add('active');
    }

    function changeQty(n) {
        let v = currentQty + n;
        if (v < 1) v = 1;
        currentQty = v;
        document.getElementById('qty-display').innerText = v;
        updateFactor();
    }

    function updateExtra(id, n) {
        let el = document.getElementById('extra-qty-' + id);
        let v = parseInt(el.value) + n;
        if (v < 0) v = 0;
        el.value = v;

        if (v > 0) extras[id] = {
            name: el.dataset.name,
            price: parseInt(el.dataset.price),
            qty: v
        };
        else delete extras[id];

        updateFactor();
    }

    function updateFactor() {
        const list = document.getElementById('invoice-list');
        list.innerHTML = '';
        let total = 0;

        const colorInput = document.querySelector('input[name="selected_color"]:checked');
        const [cName, cHex] = colorInput.value.split(':');
        const mainTotal = mainProd.price * currentQty;
        total += mainTotal;

        list.innerHTML += `
        <div class="invoice-row">
            <div>
                <div class="text-white font-bold">${mainProd.name}</div>
                <div class="text-xs text-gray-500 flex items-center gap-1 mt-1">
                    <span style="width:8px;height:8px;border-radius:50%;background:${cHex};display:inline-block;"></span> ${cName}
                </div>
            </div>
            <div class="text-right">
                <div class="text-white text-xs">x${currentQty}</div>
                <div class="text-[#d4af37] text-xs font-bold">${mainTotal.toLocaleString()}</div>
            </div>
        </div>`;

        for (const [id, item] of Object.entries(extras)) {
            const itemTotal = item.price * item.qty;
            total += itemTotal;
            list.innerHTML += `
            <div class="invoice-extra-row">
                <span>+ ${item.name}</span>
                <span>x${item.qty} (${itemTotal.toLocaleString()})</span>
            </div>`;
        }

        const finalAmount = total + shippingCost;
        document.getElementById('total-price').innerText = finalAmount.toLocaleString();
        document.getElementById('final-total-input').value = finalAmount;

        let finalData = [];
        finalData.push({ id: mainProd.id, name: mainProd.name, color: cName, qty: currentQty, price: mainProd.price });
        for (const [id, item] of Object.entries(extras)) {
            finalData.push({ id: id, name: item.name + ' (ست)', color: '-', qty: item.qty, price: item.price });
        }
        document.getElementById('final-json').value = JSON.stringify(finalData);
    }

    async function checkAddressAndPay() {
        const address = document.getElementById('address-input').value.trim();
        const box = document.getElementById('ai-status-box');
        const btn = document.getElementById('pay-btn');

        if (address.length < 10) {
            alert("لطفاً آدرس کامل را وارد کنید (حداقل ۱۰ کاراکتر).");
            return;
        }

        btn.disabled = true;
        btn.innerText = "در حال استعلام...";
        box.style.display = "flex";
        box.className = "ai-loading";
        box.innerHTML = `<span class="op-spinner"></span><span>هوش مصنوعی در حال بررسی صحت آدرس شماست...</span>`;

        try {
            const response = await fetch("https://api.sambanova.ai/v1/chat/completions", {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${SAMBANOVA_API_KEY}`,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    model: "Llama-3.2-3B-Instruct",
                    messages: [
                        { role: "system", content: "You are an address validator for Iran. Respond only with a JSON object: {\"valid\": true/false, \"message\": \"Persian explanation\"}." },
                        { role: "user", content: `Verify this address: "${address}". If it contains city/street keywords, it is valid.` }
                    ],
                    temperature: 0.1
                }),
            });

            const data = await response.json();

            if (data.choices && data.choices[0].message.content) {
                const rawContent = data.choices[0].message.content;
                const jsonMatch = rawContent.match(/\{[\s\S]*\}/);
                const result = jsonMatch ? JSON.parse(jsonMatch[0]) : { valid: true, message: "آدرس قابل قبول به نظر می‌رسد." };

                if (result.valid) {
                    box.className = "ai-success";
                    box.innerHTML = `<span>✓</span><span><b>آدرس تایید شد</b><br>${result.message}</span>`;

                    setTimeout(() => {
                        btn.innerText = "انتقال به درگاه پرداخت...";
                        document.getElementById('orderForm').submit();
                    }, 1000);
                } else {
                    box.className = "ai-error";
                    box.innerHTML = `<span>!</span><span><b>لطفاً آدرس را بازبینی کنید</b><br>${result.message}</span>`;
                    btn.disabled = false;
                    btn.innerText = "بررسی مجدد و پرداخت";
                }
            } else {
                throw new Error("Invalid API Response");
            }

        } catch (error) {
            console.warn(error);
            box.className = "ai-loading";
            box.innerHTML = `<span>⚠</span><span>سیستم هوشمند موقتاً در دسترس نیست، سفارش ثبت می‌شود.</span>`;
            setTimeout(() => document.getElementById('orderForm').submit(), 1500);
        }
    }

    updateFactor();
</script>