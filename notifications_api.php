<?php
// api/notifications_api.php  — يعمل بصيغة JSON
if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$uid = (int)($_SESSION['user_id'] ?? 0);
if ($uid < 1) { http_response_code(401); echo json_encode(['ok' => false]); exit; }

require_once __DIR__ . '/includes/notify.php';

if (empty($_SESSION['notification_csrf'])) {
    $_SESSION['notification_csrf'] = bin2hex(random_bytes(32));
}

/* ---------- تنفيذ إجراء ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token  = $_POST['csrf_token'] ?? '';
    $action = $_POST['action'] ?? '';
    $nid    = filter_input(INPUT_POST, 'notification_id', FILTER_VALIDATE_INT);

    if (!is_string($token) || !hash_equals($_SESSION['notification_csrf'], $token)) {
        http_response_code(403); echo json_encode(['ok' => false, 'error' => 'csrf']); exit;
    }
    $chk = $pdo->prepare("SELECT notification_id, schedule_id FROM notifications
                          WHERE notification_id = ? AND user_id = ? LIMIT 1");
    $chk->execute([$nid, $uid]);
    $n = $chk->fetch(PDO::FETCH_ASSOC);
    if (!$n) { http_response_code(404); echo json_encode(['ok' => false, 'error' => 'not_found']); exit; }

    if ($action === 'mark_read') {
        $pdo->prepare("UPDATE notifications SET in_site_status='read', read_at=NOW()
                       WHERE notification_id=? AND user_id=?")->execute([$nid, $uid]);
        echo json_encode(['ok' => true]); exit;
    }
    if ($action === 'take_dose' && $n['schedule_id']) {
        $u = $pdo->prepare("UPDATE dosage_schedules SET status='taken'
                            WHERE schedule_id=? AND status='pending'");
        $u->execute([$n['schedule_id']]);
        // الجرعة أُخذت: نغلق كل إشعاراتها
        $pdo->prepare("UPDATE notifications SET in_site_status='read', read_at=NOW()
                       WHERE schedule_id=? AND in_site_status='unread'")->execute([$n['schedule_id']]);
        echo json_encode(['ok' => true]); exit;
    }
    http_response_code(400); echo json_encode(['ok' => false, 'error' => 'bad_action']); exit;
}

/* ---------- Polling: توليد التذكيرات المستحقة ثم إرجاع غير المقروء ---------- */
$rel = $pdo->prepare("SELECT patient_user_id FROM patient_dependents WHERE dependent_user_id = ? LIMIT 1");
$rel->execute([$uid]);
$patientId = (int)($rel->fetchColumn() ?: $uid);
generate_due_reminders($pdo, $patientId);

$st = $pdo->prepare(
    "SELECT n.notification_id, n.notification_type, n.notification_text, n.notification_time,
            n.audio_text, n.schedule_id,
            ds.status AS dosage_status, ds.dose_amount, ds.dose_unit,
            m.medication_name, m.medication_shape, m.medication_color
     FROM notifications n
     LEFT JOIN dosage_schedules ds ON ds.schedule_id = n.schedule_id
     LEFT JOIN medications m ON m.medication_id = ds.medication_id
     WHERE n.user_id = ? AND n.in_site_status = 'unread'
     ORDER BY n.notification_id DESC LIMIT 10"
);
$st->execute([$uid]);
$items = $st->fetchAll(PDO::FETCH_ASSOC);

$cnt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND in_site_status = 'unread'");
$cnt->execute([$uid]);

echo json_encode([
    'ok'     => true,
    'csrf'   => $_SESSION['notification_csrf'],
    'unread' => (int)$cnt->fetchColumn(),
    'items'  => $items,
], JSON_UNESCAPED_UNICODE);
