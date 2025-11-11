<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
error_reporting(0);
$AJAX = true;
$PAGE = basename(__FILE__);
require __DIR__."/_root_.php";
require ROOT."/inc/_include.php";

header('Content-Type: application/json; charset=utf-8');

$R = $_REQUEST;
$email = isset($R['email']) ? strtolower(trim($R['email'])) : '';
$out = array('ok'=>0);

if ($email === '') { echo json_encode(array('ok'=>0,'error'=>'missing_email')); exit; }

try {
    $DB = Database::getInstance();
    $DB->connect($config["server"], $config["database"], $config["user"], $config["pass"]);
    $row = $DB->PreparedSelect('SELECT id FROM patients WHERE LOWER(email)=:em LIMIT 1', array('em'=>$email), false, false);
    if (!is_array($row) || !isset($row[0]['id'])) { echo json_encode(array('ok'=>0,'error'=>'not_found')); exit; }

    if (!isset($_SESSION)) { session_start(); }
    if (!isset($_SESSION['patient_reset'])) { $_SESSION['patient_reset'] = array(); }
    $code = (string)random_int(100000, 999999);
    $_SESSION['patient_reset'][$email] = array('code'=>$code, 'ts'=>time());

    // Try sending email; fallback to server log if mail is not configured
    $subject = 'Ihr Passwort-Reset-Code';
    $body = "Ihr Reset-Code lautet: $code\nEr ist 15 Minuten gültig.";
    @mail($email, $subject, $body);
    error_log("[patient_send_reset] email=$email code=$code");

    $out['ok'] = 1;
} catch (Exception $e) {
    $out['ok'] = 0; $out['error'] = 'exception';
}

echo json_encode($out);
