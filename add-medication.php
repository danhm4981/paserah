<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

$current_user_id = $_SESSION['user_id'] ?? null;
$user_role = strtolower(trim($_SESSION['role'] ?? 'patient'));

if (!$current_user_id) {
    header("Location: login.php");
    exit;
}

if ($user_role === 'dependent') {
    $stmt_p = $pdo->prepare("
        SELECT patient_user_id
        FROM patient_dependents
        WHERE dependent_user_id = ?
    ");
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
    $start_date = trim($_POST['start_date'] ?? '');
$end_date = trim($_POST['end_date'] ?? '');

    $image_name = '';

if (
    isset($_FILES['medication_image']) &&
    $_FILES['medication_image']['error'] == 0
) {

    $upload_dir = "uploads/";

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $image_name = time() . "_" . basename($_FILES['medication_image']['name']);

    move_uploaded_file(
        $_FILES['medication_image']['tmp_name'],
        $upload_dir . $image_name
    );
}
    if (
    empty($medication_name) ||
    empty($medication_shape) ||
    empty($medication_color) ||
    empty($dose_amount) ||
    empty($dose_unit) ||
    empty($dosage_time) ||
    empty($start_date) ||
empty($end_date)
) {

        $error = "يرجى تعبئة جميع الحقول المطلوبة";

    } else {

        try {

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

            $message = "✅ تم حفظ الدواء بنجاح";

        } catch(PDOException $e) {

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
    background: #fff;
    border-radius: 20px;
    padding: 35px;
    box-shadow: 0 10px 25px rgba(0,0,0,.05);
}


.page-title{
    text-align:center;
    color:var(--primary-color);
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
    display:block;
    border-right:4px solid #008080;
    padding-right:10px;
    margin-bottom:10px;
    font-weight:700;
    color:#2b3e42;
}
label{
    display:inline-block;
    background:#e9f8f8;
    color:#008080;
    padding:7px 14px;
    border-radius:10px;
    font-weight:700;
    margin-bottom:10px;
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
<img src="logo.png" alt="بصيرة" style="height:45px;">
<span>بصيرة</span>
</div>

<ul class="nav-links">
<li><a href="index.php">الرئيسية</a></li>
<li><a href="about.php">عن بصيرة</a></li>
<li><a href="contact.php">تواصل معنا</a></li>
</ul>

<div class="buttons">
<a href="register.php" class="btn btn-outline">إنشاء حساب</a>
<a href="login.php" class="btn btn-primary">تسجيل الدخول</a>
</div>

</nav>
</header>


<div class="dashboard-layout">

    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <div class="container">

        <div class="card">

            <div class="page-header">
    <h1 class="page-title">إضافة دواء جديد</h1>
</div>

            <?php if(!empty($message)): ?>
                <div class="success-message">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php if(!empty($error)): ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">

                <!-- جميع حقول الفورم هنا -->
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

</div>
            </form>

        </div>

    </div>

</div>


<footer>
    <p>© <?php echo date("Y"); ?> بصيرة - جميع الحقوق محفوظة</p>
</footer>

</body>
</html>
