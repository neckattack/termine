<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * editpatient.php
 * Edit/add a patient (profile based on email)
 */

error_reporting(-1);
$PAGE = basename(__FILE__);
require "_root_.php";              // Defines the ROOT constant
require ROOT."/inc/_include.php";
require ROOT."/inc/admincheck.php"; // Check if admin logged in

$R = $_REQUEST;
$P = $_POST;

$pid         = isset($R['pid']) ? (int)$R['pid'] : 0;
$first_name  = isset($R['patient_first_name']) ? (string)$R['patient_first_name'] : '';
$last_name   = isset($R['patient_last_name'])  ? (string)$R['patient_last_name']  : '';
$email       = isset($R['patient_email'])      ? (string)$R['patient_email']      : '';
$phone       = isset($R['patient_phone'])      ? (string)$R['patient_phone']      : '';
$address     = isset($R['patient_address'])    ? (string)$R['patient_address']    : '';
$diagnosis   = isset($R['patient_diagnosis'])  ? (string)$R['patient_diagnosis']  : '';
$tax_number  = isset($R['patient_tax_number']) ? (string)$R['patient_tax_number'] : '';
$gender      = isset($R['patient_gender'])     ? (int)$R['patient_gender']        : 0;
$pwd1        = isset($R['patient_password1'])  ? (string)$R['patient_password1']  : '';
$pwd2        = isset($R['patient_password2'])  ? (string)$R['patient_password2']  : '';
// Optional: birthdate (YYYY-MM-DD)
$birthdate   = isset($R['patient_birthdate'])  ? (string)$R['patient_birthdate']  : '';

// Helper: check if a column exists on patients
function patients_has_column($name){
    static $cols = null; global $DB;
    if ($cols === null) {
        $rows = $DB->PreparedSelect("SHOW COLUMNS FROM patients", array(), false, false);
        $cols = array();
        if (is_array($rows)) { foreach ($rows as $r) { $cols[$r['Field']] = true; } }
    }
    return isset($cols[$name]);
}

// Save
if (isset($P['save_patient']) && ($pid > 0)) {
    try {
        $set = array(); $params = array('pid'=>$pid);
        $set[] = "first_name = :fn";  $params['fn'] = $P['patient_first_name'];
        $set[] = "last_name = :ln";   $params['ln'] = $P['patient_last_name'];
        if (patients_has_column('phone'))     { $set[] = "phone = :ph";        $params['ph'] = $P['patient_phone']; }
        if (patients_has_column('address'))   { $set[] = "address = :ad";      $params['ad'] = $P['patient_address']; }
        if (patients_has_column('diagnosis')) { $set[] = "diagnosis = :dg";    $params['dg'] = $P['patient_diagnosis']; }
        if (patients_has_column('tax_number')){ $set[] = "tax_number = :tx";   $params['tx'] = $P['patient_tax_number']; }
        if (patients_has_column('gender'))    { $set[] = "gender = :gd";       $params['gd'] = (int)$P['patient_gender']; }
        if (patients_has_column('birthdate')) {
            $bd = isset($P['patient_birthdate']) ? trim((string)$P['patient_birthdate']) : '';
            if ($bd !== '') { $set[] = "birthdate = :bd"; $params['bd'] = $bd; }
            else { $set[] = "birthdate = NULL"; }
        }
        if (patients_has_column('updated_at')){ $set[] = "updated_at = NOW()"; }
        if (patients_has_column('created_by')){ $set[] = "created_by = :cb";   $params['cb'] = (int)$_SESSION['userid']; }

        if (count($set) > 0) {
            $sql = "UPDATE patients SET ".implode(", ", $set)." WHERE id = :pid";
            $DB->PreparedStatement($sql, $params, false, false);
        }
        header('Location: '.ABSURL.'web/admin/editpatient.php?pid='.$pid);
        exit;
    } catch (Exception $e) {}
}

// Separate password save
if (isset($P['save_patient_password']) && ($pid > 0)) {
    try {
        if (patients_has_column('password')) {
            if ($pwd1 !== '' && $pwd2 !== '' && $pwd1 === $pwd2) {
                $hash = password_hash($pwd1, PASSWORD_BCRYPT);
                $params = array('pid'=>$pid, 'pw'=>$hash);
                $set = array('password = :pw');
                if (patients_has_column('updated_at')) { $set[] = 'updated_at = NOW()'; }
                if (patients_has_column('created_by')) { $set[] = 'created_by = :cb'; $params['cb'] = (int)$_SESSION['userid']; }
                $sql = 'UPDATE patients SET '.implode(', ', $set).' WHERE id = :pid';
                $DB->PreparedStatement($sql, $params, false, false);
            }
        }
        header('Location: '.ABSURL.'web/admin/editpatient.php?pid='.$pid);
        exit;
    } catch (Exception $e) {}
}

// Load
if ($pid > 0) {
    $entry = $DB->PreparedSelect('SELECT * FROM patients WHERE id = :pid', array('pid'=>$pid), false, false);
    if (is_array($entry) && isset($entry[0]['id'])) {
        $row = $entry[0];
        $first_name = isset($row['first_name']) ? $row['first_name'] : '';
        $last_name  = isset($row['last_name'])  ? $row['last_name']  : '';
        $email      = isset($row['email'])      ? $row['email']      : '';
        $phone      = isset($row['phone'])      ? $row['phone']      : '';
        $address    = isset($row['address'])    ? $row['address']    : '';
        $diagnosis  = isset($row['diagnosis'])  ? $row['diagnosis']  : '';
        $tax_number = isset($row['tax_number']) ? $row['tax_number'] : '';
        $gender     = isset($row['gender'])     ? (int)$row['gender'] : 0;
        $birthdate  = isset($row['birthdate'])  ? (string)$row['birthdate'] : '';
    }
}

// Variables for template
$PATIENT = array(
    'id'         => $pid,
    'first_name' => $first_name,
    'last_name'  => $last_name,
    'email'      => $email,
    'phone'      => $phone,
    'address'    => $address,
    'diagnosis'  => $diagnosis,
    'tax_number' => $tax_number,
    'gender'     => $gender,
    'birthdate'  => $birthdate,
);

// Include the template
require ROOT."/tpl/_include.php";
