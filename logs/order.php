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

$main_id = $_GET['id'];
$result_main = $conn->query("SELECT * FROM products WHERE id = $main_id");

if ($result_main->num_rows == 0) {
    header("Location: index.php");
    exit();
}

$prod = $result_main->fetch_assoc();
$shipping_cost = isset($prod['shipping_cost']) ? intval($prod['shipping_cost']) : 0;

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

// محصولات پیشنهادی
$result_others = $conn->query("SELECT * FROM products WHERE id != $main_id LIMIT 4");

$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
?>

<style>
    :root {
        --accent: #d4af37;
        --accent-dark: #b4932a;
        --ink: #f3f4f6;
        --muted: #9ca3af;
        --surface: #1f2937;
        --surface-soft: #111827;
        --border: #374151;
    }

    body {
        background: linear-gradient(180deg, #111827 0%, #0f172a 100%);
        color: var(--ink);
        font-family: 'Vazir', sans-serif;
    }

    .page-shell {
        padding: 2rem 0 4rem;
    }

    .product-panel,
    .summary-card {
        background: rgba(31, 41, 55, 0.95);
        border: 1px solid var(--border);
        border-radius: 24px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
        overflow: hidden;
    }

    .thumb-img {
        width: 62px;
        height: 62px;
        object-fit: cover;
        border-radius: 14px;
        border: 2px solid var(--border);
        cursor: pointer;
        transition: all 0.2s ease;
        opacity: 0.85;
        background: #0f172a;
    }

    .thumb-img.active,
    .thumb-img:hover {
        border-color: var(--accent);
        opacity: 1;
        transform: scale(1.05);
    }

    .color-option {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        cursor: pointer;
        border: 3px solid #0f172a;
        position: relative;
        box-shadow: 0 0 0 2px #4b5563;
        transition: 0.2s;
    }

    .color-radio:checked+.color-option {
        box-shadow: 0 0 0 2px var(--accent), 0 0 10px rgba(212, 175, 55, 0.2);
        transform: scale(1.08);
    }

    .color-radio:checked+.color-option::after {
        content: '✓';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 15px;
        font-weight: bold;
        text-shadow: 0 1px 3px black;
    }

    .qty-btn {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: #111827;
        color: var(--ink);
        border: 1px solid var(--border);
        cursor: pointer;
        font-weight: bold;
    }

    .qty-btn:hover {
        background: #1f2937;
        color: var(--accent);
        border-color: rgba(212, 175, 55, 0.35);
    }

    .submit-btn {
        background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
        color: #111827;
        width: 100%;
        padding: 16px;
        border-radius: 16px;
        font-size: 1rem;
        font-weight: bold;
        border: none;
        cursor: pointer;
        transition: 0.3s;
        box-shadow: 0 10px 25px rgba(212, 175, 55, 0.24);
        margin-top: 20px;
        position: relative;
    }

    .submit-btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        filter: grayscale(0.8);
    }

    .submit-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 14px 30px rgba(212, 175, 55, 0.32);
    }

    #ai-status-box {
        margin-top: 10px;
        padding: 12px;
        border-radius: 12px;
        font-size: 12px;
        line-height: 1.6;
        display: none;
    }

    .ai-loading {
        background: rgba(212, 175, 55, 0.12);
        color: var(--accent);
        border: 1px solid rgba(212, 175, 55, 0.25);
        animation: pulse 1.5s infinite;
    }

    .ai-success {
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid #10b981;
        color: #10b981;
    }

    .ai-error {
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid #ef4444;
        color: #ef4444;
    }

    @keyframes pulse {
        0% {
            opacity: 0.7;
        }

        50% {
            opacity: 1;
        }

        100% {
            opacity: 0.7;
        }
    }
</style>

<div class="page-shell container mx-auto px-4 max-w-7xl">
    <a href="index.php"
        class="inline-flex items-center gap-2 mb-6 text-sm font-bold text-[#d4af37] hover:text-white transition">
        <span>←</span> بازگشت به فروشگاه
    </a>

    <form id="orderForm" action="submit_order.php" method="POST">
        <input type="hidden" name="main_product_id" value="<?php echo $prod['id']; ?>">
        <input type="hidden" name="order_details" id="final-json">
        <input type="hidden" name="final_total_amount" id="final-total-input">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <div class="lg:col-span-7 space-y-6">
                <div class="product-panel p-6">
                    <div class="flex flex-col md:flex-row gap-6">
                        <div class="w-full md:w-1/2 shrink-0">
                            <div
                                class="aspect-[3/4] rounded-2xl overflow-hidden mb-4 border border-slate-200 relative bg-[#f8fafc]">
                                <img id="main-img" src="<?php echo $gallery[0]; ?>"
                                    class="w-full h-full object-contain transition duration-300">
                            </div>
                            <?php if (count($gallery) > 1): ?>
                                <div class="flex gap-2 overflow-x-auto pb-2">
                                    <?php foreach ($gallery as $idx => $img): ?>
                                        <img src="<?php echo $img; ?>" onclick="changeImage('<?php echo $img; ?>', this)"
                                            class="thumb-img <?php echo $idx == 0 ? 'active' : ''; ?>">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="flex-1">
                            <h1 class="text-2xl font-black text-white mb-2"><?php echo $prod['name']; ?></h1>
                            <div
                                class="inline-flex items-center rounded-full bg-[#111827] border border-[#374151] px-3 py-1 text-sm font-bold text-[#d4af37] mb-4">
                                <?php echo number_format($prod['price']); ?> تومان
                            </div>
                            <p
                                class="text-gray-400 text-sm leading-relaxed mb-6 border-b border-[#374151] pb-4 min-h-[8rem] overflow-y-auto">
                                <?php echo $prod['description']; ?></p>

                            <?php if (!empty($colors_parsed)): ?>
                                <div class="mb-5">
                                    <label class="text-gray-300 text-xs font-bold mb-2 block">رنگ مورد نظر:</label>
                                    <div class="flex flex-wrap gap-3">
                                        <?php foreach ($colors_parsed as $idx => $cp): ?>
                                            <label class="relative group" title="<?php echo $cp['name']; ?>">
                                                <input type="radio" name="selected_color"
                                                    value="<?php echo $cp['name']; ?>:<?php echo $cp['hex']; ?>"
                                                    class="color-radio hidden" <?php echo $idx == 0 ? 'checked' : ''; ?>
                                                    onchange="updateFactor()">
                                                <div class="color-option" style="background-color: <?php echo $cp['hex']; ?>;">
                                                </div>
                                                <span
                                                    class="absolute -bottom-7 left-1/2 -translate-x-1/2 text-[10px] bg-slate-900 text-white px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap pointer-events-none z-20 border border-slate-700"><?php echo $cp['name']; ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <input type="hidden" name="selected_color" value="استاندارد:#000">
                            <?php endif; ?>

                            <div
                                class="flex items-center justify-between bg-[#111827] p-3 rounded-xl border border-[#374151]">
                                <span class="text-gray-400 text-sm">تعداد سفارش:</span>
                                <div class="flex items-center gap-3">
                                    <button type="button" class="qty-btn" onclick="changeQty(-1)">-</button>
                                    <span id="qty-display"
                                        class="text-xl font-bold text-slate-900 w-8 text-center">1</span>
                                    <button type="button" class="qty-btn" onclick="changeQty(1)">+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="product-panel p-5">
                    <h3 class="text-white font-bold mb-3 text-sm">اقلام جانبی (ست پیشنهادی)</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <?php while ($item = $result_others->fetch_assoc()): ?>
                            <div
                                class="flex items-center justify-between bg-[#f8fafc] p-2 rounded-lg border border-slate-200">
                                <div class="flex items-center gap-2">
                                    <img src="<?php echo $item['image']; ?>" class="w-10 h-10 rounded object-cover">
                                    <div>
                                        <div class="text-xs text-slate-700 font-bold"><?php echo $item['name']; ?></div>
                                        <div class="text-[10px] text-[#ef4056] font-bold">
                                            <?php echo number_format($item['price']); ?> تومان</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" onclick="updateExtra('<?php echo $item['id']; ?>', -1)"
                                        class="text-slate-400 hover:text-[#ef4056] text-lg font-bold">-</button>
                                    <input type="text" readonly id="extra-qty-<?php echo $item['id']; ?>"
                                        data-name="<?php echo $item['name']; ?>" data-price="<?php echo $item['price']; ?>"
                                        value="0" class="w-4 text-center bg-transparent text-slate-700 text-xs font-mono">
                                    <button type="button" onclick="updateExtra('<?php echo $item['id']; ?>', 1)"
                                        class="text-[#ef4056] font-bold text-lg">+</button>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5">
                <div class="summary-card p-6 sticky top-24">
                    <h2 class="text-lg font-black text-white mb-4 border-b border-[#374151] pb-2">🧾 فاکتور نهایی
                    </h2>

                    <div class="space-y-2 mb-4 text-sm" id="invoice-list"></div>

                    <div class="border-t border-[#374151] pt-3 space-y-2">
                        <div class="flex justify-between text-gray-400 text-xs">
                            <span>هزینه ارسال (پست پیشتاز)</span>
                            <span><?php echo number_format($shipping_cost); ?> تومان</span>
                        </div>
                        <div class="flex justify-between text-white font-black text-lg">
                            <span>مبلغ کل</span>
                            <span class="text-[#d4af37]"><span id="total-price">0</span> تومان</span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="text-xs text-[#d4af37] block mb-1">آدرس دقیق پستی <span
                                class="text-red-500">*</span></label>
                        <textarea id="address-input" name="address" rows="3"
                            class="w-full bg-[#111827] border border-[#374151] text-gray-200 rounded-xl p-3 focus:border-[#d4af37] focus:outline-none text-sm transition"
                            placeholder="استان، شهر، خیابان، کوچه، پلاک..."></textarea>
                        <div id="ai-status-box"></div>
                    </div>

                    <button type="button" onclick="checkAddressAndPay()" class="submit-btn" id="pay-btn">
                        بررسی آدرس و پرداخت
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    // تنظیمات محصول
    const mainProd = {
        id: "<?php echo $prod['id']; ?>",
        name: "<?php echo $prod['name']; ?>",
        price: <?php echo $prod['price']; ?>
    };
    const shippingCost = <?php echo $shipping_cost; ?>;

    // کلید API جدید شما
    const SAMBANOVA_API_KEY = "6add4815-fd20-484b-b785-b809533a0874";

    let currentQty = 1;
    let extras = {};

    // 1. تغییر تصویر
    function changeImage(src, el) {
        document.getElementById('main-img').src = src;
        document.querySelectorAll('.thumb-img').forEach(t => t.classList.remove('active'));
        el.classList.add('active');
    }

    // 2. تغییر تعداد
    function changeQty(n) {
        let v = currentQty + n;
        if (v < 1) v = 1;
        currentQty = v;
        document.getElementById('qty-display').innerText = v;
        updateFactor();
    }

    // 3. تغییر تعداد جانبی
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

    // 4. آپدیت فاکتور
    function updateFactor() {
        const list = document.getElementById('invoice-list');
        list.innerHTML = '';
        let total = 0;

        // محصول اصلی
        const colorInput = document.querySelector('input[name="selected_color"]:checked');
        const [cName, cHex] = colorInput.value.split(':');
        const mainTotal = mainProd.price * currentQty;
        total += mainTotal;

        list.innerHTML += `
        <div class="flex justify-between items-center bg-[#111827] p-2 rounded border border-gray-700">
            <div>
                <div class="text-white font-bold">${mainProd.name}</div>
                <div class="text-xs text-gray-500 flex items-center gap-1">
                    <span style="width:8px;height:8px;border-radius:50%;background:${cHex};display:inline-block;"></span> ${cName}
                </div>
            </div>
            <div class="text-right">
                <div class="text-white text-xs">x${currentQty}</div>
                <div class="text-[#d4af37] text-xs">${mainTotal.toLocaleString()}</div>
            </div>
        </div>`;

        // جانبی‌ها
        for (const [id, item] of Object.entries(extras)) {
            const itemTotal = item.price * item.qty;
            total += itemTotal;
            list.innerHTML += `
            <div class="flex justify-between items-center text-gray-400 px-2">
                <span>+ ${item.name}</span>
                <span>x${item.qty} (${itemTotal.toLocaleString()})</span>
            </div>`;
        }

        // جمع کل
        const finalAmount = total + shippingCost;
        document.getElementById('total-price').innerText = finalAmount.toLocaleString();
        document.getElementById('final-total-input').value = finalAmount;

        // ساخت JSON
        let finalData = [];
        finalData.push({ id: mainProd.id, name: mainProd.name, color: cName, qty: currentQty, price: mainProd.price });
        for (const [id, item] of Object.entries(extras)) {
            finalData.push({ id: id, name: item.name + ' (ست)', color: '-', qty: item.qty, price: item.price });
        }
        document.getElementById('final-json').value = JSON.stringify(finalData);
    }

    // 5. هوش مصنوعی Sambanova
    async function checkAddressAndPay() {
        const address = document.getElementById('address-input').value.trim();
        const box = document.getElementById('ai-status-box');
        const btn = document.getElementById('pay-btn');

        if (address.length < 10) {
            alert("لطفاً آدرس کامل را وارد کنید (حداقل ۱۰ کاراکتر).");
            return;
        }

        btn.disabled = true;
        btn.innerText = "درحال استعلام از هوش مصنوعی...";
        box.style.display = "block";
        box.className = "ai-loading";
        box.innerHTML = "🤖 در حال بررسی صحت آدرس با هوش مصنوعی...";

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
                // تلاش برای استخراج JSON تمیز
                const jsonMatch = rawContent.match(/\{[\s\S]*\}/);
                const result = jsonMatch ? JSON.parse(jsonMatch[0]) : { valid: true, message: "آدرس قابل قبول به نظر می‌رسد." }; // Fallback if JSON fails

                if (result.valid) {
                    box.className = "ai-success";
                    box.innerHTML = `<b>✅ تایید شد!</b><br>${result.message}`;

                    setTimeout(() => {
                        btn.innerText = "انتقال به درگاه...";
                        document.getElementById('orderForm').submit();
                    }, 1000);
                } else {
                    box.className = "ai-error";
                    box.innerHTML = `<b>❌ خطا در آدرس</b><br>${result.message}`;
                    btn.disabled = false;
                    btn.innerText = "بررسی مجدد و پرداخت";
                }
            } else {
                throw new Error("Invalid API Response");
            }

        } catch (error) {
            console.warn(error);
            // در صورت خطا در API، سخت نگیریم و رد کنیم
            box.className = "text-yellow-500";
            box.innerHTML = "⚠️ سیستم هوشمند موقتاً در دسترس نیست، سفارش ثبت می‌شود.";
            setTimeout(() => document.getElementById('orderForm').submit(), 1500);
        }
    }

    updateFactor();
</script>