<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$dependent_id = (int) $_SESSION['user_id'];
$message = '';
$error = '';

// التأكد من أن الحساب تابع
$stmt = $pdo->prepare(
    "SELECT user_id FROM users
     WHERE user_id = ? AND account_type = 'dependent'"
);
$stmt->execute([$dependent_id]);

if (!$stmt->fetch()) {
    exit('هذه الصفحة مخصصة لحساب التابع فقط.');
}

// جلب المرضى المرتبطين بالتابع
$stmt = $pdo->prepare(
    "SELECT patient_user_id
     FROM patient_dependents
     WHERE dependent_user_id = ? AND status = 'active'"
);
$stmt->execute([$dependent_id]);
$patients = $stmt->fetchAll();

$patient_ids = array_column($patients, 'patient_user_id');

// إضافة دواء
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['add_medication'])) {

    $patient_id = (int) ($_POST['patient_id'] ?? 0);
    $name = trim($_POST['medication_name'] ?? '');
    $description = trim($_POST['medication_description'] ?? '');
    $color = trim($_POST['medication_color'] ?? '');
    $shape = trim($_POST['medication_shape'] ?? '');

    if (!in_array($patient_id, $patient_ids)) {
        $error = 'المريض غير مرتبط بحسابك.';
    } elseif ($name === '') {
        $error = 'اكتب اسم الدواء.';
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO medications
             (user_id, medication_name, medication_description,
              medication_color, medication_shape)
             VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $patient_id, $name, $description, $color, $shape
        ]);

        $message = 'تمت إضافة الدواء بنجاح.';
    }
}

// حذف دواء مع التحقق من ملكية المريض المرتبط
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['delete_medication'])) {

    $medication_id = (int) ($_POST['medication_id'] ?? 0);

    $stmt = $pdo->prepare(
        "DELETE FROM medications
         WHERE medication_id = ?
         AND user_id IN (
             SELECT patient_user_id
             FROM patient_dependents
             WHERE dependent_user_id = ?
             AND status = 'active'
         )"
    );
    $stmt->execute([$medication_id, $dependent_id]);

    if ($stmt->rowCount() > 0) {
        $message = 'تم حذف الدواء.';
    } else {
        $error = 'لا يمكن حذف الدواء ، تأكد من ارتباط المريض.';
    }
}

// جلب الأدوية للمرضى المرتبطين بالتابع
$medications = [];

if (!empty($patient_ids)) {
    $placeholders = implode(',', array_fill(0, count($patient_ids), '?'));

    $stmt = $pdo->prepare(
        "SELECT m.*, u.username
         FROM medications m
         JOIN users u ON u.user_id = m.user_id
         WHERE m.user_id IN ($placeholders)
         ORDER BY m.medication_id DESC"
    );
    $stmt->execute($patient_ids);
    $medications = $stmt->fetchAll();
}

include __DIR__ . '/includes/header.php';
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بصيرة - لوحة التابع</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: #f4fbfb;
            color: #2b3e42;
            font-family: 'Tajawal', sans-serif;
        }

        .dashboard-layout {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            gap: 25px;
            min-height: calc(100vh - 140px);
            width: 100%;
        }

        .container {
            flex: 1;
            min-width: 0;
            padding: 30px;
        }

        .page-title {
            color: #008080;
            margin: 0 0 8px;
        }

        .intro {
            color: #6c7a7d;
            margin-bottom: 25px;
        }

        .card {
            background: #fff;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 4px 15px rgba(0,0,0,.05);
            margin-bottom: 25px;
        }

        .card h2 {
            color: #008080;
            font-size: 21px;
            margin: 0 0 20px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-weight: 600;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #d7e3e3;
            border-radius: 8px;
            font: inherit;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 75px;
        }

        .full-width { grid-column: 1 / -1; }

        .btn {
            border: 0;
            border-radius: 8px;
            padding: 11px 20px;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-primary {
            background: #008080;
            color: white;
        }

        .btn-delete {
            background: #fff0f0;
            color: #b42318;
        }

        .alert {
            padding: 13px;
            border-radius: 8px;
            margin-bottom: 18px;
        }

        .success { background: #e6f4ea; color: #1e7e34; }
        .danger { background: #fce8e6; color: #b42318; }

        .medication-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 18px;
        }

        .medication-card {
            border: 1px solid #e4eeee;
            border-radius: 12px;
            padding: 20px;
        }

        .medication-card h3 {
            margin: 0 0 12px;
            color: #008080;
        }

        .medication-card p {
            line-height: 1.7;
            overflow-wrap: anywhere;
        }

        .patient-name {
            color: #6c7a7d;
            font-size: 14px;
        }

        .empty {
            text-align: center;
            color: #6c7a7d;
            padding: 25px 10px;
        }

        @media (max-width: 700px) {
            .dashboard-layout { gap: 8px; }
            .container { padding: 15px; }
            .form-grid { grid-template-columns: 1fr; }
            .full-width { grid-column: auto; }
        }
    </style>
</head>
<body>

<div class="dashboard-layout">

    <?php include __DIR__ . '/includes/dep-sidebar.php'; ?>

    <main class="container">
        <h1 class="page-title">إدارة الأدوية</h1>
        <p class="intro">تابعي أدوية المرضى المرتبطين بحسابك وأضيفي الأدوية عند الحاجة.</p>

        <?php if ($message): ?>
            <div class="alert success">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <section class="card">
            <h2>إضافة دواء جديد</h2>

            <?php if (empty($patient_ids)): ?>
                <p class="empty">ما فيه مريض مرتبط بحسابك حاليًا، لذلك ما تقدري تضيفي دواء.</p>
            <?php else: ?>
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="patient_id">اختاري المريض</label>
                            <select name="patient_id" id="patient_id" required>
                                <?php foreach ($patients as $patient): ?>
                                    <?php
                                    $stmt_name = $pdo->prepare(
                                        "SELECT username FROM users WHERE user_id = ?"
                                    );
                                    $stmt_name->execute([$patient['patient_user_id']]);
                                    $patient_name = $stmt_name->fetchColumn();
                                    ?>
                                    <option value="<?= (int)$patient['patient_user_id'] ?>">
                                        <?= htmlspecialchars($patient_name ?: 'المريض') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="medication_name">اسم الدواء</label>
                            <input id="medication_name" name="medication_name"
                                   type="text" maxlength="100" required>
                        </div>

                        <div class="form-group">
                            <label for="medication_color">لون الدواء</label>
                            <input id="medication_color" name="medication_color"
                                   type="text" maxlength="50">
                        </div>

                        <div class="form-group">
                            <label for="medication_shape">شكل الدواء</label>
                            <input id="medication_shape" name="medication_shape"
                                   type="text" maxlength="50"
                                   placeholder="مثال: حبوب، كبسولات">
                        </div>

                        <div class="form-group full-width">
                            <label for="medication_description">وصف الدواء</label>
                            <textarea id="medication_description"
                                      name="medication_description"></textarea>
                        </div>

                        <div class="full-width">
                            <button class="btn btn-primary" type="submit"
                                    name="add_medication">
                                إضافة الدواء
                            </button>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </section>

        <section class="card">
            <h2>الأدوية المسجلة</h2>

            <?php if (empty($medications)): ?>
                <p class="empty">لا يوجد أدوية مسجلة للمرضى المرتبطين بحسابك.</p>
            <?php else: ?>
                <div class="medication-grid">
                    <?php foreach ($medications as $med): ?>
                        <article class="medication-card">
                            <h3><?= htmlspecialchars($med['medication_name']) ?></h3>

                            <p class="patient-name">
                                المريض: <?= htmlspecialchars($med['username']) ?>
                            </p>

                            <p>
                                الوصف:
                                <?= htmlspecialchars($med['medication_description'] ?? 'غير محدد') ?>
                            </p>

                            <p>
                                اللون:
                                <?= htmlspecialchars($med['medication_color'] ?? 'غير محدد') ?>
                            </p>

                            <p>
                                الشكل:
                                <?= htmlspecialchars($med['medication_shape'] ?? 'غير محدد') ?>
                            </p>

                            <form method="POST"
                                  onsubmit="return confirm('متأكد من حذف هذا الدواء؟');">
                                <input type="hidden" name="medication_id"
                                       value="<?= (int)$med['medication_id'] ?>">
                                <button type="submit" name="delete_medication"
                                        class="btn btn-delete">
                                    حذف الدواء
                                </button>
                            </form>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
