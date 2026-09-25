<?php
$file = 'C:\xampp\htdocs\ALSSPORT\home.php';
$content = file_get_contents($file);

// 1. Replace CSS block
$new_css = '
    <style>
        :root {
            --gold: #d4af37;
            --gold-light: #f0d060;
            --gold-dark: #a9821f;
            --gold-glow: rgba(212,175,55,0.35);
            --gold-soft: rgba(212,175,55,0.10);
            --gold-glass: rgba(212,175,55,0.08);
            --dark: #080e1a;
            --dark-2: #0b1120;
            --dark-3: #111b2e;
            --panel: #141f33;
            --panel-hover: #1a2640;
            --panel-glass: rgba(20,31,51,0.75);
            --border-subtle: rgba(255,255,255,0.07);
            --border-gold: rgba(212,175,55,0.25);
            --text-primary: #f1f3f5;
            --text-secondary: #9ca3af;
            --text-muted: #6b7280;
            --radius-sm: 10px;
            --radius-md: 16px;
            --radius-lg: 22px;
            --radius-xl: 28px;
            --shadow-card: 0 8px 32px rgba(0,0,0,0.3);
            --shadow-gold: 0 8px 32px rgba(212,175,55,0.15);
            --shadow-gold-lg: 0 16px 48px rgba(212,175,55,0.20);
            --transition-fast: 0.2s cubic-bezier(0.4,0,0.2,1);
            --transition-med: 0.35s cubic-bezier(0.4,0,0.2,1);
            --transition-slow: 0.55s cubic-bezier(0.4,0,0.2,1);
        }
        html { scroll-behavior: smooth; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: var(--dark); }
        ::-webkit-scrollbar-thumb { background: var(--gold-dark); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--gold); }
        ::selection { background: var(--gold); color: #111; }
        @media (prefers-reduced-motion: reduce) {
            *,*::before,*::after { animation-duration:0.01ms!important; animation-iteration-count:1!important; transition-duration:0.01ms!important; }
        }
        .section-tag { display:inline-flex; align-items:center; gap:10px; color:var(--gold); font-size:11px; font-weight:800; letter-spacing:0.18em; text-transform:uppercase; }
        .section-tag::before { content:""; width:28px; height:2px; background:linear-gradient(90deg,var(--gold),transparent); display:inline-block; border-radius:1px; }
        .section-title { font-size:clamp(1.75rem,4vw,2.85rem); font-weight:900; color:var(--text-primary); margin-top:10px; line-height:1.25; background:linear-gradient(135deg,#fff 55%,var(--gold-light)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
        .section-sub { color:var(--text-secondary); max-width:600px; margin:14px auto 0; font-size:14px; line-height:2; }
        .gold-btn { background:linear-gradient(135deg,var(--gold-light),var(--gold) 50%,var(--gold-dark)); color:#0a0a0a; font-weight:800; border-radius:var(--radius-md); transition:var(--transition-med); box-shadow:0 8px 28px var(--gold-glow); position:relative; overflow:hidden; isolation:isolate; }
        .gold-btn::before { content:""; position:absolute; inset:0; background:linear-gradient(135deg,var(--gold),var(--gold-light) 60%,var(--gold)); opacity:0; transition:opacity 0.4s; z-index:-1; border-radius:inherit; }
        .gold-btn:hover { transform:translateY(-3px) scale(1.02); box-shadow:0 14px 40px var(--gold-glow); }
        .gold-btn:hover::before { opacity:1; }
        .gold-btn:active { transform:translateY(-1px) scale(0.98); }
        .ghost-btn { border:1px solid var(--border-subtle); color:var(--text-primary); border-radius:var(--radius-md); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); background:rgba(255,255,255,0.04); transition:var(--transition-med); }
        .ghost-btn:hover { border-color:var(--gold); color:var(--gold); background:var(--gold-glass); transform:translateY(-2px); box-shadow:0 6px 20px rgba(212,175,55,0.10); }
        .hero-wrap { position:relative; min-height:98vh; display:flex; align-items:center; overflow:hidden; contain:layout; }
        .hero-wrap::before { content:""; position:absolute; inset:0; background:linear-gradient(180deg,rgba(8,14,26,0.50) 0%,rgba(8,14,26,0.92) 85%),radial-gradient(ellipse at 75% 10%,rgba(212,175,55,0.20),transparent 55%),radial-gradient(ellipse at 25% 80%,rgba(212,175,55,0.06),transparent 40%); z-index:1; }
        .hero-wrap::after { content:""; position:absolute; inset:0; background:repeating-linear-gradient(0deg,transparent,transparent 2px,rgba(255,255,255,0.008) 2px,rgba(255,255,255,0.008) 4px); z-index:1; pointer-events:none; }
        .hero-bg { position:absolute; inset:-10px; background-size:cover; background-position:center; transform:scale(1.08); will-change:transform; animation:heroZoom 20s ease-in-out infinite alternate; }
        @keyframes heroZoom { 0% { transform:scale(1.10); } 100% { transform:scale(1); } }
        .hero-particles { position:absolute; inset:0; z-index:1; pointer-events:none; }
        .hero-particles span { position:absolute; width:3px; height:3px; background:var(--gold); border-radius:50%; opacity:0; will-change:transform,opacity; }
        @keyframes floatUp { from { transform:translateY(0); opacity:0; } 10% { opacity:0.6; } to { transform:translateY(-90vh); opacity:0; } }
        .stat-box { border-inline-start:2px solid var(--gold); padding-inline-start:16px; }
        .stat-num { font-size:clamp(1.6rem,2.5vw,2.2rem); font-weight:900; color:var(--text-primary); letter-spacing:-0.02em; }
        .stat-label { color:var(--text-secondary); font-size:12px; margin-top:2px; }
        .als-badge { position:absolute; top:12px; right:12px; z-index:5; background:linear-gradient(135deg,#ef4444,#b91c1c); color:#fff; font-size:10px; font-weight:800; padding:4px 12px; border-radius:999px; box-shadow:0 4px 12px rgba(239,68,68,0.35); animation:badgePulse 2.5s ease-in-out infinite; }
        @keyframes badgePulse { 0%,100% { box-shadow:0 4px 12px rgba(239,68,68,0.35); } 50% { box-shadow:0 4px 24px rgba(239,68,68,0.55); } }
        .als-card { background:var(--panel); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); overflow:hidden; transition:var(--transition-med); box-shadow:var(--shadow-card); will-change:transform; contain:layout style; }
        .als-card:hover { transform:translateY(-8px); border-color:var(--border-gold); box-shadow:var(--shadow-gold-lg); }
        .als-card .card-img-wrap { overflow:hidden; position:relative; background:var(--dark-3); }
        .als-card .card-img-wrap img { width:100%; aspect-ratio:1/1; object-fit:cover; transition:transform 0.6s cubic-bezier(0.4,0,0.2,1),opacity 0.4s; opacity:0; }
        .als-card .card-img-wrap img.loaded { opacity:1; }
        .als-card:hover .card-img-wrap img { transform:scale(1.08); }
        .als-card .card-body { padding:16px 18px 20px; }
        .shimmer-bg { background:linear-gradient(90deg,var(--dark-3) 0%,var(--panel-hover) 40%,var(--dark-3) 80%); background-size:200% 100%; animation:shimmer 1.8s ease-in-out infinite; }
        @keyframes shimmer { 0% { background-position:200% 0; } 100% { background-position:-200% 0; } }
        .als-heart { position:absolute; top:12px; left:12px; z-index:5; width:36px; height:36px; border-radius:50%; background:rgba(8,14,26,0.6); backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); display:flex; align-items:center; justify-content:center; color:var(--text-primary); cursor:pointer; transition:var(--transition-fast); border:1px solid var(--border-subtle); }
        .als-heart.active { background:rgba(239,68,68,0.9); border-color:rgba(239,68,68,0.4); }
        .als-heart.active svg { fill:currentColor; }
        .als-heart:hover { transform:scale(1.12); }
        .price-old-h { color:var(--text-muted); font-size:12px; text-decoration:line-through; margin-inline-start:6px; }
        .cat-tile { background:var(--panel); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:32px 16px 28px; text-align:center; transition:var(--transition-med); cursor:pointer; position:relative; overflow:hidden; isolation:isolate; }
        .cat-tile::before { content:""; position:absolute; inset:0; background:radial-gradient(circle at 50% 0%,var(--gold-glass),transparent 70%); opacity:0; transition:opacity 0.4s; z-index:-1; }
        .cat-tile:hover { border-color:var(--border-gold); transform:translateY(-6px); box-shadow:var(--shadow-gold); }
        .cat-tile:hover::before { opacity:1; }
        .cat-icon { font-size:36px; display:block; transition:transform 0.4s cubic-bezier(0.34,1.56,0.64,1); }
        .cat-tile:hover .cat-icon { transform:scale(1.2) rotate(-5deg); }
        .craft-step { position:relative; padding-inline-start:44px; transition:var(--transition-med); }
        .craft-step:hover { transform:translateX(-4px); }
        .craft-step .step-num { position:absolute; left:0; top:0; width:30px; height:30px; border-radius:50%; border:1.5px solid var(--gold); color:var(--gold); font-size:12px; font-weight:800; display:flex; align-items:center; justify-content:center; background:var(--dark-3); transition:var(--transition-med); }
        .craft-step:hover .step-num { background:var(--gold); color:#111; box-shadow:0 0 20px var(--gold-glow); }
        .craft-step::after { content:""; position:absolute; left:14px; top:32px; bottom:-36px; width:1.5px; background:linear-gradient(to bottom,var(--border-gold),transparent); }
        .craft-step:last-child::after { display:none; }
        .philosophy-quote { font-size:clamp(1.25rem,2.4vw,1.9rem); line-height:2; color:var(--text-primary); font-weight:700; position:relative; }
        .philosophy-quote::before { content:"\201C"; font-size:4rem; color:var(--gold); opacity:0.3; position:absolute; top:-1rem; left:-0.5rem; font-family:serif; line-height:1; }
        .pillar-card { background:rgba(255,255,255,0.03); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:28px 24px; transition:var(--transition-med); backdrop-filter:blur(4px); }
        .pillar-card:hover { border-color:var(--border-gold); transform:translateY(-4px); box-shadow:var(--shadow-gold); background:rgba(212,175,55,0.04); }
        .review-card { background:var(--panel-glass); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:28px 24px; transition:var(--transition-med); }
        .review-card:hover { border-color:var(--border-gold); transform:translateY(-4px); box-shadow:var(--shadow-gold); }
        .review-stars { color:var(--gold); letter-spacing:3px; font-size:13px; }
        .tier-card { background:var(--panel); border:1px solid var(--border-subtle); border-radius:var(--radius-xl); padding:36px 28px; transition:var(--transition-med); position:relative; overflow:hidden; }
        .tier-card.featured { border-color:var(--border-gold); background:linear-gradient(160deg,var(--panel),var(--gold-soft)); transform:scale(1.04); box-shadow:var(--shadow-gold); }
        .tier-card:hover { transform:translateY(-8px); box-shadow:var(--shadow-gold-lg); }
        .tier-card.featured:hover { transform:scale(1.04) translateY(-8px); box-shadow:var(--shadow-gold-lg); }
        .contact-card { background:var(--panel-glass); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:28px 24px; transition:var(--transition-med); }
        .contact-card:hover { border-color:var(--border-gold); transform:translateY(-3px); }
        .input-als { width:100%; background:rgba(255,255,255,0.05); border:1px solid var(--border-subtle); border-radius:var(--radius-sm); padding:12px 16px; color:var(--text-primary); font-size:13px; transition:var(--transition-fast); outline:none; }
        .input-als:focus { border-color:var(--gold); box-shadow:0 0 0 3px var(--gold-soft); }
        .input-als::placeholder { color:var(--text-muted); }
        #backToTop { position:fixed; bottom:30px; right:30px; z-index:999; width:46px; height:46px; border-radius:50%; background:var(--panel-glass); backdrop-filter:blur(12px); border:1px solid var(--border-gold); color:var(--gold); display:flex; align-items:center; justify-content:center; cursor:pointer; opacity:0; transform:translateY(20px) scale(0.8); transition:var(--transition-med); pointer-events:none; }
        #backToTop.show { opacity:1; transform:translateY(0) scale(1); pointer-events:auto; }
        #backToTop:hover { background:var(--gold); color:#111; box-shadow:0 0 24px var(--gold-glow); }
        .reveal-fade { opacity:0; transform:translateY(30px); transition:opacity 0.7s cubic-bezier(0.4,0,0.2,1),transform 0.7s cubic-bezier(0.4,0,0.2,1); }
        .reveal-fade.revealed { opacity:1; transform:translateY(0); }
        .reveal-left { opacity:0; transform:translateX(-40px); transition:opacity 0.7s cubic-bezier(0.4,0,0.2,1),transform 0.7s cubic-bezier(0.4,0,0.2,1); }
        .reveal-left.revealed { opacity:1; transform:translateX(0); }
        .reveal-right { opacity:0; transform:translateX(40px); transition:opacity 0.7s cubic-bezier(0.4,0,0.2,1),transform 0.7s cubic-bezier(0.4,0,0.2,1); }
        .reveal-right.revealed { opacity:1; transform:translateX(0); }
        .reveal-scale { opacity:0; transform:scale(0.9); transition:opacity 0.6s cubic-bezier(0.4,0,0.2,1),transform 0.6s cubic-bezier(0.4,0,0.2,1); }
        .reveal-scale.revealed { opacity:1; transform:scale(1); }
        .stagger-children > * { opacity:0; transform:translateY(24px); transition:opacity 0.5s cubic-bezier(0.4,0,0.2,1),transform 0.5s cubic-bezier(0.4,0,0.2,1); }
        .stagger-children.revealed > *:nth-child(1) { transition-delay:0.00s; }
        .stagger-children.revealed > *:nth-child(2) { transition-delay:0.08s; }
        .stagger-children.revealed > *:nth-child(3) { transition-delay:0.16s; }
        .stagger-children.revealed > *:nth-child(4) { transition-delay:0.24s; }
        .stagger-children.revealed > *:nth-child(5) { transition-delay:0.32s; }
        .stagger-children.revealed > *:nth-child(6) { transition-delay:0.40s; }
        .stagger-children.revealed > *:nth-child(7) { transition-delay:0.48s; }
        .stagger-children.revealed > *:nth-child(8) { transition-delay:0.56s; }
        .stagger-children.revealed > * { opacity:1; transform:translateY(0); }
        @media (max-width: 768px) { .hero-wrap { min-height:92vh; } .tier-card.featured { transform:none; } .tier-card.featured:hover { transform:translateY(-6px); } }
    </style>';

$css_start = strpos($content, '<style>');
$css_end = strpos($content, '</style>');
if ($css_start !== false && $css_end !== false) {
    $content = substr_replace($content, $new_css, $css_start, ($css_end + 8) - $css_start);
}

// 2. Replace JS block
$new_js = '
    <script>
        function alsDebounce(fn,ms){let t;return function(...a){clearTimeout(t);t=setTimeout(()=>fn.apply(this,a),ms);};}
        (function(){const w=document.getElementById("heroParticles");if(!w)return;const f=document.createDocumentFragment();for(let i=0;i<24;i++){const s=document.createElement("span");s.style.cssText="left:"+(Math.random()*100)+"%;bottom:-6px;animation:floatUp "+(6+Math.random()*12).toFixed(1)+"s linear "+(Math.random()*10).toFixed(1)+"s infinite;width:"+(2+Math.random()*3)+"px;height:"+(2+Math.random()*3)+"px;";f.appendChild(s);}w.appendChild(f);})();
        (function(){const c=document.querySelectorAll(".counter");if(!c.length)return;const io=new IntersectionObserver((entries)=>{entries.forEach(e=>{if(!e.isIntersecting)return;const el=e.target,target=parseInt(el.dataset.target,10);let cur=0;const duration=1200;const start=performance.now();function tick(now){const p=Math.min((now-start)/duration,1);const eased=1-Math.pow(1-p,3);cur=Math.round(eased*target);el.textContent=cur.toLocaleString("fa-IR");if(p<1)requestAnimationFrame(tick);else el.textContent=target.toLocaleString("fa-IR");}requestAnimationFrame(tick);io.unobserve(el);});},{threshold:0.3});c.forEach(el=>io.observe(el));})();
        (function(){const els=document.querySelectorAll("[data-reveal]");if(!els.length)return;const io=new IntersectionObserver((entries)=>{entries.forEach(e=>{if(!e.isIntersecting)return;const type=e.target.dataset.reveal||"fade";e.target.classList.add("reveal-"+type);requestAnimationFrame(()=>requestAnimationFrame(()=>e.target.classList.add("revealed")));io.unobserve(e.target);});},{threshold:0.08,rootMargin:"0px 0px -40px 0px"});els.forEach(el=>io.observe(el));const st=document.querySelectorAll("[data-stagger]");if(st.length){const io2=new IntersectionObserver((entries)=>{entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add("revealed");io2.unobserve(e.target);}});},{threshold:0.08});st.forEach(el=>io2.observe(el));}})();
        (function(){if(!("IntersectionObserver" in window))return;const io=new IntersectionObserver((entries)=>{entries.forEach(e=>{if(!e.isIntersecting)return;const img=e.target,src=img.dataset.src;if(src){img.src=src;img.removeAttribute("data-src");}img.addEventListener("load",function onLoad(){img.classList.add("loaded");img.removeEventListener("load",onLoad);},{once:true});if(img.complete&&img.naturalWidth)img.classList.add("loaded");io.unobserve(img);});},{rootMargin:"200px 0px"});document.querySelectorAll("img[data-src]").forEach(img=>io.observe(img));})();
        function toggleWishlist(productId,el){<?php if(!isset($_SESSION["user_id"])): ?>openAuthModal();return;<?php endif; ?>const isActive=el.classList.contains("active");const action=isActive?"remove":"add";const fd=new FormData();fd.append("action",action);fd.append("product_id",productId);fetch("api/wishlist.php",{method:"POST",body:fd}).then(r=>r.json()).then(d=>{if(d.status==="success"){el.classList.toggle("active");}else{alert(d.message||"خطا در ثبت علاقه\u200cمندی");}}).catch(()=>alert("خطا در ارتباط با سرور"));}
        function alsSwitchTab(tab,btn){document.querySelectorAll(".tab-btn").forEach(b=>b.classList.remove("active"));btn.classList.add("active");document.getElementById("tab-essentials").classList.toggle("hidden",tab!=="essentials");document.getElementById("tab-accessories").classList.toggle("hidden",tab!=="accessories");}
        function setupTestimonialStars(){const rw=document.getElementById("testimonial-rating");if(!rw)return;const set=(v)=>{rw.dataset.value=v;rw.querySelectorAll(".star-btn").forEach(b=>{const sv=Number(b.dataset.star);b.classList.toggle("text-yellow-400",sv<=v);b.classList.toggle("text-gray-500",sv>v);});};rw.querySelectorAll(".star-btn").forEach(b=>b.addEventListener("click",()=>set(Number(b.dataset.star))));set(5);}
        function alsSubmitContact(e){e.preventDefault();const name=document.getElementById("ct-name").value.trim();const phone=document.getElementById("ct-phone").value.trim();const message=document.getElementById("ct-message").value.trim();const rw=document.getElementById("testimonial-rating");const rating=rw?Number(rw.dataset.value||5):5;const re=document.getElementById("ct-result");if(!name||!message)return;const fd=new FormData();fd.append("action","submit_testimonial");fd.append("name",name);fd.append("phone",phone);fd.append("message",message);fd.append("rating",String(rating));fetch("auth.php",{method:"POST",body:fd}).then(r=>r.json()).then(d=>{re.classList.remove("hidden","text-green-400","text-red-400");re.classList.add(d.status==="success"?"text-green-400":"text-red-400");re.textContent=d.message||(d.status==="success"?"ثبت شد":"خطا رخ داد");if(d.status==="success"){document.getElementById("als-contact-form").reset();setupTestimonialStars();}}).catch(()=>{re.classList.remove("hidden");re.classList.add("text-red-400");re.textContent="خطا در ارتباط با سرور";});}
        (function(){const btn=document.getElementById("backToTop");if(!btn)return;let ticking=false;window.addEventListener("scroll",()=>{if(!ticking){requestAnimationFrame(()=>{btn.classList.toggle("show",window.scrollY>500);ticking=false;});ticking=true;}},{passive:true});})();
        document.addEventListener("DOMContentLoaded",function(){setupTestimonialStars();});
    </script>';

$backtotop = strrpos($content, 'id="backToTop"');
$last_script = strrpos($content, '<script>', $backtotop !== false ? $backtotop : 0);
$script_end = strrpos($content, '</script>', $last_script !== false ? $last_script + 1 : 0);

if ($last_script !== false && $script_end !== false && $script_end > $last_script) {
    $content = substr_replace($content, $new_js, $last_script, ($script_end + 9) - $last_script);
}

// 3. Replace data-aos with data-reveal
$content = str_replace('data-aos="fade-up"', 'data-reveal="fade"', $content);
$content = str_replace('data-aos-delay="', 'data-old-delay="', $content);

// 4. Add stagger to product grids
$content = str_replace(
    'class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5"',
    'class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5 stagger-children" data-stagger',
    $content
);

// 5. Lazy loading for product images
$content = str_replace(
    'src="<?php echo als_product_img($p',
    'data-src="<?php echo als_product_img($p',
    $content
);
$content = str_replace(
    'src="<?php echo htmlspecialchars($cat',
    'data-src="<?php echo htmlspecialchars($cat',
    $content
);

file_put_contents($file, $content);
echo "OK - home.php upgraded successfully\n";
echo "Size: " . strlen($content) . " chars\n";
