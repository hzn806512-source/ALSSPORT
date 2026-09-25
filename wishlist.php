<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

include 'header.php';
$user_id = $_SESSION['user_id'];
?>

<style>
    :root {
        --shop-gold: #d4af37;
        --shop-dark: #0f172a;
        --shop-panel: #1e293b;
        --glass-bg: rgba(30, 41, 59, 0.45);
        --glass-border: rgba(212, 175, 55, 0.18);
        --glass-blur: 16px;
    }

    * {
        font-family: 'Vazir', sans-serif;
    }

    body {
        background: linear-gradient(180deg, #0f172a 0%, #111827 100%);
        min-height: 100vh;
        overflow-x: hidden;
    }

    .product-card {
        cursor: pointer;
        background: var(--glass-bg);
        backdrop-filter: blur(var(--glass-blur)) saturate(160%);
        -webkit-backdrop-filter: blur(var(--glass-blur)) saturate(160%);
        border: 1px solid rgba(255, 255, 255, 0.10);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.06);
        transition: transform 0.35s cubic-bezier(.2, .8, .2, 1), box-shadow 0.35s, border-color 0.35s;
        transform-style: preserve-3d;
        will-change: transform;
        position: relative;
    }

    .product-card:hover {
        transform: translateY(-8px);
        border-color: var(--glass-border);
        box-shadow: 0 20px 48px rgba(0, 0, 0, 0.45), 0 0 24px rgba(212, 175, 55, 0.18), inset 0 1px 0 rgba(255, 255, 255, 0.12);
    }

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
        transition: opacity .4s;
        pointer-events: none;
    }

    .product-card:hover::before {
        opacity: 1;
    }

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
    }

    .product-card:hover .img-wrap::after {
        left: 130%;
    }

    .wishlist-heart {
        position: absolute;
        top: 10px;
        left: 10px;
        z-index: 5;
        width: 38px;
        height: 38px;
        border-radius: 9999px;
        background: rgba(15, 23, 42, 0.60);
        backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        cursor: pointer;
        transition: 0.25s;
        border: 1px solid rgba(255, 255, 255, 0.15);
    }

    .wishlist-heart:hover {
        background: rgba(239, 68, 68, 0.85);
        transform: scale(1.1);
    }

    .wishlist-heart.active {
        background: #ef4444;
    }

    .badge-sale {
        background: rgba(239, 68, 68, 0.92);
        color: white;
        padding: 4px 10px;
        border-radius: 9999px;
        font-size: 11px;
        font-weight: 700;
        box-shadow: 0 8px 20px rgba(239, 68, 68, 0.25);
    }

    .buy-btn {
        background: linear-gradient(135deg, #d4af37 0%, #b4932a 100%);
        color: #1a0505;
        font-weight: bold;
        border: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(212, 175, 55, 0.25);
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .buy-btn:hover {
        transform: translateY(-2px);
        filter: brightness(1.1);
        box-shadow: 0 6px 22px rgba(212, 175, 55, 0.4);
    }

    @media (max-width: 640px) {
        .product-card {
            transform: none !important;
        }
    }
</style>

<div class="h-20"></div>

<div class="container mx-auto px-4 py-8 sm:py-12">
    <div class="mb-8">
        <a href="index.php" class="text-[#d4af37] hover:text-white transition">← بازگشت</a>
        <h1 class="text-3xl sm:text-4xl font-black text-white mt-4">❤️ علاقه‌مندی‌های من</h1>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5 md:gap-6">
        <?php
        $stmt = $conn->prepare("
            SELECT p.*, d.percentage
            FROM wishlist w
            JOIN products p ON w.product_id = p.id
            LEFT JOIN discounts d ON p.id = d.product_id AND d.active = 1
            WHERE w.user_id = ?
            ORDER BY w.created_at DESC
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $img = !empty($row['image']) ? $row['image'] : 'https://via.placeholder.com/400';
                $discount = (int) ($row['percentage'] ?? 0);
                $new_price = $discount > 0 ? $row['price'] * (100 - $discount) / 100 : $row['price'];
                ?>
                <div class="product-card rounded-2xl overflow-hidden group relative">
                    <div class="wishlist-heart active" onclick="toggleWishlist(<?php echo (int) $row['id']; ?>, this)">❤️</div>

                    <div class="img-wrap aspect-[3/4] w-full overflow-hidden bg-gray-900/40 relative">
                        <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>"
                            class="w-full h-full object-cover group-hover:scale-110 transition duration-500">

                        <div class="absolute top-2 right-2 sm:top-3 sm:right-3 flex flex-col gap-1.5 items-end">
                            <?php if ($discount > 0): ?>
                                <span class="badge-sale">٪<?php echo $discount; ?> تخفیف</span>
                            <?php endif; ?>
                        </div>

                        <div
                            class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                            <a href="order.php?id=<?php echo $row['id']; ?>"
                                class="buy-btn px-4 sm:px-6 py-2 rounded-full text-sm">خرید سریع</a>
                        </div>
                    </div>

                    <div class="p-3 sm:p-4">
                        <h3 class="text-white font-bold truncate mb-1 text-sm sm:text-base"><?php echo $row['name']; ?></h3>
                        <p class="text-gray-400 text-xs line-clamp-2 mb-3"><?php echo $row['description']; ?></p>
                        <div class="flex justify-between items-center border-t border-gray-700/50 pt-3">
                            <span class="text-[#d4af37] font-bold text-sm sm:text-base"><?php echo number_format($new_price); ?>
                                <small>تومان</small></span>
                            <span class="text-green-400 text-[10px] font-bold">در لیست علاقه‌مندی</span>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo '<div class="col-span-full py-12 text-center"><p class="text-gray-400 text-lg">هیچ آیتمی در علاقه‌مندی‌ها نیست 😔</p></div>';
        }
        ?>
    </div>
</div>

<script>
    function toggleWishlist(productId, el) {
        const isActive = el.classList.contains('active');
        const action = isActive ? 'remove' : 'add';
        const fd = new FormData();
        fd.append('action', action);
        fd.append('product_id', productId);

        fetch('api/wishlist.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(d => {
                if (d.status === 'success') {
                    if (action === 'remove') {
                        el.closest('.product-card')?.remove();
                    } else {
                        el.classList.add('active');
                        el.innerText = '❤️';
                    }
                } else {
                    alert(d.message || 'خطا در ثبت علاقه‌مندی');
                }
            })
            .catch(() => alert('خطا در ارتباط با سرور'));
    }
</script>

<?php include 'assistant.php'; ?>