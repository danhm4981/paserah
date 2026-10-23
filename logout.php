<?php
// تفعيل التخزين المؤقت للمخرجات لحل مشكلة db.php
ob_start(); 

require_once __DIR__ . '/db.php';
include __DIR__ . '/includes/header.php';

// 1. بدء الجلسة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. تدمير الجلسة إن وجدت
session_destroy();

// 3. التوجيه الفوري المباشر عن طريق السيرفر والمتصفح معاً
header("Location: index.php");
?>
<script>
    window.location.href = 'index.php';
</script>
<?php
exit;
?>
<!DOCTYPE html>
<html>
<head>
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