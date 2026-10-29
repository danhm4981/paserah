
<?php
session_start();
require_once __DIR__ . '/db.php';
include __DIR__ . '/includes/header.php';

// عرض إشعارات المستخدم المسجل دخوله فقط
// if (!isset($_SESSION['user_id'])) {
//     header('Location: login.php');
//     exit;
// }

$userId = (int) $_SESSION['user_id'];

// حماية نموذج تحديث حالة القراءة
if (empty($_SESSION['notification_csrf'])) {
    $_SESSION['notification_csrf'] = bin2hex(random_bytes(32));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $notificationId = filter_input(
        INPUT_POST,
        'notification_id',
        FILTER_VALIDATE_INT
    );

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['notification_csrf'], $token)
    ) {
        $error = 'انتهت صلاحية الطلب. حدّثي الصفحة وحاولي مرة أخرى.';
    } elseif (!$notificationId || $notificationId < 1) {
        $error = 'رقم الإشعار غير صالح.';
    } else {
        // لا يمكن تحديث إلا إشعار يخص المستخدم الحالي
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
    }
}

// جلب إشعارات المستخدم الحالي فقط
$stmt = $pdo->prepare(
    "SELECT
        n.notification_id,
        n.notification_type,
        n.notification_text,
        n.notification_time,
        n.audio_text,
        n.notification_image,
        n.notification_sound,
        n.email_status,
        n.in_site_status,
        n.email_sent_at,
        ds.dose_amount,
        ds.dose_unit,
        ds.dosage_time,
        ds.dosage_date,
        m.medication_name,
        m.medication_description,
        m.medication_image,
        m.medication_color,
        m.medication_shape
     FROM notifications AS n
     LEFT JOIN dosage_schedules AS ds
        ON n.schedule_id = ds.schedule_id
     LEFT JOIN medications AS m
        ON ds.medication_id = m.medication_id
     WHERE n.user_id = ?
     ORDER BY n.notification_time DESC, n.notification_id DESC"
);

$stmt->execute([$userId]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);


$unreadCount = 0;
foreach ($notifications as $notification) {
    if ($notification['in_site_status'] === 'unread') {
        $unreadCount++;
    }
}

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
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
.dashboard-layout {
    display: flex;
    align-items: stretch; /* لتمكين السايدبار من التمدد حتى نهاية ارتفاع الصفحة */
    min-height: calc(100vh - 140px); /* ليملأ الفراغ دائماً في الشاشات الطويلة */
    width: 100%;
}

        main {
            max-width: 1000px;
            min-height: 450px;
            margin: 45px auto;
            padding: 0 20px;
        }

        .page-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 25px;
        }

        h1 {
            color: var(--primary-color);
            margin: 0;
        }

        .count {
            background: #e0f3f1;
            color: var(--primary-color);
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 14px;
        }

        .notification {
            background: var(--white);
            border: 1px solid var(--border-color);
            border-right: 5px solid var(--primary-color);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, .03);
        }

        .notification.unread {
            background: #faffff;
        }

        .notification-top {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
        }

        .notification h2 {
            font-size: 18px;
            margin: 0;
        }

        .status {
            font-size: 12px;
            border-radius: 20px;
            padding: 3px 10px;
            background: #edf1f1;
        }

        .new-status {
            background: #dff4ed;
            color: #176b4c;
        }

        .notification time {
            color: #718184;
            font-size: 13px;
        }

        .email-status {
            color: #657477;
            font-size: 13px;
        }


        .btn:hover {
            opacity: .9;
        }

        .empty, .error {
            background: var(--white);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            padding: 30px;
            text-align: center;
        }

        .error {
            color: #a32626;
            margin-bottom: 18px;
        }


        @media (max-width: 600px) {
            nav {
                align-items: flex-start;
            }

            .nav-links {
                gap: 12px;
            }

            main {
                margin-top: 25px;
            }
        }
    </style>
</head>

<body>

     <div class="dashboard-layout">
            <?php include __DIR__ . '/includes/sidebar.php';?>
<main>
    <div class="page-heading">
        <div>
            <h1>تنبيهاتي</h1>
            <p>تابعي تنبيهات أدويتك ومواعيدك من مكان واحد.</p>
        </div>

        <span class="count">
            <?= $unreadCount ?> تنبيه غير مقروء
        </span>
    </div>

    <?php if ($error !== ''): ?>
        <div class="error"><?= h($error) ?></div>
    <?php endif; ?>

    <?php if (empty($notifications)): ?>
        <div class="empty">
            <h2>لا توجد تنبيهات حاليًا</h2>
            <p>ستظهر تنبيهاتك هنا عند توفرها.</p>
        </div>
    <?php else: ?>

        <?php foreach ($notifications as $item): ?>
            <article class="notification <?= $item['in_site_status'] === 'unread' ? 'unread' : '' ?>">
                <div class="notification-top">
                    <h2>
                        <?= h($item['notification_type'] ?: 'تنبيه') ?>
                    </h2>

                    <span class="status <?= $item['in_site_status'] === 'unread' ? 'new-status' : '' ?>">
                        <?= $item['in_site_status'] === 'unread' ? 'جديد' : 'مقروء' ?>
                    </span>
                </div>

                <p><?= nl2br(h($item['notification_text'])) ?></p>
<?php if (!empty($item['medication_name'])): ?>
    <div class="medicine-info">
        <h3>معلومات الدواء</h3>

        <p>
            اسم الدواء:
            <?= h($item['medication_name']) ?>
        </p>

        <?php if (!empty($item['medication_description'])): ?>
            <p>
                الوصف:
                <?= h($item['medication_description']) ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($item['medication_shape'])): ?>
            <p>
                شكل الدواء:
                <?= h($item['medication_shape']) ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($item['medication_color'])): ?>
            <p>
                لون الدواء:
                <?= h($item['medication_color']) ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($item['dose_amount'])): ?>
            <p>
                الجرعة:
                <?= h($item['dose_amount']) ?>
                <?= h($item['dose_unit'] ?? '') ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($item['medication_image'])): ?>
            <img
                src="<?= h($item['medication_image']) ?>"
                alt="<?= h($item['medication_name']) ?>"
                style="max-width:120px; max-height:120px; object-fit:contain;"
            >
        <?php endif; ?>
    </div>
<?php endif; ?>
                <time>
                    تاريخ التنبيه:
                    <?= h($item['notification_time']) ?>
                </time>

                <p class="email-status">
                    حالة البريد الإلكتروني:
                    <?php
                    $emailLabels = [
                        'pending' => 'بانتظار الإرسال',
                        'sent' => 'تم تسجيل الإرسال',
                        'failed' => 'فشل الإرسال'
                    ];
                    echo h(
                        $emailLabels[$item['email_status'] ?? 'pending']
                        ?? 'غير محددة'
                    );
                    ?>

                    <?php if (!empty($item['email_sent_at'])): ?>
                        <br>
                        وقت الإرسال:
                        <?= h($item['email_sent_at']) ?>
                    <?php endif; ?>
                </p>

                <?php if ($item['in_site_status'] === 'unread'): ?>
                    <form method="post" action="notifications.php">
                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= h($_SESSION['notification_csrf']) ?>"
                        >
                        <input
                            type="hidden"
                            name="notification_id"
                            value="<?= (int) $item['notification_id'] ?>"
                        >
                        <button type="submit" class="btn">
                            تحديد كمقروء
                        </button>
                    </form>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>

    <?php endif; ?>
                </div>
</main>

<?php include __DIR__ . '/includes/footer.php';?>

</body>
</html>
