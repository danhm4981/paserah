<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__.'/db.php';
include __DIR__.'/includes/header.php';

$lang=$_GET['lang']??'ar';$lang=in_array($lang,['ar','en'],true)?$lang:'ar';$en=$lang==='en';
$t=['title'=>$en?'Choose account type':'اختر نوع الحساب','subtitle'=>$en?'Create Account':'إنشاء حساب','patient_title'=>$en?'Patient Account':'حساب مريض','dependent_title'=>$en?'Dependent Account':'حساب تابع','patient_btn'=>$en?'Create Patient Account':'إنشاء حساب مريض','dependent_btn'=>$en?'Create Dependent Account':'إنشاء حساب تابع','username'=>$en?'Username':'اسم المستخدم','phone'=>$en?'Phone Number':'رقم الجوال','email'=>$en?'Email Address':'البريد الإلكتروني','patient_email'=>$en?'Patient Email Address':'البريد الإلكتروني للمريض','birth'=>$en?'Date of Birth':'تاريخ الميلاد','password'=>$en?'Password':'كلمة المرور','confirm'=>$en?'Confirm Password':'تأكيد كلمة المرور','submit'=>$en?'Create Account':'إنشاء الحساب','desc1'=>$en?'To manage medications doses and their schedules':'لإدارة الأدوية والجرعات ومواعيد تناولها','desc2'=>$en?'To track the patient’s medications and doses after linking approval':'لمتابعة أدوية وجرعات المريض بعد الموافقة على الربط','required'=>$en?'Please fill in all fields.':'فضلاً، عبّي جميع الحقول.','invalid'=>$en?'Please check your information.':'تحققي من صحة البيانات المدخلة.','mismatch'=>$en?'Passwords do not match.':'كلمتا المرور غير متطابقتين.','short'=>$en?'Password must be at least 8 characters.':'كلمة المرور يجب أن تكون 8 أحرف على الأقل.','exists'=>$en?'Username or email already exists.':'اسم المستخدم أو البريد الإلكتروني مستخدم مسبقًا.','error'=>$en?'Could not create account. Please try again.':'تعذر إنشاء الحساب. حاولي مرة أخرى.'];
$message='';$selected_type=$_GET['type']??$_POST['account_type']??'';
if(!in_array($selected_type,['patient','dependent'],true))$selected_type='';

if($_SERVER['REQUEST_METHOD']==='POST'&&$selected_type){
$name=trim($_POST['username']??'');$phone=trim($_POST['phone']??'');$email=trim($_POST['email']??'');$birth=$_POST['birth_date']??'';$pass=$_POST['password']??'';$confirm=$_POST['confirm_password']??'';$patient_email=trim($_POST['patient_email']??'');
if(!$name||!$email||!$pass||($selected_type==='patient'&&(!$phone||!$birth||!$confirm))||($selected_type==='dependent'&&!$patient_email))$message=$t['required'];
elseif(strlen($name)>50||strlen($email)>100||!filter_var($email,FILTER_VALIDATE_EMAIL)||($selected_type==='patient'&&(strlen($phone)>20||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$birth)||!checkdate((int)substr($birth,5,2),(int)substr($birth,8,2),(int)substr($birth,0,4))||$birth>date('Y-m-d')))||($selected_type==='dependent'&&(!filter_var($patient_email,FILTER_VALIDATE_EMAIL)||$patient_email===$email)))$message=$t['invalid'];
elseif($selected_type==='patient'&&$pass!==$confirm)$message=$t['mismatch'];
elseif(strlen($pass)<8)$message=$t['short'];
else{
try{
$check=$pdo->prepare('SELECT user_id FROM users WHERE username=? OR email=?');$check->execute([$name,$email]);
if($check->fetch())$message=$t['exists'];
else{
$patient_id=null;
if($selected_type==='dependent'){
$stmt=$pdo->prepare("SELECT user_id FROM users WHERE email=? AND account_type='patient'");
$stmt->execute([$patient_email]);$patient_id=$stmt->fetchColumn();
if(!$patient_id)$message=$t['invalid'];
}
if(!$message){
$stmt=$pdo->prepare('INSERT INTO users (username,password,phone,email,birth_date,account_type) VALUES (?,?,?,?,?,?)');
$stmt->execute([$name,password_hash($pass,PASSWORD_DEFAULT),$phone,$email,$birth,$selected_type]);
$new_user_id = (int) $pdo->lastInsertId();
if ($selected_type === 'dependent') {
    $stmt = $pdo->prepare("
        INSERT INTO patient_dependents
        (patient_user_id, dependent_user_id, relationship, status)
        VALUES (?, ?, ?, 'pending')
    ");

    $stmt->execute([$patient_id, $new_user_id, 'تابع']);
}
$_SESSION['user_id'] = $new_user_id;
$_SESSION['user_type'] = $selected_type;
$_SESSION['account_type'] = $selected_type;

if ($selected_type === 'patient') {
    header('Location: dashboard.php');
} else {
    header('Location: dep-dashboard.php');
}
exit();
}
}
}catch(PDOException $e){error_log($e->getMessage());$message=$t['error'];}
}
}
?>
<!DOCTYPE html>
<html lang="<?= $en?'en':'ar' ?>" dir="<?= $en?'ltr':'rtl' ?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $t['subtitle'] ?> | بصيرة</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
<style>
body{background:var(--bg-light);color:var(--text-color);min-height:100vh;display:flex;flex-direction:column}
main{width:90%;max-width:850px;margin:40px auto;flex:1;display:flex;flex-direction:column;align-items:center}
h1{font-size:28px;margin-bottom:6px;color:var(—text-color);text-align:center}
.sub{color:var(—text-muted);font-size:14px;margin-bottom:30px;text-align:center}
.cards{display:flex;gap:25px;width:100%;justify-content:center}
.card-type{flex:1;background:#ffffff;border:1px solid #e0f0f0;border-radius:16px;padding:35px 20px;text-align:center;display:flex;flex-direction:column;align-items:center;justify-content:center;box-shadow:0 4px 15px rgba(0,0,0,.02)}
.icon{font-size:38px;margin-bottom:15px;display:flex;align-items:center;justify-content:center;width:100%}
.card-title{font-size:18px;font-weight:700;margin-bottom:10px;color:var(—text-color)}
.card-desc{font-size:13px;color:var(—text-muted);line-height:1.6;margin-bottom:25px}
.form-box{width:100%;max-width:480px;background:#ffffff;padding:30px;border-radius:16px;box-shadow:0 4px 15px rgba(0,0,0,.02);border:1px solid #e0f0f0}
label{display:block;margin:14px 0 6px;font-size:13px;font-weight:700;color:var(—text-color)}
input{width:100%;padding:11px;border:1px solid #dcdcdc;border-radius:8px;background:#fafafa;font-size:14px}
input:focus{outline:2px solid var(—primary-color);background:var(—white)}
.message{text-align:center;padding:12px;margin:12px 0;background:#fff0ed;color:#a33b2e;border-radius:8px;font-size:13px;width:100%;max-width:480px}
footer{text-align:center;padding:20px;background:var(—primary-dark);color:var(—white);font-size:13px;margin-top:auto}
@media(max-width:768px){.cards{flex-direction:column}.nav-links{display:none}}
</style>
</head>
<body>
<main>
<?php if($message): ?><div class="message"><?= htmlspecialchars($message,ENT_QUOTES,'UTF-8') ?></div><?php endif; ?>
<?php if(!$selected_type): ?>
<h1><?= $t['subtitle'] ?></h1><p class="sub"><?= $t['title'] ?></p>
<div class="cards">
<div class="card-type"><div class="icon">👩‍⚕️</div><div class="card-title"><?= $t['patient_title'] ?></div><div class="card-desc"><?= $t['desc1'] ?></div><a href="register.php?type=patient&lang=<?= $lang ?>" class="btn btn-primary" style="width:100%"><?= $t['patient_btn'] ?></a></div>
<div class="card-type"><div class="icon">👥</div><div class="card-title"><?= $t['dependent_title'] ?></div><div class="card-desc"><?= $t['desc2'] ?></div><a href="register.php?type=dependent&lang=<?= $lang ?>" class="btn btn-primary" style="width:100%"><?= $t['dependent_btn'] ?></a></div>
</div>
<?php else: ?>
<h1><?= $selected_type==='patient'?$t['patient_btn']:$t['dependent_btn'] ?></h1>
<div class="form-box">
<form method="POST" action="register.php?type=<?= $selected_type ?>&lang=<?= $lang ?>">
<input type="hidden" name="account_type" value="<?= $selected_type ?>">
<label for="username"><?= $t['username'] ?></label><input id="username" name="username" maxlength="50" autocomplete="off" required>
<label for="email"><?= $t['email'] ?></label><input id="email" name="email" type="email" maxlength="100" required>
<?php if($selected_type==='patient'): ?>
<label for="phone"><?= $t['phone'] ?></label><input id="phone" name="phone" type="tel" maxlength="20" required>
<label for="birth_date"><?= $t['birth'] ?></label><input id="birth_date" name="birth_date" type="date" max="<?= date('Y-m-d') ?>" required>
<?php else: ?>
<label for="patient_email"><?= $t['patient_email'] ?></label><input id="patient_email" name="patient_email" type="email" maxlength="100" required>
<?php endif; ?>
<label for="password"><?= $t['password'] ?></label><input id="password" name="password" type="password" minlength="8" required>
<?php if($selected_type==='patient'): ?>
<label for="confirm_password"><?= $t['confirm'] ?></label><input id="confirm_password" name="confirm_password" type="password" minlength="8" required>
<?php endif; ?>
<button type="submit" class="btn btn-primary" style="width:100%;margin-top:20px"><?= $t['submit'] ?></button>
</form>
</div>
<?php endif; ?>
</main>
<?php include __DIR__.'/includes/footer.php'; ?>
</body>
</html>