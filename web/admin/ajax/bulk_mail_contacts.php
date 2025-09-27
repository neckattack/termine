<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
// Minimalinvasiver AJAX-Endpunkt zum Versenden von E-Mails an Ansprechpartner (Kunde)
// Tag: stornovorlauf_admin_preview – bulk_mail_contacts

error_reporting(0);
ini_set('display_errors', '0');
$__ob_started = false;
if (function_exists('ob_get_level')) { ob_start(); $__ob_started = true; }
$AJAX  = true;
$PAGE  = basename(__FILE__);

require __DIR__ . '/_root_.php';
require ROOT . '/inc/_include.php';
require ROOT . '/controller/index-controller.php'; // enthält sendMail()

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'POST') {
    http_response_code(405);
    if ($__ob_started) { @ob_clean(); }
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

// Minimaler Admin-/Session-Check (wie in anderen AJAX-Files)
if (!isset($_SESSION["ISADMIN"]) || !$_SESSION["ISADMIN"] || !isset($_SESSION["LOGGED_IN"]) || !$_SESSION["LOGGED_IN"]) {
    http_response_code(403);
    if ($__ob_started) { @ob_clean(); }
    echo json_encode(['ok' => false, 'error' => 'Forbidden']);
    exit;
}

// Expect JSON or form-encoded
$raw = file_get_contents('php://input');
$payload = [];
if ($raw && isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $payload = json_decode($raw, true) ?: [];
} else {
    $payload = $_POST;
}

$clientIds = $payload['client_ids'] ?? [];
$subject   = trim((string)($payload['subject'] ?? ''));
$message   = (string)($payload['message'] ?? '');

if (!is_array($clientIds) || count($clientIds) === 0) {
    http_response_code(400);
    if ($__ob_started) { @ob_clean(); }
    echo json_encode(['ok' => false, 'error' => 'No client ids provided']);
    exit;
}

if ($subject === '') {
    $subject = 'Kommender Termin bitte bewerben';
}

// Clean IDs
$clientIds = array_values(array_filter(array_map(function($v){ return (int)$v; }, $clientIds), function($v){ return $v > 0; }));
if (count($clientIds) === 0) {
    http_response_code(400);
    if ($__ob_started) { @ob_clean(); }
    echo json_encode(['ok' => false, 'error' => 'No valid client ids']);
    exit;
}

try {
    global $DB;
    // Ansprechpartner (Kunde) via clients.contact_client_id -> admin.id
    // Hinweis: Das DB-Layer nutzt häufig String-Interpolation mit vorheriger Int-Säuberung.
    $idStr = implode("', '", $clientIds);
    $sql = "SELECT c.id AS client_id, c.name AS client_name, a.email, a.first_name, a.last_name
            FROM clients c
            LEFT JOIN admin a ON a.id = c.contact_client_id
            WHERE c.id IN ('".$idStr."')";
    $rows = $DB->PreparedSelect($sql, array(), false, false);

    $sent = 0; $skipped = [];
    foreach ($rows as $row) {
        $to = trim((string)($row['email'] ?? ''));
        if ($to === '') { $skipped[] = (int)$row['client_id']; continue; }
        $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
        if ($name === '') { $name = $row['client_name'] ?? 'Ansprechpartner'; }
        // System-Standard-Absender wie im System üblich
        $from = 'neckAttack Ltd. <termine@neckattack.net>';
        $ok = sendMail($to, $name, $from, $subject, $message, null, false);
        if ($ok) { $sent++; } else { $skipped[] = (int)$row['client_id']; }
    }

    if ($__ob_started) { @ob_clean(); }
    echo json_encode(['ok' => true, 'sent' => $sent, 'skipped' => $skipped]);
} catch (Exception $e) {
    http_response_code(500);
    if ($__ob_started) { @ob_clean(); }
    echo json_encode(['ok' => false, 'error' => 'Server error', 'detail' => $e->getMessage()]);
}
