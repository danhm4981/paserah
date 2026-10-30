<?php
// Notification and email helpers
require_once __DIR__ . '/db.php';

date_default_timezone_set('Asia/Riyadh');
try { $pdo->exec("SET time_zone = '+03:00'"); } catch (Throwable $e) {}

const NOTIF_TYPE_ADDED    = 'تمت إضافة دواء';
const NOTIF_TYPE_REMINDER = 'تذكير بالجرعة';

/* ---------- البريد الإلكتروني ---------- */
function send_mail(string $to, string $toName, string $subject, string $html): bool
{
    $cfg = require __DIR__ . '/mail_config.php';
    $autoload = __DIR__ . '/vendor/autoload.php';
    if (!file_exists($autoload)) {
        error_log('Basira: PHPMailer غير مثبت (composer require phpmailer/phpmailer)');
        return false;
    }
    require_once $autoload;

    $m = new \PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $m->isSMTP();
        $m->Host       = $cfg['host'];
        $m->SMTPAuth   = true;
        $m->Username   = $cfg['username'];
        $m->Password   = $cfg['password'];
        $m->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $m->Port       = $cfg['port'];
        $m->CharSet    = 'UTF-8';
        $m->Timeout    = 10;
        $m->setFrom($cfg['from'], $cfg['from_name']);
        $m->addAddress($to, $toName);
        $m->isHTML(true);
        $m->Subject = $subject;
        $m->Body    = $html;
        $m->AltBody = trim(strip_tags(str_replace(['<br>', '</p>'], "\n", $html)));
        return $m->send();
    } catch (Throwable $e) {
        error_log('Basira mail error: ' . $e->getMessage());
        return false;
    }
}

function email_template(string $title, string $intro, array $details, string $accent = '#008080'): string
{
    $cfg  = require __DIR__ . '/mail_config.php';
    $rows = '';
    foreach ($details as $label => $value) {
        $rows .= '<tr><td style="padding:8px 0;color:#6c7a7d;width:35%">' . htmlspecialchars($label) . '</td>'
               . '<td style="padding:8px 0;font-weight:bold">' . htmlspecialchars((string)$value) . '</td></tr>';
    }
    $url = htmlspecialchars($cfg['site_url'] . 'notifications.php');
    return '<div dir="rtl" style="font-family:Tahoma,Arial,sans-serif;background:#f8fcfc;padding:24px">'
         . '<div style="max-width:520px;margin:auto;background:#fff;border-radius:16px;overflow:hidden;border:1px solid #e3eeee">'
         . '<div style="background:' . $accent . ';color:#fff;padding:20px 24px;font-size:20px;font-weight:bold">' . htmlspecialchars($title) . '</div>'
         . '<div style="padding:24px;color:#2b3e42">'
         . '<p style="margin:0 0 16px;line-height:1.8">' . htmlspecialchars($intro) . '</p>'
         . '<table style="width:100%;border-collapse:collapse;border-top:1px solid #eee">' . $rows . '</table>'
         . '<p style="margin:24px 0 0"><a href="' . $url . '" style="background:' . $accent . ';color:#fff;text-decoration:none;padding:11px 24px;border-radius:24px;display:inline-block">فتح التنبيهات</a></p>'
         . '</div></div></div>';
}

/* ---------- إنشاء إشعار + إرسال بريد ---------- */
function create_notification(PDO $pdo, int $userId, ?int $scheduleId, string $type, string $text,
                             string $audio, string $mailSubject, array $mailDetails, string $accent): int
{
    $ins = $pdo->prepare(
        "INSERT INTO notifications
            (user_id, schedule_id, notification_type, notification_text, notification_time,
             audio_text, email_status, in_site_status)
         VALUES (?, ?, ?, ?, NOW(), ?, 'pending', 'unread')"
    );
    $ins->execute([$userId, $scheduleId, $type, $text, $audio]);
    $nid = (int)$pdo->lastInsertId();

    $u = $pdo->prepare("SELECT username, email FROM users WHERE user_id = ?");
    $u->execute([$userId]);
    $user = $u->fetch(PDO::FETCH_ASSOC);

    if ($user && filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
        $ok = send_mail($user['email'], (string)$user['username'], $mailSubject,
                        email_template($type, $text, $mailDetails, $accent));
        $upd = $pdo->prepare(
            "UPDATE notifications SET email_status = ?, email_sent_at = " . ($ok ? 'NOW()' : 'NULL') . "
             WHERE notification_id = ?"
        );
        $upd->execute([$ok ? 'sent' : 'failed', $nid]);
    }
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
