<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
include __DIR__ . '/includes/header.php';

// 1. التحقق من تسجيل الدخول وجلب معرف المستخدم من الجلسة
$current_user_id = $_SESSION['user_id'] ?? null;
$user_role = strtolower(trim($_SESSION['role'] ?? 'patient'));

if (!$current_user_id) {
    header("Location: login.php");
    exit;
}

// 2. المنطق الذكي لتحديد هوية المريض المستهدف (سواء كان المستخدم مريضاً أو تابعاً يضيف للمريض المرتبط)
if ($user_role === 'dependent') {
    $stmt_p = $pdo->prepare("SELECT patient_user_id FROM patient_dependents WHERE dependent_user_id = ?");
    $stmt_p->execute([$current_user_id]);
    $relation = $stmt_p->fetch();
    $target_user_id = $relation['patient_user_id'] ?? $current_user_id;
} else {
    $target_user_id = $current_user_id;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $medication_name = trim($_POST['medication_name'] ?? '');
    $medication_shape = trim($_POST['medication_shape'] ?? '');
    $medication_color = trim($_POST['medication_color'] ?? '');
    $medication_description = trim($_POST['notes'] ?? '');

    $dose_amount = trim($_POST['dose_amount'] ?? '');
    $dose_unit = trim($_POST['dose_unit'] ?? '');
    $dosage_time = trim($_POST['dosage_time'] ?? '');
    $dosage_date = trim($_POST['dosage_date'] ?? '');

    if (
        empty($medication_name) ||
        empty($medication_shape) ||
        empty($medication_color) ||
        empty($dose_amount) ||
        empty($dose_unit) ||
        empty($dosage_time) ||
        empty($dosage_date)
    ) {
        $error = "يرجى تعبئة جميع الحقول المطلوبة";
    } else {
        try {
            // استخدام $target_user_id لربط الدواء بالمريض الصحيح بناءً على السيشن
            $stmt = $pdo->prepare("
                INSERT INTO medications
                (
                    user_id,
                    medication_name,
                    medication_description,
                    medication_shape,
                    medication_color
                )
                VALUES
                (
                    :user_id,
                    :medication_name,
                    :medication_description,
                    :medication_shape,
                    :medication_color
                )
            ");

            $stmt->execute([
                'user_id' => $target_user_id,
                'medication_name' => $medication_name,
                'medication_description' => $medication_description,
                'medication_shape' => $medication_shape,
                'medication_color' => $medication_color
            ]);

            $medication_id = $pdo->lastInsertId();

            $stmt2 = $pdo->prepare("
                INSERT INTO dosage_schedules
                (
                    medication_id,
                    dose_amount,
                    dose_unit,
                    dosage_time,
                    dosage_date,
                    status
                )
                VALUES
                (
                    :medication_id,
                    :dose_amount,
                    :dose_unit,
                    :dosage_time,
                    :dosage_date,
                    'pending'
                )
            ");

            $stmt2->execute([
                'medication_id' => $medication_id,
                'dose_amount' => $dose_amount,
                'dose_unit' => $dose_unit,
                'dosage_time' => $dosage_time,
                'dosage_date' => $dosage_date
            ]);

            $schedule_id = (int)$pdo->lastInsertId();
            require_once __DIR__ . '/includes/notify.php';
            notify_medication_added(
                $pdo,
                (int)$target_user_id,
                $schedule_id,
                $medication_name,
                $dose_amount,
                $dose_unit,
                $dosage_time,
                $dosage_date
            );
            $message = "تم حفظ الدواء بنجاح";
        } catch (PDOException $e) {
            $error = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة دواء جديد | بصيرة</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        .dashboard-layout {
            display: flex;
            align-items: stretch;
            min-height: calc(100vh - 140px);
            width: 100%;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
            flex-grow: 1;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .05);
        }

        .page-title {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 25px;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .medicine-types {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .medicine-types label {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 12px;
            text-align: center;
            cursor: pointer;
        }

        .medicine-types input {
            width: auto;
            margin-left: 5px;
        }

        .actions {
            margin-top: 20px;
            display: flex;
            gap: 12px;
        }

        .save-btn {
            flex: 1;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 13px;
            cursor: pointer;
        }

        .cancel-btn {
            flex: 1;
            background: white;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            border-radius: 10px;
            padding: 13px;
            cursor: pointer;
        }

        @media(max-width:768px) {
            .row {
                grid-template-columns: 1fr;
            }

            .medicine-types {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard-layout">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <div class="container">
            <div class="card">
                <h1 class="page-title">إضافة دواء جديد</h1>

                <?php if (!empty($message)): ?>
                    <div class="success-message">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="error-message">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label>اسم الدواء</label>
                        <input type="text" name="medication_name" required placeholder="مثال: أموكسيسيلين">
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <label>الجرعة</label>
                            <input type="number" step="0.01" name="dose_amount" required>
                        </div>
                        <div class="form-group">
                            <label>الوحدة</label>
                            <select name="dose_unit" required>
                                <option value="mg">mg</option>
                                <option value="ml">ml</option>
                                <option value="g">g</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>شكل الدواء</label>
                        <div class="medicine-types">
                            <label><input type="radio" name="medication_shape" value="أقراص" required> أقراص</label>
                            <label><input type="radio" name="medication_shape" value="كبسول"> كبسول</label>
                            <label><input type="radio" name="medication_shape" value="شراب"> شراب</label>
                            <label><input type="radio" name="medication_shape" value="حقن"> حقن</label>
                            <label><input type="radio" name="medication_shape" value="قطرة"> قطرة</label>
                            <label><input type="radio" name="medication_shape" value="لصقة"> لصقة</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <label>وقت الجرعة</label>
                            <input type="time" name="dosage_time" required>
                        </div>
                        <div class="form-group">
                            <label>تاريخ الجرعة</label>
                            <input type="date" name="dosage_date" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>لون الدواء</label>
                        <select name="medication_color" required>
                            <option value="">اختر اللون</option>
                            <option value="أبيض">أبيض</option>
                            <option value="أحمر">أحمر</option>
                            <option value="أزرق">أزرق</option>
                            <option value="أخضر">أخضر</option>
                            <option value="أصفر">أصفر</option>
                            <option value="برتقالي">برتقالي</option>
                            <option value="وردي">وردي</option>
                            <option value="بني">بني</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>ملاحظات</label>
                        <textarea name="notes" rows="4" placeholder="يؤخذ بعد الأكل"></textarea>
                    </div>

                    <div class="actions">
                        <button type="submit" class="save-btn">حفظ الدواء</button>
                        <button type="reset" class="cancel-btn">إلغاء</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>

</html>