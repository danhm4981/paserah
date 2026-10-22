<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بصيرة - الصفحة الرئيسية</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    
    <style>
               html {
    scroll-behavior: smooth;
}
        :root {
            --primary-color: #008080; /* اللون الأساسي */
            --primary-dark: #005a5a;
            --bg-light: #f4fbfb;
            --text-color: #2b3e42;
            --text-muted: #6c7a7d;
            --white: #ffffff;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Tajawal', sans-serif;
        }

        body {
            background-color: var(--bg-light);
            color: var(--text-color);
            line-height: 1.6;
        }

        header {
            background-color: var(--white);
            padding: 15px 5%;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 24px;
            font-weight: 800;
            color: var(--primary-color);
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 25px;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text-color);
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .nav-links a:hover {
            color: var(--primary-color);
        }

        .btn {
            padding: 8px 22px;
            border-radius: 20px;
            font-weight: 600;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
            cursor: pointer;
            display: inline-block;
        }

        .btn-outline {
            border: 1.5px solid var(--primary-color);
            color: var(--primary-color);
            background: transparent;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: var(--white);
            border: 1.5px solid var(--primary-color);
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        /* ---  المين--- */
        .hero {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 5%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            min-height: calc(100vh - 80px); }

       
        .hero-image img {
            max-width: 290px;
            width: 100%;
            height: auto;
            padding: 10px;
            mix-blend-mode: multiply;
            filter: drop-shadow(0px 15px 20px rgba(0, 80, 80, 0.15));
            transition: transform 0.3s ease, filter 0.3s ease;
        }

       
        .hero-image img:hover {
            transform: translateY(-5px);
            filter: drop-shadow(0px 20px 25px rgba(0, 80, 80, 0.22));
        }
        
        .hero-text h1 {
            font-size: 38px;
            color: var(--primary-color);
            font-weight: 800;
            margin-bottom: 15px;
            line-height: 1.3;
        }

        .hero-text p {
            color: var(--text-muted);
            font-size: 16px;
            margin-bottom: 25px;
            max-width: 420px;
            line-height: 1.6;
        }

        .hero-text .btn-primary {
            width: auto;
            padding: 10px 28px;
        }

        /* --- المميزات --- */
.features-section {
    max-width: 1200px;
    margin: 40px auto 60px auto; 
    padding: 0 5%;
    text-align: center;
}

        .section-title {
            font-size: 28px;
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 8px;
        }

        .section-subtitle {
            color: var(--text-muted);
            font-size: 15px;
            margin-bottom: 40px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 20px;
        }

        .feature-card {
            background: var(--white);
            padding: 30px 20px;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 128, 128, 0.08);
        }

        .feature-icon {
            width: 55px;
            height: 55px;
            background-color: #e8f7f7;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            font-size: 24px;
        }

        .feature-card h3 {
            font-size: 18px;
            color: var(--primary-color);
            margin-bottom: 10px;
            font-weight: 700;
        }

        .feature-card p {
            color: var(--text-muted);
            font-size: 13px;
        }

        /* --- بنر --- */
        .banner {
            max-width: 1200px;
            margin: 50px auto;
            padding: 35px;
            background: var(--white);
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
        }

        .banner h2 {
            color: var(--primary-color);
            font-size: 22px;
            margin-bottom: 8px;
        }

        .banner p {
            color: var(--text-muted);
            font-size: 14px;
        }

        /* --- فوتر --- */
        footer {
            background-color: var(--primary-color);
            color: var(--white);
            text-align: center;
            padding: 18px;
            font-size: 14px;
             margin-top: 60px;

        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <img src="logo.png" alt="شعار بصيرة" style="height: 45px; width: auto; object-fit: contain;">
                <span>بصيرة</span>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">الرئيسية</a></li>
                <li><a href="about.php">عن بصيرة</a></li>
                <li><a href="index.php#features">المميزات</a></li>
                <li><a href="contact.php">تواصل معنا</a></li>
            </ul>
            <div class="buttons">
                <a href="register.php" class="btn btn-outline">إنشاء حساب</a>
                <a href="login.php" class="btn btn-primary">تسجيل الدخول</a>
            </div>
        </nav>
    </header>

    <section class="hero">
        <div class="hero-image">
            <img src="img0.png" alt="تطبيق بصيرة">
        </div>
        <div class="hero-text">
            <h1>معًا .. لنكن أكثر التزامًا بأدويتنا</h1>
            <p>موقع بصيرة يساعدك على تذكر مواعيد أدويتك من خلال تنبيهات صوتية والمرئية، وتنظيم الجرعات ومتابعتها بسهولة.</p>
            <a href="register.php" class="btn btn-primary">ابدأ الآن ←</a>
        </div>
    </section>

    <!-- المميزات -->
    <section id="features" class="features-section"> 
        <h2 class="section-title">مميزات بصيرة</h2>
        <p class="section-subtitle">كل ما تحتاجه لتنظيم أدويتك ومواعيد جرعاتك</p>
    
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">🔔</div>
                <h3>تنبيهات صوتية ومرئية</h3>
                <p>لتذكيرك بالدواء في الوقت المناسب من خلال التنبيهات.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💊</div>
                <h3>إدارة الأدوية</h3>
                <p>إضافة الأدوية وتحديد الجرعات والمواعيد بسهولة.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">👥</div>
                <h3>ربط المريض بالمتابع</h3>
                <p>إمكانية ربط حساب المريض بالمتابع لمتابعة الأدوية والجرعات.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🛡️</div>
                <h3>سهل الاستخدام</h3>
                <p>تصميم بسيط وواضح يناسب مختلف الفئات العمرية.</p>
            </div>
        </div>
    </section>

    <!-- بنر -->
    <div class="banner">
        <h2>لأن صحتك تهمنا ..</h2>
        <p>بصيرة .. عون لك في رحلتك مع أدويتك.</p>
    </div>

    <!-- الفوتر -->
    <footer>
        <p>© <?php echo date("Y"); ?> بصيرة - جميع الحقوق محفوظة</p>
    </footer>

</body>
</html>