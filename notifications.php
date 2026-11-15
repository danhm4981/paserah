<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بصيرة | نظام التذكير الذكي للأدوية</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; }
        @keyframes flashAlert {
            0%, 100% { background-color: #f8fafc; }
            50% { background-color: #fee2e2; }
        }
        .visual-alarm { animation: flashAlert 0.6s ease-in-out 4; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col justify-between transition-colors">

    <!-- الشريط العلوي -->
    <header class="bg-white shadow-sm border-b border-slate-100 sticky top-0 z-40">
        <div class="max-w-4xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-teal-600 flex items-center justify-center text-white shadow-md">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </div>
                <div>
                    <h1 class="font-bold text-lg text-teal-900">بصيرة</h1>
                    <p class="text-xs text-slate-500">لوحة المريض والتنبيهات</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="openModal()" class="px-3 py-2 text-xs font-bold bg-teal-600 text-white rounded-xl hover:bg-teal-700 transition shadow-sm">
                    + إضافة دواء
                </button>
                <button onclick="triggerAlarm('باراسيتامول', 'حبوب دائرية', 'أبيض', '#ffffff', '500 ملغ')" class="relative p-2.5 rounded-full bg-teal-50 text-teal-700 hover:bg-teal-100 transition" title="تجربة التنبيه الصوتي والمرئي">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-red-500 rounded-full animate-ping"></span>
                </button>
            </div>
        </div>
    </header>

    <!-- المحتوى الرئيسي -->
    <main class="max-w-4xl mx-auto px-4 py-6 w-full flex-grow space-y-6">

        <!-- لوحة الساعة والحالة -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex flex-col items-center text-center">
            <span class="text-xs font-semibold px-3 py-1 bg-teal-50 text-teal-700 rounded-full mb-2">الوقت الحالي (مراقبة الجرعات نشطة)</span>
            <div id="live-clock" class="text-3xl font-black text-slate-800 tracking-wider mb-3" dir="ltr">00:00:00</div>
            <div class="flex flex-wrap justify-center gap-2">
                <span class="flex items-center gap-1.5 text-xs text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-lg font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    النظام متصل ومستعد للتنبيه
                </span>
                <button onclick="triggerAlarm('باراسيتامول', 'حبوب دائرية', 'أبيض', '#ffffff', '500 ملغ')" class="text-xs font-bold bg-amber-100 text-amber-800 px-3 py-1.5 rounded-lg hover:bg-amber-200 transition">
                    🔔 تجربة تنبيه صوتي ومرئي الآن
                </button>
            </div>
        </div>

        <!-- قسم التنبيهات -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">تنبيهاتي</h2>
                    <p class="text-xs text-slate-500 mt-0.5">تابعي تنبيهات أدويتك ومواعيد الجرعات مع تفاصيل الشكل واللون لسهولة التمييز</p>
                </div>
                <div class="flex items-center gap-3">
                    <span id="badge-count" class="text-xs font-bold px-3 py-1 bg-teal-50 text-teal-700 rounded-full border border-teal-200">2 تنبيه نشط</span>
                    <button onclick="markAllRead()" class="text-xs text-slate-500 font-semibold hover:text-teal-700 underline">تحديد الكل كمقروء</button>
                </div>
            </div>

            <!-- قائمة الأدوية والتنبيهات -->
            <div id="notifications-list" class="space-y-4">

                <!-- بطاقة دواء 1 -->
                <div id="alert-1" class="p-4 rounded-xl border border-amber-200 bg-amber-50/30 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 transition">
                    <div class="flex items-start gap-3.5 w-full">
                        <!-- تمثيل بصري لشكل ولون الدواء -->
                        <div class="w-14 h-14 rounded-2xl bg-slate-100 border border-slate-200 flex flex-col items-center justify-center flex-shrink-0 shadow-inner">
                            <span class="w-7 h-7 rounded-full bg-white border-2 border-slate-300 shadow-sm block"></span>
                            <span class="text-[10px] text-slate-500 mt-1 font-bold">أبيض</span>
                        </div>
                        <div class="flex-grow">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold px-2.5 py-0.5 bg-amber-100 text-amber-800 rounded-md">تذكير بالجرعة</span>
                                <span class="text-xs text-slate-400 font-semibold" dir="ltr">08:00 PM</span>
                            </div>
                            <h3 class="font-bold text-slate-800 mt-1.5 text-base">حان موعد دواء باراسيتامول</h3>
                            <div class="text-xs text-slate-600 mt-2 bg-white p-3 rounded-xl border border-slate-100 grid grid-cols-2 gap-2">
                                <p><span class="text-slate-400">اسم الدواء:</span> <strong class="text-slate-800">باراسيتامول</strong></p>
                                <p><span class="text-slate-400">الجرعة:</span> <strong class="text-teal-700">500 ملغ</strong></p>
                                <p><span class="text-slate-400">شكل الدواء:</span> <strong class="text-slate-800">حبوب دائرية</strong></p>
                                <p class="flex items-center gap-1.5"><span class="text-slate-400">لون الدواء:</span> <strong class="text-slate-800">أبيض</strong> <span class="w-3 h-3 rounded-full bg-white border border-slate-400 inline-block"></span></p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 w-full md:w-auto justify-end pt-2 md:pt-0 border-t md:border-t-0 border-slate-100">
                        <button onclick="triggerAlarm('باراسيتامول', 'حبوب دائرية', 'أبيض', '#ffffff', '500 ملغ')" class="p-2 text-teal-700 bg-teal-50 rounded-lg hover:bg-teal-100" title="سماع التنبيه">🔊</button>
                        <button onclick="dismissAlert('alert-1')" class="px-3 py-2 text-xs font-semibold bg-white border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-50">تحديد كمقروء</button>
                        <button onclick="confirmDose('alert-1', 'باراسيتامول')" class="px-4 py-2 text-xs font-bold bg-teal-600 text-white rounded-lg hover:bg-teal-700 shadow-sm">تناولت الجرعة</button>
                    </div>
                </div>

                <!-- بطاقة دواء 2 -->
                <div id="alert-2" class="p-4 rounded-xl border border-teal-100 bg-white flex flex-col md:flex-row items-start md:items-center justify-between gap-4 transition">
                    <div class="flex items-start gap-3.5 w-full">
                        <!-- تمثيل بصري لشكل ولون الدواء -->
                        <div class="w-14 h-14 rounded-2xl bg-amber-50 border border-amber-200 flex flex-col items-center justify-center flex-shrink-0 shadow-inner">
                            <span class="w-5 h-8 rounded-t-md rounded-b-lg bg-amber-400 border border-amber-500 shadow-sm block"></span>
                            <span class="text-[10px] text-amber-800 mt-1 font-bold">أصفر</span>
                        </div>
                        <div class="flex-grow">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold px-2.5 py-0.5 bg-teal-50 text-teal-700 rounded-md">موعد قادم</span>
                                <span class="text-xs text-slate-400 font-semibold" dir="ltr">09:45 PM</span>
                            </div>
                            <h3 class="font-bold text-slate-800 mt-1.5 text-base">مضاد حيوي للأطفال (شراب)</h3>
                            <div class="text-xs text-slate-600 mt-2 bg-slate-50 p-3 rounded-xl border border-slate-100 grid grid-cols-2 gap-2">
                                <p><span class="text-slate-400">اسم الدواء:</span> <strong class="text-slate-800">أموكسيسيلين</strong></p>
                                <p><span class="text-slate-400">الجرعة:</span> <strong class="text-teal-700">5 مل</strong></p>
                                <p><span class="text-slate-400">شكل الدواء:</span> <strong class="text-slate-800">شراب سائل</strong></p>
                                <p class="flex items-center gap-1.5"><span class="text-slate-400">لون الدواء:</span> <strong class="text-slate-800">أصفر</strong> <span class="w-3 h-3 rounded-full bg-amber-400 inline-block"></span></p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 w-full md:w-auto justify-end pt-2 md:pt-0 border-t md:border-t-0 border-slate-100">
                        <button onclick="triggerAlarm('شراب مضاد حيوي', 'شراب سائل', 'أصفر', '#fbbf24', '5 مل')" class="p-2 text-teal-700 bg-teal-50 rounded-lg hover:bg-teal-100" title="سماع التنبيه">🔊</button>
                        <button onclick="dismissAlert('alert-2')" class="px-3 py-2 text-xs font-semibold bg-white border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-50">تحديد كمقروء</button>
                        <button onclick="confirmDose('alert-2', 'مضاد حيوي')" class="px-4 py-2 text-xs font-bold bg-teal-600 text-white rounded-lg hover:bg-teal-700 shadow-sm">تناولت الجرعة</button>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- نافذة التنبيه المرئي المنبثقة عند موعد الجرعة -->
    <div id="visual-alarm-modal" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-sm w-full p-6 text-center shadow-2xl border-4 border-red-500 animate-bounce">
            <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-3 text-3xl">
                ⏰
            </div>
            <span class="text-xs font-bold bg-red-100 text-red-700 px-3 py-1 rounded-full">تنبيه موعد الدواء الآن</span>
            <h3 id="alarm-med-name" class="text-2xl font-black text-slate-800 mt-3">باراسيتامول</h3>
            <p id="alarm-med-dose" class="text-sm font-bold text-teal-700 mt-1">الجرعة: 500 ملغ</p>
            
            <div class="my-4 p-4 bg-slate-50 rounded-2xl border border-slate-200 flex flex-col items-center">
                <p class="text-xs text-slate-500 mb-2">مواصفات تمييز الدواء:</p>
                <div id="alarm-color-circle" class="w-12 h-12 rounded-full border-2 border-slate-300 shadow-md mb-2"></div>
                <p id="alarm-med-desc" class="text-sm font-bold text-slate-800">الشكل: حبوب | اللون: أبيض</p>
            </div>

            <button onclick="closeAlarmModal()" class="w-full py-3 bg-teal-600 text-white font-bold rounded-xl hover:bg-teal-700 transition shadow-lg">
                تم الاستلام وإيقاف التنبيه
            </button>
        </div>
    </div>

    <!-- نافذة إضافة دواء جديد -->
    <div id="add-med-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 class="font-bold text-lg text-slate-800">إضافة دواء وموعد تنبيه</h3>
                <button onclick="closeModal()" class="text-slate-400 hover:text-slate-700 font-bold text-xl">&times;</button>
            </div>
            <form onsubmit="addMedication(event)" class="space-y-3 text-sm">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">اسم الدواء</label>
                    <input type="text" id="med-name" required placeholder="مثال: فيتامين د" class="w-full border border-slate-200 rounded-xl px-3 py-2 focus:outline-teal-600">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">شكل الدواء</label>
                        <select id="med-shape" class="w-full border border-slate-200 rounded-xl px-3 py-2">
                            <option value="حبوب دائرية">حبوب دائرية</option>
                            <option value="كبسولات">كبسولات</option>
                            <option value="شراب سائل">شراب سائل</option>
                            <option value="قطرة">قطرة</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">لون الدواء</label>
                        <select id="med-color" class="w-full border border-slate-200 rounded-xl px-3 py-2">
                            <option value="أبيض|#ffffff">أبيض</option>
                            <option value="أحمر|#ef4444">أحمر</option>
                            <option value="أصفر|#fbbf24">أصفر</option>
                            <option value="أزرق|#3b82f6">أزرق</option>
                            <option value="أخضر|#10b981">أخضر</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">الجرعة</label>
                        <input type="text" id="med-dose" required placeholder="مثال: حبة واحدة" class="w-full border border-slate-200 rounded-xl px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">وقت الجرعة</label>
                        <input type="time" id="med-time" required class="w-full border border-slate-200 rounded-xl px-3 py-2">
                    </div>
                </div>
                <button type="submit" class="w-full py-2.5 bg-teal-600 text-white font-bold rounded-xl hover:bg-teal-700 mt-2">حفظ وتفعيل التنبيه</button>
            </form>
        </div>
    </div>

    <!-- تذييل الصفحة -->
    <footer class="bg-white border-t border-slate-100 py-4 text-center text-xs text-slate-400 mt-8">
        جميع الحقوق محفوظة © 2026 - نظام بصيرة للتذكير بالأدوية
    </footer>

    <script>
        let scheduledMeds = [];

        // تحديث الساعة الحية وفحص المواعيد كل ثانية
        function updateClock() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('en-GB');
            document.getElementById('live-clock').innerText = timeStr;

            const currentHM = timeStr.slice(0, 5);
            if (now.getSeconds() === 0) {
                scheduledMeds.forEach(med => {
                    if (med.time === currentHM) {
                        triggerAlarm(med.name, med.shape, med.colorName, med.colorHex, med.dose);
                    }
                });
            }
        }
        setInterval(updateClock, 1000);
        updateClock();

        // تحديث عداد التنبيهات
        function updateBadge() {
            const count = document.querySelectorAll('#notifications-list > div').length;
            document.getElementById('badge-count').innerText = count + ' تنبيه نشط';
        }

        // إزالة تنبيه
        function dismissAlert(id) {
            const el = document.getElementById(id);
            if (el) {
                el.remove();
                updateBadge();
            }
        }

        // تأكيد تناول الجرعة
        function confirmDose(id, medName) {
            speakArabic('أحسنت، تم تسجيل تناول جرعة ' + medName);
            dismissAlert(id);
        }

        // تحديد الكل مقروء
        function markAllRead() {
            document.getElementById('notifications-list').innerHTML = '<p class="text-center text-xs text-slate-400 py-6">لا توجد تنبيهات غير مقروءة حالياً</p>';
            updateBadge();
        }

        // نغمة تنبيه صوتية (Beep) + نطق عربي
        function playBeep() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(880, ctx.currentTime);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                gain.gain.exponentialRampToValueAtTime(0.00001, ctx.currentTime + 0.8);
                osc.stop(ctx.currentTime + 0.8);
            } catch (e) {}
        }

        function speakArabic(text) {
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
                const msg = new SpeechSynthesisUtterance(text);
                msg.lang = 'ar-SA';
                msg.rate = 0.95;
                window.speechSynthesis.speak(msg);
            }
        }

        // تشغيل التنبيه الصوتي والمرئي الكامل
        function triggerAlarm(name, shape, colorName, colorHex, dose) {
            playBeep();
            document.body.classList.add('visual-alarm');
            setTimeout(() => document.body.classList.remove('visual-alarm'), 2500);

            document.getElementById('alarm-med-name').innerText = name;
            document.getElementById('alarm-med-dose').innerText = 'الجرعة: ' + dose;
            document.getElementById('alarm-med-desc').innerText = 'الشكل: ' + shape + ' | اللون: ' + colorName;
            document.getElementById('alarm-color-circle').style.backgroundColor = colorHex;

            const modal = document.getElementById('visual-alarm-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            speakArabic(`تنبيه موعد الدواء. حان الآن موعد ${name}، الجرعة ${dose}، شكل الدواء ${shape}، ولونه ${colorName}`);
        }

        function closeAlarmModal() {
            window.speechSynthesis.cancel();
            const modal = document.getElementById('visual-alarm-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // نافذة إضافة دواء
        function openModal() {
            document.getElementById('add-med-modal').classList.remove('hidden');
            document.getElementById('add-med-modal').classList.add('flex');
        }

        function closeModal() {
            document.getElementById('add-med-modal').classList.add('hidden');
            document.getElementById('add-med-modal').classList.remove('flex');
        }

        function addMedication(e) {
            e.preventDefault();
            const name = document.getElementById('med-name').value;
            const shape = document.getElementById('med-shape').value;
            const [colorName, colorHex] = document.getElementById('med-color').value.split('|');
            const dose = document.getElementById('med-dose').value;
            const time = document.getElementById('med-time').value;
            const id = 'alert-' + Date.now();

            scheduledMeds.push({ name, shape, colorName, colorHex, dose, time });

            const list = document.getElementById('notifications-list');
            if (list.querySelector('p.text-center')) list.innerHTML = '';

            const card = `
                <div id="${id}" class="p-4 rounded-xl border border-teal-200 bg-teal-50/20 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 transition">
                    <div class="flex items-start gap-3.5 w-full">
                        <div class="w-14 h-14 rounded-2xl bg-slate-100 border border-slate-200 flex flex-col items-center justify-center flex-shrink-0 shadow-inner">
                            <span class="w-7 h-7 rounded-full border border-slate-300 shadow-sm block" style="background-color: ${colorHex}"></span>
                            <span class="text-[10px] text-slate-600 mt-1 font-bold">${colorName}</span>
                        </div>
                        <div class="flex-grow">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold px-2.5 py-0.5 bg-teal-100 text-teal-800 rounded-md">موعد مجدول</span>
                                <span class="text-xs text-slate-500 font-bold" dir="ltr">${time}</span>
                            </div>
                            <h3 class="font-bold text-slate-800 mt-1.5 text-base">${name}</h3>
                            <div class="text-xs text-slate-600 mt-2 bg-white p-3 rounded-xl border border-slate-100 grid grid-cols-2 gap-2">
                                <p><span class="text-slate-400">اسم الدواء:</span> <strong class="text-slate-800">${name}</strong></p>
                                <p><span class="text-slate-400">الجرعة:</span> <strong class="text-teal-700">${dose}</strong></p>
                                <p><span class="text-slate-400">شكل الدواء:</span> <strong class="text-slate-800">${shape}</strong></p>
                                <p class="flex items-center gap-1.5"><span class="text-slate-400">لون الدواء:</span> <strong class="text-slate-800">${colorName}</strong> <span class="w-3 h-3 rounded-full border border-slate-400 inline-block" style="background-color: ${colorHex}"></span></p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 w-full md:w-auto justify-end pt-2 md:pt-0 border-t md:border-t-0 border-slate-100">
                        <button onclick="triggerAlarm('${name}', '${shape}', '${colorName}', '${colorHex}', '${dose}')" class="p-2 text-teal-700 bg-teal-50 rounded-lg hover:bg-teal-100">🔊</button>
                        <button onclick="dismissAlert('${id}')" class="px-3 py-2 text-xs font-semibold bg-white border border-slate-200 text-slate-600 rounded-lg">تحديد كمقروء</button>
                        <button onclick="confirmDose('${id}', '${name}')" class="px-4 py-2 text-xs font-bold bg-teal-600 text-white rounded-lg hover:bg-teal-700">تناولت الجرعة</button>
                    </div>
                </div>
            `;
            list.insertAdjacentHTML('afterbegin', card);
            updateBadge();
            closeModal();
            e.target.reset();
            speakArabic('تمت إضافة دواء ' + name + ' بنجاح');
        }
    </script>
</body>
</html>