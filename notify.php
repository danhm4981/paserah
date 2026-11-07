<?php
// Notification and email helpers
require_once __DIR__ . '/db.php';

date_default_timezone_set('Asia/Riyadh');
try { $pdo->exec("SET time_zone = '+03:00'"); } catch (Throwable $e) {}

const NOTIF_TYPE_ADDED    = 'تمت إضافة دواء';
const NOTIF_TYPE_REMINDER = 'تذكير بالجرعة';

/* ---------- البريد الإلكتروني (تم إلغاء الخدمة) ---------- */
function send_mail(string $to, string $toName, string $subject, string $html): bool
{
    // تم إلغاء خدمة إرسال البريد الإلكتروني
    return false;
}

function email_template(string $title, string $intro, array $details, string $accent = '#008080'): string
{
    // تم إلغاء خدمة البريد الإلكتروني — لا يوجد قالب
    return '';
}

/* ---------- إنشاء إشعار داخل الموقع فقط ---------- */
function create_notification(PDO $pdo, int $userId, ?int $scheduleId, string $type, string $text,
                             string $audio, string $mailSubject = '', array $mailDetails = [], string $accent = '#008080'): int
{
    $ins = $pdo->prepare(
        "INSERT INTO notifications
            (user_id, schedule_id, notification_type, notification_text, notification_time,
             audio_text, email_status, in_site_status)
         VALUES (?, ?, ?, ?, NOW(), ?, 'pending', 'unread')"
    );
    $ins->execute([$userId, $scheduleId, $type, $text, $audio]);
    $nid = (int)$pdo->lastInsertId();

    // تم إلغاء إرسال الإشعار عبر البريد الإلكتروني
    return $nid;
}

/* المريض + التوابع المرتبطون النشطون */
function notification_recipients(PDO $pdo, int $patientId): array
{
    $ids = [$patientId];
    $s = $pdo->prepare("SELECT dependent_user_id FROM patient_dependents
                        WHERE patient_user_id = ? AND status = 'active'");
    $s->execute([$patientId]);
    foreach ($s->fetchAll(PDO::FETCH_COLUMN) as $d) { $ids[] = (int)$d; }
    return array_values(array_unique($ids));
}

/* ---------- عند إضافة دواء ---------- */
function notify_medication_added(PDO $pdo, int $patientId, int $scheduleId, string $medName,
                                 $amount, string $unit, string $time, string $date, string $addedBy = ''): void
{
    $dose = rtrim(rtrim((string)$amount, '0'), '.') . ' ' . $unit;
    $text = "تمت إضافة الدواء «{$medName}» بجرعة {$dose}، وموعدها {$date} الساعة " . substr($time, 0, 5)
          . ($addedBy !== '' ? " (أضافه: {$addedBy})" : '');
    $audio = "تمت إضافة دواء {$medName} بنجاح";
    $details = ['الدواء' => $medName, 'الجرعة' => $dose, 'التاريخ' => $date, 'الوقت' => substr($time, 0, 5)];

    foreach (notification_recipients($pdo, $patientId) as $uid) {
        create_notification($pdo, $uid, $scheduleId, NOTIF_TYPE_ADDED, $text, $audio,
                            "بصيرة: تمت إضافة دواء {$medName}", $details, '#008080');
    }
}

/* ---------- تذكيرات الجرعات المستحقة ---------- */
function generate_due_reminders(PDO $pdo, ?int $patientId = null): int
{
    $sql = "SELECT ds.schedule_id, ds.dose_amount, ds.dose_unit, ds.dosage_time, ds.dosage_date,
                   m.medication_name, m.user_id AS patient_id
            FROM dosage_schedules ds
            JOIN medications m ON m.medication_id = ds.medication_id
            WHERE ds.status = 'pending'
              AND TIMESTAMP(ds.dosage_date, ds.dosage_time) <= NOW()
              AND TIMESTAMP(ds.dosage_date, ds.dosage_time) >= NOW() - INTERVAL 1 DAY"
         . ($patientId ? " AND m.user_id = ?" : "");
    $st = $pdo->prepare($sql);
    $st->execute($patientId ? [$patientId] : []);

    $exists = $pdo->prepare("SELECT 1 FROM notifications
                             WHERE schedule_id = ? AND user_id = ? AND notification_type = ? LIMIT 1");
    $count = 0;
    foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $d) {
        $dose = rtrim(rtrim((string)$d['dose_amount'], '0'), '.') . ' ' . $d['dose_unit'];
        $text = "حان موعد دواء «{$d['medication_name']}» — الجرعة {$dose}.";
        $audio = "حان الآن موعد دواء {$d['medication_name']}. الجرعة {$dose}";
        $details = ['الدواء' => $d['medication_name'], 'الجرعة' => $dose, 'الموعد' => substr($d['dosage_time'], 0, 5)];

        foreach (notification_recipients($pdo, (int)$d['patient_id']) as $uid) {
            $exists->execute([$d['schedule_id'], $uid, NOTIF_TYPE_REMINDER]);
            if ($exists->fetchColumn()) { continue; }
            create_notification($pdo, $uid, (int)$d['schedule_id'], NOTIF_TYPE_REMINDER, $text, $audio,
                                "⏰ بصيرة: حان موعد {$d['medication_name']}", $details, '#d98e04');
            $count++;
        }
    }
    return $count;
}
