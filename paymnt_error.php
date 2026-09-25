<?php
session_start();

$error = trim((string) ($_SESSION['payment_error'] ?? ''));
unset($_SESSION['payment_error']);
$hasError = $error !== '';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>خطا در اتصال به درگاه پرداخت | بوتیک پاریس</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Vazirmatn', sans-serif;
        }

        html,
        body {
            width: 100%;
            height: 100%;
            overflow: hidden;
            background: #070707;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        .background {
            position: fixed;
            inset: 0;
            overflow: hidden;
            background: radial-gradient(circle at top, #232323 0%, #090909 45%, #000000 100%);
        }

        .background::before {
            content: '';
            position: absolute;
            left: -20%;
            top: -20%;
            width: 140%;
            height: 140%;
            background: radial-gradient(circle, #d4af3715 1px, transparent 2px);
            background-size: 90px 90px;
            animation: gridMove 30s linear infinite;
            opacity: .5;
        }

        .background::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(130deg, transparent, rgba(212, 175, 55, .03), transparent);
            animation: shineBackground 8s linear infinite;
        }

        @keyframes gridMove {
            0% {
                transform: translateY(0);
            }
            100% {
                transform: translateY(-90px);
            }
        }

        @keyframes shineBackground {
            0% {
                transform: translateX(-100%);
            }
            100% {
                transform: translateX(100%);
            }
        }

        .light {
            position: absolute;
            width: 700px;
            height: 700px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(212, 175, 55, .15), transparent 70%);
            filter: blur(40px);
            animation: lightMove 10s ease-in-out infinite;
        }

        @keyframes lightMove {
            0% {
                transform: translate(-150px, -100px);
            }
            50% {
                transform: translate(130px, 80px);
            }
            100% {
                transform: translate(-150px, -100px);
            }
        }

        .card {
            position: relative;
            width: min(92%, 760px);
            padding: 60px;
            border-radius: 34px;
            background: rgba(14, 14, 14, .58);
            backdrop-filter: blur(22px);
            border: 1px solid rgba(212, 175, 55, .20);
            box-shadow: 0 0 70px rgba(212, 175, 55, .12), inset 0 0 0 1px rgba(255, 255, 255, .03);
            overflow: hidden;
            animation: cardShow 1s cubic-bezier(.18, .89, .32, 1.28);
        }

        .card::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            height: 1px;
            width: 100%;
            background: linear-gradient(90deg, transparent, #d4af37, transparent);
        }

        .card::after {
            content: "";
            position: absolute;
            right: -200px;
            top: -200px;
            width: 420px;
            height: 420px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(212, 175, 55, .09), transparent 70%);
        }

        @keyframes cardShow {
            0% {
                opacity: 0;
                transform: translateY(60px) scale(.90);
            }
            100% {
                opacity: 1;
                transform: none;
            }
        }

        .logo-wrapper {
            width: 180px;
            height: 180px;
            margin: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: linear-gradient(180deg, rgba(212, 175, 55, .12), rgba(255, 255, 255, .01));
            border: 1px solid rgba(212, 175, 55, .25);
            box-shadow: 0 0 40px rgba(212, 175, 55, .20);
            animation: pulse 3s infinite;
        }

        @keyframes pulse {
            50% {
                transform: scale(1.05);
                box-shadow: 0 0 60px rgba(212, 175, 55, .35);
            }
        }

        .hanger {
            width: 95px;
            height: 95px;
            display: block;
        }

        .title {
            font-size: 40px;
            font-weight: 900;
            color: #e7c252;
            margin-top: 40px;
            text-align: center;
            letter-spacing: .3px;
        }

        .subtitle {
            margin-top: 25px;
            font-size: 18px;
            line-height: 2.3;
            color: #bdbdbd;
            text-align: center;
            max-width: 580px;
            margin-inline: auto;
        }

        .buttons {
            margin-top: 55px;
            display: flex;
            gap: 18px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .gold-btn {
            cursor: pointer;
            border: none;
            padding: 16px 42px;
            border-radius: 100px;
            font-size: 16px;
            font-weight: 800;
            background: linear-gradient(180deg, #f1d26b, #d4af37);
            color: #111;
            transition: .45s;
            box-shadow: 0 10px 35px rgba(212, 175, 55, .25);
        }

        .gold-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 18px 45px rgba(212, 175, 55, .35);
        }

        .border-btn {
            cursor: pointer;
            padding: 16px 42px;
            border-radius: 100px;
            font-size: 16px;
            font-weight: 700;
            background: transparent;
            border: 1px solid rgba(212, 175, 55, .45);
            color: #e7c252;
            transition: .45s;
        }

        .border-btn:hover {
            background: rgba(212, 175, 55, .08);
            transform: translateY(-5px);
        }

        .dev-error {
            margin-top: 35px;
            padding: 18px;
            border-radius: 18px;
            background: #101010;
            border: 1px dashed rgba(212, 175, 55, .35);
            font-size: 13px;
            color: #888;
            line-height: 2;
            word-break: break-word;
        }

        @media (max-width: 768px) {
            .card {
                padding: 35px 24px;
            }

            .title {
                font-size: 30px;
            }

            .subtitle {
                font-size: 15px;
                line-height: 2;
            }

            .logo-wrapper {
                width: 150px;
                height: 150px;
            }
        }
    </style>
</head>

<body>
    <div class="background"></div>
    <div class="light"></div>

    <div class="card">
        <div class="logo-wrapper">
            <svg class="hanger" viewBox="0 0 256 256" fill="none">
                <path d="M128 26 C146 26 160 40 160 58 C160 71 154 79 146 84 L128 97 V121 L216 184 C225 190 222 204 210 204 H46 C34 204 31 190 40 184 L128 121" stroke="#d4af37" stroke-width="7" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M83 166 H173" stroke="#d4af37" stroke-width="7" stroke-linecap="round" />
            </svg>
        </div>

        <h1 class="title">ارتباط با درگاه پرداخت برقرار نشد</h1>
        <p class="subtitle">
            در حال حاضر امکان اتصال به درگاه پرداخت وجود ندارد.
            این مشکل معمولاً موقتی است و ممکن است به دلیل اختلال در اینترنت یا سرویس بانک باشد.
            لطفاً چند دقیقه دیگر دوباره تلاش کنید.
        </p>

        <div class="buttons">
            <button class="gold-btn" onclick="history.back()">بازگشت به صفحه قبل</button>
            <button class="border-btn" onclick="location.reload()">تلاش مجدد</button>
        </div>

        <?php if ($hasError): ?>
            <div class="dev-error">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <script>
            const background = document.querySelector('.background');
            const card = document.querySelector('.card');
            const logoWrapper = document.querySelector('.logo-wrapper');
            const hanger = document.querySelector('.hanger');

            function injectStyles() {
                const style = document.createElement('style');
                style.innerHTML = `
                    .particle {
                        position: absolute;
                        border-radius: 50%;
                        background: #d4af37;
                        opacity: .18;
                        box-shadow: 0 0 8px rgba(212, 175, 55, .6), 0 0 18px rgba(212, 175, 55, .4);
                        animation: float infinite linear;
                    }

                    @keyframes float {
                        0% {
                            transform: translateY(80px) translateX(0) scale(0);
                            opacity: 0;
                        }
                        15% {
                            opacity: .25;
                        }
                        50% {
                            transform: translateY(-220px) translateX(25px) scale(1);
                        }
                        100% {
                            transform: translateY(-700px) translateX(-20px) scale(0);
                            opacity: 0;
                        }
                    }

                    .light-line {
                        position: absolute;
                        height: 1px;
                        width: 320px;
                        background: linear-gradient(90deg, transparent, #d4af37, transparent);
                        opacity: .12;
                        animation: moveLine linear infinite;
                    }

                    @keyframes moveLine {
                        0% {
                            transform: translateX(-350px) rotate(-25deg);
                        }
                        100% {
                            transform: translateX(calc(100vw + 350px)) rotate(-25deg);
                        }
                    }

                    .corner {
                        position: absolute;
                        width: 160px;
                        height: 160px;
                        border: 1px solid rgba(212, 175, 55, .18);
                    }

                    .corner:nth-child(1) {
                        top: 18px;
                        right: 18px;
                        border-left: none;
                        border-bottom: none;
                        border-radius: 0 30px 0 0;
                    }

                    .corner:nth-child(2) {
                        bottom: 18px;
                        left: 18px;
                        border-right: none;
                        border-top: none;
                        border-radius: 0 0 0 30px;
                    }

                    .gold-btn {
                        position: relative;
                        overflow: hidden;
                    }

                    .gold-btn::before {
                        content: '';
                        position: absolute;
                        top: 0;
                        left: -120%;
                        width: 60%;
                        height: 100%;
                        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .45), transparent);
                        transform: skewX(-25deg);
                        transition: 1s;
                    }

                    .gold-btn:hover::before {
                        left: 180%;
                    }

                    .border-btn {
                        position: relative;
                        overflow: hidden;
                    }

                    .border-btn::before {
                        content: '';
                        position: absolute;
                        inset: 0;
                        background: linear-gradient(135deg, transparent, rgba(212, 175, 55, .06), transparent);
                        opacity: 0;
                        transition: .5s;
                    }

                    .border-btn:hover::before {
                        opacity: 1;
                    }

                    .status {
                        margin-top: 35px;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        gap: 12px;
                        font-size: 14px;
                        color: #8d8d8d;
                    }

                    .status-dot {
                        width: 10px;
                        height: 10px;
                        border-radius: 50%;
                        background: #d4af37;
                        box-shadow: 0 0 12px rgba(212, 175, 55, .8);
                        animation: blink 1.6s infinite;
                    }

                    @keyframes blink {
                        50% {
                            opacity: .35;
                        }
                    }
                `;

                document.head.appendChild(style);
            }

            function createParticles(container, count) {
                for (let index = 0; index < count; index += 1) {
                    const particle = document.createElement('span');
                    const size = Math.random() * 4 + 2;

                    particle.className = 'particle';
                    particle.style.width = `${size}px`;
                    particle.style.height = `${size}px`;
                    particle.style.left = `${Math.random() * 100}%`;
                    particle.style.top = `${Math.random() * 100}%`;
                    particle.style.animationDuration = `${Math.random() * 10 + 10}s`;
                    particle.style.animationDelay = `${Math.random() * 8}s`;

                    container.appendChild(particle);
                }
            }

            function createLightLines(container, count) {
                for (let index = 0; index < count; index += 1) {
                    const line = document.createElement('div');
                    line.className = 'light-line';
                    line.style.top = `${Math.random() * 100}%`;
                    line.style.animationDuration = `${Math.random() * 8 + 10}s`;
                    line.style.animationDelay = `${Math.random() * 5}s`;

                    container.appendChild(line);
                }
            }

            function decorateCard() {
                card.insertAdjacentHTML('beforeend', `
                    <div class="status">
                        <div class="status-dot"></div>
                        <span>در حال بررسی وضعیت اتصال...</span>
                    </div>
                    <div class="corner"></div>
                    <div class="corner"></div>
                `);
            }

            function attachCardMotion() {
                document.addEventListener('mousemove', (event) => {
                    const x = (event.clientX / window.innerWidth - 0.5) * 20;
                    const y = (event.clientY / window.innerHeight - 0.5) * 20;
                    card.style.transform = `rotateY(${x * 0.25}deg) rotateX(${-y * 0.25}deg)`;
                });

                document.addEventListener('mouseleave', () => {
                    card.style.transform = '';
                });
            }

            function animateHanger() {
                let angle = 0;

                const step = () => {
                    angle += 0.02;
                    hanger.style.transform = `rotate(${Math.sin(angle) * 3}deg) translateY(${Math.sin(angle * 2) * 2}px)`;
                    requestAnimationFrame(step);
                };

                step();
            }

            function pulseLogo() {
                setInterval(() => {
                    logoWrapper.animate([
                        { boxShadow: '0 0 30px rgba(212, 175, 55, .25)' },
                        { boxShadow: '0 0 80px rgba(212, 175, 55, .55)' },
                        { boxShadow: '0 0 30px rgba(212, 175, 55, .25)' }
                    ], {
                        duration: 2500,
                        iterations: 1
                    });
                }, 2600);
            }

            function startRetryTimer() {
                let seconds = 15;
                const statusContainer = document.querySelector('.status');
                const timer = document.createElement('div');
                const statusText = document.querySelector('.status span');

                timer.style.marginTop = '18px';
                timer.style.color = '#9d9d9d';
                timer.style.fontSize = '13px';
                timer.style.textAlign = 'center';

                statusContainer.after(timer);

                const updateTimer = () => {
                    timer.innerHTML = `امکان تلاش مجدد تا <b style="color:#d4af37">${seconds}</b> ثانیه دیگر`;

                    if (seconds > 0) {
                        seconds -= 1;
                        setTimeout(updateTimer, 1000);
                    } else {
                        timer.innerHTML = 'اکنون می‌توانید دوباره تلاش کنید.';
                    }
                };

                updateTimer();
                return statusText;
            }

            function attachRippleEffects() {
                document.querySelectorAll('button').forEach((button) => {
                    button.addEventListener('click', (event) => {
                        const ripple = document.createElement('span');
                        const rect = button.getBoundingClientRect();
                        const size = Math.max(rect.width, rect.height);

                        ripple.style.position = 'absolute';
                        ripple.style.width = `${size}px`;
                        ripple.style.height = `${size}px`;
                        ripple.style.borderRadius = '50%';
                        ripple.style.left = `${event.clientX - rect.left - size / 2}px`;
                        ripple.style.top = `${event.clientY - rect.top - size / 2}px`;
                        ripple.style.background = 'rgba(255,255,255,.25)';
                        ripple.style.pointerEvents = 'none';
                        ripple.style.transform = 'scale(0)';
                        ripple.style.transition = '.7s';

                        button.appendChild(ripple);

                        requestAnimationFrame(() => {
                            ripple.style.transform = 'scale(4)';
                            ripple.style.opacity = '0';
                        });

                        setTimeout(() => ripple.remove(), 700);
                    });
                });
            }

            function updateNetworkStatus(statusText) {
                const dot = document.querySelector('.status-dot');

                if (navigator.onLine) {
                    dot.style.background = '#d4af37';
                    statusText.innerHTML = 'در حال بررسی وضعیت اتصال...';
                } else {
                    dot.style.background = '#ff4444';
                    statusText.innerHTML = 'اتصال اینترنت شما قطع شده است.';
                }
            }

            function emitSparkles(event) {
                for (let index = 0; index < 12; index += 1) {
                    const spark = document.createElement('span');
                    spark.style.position = 'fixed';
                    spark.style.left = `${event.clientX}px`;
                    spark.style.top = `${event.clientY}px`;
                    spark.style.width = '4px';
                    spark.style.height = '4px';
                    spark.style.borderRadius = '50%';
                    spark.style.background = '#d4af37';
                    spark.style.pointerEvents = 'none';
                    spark.style.zIndex = '9999';
                    document.body.appendChild(spark);

                    const x = (Math.random() - 0.5) * 220;
                    const y = (Math.random() - 0.5) * 220;

                    spark.animate([
                        { transform: 'translate(0,0) scale(1)', opacity: 1 },
                        { transform: `translate(${x}px,${y}px) scale(0)`, opacity: 0 }
                    ], {
                        duration: 900,
                        easing: 'ease-out'
                    });

                    setTimeout(() => spark.remove(), 900);
                }
            }

            function animateEntrance() {
                document.querySelector('.title').animate([
                    { opacity: 0, transform: 'translateY(40px)' },
                    { opacity: 1, transform: 'translateY(0)' }
                ], {
                    duration: 1200,
                    fill: 'forwards'
                });

                document.querySelector('.subtitle').animate([
                    { opacity: 0, transform: 'translateY(40px)' },
                    { opacity: 1, transform: 'translateY(0)' }
                ], {
                    duration: 1500,
                    delay: 300,
                    fill: 'forwards'
                });

                document.querySelector('.buttons').animate([
                    { opacity: 0, transform: 'translateY(30px)' },
                    { opacity: 1, transform: 'translateY(0)' }
                ], {
                    duration: 1700,
                    delay: 700,
                    fill: 'forwards'
                });
            }

            injectStyles();
            createParticles(background, 80);
            createLightLines(background, 8);
            decorateCard();
            attachCardMotion();
            animateHanger();
            pulseLogo();
            const statusText = startRetryTimer();
            attachRippleEffects();
            window.addEventListener('online', () => updateNetworkStatus(statusText));
            window.addEventListener('offline', () => updateNetworkStatus(statusText));
            updateNetworkStatus(statusText);
            document.body.addEventListener('click', emitSparkles);
            animateEntrance();
        </script>
    </div>
</body>
</html>
