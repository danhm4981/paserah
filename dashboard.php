<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';

// معرف المريض (من الجلسة أو رقم افتراضي للتجربة)
$patient_id = $_SESSION['user_id'] ?? 1; 
$today = date('Y-m-d');

$total_meds = 0;
$today_doses = 0;
$taken_doses = 0;
$pending_doses = 0;
$doses_result = [];

if (isset($pdo)) {
    try {
        // 1. إجمالي عدد الأدوية للمريض
        $stmt_med = $pdo->prepare("SELECT COUNT(*) AS total_meds FROM medications WHERE user_id = ?");
        $stmt_med->execute([$patient_id]);
        $total_meds = $stmt_med->fetch()['total_meds'] ?? 0;

        // 2. إحصائيات جرعات اليوم
        $stmt_doses = $pdo->prepare("
            SELECT COUNT(*) AS today_doses,
                   SUM(CASE WHEN ds.status = 'taken' THEN 1 ELSE 0 END) AS taken_doses,
                   SUM(CASE WHEN ds.status != 'taken' THEN 1 ELSE 0 END) AS pending_doses
            FROM dosage_schedules ds
            JOIN medications m ON ds.medication_id = m.medication_id
            WHERE m.user_id = ? AND ds.dosage_date = ?
        ");
        $stmt_doses->execute([$patient_id, $today]);
        $doses_stats = $stmt_doses->fetch();

        if ($doses_stats) {
            $today_doses = $doses_stats['today_doses'] ?? 0;
            $taken_doses = $doses_stats['taken_doses'] ?? 0;
            $pending_doses = $doses_stats['pending_doses'] ?? 0;
        }

        // 3. جلب قائمة جرعات اليوم
        $stmt_list = $pdo->prepare("
            SELECT ds.schedule_id, m.medication_name, ds.dose_amount, ds.dosage_time, ds.status 
            FROM dosage_schedules ds 
            JOIN medications m ON ds.medication_id = m.medication_id 
            WHERE m.user_id = ? AND ds.dosage_date = ?
            ORDER BY ds.dosage_time ASC
        ");
        $stmt_list->execute([$patient_id, $today]);
        $doses_result = $stmt_list->fetchAll();
    } catch (PDOException $e) {
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بصيرة - لوحة المريض</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #008080;
            --primary-dark: #005a5a;
            --bg-light: #f8fcfc;
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
            direction: rtl;
        }
        header {
            background-color: var(--white);
            padding: 15px 5%;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            position: sticky;
            top: 0;
            z-index: 1000;
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

        .dashboard-layout {
            display: flex;
            min-height: calc(100vh - 140px);
            max-width: 1400px;
            margin: 0 auto;
        }

        /*  القائمة الجانبية */
        .sidebar {
            width: 260px;
            background-color: var(--white);
            padding: 30px 20px;
            box-shadow: -4px 0 20px rgba(0, 0, 0, 0.03);
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            flex-shrink: 0;
        }

        .sidebar-header h2 {
            color: var(--text-color);
            font-size: 20px;
            margin-bottom: 30px;
            padding-right: 15px;
            font-weight: bold;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li {
            margin-bottom: 10px;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 14px 18px;
            color: #557075;
            text-decoration: none;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .sidebar-menu li.active a {
            background-color: #e6f4f4;
            color: var(--primary-color);
            font-weight: bold;
        }

        .sidebar-menu li a:hover {
            background-color: #f4f9f9;
            color: var(--primary-color);
        }

        .sidebar-menu li.logout {
            margin-top: 40px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .sidebar-menu li.logout a {
            color: #d9534f;
        }

        .sidebar-menu li.logout a:hover {
            background-color: #fdf2f2;
        }

        .main-content {
            flex-grow: 1;
            padding: 40px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--white);
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        }

        .stat-card h2 {
            color: var(--primary-color);
            font-size: 28px;
        }

        .stat-card p {
            color: var(--text-muted);
            font-size: 14px;
        }

        .doses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .dose-card {
            background: var(--white);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        }

        .dose-card h3 {
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .dose-info, .dose-time {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 5px;
        }

        .status-btn {
            width: 100%;
            padding: 10px;
            border-radius: 20px;
            cursor: pointer;
            margin-top: 15px;
            font-weight: 600;
        }

        .status-btn.taken {
            background-color: var(--primary-color);
            color: white;
            border: none;
        }

        .status-btn.pending {
            background-color: transparent;
            border: 1.5px solid var(--primary-color);
            color: var(--primary-color);
        }

        .empty-notice {
            background: var(--white);
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            color: var(--text-muted);
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            grid-column: 1 / -1;
        }

        footer {
            background-color: var(--primary-color);
            color: var(--white);
            text-align: center;
            padding: 18px;
            font-size: 14px;
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

    <!-- لوحة التحكم -->
    <div class="dashboard-layout">
        <!-- القائمة الجانبية -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>لوحة المريض</h2>
            </div>
            <ul class="sidebar-menu">
                <li class="active"><a href="#"><span class="icon">🏠</span> الرئيسية</a></li>
                <li><a href="medications.php"><span class="icon">💊</span> أدويتي</a></li>
                <li><a href="add-medication.php"><span class="icon">➕</span> إضافة دواء</a></li>
                <li><a href="notifications.php"><span class="icon">🔔</span> التنبيهات</a></li>
                <li><a href="dependents.php"><span class="icon">👥</span> التابع</a></li>
                <li><a href="subscription.php"><span class="icon">⭐</span> الاشتراك</a></li>
                <li><a href="profile.php"><span class="icon">⚙️</span> الملف الشخصي</a></li>
                <li class="logout"><a href="logout.php"><span class="icon">🚪</span> تسجيل الخروج</a></li>
            </ul>
        </aside>

        <!-- الترحيب-->
        <main class="main-content">
            
            <header class="welcome-header" style="margin-bottom: 25px;">
                <h1 style="font-size: 24px; color: var(--text-color);">مرحبًا بك في بصيرة 👋</h1>
                <p style="color: var(--text-muted);">تابع أدويتك وجرعاتك بسهولة</p>
            </header>

            <!--كروت الاحصائيات-->
            <section class="stats-grid">
                <div class="stat-card">
                    <h2><?php echo $total_meds; ?></h2>
                    <p>عدد الأدوية</p>
                </div>
                <div class="stat-card">
                    <h2><?php echo $today_doses; ?></h2>
                    <p>جرعات اليوم</p>
                </div>
                <div class="stat-card">
                    <h2><?php echo $taken_doses; ?></h2>
                    <p>تم تناولها</p>
                </div>
                <div class="stat-card">
                    <h2><?php echo $pending_doses; ?></h2>
                    <p>المتبقية</p>
                </div>
            </section>

            <!-- كروت الجرعات اليوميه -->
            <section class="doses-section">
                <h2 style="font-size: 20px; color: var(--text-color); margin-bottom: 20px;">جرعات اليوم</h2>
                <div class="doses-grid">
                    <?php if (!empty($doses_result)): ?>
                        <?php foreach($doses_result as $row): ?>
                            <div class="dose-card">
                                <h3><?php echo htmlspecialchars($row['medication_name']); ?></h3>
                                <p class="dose-info">الجرعة: <?php echo htmlspecialchars($row['dose_amount']); ?></p>
                                <p class="dose-time">الوقت: <?php echo date("h:i A", strtotime($row['dosage_time'])); ?></p>
                                
                                <?php if ($row['status'] == 'taken'): ?>
                                    <button class="status-btn taken">تم تناول الجرعة</button>
                                <?php else: ?>
                                    <button class="status-btn pending">بانتظار الموعد</button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-notice">
                            <p>لا توجد جرعات مجدولة لهذا اليوم.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

        </main>
    </div>

    <footer>
        <p>©️ <?php echo date("Y"); ?> بصيرة - جميع الحقوق محفوظة</p>
    </footer>

</body>
</html>