<?php
session_start();
include 'db.php';

// امنیت: باید لاگین باشد و is_admin = 1 باشد (هم‌راستا با admin.php)
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$adminCheck = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$adminCheck || intval($adminCheck['is_admin']) !== 1) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/3159/3159614.png" type="image/png">
    <title>پشتیبانی بوتیک | مدیریت</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font/dist/font-face.css" rel="stylesheet"/>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'shop-gold': '#d4af37',
                        'shop-dark': '#111827',
                        'shop-panel': '#1f2937',
                        'shop-accent': '#6366f1',
                    },
                    fontFamily: {
                        'vazir': ['Vazir', 'sans-serif']
                    }
                }
            }
        }
    </script>

    <style>
        :root {
            --shop-gold: #d4af37;
            --shop-gold-light: #f0d375;
            --shop-dark: #111827;
            --shop-panel: #1f2937;
            --shop-border: #2c3644;
            --shop-accent: #6366f1;
        }

        html, body {
            height: 100%;
            overflow: hidden;
            background-color: var(--shop-dark);
            font-family: 'Vazir', sans-serif;
        }

        * { box-sizing: border-box; }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes bubbleIn { from { opacity: 0; transform: translateY(6px) scale(0.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
        @keyframes spin { to { transform: rotate(360deg); } }

        .custom-scroll::-webkit-scrollbar { width: 5px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #374151; border-radius: 10px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }

        .chat-pattern {
            background-color: #0b1220;
            background-image:
                radial-gradient(rgba(212,175,55,0.05) 1px, transparent 1px),
                radial-gradient(rgba(99,102,241,0.04) 1px, transparent 1px);
            background-size: 28px 28px, 46px 46px;
            background-position: 0 0, 14px 14px;
        }

        #sidebar-users { background: linear-gradient(180deg, #1c2634 0%, #161f2b 100%); }

        .search-box {
            background: rgba(0,0,0,0.25);
            border: 1px solid var(--shop-border);
            border-radius: 12px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .search-box:focus-within {
            border-color: var(--shop-gold);
            box-shadow: 0 0 0 3px rgba(212,175,55,0.12);
        }

        .user-card {
            position: relative;
            display: flex; align-items: center; gap: 10px;
            padding: 10px; border-radius: 14px; cursor: pointer;
            border-right: 3px solid transparent;
            transition: background 0.2s ease, border-color 0.2s ease;
            animation: slideUp 0.25s ease both;
        }
        .user-card:hover { background: rgba(255,255,255,0.04); }
        .user-card.active {
            background: rgba(212,175,55,0.08);
            border-right-color: var(--shop-gold);
        }
        .user-avatar-wrap { position: relative; flex-shrink: 0; }
        .user-avatar {
            width: 46px; height: 46px; border-radius: 50%; object-fit: cover;
            border: 1.5px solid var(--shop-border);
        }
        .unread-badge {
            background: linear-gradient(135deg, var(--shop-gold-light), var(--shop-gold));
            color: #111827; font-size: 10px; font-weight: 800;
            min-width: 20px; height: 20px; padding: 0 6px;
            border-radius: 999px; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 8px rgba(212,175,55,0.35);
        }

        #chat-header { background: rgba(31,41,55,0.9); backdrop-filter: blur(14px); }

        .icon-btn {
            width: 38px; height: 38px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,0.04); border: 1px solid var(--shop-border);
            color: #cbd5e1; cursor: pointer; transition: all 0.2s ease;
        }
        .icon-btn:hover { background: rgba(212,175,55,0.12); border-color: rgba(212,175,55,0.4); color: var(--shop-gold-light); }
        .icon-btn:active { transform: scale(0.92); }
        .icon-btn.spinning svg { animation: spin 0.7s linear infinite; }

        .msg-row { display: flex; margin-bottom: 4px; animation: bubbleIn 0.25s ease both; }
        .msg-row.grouped { margin-bottom: 2px; }

        .msg-bubble {
            max-width: 78%;
            padding: 10px 14px;
            border-radius: 16px;
            font-size: 0.9rem;
            line-height: 1.6;
            word-wrap: break-word;
            white-space: pre-wrap;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }

        .msg-user .msg-bubble {
            background: #263244;
            color: #e5e7eb;
            border-bottom-left-radius: 4px;
        }
        .msg-admin .msg-bubble {
            background: linear-gradient(135deg, #4f46e5, #4338ca);
            color: #fff;
            border-bottom-right-radius: 4px;
        }

        .msg-time {
            font-size: 0.62rem; opacity: 0.6; margin-top: 4px; display: block;
        }
        .msg-user .msg-time { text-align: left; }
        .msg-admin .msg-time { text-align: right; }

        #input-area {
            background: linear-gradient(180deg, transparent, rgba(15,23,42,0.9) 20%);
        }
        .composer {
            background: var(--shop-panel);
            border: 1px solid var(--shop-border);
            border-radius: 18px;
            display: flex; align-items: center; gap: 8px;
            padding: 6px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .composer:focus-within {
            border-color: var(--shop-gold);
            box-shadow: 0 4px 20px rgba(212,175,55,0.15);
        }
        .composer input {
            flex: 1; background: transparent; border: none; outline: none;
            color: #fff; font-size: 0.9rem; padding: 10px 8px;
        }
        .send-btn {
            width: 42px; height: 42px; border-radius: 14px; flex-shrink: 0;
            background: linear-gradient(135deg, var(--shop-gold-light), var(--shop-gold));
            display: flex; align-items: center; justify-content: center;
            border: none; cursor: pointer; color: #111827;
            transition: all 0.2s ease;
        }
        .send-btn:hover:not(:disabled) { transform: scale(1.06); box-shadow: 0 6px 18px rgba(212,175,55,0.35); }
        .send-btn:active:not(:disabled) { transform: scale(0.94); }
        .send-btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .send-btn.loading svg { animation: spin 0.7s linear infinite; }

        @media (max-width: 768px) {
            #input-area {
                position: fixed; bottom: 0; left: 0; right: 0; z-index: 50;
                padding-bottom: env(safe-area-inset-bottom);
            }
            #chat-messages { padding-bottom: 90px !important; }
            #main-chat { height: 100vh; height: 100dvh; }
        }
    </style>
</head>
<body class="flex w-full h-full text-white">

    <aside id="sidebar-users" class="w-full md:w-80 border-l border-gray-700 flex flex-col z-20 h-full absolute md:relative">
        <div class="p-4 border-b border-gray-700 flex justify-between items-center shrink-0">
            <h2 class="font-bold text-shop-gold flex items-center gap-2 text-sm">💬 مرکز پیام مشتریان</h2>
            <div class="flex items-center gap-2">
                <button onclick="refreshUsersList()" id="users-refresh-btn" class="icon-btn" title="بارگذاری مجدد لیست مشتریان">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </button>
                <a href="admin.php" class="text-xs bg-gray-700/70 hover:bg-gray-600 px-3 py-1.5 rounded-lg transition">خروج</a>
            </div>
        </div>

        <div class="px-3 pt-3 pb-2 shrink-0">
            <div class="search-box flex items-center gap-2 px-3 py-2">
                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" id="search-input" oninput="filterUsers()" placeholder="جستجوی مشتری..." class="bg-transparent outline-none text-sm w-full text-gray-200 placeholder-gray-500">
            </div>
        </div>

        <div id="users-list" class="flex-1 overflow-y-auto custom-scroll px-2 pb-2 space-y-1">
            <div class="text-center text-gray-500 mt-10 text-sm">در حال بارگذاری...</div>
        </div>
    </aside>

    <main id="main-chat" class="flex-1 flex flex-col chat-pattern relative w-full h-full hidden md:flex z-30">

        <div id="chat-header" class="h-16 px-3 border-b border-gray-700 flex items-center gap-3 shrink-0 sticky top-0 z-40">
            <button onclick="toggleView('list')" class="md:hidden icon-btn">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
            <img id="header-img" src="" class="w-10 h-10 rounded-full border border-gray-600 bg-gray-800 object-cover">
            <div class="flex-1 min-w-0">
                <h3 id="header-name" class="font-bold truncate text-sm">انتخاب کنید</h3>
                <span id="header-status" class="text-xs text-gray-400">یک مشتری را انتخاب کنید</span>
            </div>
            <button onclick="refreshCurrentChat()" id="chat-refresh-btn" class="icon-btn" title="بارگذاری مجدد این گفتگو">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </button>
        </div>

        <div id="chat-messages" class="flex-1 overflow-y-auto custom-scroll p-4 space-y-1 pb-24 md:pb-4">
            <div class="h-full flex flex-col items-center justify-center text-gray-500 opacity-50 gap-2">
                <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <p class="text-sm">یک مشتری را جهت گفتگو انتخاب کنید</p>
            </div>
        </div>

        <div id="input-area" class="p-3 shrink-0 hidden">
            <form onsubmit="sendAdminMessage(event)" class="composer max-w-4xl mx-auto w-full">
                <input type="text" id="msg-input" placeholder="پیام خود را بنویسید..." autocomplete="off">
                <button type="submit" class="send-btn" id="send-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                </button>
            </form>
        </div>
    </main>

<script>
    let currentUserId = null;
    let lastMsgId = 0;
    let lastSender = null;
    let allUsers = [];

    const sidebar = document.getElementById('sidebar-users');
    const mainChat = document.getElementById('main-chat');
    const inputArea = document.getElementById('input-area');

    function timeAgo(dateStr) {
        if (!dateStr) return '';
        const then = new Date(dateStr.replace(' ', 'T'));
        const diffMin = Math.floor((Date.now() - then.getTime()) / 60000);
        if (isNaN(diffMin)) return '';
        if (diffMin < 1) return 'همین الان';
        if (diffMin < 60) return diffMin + ' دقیقه پیش';
        if (diffMin < 1440) return Math.floor(diffMin / 60) + ' ساعت پیش';
        return Math.floor(diffMin / 1440) + ' روز پیش';
    }

    function toggleView(view) {
        if (window.innerWidth >= 768) return;

        if (view === 'chat') {
            sidebar.classList.add('hidden');
            mainChat.classList.remove('hidden');
            mainChat.classList.add('flex');
        } else {
            sidebar.classList.remove('hidden');
            mainChat.classList.add('hidden');
            mainChat.classList.remove('flex');
            currentUserId = null;
        }
    }

    function loadUsers() {
        const fd = new FormData();
        fd.append('action', 'get_users_list');

        fetch('api_chat.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(users => {
            allUsers = Array.isArray(users) ? users : [];
            renderUsers(allUsers);
        })
        .catch(err => console.error('loadUsers error:', err));
    }

    function refreshUsersList() {
        const btn = document.getElementById('users-refresh-btn');
        btn.classList.add('spinning');
        loadUsers();
        setTimeout(() => btn.classList.remove('spinning'), 600);
    }

    // ===== رندر لیست مشتریان: بدون onclick، فقط با data-* + createElement =====
    // (این تغییر همان فیکس مشکل «باز نشدن چت با کلیک» است)
    function renderUsers(users) {
        const list = document.getElementById('users-list');

        if (users.length === 0) {
            list.innerHTML = '<div class="text-center text-gray-500 mt-10 text-xs">هنوز مکالمه‌ای ثبت نشده</div>';
            return;
        }

        list.innerHTML = '';
        users.forEach(u => {
            const img = u.profile_pic && u.profile_pic !== 'null' ? u.profile_pic : 'https://cdn.jsdelivr.net/gh/microsoft/fluentui-emoji@latest/assets/Person/3D/person_3d.png';
            const activeClass = (currentUserId == u.id) ? 'active' : '';
            const preview = u.last_message ? u.last_message.slice(0, 40) : 'هنوز پیامی نیست';
            const time = timeAgo(u.last_message_time);

            const card = document.createElement('div');
            card.className = `user-card ${activeClass}`;
            card.dataset.userId = u.id;
            card.dataset.userName = u.name;
            card.dataset.userImg = img;

            const avatarWrap = document.createElement('div');
            avatarWrap.className = 'user-avatar-wrap';
            const avatarImg = document.createElement('img');
            avatarImg.className = 'user-avatar';
            avatarImg.src = img;
            avatarWrap.appendChild(avatarImg);

            const infoWrap = document.createElement('div');
            infoWrap.className = 'flex-1 min-w-0';

            const topRow = document.createElement('div');
            topRow.className = 'flex justify-between items-center gap-2';
            const nameEl = document.createElement('h4');
            nameEl.className = 'font-bold text-gray-200 text-sm truncate';
            nameEl.textContent = u.name;
            const timeEl = document.createElement('span');
            timeEl.className = 'text-[10px] text-gray-500 shrink-0';
            timeEl.textContent = time;
            topRow.appendChild(nameEl);
            topRow.appendChild(timeEl);

            const bottomRow = document.createElement('div');
            bottomRow.className = 'flex justify-between items-center gap-2 mt-0.5';
            const previewEl = document.createElement('p');
            previewEl.className = 'text-xs text-gray-400 truncate';
            previewEl.textContent = preview;
            bottomRow.appendChild(previewEl);

            if (u.unread > 0) {
                const badge = document.createElement('span');
                badge.className = 'unread-badge';
                badge.textContent = u.unread;
                bottomRow.appendChild(badge);
            }

            infoWrap.appendChild(topRow);
            infoWrap.appendChild(bottomRow);

            card.appendChild(avatarWrap);
            card.appendChild(infoWrap);
            list.appendChild(card);
        });
    }

    // یک بار روی کل لیست کلیک‌ها را می‌گیریم (event delegation)
    document.getElementById('users-list').addEventListener('click', function(e) {
        const card = e.target.closest('.user-card');
        if (!card) return;
        openChat(parseInt(card.dataset.userId), card.dataset.userName, card.dataset.userImg);
    });

    function filterUsers() {
        const q = document.getElementById('search-input').value.trim().toLowerCase();
        if (!q) { renderUsers(allUsers); return; }
        renderUsers(allUsers.filter(u => (u.name || '').toLowerCase().includes(q)));
    }

    function openChat(uid, name, img) {
        currentUserId = uid;
        document.getElementById('header-name').textContent = name;
        document.getElementById('header-img').src = img;
        document.getElementById('header-status').textContent = 'در حال بارگذاری گفتگو...';

        inputArea.classList.remove('hidden');
        toggleView('chat');

        document.getElementById('chat-messages').innerHTML = '';
        lastMsgId = 0;
        lastSender = null;
        loadMessages();
        loadUsers();
    }

    function refreshCurrentChat() {
        if (!currentUserId) return;
        const btn = document.getElementById('chat-refresh-btn');
        btn.classList.add('spinning');
        loadMessages();
        setTimeout(() => btn.classList.remove('spinning'), 600);
    }

    function loadMessages() {
        if (!currentUserId) return;

        const fd = new FormData();
        fd.append('action', 'get_conversation');
        fd.append('user_id', currentUserId);

        fetch('api_chat.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(msgs => {
            const container = document.getElementById('chat-messages');

            if (msgs.length === 0) {
                container.innerHTML = '<div class="flex flex-col items-center justify-center h-full text-gray-500 text-sm"><p>هنوز پیامی نیست.</p></div>';
                document.getElementById('header-status').textContent = 'شروع مکالمه';
                lastMsgId = 0;
                lastSender = null;
                return;
            }

            container.innerHTML = '';
            lastMsgId = 0;
            lastSender = null;

            msgs.forEach(m => {
                const isUser = m.sender === 'user';
                const rowClass = isUser ? 'justify-start msg-user' : 'justify-end msg-admin';
                const grouped = (lastSender === m.sender) ? 'grouped' : '';

                const row = document.createElement('div');
                row.className = `flex mb-3 msg-row ${rowClass} ${grouped}`;

                const bubble = document.createElement('div');
                bubble.className = 'msg-bubble';
                bubble.textContent = m.message;

                const timeSpan = document.createElement('span');
                timeSpan.className = 'msg-time';
                timeSpan.textContent = (m.sender === 'admin' ? '✓ ' : '') + m.created_at;
                bubble.appendChild(timeSpan);

                row.appendChild(bubble);
                container.appendChild(row);

                lastMsgId = parseInt(m.id);
                lastSender = m.sender;
            });

            document.getElementById('header-status').textContent = 'آخرین پیام: ' + timeAgo(msgs[msgs.length - 1].created_at);
            container.scrollTop = container.scrollHeight;
        })
        .catch(err => console.error('loadMessages error:', err));
    }

    function sendAdminMessage(e) {
        e.preventDefault();

        if (!currentUserId) {
            alert("لطفاً ابتدا یک مشتری را از لیست انتخاب کنید.");
            return;
        }

        const input = document.getElementById('msg-input');
        const sendBtn = document.getElementById('send-btn');
        const msg = input.value.trim();

        if (!msg) return;

        input.disabled = true;
        sendBtn.disabled = true;
        sendBtn.classList.add('loading');

        const fd = new FormData();
        fd.append('action', 'admin_reply');
        fd.append('user_id', currentUserId);
        fd.append('message', msg);

        fetch('api_chat.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            input.disabled = false;
            sendBtn.disabled = false;
            sendBtn.classList.remove('loading');
            input.focus();
            if (d.status === 'success') {
                input.value = '';
                loadMessages();
                loadUsers();
            } else {
                alert('خطا در ارسال پیام');
            }
        })
        .catch(err => {
            input.disabled = false;
            sendBtn.disabled = false;
            sendBtn.classList.remove('loading');
            console.error(err);
        });
    }

    loadUsers();
</script>
</body>
</html>