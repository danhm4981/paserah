<?php
// تفعيل التخزين المؤقت للمخرجات لتفادي مشاكل الـ Headers
ob_start(); 

// استدعاء ملف قاعدة البيانات إن وجد
if (file_exists(__DIR__ . '/db.php')) {
    require_once __DIR__ . '/db.php';
}

// بدء الجلسة لتدميرها
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تدمير بيانات الجلسة (تسجيل الخروج الفعلي)
$_SESSION = array();
session_destroy();

// تحويل تلقائي للرئيسية بعد 4 ثوانٍ ليتمكن المستخدم من رؤية صفحتك وتصميمك
header("Refresh: 4; url=index.php"); 
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>بصيرة - تم تسجيل الخروج</title>
    <style>
<<<<<<< HEAD
        
        .dashboard-layout {
    display: flex;
    min-height: calc(100vh - 140px);
    max-width: 1400px;
    margin: 0 auto;
    width: 100%;
}
    </style>
</head>
<body>
     <div class="dashboard-layout">
            <?php include __DIR__ . '/includes/sidebar.php';?>
</div>
   <?php include __DIR__ . '/includes/footer.php';?>

=======
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

        /* تنسيق محتوى وسط الصفحة الخاص بالتأكيد */
        .logout-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .logout-card {
            background-color: var(--white);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }

        .logout-card h2 {
            color: var(--primary-color);
            margin-bottom: 15px;
            font-size: 24px;
        }

        .logout-card p {
            color: var(--text-muted);
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 25px;
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
            margin-top: auto; /* ليبقى الفوتر ملتصقاً بالأسفل تماماً */
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
                <!-- تم إرجاع الأزرار هنا وتعديلها لتظهر كحالة زائر خرج للتو -->
                <a href="register.php" class="btn btn-outline">إنشاء حساب</a>
                <a href="login.php" class="btn btn-primary">تسجيل الدخول</a>
            </div>
        </nav>
    </header>

    <div class="logout-container">
        <div class="logout-card">
            <h2>تم تسجيل الخروج بنجاح!</h2>
            <p>شكراً لاستخدامك منصة بصيرة. لقد تم إنهاء جلستك بأمان، وسيتم تحويلك إلى الصفحة الرئيسية الآن...</p>
            <div class="loader"></div>
        </div>
    </div>

    <footer>
        <p>©️ <?php echo date("Y"); ?> بصيرة - جميع الحقوق محفوظة</p>
    </footer>
>>>>>>> 13e589139f5b5777ac133166ef3bd2eb752d5c01

</body>
</html>