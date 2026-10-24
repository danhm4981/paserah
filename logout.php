<?php
// تفعيل التخزين المؤقت للمخرجات لتفادي مشاكل الـ Headers
ob_start(); 

// استدعاء ملف قاعدة البيانات إن وجد
if (file_exists(__DIR__ . '/db.php')) {
    require_once __DIR__ . '/db.php';
}
include __DIR__ . '/includes/header.php';

// بدء الجلسة للتحقق من حالة تسجيل الخروج
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تدمير بيانات الجلسة (تسجيل الخروج الفعلي)
$_SESSION = array();
session_destroy();

// تحويل تلقائي للرئيسية بعد 4 ثوانٍ ليتمكن المستخدم من رؤية صفحتك وتصميمك
header("Refresh: 4; url=index.php"); 
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>بصيرة - تم تسجيل الخروج</title>
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
   <?php include __DIR__ . '/includes/footer.php';?>


</body>
</html>
<?php
ob_end_flush(); 
?>