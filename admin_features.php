<?php
// ============================================================
//  admin_features.php — نسخه ۲.۰
//  فقط دو بخش: دسته‌بندی‌ها (افزودن/ویرایش/حذف) و تخفیف‌ها (افزودن/حذف)
//  بخش‌های محصولات (تکراری با admin.php)، نظرات و خبرنامه حذف شدند
//  چون هیچ فرم ثبت نظر یا عضویت خبرنامه‌ای در فروشگاه وجود نداشت.
// ============================================================

$feature_message = null; // ['type' => 'ok'|'err', 'text' => '...']

// ===== حذف دسته‌بندی =====
if (isset($_GET['delete_cat_id'])) {
    $cid = intval($_GET['delete_cat_id']);
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $cid);
    if ($stmt->execute()) {
        // محصولاتی که این دسته رو داشتن، category_id شون به NULL برمیگرده (طبق ON DELETE SET NULL)
        echo "<script>window.location.href='admin.php?tab=categories';</script>";
        exit();
    }
}

// ===== حذف تخفیف =====
if (isset($_GET['delete_discount_id'])) {
    $did = intval($_GET['delete_discount_id']);
    $stmt = $conn->prepare("DELETE FROM discounts WHERE id = ?");
    $stmt->bind_param("i", $did);
    if ($stmt->execute()) {
        echo "<script>window.location.href='admin.php?tab=discounts';</script>";
        exit();
    }
}

// ===== بارگذاری دسته‌بندی برای حالت ویرایش =====
$cat_edit_mode = false;
$cat_edit_id = 0;
$cat_name_val = ""; $cat_icon_val = "📦"; $cat_desc_val = "";
if (isset($_GET['edit_cat_id'])) {
    $cat_edit_mode = true;
    $cat_edit_id = intval($_GET['edit_cat_id']);
    $cres = $conn->query("SELECT * FROM categories WHERE id = $cat_edit_id");
    if ($cres && $cres->num_rows > 0) {
        $crow = $cres->fetch_assoc();
        $cat_name_val = $crow['name'];
        $cat_icon_val = $crow['icon'] ?: '📦';
        $cat_desc_val = $crow['description'];
    }
}

// ===== پردازش فرم‌ها =====
if (isset($_POST['manage_feature'])) {
    $feature = $_POST['manage_feature'];

    // افزودن دسته‌بندی
    if ($feature === 'add_category') {
        $name = cleanInput($_POST['cat_name']);
        $icon = cleanInput($_POST['cat_icon'] ?? '📦');
        $desc = cleanInput($_POST['cat_desc']);
        $slug = strtolower(str_replace(' ', '-', $name));

        $stmt = $conn->prepare("INSERT INTO categories (name, slug, description, icon) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $slug, $desc, $icon);
        if ($stmt->execute()) {
            $feature_message = ['type' => 'ok', 'text' => '✓ دسته‌بندی اضافه شد'];
        } else {
            $feature_message = ['type' => 'err', 'text' => 'خطا: این نام قبلاً استفاده شده یا نامعتبر است'];
        }
    }

    // ویرایش دسته‌بندی
    elseif ($feature === 'update_category') {
        $cid = intval($_POST['cat_id']);
        $name = cleanInput($_POST['cat_name']);
        $icon = cleanInput($_POST['cat_icon'] ?? '📦');
        $desc = cleanInput($_POST['cat_desc']);
        $slug = strtolower(str_replace(' ', '-', $name));

        $stmt = $conn->prepare("UPDATE categories SET name=?, slug=?, description=?, icon=? WHERE id=?");
        $stmt->bind_param("ssssi", $name, $slug, $desc, $icon, $cid);
        if ($stmt->execute()) {
            echo "<script>window.location.href='admin.php?tab=categories';</script>";
            exit();
        } else {
            $feature_message = ['type' => 'err', 'text' => 'خطا در ویرایش دسته‌بندی'];
        }
    }

    // افزودن تخفیف
    elseif ($feature === 'add_discount') {
        $pid = intval($_POST['product_id']);
        $percent = intval($_POST['discount_percent']);
        $desc = cleanInput($_POST['discount_desc']);

        $stmt = $conn->prepare("INSERT INTO discounts (product_id, percentage, description) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $pid, $percent, $desc);
        if ($stmt->execute()) {
            // فعال کردن on_sale برای این محصول تا در فروشگاه نشان تخفیف نمایش داده بشه
            $conn->query("UPDATE products SET on_sale = 1 WHERE id = " . $pid);
            $feature_message = ['type' => 'ok', 'text' => '✓ تخفیف اضافه شد'];
        } else {
            $feature_message = ['type' => 'err', 'text' => 'خطا در افزودن تخفیف'];
        }
    }
}
?>

<!-- ================= CATEGORIES ================= -->
<div class="admin-section <?php echo $active_tab==='categories'?'active':''; ?>" id="section-categories">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1">
            <div class="panel">
                <div class="panel-title"><?php echo $cat_edit_mode ? '✏️ ویرایش دسته‌بندی' : '➕ افزودن دسته‌بندی'; ?></div>

                <?php if ($feature_message): ?>
                    <div class="feature-alert <?php echo $feature_message['type']==='ok'?'ok':'err'; ?>"><?php echo $feature_message['text']; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="manage_feature" value="<?php echo $cat_edit_mode ? 'update_category' : 'add_category'; ?>">
                    <?php if ($cat_edit_mode): ?>
                        <input type="hidden" name="cat_id" value="<?php echo $cat_edit_id; ?>">
                    <?php endif; ?>
                    <label class="text-gray-400 text-xs">نام دسته‌بندی</label>
                    <input type="text" name="cat_name" value="<?php echo htmlspecialchars($cat_name_val); ?>" class="admin-input" required>

                    <label class="text-gray-400 text-xs block mt-3">آیکون (اموجی)</label>
                    <input type="text" name="cat_icon" value="<?php echo htmlspecialchars($cat_icon_val); ?>" class="admin-input">

                    <label class="text-gray-400 text-xs block mt-3">توضیحات</label>
                    <textarea name="cat_desc" rows="2" class="admin-input"><?php echo htmlspecialchars($cat_desc_val); ?></textarea>

                    <button type="submit" class="admin-btn btn-gold"><?php echo $cat_edit_mode ? '💾 ذخیره تغییرات' : '✓ افزودن دسته‌بندی'; ?></button>
                    <?php if ($cat_edit_mode): ?>
                        <a href="admin.php?tab=categories" class="block text-center text-gray-500 text-xs mt-3">انصراف</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="panel">
                <div class="panel-title">📂 دسته‌بندی‌های موجود</div>
                <?php
                $cats = $conn->query("SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id=c.id) AS product_count FROM categories c ORDER BY c.name ASC");
                if ($cats->num_rows > 0):
                    while($cat = $cats->fetch_assoc()):
                ?>
                    <div class="cat-row">
                        <div>
                            <span style="font-size:16px;"><?php echo htmlspecialchars($cat['icon']); ?></span>
                            <strong class="text-white mr-1"><?php echo htmlspecialchars($cat['name']); ?></strong>
                            <span class="text-gray-500 text-xs mr-2">(<?php echo $cat['product_count']; ?> محصول)</span>
                        </div>
                        <div class="flex gap-2">
                            <a href="admin.php?tab=categories&edit_cat_id=<?php echo $cat['id']; ?>" class="bg-blue-600 text-white text-[10px] px-3 py-1.5 rounded">ویرایش</a>
                            <a href="admin.php?tab=categories&delete_cat_id=<?php echo $cat['id']; ?>"
                               onclick="return confirm('این دسته‌بندی حذف بشه؟ محصولات این دسته بدون‌دسته‌بندی میمونن (حذف نمیشن).')"
                               class="bg-red-600 text-white text-[10px] px-3 py-1.5 rounded">حذف</a>
                        </div>
                    </div>
                <?php
                    endwhile;
                else:
                    echo '<p class="text-gray-500 text-sm">هنوز دسته‌بندی‌ای اضافه نکردید.</p>';
                endif;
                ?>
            </div>
        </div>
    </div>
</div>

<!-- ================= DISCOUNTS ================= -->
<div class="admin-section <?php echo $active_tab==='discounts'?'active':''; ?>" id="section-discounts">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1">
            <div class="panel">
                <div class="panel-title">🔥 افزودن تخفیف</div>
                <?php if ($feature_message && in_array($_POST['manage_feature'] ?? '', ['add_discount'])): ?>
                    <div class="feature-alert <?php echo $feature_message['type']==='ok'?'ok':'err'; ?>"><?php echo $feature_message['text']; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="manage_feature" value="add_discount">
                    <label class="text-gray-400 text-xs">محصول</label>
                    <select name="product_id" class="admin-input" required>
                        <option value="">انتخاب محصول</option>
                        <?php
                        $prods = $conn->query("SELECT id, name FROM products ORDER BY name ASC");
                        while($prod = $prods->fetch_assoc()) {
                            echo "<option value='{$prod['id']}'>" . htmlspecialchars($prod['name']) . "</option>";
                        }
                        ?>
                    </select>
                    <label class="text-gray-400 text-xs block mt-3">درصد تخفیف</label>
                    <input type="number" name="discount_percent" min="1" max="99" class="admin-input" required>
                    <label class="text-gray-400 text-xs block mt-3">توضیح (اختیاری)</label>
                    <input type="text" name="discount_desc" class="admin-input" placeholder="مثلاً: حراج تابستانه">
                    <button type="submit" class="admin-btn" style="background:var(--danger); color:#fff;">✓ افزودن تخفیف</button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="panel">
                <div class="panel-title">📋 تخفیف‌های فعال</div>
                <?php
                $discounts = $conn->query("SELECT d.*, p.name FROM discounts d JOIN products p ON d.product_id = p.id WHERE d.active = 1 ORDER BY d.created_at DESC");
                if ($discounts->num_rows > 0):
                    while($disc = $discounts->fetch_assoc()):
                ?>
                    <div class="disc-row">
                        <span class="text-white"><strong><?php echo htmlspecialchars($disc['name']); ?></strong> — <span style="color:var(--gold);"><?php echo $disc['percentage']; ?>% تخفیف</span>
                            <?php if(!empty($disc['description'])): ?><span class="text-gray-500 text-xs"> (<?php echo htmlspecialchars($disc['description']); ?>)</span><?php endif; ?>
                        </span>
                        <a href="admin.php?tab=discounts&delete_discount_id=<?php echo $disc['id']; ?>"
                           onclick="return confirm('این تخفیف حذف بشه؟')"
                           class="bg-red-600 text-white text-[10px] px-3 py-1.5 rounded">حذف</a>
                    </div>
                <?php
                    endwhile;
                else:
                    echo '<p class="text-gray-500 text-sm">تخفیف فعالی وجود نداره.</p>';
                endif;
                ?>
            </div>
        </div>
    </div>
</div>
