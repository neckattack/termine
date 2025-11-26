<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
// AJAX: Individuelle E-Mails an ausgewählte Reservierungen versenden (ohne PDF)

error_reporting(0);
ini_set('display_errors', '0');
$__ob_started = false;
if (function_exists('ob_get_level')) { ob_start(); $__ob_started = true; }
$AJAX = true;
$PAGE = basename(__FILE__);

require __DIR__ . '/_root_.php';
require ROOT . '/inc/_include.php';
require ROOT . '/inc/admincheck.php';
require ROOT . '/controller/index-controller.php';
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
    if (is_string($v) && strpos($v, ',') !== false) { 
        foreach (explode(',', $v) as $p) { $norm[] = (int)$p; } 
    } else { 
        $norm[] = (int)$v; 
    }
}
$rids = array_values(array_unique(array_filter($norm, function($v){ return $v>0; })));

$subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
$body = isset($_POST['body']) ? trim($_POST['body']) : '';

if (count($rids) === 0) {
    if ($__ob_started) { @ob_clean(); }
    echo json_encode(['ok'=>false,'error'=>'Keine Reservierungen ausgewählt']);
    exit;
}

if ($subject === '' || $body === '') {
    if ($__ob_started) { @ob_clean(); }
    echo json_encode(['ok'=>false,'error'=>'Betreff und Nachricht müssen ausgefüllt sein']);
    exit;
}

// Hilfsfunktion: E-Mail versenden (ohne PDF)
function sendSimpleMail($to, $name, $from, $subject, $htmlMessage, $cc='', $replyTo=''){
    $headers   = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/html; charset=utf-8';
    $headers[] = 'Content-Transfer-Encoding: 8bit';
    $headers[] = 'X-Priority: 3';
    $headers[] = 'X-MSMail-Priority: Normal';
    $headers[] = 'X-Mailer: PHP/'.phpversion();
    $headers[] = 'From: '.$from;
    if ($cc !== '') { $headers[] = 'Bcc: '.$cc; }
    if ($replyTo !== '') { 
        $headers[] = 'Reply-To: '.$replyTo; 
        $headers[] = 'Return-Path: '.$replyTo; 
    }

    // UTF-8 Subject
    $subj = '=?UTF-8?B?'.base64_encode($subject).'?=';
    
    if (defined('DEVELOPMENT_ENVIRONMENT') && DEVELOPMENT_ENVIRONMENT === true) {
        $to = 'termine@neckattack.net';
    }
    
    // Envelope Sender (-f)
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
        $ok = @mail($to, $subj, $htmlMessage, $hdr, $env);
        if ($ok) return true;
        // Fallback ohne -f
        $ok2 = @mail($to, $subj, $htmlMessage, $hdr);
        return $ok2 ? true : false;
    }
    return @mail($to, $subj, $htmlMessage, $hdr);
}

// Patientendaten+E-Mails zu den Reservierungen laden
try {
    $placeholders = [];
    $params = [];
    foreach ($rids as $i=>$rid) { 
        $placeholders[] = ':r'.$i; 
        $params['r'.$i] = $rid; 
    }
    
    $sql = 'SELECT r.id AS res_id, r.name AS patient_name, r.email AS patient_email, '
         . 't.id AS time_id, d.date AS date_ymd, c.id AS client_id, c.name AS client_name, '
         . 'd.masseur_id AS masseur_id, a.email AS masseur_email '
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
    $sent = 0; 
    $fail = []; 
    $results = [];
    $from = 'neckAttack Ltd. <termine@neckattack.net>';
    $replyTo = 'termine@neckattack.net';
    
    foreach ((array)$rows as $row) {
        $rid = (int)$row['res_id'];
        $to = trim((string)($row['patient_email'] ?? ''));
        $pname = (string)($row['patient_name'] ?? 'Patient');
        
        if ($to === '') { 
            $fail[] = $rid; 
            $results[] = ['rid'=>$rid,'ok'=>false,'reason'=>'keine Patienten-E-Mail']; 
            @error_log(date('c')." send_custom_emails rid=$rid no-to\n", 3, ROOT.'/web/ajax/php_mail.log'); 
            continue; 
        }

        // Name aufsplitten für persönliche Anrede
        $nameParts = preg_split('/\s+/', trim($pname));
        $firstName = is_array($nameParts) && count($nameParts) > 0 ? $nameParts[0] : $pname;
        
        // Body mit Zeilenumbrüchen in HTML umwandeln
        $bodyHtml = nl2br(htmlspecialchars($body));
        
        // E-Mail Inhalt mit persönlicher Anrede
        $coreHtml = '<p>Hallo '.htmlspecialchars($firstName).',</p>'
                  . '<p>'.$bodyHtml.'</p>'
                  . '<p>Vielen Dank und bis zum nächsten Mal,<br/>Ihr neckattack Team</p>';
        
        $html = render_email_template($subject, $coreHtml);

        $bcc = isset($row['masseur_email']) ? (string)$row['masseur_email'] : '';
        $ok = sendSimpleMail($to, $pname, $from, $subject, $html, $bcc, $replyTo);
        
        if ($ok) { 
            $sent++; 
            $results[] = ['rid'=>$rid,'ok'=>true,'reason'=>'gesendet'];
            @error_log(date('c')." send_custom_emails rid=$rid to=$to subj='".$subject."' ok=1\n", 3, ROOT.'/web/ajax/php_mail.log');
        } else { 
            // Fallback: vorhandene Systemfunktion sendMail()
            try {
                $cc = $bcc;
                $ok2 = sendMail($to, $pname, $from, $subject, $html, $replyTo, true, null, null, $cc);
                if ($ok2) {
                    $sent++;
                    $results[] = ['rid'=>$rid,'ok'=>true,'reason'=>'Fallback sendMail() verwendet'];
                    @error_log(date('c')." send_custom_emails rid=$rid to=$to subj='".$subject."' ok=1 fallback=1\n", 3, ROOT.'/web/ajax/php_mail.log');
                } else {
                    $fail[] = $rid; 
                    $results[] = ['rid'=>$rid,'ok'=>false,'reason'=>'mail() und Fallback fehlgeschlagen'];
                    @error_log(date('c')." send_custom_emails rid=$rid to=$to subj='".$subject."' ok=0\n", 3, ROOT.'/web/ajax/php_mail.log');
                }
            } catch (Exception $e) {
                $fail[] = $rid; 
                $results[] = ['rid'=>$rid,'ok'=>false,'reason'=>'Exception beim Fallback'];
                @error_log(date('c')." send_custom_emails rid=$rid exception='".$e->getMessage()."'\n", 3, ROOT.'/web/ajax/php_mail.log');
            }
        }
    }

    if ($__ob_started) { @ob_clean(); }
    echo json_encode(['ok'=>true,'sent'=>$sent,'failed'=>count($fail),'results'=>$results]);
    
} catch (Throwable $e) {
    if ($__ob_started) { @ob_clean(); }
    @error_log(date('c')." send_custom_emails fatal: ".$e->getMessage()."\n", 3, ROOT.'/web/ajax/php_mail.log');
    echo json_encode(['ok'=>false,'error'=>'Unhandled exception','message'=>$e->getMessage()]);
}
