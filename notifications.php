
<?php
session_start();
require_once __DIR__ . '/db.php';

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
    "SELECT notification_id, notification_type,
            notification_text, notification_time,
            email_status, in_site_status, email_sent_at
     FROM notifications
     WHERE user_id = ?
     ORDER BY notification_time DESC, notification_id DESC"
);

$stmt->execute([$userId]);
$notifications = $stmt->fetchAll();

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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>التنبيهات | بصيرة</title>

    <style>
        :root {
            --primary-color: #008783;
            --white: #ffffff;
            --text-color: #34474b;
            --page-bg: #f3fafa;
            --border-color: #dce9e8;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Tahoma, sans-serif;
            color: var(--text-color);
            background: var(--page-bg);
            line-height: 1.8;
        }

        header {
            background: var(--white);
            padding: 15px 5%;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .03);
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            max-width: 1200px;
            margin: auto;
        }

        .logo {
            font-size: 24px;
            font-weight: 800;
            color: var(--primary-color);
        }

        .nav-links {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text-color);
            font-weight: 500;
        }

        .nav-links a:hover {
            color: var(--primary-color);
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

        .btn {
            display: inline-block;
            border: 1px solid var(--primary-color);
            border-radius: 20px;
            padding: 7px 17px;
            background: var(--primary-color);
            color: white;
            cursor: pointer;
            font-weight: 600;
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

        footer {
            background: var(--primary-color);
            color: var(--white);
            text-align: center;
            padding: 18px;
            font-size: 14px;
            margin-top: 60px;
        }

        footer p {
            margin: 0;
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
</main>

<footer>
        <p>© <?php echo date("Y"); ?> بصيرة - جميع الحقوق محفوظة</p>
</footer>
</body>
</html>
