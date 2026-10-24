<?php
// تفعيل التخزين المؤقت للمخرجات لتفادي مشاكل الـ Headers
ob_start(); 

// استدعاء ملف قاعدة البيانات إن وجد
if (file_exists(__DIR__ . '/db.php')) {
    require_once __DIR__ . '/db.php';
}
include __DIR__ . '/includes/header.php';

// بدء الجلسة لتدميرها
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تدمير بيانات الجلسة (تسجيل الخروج الفعلي)
$_SESSION = array();
session_destroy();

// تحويل تلقائي للرئيسية بعد 4 ثوانٍ ليتمكن المستخدم من رؤية صفحتك وتصميمك
header("Refresh: 4; url=index.php"); 
?><!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>بصيرة - تم تسجيل الخروج</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        .dashboard-layout {
    display: flex;
    min-height: calc(100vh - 140px);
    max-width: 1400px;
    margin: 0 auto;
    width: 100%;
}
    </style>
</head>
<body>
     <div class="dashboard-layout">
            <?php include __DIR__ . '/includes/sidebar.php';?>
</div>
    <div class="logout-container">
        <div class="logout-card">
            <h2>تم تسجيل الخروج بنجاح!</h2>
            <p>شكراً لاستخدامك منصة بصيرة. لقد تم إنهاء جلستك بأمان، وسيتم تحويلك إلى الصفحة الرئيسية الآن...</p>
            <div class="loader"></div>
        </div>
    </div>
   <?php include __DIR__ . '/includes/footer.php';?>
</body>
</html>