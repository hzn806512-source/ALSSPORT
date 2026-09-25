<?php
include 'db.php';
include 'header.php';

// این صفحه فقط برای کاربر واردشده است
if (!isset($_SESSION['user_id'])) {
    header('Location: home.php');
    exit();
}
?>

<style>
    .pg-wrap { max-width: 920px; margin: 0 auto; padding: 24px 16px 60px; }
    .pg-card { background: var(--glass-bg-strong, rgba(30,41,59,.72)); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,.1); border-radius: 22px; }
    .pg-section-title { color: #fff; font-weight: 800; font-size: 15px; display: flex; align-items: center; gap: 8px; }
    .pg-avatar-wrap { position: relative; width: 96px; height: 96px; }
    .pg-avatar-wrap img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 4px solid #1e293b; }
    .pg-chat-bubble-user { background: #374151; color: #fff; border-radius: 16px 16px 4px 16px; }
    .pg-chat-bubble-admin { background: #4f46e5; color: #fff; border-radius: 16px 16px 16px 4px; }
</style>

<div class="pg-wrap">

    <h1 class="text-2xl sm:text-3xl font-black text-white mb-8 flex items-center gap-3">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-shop-gold">
            <circle cx="12" cy="8" r="4"></circle>
            <path d="M4 21c0-4 4-6 8-6s8 2 8 6"></path>
        </svg>
        پروفایل من
    </h1>

    <div class="grid lg:grid-cols-2 gap-6">

        <!-- ============================================================
             کارت اطلاعات حساب: عکس + ویرایش نام و رمز عبور
        ============================================================ -->
        <div class="pg-card p-6 sm:p-7">
            <div class="flex items-center gap-5 mb-8">
                <div class="pg-avatar-wrap group cursor-pointer">
                    <img src="<?php echo !empty($_SESSION['user_pic']) ? $_SESSION['user_pic'] : 'https://cdn.jsdelivr.net/gh/microsoft/fluentui-emoji@latest/assets/Person/3D/person_3d.png'; ?>" id="pg-dash-img">
                    <div id="pg-dash-loader" class="absolute inset-0 bg-black/60 rounded-full hidden items-center justify-center">
                        <div class="w-6 h-6 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    </div>
                    <label for="pg-dash-upload" class="absolute bottom-0 right-0 bg-shop-gold text-black p-2 rounded-full cursor-pointer hover:scale-110 transition shadow-lg border border-[#111827]">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                        </svg>
                    </label>
                    <input type="file" id="pg-dash-upload" class="hidden" accept="image/png,image/jpeg,image/gif,image/webp" onchange="pgUploadProfile()">
                </div>
                <div>
                    <div class="text-white font-bold text-lg"><?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?></div>
                    <div class="text-gray-400 text-xs mt-1" dir="ltr"><?php echo htmlspecialchars($_SESSION['user_phone'] ?? ''); ?></div>
                    <div class="text-gray-500 text-xs mt-0.5" dir="ltr"><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></div>
                </div>
            </div>

            <div class="pg-section-title mb-4">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-shop-gold">
                    <path d="M12 20h9"></path>
                    <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"></path>
                </svg>
                ویرایش اطلاعات
            </div>

            <div class="space-y-4">
                <input type="text" id="pg-name" value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>" placeholder="نام و نام خانوادگی" class="w-full input-shop rounded-xl p-3 text-sm">

                <div class="border-t border-white/10 pt-4 mt-2">
                    <p class="text-xs text-gray-500 mb-3">برای تغییر رمز عبور، هر دو فیلد زیر را پر کنید. در غیر این صورت خالی بگذارید.</p>
                    <div class="space-y-3">
                        <input type="password" id="pg-current-pass" placeholder="رمز عبور فعلی" class="w-full input-shop rounded-xl p-3 text-sm text-left" dir="ltr">
                        <input type="password" id="pg-new-pass" placeholder="رمز عبور جدید" class="w-full input-shop rounded-xl p-3 text-sm text-left" dir="ltr">
                    </div>
                </div>

                <button onclick="submitProfileUpdate()" class="w-full btn-shop py-3 rounded-xl text-sm font-bold mt-2">ذخیره تغییرات</button>
                <p id="pg-update-msg" class="text-xs text-center hidden"></p>
            </div>

            <div class="border-t border-white/10 mt-7 pt-5 text-center">
                <a href="auth.php?logout=true" class="text-red-500/70 text-xs hover:text-red-500 font-bold tracking-wider uppercase transition">خروج از حساب</a>
            </div>
        </div>

        <!-- ============================================================
             کارت گفتگو با پشتیبانی
        ============================================================ -->
        <div class="pg-card p-0 flex flex-col overflow-hidden" style="height: 560px;">
            <div class="p-4 sm:p-5 border-b border-white/10 pg-section-title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-shop-gold">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                گفتگو با پشتیبانی
            </div>

            <div id="pg-chat-history" class="flex-1 overflow-y-auto space-y-4 p-4 sm:p-5 custom-scroll">
                <?php
                $uid = $_SESSION['user_id'];
                $msgs = $conn->query("SELECT * FROM messages WHERE user_id = " . intval($uid) . " ORDER BY created_at ASC");
                if ($msgs && $msgs->num_rows > 0) {
                    while ($m = $msgs->fetch_assoc()) {
                        $isUser = $m['sender'] == 'user';
                        $align = $isUser ? 'justify-start' : 'justify-end';
                        $bubbleClass = $isUser ? 'pg-chat-bubble-user' : 'pg-chat-bubble-admin';
                        echo "<div class='flex $align'><div class='$bubbleClass text-sm px-4 py-2.5 max-w-[85%] leading-relaxed'>" . htmlspecialchars($m['message']) . "</div></div>";
                    }
                } else {
                    echo '<div class="h-full flex flex-col items-center justify-center text-gray-600 gap-3 opacity-50"><p class="text-sm">هنوز گفتگویی نداشته‌اید</p></div>';
                }
                ?>
            </div>

            <div class="p-3 sm:p-4 bg-black/30 border-t border-white/10 flex gap-2 sm:gap-3">
                <input type="text" id="pg-chat-input" placeholder="متن پیام..." class="flex-grow bg-gray-800/60 border border-gray-600 rounded-full px-4 sm:px-5 py-2.5 text-white text-sm focus:border-[#d4af37] focus:outline-none transition">
                <button onclick="pgSendMessage()" class="bg-shop-gold text-black rounded-full w-11 h-11 flex items-center justify-center hover:scale-105 transition shadow-lg shrink-0">
                    <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" class="rotate-180"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"></path></svg>
                </button>
            </div>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const c = document.getElementById('pg-chat-history');
        if (c) c.scrollTop = c.scrollHeight;
    });

    // ---- ویرایش نام / رمز عبور ----
    function submitProfileUpdate() {
        const name = document.getElementById('pg-name').value.trim();
        const curPass = document.getElementById('pg-current-pass').value;
        const newPass = document.getElementById('pg-new-pass').value;
        const msgEl = document.getElementById('pg-update-msg');

        if (!name) return alert('نام نمی‌تواند خالی باشد');
        if ((curPass && !newPass) || (!curPass && newPass)) {
            return alert('برای تغییر رمز عبور، هر دو فیلد رمز فعلی و رمز جدید را پر کنید');
        }

        const fd = new FormData();
        fd.append('action', 'update_profile');
        fd.append('name', name);
        if (curPass && newPass) {
            fd.append('current_password', curPass);
            fd.append('new_password', newPass);
        }

        fetch('auth.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(d => {
                msgEl.classList.remove('hidden', 'text-green-400', 'text-red-400');
                msgEl.classList.add(d.status === 'success' ? 'text-green-400' : 'text-red-400');
                msgEl.textContent = d.message || (d.status === 'success' ? 'ذخیره شد' : 'خطا رخ داد');
                if (d.status === 'success') {
                    document.getElementById('pg-current-pass').value = '';
                    document.getElementById('pg-new-pass').value = '';
                }
            })
            .catch(() => alert('خطا در ارتباط با سرور'));
    }

    // ---- آپلود عکس پروفایل ----
    function pgUploadProfile() {
        const f = document.getElementById('pg-dash-upload').files[0];
        if (!f) return;
        document.getElementById('pg-dash-loader').classList.replace('hidden', 'flex');
        const fd = new FormData();
        fd.append('action', 'upload_profile');
        fd.append('profile_img', f);
        fetch('auth.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(d => {
                document.getElementById('pg-dash-loader').classList.replace('flex', 'hidden');
                if (d.status === 'success') {
                    document.getElementById('pg-dash-img').src = d.url;
                } else {
                    alert(d.message);
                }
            });
    }

    // ---- ارسال پیام به پشتیبانی ----
    function pgSendMessage() {
        const input = document.getElementById('pg-chat-input');
        const m = input.value.trim();
        if (!m) return;
        const fd = new FormData();
        fd.append('action', 'send_msg');
        fd.append('message', m);
        fetch('auth.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(d => {
                if (d.status === 'success') {
                    const c = document.getElementById('pg-chat-history');
                    const wrap = document.createElement('div');
                    wrap.className = 'flex justify-start';
                    const bubble = document.createElement('div');
                    bubble.className = 'pg-chat-bubble-user text-sm px-4 py-2.5 max-w-[85%] leading-relaxed';
                    bubble.textContent = m;
                    wrap.appendChild(bubble);
                    c.appendChild(wrap);
                    c.scrollTop = c.scrollHeight;
                    input.value = '';
                } else {
                    alert(d.message || 'خطا در ارسال پیام');
                }
            });
    }
</script>

<?php include 'assistant.php'; ?>
