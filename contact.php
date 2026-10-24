<?php
require_once __DIR__ . '/db.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>تواصل معنا - بصيرة</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
<style>
{
box-sizing: border-box;
}
body {
margin: 0;
font-family: 'Tajawal', sans-serif;
background: #f5fafa;
color: #2b3e42;
}
.contact-container {
max-width: 1050px;
width: 92%;
margin: 40px auto 60px;
}
.contact-heading {
text-align: center;
margin-bottom: 35px;
}
.contact-heading h1 {
color: #008080;
font-size: 36px;
margin-bottom: 12px;
}
.contact-heading p {
color: #6c7a7d;
font-size: 17px;
line-height: 1.9;
margin: 0;
}
.section-title {
color: #008080;
font-size: 25px;
margin: 35px 0 20px;
}
.contact-cards {
display: grid;
grid-template-columns: repeat(2, minmax(0, 1fr));
gap: 22px;
}
.contact-card {
background: #ffffff;
padding: 25px;
border-radius: 20px;
border: 1px solid #e0eeee;
box-shadow: 0 5px 18px rgba(0, 128, 128, 0.07);
}
.contact-card h3 {
color: #008080;
font-size: 21px;
margin: 0 0 18px;
}
.contact-item {
display: flex;
align-items: flex-start;
gap: 12px;
margin: 14px 0;
line-height: 1.8;
}
.contact-icon {
background: #e2f5f3;
color: #008080;
min-width: 42px;
height: 42px;
border-radius: 12px;
display: flex;
align-items: center;
justify-content: center;
font-size: 20px;
}
.contact-item a {
color: #36575b;
text-decoration: none;
overflow-wrap: anywhere;
}
.contact-item a:hover {
color: #008080;
text-decoration: underline;
}
.faq-list {
display: grid;
gap: 14px;
}
.faq-list details {
background: #ffffff;
border: 1px solid #e0eeee;
border-radius: 15px;
padding: 20px 22px;
box-shadow: 0 3px 12px rgba(0, 128, 128, 0.04);
}
.faq-list summary {
color: #36575b;
font-weight: 700;
font-size: 17px;
cursor: pointer;
line-height: 1.8;
}
.faq-list details[open] summary {
color: #008080;
}
.faq-list details p {
color: #6c7a7d;
line-height: 2;
margin: 14px 0 0;
}
.closing-note {
background: #e4f5f3;
border-radius: 18px;
padding: 22px;
text-align: center;
color: #36575b;
line-height: 2;
margin-top: 35px;
}
@media (max-width: 650px) {
.contact-heading h1 {
font-size: 29px;
}
.contact-heading p {
font-size: 15px;
}
.contact-cards {
grid-template-columns: 1fr;
}
.section-title {
font-size: 22px;
}
.contact-card {
padding: 20px;
}
}
</style>
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
<main class="contact-container">
<section class="contact-heading">
<h1>يسعدنا تواصلك</h1>
<p>
أهلًا بك في بصيرة، نسعد باستفساراتك واهتمامك بمشروعنا.
</p>
</section>
<h2 class="section-title">معلومات التواصل</h2>
<section class="contact-cards">
<div class="contact-card">
<h3>البريد الإلكتروني</h3>
<div class="contact-item">
<span class="contact-icon">✉</span>
<div>
<a href="mailto:445224832@tvtc.edu.sa">
445224832@tvtc.edu.sa</a></div>
</div>
<div class="contact-item">
<span class="contact-icon">✉</span>
<div>
<a href="mailto:445245950@tvtc.edu.sa">
445245950@tvtc.edu.sa</a></div>
</div></div>
<div class="contact-card">
<h3>رقم التواصل</h3>
<div class="contact-item">
<span class="contact-icon">📱</span>
<div>
<a href="tel:0546723442">0546723442</a>
</div>
</div>
</div>
</section>
<h2 class="section-title">الأسئلة الشائعة عن بصيرة</h2>
<section class="faq-list">
<details>
<summary>ما هو موقع بصيرة؟</summary>
<p>
بصيرة موقع تفاعلي يساعد المرضى على تنظيم أدويتهم
وجرعاتهم ومواعيد تناولها، من خلال التذكيرات المرئية والصوتية.
</p>
</details>
<details>
<summary>من الفئة المستهدفة لموقع بصيرة؟</summary>
<p>يستهدف الموقع كبار السن، ومرضى الأمراض المزمنة،
والأشخاص الذين يتناولون عدة أدوية يوميًا أو يواجهون
صعوبة في تذكّر مواعيدها والتمييز بينها.</p></details>
<details>
<summary>كيف يساعد بصيرة على تذكّر مواعيد الدواء؟</summary>
<p>يوفّر الموقع تنبيهات مرئية وصوتية عند موعد الجرعة
للمساعدة على تذكّر الدواء في الوقت المحدد.</p></details>
<details>
<summary>هل يستطيع أحد أفراد الأسرة متابعة أدوية المريض؟</summary>
<p>نعم، يمكن ربط حساب المريض بحساب التابع بعد موافقة الطرفين،
ليتمكن التابع من متابعة الأدوية والجرعات وفق صلاحياته.</p></details>
<details>
<summary>هل يمكن إضافة أكثر من دواء؟</summary>
<p>نعم، يمكن إضافة الأدوية وتحديد الجرعات والمواعيد لكل دواء.
يتيح الحساب المجاني عددًا محددًا من الأدوية، مع إمكانية
الاشتراك للحصول على عدد أكبر ومزايا إضافية.</p></details>
<details>
<summary>هل يحتاج موقع بصيرة إلى الإنترنت؟</summary>
<p>نعم، يحتاج الموقع إلى اتصال بالإنترنت للاستفادة من وظائفه
المختلفة، ومنها التنبيهات عبر البريد الإلكتروني.</p></details>
</section>
<div class="closing-note">
<strong>بصيرة — لأن تذكّر الدواء خطوة مهمة نحو رعاية أفضل.</strong>
<br>شكرًا لاهتمامك بمشروعنا.
</div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
