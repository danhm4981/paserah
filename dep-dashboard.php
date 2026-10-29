<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
include __DIR__ . '/includes/header.php';

// التحقق من وجود معرف المستخدم في الجلسة
$current_user_id = $_SESSION['user_id'] ?? null;

if (!$current_user_id) {
    header("Location: login.php");
    exit;
}

$today = date('Y-m-d');
$patient = null;
$total_meds = 0;
$today_doses = 0;
$taken_doses = 0;
$pending_doses = 0;
$doses_result = [];
$error = "";

try {
    // 1. جلب المريض المرتبط بهذا التابع (بدون الاعتماد على قيمة role في الجلسة)
    $stmt_p = $pdo->prepare("
        SELECT u.*
        FROM patient_dependents pd
        JOIN users u ON u.user_id = pd.patient_user_id
        WHERE pd.dependent_user_id = ?
        LIMIT 1
    ");
    $stmt_p->execute([$current_user_id]);
    $patient = $stmt_p->fetch() ?: null;

    if ($patient) {
        $target_user_id = $patient['user_id'];

        // 2. عدد أدوية المريض المرتبط
        $stmt_med = $pdo->prepare("SELECT COUNT(*) FROM medications WHERE user_id = ?");
        $stmt_med->execute([$target_user_id]);
        $total_meds = (int) $stmt_med->fetchColumn();

        // 3. إحصائيات جرعات اليوم للمريض
        $stmt_doses = $pdo->prepare("
            SELECT COUNT(*) AS today_doses,
                   SUM(CASE WHEN ds.status = 'taken' THEN 1 ELSE 0 END) AS taken_doses,
                   SUM(CASE WHEN ds.status != 'taken' THEN 1 ELSE 0 END) AS pending_doses
            FROM dosage_schedules ds
            JOIN medications m ON ds.medication_id = m.medication_id
            WHERE m.user_id = ? AND ds.dosage_date = ?
        ");
        $stmt_doses->execute([$target_user_id, $today]);
        $doses_stats = $stmt_doses->fetch();

        if ($doses_stats) {
            $today_doses   = $doses_stats['today_doses'] ?? 0;
            $taken_doses   = $doses_stats['taken_doses'] ?? 0;
            $pending_doses = $doses_stats['pending_doses'] ?? 0;
        }

        // 4. قائمة جرعات اليوم للمريض
        $stmt_list = $pdo->prepare("
            SELECT ds.schedule_id, m.medication_name, ds.dose_amount, ds.dosage_time, ds.status
            FROM dosage_schedules ds
            JOIN medications m ON ds.medication_id = m.medication_id
            WHERE m.user_id = ? AND ds.dosage_date = ?
            ORDER BY ds.dosage_time ASC
        ");
        $stmt_list->execute([$target_user_id, $today]);
        $doses_result = $stmt_list->fetchAll();
    }
} catch (PDOException $e) {
    $error = "تعذر تحميل البيانات حالياً.";
    // للتشخيص أثناء التطوير فقط: $error = $e->getMessage();
}

$pat_name = $patient['full_name'] ?? $patient['name'] ?? '';
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بصيرة - لوحة التابع</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

    <style>
        .dashboard-layout {
            display: flex;
            align-items: stretch;
            min-height: calc(100vh - 140px);
            width: 100%;
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
        .main-content {
            flex-grow: 1;
            padding: 40px;
        }
    </style>
</head>
<body>
  <div class="dashboard-layout">
        <!-- استدعاء سايدبار التابع المخصص داخل مجلد dependent -->
        <?php include __DIR__ . '/dependent/dep-sidebar.php'; ?>
        <main class="main-content">
            <header class="welcome-header" style="margin-bottom: 25px;">
                <h1 style="font-size: 24px; color: var(--text-color);">مرحبًا بك في لوحة التابع 👋</h1>
                <p style="color: var(--text-muted);">
                    <?php if ($patient): ?>
                        متابعة أدوية وجرعات المريض: <?php echo htmlspecialchars($pat_name); ?>
                    <?php else: ?>
                        متابعة أدوية وجرعات المريض المرتبط بسهولة
                    <?php endif; ?>
                </p>
            </header>

            <!-- الكروت الإحصائية -->
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

            <!-- كرت الجرعات اليومية -->
            <section class="doses-section">
                <h2 style="font-size: 20px; color: var(--text-color); margin-bottom: 20px;">جرعات اليوم</h2>
                <div class="doses-grid">
                    <?php if ($error): ?>
                        <div class="empty-notice"><p><?php echo htmlspecialchars($error); ?></p></div>

                    <?php elseif (!$patient): ?>
                        <div class="empty-notice"><p>حسابك غير مرتبط بمريض حتى الآن.</p></div>

                    <?php elseif (!empty($doses_result)): ?>
                        <?php foreach ($doses_result as $row): ?>
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

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>