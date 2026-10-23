<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
include __DIR__ . '/includes/header.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بصيرة - لوحة التابع</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    
    <style>
    .dashboard-layout {
    display: flex;
    flex-direction: row; /* ترتيب العناصر بجانب بعضها */
    justify-content: flex-start;
    align-items: flex-start;
    min-height: calc(100vh - 140px);
    width: 100%;
    max-width: 100%; /* لجعله يمتد بالشكل المناسب على الشاشة */
    margin: 0;
}
    </style>
</head>
<body>

<div class="dashboard-layout">
    <?php include __DIR__ . '/includes/dep-sidebar.php';?>
    <div class="container"></div>
</div>

<?php include __DIR__ . '/includes/footer.php';?>

</body>
</html>