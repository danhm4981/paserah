<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

if (
    !isset($_SESSION['user_id']) ||
    ($_SESSION['account_type'] ?? '') !== 'patient'
) {
    header('Location: login.php');
    exit;
}

$patient_id = (int) $_SESSION['user_id'];

include __DIR__ . '/includes/header.php';

// معالجة قبول أو رفض طلب الربط
$link_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['link_action'])) {
    $request_id = (int) ($_POST['request_id'] ?? 0);
    $action = $_POST['link_action'];

    if (in_array($action, ['active', 'rejected'], true)) {
        $stmt = $pdo->prepare("
            UPDATE patient_dependents
            SET status = ?
            WHERE id = ?
              AND patient_user_id = ?
              AND status = 'pending'
        ");

        $stmt->execute([$action, $request_id, $patient_id]);

        $link_message = $stmt->rowCount() > 0
            ? 'تم تحديث طلب الربط.'
            : 'تعذر تحديث الطلب.';
    }
}

// جلب طلبات الربط المعلقة
$stmt = $pdo->prepare("
    SELECT pd.id, pd.relationship, u.username, u.email
    FROM patient_dependents pd
    JOIN users u ON u.user_id = pd.dependent_user_id
    WHERE pd.patient_user_id = ?
      AND pd.status = 'pending'
");
$stmt->execute([$patient_id]);
$link_requests = $stmt->fetchAll();

// إحصائيات المريض
$today = date('Y-m-d');
$total_meds = 0;
$today_doses = 0;
$taken_doses = 0;
$pending_doses = 0;
$doses_result = [];

try {
    $stmt_med = $pdo->prepare("
        SELECT COUNT(*) AS total_meds
        FROM medications
        WHERE user_id = ?
    ");
    $stmt_med->execute([$patient_id]);
    $total_meds = $stmt_med->fetch()['total_meds'] ?? 0;

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

    $stmt_list = $pdo->prepare("
        SELECT ds.schedule_id, m.medication_name,
               ds.dose_amount, ds.dosage_time, ds.status
        FROM dosage_schedules ds
        JOIN medications m ON ds.medication_id = m.medication_id
        WHERE m.user_id = ? AND ds.dosage_date = ?
        ORDER BY ds.dosage_time ASC
    ");
    $stmt_list->execute([$patient_id, $today]);
    $doses_result = $stmt_list->fetchAll();

} catch (PDOException $e) {
    error_log($e->getMessage());
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
            <?php include __DIR__ . '/includes/sidebar.php';?>
        <main class="main-content">
            
            <header class="welcome-header" style="margin-bottom: 25px;">
                <h1 style="font-size: 24px; color: var(--text-color);">مرحبًا بك في بصيرة 👋</h1>
                <p style="color: var(--text-muted);">تابع أدويتك وجرعاتك بسهولة</p>
            </header>

<section style="background:white;padding:20px;border-radius:15px;margin-bottom:25px;">
    <h2 style="color:#008080;margin-bottom:15px;">طلبات ربط التابعين</h2>

    <?php if ($link_message): ?>
        <p><?= htmlspecialchars($link_message, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (empty($link_requests)): ?>
        <p>لا توجد طلبات ربط جديدة.</p>
    <?php else: ?>
        <?php foreach ($link_requests as $request): ?>
            <div style="padding:15px;border-bottom:1px solid #eee;">
                <p>
                    التابع:
                    <?= htmlspecialchars($request['username'], ENT_QUOTES, 'UTF-8') ?>
                </p>
                <p>
                    البريد الإلكتروني:
                    <?= htmlspecialchars($request['email'], ENT_QUOTES, 'UTF-8') ?>
                </p>

                <form method="POST" style="display:flex;gap:10px;margin-top:10px;">
                    <input type="hidden" name="request_id"
                           value="<?= (int)$request['id'] ?>">

                    <button type="submit" name="link_action" value="active"
                            style="background:#008080;color:white;padding:9px 18px;border:0;border-radius:8px;cursor:pointer;">
                        قبول
                    </button>

                    <button type="submit" name="link_action" value="rejected"
                            style="background:#b42318;color:white;padding:9px 18px;border:0;border-radius:8px;cursor:pointer;">
                        رفض
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</section>
            <!--الكروت-->
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

            <!-- كرت الجرعات اليوميه -->
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
    <?php include __DIR__ . '/includes/footer.php';?>

</body>
</html>