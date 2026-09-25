<?php
session_start();
$error = $_SESSION['payment_error'] ?? '';
unset($_SESSION['payment_error']);
?><!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>خطا در اتصال به درگاه پرداخت</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
body{margin:0;background:radial-gradient(circle at top,#1b1b1b,#050505);overflow:hidden}
.bgfx{position:fixed;inset:0}
.p{position:absolute;width:3px;height:3px;border-radius:50%;background:#d4af37;opacity:.25;animation:f 12s linear infinite}
@keyframes f{from{transform:translateY(110vh) scale(0)}to{transform:translateY(-20vh) scale(2);opacity:0}}
.card{backdrop-filter:blur(16px);background:rgba(20,20,20,.72);border:1px solid rgba(212,175,55,.35);box-shadow:0 0 50px rgba(212,175,55,.15);animation:show .8s ease}
@keyframes show{from{opacity:0;transform:translateY(30px) scale(.95)}to{opacity:1;transform:none}}
.glow{animation:pulse 2s infinite}
@keyframes pulse{50%{transform:scale(1.08);filter:drop-shadow(0 0 15px #d4af37)}}
.btn{transition:.3s}
.btn:hover{transform:translateY(-3px) scale(1.03)}
</style>
</head>
<body class="text-white flex items-center justify-center min-h-screen">
<div class="bgfx">
<?php for($i=0;$i<90;$i++): ?>
<span class="p" style="left:<?=rand(0,100)?>%;animation-delay:<?=rand(0,100)/10?>s;animation-duration:<?=rand(8,18)?>s"></span>
<?php endfor; ?>
</div>

<div class="card rounded-3xl p-10 max-w-2xl mx-4 text-center">
<div class="text-7xl glow mb-6">⚠️</div>
<h1 class="text-4xl font-black text-yellow-400 mb-6">ارتباط با درگاه پرداخت برقرار نشد</h1>
<p class="text-gray-300 leading-8">
در حال حاضر امکان اتصال به درگاه پرداخت وجود ندارد.
این مشکل معمولاً موقتی است. لطفاً چند دقیقه دیگر دوباره تلاش کنید.
</p>

<?php if($error): ?>
<div class="mt-6 text-xs text-gray-500 border border-yellow-700 rounded-xl p-3">
کد خطا (حالت توسعه): <?=htmlspecialchars($error,ENT_QUOTES,'UTF-8')?>
</div>
<?php endif; ?>

<div class="flex flex-col sm:flex-row gap-4 justify-center mt-10">
<button onclick="history.back()" class="btn bg-yellow-400 text-black font-bold px-8 py-3 rounded-full">⬅ بازگشت به فروشگاه </button>
<button onclick="location.reload()" class="btn border border-yellow-400 text-yellow-300 px-8 py-3 rounded-full"> تلاش مجدد</button>
</div>
</div>
</body>
</html>