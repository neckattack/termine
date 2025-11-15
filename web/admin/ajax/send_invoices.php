<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
// AJAX: Ausgewählte Reservierungen per E-Mail mit PDF-Rechnung versenden
// Minimal-invasiv: nutzt bestehendes sendMail() nicht, um Signatur nicht zu brechen; lokale Attachment-Variante unten

error_reporting(0);
ini_set('display_errors', '0');
$__ob_started = false;
if (function_exists('ob_get_level')) { ob_start(); $__ob_started = true; }
$AJAX = true;
$PAGE = basename(__FILE__);

require __DIR__ . '/_root_.php';
require ROOT . '/inc/_include.php';
require ROOT . '/inc/admincheck.php';
require ROOT . '/controller/index-controller.php'; // für DB-Helper und ggf. Kontaktinfos
require ROOT . '/inc/email_template.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    if ($__ob_started) { @ob_clean(); }
    echo json_encode(['ok'=>false,'error'=>'Method not allowed']);
    exit;
}

// Eingaben
$rids = isset($_POST['reservation_ids']) ? $_POST['reservation_ids'] : [];
if (!is_array($rids)) { $rids = [$rids]; }
// Support comma-joined payloads
$norm = [];
foreach ($rids as $v) {
    if (is_string($v) && strpos($v, ',') !== false) { foreach (explode(',', $v) as $p) { $norm[] = (int)$p; } }
    else { $norm[] = (int)$v; }
}
$rids = array_values(array_unique(array_filter($norm, function($v){ return $v>0; })));
if (count($rids)===0) {
    if ($__ob_started) { @ob_clean(); }
    echo json_encode(['ok'=>false,'error'=>'No reservations selected']);
    exit;
}

// Hilfsfunktion: PDF für eine Reservierung abrufen (via interner HTTP-Request an invoice.php)
function fetchInvoicePdf($resId){
    $url = ABSURL . 'web/admin/invoice.php?res_id=' . (int)$resId;
    // Session-Cookie übernehmen, damit admincheck greift
    $sessName = session_name();
    $sessId   = isset($_COOKIE[$sessName]) ? $_COOKIE[$sessName] : '';

    // cURL bevorzugen (häufig aktiviert, robuster als allow_url_fopen)
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Cookie: '.$sessName.'='.$sessId,
            'Accept: application/pdf'
        ]);
        $data = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($data === false || $code >= 400) { return null; }
        // Prüfe PDF-Signatur
        if (strpos($data, '%PDF') === 0 || strpos($data, "\n%PDF") !== false) { return $data; }
        // Falls sehr klein oder HTML, als Fehler behandeln
        if (strlen($data) < 1000) { return null; }
        return (strpos($data, '%PDF') !== false) ? $data : null;
    }

    // Fallback: file_get_contents
    $opts = [
        'http' => [
            'method' => 'GET',
            'header' => "Cookie: {$sessName}={$sessId}\r\n".
                       "Accept: application/pdf\r\n",
            'ignore_errors' => true,
            'timeout' => 20,
        ]
    ];
    $context = stream_context_create($opts);
    $data = @file_get_contents($url, false, $context);
    if ($data===false) { return null; }
    if (strpos($data, '%PDF') === 0 || strpos($data, "\n%PDF") !== false) { return $data; }
    if (strlen($data) < 1000) { return null; }
    return (strpos($data, '%PDF') !== false) ? $data : null;
}

// Hilfsfunktion: generische E-Mail mit PDF als Attachment versenden
function sendMailWithPdf($to, $name, $from, $subject, $htmlMessage, $pdfBytes, $pdfFilename, $cc='', $replyTo=''){
    // Multipart mail bauen
    $multipartSep = '-----'.md5(uniqid('mx', true)).'-----';

    $headers   = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Transfer-Encoding: 8bit';
    $headers[] = 'X-Priority: 3';
    $headers[] = 'X-MSMail-Priority: Normal';
    $headers[] = 'X-Mailer: PHP/'.phpversion();
    $headers[] = 'From: '.$from;
    if ($cc !== '') { $headers[] = 'Bcc: '.$cc; }
    if ($replyTo !== '') { $headers[] = 'Reply-To: '.$replyTo; $headers[] = 'Return-Path: '.$replyTo; }
    $hasAttachment = (is_string($pdfBytes) && strlen($pdfBytes) > 0);
    $headers[] = $hasAttachment
        ? 'Content-Type: multipart/mixed; boundary="'.$multipartSep.'"'
        : 'Content-Type: text/html; charset=utf-8';

    if ($hasAttachment) {
        $body  = "--{$multipartSep}\r\n";
        $body .= "Content-Type: text/html; charset=utf-8\r\n";
        $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $body .= $htmlMessage."\r\n";
        $body .= "--{$multipartSep}\r\n";
        $body .= "Content-Type: application/pdf; name=\"{$pdfFilename}\"\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n";
        $body .= "Content-Disposition: attachment; filename=\"{$pdfFilename}\"\r\n\r\n";
        $body .= chunk_split(base64_encode($pdfBytes));
        $body .= "--{$multipartSep}--";
    } else {
        $body = $htmlMessage;
    }

    // UTF-8 Subject
    $subj = '=?UTF-8?B?'.base64_encode($subject).'?=';
    if (defined('DEVELOPMENT_ENVIRONMENT') && DEVELOPMENT_ENVIRONMENT === true) {
        $to = 'termine@neckattack.net';
    }
    // Envelope Sender (-f) für bessere Zustellbarkeit/Serveranforderungen
    $env = '';
    $sender = '';
    if ($replyTo !== '') { $sender = $replyTo; }
    if ($sender === '') {
        if (preg_match('/<([^>]+)>/', $from, $m)) { $sender = $m[1]; }
        else { $sender = $from; }
    }
    if ($sender !== '') { $env = '-f '.escapeshellarg($sender); }
    $hdr = implode("\r\n", $headers);
    if ($env !== '') {
        $ok = @mail($to, $subj, $body, $hdr, $env);
        if ($ok) return true;
        // Fallback ohne -f
        $ok2 = @mail($to, $subj, $body, $hdr);
        return $ok2 ? true : false;
    }
    return @mail($to, $subj, $body, $hdr);
}

// Patientendaten+E-Mails zu den Reservierungen laden
try {
    $placeholders = [];$params=[]; foreach ($rids as $i=>$rid){ $placeholders[]=':r'.$i; $params['r'.$i]=$rid; }
    $sql = 'SELECT r.id AS res_id, r.name AS patient_name, r.email AS patient_email, '
         . 't.id AS time_id, d.date AS date_ymd, c.id AS client_id, c.name AS client_name, d.masseur_id AS masseur_id, a.email AS masseur_email '
         . 'FROM reservations r '
         . 'JOIN times t ON t.id = r.time_id '
         . 'JOIN dates d ON d.id = t.date_id '
         . 'JOIN clients c ON c.id = d.client_id '
         . 'LEFT JOIN admin a ON a.id = d.masseur_id '
         . 'WHERE r.id IN ('.implode(',', $placeholders).')';
    $rows = $DB->PreparedSelect($sql, $params, false, false);
} catch (Exception $e) {
    if ($__ob_started) { @ob_clean(); }
    echo json_encode(['ok'=>false,'error'=>'DB error']);
    exit;
}

try {
$sent=0; $fail=[]; $results=[];
$from = 'neckAttack Ltd. <termine@neckattack.net>';
$replyTo = 'termine@neckattack.net';
foreach ((array)$rows as $row) {
    $rid = (int)$row['res_id'];
    $to  = trim((string)($row['patient_email'] ?? ''));
    $pname = (string)($row['patient_name'] ?? 'Patient');
    if ($to==='') { $fail[]=$rid; $results[]=['rid'=>$rid,'ok'=>false,'reason'=>'keine Patienten-E-Mail']; @error_log(date('c')." send_invoices rid=$rid no-to\n", 3, ROOT.'/web/ajax/php_mail.log'); continue; }

    // PDF via HTTP abrufen; wenn das nicht möglich ist, Link-Fallback
    $pdf = fetchInvoicePdf($rid);
    $pdfMissing = !$pdf;

    // Rechnungsnummer nach Generierung laden
    $invoiceNo = '';
    try {
        $qr = $DB->PreparedSelect('SELECT invoice_no FROM reservation_invoices WHERE reservation_id = :rid LIMIT 1', ['rid'=>$rid], false, false);
        if (is_array($qr) && isset($qr[0]['invoice_no'])) { $invoiceNo = (string)$qr[0]['invoice_no']; }
    } catch (Exception $e) {}

    // Betreff und kurzer Text
    $subject = 'Ihre Rechnung' . ($invoiceNo!=='' ? (' - '.$invoiceNo) : '');
    // Name aufsplitten
    $nameParts = preg_split('/\s+/', trim($pname));
    $pn = $pname;
    if (is_array($nameParts) && count($nameParts)>1) { $pn = $nameParts[0].' '.end($nameParts); }
    // Magic-Link Token erzeugen (30 Tage gültig)
    $invoiceToken = '';
    try {
        Database::PreparedStatement(
            'CREATE TABLE IF NOT EXISTS `invoice_tokens` (
                `token` VARCHAR(64) NOT NULL PRIMARY KEY,
                `reservation_id` INT NOT NULL,
                `expires_at` DATETIME NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX(`reservation_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
            []
        );
        $invoiceToken = bin2hex(random_bytes(16));
        $expires = date('Y-m-d H:i:s', time() + 30*24*60*60);
        Database::PreparedStatement(
            'INSERT INTO invoice_tokens (`token`, `reservation_id`, `expires_at`) VALUES (:t, :rid, :exp)',
            ['t'=>$invoiceToken, 'rid'=>$rid, 'exp'=>$expires]
        );
    } catch (Exception $e) {
        $invoiceToken = '';
    }
    $invoiceLink = $invoiceToken !== ''
        ? (ABSURL . 'web/admin/invoice.php?t=' . urlencode($invoiceToken))
        : (ABSURL . 'web/admin/invoice.php?res_id='.(int)$rid);
    $coreHtml = '<p>Hallo '.htmlspecialchars($pn).',</p>'
              . '<p>nochmal vielen Dank für Ihr Vertrauen in neckattack. '
              . ($pdf ? 'Im Anhang finden Sie Ihre Rechnung für Ihren letzten Termin.' : 'Ihre Rechnung können Sie jederzeit über folgenden Link abrufen: <a href="'.htmlspecialchars($invoiceLink).'" target="_blank">Rechnung ansehen</a>.')
              . '</p>'
              . '<p>Bei Fragen dazu können Sie diese am besten mit Ihrem Therapeuten beim nächsten Mal klären oder schreiben Sie uns eine kurze E-Mail zurück.</p>'
              . '<p>Vielen Dank und bis zum nächsten Mal,<br/>Ihr neckattack Team</p>';
    $html = render_email_template($subject, $coreHtml);

    $bcc = isset($row['masseur_email']) ? (string)$row['masseur_email'] : '';
    $ok = sendMailWithPdf($to, $pname, $from, $subject, $html, $pdfMissing ? '' : $pdf, 'Rechnung_'.($invoiceNo!==''?$invoiceNo:$rid).'.pdf', $bcc, $replyTo);
    if ($ok) { 
        $sent++; 
        $results[]=['rid'=>$rid,'ok'=>true, 'reason'=>$pdfMissing ? 'gesendet ohne Anhang (Link)' : 'gesendet mit PDF'];
        @error_log(date('c')." send_invoices rid=$rid to=$to subj='".$subject."' ok=1 attach=".($pdfMissing?0:1)."\n", 3, ROOT.'/web/ajax/php_mail.log');
    } else { 
        // Fallback: vorhandene Systemfunktion sendMail() ohne Anhang, HTML=true, CC statt BCC
        try {
            $cc = $bcc; // sendMail() unterstützt nur Cc, nicht Bcc
            $ok2 = sendMail($to, $pname, $from, $subject, $html, $replyTo, true, null, null, $cc);
            if ($ok2) {
                $sent++;
                $results[]=['rid'=>$rid,'ok'=>true,'reason'=>'Fallback sendMail() ohne Anhang'];
                @error_log(date('c')." send_invoices rid=$rid to=$to subj='".$subject."' ok=1 fallback=1 attach=0\n", 3, ROOT.'/web/ajax/php_mail.log');
            } else {
                $fail[]=$rid; 
                $results[]=['rid'=>$rid,'ok'=>false,'reason'=>'mail() und Fallback fehlgeschlagen'];
                @error_log(date('c')." send_invoices rid=$rid to=$to subj='".$subject."' ok=0\n", 3, ROOT.'/web/ajax/php_mail.log');
            }
        } catch (Exception $e) {
            $fail[]=$rid; 
            $results[]=['rid'=>$rid,'ok'=>false,'reason'=>'Exception beim Fallback'];
            @error_log(date('c')." send_invoices rid=$rid exception='".$e->getMessage()."'\n", 3, ROOT.'/web/ajax/php_mail.log');
        }
    }
}

if ($__ob_started) { @ob_clean(); }
echo json_encode(['ok'=>true,'sent'=>$sent,'failed'=>$fail,'results'=>$results]);
} catch (Throwable $e) {
    if ($__ob_started) { @ob_clean(); }
    @error_log(date('c')." send_invoices fatal: ".$e->getMessage()."\n", 3, ROOT.'/web/ajax/php_mail.log');
    echo json_encode(['ok'=>false,'error'=>'Unhandled exception','message'=>$e->getMessage()]);
}
