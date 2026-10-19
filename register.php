<?php
require_once __DIR__.'/db.php';
$lang=$_GET['lang']??'ar';
$lang=in_array($lang,['ar','en'],true)?$lang:'ar';
$en=$lang==='en';
$t=['title'=>$en?'Your first step toward better medication organization':'خطوتك الأولى نحو تنظيم أفضل لأدويتك','subtitle'=>$en?'Create Account':'إنشاء حساب جديد','username'=>$en?'Username':'اسم المستخدم','phone'=>$en?'Phone Number':'رقم الجوال','email'=>$en?'Email Address':'البريد الإلكتروني','birth'=>$en?'Date of Birth':'تاريخ الميلاد','type'=>$en?'Account Type':'نوع الحساب','patient'=>$en?'Patient':'مريض','dependent'=>$en?'Dependent':'تابع','desc1'=>$en?'To manage medications doses and their schedules':'لإدارة الأدوية والجرعات ومواعيد تناولها','desc2'=>$en?'To track the patient’s medications and doses after linking approval':'لمتابعة أدوية وجرعات المريض بعد الموافقة على الربط','password'=>$en?'Password':'كلمة المرور','confirm'=>$en?'Confirm Password':'تأكيد كلمة المرور','submit'=>$en?'Create Account':'إنشاء الحساب','have'=>$en?'Already have an account?':'لديك حساب بالفعل؟','login'=>$en?'Log in':'سجّل دخولك','required'=>$en?'Please fill in all fields.':'فضلاً، عبّي جميع الحقول.','invalid'=>$en?'Please check your information.':'تحققي من صحة البيانات المدخلة.','mismatch'=>$en?'Passwords do not match.':'كلمتا المرور غير متطابقتين.','short'=>$en?'Password must be at least 8 characters.':'كلمة المرور يجب أن تكون 8 أحرف على الأقل.','exists'=>$en?'Username or email already exists.':'اسم المستخدم أو البريد الإلكتروني مستخدم مسبقًا.','success'=>$en?'Account created successfully!':'تم إنشاء حسابك بنجاح! 🎉','error'=>$en?'Could not create account. Please try again.':'تعذر إنشاء الحساب. حاولي مرة أخرى.','footer'=>$en?'© Baseerah - All rights reserved':'© بصيرة - جميع الحقوق محفوظة'];
$message='';$success=false;
if($_SERVER['REQUEST_METHOD']==='POST'){
$name=trim($_POST['username']??'');$phone=trim($_POST['phone']??'');$email=trim($_POST['email']??'');$birth=$_POST['birth_date']??'';$type=$_POST['account_type']??'';$pass=$_POST['password']??'';$confirm=$_POST['confirm_password']??'';
if(!$name||!$phone||!$email||!$birth||!$type||!$pass||!$confirm)$message=$t['required'];
elseif(strlen($name)>50||strlen($phone)>20||strlen($email)>100||!filter_var($email,FILTER_VALIDATE_EMAIL)||!in_array($type,['patient','dependent'],true)||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$birth)||!checkdate((int)substr($birth,5,2),(int)substr($birth,8,2),(int)substr($birth,0,4))||$birth>date('Y-m-d'))$message=$t['invalid'];
elseif($pass!==$confirm)$message=$t['mismatch'];
elseif(strlen($pass)<8)$message=$t['short'];
else{
try{
$check=$pdo->prepare('SELECT user_id FROM users WHERE username=? OR email=?');$check->execute([$name,$email]);
if($check->fetch())$message=$t['exists'];
else{$stmt=$pdo->prepare('INSERT INTO users (username,password,phone,email,birth_date,account_type) VALUES (?,?,?,?,?,?)');$stmt->execute([$name,password_hash($pass,PASSWORD_DEFAULT),$phone,$email,$birth,$type]);$message=$t['success'];$success=true;}
}catch(PDOException $e){error_log($e->getMessage());$message=$t['error'];}
}
}
?>
<!DOCTYPE html>
<html lang="<?= $en?'en':'ar' ?>" dir="<?= $en?'ltr':'rtl' ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $t['subtitle'] ?> | بصيرة</title>
<style>
*{box-sizing:border-box}
body{margin:0;font-family:Tahoma,Arial,sans-serif;background:#f4f7f6;color:#0f4c5c;min-height:100vh;display:flex;flex-direction:column}
nav{background:#0f4c5c;padding:16px 20px;display:flex;justify-content:flex-start;align-items:center;box-shadow:0 2px 8px #00000008;height:65px;direction:rtl}
.logo{font-size:23px;font-weight:bold;color:#fff;text-decoration:none;transform:translateY(-2px)}
main{position:relative;width:92%;max-width:500px;margin:25px auto;padding:26px;background:#fff;border-radius:16px;box-shadow:0 4px 15px #00000008;flex:1}
.lang{position:absolute;top:14px;left:14px;padding:7px 16px;border:1px solid #ddd;border-radius:25px;color:#00a896;text-decoration:none;font-size:13px;font-weight:bold}
h1{text-align:center;font-size:23px;margin:25px 0 8px}
.sub{text-align:center;color:#777;font-size:13px;line-height:1.8;margin-bottom:20px}
label{display:block;margin:12px 0 6px;font-size:14px;font-weight:bold}
input:not([type=radio]){width:100%;padding:11px;border:1px solid #e0e0e0;border-radius:9px;background:#fafafa}
input:focus{outline:2px solid #b4dcd5}
.options{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.card{position:relative;margin:0;cursor:pointer}
.card input{position:absolute;opacity:0}
.content{display:flex;flex-direction:column;align-items:center;text-align:center;gap:10px;padding:18px 10px;border:1px solid #dce5e2;border-radius:13px;background:#fafcfb;height:200px;justify-content:center}
.icon{font-size:22px;background:#e6f6f3;border-radius:50%;padding:9px}
.desc{font-size:12px;color:#777;line-height:1.7;font-weight:normal}
.card:has(input:checked) .content{border:2px solid #00a896;background:#f0faf8}
button{width:100%;padding:13px;margin-top:22px;border:0;border-radius:20px;background:#00a896;color:#fff;font-weight:bold;font-size:15px;cursor:pointer}
button:hover{background:#028090}
.message{text-align:center;padding:10px;margin:12px 0;background:#fff0ed;color:#a33b2e;border-radius:8px;font-size:13px;line-height:1.8}
.message.success{background:#e5f6eb;color:#21683c}
.login{text-align:center;margin-top:18px;font-size:13px}
.login a{color:#00a896;text-decoration:none;font-weight:bold}
footer{text-align:center;padding:15px;background:#0f4c5c;color:#fff;font-size:13px}
@media(max-width:380px){main{padding:22px 16px}.options{grid-template-columns:1fr}}
</style>
</head>
<body>
<nav><a class="logo" href="index.php">بصيرة</a></nav>
<main>
<a class="lang" href="?lang=<?= $en?'ar':'en' ?>"><?= $en?'AR':'EN' ?></a>
<h1><?= $t['subtitle'] ?></h1>
<p class="sub"><?= $t['title'] ?></p>
<?php if($message): ?><div class="message <?= $success?'success':'' ?>"><?= htmlspecialchars($message,ENT_QUOTES,'UTF-8') ?></div><?php endif; ?>
<?php if(!$success): ?>
<form method="POST" action="?lang=<?= $lang ?>">
<label for="username"><?= $t['username'] ?></label>
<input id="username" name="username" maxlength="50" autocomplete="username" required>
<label for="phone"><?= $t['phone'] ?></label>
<input id="phone" name="phone" type="tel" maxlength="20" autocomplete="tel" required>
<label for="email"><?= $t['email'] ?></label>
<input id="email" name="email" type="email" maxlength="100" autocomplete="email" required>
<label for="birth_date"><?= $t['birth'] ?></label>
<input id="birth_date" name="birth_date" type="date" max="<?= date('Y-m-d') ?>" required>
<label><?= $t['type'] ?></label>
<div class="options">
<label class="card"><input type="radio" name="account_type" value="patient" required><span class="content"><span class="icon">👩‍⚕️</span><?= $t['patient'] ?><span class="desc"><?= $t['desc1'] ?></span></span></label>
<label class="card"><input type="radio" name="account_type" value="dependent" required><span class="content"><span class="icon">👨‍👩‍👧</span><?= $t['dependent'] ?><span class="desc"><?= $t['desc2'] ?></span></span></label>
</div>
<label for="password"><?= $t['password'] ?></label>
<input id="password" name="password" type="password" minlength="8" autocomplete="new-password" required>
<label for="confirm_password"><?= $t['confirm'] ?></label>
<input id="confirm_password" name="confirm_password" type="password" minlength="8" autocomplete="new-password" required>
<button type="submit"><?= $t['submit'] ?></button>
</form>
<?php endif; ?>
<div class="login"><?= $t['have'] ?> <a href="login.php?lang=<?= $lang ?>"><?= $t['login'] ?></a></div>
</main>
<footer><?= $t['footer'] ?></footer>
</body>
</html>