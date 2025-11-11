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
$result = array('exists'=>0, 'has_password'=>0);

try {
    if ($email !== '') {
        $DB = Database::getInstance();
        $DB->connect($config["server"], $config["database"], $config["user"], $config["pass"]);
        $row = $DB->PreparedSelect('SELECT id, password FROM patients WHERE LOWER(email) = :em LIMIT 1', array('em'=>$email), false, false);
        if (is_array($row) && isset($row[0]['id'])) {
            $result['exists'] = 1;
            $pw = isset($row[0]['password']) ? (string)$row[0]['password'] : '';
            $result['has_password'] = ($pw !== '') ? 1 : 0;
        }
    }
} catch (Exception $e) {}

echo json_encode($result);
