<?php
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user_id = $_SESSION['user_id'] ?? 1;

$message = "";
$error = "";

// التعامل مع تحديث أو إضافة الاشتراك
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subscribe_plan'])) {
    $plan_type = $_POST['plan_type']; // free, monthly, yearly
    $start_date = date('Y-m-d');
    
    // حساب تاريخ الانتهاء بناءً على نوع الباقة
    if ($plan_type === 'monthly') {
        $end_date = date('Y-m-d', strtotime('+1 month'));
    } elseif ($plan_type === 'yearly') {
        $end_date = date('Y-m-d', strtotime('+1 year'));
    } else {
        $end_date = NULL; // الباقة المجانية بدون تاريخ انتهاء
    }

    $status = 'active';

    // إلغاء أي اشتراك نشط سابق
    $stmt_cancel = $conn->prepare("UPDATE subscriptions SET subscription_status = 'cancelled' WHERE user_id = ? AND subscription_status = 'active'");
    $stmt_cancel->bind_param("i", $user_id);
    $stmt_cancel->execute();

    // إضافة الاشتراك الجديد
    $stmt_sub = $conn->prepare("INSERT INTO subscriptions (user_id, plan_type, start_date, end_date, subscription_status) VALUES (?, ?, ?, ?, ?)");
    $stmt_sub->bind_param("issss", $user_id, $plan_type, $start_date, $end_date, $status);

    if ($stmt_sub->execute()) {
        $message = "تم تفعيل الاشتراك بنجاح!";
    } else {
        $error = "حدث خطأ أثناء تفعيل الاشتراك.";
    }
}

// جلب الاشتراك الحالي
$stmt_curr = $conn->prepare("SELECT * FROM subscriptions WHERE user_id = ? AND subscription_status = 'active' ORDER BY subscription_id DESC LIMIT 1");
$stmt_curr->bind_param("i", $user_id);
$stmt_curr->execute();
$current_sub = $stmt_curr->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>باقات الاشتراك - بصيرة</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #008080;
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

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .title-section {
            text-align: center;
            margin-bottom: 40px;
        }

        .title-section h1 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }

        .plan-card {
            background: var(--white);
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 2px solid transparent;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .plan-card.active-plan {
            border-color: var(--primary-color);
            background-color: #f0fdfd;
        }

        .plan-card:hover {
            transform: translateY(-5px);
        }

        .plan-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .plan-price {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 20px;
        }

        .plan-features {
            list-style: none;
            margin-bottom: 25px;
            text-align: right;
        }

        .plan-features li {
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
            color: var(--text-color);
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

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
                <li><a href="#features">المميزات</a></li> 
                <li><a href="contact.php">تواصل معنا</a></li>
            </ul>
            <div class="buttons">
                <a href="register.php" class="btn btn-outline">إنشاء حساب</a>
                <a href="login.php" class="btn btn-primary">تسجيل الدخول</a>
            </div>
        </nav>
    </header>

    <div class="container">
        <div class="title-section">
            <h1>خطط وباقات الاشتراك</h1>
            <p>اختر الخطة المناسبة لك للاستفادة الكاملة من مميزات تطبيق بصيرة</p>
        </div>

        <?php if ($message): ?>
            <div class="alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="plans-grid">
            <!-- الباقة المجانية -->
            <div class="plan-card <?php echo ($current_sub && $current_sub['plan_type'] == 'free') ? 'active-plan' : ''; ?>">
                <div>
                    <div class="plan-title">الباقة المجانية</div>
                    <div class="plan-price">مجاناً</div>
                    <ul class="plan-features">
                        <li>إضافة الأدوية ومواعيد الجرعات</li>
                        <li>تنبيهات صوتية ومرئية بسيطة</li>
                        <li>ربط تابع واحد فقط</li>
                    </ul>
                </div>
                <form action="" method="POST">
                    <input type="hidden" name="plan_type" value="free">
                    <button type="submit" name="subscribe_plan" class="btn btn-outline" style="width: 100%;">
                        <?php echo ($current_sub && $current_sub['plan_type'] == 'free') ? 'خطة حالية' : 'اختيار الباقة'; ?>
                    </button>
                </form>
            </div>

            <!-- الباقة الشهرية -->
            <div class="plan-card <?php echo ($current_sub && $current_sub['plan_type'] == 'monthly') ? 'active-plan' : ''; ?>">
                <div>
                    <div class="plan-title">الباقة الشهرية</div>
                    <div class="plan-price">29 ر.س / شهرياً</div>
                    <ul class="plan-features">
                        <li>جميع مميزات الباقة المجانية</li>
                        <li>تنبيهات صوتية مخصصة</li>
                        <li>إضافة عدد لا محدود من التوابع</li>
                        <li>سجل تقارير تفصيلي لتناول الأدوية</li>
                    </ul>
                </div>
                <form action="" method="POST">
                    <input type="hidden" name="plan_type" value="monthly">
                    <button type="submit" name="subscribe_plan" class="btn btn-primary" style="width: 100%;">
                        <?php echo ($current_sub && $current_sub['plan_type'] == 'monthly') ? 'خطة حالية' : 'اشترك الآن'; ?>
                    </button>
                </form>
            </div>

            <!-- الباقة السنوية -->
            <div class="plan-card <?php echo ($current_sub && $current_sub['plan_type'] == 'yearly') ? 'active-plan' : ''; ?>">
                <div>
                    <div class="plan-title">الباقة السنوية</div>
                    <div class="plan-price">299 ر.س / سنوياً</div>
                    <ul class="plan-features">
                        <li>جميع مميزات الباقة الشهرية</li>
                        <li>توفير أكثر من 15% سنوياً</li>
                        <li>دعم فني مباشر ومميز</li>
                        <li>ربط وتكامل مع منصة صحتي</li>
                    </ul>
                </div>
                <form action="" method="POST">
                    <input type="hidden" name="plan_type" value="yearly">
                    <button type="submit" name="subscribe_plan" class="btn btn-primary" style="width: 100%;">
                        <?php echo ($current_sub && $current_sub['plan_type'] == 'yearly') ? 'خطة حالية' : 'اشترك الآن'; ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <footer>
        <p>©️ <?php echo date("Y"); ?> بصيرة - جميع الحقوق محفوظة</p>
    </footer>

</body>
</html>
