<?php
// cron_reminders.php — يرسل التذكيرات (إشعار + بريد) حتى لو كان الموقع مغلقاً.
// Windows (XAMPP): Task Scheduler كل دقيقة:
//   C:\xampp\php\php.exe C:\xampp\htdocs\danah\GProject\paserah\cron_reminders.php
// Linux: * * * * * php /path/to/paserah/cron_reminders.php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }
require_once __DIR__ . '/includes/notify.php';
$n = generate_due_reminders($pdo);
echo date('Y-m-d H:i:s') . " — reminders created: $n\n";
