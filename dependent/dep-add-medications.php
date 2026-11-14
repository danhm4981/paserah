<?php
date_default_timezone_set('Asia/Riyadh');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db.php';

$current_user_id = $_SESSION['user_id'] ?? null;

// يجب أن يكون المستخدم مسجل دخول وحسابه تابع
if (!$current_user_id || ($_SESSION['account_type'] ?? '') !== 'dependent') {
    header("Location: ../login.php");
    exit;
}

$message = '';
$error = '';
$patient = null;
$link_status = null;

try {
    // المريض المرتبط بالتابع (فقط بعد موافقة المريض)
    $stmt = $pdo->prepare("
        SELECT u.user_id, u.username
        FROM patient_dependents pd
        JOIN users u ON u.user_id = pd.patient_user_id
        WHERE pd.dependent_user_id = ?
          AND pd.status = 'active'
        LIMIT 1
    ");
    $stmt->execute([$current_user_id]);
    $patient = $stmt->fetch() ?: null;

    if (!$patient) {
        $stmt = $pdo->prepare("
            SELECT status
            FROM patient_dependents
            WHERE dependent_user_id = ?
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([$current_user_id]);
        $link_status = $stmt->fetchColumn() ?: null;
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = "حدث خطأ أثناء التحقق من الربط، حاول مرة أخرى لاحقاً.";
}

// معرّف المريض يؤخذ دائماً من الربط في القاعدة وليس من النموذج
$target_user_id = $patient ? (int) $patient['user_id'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $target_user_id) {
    $medication_name = trim($_POST['medication_name'] ?? '');
    $medication_shape = trim($_POST['medication_shape'] ?? '');
    $medication_color = trim($_POST['medication_color'] ?? '');
    $medication_description = trim($_POST['notes'] ?? '');

    $dose_amount = trim($_POST['dose_amount'] ?? '');
    $dose_unit = trim($_POST['dose_unit'] ?? '');
    $dosage_time = trim($_POST['dosage_time'] ?? '');
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');

    if (
        $medication_name === '' ||
        $medication_shape === '' ||
        $medication_color === '' ||
        $dose_amount === '' ||
        $dose_unit === '' ||
        $dosage_time === '' ||
        $start_date === '' ||
        $end_date === ''
    ) {
        $error = "يرجى تعبئة جميع الحقول المطلوبة";
    } elseif ($end_date < $start_date) {
        $error = "تاريخ النهاية يجب أن يكون بعد تاريخ البداية";
    } else {
        // رفع الصورة (اختياري) إلى نفس مجلد uploads الخاص بالمريض
        $image_name = '';

        if (
            isset($_FILES['medication_image']) &&
            $_FILES['medication_image']['error'] === UPLOAD_ERR_OK
        ) {
            $ext = strtolower(pathinfo($_FILES['medication_image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($ext, $allowed, true)) {
                $error = "صيغة الصورة غير مدعومة";
            } else {
                $upload_dir = __DIR__ . '/../uploads/';

                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $image_name = time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;

                move_uploaded_file(
                    $_FILES['medication_image']['tmp_name'],
                    $upload_dir . $image_name
                );
            }
        }

        if ($error === '') {
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("
                    INSERT INTO medications
                    (
                        user_id,
                        medication_name,
                        medication_image,
                        medication_description,
                        medication_shape,
                        medication_color
                    )
                    VALUES
                    (
                        :user_id,
                        :medication_name,
                        :medication_image,
                        :medication_description,
                        :medication_shape,
                        :medication_color
                    )
                ");

                $stmt->execute([
                    'user_id' => $target_user_id,
                    'medication_name' => $medication_name,
                    'medication_image' => $image_name,
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
                        start_date,
                        end_date,
                        status
                    )
                    VALUES
                    (
                        :medication_id,
                        :dose_amount,
                        :dose_unit,
                        :dosage_time,
                        :start_date,
                        :end_date,
                        'pending'
                    )
                ");

                $stmt2->execute([
                    'medication_id' => $medication_id,
                    'dose_amount' => $dose_amount,
                    'dose_unit' => $dose_unit,
                    'dosage_time' => $dosage_time,
                    'start_date' => $start_date,
                    'end_date' => $end_date
                ]);

                $pdo->commit();

                $message = "✅ تم حفظ الدواء للمريض بنجاح";

            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log($e->getMessage());
                $error = "تعذر حفظ الدواء، حاول مرة أخرى.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>إضافة دواء للمريض | بصيرة</title>

<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

<style>

:root{
    --primary-color:#008080;
    --primary-dark:#005a5a;
    --bg-light:#f4fbfb;
    --text-color:#2b3e42;
    --text-muted:#6c7a7d;
    --white:#ffffff;
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Tajawal',sans-serif;
}

body{
    background:var(--bg-light);
    color:var(--text-color);
}

header{
    background:var(--white);
    padding:15px 5%;
    box-shadow:0 2px 10px rgba(0,0,0,.05);
}

nav{
    display:flex;
    justify-content:space-between;
    align-items:center;
    max-width:1200px;
    margin:auto;
}

.logo{
    display:flex;
    align-items:center;
    gap:10px;
    font-size:24px;
    font-weight:800;
    color:var(--primary-color);
}

.nav-links{
    list-style:none;
    display:flex;
    gap:25px;
}

.nav-links a{
    text-decoration:none;
    color:var(--text-color);
}

.buttons{
    display:flex;
    gap:10px;
}

.btn{
    padding:8px 20px;
    border-radius:20px;
    text-decoration:none;
    font-weight:600;
}

.btn-outline{
    border:1px solid var(--primary-color);
    color:var(--primary-color);
}

.btn-primary{
    background:var(--primary-color);
    color:#fff;
}

.card{
    width: 700px;
    max-width: 100%;
    background: #fff;
    border-radius: 20px;
    padding: 35px;
    box-shadow: 0 10px 25px rgba(0,0,0,.05);
}

.page-title{
    text-align:center;
    color:var(--primary-color);
    margin-bottom:8px;
}

.page-sub{
    text-align:center;
    color:var(--text-muted);
    font-size:14px;
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

.notice-message{
    background:#fff8e1;
    color:#7a5b00;
    padding:20px;
    border-radius:10px;
    text-align:center;
    font-weight:bold;
}

.form-group{
    margin-bottom:18px;
}

textarea{
    width:100%;
    padding:12px;
    border:1px solid #ddd;
    border-radius:10px;
    min-height:48px;
    resize:vertical;
}
input,select,textarea{width:100%;
padding:12px;border:1px solid #ddd;border-radius:10px;}

.row{
    display:grid;
    grid-template-columns:repeat(3,1fr);
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
    background:var(--primary-color);
    color:white;
    border:none;
    border-radius:10px;
    padding:13px;
    cursor:pointer;
}

.cancel-btn{
    flex:1;
    background:white;
    color:var(--primary-color);
    border:2px solid var(--primary-color);
    border-radius:10px;
    padding:13px;
    cursor:pointer;
}

footer{
    background:var(--primary-color);
    color:white;
    text-align:center;
    padding:18px;
    margin-top:50px;
}

@media(max-width:768px){

    .row{
        grid-template-columns:1fr;
    }

    .medicine-types{
        grid-template-columns:1fr 1fr;
    }

    .nav-links{
        display:none;
    }
}
.dashboard-layout {
    display: flex;
    align-items: stretch;
    min-height: calc(100vh - 140px);
    width: 100%;
}
.container{
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding: 40px;
}
.page-header{
    background:#fff;
    border-radius:20px;
    padding:35px;
    text-align:center;
    margin-bottom:25px;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
}

label{
    display:inline-block;
    background:#e9f8f8;
    color:#008080;
    padding:7px 14px;
    border-radius:10px;
    font-weight:700;
    margin-bottom:10px;
    border-right:4px solid #008080;
}

select{
    width:100%;
    padding:12px;
    height:50px;
    border:1px solid #ddd;
    border-radius:10px;
}
</style>
</head>

<body>
<header>
<nav>

<div class="logo">
<img src="../logo.png" alt="بصيرة" style="height:45px;">
<span>بصيرة</span>
</div>

<ul class="nav-links">
<li><a href="../index.php">الرئيسية</a></li>
<li><a href="../about.php">عن بصيرة</a></li>
<li><a href="../contact.php">تواصل معنا</a></li>
</ul>

<div class="buttons">
<a href="../register.php" class="btn btn-outline">إنشاء حساب</a>
<a href="../login.php" class="btn btn-primary">تسجيل الدخول</a>
</div>

</nav>
</header>


<div class="dashboard-layout">

    <?php include __DIR__ . '/dep-sidebar.php'; ?>

    <div class="container">

        <div class="card">

            <div class="page-header">
                <h1 class="page-title">إضافة دواء للمريض</h1>
                <?php if ($patient): ?>
                    <p class="page-sub">المريض: <?= htmlspecialchars($patient['username'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </div>

            <?php if (!empty($message)): ?>
                <div class="success-message">
                    <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <?php if (!$patient): ?>

                <div class="notice-message">
                    <?php if ($link_status === 'pending'): ?>
                        طلب الربط بانتظار موافقة المريض. تقدر تضيف الأدوية بعد القبول.
                    <?php elseif ($link_status === 'rejected'): ?>
                        تم رفض طلب الربط من قبل المريض، ولا يمكن إضافة أدوية.
                    <?php else: ?>
                        حسابك غير مرتبط بمريض حتى الآن.
                    <?php endif; ?>
                </div>

            <?php else: ?>

            <form method="POST" enctype="multipart/form-data">

<div class="form-group">
<label> اسم الدواء</label>
<input
type="text"
name="medication_name"
required
placeholder="مثال: أموكسيسيلين">
</div>

<div class="form-group">
<label> صورة الدواء</label>
    <input type="file"
           name="medication_image"
           accept="image/*">
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

</div>

<div class="form-group">

<label>شكل الدواء</label>

<div class="medicine-types">

<label>
<input type="radio" name="medication_shape" value="أقراص" required>
أقراص
</label>

<label>
<input type="radio" name="medication_shape" value="كبسول">
كبسول
</label>

<label>
<input type="radio" name="medication_shape" value="شراب">
شراب
</label>

<label>
<input type="radio" name="medication_shape" value="حقن">
حقن
</label>

<label>
<input type="radio" name="medication_shape" value="قطرة">
قطرة
</label>

<label>
<input type="radio" name="medication_shape" value="لصقة">
لصقة
</label>

</div>

</div>


<div class="row">

    <div class="form-group">
        <label>وقت الجرعة</label>
        <input type="time" name="dosage_time" required>
    </div>

    <div class="form-group">
        <label>من تاريخ</label>
        <input type="date" name="start_date" required>
    </div>

    <div class="form-group">
        <label>إلى تاريخ</label>
        <input type="date" name="end_date" required>
    </div>

</div>

<div class="form-group">
    <label>ملاحظات</label>
    <textarea
        name="notes"
        rows="2"
        maxlength="3000"
        placeholder="يؤخذ بعد الأكل"></textarea>
</div>

<div class="actions">
    <button type="submit" class="save-btn">حفظ الدواء</button>
    <button type="reset" class="cancel-btn">إلغاء</button>
</div>

            </form>

            <?php endif; ?>

        </div>

    </div>

</div>


<footer>
    <p>© <?php echo date("Y"); ?> بصيرة - جميع الحقوق محفوظة</p>
</footer>

</body>
</html>
