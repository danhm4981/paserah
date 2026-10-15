<?php
// بيانات الاتصال بقاعدة بيانات بصيرة
$host = 'localhost';
$dbname = 'baseerah_db';
$username = 'root';
$password = '';
try {
// إنشاء الاتصال بقاعدة البيانات
$pdo = new PDO(
"mysql:host=$host;dbname=$dbname;charset=utf8mb4",
$username,
$password,
[
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
PDO::ATTR_EMULATE_PREPARES => false
]
);
} catch (PDOException $e) {
// تسجيل الخطأ دون عرضه للزائر
error_log($e->getMessage());
http_response_code(500);
exit('تعذر الاتصال بقاعدة البيانات. تأكدي من تشغيل MySQL وصحة الإعدادات.');
}
?>