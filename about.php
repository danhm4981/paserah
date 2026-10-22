<?php 
require_once __DIR__. '/db.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>عن بصيرة | Basirah</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
    --primary-color:#008080; --primary-dark:#005a5a;
    --bg-light:#f4fbfb; --text-color:#2b3e42;
    --text-muted:#6c7a7d; --white:#ffffff;
}
* { box-sizing:border-box; margin:0; padding:0; font-family:'Tajawal',sans-serif; }
body{
    background-color: #f4fbfb
}
/* Header */
header { background:var(--white); padding:15px 5%; box-shadow:0 2px 10px rgba(0,0,0,.03); }
nav { display:flex; justify-content:space-between; align-items:center; max-width:1200px; margin:auto; }
.logo { display:flex; align-items:center; gap:10px; font-size:24px; font-weight:800; color:var(--primary-color); }
.nav-links { display:flex; list-style:none; gap:25px; }
.nav-links a { text-decoration:none; color:var(--text-color); font-weight:500; transition:color .3s; }
.nav-links a:hover { color:var(--primary-color); }
.buttons { display:flex; gap:12px; }
.btn { padding:8px 22px; border-radius:20px; font-weight:600; text-decoration:none; font-size:14px; transition:all .3s; cursor:pointer; display:inline-block; }
.btn-outline { border:1.5px solid var(--primary-color); color:var(--primary-color); background:transparent; }
.btn-primary { background:var(--primary-color); color:var(--white); border:1.5px solid var(--primary-color); }
.btn:hover { opacity:.9; transform:translateY(-1px); }
/* Page Content */
main { max-width:1200px; margin:auto; padding:40px 5%; }
.about-hero { background:var(--white); padding:50px 35px; text-align:center; border-radius:15px; margin-bottom:35px; }
.about-hero h1 { color:var(--primary-color); font-size:36px; margin-bottom:15px; }
.about-hero p,.section-description { color:var(--text-muted); line-height:1.9; }
.about-hero p { font-size:18px; max-width:750px; margin:auto; }
.content-section { margin-bottom:40px; }
.section-title { color:var(--primary-color); font-size:27px; text-align:center; margin-bottom:12px; }
.section-description { text-align:center; margin-bottom:25px; }
.about-box { background:var(--white); padding:30px; border-radius:12px; border:1px solid var(--bg-light); line-height:2; }
.about-box p { color:var(--text-color); margin-bottom:15px; }
.about-box p:last-child { margin-bottom:0; }
.cards-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:22px; }
.info-card,.goal-card { background:var(--white); padding:25px; border-radius:12px; border:1px solid var(--bg-light); }
.info-card { border-top:4px solid var(--primary-color); }
.card-icon { font-size:30px; margin-bottom:12px; }
.info-card h3,.goal-card h3,.feature-card h3 { color:var(--primary-color); margin-bottom:10px; }
.info-card h3,.feature-card h3 { font-size:20px; }
.info-card p,.goal-card p,.feature-card p { color:var(--text-muted); line-height:1.9; }
.goals-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:18px; }
.goal-card { text-align:center; padding:24px 18px; }
.goal-card h3 { font-size:18px; margin:12px 0 8px; }
.audience-grid,.features-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:15px; }
.audience-item { background:var(--white); padding:18px 20px; border:1px solid var(--bg-light); border-radius:10px; color:var(--text-color); line-height:1.8; }
.feature-card { background:var(--white); padding:25px; border-radius:12px; }
/* Footer */
footer { background:var(--primary-color); color:var(--white); text-align:center; padding:18px; font-size:14px; margin-top:60px; }

/* Responsive */
@media(max-width:900px) {
    nav { flex-wrap:wrap; gap:18px; }
    .goals-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
}
@media(max-width:600px) {
    nav { flex-direction:column; }
    .nav-links { flex-wrap:wrap; justify-content:center; gap:15px; }
    .buttons { flex-wrap:wrap; justify-content:center; }
    main { padding:25px 5%; }
    .about-hero { padding:35px 20px; }
    .about-hero h1 { font-size:29px; }
    .about-hero p { font-size:16px; }
    .cards-grid,.goals-grid,.audience-grid,.features-grid { grid-template-columns:1fr; }
    .about-box,.info-card { padding:22px; }
}
</style>
</head>
<body>
<header>
<nav>
    <div class="logo"><img src="logo.png" alt="شعار بصيرة" style="height:45px;width:auto;object-fit:contain;">
<span>بصيرة</span>
</div>
    <ul class="nav-links">
        <li><a href="index.php">الرئيسية</a></li>
        <li><a href="about.php">عن بصيرة</a></li>
        <li><a href="#features">المميزات</a></li>
        <li><a href="contact.php">تواصل معنا</a></li>
    </ul>
    <div class="buttons">
        <a href="register.php" class="btn btn-outline">إنشاء حساب</a>
        <a href="login.php" class="btn btn-primary">تسجيل الدخول</a>
    </div>
</nav>
</header>
<main>
<section class="about-hero">
    <h1>عن بصيرة</h1>
    <p>موقع تفاعلي يساعد المرضى على تنظيم أدويتهم وتذكّر مواعيد الجرعات، ويسعى إلى تسهيل متابعة العلاج وتعزيز الالتزام بمواعيد الأدوية.</p>
</section>
<section class="content-section" id="about">
    <h2 class="section-title">من نحن؟</h2>
    <p class="section-description">فكرة مشروع بصيرة وأهميته في تنظيم الأدوية.</p>
    <div class="about-box">
        <p>يهدف مشروع بصيرة إلى مساعدة المرضى، وخاصة كبار السن ومرضى الأمراض المزمنة، على تنظيم أدويتهم وتذكّر مواعيد الجرعات.</p>
        <p>يتيح الموقع إضافة الأدوية وتحديد الجرعات والمواعيد، مع توفير تنبيهات داخل الموقع وعبر البريد الإلكتروني لتذكير المستخدم بمواعيد أدويته.</p>
        <p>كما يتيح ربط حساب المريض بالتابع بعد موافقة الطرفين، بما يساعد على متابعة الأدوية والجرعات وتنظيم العلاج.</p>
    </div>
</section>
<section class="content-section" id="vision">
    <h2 class="section-title">الرؤية والرسالة</h2>
    <p class="section-description">التوجه الذي يسعى المشروع إلى تحقيقه.</p>
    <div class="cards-grid">
        <div class="info-card">
            <div class="card-icon">👁️</div>
            <h3>رؤيتنا</h3>
            <p>المساهمة في تسهيل تنظيم الأدوية ومواعيدها من خلال وسيلة رقمية تساعد المرضى وأسرهم على متابعة العلاج.</p>
        </div>
        <div class="info-card">
            <div class="card-icon">💚</div>
            <h3>رسالتنا</h3>
            <p>توفير موقع يساعد المستخدم على تنظيم أدويته وتذكّر الجرعات، مع إتاحة المتابعة من خلال حساب التابع بعد موافقة المريض.</p>
        </div>
    </div>
</section>
<section class="content-section" id="goals">
    <h2 class="section-title">أهداف المشروع</h2>
    <p class="section-description">الجوانب التي يركز عليها مشروع بصيرة.</p>
    <div class="goals-grid">
        <div class="goal-card"><div class="card-icon">🔔</div><h3>التذكير بالمواعيد</h3><p>تذكير المستخدم بمواعيد الجرعات من خلال التنبيهات.</p></div>
        <div class="goal-card"><div class="card-icon">💊</div><h3>تنظيم الأدوية</h3><p>إضافة الأدوية وتحديد الجرعات والمواعيد الخاصة بها.</p></div>
        <div class="goal-card"><div class="card-icon">✓</div><h3>تقليل النسيان</h3><p>المساعدة على تذكّر الجرعات والحد من الأخطاء الدوائية.</p></div>
        <div class="goal-card"><div class="card-icon">🤝</div><h3>المتابعة</h3><p>تمكين التابع من متابعة أدوية المريض وجرعاته.</p></div>
    </div>
</section>
<section class="content-section" id="audience">
    <h2 class="section-title">الفئة المستهدفة</h2>
    <p class="section-description">الفئات التي يركز المشروع على مساعدتها.</p>
    <div class="audience-grid">
        <div class="audience-item">كبار السن.</div>
        <div class="audience-item">مرضى الأمراض المزمنة.</div>
        <div class="audience-item">المرضى الذين يتناولون عدة أدوية يوميًا.</div>
        <div class="audience-item">الأشخاص الذين يواجهون صعوبة في تذكّر مواعيد الأدوية.</div>
        <div class="audience-item">الأشخاص الذين يواجهون صعوبة في التمييز بين الأدوية.</div>
    </div>
</section>
<section class="content-section" id="features">
    <h2 class="section-title">مميزات بصيرة</h2>
    <p class="section-description">الخدمات الأساسية التي يوفرها الموقع.</p>
    <div class="features-grid">
        <div class="feature-card"><h3>تنظيم الأدوية والجرعات</h3><p>إضافة الأدوية وتحديد الجرعات ومواعيد تناولها.</p></div>
        <div class="feature-card"><h3>التنبيهات</h3><p>توفير تنبيهات داخل الموقع وعبر البريد الإلكتروني.</p></div>
        <div class="feature-card"><h3>حساب المريض والتابع</h3><p>ربط حساب المريض بالتابع بعد موافقة الطرفين.</p></div>
        <div class="feature-card"><h3>متابعة الأدوية</h3><p>إتاحة متابعة أدوية المريض وجرعاته من خلال التابع.</p></div>
    </div>
</section>
</main>
<footer>
<<<<<<< HEAD
        <p>© <?php echo date("Y"); ?> بصيرة - جميع الحقوق محفوظة</p>
=======
    <p>©️ <?php echo date("Y"); ?> بصيرة - جميع الحقوق محفوظة</p>
>>>>>>> 4b0c9594aa9b30011ad8cf39a00d8ef1f0086cec
</footer>
</body>
</html>