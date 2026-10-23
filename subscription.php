<?php
require_once __DIR__ . '/db.php';
include __DIR__ . '/includes/header.php';

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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
 <style>
        body {
            background-color: var(--bg-light);
            color: var(--text-color);
        }
        .dashboard-layout {
    display: flex;
    min-height: calc(100vh - 140px);
    max-width: 1400px;
    margin: 0 auto;
    width: 100%;
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
<div class="dashboard-layout">
            <?php include __DIR__ . '/includes/sidebar.php';?>
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

     </div>
    <?php include __DIR__ . '/includes/footer.php';?>
</body>
</html>
