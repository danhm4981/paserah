<?php
date_default_timezone_set('Asia/Riyadh');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

if (
    !isset($_SESSION['user_id']) ||
    ($_SESSION['account_type'] ?? '') !== 'dependent'
) {
    header('Location: login.php');
    exit;
}

$dependent_id = (int) $_SESSION['user_id'];

$patient = null;
$link_state = 'none';   // active | pending | rejected | none

$today = date('Y-m-d');
$total_meds = 0;
$today_doses = 0;
$taken_doses = 0;
$pending_doses = 0;
$doses_result = [];

try {
    // المريض المرتبط بالتابع (طلب مفعّل فقط، أي بعد موافقة المريض)
    $stmt = $pdo->prepare("
        SELECT u.*
        FROM patient_dependents pd
        JOIN users u ON u.user_id = pd.patient_user_id
        WHERE pd.dependent_user_id = ?
          AND pd.status = 'active'
        LIMIT 1
    ");
    $stmt->execute([$dependent_id]);
    $patient = $stmt->fetch() ?: null;

    if ($patient) {
        $link_state = 'active';
        $patient_id = (int) $patient['user_id'];

        // عدد أدوية المريض المرتبط
        $stmt_med = $pdo->prepare("
            SELECT COUNT(*) AS total_meds
            FROM medications
            WHERE user_id = ?
        ");
        $stmt_med->execute([$patient_id]);
        $total_meds = $stmt_med->fetch()['total_meds'] ?? 0;

        // إحصائيات جرعات اليوم للمريض
        $stmt_doses = $pdo->prepare("
            SELECT COUNT(*) AS today_doses,
                   SUM(CASE WHEN ds.status = 'taken' THEN 1 ELSE 0 END) AS taken_doses,
                   SUM(CASE WHEN ds.status != 'taken' THEN 1 ELSE 0 END) AS pending_doses
            FROM dosage_schedules ds
            JOIN medications m ON ds.medication_id = m.medication_id
            WHERE m.user_id = ? AND ds.start_date = ?
        ");
        $stmt_doses->execute([$patient_id, $today]);
        $doses_stats = $stmt_doses->fetch();

        if ($doses_stats) {
            $today_doses = $doses_stats['today_doses'] ?? 0;
            $taken_doses = $doses_stats['taken_doses'] ?? 0;
            $pending_doses = $doses_stats['pending_doses'] ?? 0;
        }

        // قائمة جرعات اليوم للمريض
        $stmt_list = $pdo->prepare("
            SELECT ds.schedule_id, m.medication_name,
                   ds.dose_amount, ds.dosage_time, ds.status
            FROM dosage_schedules ds
            JOIN medications m ON ds.medication_id = m.medication_id
            WHERE m.user_id = ? AND ds.start_date = ?
            ORDER BY ds.dosage_time ASC
        ");
        $stmt_list->execute([$patient_id, $today]);
        $doses_result = $stmt_list->fetchAll();

    } else {
        // حالة آخر طلب ربط (بانتظار الموافقة أو مرفوض)
        $stmt = $pdo->prepare("
            SELECT status
            FROM patient_dependents
            WHERE dependent_user_id = ?
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([$dependent_id]);
        $last_status = $stmt->fetchColumn();

        if ($last_status === 'pending') {
            $link_state = 'pending';
        } elseif ($last_status === 'rejected') {
            $link_state = 'rejected';
        }
    }

} catch (PDOException $e) {
    error_log($e->getMessage());
}

$patient_name = $patient['username'] ?? ($patient['full_name'] ?? ($patient['name'] ?? ''));

include __DIR__ . '/includes/header.php';
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
    flex-direction: row;
    justify-content: flex-start;
    align-items: flex-start;
    min-height: calc(100vh - 140px);
    width: 100%;
    max-width: 100%;
    margin: 0;
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
    </style>
</head>
<body>
    <div class="dashboard-layout">
            <?php include __DIR__ . '/dependent/dep-sidebar.php'; ?>
        <main class="main-content">

            <header class="welcome-header" style="margin-bottom: 25px;">
                <h1 style="font-size: 24px; color: var(--text-color);">مرحبًا بك في لوحة التابع 👋</h1>
                <p style="color: var(--text-muted);">
                    <?php if ($link_state === 'active'): ?>
                        أنت تتابع أدوية وجرعات المريض: <?= htmlspecialchars($patient_name, ENT_QUOTES, 'UTF-8') ?>
                    <?php elseif ($link_state === 'pending'): ?>
                        طلب الربط بانتظار موافقة المريض
                    <?php elseif ($link_state === 'rejected'): ?>
                        تم رفض طلب الربط من قبل المريض
                    <?php else: ?>
                        حسابك غير مرتبط بمريض حتى الآن
                    <?php endif; ?>
                </p>
            </header>

            <!--الكروت-->
            <section class="stats-grid">
                <div class="stat-card">
                    <h2><?php echo $total_meds; ?></h2>
                    <p>عدد أدوية المريض</p>
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

            <!-- كرت الجرعات اليوميه -->
            <section class="doses-section">
                <h2 style="font-size: 20px; color: var(--text-color); margin-bottom: 20px;">جرعات المريض اليوم</h2>
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
                            <p>
                                <?php if ($link_state === 'none'): ?>
                                    حسابك غير مرتبط بمريض حتى الآن.
                                <?php elseif ($link_state === 'pending'): ?>
                                    ستظهر الجرعات بعد موافقة المريض على طلب الربط.
                                <?php elseif ($link_state === 'rejected'): ?>
                                    تم رفض طلب الربط، ولا يمكن عرض بيانات المريض.
                                <?php else: ?>
                                    لا توجد جرعات مجدولة لهذا اليوم.
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

        </main>
    </div>
    <?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
