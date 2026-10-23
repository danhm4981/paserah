<?php
require_once __DIR__ . '/db.php';
include __DIR__ . '/includes/header.php';
<<<<<<< HEAD

$success = ""; // اجعلي القيمة فارغة في البداية حتى لا تظهر كلمة contact

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST["name"] ?? "", ENT_QUOTES, 'UTF-8');
    $success = "تم إرسال رسالتك بنجاح يا " . $name;
=======
$success = "contact";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
$name = htmlspecialchars($_POST["name"] ?? "", ENT_QUOTES, 'UTF-8');
$success = "تم إرسال رسالتك بنجاح يا " . $name;
>>>>>>> 13e589139f5b5777ac133166ef3bd2eb752d5c01
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<<<<<<< HEAD
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
<title>تواصل معنا - بصيرة</title>
<style>
body {
    margin: 0;
    font-family: Tahoma, Arial, sans-serif;
    background: #f5f7f5;
    color: #36575b;
}

=======
<title>تواصل معنا - بصيرة</title>
<style>
body {
margin: 0;
font-family: Tahoma, Arial, sans-serif;
background: #f5f7f5;
color: #36575b;
}
.container {
background: white;
width: 80%;
max-width: 500px;
margin: 70px auto;
padding: 30px;
border-radius: 25px;
box-shadow: 0 5px 20px #ddd;
}
h1 {
text-align: center;
margin-bottom: 30px;
}
label {
display: block;
margin: 15px 0 8px;
font-weight: bold;
}
input, textarea {
width: 100%;
padding: 13px;
border: 1px solid #d5dddd;
border-radius: 12px;
box-sizing: border-box;
font-family: inherit;
}
textarea {
height: 120px;
resize: vertical;
}
button {
width: 100%;
margin-top: 22px;
padding: 15px;
background: #20a49c;
color: white;
border: none;
border-radius: 30px;
font-family: inherit;
font-size: 16px;
cursor: pointer;
}
button:hover {
    background: #087b79;
}
.success {
background: #e5f8ed;
color: #176c4c;
padding: 12px;
border-radius: 10px;
 text-align: center;
}
@media (max-width: 600px) {
.container {
width: auto;
margin: 25px 15px;
padding: 22px;
}
>>>>>>> 13e589139f5b5777ac133166ef3bd2eb752d5c01
.dashboard-layout {
    display: flex;
    min-height: calc(100vh - 140px);
    max-width: 1400px;
    margin: 0 auto;
    width: 100%;
}
<<<<<<< HEAD
.container {
    background: white;
    width: 80%;
    max-width: 500px;
    margin: 70px auto;
    padding: 30px;
    border-radius: 25px;
    box-shadow: 0 5px 20px #ddd;
}
h1 {
    text-align: center;
    margin-bottom: 30px;
}
label {
    display: block;
    margin: 15px 0 8px;
    font-weight: bold;
}
input, textarea {
    width: 100%;
    padding: 13px;
    border: 1px solid #d5dddd;
    border-radius: 12px;
    box-sizing: border-box;
    font-family: inherit;
}
textarea {
    height: 120px;
    resize: vertical;
}
button {
    width: 100%;
    margin-top: 22px;
    padding: 15px;
    background: #20a49c;
    color: white;
    border: none;
    border-radius: 30px;
    font-family: inherit;
    font-size: 16px;
    cursor: pointer;
}
button:hover {
    background: #087b79;
}
.success {
    background: #e5f8ed;
    color: #176c4c;
    padding: 12px;
    border-radius: 10px;
    text-align: center;
    margin-bottom: 20px;
}
@media (max-width: 600px) {
    .container {
        width: auto;
        margin: 25px 15px;
        padding: 22px;
    }
    .dashboard-layout {
        flex-direction: column;
    }
=======
<div class="dashboard-layout">
            <?php include __DIR__ . '/includes/sidebar.php';?>
>>>>>>> 13e589139f5b5777ac133166ef3bd2eb752d5c01
}
</style>
</head>
<body>
<<<<<<< HEAD
    <div class="dashboard-layout">
        <?php include __DIR__ . '/includes/sidebar.php';?>
    
        <main class="container">
            <h1>تواصل معنا</h1>
            
            <?php if ($success != "") { ?>
                <p class="success"><?php echo $success; ?></p>
            <?php } ?>

            <form method="POST">
                <section>
                    <label for="name">الاسم</label>
                    <input type="text" id="name" name="name" placeholder="اكتب اسمك" required>
                </section>
                <section>
                    <label for="email">البريد الإلكتروني</label>
                    <input type="email" id="email" name="email" placeholder="example@email.com" required>
                </section>
                <section>
                    <label for="message">الرسالة</label>
                    <textarea id="message" name="message" placeholder="اكتب رسالتك" required></textarea>
                </section>
                <button type="submit">إرسال الرسالة</button>
            </form>
        </main>
    </div>

    <?php include __DIR__ . '/includes/footer.php';?>
</body>
</html>
=======
     <div class="logo">
            <img src="logo.png" alt="شعار بصيرة">
            <span>بصيرة</span>
        </div>
<main class="container">
<h1>تواصل معنا</h1>
 <?php if ($success != "") { ?>
<p class="success">
 <?php echo $success; ?>
</p>
<?php } ?>
<form method="POST">
<section>
<label for="name">الاسم</label>
<input type="text" id="name" name="name"
 placeholder="اكتب اسمك" required>
</section>
<section>
<label for="email">البريد الإلكتروني</label>
<input type="email" id="email" name="email"
 placeholder="example@email.com" required>
</section>
<section>
<label for="message">الرسالة</label>
<textarea id="message" name="message"
placeholder="اكتب رسالتك" required></textarea>
</section>
<button type="submit">إرسال الرسالة</button>
    </form>
</main>
<?php include __DIR__ . '/includes/footer.php';?>
</body>
</html>
>>>>>>> 13e589139f5b5777ac133166ef3bd2eb752d5c01
