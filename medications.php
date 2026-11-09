<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';

$current_user_id = $_SESSION['user_id'] ?? null;

// فحص تسجيل الدخول قبل أي إخراج
if (!$current_user_id) {
    header("Location: login.php");
    exit;
}

$medications = [];
$error = "";

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // حذف دواء (يخص المريض الحالي فقط)
        if (isset($_POST['delete_medicine'])) {
            $id = (int) ($_POST['medication_id'] ?? 0);

            $stmt = $pdo->prepare(
                "DELETE FROM medications WHERE medication_id = ? AND user_id = ?"
            );
            $stmt->execute([$id, $current_user_id]);

            header("Location: medications.php");
            exit;
        }

        // تعديل دواء (يخص المريض الحالي فقط)
        if (isset($_POST['update_medicine'])) {
            $id = (int) ($_POST['medication_id'] ?? 0);
            $name = trim($_POST['medication_name'] ?? '');
            $shape = trim($_POST['medication_shape'] ?? '');
            $color = trim($_POST['medication_color'] ?? '');
            $description = trim($_POST['medication_description'] ?? '');

            if ($name !== '' && $shape !== '') {
                $stmt = $pdo->prepare(
                    "UPDATE medications
                     SET medication_name = ?,
                         medication_shape = ?,
                         medication_color = ?,
                         medication_description = ?
                     WHERE medication_id = ? AND user_id = ?"
                );
                $stmt->execute([$name, $shape, $color, $description, $id, $current_user_id]);
            }

            header("Location: medications.php");
            exit;
        }
    }

    // جلب أدوية المريض المرتبط بالجلسة الحالية فقط
    $stmt = $pdo->prepare("SELECT * FROM medications WHERE user_id = ? ORDER BY medication_id DESC");
    $stmt->execute([$current_user_id]);
    $medications = $stmt->fetchAll();

} catch (PDOException $e) {
    $error = "حدث خطأ أثناء معالجة البيانات، حاول مرة أخرى لاحقاً.";
}

include __DIR__ . '/includes/header.php';
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>أدويتي | لوحة المريض</title>
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
            min-width: 0;
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
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
        }
        .med-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            overflow-wrap: anywhere;
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
        .med-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
        }
        .edit-form {
            display: none;
            margin-top: 15px;
        }
        .edit-form input {
            width: 100%;
            padding: 10px;
            margin: 6px 0;
            border: 1px solid var(--border-color, #d5e0e2);
            border-radius: 10px;
            font-family: inherit;
        }

        @media (max-width: 900px) {
            .meds-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 600px) {
            .meds-grid {
                grid-template-columns: 1fr;
            }
            .main-content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>
        <main class="main-content">
            <h1 class="page-title">أدويتي</h1>
            <p class="page-sub">إدارة الأدوية والجرعات والمواعيد</p>

            <div class="meds-grid">
                <?php if ($error): ?>
                    <div class="empty-notice"><p><?= htmlspecialchars($error) ?></p></div>

                <?php elseif (!empty($medications)): ?>
                    <?php foreach ($medications as $med): ?>
                        <div class="med-card">
                            <h3>💊 <?= htmlspecialchars($med['medication_name']) ?></h3>
                            <p class="med-info"><strong>الشكل:</strong> <?= htmlspecialchars($med['medication_shape'] ?? '') ?></p>
                            <p class="med-info"><strong>اللون:</strong> <?= htmlspecialchars($med['medication_color'] ?? '') ?></p>
                            <?php if (!empty($med['medication_description'])): ?>
                                <p class="med-info"><strong>ملاحظات:</strong> <?= htmlspecialchars($med['medication_description']) ?></p>
                            <?php endif; ?>

                            <div class="med-actions">
                                <button type="button" onclick="toggleEdit(<?= (int)$med['medication_id'] ?>)">تعديل</button>

                                <form method="POST" onsubmit="return confirm('متأكد من الحذف؟');">
                                    <input type="hidden" name="medication_id" value="<?= (int)$med['medication_id'] ?>">
                                    <button type="submit" name="delete_medicine">حذف</button>
                                </form>
                            </div>

                            <form method="POST" class="edit-form" id="edit-<?= (int)$med['medication_id'] ?>">
                                <input type="hidden" name="medication_id" value="<?= (int)$med['medication_id'] ?>">
                                <input type="text" name="medication_name" value="<?= htmlspecialchars($med['medication_name']) ?>" placeholder="اسم الدواء" required>
                                <input type="text" name="medication_shape" value="<?= htmlspecialchars($med['medication_shape'] ?? '') ?>" placeholder="الشكل" required>
                                <input type="text" name="medication_color" value="<?= htmlspecialchars($med['medication_color'] ?? '') ?>" placeholder="اللون">
                                <input type="text" name="medication_description" value="<?= htmlspecialchars($med['medication_description'] ?? '') ?>" placeholder="ملاحظات">
                                <button type="submit" name="update_medicine">حفظ التعديل</button>
                            </form>
                        </div>
                    <?php endforeach; ?>

                <?php else: ?>
                    <div class="empty-notice">
                        <p>لا توجد أدوية مضافة حتى الآن.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script>
        function toggleEdit(id) {
            const form = document.getElementById('edit-' + id);
            form.style.display = form.style.display === 'block' ? 'none' : 'block';
        }
    </script>
</body>
</html>
