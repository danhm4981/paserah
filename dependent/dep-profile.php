<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../db.php';

$current_user_id = $_SESSION['user_id'] ?? null;

if (!$current_user_id) {
    header("Location: ../login.php");
    exit;
}

$message = "";
$error = "";
$dependent = [];
$patient = null;
$link_requests = [];
$dep_name = '';
$dep_email = '';

// اسم المستخدم من أي عمود موجود (full_name أو name أو username)
function user_display_name(array $u): string
{
    return $u['full_name'] ?? $u['name'] ?? $u['username'] ?? '';
}

// رسائل بعد إعادة التوجيه
if (!empty($_SESSION['flash_success'])) {
    $message = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}
if (!empty($_SESSION['flash_error'])) {
    $error = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}

try {
    // ----- بيانات التابع (الاسم والإيميل) -----
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$current_user_id]);
    $dependent = $stmt->fetch() ?: [];

    // تحديد عمود الاسم الموجود فعلاً في جدول users
    $name_col = 'username';
    foreach (['full_name', 'name', 'username'] as $col) {
        if (array_key_exists($col, $dependent)) {
            $name_col = $col;
            break;
        }
    }
    $dep_name  = $dependent[$name_col] ?? '';
    $dep_email = $dependent['email'] ?? '';

    // ----- معالجة النماذج -----
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // (أ) تحديث الاسم
        if (isset($_POST['update_name'])) {
            $new_name = trim($_POST['full_name'] ?? '');
            if ($new_name === '') {
                $_SESSION['flash_error'] = "الاسم مطلوب";
            } else {
                $pdo->prepare("UPDATE users SET $name_col = ? WHERE user_id = ?")
                    ->execute([$new_name, $current_user_id]);
                $_SESSION['flash_success'] = "تم حفظ الاسم بنجاح ✓";
            }
            header("Location: dep-profile.php");
            exit;
        }

        // (ب) الموافقة أو الرفض على طلب ربط
        if (isset($_POST['link_action'])) {
            $request_id = (int) ($_POST['request_id'] ?? 0);
            $action     = $_POST['link_action'];

            if ($request_id > 0 && in_array($action, ['approve', 'reject'], true)) {

                if ($action === 'approve') {
                    // التابع مرتبط بمريض واحد فقط (طلب مفعّل)
                    $chk = $pdo->prepare("SELECT 1 FROM patient_dependents WHERE dependent_user_id = ? AND status = 'active'");
                    $chk->execute([$current_user_id]);

                    if ($chk->fetch()) {
                        $_SESSION['flash_error'] = "حسابك مرتبط بمريض بالفعل، لا يمكن قبول طلب جديد";
                        header("Location: dep-profile.php");
                        exit;
                    }
                }

                $new_status = ($action === 'approve') ? 'active' : 'rejected';

                // الطلب لازم يكون معلّقاً وموجّهاً لهذا التابع بالذات
                $upd = $pdo->prepare("
                    UPDATE patient_dependents
                    SET status = ?
                    WHERE id = ?
                      AND dependent_user_id = ?
                      AND status = 'pending'
                ");
                $upd->execute([$new_status, $request_id, $current_user_id]);

                if ($upd->rowCount() > 0) {
                    $_SESSION['flash_success'] = ($action === 'approve')
                        ? "تمت الموافقة وربط حسابك بالمريض ✓"
                        : "تم رفض الطلب";
                } else {
                    $_SESSION['flash_error'] = "الطلب غير موجود أو تمت معالجته مسبقاً";
                }
            }

            header("Location: dep-profile.php");
            exit;
        }
    }

    // ----- المريض المرتبط حالياً (طلب مفعّل فقط) -----
    $stmt = $pdo->prepare("
        SELECT u.*
        FROM patient_dependents pd
        JOIN users u ON u.user_id = pd.patient_user_id
        WHERE pd.dependent_user_id = ? AND pd.status = 'active'
        LIMIT 1
    ");
    $stmt->execute([$current_user_id]);
    $patient = $stmt->fetch() ?: null;

    // ----- طلبات الربط المعلّقة الموجّهة لهذا التابع -----
    $stmt = $pdo->prepare("
        SELECT pd.id AS req_id, pd.relationship, u.*
        FROM patient_dependents pd
        JOIN users u ON u.user_id = pd.patient_user_id
        WHERE pd.dependent_user_id = ? AND pd.status = 'pending'
        ORDER BY pd.id DESC
    ");
    $stmt->execute([$current_user_id]);
    $link_requests = $stmt->fetchAll();

} catch (PDOException $e) {
    $error = "تعذر تحميل البيانات حالياً.";
    // للتشخيص أثناء التطوير فقط: $error = $e->getMessage();
}

$pat_name  = $patient ? user_display_name($patient) : '';
$pat_email = $patient['email'] ?? '';

// الهيدر بعد المعالجة حتى تعمل إعادة التوجيه
include __DIR__ . '/../includes/header.php';
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بصيرة - الملف الشخصي</title>
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
        .main-content {
            flex-grow: 1;
            padding: 40px;
        }
        .page-title {
            text-align: center;
            margin-bottom: 30px;
        }
        .page-title h1 {
            font-size: 26px;
            color: var(--text-color);
            margin-bottom: 8px;
        }
        .page-title p {
            color: var(--text-muted);
            font-size: 14px;
        }
        .info-card {
            background: var(--white);
            padding: 28px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            margin-bottom: 25px;
        }
        .info-card h3 {
            color: var(--text-color);
            font-size: 18px;
            margin-bottom: 18px;
        }
        .info-line {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 8px;
        }
        .linked-ok {
            color: var(--primary-color);
            font-weight: bold;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 18px;
        }
        .form-group label {
            display: block;
            color: var(--text-color);
            font-size: 14px;
            margin-bottom: 8px;
        }
        .form-group input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #dce7e7;
            border-radius: 10px;
            font-family: inherit;
            font-size: 14px;
            box-sizing: border-box;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        .form-group input[readonly] {
            background: #f3f6f6;
            color: var(--text-muted);
        }
        .status-btn {
            padding: 10px 32px;
            border-radius: 20px;
            cursor: pointer;
            margin-top: 20px;
            font-weight: 600;
            font-family: inherit;
            background-color: var(--primary-color);
            color: white;
            border: none;
        }
        .alert-box {
            padding: 12px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-box.success {
            background: #e6f4f4;
            color: var(--primary-color);
        }
        .alert-box.error {
            background: #fdf2f2;
            color: #d9534f;
        }

        /* طلبات الربط */
        .request-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            padding: 16px 0;
            border-top: 1px solid #f0f4f4;
        }
        .request-item:first-of-type {
            border-top: none;
            padding-top: 0;
        }
        .request-text strong {
            color: var(--text-color);
            display: block;
            margin-bottom: 4px;
        }
        .request-text span {
            color: var(--text-muted);
            font-size: 13px;
            display: block;
        }
        .request-actions {
            display: flex;
            gap: 10px;
        }
        .request-actions form {
            margin: 0;
        }
        .btn-accept, .btn-reject {
            padding: 9px 26px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            font-family: inherit;
        }
        .btn-accept {
            background-color: var(--primary-color);
            color: white;
            border: none;
        }
        .btn-reject {
            background-color: transparent;
            border: 1.5px solid #d9534f;
            color: #d9534f;
        }
        .requests-badge {
            background: var(--primary-color);
            color: white;
            font-size: 12px;
            padding: 2px 10px;
            border-radius: 20px;
            margin-right: 8px;
            vertical-align: middle;
        }
        .empty-line {
            color: var(--text-muted);
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <?php include __DIR__ . '/dep-sidebar.php'; ?>

        <main class="main-content">
            <header class="page-title">
                <h1>الملف الشخصي</h1>
                <p>بياناتك وطلبات الربط مع المرضى</p>
            </header>

            <?php if ($message): ?>
                <div class="alert-box success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert-box error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- طلبات الربط -->
            <section class="info-card">
                <h3>
                    طلبات الربط
                    <?php if (count($link_requests) > 0): ?>
                        <span class="requests-badge"><?php echo count($link_requests); ?></span>
                    <?php endif; ?>
                </h3>

                <?php if (empty($link_requests)): ?>
                    <p class="empty-line">لا توجد طلبات ربط جديدة.</p>
                <?php else: ?>
                    <?php foreach ($link_requests as $request): ?>
                        <div class="request-item">
                            <div class="request-text">
                                <strong><?php echo htmlspecialchars(user_display_name($request)); ?></strong>
                                <span>البريد الإلكتروني: <?php echo htmlspecialchars($request['email'] ?? ''); ?></span>
                                <?php if (!empty($request['relationship'])): ?>
                                    <span>صلة القرابة: <?php echo htmlspecialchars($request['relationship']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="request-actions">
                                <form method="POST" action="dep-profile.php">
                                    <input type="hidden" name="request_id" value="<?php echo (int) $request['req_id']; ?>">
                                    <input type="hidden" name="link_action" value="approve">
                                    <button type="submit" class="btn-accept">موافقة</button>
                                </form>
                                <form method="POST" action="dep-profile.php">
                                    <input type="hidden" name="request_id" value="<?php echo (int) $request['req_id']; ?>">
                                    <input type="hidden" name="link_action" value="reject">
                                    <button type="submit" class="btn-reject">رفض</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>

            <!-- المريض المرتبط -->
            <section class="info-card">
                <h3>بيانات المريض</h3>
                <?php if ($patient): ?>
                    <p class="info-line">اسم المريض: <?php echo htmlspecialchars($pat_name); ?></p>
                    <p class="info-line">البريد الإلكتروني: <?php echo htmlspecialchars($pat_email); ?></p>
                    <p class="info-line">حالة الربط: <span class="linked-ok">مرتبط ✓</span></p>
                <?php else: ?>
                    <p class="info-line">حالة الربط: غير مرتبط بمريض حتى الآن</p>
                <?php endif; ?>
            </section>

            <!-- بيانات التابع -->
            <section class="info-card">
                <h3>بياناتي الشخصية</h3>
                <form method="POST">
                    <input type="hidden" name="update_name" value="1">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>الاسم</label>
                            <input type="text" name="full_name" value="<?php echo htmlspecialchars($dep_name); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>البريد الإلكتروني</label>
                            <input type="email" value="<?php echo htmlspecialchars($dep_email); ?>" readonly>
                        </div>
                    </div>
                    <button type="submit" class="status-btn">حفظ الاسم</button>
                </form>
            </section>
        </main>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>