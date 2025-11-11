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
$code  = isset($R['code']) ? trim($R['code']) : '';
$pass  = isset($R['password']) ? (string)$R['password'] : '';
$out = array('ok'=>0);

if ($email === '' || $code === '' || $pass === '') { echo json_encode(array('ok'=>0,'error'=>'missing_params')); exit; }

try {
    if (!isset($_SESSION)) { session_start(); }
    if (!isset($_SESSION['patient_reset'][$email])) { echo json_encode(array('ok'=>0,'error'=>'no_reset')); exit; }
    $entry = $_SESSION['patient_reset'][$email];
    if (!is_array($entry) || !isset($entry['code'])) { echo json_encode(array('ok'=>0,'error'=>'no_reset')); exit; }
    if ($entry['code'] !== $code) { echo json_encode(array('ok'=>0,'error'=>'bad_code')); exit; }
    if (isset($entry['ts']) && (time() - (int)$entry['ts']) > 900) { echo json_encode(array('ok'=>0,'error'=>'expired')); exit; }

    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $DB = Database::getInstance();
    $DB->connect($config["server"], $config["database"], $config["user"], $config["pass"]);
    $DB->PreparedStatement('UPDATE patients SET password = :ph WHERE LOWER(email)=:em', array('ph'=>$hash, 'em'=>$email), false, false);
    unset($_SESSION['patient_reset'][$email]);
    $out['ok'] = 1;
} catch (Exception $e) {
    $out['ok'] = 0; $out['error'] = 'exception';
}

echo json_encode($out);
