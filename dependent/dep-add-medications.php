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

$message = '';
$error = '';
$patient = null;

if (!empty($_SESSION['flash_success'])) {
    $message = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}

try {
    $stmt_p = $pdo->prepare("
        SELECT u.*
        FROM patient_dependents pd
        JOIN users u ON u.user_id = pd.patient_user_id
        WHERE pd.dependent_user_id = ?
        LIMIT 1
    ");
    $stmt_p->execute([$current_user_id]);
    $patient = $stmt_p->fetch() ?: null;
} catch (PDOException $e) {
    $error = "تعذر التحقق من ربط الحساب بالمريض.";
}

$pat_name = $patient['full_name'] ?? $patient['name'] ?? '';
$allowed_units = ['mg', 'ml', 'g'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$patient) {
        // منع الإضافة نهائياً إذا الحساب غير مرتبط بمريض
        $error = "حسابك غير مرتبط بمريض، لا يمكن إضافة أدوية.";
    } else {
        $target_user_id = $patient['user_id'];

        $medication_name        = trim($_POST['medication_name'] ?? '');
        $medication_shape       = trim($_POST['medication_shape'] ?? '');
        $medication_color       = trim($_POST['medication_color'] ?? '');
        $medication_description = trim($_POST['notes'] ?? '');

        $dose_amount = trim($_POST['dose_amount'] ?? '');
        $dose_unit   = trim($_POST['dose_unit'] ?? '');
        $dosage_time = trim($_POST['dosage_time'] ?? '');
        $dosage_date = trim($_POST['dosage_date'] ?? '');

        if (
            $medication_name === '' || $medication_shape === '' || $medication_color === '' ||
            $dose_amount === '' || $dose_unit === '' || $dosage_time === '' || $dosage_date === ''
        ) {
            $error = "يرجى تعبئة جميع الحقول المطلوبة";
        } elseif (!is_numeric($dose_amount) || $dose_amount <= 0) {
            $error = "الجرعة يجب أن تكون رقماً أكبر من صفر";
        } elseif (!in_array($dose_unit, $allowed_units, true)) {
            $error = "وحدة الجرعة غير صحيحة";
        } else {
            try {
                $pdo->beginTransaction();

                // 3. إدخال الدواء وربطه بالمريض المرتبط
                $stmt = $pdo->prepare("
                    INSERT INTO medications
                        (user_id, medication_name, medication_description, medication_shape, medication_color)
                    VALUES
                        (:user_id, :medication_name, :medication_description, :medication_shape, :medication_color)
                ");
                $stmt->execute([
                    'user_id'                => $target_user_id,
                    'medication_name'        => $medication_name,
                    'medication_description' => $medication_description,
                    'medication_shape'       => $medication_shape,
                    'medication_color'       => $medication_color
                ]);

                $medication_id = $pdo->lastInsertId();

                // 4. إدخال جدول الجرعات المرتبط بهذا الدواء
                $stmt2 = $pdo->prepare("
                    INSERT INTO dosage_schedules
                        (medication_id, dose_amount, dose_unit, dosage_time, dosage_date, status)
                    VALUES
                        (:medication_id, :dose_amount, :dose_unit, :dosage_time, :dosage_date, 'pending')
                ");
                $stmt2->execute([
                    'medication_id' => $medication_id,
                    'dose_amount'   => $dose_amount,
                    'dose_unit'     => $dose_unit,
                    'dosage_time'   => $dosage_time,
                    'dosage_date'   => $dosage_date
                ]);

                $schedule_id = (int)$pdo->lastInsertId();

                $pdo->commit();
                require_once __DIR__ . '/../includes/notify.php';
                notify_medication_added($pdo, (int)$target_user_id, $schedule_id,
                    $medication_name, $dose_amount, $dose_unit, $dosage_time, $dosage_date, 'التابع');

                $_SESSION['flash_success'] = "✅ تم إضافة الدواء وجرعته للمريض المرتبط بنجاح";
                header("Location: dep-add-medications.php");
                exit;

            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $error = "تعذر حفظ الدواء، حاولي مرة أخرى.";
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>إضافة دواء للمريض | لوحة التابع</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
<style>
.dashboard-layout {
    display: flex;
    align-items: stretch;
    min-height: calc(100vh - 140px);
    width: 100%;
}
.container{
    max-width:900px;
    margin:40px auto;
    padding:0 20px;
    flex-grow: 1;
}
.card{
    background:white;
    border-radius:20px;
    padding:35px;
    box-shadow:0 10px 25px rgba(0,0,0,.05);
}
.page-title{
    text-align:center;
    color:var(--primary-color, #008080);
    margin-bottom:8px;
}
.page-sub{
    text-align:center;
    color:var(--text-muted, #557075);
    font-size:14px;
    margin-bottom:25px;
}
.success-message{
    background:#d4edda;
    color:#155724;
    padding:15px;
    border-radius:10px;
    margin-bottom:20px;
    text-align:center;
    font-weight:bold;
}
.error-message{
    background:#f8d7da;
    color:#721c24;
    padding:15px;
    border-radius:10px;
    margin-bottom:20px;
    text-align:center;
    font-weight:bold;
}
.form-group{
    margin-bottom:18px;
}
label{
    display:block;
    margin-bottom:8px;
    font-weight:600;
}
input,
select,
textarea{
    width:100%;
    padding:12px;
    border:1px solid #ddd;
    border-radius:10px;
    box-sizing: border-box;
    font-family: inherit;
}
.row{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:15px;
}
.medicine-types{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:10px;
}
.medicine-types label{
    border:1px solid #ddd;
    border-radius:10px;
    padding:12px;
    text-align:center;
    cursor:pointer;
    margin-bottom: 0;
}
.medicine-types input{
    width:auto;
    margin-left:5px;
}
.actions{
    margin-top:20px;
    display:flex;
    gap:12px;
}
.save-btn{
    flex:1;
    background:var(--primary-color, #008080);
    color:white;
    border:none;
    border-radius:10px;
    padding:13px;
    cursor:pointer;
    font-weight: bold;
    font-family: inherit;
}
.save-btn:disabled{
    opacity:.5;
    cursor:not-allowed;
}
.cancel-btn{
    flex:1;
    background:white;
    color:var(--primary-color, #008080);
    border:2px solid var(--primary-color, #008080);
    border-radius:10px;
    padding:13px;
    cursor:pointer;
    font-weight: bold;
    font-family: inherit;
}
@media(max-width:768px){
    .row{
        grid-template-columns:1fr;
    }
    .medicine-types{
        grid-template-columns:1fr 1fr;
    }
}
</style>
</head>
<body>
    <div class="dashboard-layout">
        <!-- استدعاء سايدبار التابع المخصص داخل مجلد dependent -->
        <?php include __DIR__ . '/dep-sidebar.php'; ?>

        <div class="container">
            <div class="card">
                <h1 class="page-title">إضافة دواء للمريض المرتبط</h1>
                <?php if ($patient): ?>
                    <p class="page-sub">سيتم إضافة الدواء للمريض: <?php echo htmlspecialchars($pat_name); ?></p>
                <?php endif; ?>

                <?php if (!empty($message)): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>

                <?php if (!$patient): ?>
                    <div class="error-message">حسابك غير مرتبط بمريض حتى الآن، لذلك لا يمكن إضافة أدوية.</div>
                <?php endif; ?>

                <form method="POST">
                    <fieldset style="border:none;padding:0;margin:0;" <?php echo $patient ? '' : 'disabled'; ?>>

                    <div class="form-group">
                        <label>اسم الدواء</label>
                        <input type="text" name="medication_name" required placeholder="مثال: أموكسيسيلين">
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <label>الجرعة</label>
                            <input type="number" step="0.01" min="0.01" name="dose_amount" required>
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
                            <input type="date" name="dosage_date" value="<?php echo date('Y-m-d'); ?>" required>
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
                        <button type="submit" class="save-btn">حفظ الدواء للمريض</button>
                        <button type="reset" class="cancel-btn">إلغاء</button>
                    </div>

                    </fieldset>
                </form>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>