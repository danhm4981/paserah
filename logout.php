<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
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
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }

        .logout-icon {
            font-size: 44px;
            margin-bottom: 10px;
        }

        .logout-card h2 {
            color: var(--text-color, #2b3e42);
            margin-bottom: 15px;
            font-size: 24px;
        }

        .logout-card h2.success-title {
            color: var(--primary-color, #008080);
        }

        .logout-card p {
            color: var(--text-muted, #557075);
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .logout-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
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
        }

        .loader {
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--primary-color, #008080);
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

        @media (max-width: 768px) {
            .logout-card { padding: 30px 20px; }
            .logout-btn, .cancel-btn { width: 100%; }
        }
    </style>
</head>
<body>

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

</body>
</html>
