<?php
// includes/notify-widget.php  — يُضاف داخل footer.php (قبل </body>)
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['user_id'])) { return; }
if (empty($_SESSION['notification_csrf'])) { $_SESSION['notification_csrf'] = bin2hex(random_bytes(32)); }
$documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '';
$projectRoot = realpath(__DIR__) ?: __DIR__;
$relativeRoot = $documentRoot !== '' && stripos($projectRoot, $documentRoot) === 0
  ? substr($projectRoot, strlen($documentRoot))
  : '';
$NB = '/' . trim(str_replace('\\', '/', $relativeRoot), '/');
if ($NB !== '/') { $NB .= '/'; }
?>
<style>
.bn-stack{position:fixed;top:84px;left:20px;z-index:3000;display:flex;flex-direction:column;gap:12px;width:min(380px,calc(100vw - 40px));pointer-events:none;font-family:'Tajawal',sans-serif;direction:rtl}
.bn-toast{pointer-events:auto;position:relative;background:#fff;border-radius:16px;padding:16px 16px 14px;box-shadow:0 14px 40px rgba(0,60,60,.18);border-right:6px solid #008080;overflow:hidden;animation:bn-in .45s cubic-bezier(.2,.9,.3,1.2)}
.bn-toast.reminder{border-right-color:#d98e04;background:#fffaf0}
.bn-toast.done{border-right-color:#1e7e34}
.bn-toast.leaving{animation:bn-out .3s ease forwards}
.bn-row{display:flex;gap:12px;align-items:flex-start}
.bn-icon{flex:none;width:44px;height:44px;border-radius:50%;display:grid;place-items:center;font-size:22px;background:#e0f3f1;position:relative}
.reminder .bn-icon{background:#fdecc8}
.reminder .bn-icon::after{content:"";position:absolute;inset:-4px;border-radius:50%;border:2px solid #d98e04;animation:bn-pulse 1.6s ease-out infinite}
.bn-title{font-weight:800;font-size:16px;color:#2b3e42;margin:0 0 2px}
.bn-text{font-size:14px;line-height:1.7;color:#4a5b5e;margin:0}
.bn-chips{display:flex;flex-wrap:wrap;gap:6px;margin-top:8px}
.bn-chip{background:#eef6f6;color:#005a5a;border-radius:20px;padding:2px 10px;font-size:12px;font-weight:600}
.reminder .bn-chip{background:#fbefd3;color:#7a5200}
.bn-actions{display:flex;gap:8px;margin-top:12px}
.bn-btn{flex:1;border:0;border-radius:20px;padding:9px 12px;font:600 14px 'Tajawal',sans-serif;cursor:pointer;background:#008080;color:#fff;transition:transform .15s,opacity .15s}
.bn-btn:hover{opacity:.92;transform:translateY(-1px)}
.bn-btn.ghost{background:transparent;color:#008080;border:1.5px solid #008080}
.reminder .bn-btn:not(.ghost){background:#d98e04}
.bn-btn:focus-visible,.bn-bell:focus-visible,.bn-close:focus-visible,.bn-mute:focus-visible{outline:3px solid #005a5a;outline-offset:2px}
.bn-close{position:absolute;top:8px;left:10px;border:0;background:none;font-size:20px;color:#8a9a9d;cursor:pointer}
.bn-bar{position:absolute;bottom:0;right:0;height:3px;background:#008080;width:100%;transform-origin:right;animation:bn-bar linear forwards}
.bn-dock{position:fixed;bottom:22px;left:22px;z-index:3000;display:flex;gap:10px;align-items:center}
.bn-bell,.bn-mute{border:0;cursor:pointer;border-radius:50%;width:54px;height:54px;font-size:24px;background:#008080;color:#fff;box-shadow:0 8px 22px rgba(0,90,90,.35);position:relative;text-decoration:none;display:grid;place-items:center}
.bn-mute{width:40px;height:40px;font-size:17px;background:#fff;color:#008080;box-shadow:0 4px 14px rgba(0,0,0,.12)}
.bn-badge{position:absolute;top:-4px;right:-4px;min-width:22px;height:22px;border-radius:11px;background:#d93025;color:#fff;font:700 12px 'Tajawal',sans-serif;display:none;place-items:center;padding:0 5px}
.bn-bell.ring{animation:bn-ring 1s ease 2}
@keyframes bn-in{from{opacity:0;transform:translateX(-40px) scale(.96)}to{opacity:1;transform:none}}
@keyframes bn-out{to{opacity:0;transform:translateX(-40px)}}
@keyframes bn-pulse{from{opacity:.9;transform:scale(1)}to{opacity:0;transform:scale(1.5)}}
@keyframes bn-bar{to{transform:scaleX(0)}}
@keyframes bn-ring{0%,100%{transform:rotate(0)}20%{transform:rotate(14deg)}40%{transform:rotate(-12deg)}60%{transform:rotate(8deg)}80%{transform:rotate(-6deg)}}
@media(prefers-reduced-motion:reduce){.bn-toast,.bn-bell.ring,.reminder .bn-icon::after,.bn-bar{animation:none}}
@media(max-width:600px){.bn-stack{top:70px;left:10px;width:calc(100vw - 20px)}}
</style>

<div class="bn-stack" id="bnStack" role="region" aria-live="assertive" aria-label="التنبيهات"></div>
<div class="bn-dock">
    <button class="bn-mute" id="bnMute" type="button" aria-label="تشغيل أو إيقاف الصوت">🔊</button>
    <a class="bn-bell" id="bnBell" href="<?= $NB ?>notifications.php" aria-label="صفحة التنبيهات">🔔<span class="bn-badge" id="bnBadge">0</span></a>
</div>

<script>
(function () {
  const API  = <?= json_encode($NB . 'notifications_api.php') ?>;
  let csrf   = <?= json_encode($_SESSION['notification_csrf']) ?>;
  const REMINDER = 'تذكير بالجرعة';
  const stack = document.getElementById('bnStack');
  const badge = document.getElementById('bnBadge');
  const bell  = document.getElementById('bnBell');
  const muteBtn = document.getElementById('bnMute');

  const store = {
    get(k, d){ try { return JSON.parse(sessionStorage.getItem(k)) ?? d; } catch(e){ return d; } },
    set(k, v){ try { sessionStorage.setItem(k, JSON.stringify(v)); } catch(e){} }
  };
  const seen = new Set(store.get('bn_seen', []));
  let muted = false;
  try { muted = localStorage.getItem('bn_muted') === '1'; } catch(e){}
  muteBtn.textContent = muted ? '🔇' : '🔊';
  muteBtn.onclick = () => {
    muted = !muted;
    try { localStorage.setItem('bn_muted', muted ? '1' : '0'); } catch(e){}
    muteBtn.textContent = muted ? '🔇' : '🔊';
    if (muted && window.speechSynthesis) speechSynthesis.cancel();
  };

  /* إذن إشعارات المتصفح + تفعيل الصوت عند أول نقرة */
  document.addEventListener('click', () => {
    if ('Notification' in window && Notification.permission === 'default') Notification.requestPermission();
    getCtx();
  }, { once: true });

  let ctx;
  function getCtx(){
    try { ctx = ctx || new (window.AudioContext || window.webkitAudioContext)(); if (ctx.state === 'suspended') ctx.resume(); } catch(e){}
    return ctx;
  }
  function chime(urgent){
    if (muted) return;
    const c = getCtx(); if (!c) return;
    const notes = urgent ? [880, 660, 880, 660] : [660, 880];
    notes.forEach((f, i) => {
      const o = c.createOscillator(), g = c.createGain();
      o.type = 'sine'; o.frequency.value = f;
      const t = c.currentTime + i * 0.22;
      g.gain.setValueAtTime(0.0001, t);
      g.gain.exponentialRampToValueAtTime(0.25, t + 0.03);
      g.gain.exponentialRampToValueAtTime(0.0001, t + 0.2);
      o.connect(g).connect(c.destination); o.start(t); o.stop(t + 0.22);
    });
  }
  function speak(text){
    if (muted || !text || !window.speechSynthesis) return;
    try {
      const u = new SpeechSynthesisUtterance(text);
      u.lang = 'ar-SA'; u.rate = 0.95;
      speechSynthesis.speak(u);
    } catch(e){}
  }

  function el(tag, cls, text){
    const e = document.createElement(tag);
    if (cls) e.className = cls;
    if (text !== undefined) e.textContent = text;
    return e;
  }
  function setBadge(n){
    badge.textContent = n > 99 ? '99+' : n;
    badge.style.display = n > 0 ? 'grid' : 'none';
  }
  function dismiss(t){
    t.classList.add('leaving');
    setTimeout(() => t.remove(), 300);
  }

  async function post(action, id){
    const fd = new FormData();
    fd.append('action', action);
    fd.append('notification_id', id);
    fd.append('csrf_token', csrf);
    const r = await fetch(API, { method: 'POST', body: fd, credentials: 'same-origin' });
    return r.ok ? r.json() : { ok: false };
  }

  function showToast(n){
    const reminder = n.notification_type === REMINDER;
    const t = el('article', 'bn-toast' + (reminder ? ' reminder' : ''));
    t.setAttribute('role', reminder ? 'alert' : 'status');

    const close = el('button', 'bn-close', '×');
    close.type = 'button'; close.setAttribute('aria-label', 'إغلاق');
    close.onclick = () => dismiss(t);

    const row = el('div', 'bn-row');
    row.append(el('div', 'bn-icon', reminder ? '⏰' : '✅'));
    const body = el('div');
    body.append(el('h3', 'bn-title', reminder ? 'حان وقت الدواء' : 'تمت إضافة الدواء'));
    body.append(el('p', 'bn-text', n.notification_text || ''));

    const chips = el('div', 'bn-chips');
    if (n.medication_name) chips.append(el('span', 'bn-chip', '💊 ' + n.medication_name));
    if (n.dose_amount) chips.append(el('span', 'bn-chip', parseFloat(n.dose_amount) + ' ' + (n.dose_unit || '')));
    if (n.medication_shape) chips.append(el('span', 'bn-chip', n.medication_shape));
    if (n.medication_color) chips.append(el('span', 'bn-chip', n.medication_color));
    if (chips.children.length) body.append(chips);

    const actions = el('div', 'bn-actions');
    if (n.schedule_id && n.dosage_status === 'pending' && reminder) {
      const take = el('button', 'bn-btn', 'تناولت الجرعة ✓');
      take.type = 'button';
      take.onclick = async () => {
        take.disabled = true;
        const res = await post('take_dose', n.notification_id);
        if (res.ok) { dismiss(t); flash('تم تسجيل الجرعة، صحة وعافية 💚'); refresh(); afterChange(); }
        else take.disabled = false;
      };
      actions.append(take);
    }
    const read = el('button', 'bn-btn ghost', 'تحديد كمقروء');
    read.type = 'button';
    read.onclick = async () => {
      const res = await post('mark_read', n.notification_id);
      if (res.ok) { dismiss(t); refresh(); afterChange(); }
    };
    actions.append(read);
    body.append(actions);
    row.append(body);
    t.append(close, row);

    if (!reminder) {                       // التأكيد يختفي تلقائياً، التذكير يبقى حتى يتفاعل المستخدم
      const bar = el('div', 'bn-bar');
      bar.style.animationDuration = '9s';
      t.append(bar);
      setTimeout(() => t.isConnected && dismiss(t), 9000);
    }
    stack.prepend(t);

    chime(reminder);
    bell.classList.add('ring'); setTimeout(() => bell.classList.remove('ring'), 2200);
    if (reminder) {
      speak(n.audio_text || n.notification_text);
      if ('Notification' in window && Notification.permission === 'granted' && document.hidden) {
        try { new Notification('بصيرة — حان وقت الدواء', { body: n.notification_text }); } catch(e){}
      }
    }
  }

  function flash(msg){
    const t = el('article', 'bn-toast done');
    const row = el('div', 'bn-row');
    row.append(el('div', 'bn-icon', '💚'));
    const b = el('div'); b.append(el('p', 'bn-text', msg)); row.append(b);
    t.append(row); stack.prepend(t);
    setTimeout(() => t.isConnected && dismiss(t), 4000);
  }
  function afterChange(){
    if (/notifications\.php$/.test(location.pathname)) setTimeout(() => location.reload(), 900);
  }

  async function refresh(){
    try {
      const r = await fetch(API + '?t=' + Date.now(), { credentials: 'same-origin' });
      if (!r.ok) return;
      const d = await r.json();
      if (!d.ok) return;
      csrf = d.csrf;
      setBadge(d.unread);
      d.items.slice().reverse().forEach(n => {
        const id = Number(n.notification_id);
        if (seen.has(id)) return;
        seen.add(id);
        showToast(n);
      });
      store.set('bn_seen', Array.from(seen).slice(-200));
    } catch (e) {}
  }

  refresh();
  setInterval(refresh, 20000);
  document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });
})();
</script>
