<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// العودة خطوة للخلف للوصول لملف قاعدة البيانات في المجلد الرئيسي
require_once __DIR__ . '/../db.php';
include __DIR__ . '/../includes/header.php';

// 1. التحقق من تسجيل الدخول
$current_user_id = $_SESSION['user_id'] ?? null;

if (!$current_user_id) {
    header("Location: ../login.php");
    exit;
}

$patient = null;
$medications = [];
$error = "";

try {
    // 2. جلب المريض المرتبط بهذا التابع (بدون الاعتماد على قيمة role في الجلسة)
    $stmt = $pdo->prepare("
        SELECT u.*
        FROM patient_dependents pd
        JOIN users u ON u.user_id = pd.patient_user_id
        WHERE pd.dependent_user_id = ?
        LIMIT 1
    ");
    $stmt->execute([$current_user_id]);
    $patient = $stmt->fetch() ?: null;

    // 3. جلب أدوية المريض المرتبط فقط
    if ($patient) {
        $stmt = $pdo->prepare("SELECT * FROM medications WHERE user_id = ? ORDER BY medication_id DESC");
        $stmt->execute([$patient['user_id']]);
        $medications = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $error = "تعذر تحميل الأدوية حالياً.";
    // للتشخيص أثناء التطوير فقط: $error = $e->getMessage();
}

$pat_name = $patient['full_name'] ?? $patient['name'] ?? '';
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>أدوية المريض | لوحة التابع</title>
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
            color: var(--primary-color, #008080);
            margin-bottom: 8px;
            font-size: 24px;
        }
        .page-sub {
            color: var(--text-muted, #557075);
            font-size: 14px;
            margin-bottom: 25px;
        }
        .meds-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
        }
        .med-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        }
        .med-card h3 {
            color: var(--text-color, #2b3e42);
            margin-bottom: 10px;
        }
        .med-info {
            color: var(--text-muted, #557075);
            font-size: 14px;
            margin-bottom: 6px;
        }
        .empty-notice {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            color: var(--text-muted, #557075);
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            grid-column: 1 / -1;
        }
    </style>
</head>
<body>
   <div class="dashboard-layout">
        <!-- استدعاء سايدبار التابع المخصص داخل مجلد dependent -->
        <?php include __DIR__ . '/dep-sidebar.php'; ?>
        <main class="main-content">
            <h1 class="page-title">أدوية المريض المرتبط</h1>
            <?php if ($patient): ?>
                <p class="page-sub">المريض: <?php echo htmlspecialchars($pat_name); ?></p>
            <?php endif; ?>

            <div class="meds-grid">
                <?php if ($error): ?>
                    <div class="empty-notice"><p><?php echo htmlspecialchars($error); ?></p></div>

                <?php elseif (!$patient): ?>
                    <div class="empty-notice"><p>حسابك غير مرتبط بمريض حتى الآن.</p></div>

                <?php elseif (!empty($medications)): ?>
                    <?php foreach ($medications as $med): ?>
                        <div class="med-card">
                            <h3>💊 <?php echo htmlspecialchars($med['medication_name']); ?></h3>
                            <p class="med-info"><strong>الشكل:</strong> <?php echo htmlspecialchars($med['medication_shape'] ?? ''); ?></p>
                            <p class="med-info"><strong>اللون:</strong> <?php echo htmlspecialchars($med['medication_color'] ?? ''); ?></p>
                            <?php if (!empty($med['medication_description'])): ?>
                                <p class="med-info"><strong>ملاحظات:</strong> <?php echo htmlspecialchars($med['medication_description']); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                <?php else: ?>
                    <div class="empty-notice">
                        <p>لا توجد أدوية مسجلة حالياً للمريض المرتبط.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>