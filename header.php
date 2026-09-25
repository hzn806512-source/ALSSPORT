<?php
// header.php — هدر استاندارد و کاملاً همگام آلس اسپورت

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$__is_admin_header = false;
if (isset($_SESSION['user_id'], $conn) && $conn instanceof mysqli && !$conn->connect_errno) {
    $uid = (int)$_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && ($row = $res->fetch_assoc())) {
            $__is_admin_header = (int)$row['is_admin'] === 1;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0b1120">
    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/3159/3159614.png" type="image/png">
    <title>آلس اسپورت | Als Sport</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font/dist/font-face.css" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'shop-gold': '#d4af37',
                        'shop-dark': '#0b1120',
                        'shop-panel': '#161f30'
                    },
                    fontFamily: { vazir: ['Vazir', 'sans-serif'] }
                }
            }
        };
    </script>

    <style>
        body {
            font-family: Vazir, sans-serif;
            background: #0b1120;
            color: #f3f4f6;
        }
        .nav-btn { transition: all 0.2s ease; }
        .nav-btn:hover { color: #d4af37; transform: translateY(-1px); }
        .gold-btn {
            background: linear-gradient(135deg, #f0d060, #d4af37);
            color: #111;
            font-weight: 700;
            transition: all 0.3s;
        }
        .gold-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(212, 175, 55, .3);
        }
        .mobile-menu {
            transform: translateX(100%);
            transition: transform 0.3s cubic-bezier(0.32, 0.72, 0, 1);
        }
        .mobile-menu.open { transform: translateX(0); }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="sticky top-0 z-50 bg-[#0b1120]/95 backdrop-blur-lg border-b border-white/10">
        <div class="max-w-screen-xl mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                
                <!-- Logo -->
                <a href="home.php" class="flex items-center gap-3 group">
                    <div class="w-9 h-9 bg-gradient-to-br from-[#f0d060] to-[#d4af37] rounded-xl flex items-center justify-center shadow-lg">
                        <img src="https://cdn-icons-png.flaticon.com/512/3159/3159614.png" class="w-5 h-5 brightness-0 invert" alt="آلس اسپورت">
                    </div>
                    <div>
                        <span class="font-bold text-xl tracking-tight">آلس <span class="text-[#d4af37]">اسپورت</span></span>
                    </div>
                </a>

                <!-- Desktop Navigation (مقادیر کاملاً یکنواخت و ثابت در سراسر صفحات) -->
                <nav class="hidden md:flex items-center gap-2 text-sm">
                    <a href="home.php" class="nav-btn px-4 py-2 rounded-full text-gray-300 hover:text-white">خانه</a>
                    <a href="index.php" class="nav-btn px-4 py-2 rounded-full text-gray-300 hover:text-white">محصولات</a>
                    <a href="home.php#featured" class="nav-btn px-4 py-2 rounded-full text-gray-300 hover:text-white">پیشنهاد ویژه</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="wishlist.php" class="nav-btn px-4 py-2 rounded-full text-gray-300 hover:text-white">علاقه‌مندی‌ها</a>
                    <?php endif; ?>
                    <?php if ($__is_admin_header): ?>
                        <a href="admin.php" class="nav-btn px-4 py-2 rounded-full bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold">پنل مدیریت</a>
                    <?php endif; ?>
                </nav>

                <!-- Right Side -->
                <div class="flex items-center gap-2">
                    <!-- Search -->
                    <div class="relative hidden sm:block">
                        <form action="index.php" method="GET" class="flex items-center">
                            <input type="text" name="search" 
                                   class="bg-white/5 border border-white/10 focus:border-[#d4af37] text-sm rounded-full pl-9 pr-4 py-1.5 w-64 outline-none transition-all"
                                   placeholder="جستجو در محصولات...">
                            <span class="absolute left-3 top-1/2 -mt-1.5 text-gray-400">⌕</span>
                        </form>
                    </div>

                    <!-- User / Login -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="profile.php" class="flex items-center gap-2 px-3 py-1.5 rounded-full hover:bg-white/5 transition">
                            <span class="hidden md:inline text-sm font-medium"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'حساب من'); ?></span>
                        </a>
                    <?php else: ?>
                        <button onclick="openAuthModal()" class="gold-btn text-sm px-5 py-1.5 rounded-full font-bold">
                            ورود / عضویت
                        </button>
                    <?php endif; ?>

                    <!-- Mobile Menu Button -->
                    <button id="mobileMenuBtn" class="md:hidden w-9 h-9 flex items-center justify-center text-xl" onclick="toggleMobileMenu()">
                        ☰
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Mobile Menu -->
    <div id="mobileMenu" class="mobile-menu fixed top-0 right-0 h-full w-72 bg-[#0f172a] shadow-2xl z-[999] p-6 md:hidden">
        <div class="flex justify-between items-center mb-8">
            <span class="font-bold text-lg">منو</span>
            <button onclick="toggleMobileMenu()" class="text-3xl leading-none">×</button>
        </div>
        
        <div class="flex flex-col gap-1 text-sm">
            <a href="home.php" class="py-3 px-3 rounded-xl hover:bg-white/5">خانه</a>
            <a href="index.php" class="py-3 px-3 rounded-xl hover:bg-white/5">محصولات</a>
            <a href="home.php#featured" class="py-3 px-3 rounded-xl hover:bg-white/5">پیشنهاد ویژه</a>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="wishlist.php" class="py-3 px-3 rounded-xl hover:bg-white/5">علاقه‌مندی‌ها</a>
                <a href="profile.php" class="py-3 px-3 rounded-xl hover:bg-white/5">پروفایل من</a>
            <?php endif; ?>
            
            <?php if ($__is_admin_header): ?>
                <a href="admin.php" class="py-3 px-3 rounded-xl bg-indigo-600 text-white mt-2">پنل مدیریت</a>
            <?php endif; ?>
            
            <?php if (!isset($_SESSION['user_id'])): ?>
                <button onclick="openAuthModal(); toggleMobileMenu();" class="mt-4 gold-btn w-full py-3 rounded-2xl font-bold">
                    ورود / عضویت
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Auth Modal (فرم کامل و هوشمند ورود و ثبت‌نام با هندلینگ دقیق خطاها) -->
    <div id="auth-modal" onclick="if (event.target.id === 'auth-modal') closeAuthModal()"
         class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm z-[1000] flex items-center justify-center p-4">
        <div onclick="event.stopImmediatePropagation()" 
             class="bg-[#161f30] w-full max-w-md rounded-3xl p-6 border border-white/10 shadow-2xl">
            
            <div class="flex justify-between mb-6">
                <h3 id="auth-modal-title" class="font-bold text-xl text-[#d4af37]">ورود به حساب کاربری</h3>
                <button onclick="closeAuthModal()" class="text-2xl leading-none text-gray-400 hover:text-white">×</button>
            </div>

            <!-- Login Form -->
            <div id="login-form">
                <div class="space-y-3">
                    <input id="login-email" type="email" placeholder="ایمیل" class="w-full bg-white/5 border border-white/10 px-4 py-3 rounded-2xl text-sm text-white focus:outline-none focus:border-[#d4af37]">
                    <input id="login-pass" type="password" placeholder="رمز عبور" class="w-full bg-white/5 border border-white/10 px-4 py-3 rounded-2xl text-sm text-white focus:outline-none focus:border-[#d4af37]">
                </div>
                <button onclick="performLogin()" class="mt-5 w-full gold-btn py-3 rounded-2xl font-bold shadow-lg">ورود</button>
                <p class="text-center text-xs text-gray-400 mt-4">
                    حساب ندارید؟ 
                    <span onclick="switchAuthMode('register')" class="text-[#d4af37] cursor-pointer font-bold hover:underline">ثبت‌نام کنید</span>
                </p>
            </div>

            <!-- Register Form (اصلاح‌شده و کامل جهت ثبت‌نام بدون نقص) -->
            <div id="register-form" class="hidden">
                <div class="space-y-3">
                    <input id="reg-name" placeholder="نام و نام خانوادگی" class="w-full bg-white/5 border border-white/10 px-4 py-3 rounded-2xl text-sm text-white focus:outline-none focus:border-[#d4af37]">
                    <input id="reg-email" type="email" placeholder="ایمیل معتبر" class="w-full bg-white/5 border border-white/10 px-4 py-3 rounded-2xl text-sm text-white focus:outline-none focus:border-[#d4af37]">
                    <input id="reg-phone" placeholder="شماره موبایل (مثلا 09123456789)" class="w-full bg-white/5 border border-white/10 px-4 py-3 rounded-2xl text-sm text-white focus:outline-none focus:border-[#d4af37]">
                    <input id="reg-pass" type="password" placeholder="رمز عبور (حداقل ۶ حرف)" class="w-full bg-white/5 border border-white/10 px-4 py-3 rounded-2xl text-sm text-white focus:outline-none focus:border-[#d4af37]">
                </div>
                <button onclick="performRegister()" class="mt-5 w-full gold-btn py-3 rounded-2xl font-bold shadow-lg">تکمیل ثبت‌نام</button>
                <p class="text-center text-xs text-gray-400 mt-4">
                    قبلاً ثبت‌نام کرده‌اید؟ 
                    <span onclick="switchAuthMode('login')" class="text-[#d4af37] cursor-pointer font-bold hover:underline">وارد شوید</span>
                </p>
            </div>
        </div>
    </div>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('open');
        }

        function openAuthModal() {
            document.getElementById('auth-modal').classList.remove('hidden');
            document.getElementById('auth-modal').classList.add('flex');
            switchAuthMode('login');
        }

        function closeAuthModal() {
            const modal = document.getElementById('auth-modal');
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }

        function switchAuthMode(mode) {
            const login = document.getElementById('login-form');
            const register = document.getElementById('register-form');
            const title = document.getElementById('auth-modal-title');
            
            if (mode === 'login') {
                login.classList.remove('hidden');
                register.classList.add('hidden');
                title.innerText = 'ورود به حساب کاربری';
            } else {
                login.classList.add('hidden');
                register.classList.remove('hidden');
                title.innerText = 'عضویت در آلس اسپورت';
            }
        }

        function performLogin() {
            const email = document.getElementById('login-email').value;
            const pass = document.getElementById('login-pass').value;
            
            if (!email || !pass) {
                alert('لطفاً ایمیل و رمز عبور را وارد کنید');
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'login');
            formData.append('email', email);
            formData.append('password', pass);
            
            fetch('auth.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(d => {
                    if (d.status === 'success') {
                        location.reload();
                    } else {
                        alert(d.message || 'خطا در ورود');
                    }
                })
                .catch(() => alert('خطا در برقراری ارتباط با سرور'));
        }

        function performRegister() {
            const name = document.getElementById('reg-name').value;
            const email = document.getElementById('reg-email').value;
            const phone = document.getElementById('reg-phone').value;
            const pass = document.getElementById('reg-pass').value;
            
            if (!name || !email || !phone || !pass) {
                alert('لطفاً تمام فیلدهای ثبت‌نام را پر کنید');
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'register_full');
            formData.append('name', name);
            formData.append('email', email);
            formData.append('phone', phone);
            formData.append('password', pass);
            
            fetch('api_register.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(d => {
                    if (d.status === 'success') {
                        alert(d.message);
                        location.reload();
                    } else {
                        alert(d.message || 'خطا در ثبت‌نام');
                    }
                })
                .catch(() => alert('خطا در برقراری ارتباط با سرور'));
        }

        document.addEventListener('click', function(e) {
            const menu = document.getElementById('mobileMenu');
            const btn = document.getElementById('mobileMenuBtn');
            if (menu.classList.contains('open') && !menu.contains(e.target) && !btn.contains(e.target)) {
                menu.classList.remove('open');
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('auth-modal');
                const menu = document.getElementById('mobileMenu');
                if (!modal.classList.contains('hidden')) closeAuthModal();
                if (menu.classList.contains('open')) menu.classList.remove('open');
            }
        });
    </script>
<?php
// پایان هدر
?>
