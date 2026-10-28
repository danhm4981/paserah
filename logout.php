<?php
// تفعيل التخزين المؤقت للمخرجات لتفادي مشاكل الـ Headers
ob_start(); 

// استدعاء ملف قاعدة البيانات إن وجد
if (file_exists(__DIR__ . '/db.php')) {
    require_once __DIR__ . '/db.php';
}

// بدء الجلسة للتحقق من حالة تسجيل الخروج
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
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
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            text-align: center;
            max-width: 500px;
            width: 100%;
            margin:auto;
        }

        .logout-card h2 {
            color: var(--text-color);
            margin-bottom: 15px;
            font-size: 24px;
        }

        .logout-card h2.success-title {
            color: var(--primary-color);
        }

        .logout-card p {
            color: var(--text-muted);
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .logout-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .loader {
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--primary-color);
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

        footer {
            background-color: var(--primary-color);
            color: var(--white);
            text-align: center;
            padding: 18px;
            font-size: 14px;
            margin-top: auto;
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

</body>
</html>
<?php
ob_end_flush(); 
?>