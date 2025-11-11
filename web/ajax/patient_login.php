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
$pw    = isset($R['password']) ? (string)$R['password'] : '';
$out = array('ok'=>0);

try {
    if ($email !== '' && $pw !== '') {
        $DB = Database::getInstance();
        $DB->connect($config["server"], $config["database"], $config["user"], $config["pass"]);
        $row = $DB->PreparedSelect('SELECT id, first_name, last_name, password, street, house_no, zip, city, birthdate FROM patients WHERE LOWER(email) = :em LIMIT 1', array('em'=>$email), false, false);
        if (is_array($row) && isset($row[0]['id'])) {
            $p = $row[0];
            $hash = isset($p['password']) ? (string)$p['password'] : '';
            if ($hash !== '' && password_verify($pw, $hash)) {
                $_SESSION['patient_id'] = (int)$p['id'];
                $out['ok'] = 1;
                $out['patient'] = array(
                    'first_name' => (string)$p['first_name'],
                    'last_name'  => (string)$p['last_name'],
                    'street'     => isset($p['street'])?(string)$p['street']:'',
                    'house_no'   => isset($p['house_no'])?(string)$p['house_no']:'',
                    'zip'        => isset($p['zip'])?(string)$p['zip']:'',
                    'city'       => isset($p['city'])?(string)$p['city']:'',
                    'birthdate'  => isset($p['birthdate'])?(string)$p['birthdate']:'',
                );
            } else {
                $out['error'] = 'invalid_credentials';
            }
        } else {
            $out['error'] = 'not_found';
        }
    } else {
        $out['error'] = 'missing_params';
    }
} catch (Exception $e) {
    $out['error'] = 'exception';
}

echo json_encode($out);
