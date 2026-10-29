<?php
<<<<<<< HEAD
=======
// تفعيل التخزين المؤقت للمخرجات لتفادي مشاكل الـ Headers
ob_start(); 

// استدعاء ملف قاعدة البيانات إن وجد
if (file_exists(__DIR__ . '/db.php')) {
    require_once __DIR__ . '/db.php';
}

// بدء الجلسة للتحقق من حالة تسجيل الخروج
>>>>>>> 45347a2b3be33be39e37081ed6a1c11ccdfb7c38
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

<<<<<<< HEAD
if (file_exists(__DIR__ . '/db.php')) {
    require_once __DIR__ . '/db.php';
}

$logged_out_successfully = false;

// الصفحة التي يرجع لها المستخدم عند الإلغاء (حسب نوع حسابه)
$back_url = (($_SESSION['account_type'] ?? '') === 'dependent') ? 'dep-dashboard.php' : 'dashboard.php';

// معالجة الطلب عند الضغط على زر "تأكيد الخروج"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_logout'])) {
    // تدمير بيانات الجلسة (تسجيل الخروج الفعلي)
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
    $logged_out_successfully = true;

    // تحويل تلقائي للرئيسية بعد 3 ثوانٍ
    header("Refresh: 3; url=index.php");
} elseif (!isset($_SESSION['user_id'])) {
    // من لم يسجل دخوله أصلاً لا يحتاج صفحة الخروج
    header("Location: login.php");
    exit;
}

// الهيدر بعد المعالجة حتى تعمل header() بشكل صحيح
include __DIR__ . '/includes/header.php';
=======
$logged_out_successfully = false;

// معالجة الطلب عند الضغط على زر "تأكيد الخروج"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_logout'])) {
    // تدمير بيانات الجلسة (تسجيل الخروج الفعلي)
    $_SESSION = array();
    session_destroy();
    $logged_out_successfully = true;
    
    // تحويل تلقائي للرئيسية بعد 3 ثوانٍ فقط بعد التأكيد
    header("Refresh: 3; url=index.php"); 
}
>>>>>>> 45347a2b3be33be39e37081ed6a1c11ccdfb7c38
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
<<<<<<< HEAD
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بصيرة - تسجيل الخروج</title>

    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        .dashboard-layout {
            display: flex;
            align-items: stretch;            /* السايدبار يمتد حتى نهاية الصفحة */
            min-height: calc(100vh - 140px);
            width: 100%;
        }

        /* منطقة المحتوى: تأخذ المساحة المتبقية بجانب السايدبار وتوسّط البطاقة أفقياً وعمودياً */
        .logout-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
            box-sizing: border-box;
        }

        .logout-card {
            background-color: var(--white, #fff);
=======
    <title>بصيرة - تسجيل الخروج</title>
    <style>
        :root {
            --primary-color: #008080; /* اللون الأساسي */
            --primary-dark: #005a5a;
            --bg-light: #f4fbfb;
            --text-color: #2b3e42;
            --text-muted: #6c7a7d;
            --white: #ffffff;
            --danger-color: #d9534f;
            --danger-dark: #c9302c;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Tajawal', sans-serif;
        }

        body {
            background-color: var(--bg-light);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
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
            width: 100%;
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

        .buttons {
            display: flex;
            gap: 12px;
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
            border: none;
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

        .btn-danger {
            background-color: var(--danger-color);
            color: var(--white);
            border: 1.5px solid var(--danger-color);
        }

        .btn-danger:hover {
            background-color: var(--danger-dark);
            border-color: var(--danger-dark);
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        /* تنسيق محتوى وسط الصفحة */
        .logout-container {
            position:flxed;
            top:0;
            right:0;
            bottom:0;
            left:0;

            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            z-index:9999;
        }

        .logout-card {
            background-color: var(--white);
>>>>>>> 45347a2b3be33be39e37081ed6a1c11ccdfb7c38
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            text-align: center;
            max-width: 500px;
            width: 100%;
<<<<<<< HEAD
        }

        .logout-icon {
            font-size: 44px;
            margin-bottom: 10px;
        }

        .logout-card h2 {
            color: var(--text-color, #2b3e42);
=======
            margin:auto;
        }

        .logout-card h2 {
            color: var(--text-color);
>>>>>>> 45347a2b3be33be39e37081ed6a1c11ccdfb7c38
            margin-bottom: 15px;
            font-size: 24px;
        }

        .logout-card h2.success-title {
<<<<<<< HEAD
            color: var(--primary-color, #008080);
        }

        .logout-card p {
            color: var(--text-muted, #557075);
=======
            color: var(--primary-color);
        }

        .logout-card p {
            color: var(--text-muted);
>>>>>>> 45347a2b3be33be39e37081ed6a1c11ccdfb7c38
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .logout-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
<<<<<<< HEAD
            flex-wrap: wrap;
        }

        /* أزرار الصفحة (مستقلة حتى تظهر دائماً بغض النظر عن ملف CSS العام) */
        .logout-btn,
        .cancel-btn {
            display: inline-block;
            min-width: 150px;
            padding: 12px 28px;
            border-radius: 25px;
            font-family: inherit;
            font-size: 15px;
            font-weight: 700;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }

        .logout-btn {
            background-color: var(--danger-color, #d9534f);
            color: #fff;
            border: 1.5px solid var(--danger-color, #d9534f);
        }

        .logout-btn:hover {
            background-color: var(--danger-dark, #c0392b);
            border-color: var(--danger-dark, #c0392b);
            transform: translateY(-1px);
        }

        .cancel-btn {
            background-color: transparent;
            color: var(--primary-color, #008080);
            border: 1.5px solid var(--primary-color, #008080);
        }

        .cancel-btn:hover {
            background-color: var(--primary-color, #008080);
            color: #fff;
            transform: translateY(-1px);
=======
>>>>>>> 45347a2b3be33be39e37081ed6a1c11ccdfb7c38
        }

        .loader {
            border: 3px solid #f3f3f3;
<<<<<<< HEAD
            border-top: 3px solid var(--primary-color, #008080);
=======
            border-top: 3px solid var(--primary-color);
>>>>>>> 45347a2b3be33be39e37081ed6a1c11ccdfb7c38
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

<<<<<<< HEAD
        @media (max-width: 768px) {
            .logout-card { padding: 30px 20px; }
            .logout-btn, .cancel-btn { width: 100%; }
=======
        footer {
            background-color: var(--primary-color);
            color: var(--white);
            text-align: center;
            padding: 18px;
            font-size: 14px;
            margin-top: auto;
>>>>>>> 45347a2b3be33be39e37081ed6a1c11ccdfb7c38
        }
    </style>
</head>
<body>

<<<<<<< HEAD
    <div class="dashboard-layout">
        <?php if (!$logged_out_successfully): ?>
            <?php include __DIR__ . '/includes/sidebar.php'; ?>
        <?php endif; ?>

        <div class="logout-container">
            <div class="logout-card">
                <?php if (!$logged_out_successfully): ?>
                    <!-- حالة طلب التأكيد قبل الخروج -->
                    <div class="logout-icon">🚪</div>
                    <h2>تأكيد تسجيل الخروج</h2>
                    <p>هل أنت متأكد من رغبتك في تسجيل الخروج من حسابك في منصة بصيرة؟</p>

                    <form method="POST" action="">
                        <div class="logout-actions">
                            <button type="submit" name="confirm_logout" class="logout-btn">تسجيل الخروج</button>
                            <a href="<?php echo htmlspecialchars($back_url, ENT_QUOTES, 'UTF-8'); ?>" class="cancel-btn">إلغاء</a>
                        </div>
                    </form>
                <?php else: ?>
                    <!-- حالة نجاح الخروج بعد الضغط على الزر -->
                    <h2 class="success-title">تم تسجيل الخروج بنجاح!</h2>
                    <p>شكراً لاستخدامك منصة بصيرة. لقد تم إنهاء جلستك بأمان، جاري تحويلك الآن...</p>
                    <div class="loader"></div>

                    <script>
                        setTimeout(function() {
                            window.location.href = 'index.php';
                        }, 3000);
                    </script>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>
=======
    <header>
        <nav>
            <div class="logo">
                <img src="logo.png" alt="شعار بصيرة" style="height: 45px; width: auto; object-fit: contain;">
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

    <div class="logout-container">
        <div class="logout-card">
            <?php if (!$logged_out_successfully): ?>
                <!-- حالة طلب التأكيد قبل الخروج -->
                <h2>تأكيد تسجيل الخروج</h2>
                <p>هل أنت متأكد من رغبتك في تسجيل الخروج من حسابك في منصة بصيرة؟</p>
                
                <form method="POST" action="">
                    <div class="logout-actions">
                        <button type="submit" name="confirm_logout" class="btn btn-danger">تأكيد الخروج</button>
                        <a href="index.php" class="btn btn-outline">إلغاء</a>
                    </div>
                </form>
            <?php else: ?>
                <!-- حالة نجاح الخروج بعد الضغط على الزر -->
                <h2 class="success-title">تم تسجيل الخروج بنجاح!</h2>
                <p>شكراً لاستخدامك منصة بصيرة. لقد تم إنهاء جلستك بأمان، جاري تحويلك الآن...</p>
                <div class="loader"></div>

                <script>
                    setTimeout(function() {
                        window.location.href = 'index.php';
                    }, 3000);
                </script>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>©️ <?php echo date("Y"); ?> بصيرة - جميع الحقوق محفوظة</p>
    </footer>
>>>>>>> 45347a2b3be33be39e37081ed6a1c11ccdfb7c38

</body>
</html>
<?php
ob_end_flush(); 
?>