<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (
    !isset($_SESSION['user_id']) ||
    !filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT) ||
    (int) $_SESSION['user_id'] < 1
) {
    header('Location: login.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];

require_once __DIR__ . '/db.php';
include __DIR__ . '/includes/header.php';

// حماية نموذج تحديث حالة القراءة
if (empty($_SESSION['notification_csrf'])) {
    $_SESSION['notification_csrf'] = bin2hex(random_bytes(32));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $action = $_POST['action'] ?? '';
    $notificationId = filter_input(
        INPUT_POST,
        'notification_id',
        FILTER_VALIDATE_INT
    );

    if (
        !is_string($token) ||
        !isset($_SESSION['notification_csrf']) ||
        !hash_equals($_SESSION['notification_csrf'], $token)
    ) {
        $error = 'انتهت صلاحية الطلب. حدّثي الصفحة وحاولي مرة أخرى.';
    } elseif (
        !is_string($action) ||
        !in_array($action, ['mark_read', 'take_dose'], true)
    ) {
        $error = 'العملية المطلوبة غير صالحة.';
    } elseif (!$notificationId || $notificationId < 1) {
        $error = 'رقم الإشعار غير صالح.';
    } else {
        $check = $pdo->prepare(
            "SELECT n.notification_id, n.schedule_id
             FROM notifications n
             WHERE n.notification_id = ?
               AND n.user_id = ?
             LIMIT 1"
        );
        $check->execute([$notificationId, $userId]);
        $notification = $check->fetch(PDO::FETCH_ASSOC);

        if (!$notification) {
            $error = 'الإشعار غير موجود أو لا تملكين صلاحية تعديله.';
        } elseif ($action === 'mark_read') {
            $update = $pdo->prepare(
                "UPDATE notifications
                 SET in_site_status = 'read', read_at = NOW()
                 WHERE notification_id = ?
                   AND user_id = ?
                   AND in_site_status = 'unread'"
            );
            $update->execute([$notificationId, $userId]);
            header('Location: notifications.php');
            exit;
        } elseif ($action === 'take_dose') {
            if (empty($notification['schedule_id'])) {
                $error = 'هذا الإشعار غير مرتبط بجرعة.';
            } else {
                $update = $pdo->prepare(
                    "UPDATE dosage_schedules SET status = 'taken'
                     WHERE schedule_id = ? AND status = 'pending'"
                );
                $update->execute([$notification['schedule_id']]);
                if ($update->rowCount() > 0) {
                    $read = $pdo->prepare(
                        "UPDATE notifications
                         SET in_site_status = 'read', read_at = NOW()
                         WHERE notification_id = ? AND user_id = ?"
                    );
                    $read->execute([$notificationId, $userId]);
                    header('Location: notifications.php');
                    exit;
                }
                $error = 'لم تُحدّث الجرعة. ربما تم تناولها سابقًا أو أن حالتها لم تعد pending.';
            }
        }
    }
}

// جلب إشعارات المستخدم
$stmt = $pdo->prepare(
    "SELECT
        n.notification_id,
        n.notification_type,
        n.notification_text,
        n.notification_time,
        n.audio_text,
        n.email_status,
        n.in_site_status,
        n.email_sent_at,
        ds.dose_amount,
        ds.dose_unit,
        ds.dosage_time,
        ds.dosage_date,
        ds.schedule_id,
        ds.status AS dosage_status,
        m.medication_name,
        m.medication_shape,
        m.medication_color,
        m.medication_image
     FROM notifications AS n
     LEFT JOIN dosage_schedules AS ds ON n.schedule_id = ds.schedule_id
     LEFT JOIN medications AS m ON ds.medication_id = m.medication_id
     WHERE n.user_id = ?
     ORDER BY n.notification_time DESC, n.notification_id DESC"
);
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$unreadCount = 0;
foreach ($notifications as $n) {
    if ($n['in_site_status'] === 'unread') $unreadCount++;
}

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// دالة مساعدة: أيقونة نوع الإشعار
function notif_icon(string $type): string {
    if (str_contains($type, 'تذكير') || str_contains($type, 'موعد')) return '🔔';
    if (str_contains($type, 'إضافة') || str_contains($type, 'دواء'))  return '💊';
    return '🔔';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التنبيهات | بصيرة</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        /* ===== Layout ===== */
        .dashboard-layout {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            min-height: calc(100vh - 130px);
        }
        .notif-main {
            flex: 1;
            padding: 40px 36px;
            max-width: 820px;
        }

        /* ===== Page header ===== */
        .notif-page-title {
            text-align: center;
            margin-bottom: 32px;
        }
        .notif-page-title h1 {
            font-size: 28px;
            font-weight: 800;
            color: var(--text-color, #2b3e42);
            margin-bottom: 6px;
        }
        .notif-page-title p {
            color: #8a9ea1;
            font-size: 14px;
        }

        /* ===== Card ===== */
        .notif-card {
            background: #fff;
            border: 1px solid #e3eeee;
            border-radius: 14px;
            padding: 20px 24px;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 2px 10px rgba(0,128,128,.05);
            transition: box-shadow .2s;
        }
        .notif-card:hover { box-shadow: 0 4px 18px rgba(0,128,128,.10); }
        .notif-card.unread { background: #f7fdfd; }

        /* أيقونة */
        .notif-icon-wrap {
            flex-shrink: 0;
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: #e6f4f4;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }
        .notif-card.reminder .notif-icon-wrap { background: #fff4e0; }
        .notif-card.taken   .notif-icon-wrap { background: #e8f8ef; }

        /* نص */
        .notif-body { flex: 1; min-width: 0; }
        .notif-title {
            font-size: 16px;
            font-weight: 700;
            color: #2b3e42;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .notif-sub {
            font-size: 13px;
            color: #718184;
            margin-bottom: 2px;
        }
        .notif-time {
            font-size: 12px;
            color: #aab8ba;
        }

        /* شارة حالة */
        .notif-badge {
            font-size: 12px;
            padding: 3px 11px;
            border-radius: 20px;
            font-weight: 600;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .badge-unread  { background: #e0f5f0; color: #0d7a56; }
        .badge-read    { background: #f0f3f3; color: #7a8e91; }
        .badge-pending { background: #fff3e0; color: #b36b00; }
        .badge-taken   { background: #e6f4ec; color: #1e6e3c; }

        /* زر تناول / مقروء */
        .notif-btn {
            flex-shrink: 0;
            padding: 9px 20px;
            border-radius: 24px;
            font-size: 14px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: opacity .2s, transform .15s;
            font-family: 'Tajawal', sans-serif;
        }
        .notif-btn:hover { opacity: .88; transform: translateY(-1px); }
        .btn-take  { background: #008080; color: #fff; }
        .btn-mark  { background: #f0f3f3; color: #3a5558; border: 1px solid #d0dede; }

        /* فارغ / خطأ */
        .notif-empty, .notif-error {
            background: #fff;
            border: 1px solid #e3eeee;
            border-radius: 14px;
            padding: 40px;
            text-align: center;
            color: #718184;
        }
        .notif-error { color: #a32626; }

        /* شريط أعلى القسم */
        .notif-top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .notif-count-badge {
            background: #e0f3f1;
            color: #008080;
            padding: 5px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        @media (max-width: 700px) {
            .notif-main { padding: 24px 14px; }
            .notif-card { flex-wrap: wrap; gap: 12px; }
            .notif-title { white-space: normal; }
        }
    </style>
</head>
<body>

<div class="dashboard-layout">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <div class="notif-main">

        <!-- عنوان الصفحة -->
        <div class="notif-page-title">
            <h1>التنبيهات</h1>
            <p>تنبيهات مواعيد الأدوية والجرعات</p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="notif-error"><?= h($error) ?></div>
        <?php endif; ?>

        <!-- شريط العدد -->
        <div class="notif-top-bar">
            <span class="notif-count-badge">
                <?= $unreadCount ?> غير مقروء
            </span>
        </div>

        <?php if (empty($notifications)): ?>
            <div class="notif-empty">
                <div style="font-size:40px;margin-bottom:12px">🔔</div>
                <h2 style="margin-bottom:6px;color:#2b3e42">لا توجد تنبيهات حاليًا</h2>
                <p>ستظهر تنبيهاتك هنا عند توفرها.</p>
            </div>
        <?php else: ?>

            <?php foreach ($notifications as $item):
                $isUnread   = $item['in_site_status'] === 'unread';
                $isPending  = !empty($item['schedule_id']) && ($item['dosage_status'] ?? '') === 'pending';
                $isTaken    = !empty($item['schedule_id']) && ($item['dosage_status'] ?? '') === 'taken';
                $isReminder = str_contains((string)$item['notification_type'], 'تذكير');

                // اسم الدواء + الجرعة للعنوان
                $medName  = $item['medication_name'] ?? '';
                $doseAmt  = $item['dose_amount']  ?? '';
                $doseUnit = $item['dose_unit']     ?? '';
                $doseTime = !empty($item['dosage_time']) ? substr($item['dosage_time'], 0, 5) : '';

                // بناء النص الرئيسي للبطاقة
                if ($medName && $doseTime) {
                    $cardTitle = "موعد {$medName} الساعة {$doseTime}";
                } elseif ($medName) {
                    $cardTitle = $item['notification_type'] . " — {$medName}";
                } else {
                    $cardTitle = $item['notification_type'] ?: $item['notification_text'];
                }

                // نص فرعي: الجرعة أو النص الأصلي
                $cardSub = '';
                if ($doseAmt) {
                    $cardSub = "الجرعة: {$doseAmt} {$doseUnit}";
                } elseif (mb_strlen($item['notification_text']) < 120) {
                    $cardSub = $item['notification_text'];
                }

                // وقت التنبيه منسّق
                $timeLabel = '';
                if (!empty($item['notification_time'])) {
                    $ts = strtotime($item['notification_time']);
                    if ($ts) $timeLabel = date('Y/m/d — h:i A', $ts);
                }

                // فئة البطاقة
                $cardClass = $isUnread ? 'unread' : '';
                if ($isReminder) $cardClass .= ' reminder';
                if ($isTaken)    $cardClass .= ' taken';
            ?>

            <div class="notif-card <?= $cardClass ?>">

                <!-- أيقونة -->
                <div class="notif-icon-wrap">
                    <?= notif_icon((string)$item['notification_type']) ?>
                </div>

                <!-- نص -->
                <div class="notif-body">
                    <div class="notif-title"><?= h($cardTitle) ?></div>
                    <?php if ($cardSub): ?>
                        <div class="notif-sub"><?= h($cardSub) ?></div>
                    <?php endif; ?>
                    <?php if ($timeLabel): ?>
                        <div class="notif-time"><?= h($timeLabel) ?></div>
                    <?php endif; ?>
                </div>

                <!-- شارة الحالة -->
                <?php if ($isTaken): ?>
                    <span class="notif-badge badge-taken">تم تناولها ✓</span>
                <?php elseif ($isPending): ?>
                    <span class="notif-badge badge-pending">قريباً</span>
                <?php elseif ($isUnread): ?>
                    <span class="notif-badge badge-unread">جديد</span>
                <?php else: ?>
                    <span class="notif-badge badge-read">مقروء</span>
                <?php endif; ?>

                <!-- أزرار الإجراء -->
                <?php if ($isPending): ?>
                    <form method="post" action="notifications.php">
                        <input type="hidden" name="action" value="take_dose">
                        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['notification_csrf']) ?>">
                        <input type="hidden" name="notification_id" value="<?= (int)$item['notification_id'] ?>">
                        <button type="submit" class="notif-btn btn-take">تم تناولها</button>
                    </form>
                <?php elseif ($isUnread && !$isPending): ?>
                    <form method="post" action="notifications.php">
                        <input type="hidden" name="action" value="mark_read">
                        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['notification_csrf']) ?>">
                        <input type="hidden" name="notification_id" value="<?= (int)$item['notification_id'] ?>">
                        <button type="submit" class="notif-btn btn-mark">تحديد كمقروء</button>
                    </form>
                <?php endif; ?>

            </div><!-- /notif-card -->

            <?php endforeach; ?>

        <?php endif; ?>

    </div><!-- /notif-main -->
</div><!-- /dashboard-layout -->

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>