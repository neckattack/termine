<?php 

/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * mail_reminder_cron.php
 * Detect and send mail to reservators N (2) days before reservation date
 */

error_reporting(-1);        // Enable PHP error reporting (set to 0 to disable)
$PAGE = basename(__FILE__);
$_SERVER["SERVER_NAME"] = "termine.neckattack.net";
$_SERVER["HTTPS"] = true;
$_SERVER["SERVER_PORT"] = "443";
require "_root_.php";       // Defines the ROOT constant

require ROOT."/inc/sql.pdo.php";  // Database class
require ROOT."/inc/helpers.php";  // Helper functions
require ROOT."/config/config.php";  // Config file
require ROOT."/inc/general.php";  // General stuff
include ROOT."/inc/constants.php";

include ROOT."/controller/_include.php";

include ROOT."/controller/index-controller.php";

$last_time_worked = 0;

$filename = ROOT."/logs/mail_reminder.log";

try {
  $fd = fopen($filename, "r");
  $last_time_worked = (int) fread($fd, 256);
}
catch (Exception $e){}
finally {
  fclose($fd);
}

$current_time = (int)time();
$min_rerun_time = 23 * 60 * 60; // not rerun till 23+ hours from last_time_worked


if ( $last_time_worked && $current_time - $last_time_worked < $min_rerun_time ){
  //die("It is not time yet");
}

// Access the database
$DB = Database::getInstance();
$DB->connect($config["server"], $config["database"], $config["user"], $config["pass"]);


customersReminderExecute();
therapistListExecute();


function customersReminderExecute() {

global $DB;

$current_date = date('Y-m-d');
$current_day_of_week = date('N', strtotime($current_date));
$reminder_date = date('Y-m-d', strtotime("+2 days"));

if ($current_day_of_week == 5) { 
  $sunday_date = date('Y-m-d', strtotime("next Sunday", strtotime($current_date)));
  $monday_date = date('Y-m-d', strtotime("next Monday", strtotime($current_date)));
  $tuesday_date = date('Y-m-d', strtotime("next Tuesday", strtotime($current_date)));
  
  $sql  = "SELECT `reservations`.*, `times`.`date_id`, `dates`.`client_id`, `dates`.`date` ";
  $sql .= "FROM `reservations` ";
  $sql .= "LEFT JOIN `times` ON `times`.`id` = `reservations`.`time_id` ";
  $sql .= "LEFT JOIN `dates` ON `dates`.`id` = `times`.`date_id` ";
  $sql .= "WHERE `dates`.`date` IN (:sunday_date, :monday_date, :tuesday_date)";

  $params = array(
    "sunday_date" => $sunday_date,
    "monday_date" => $monday_date,
    "tuesday_date" => $tuesday_date
  );

  $reservations = $DB->PreparedSelect($sql, $params, false, false);
} elseif ($current_day_of_week >= 6) {
  exit;
} else {
  $sql  = "SELECT `reservations`.*, `times`.`date_id`, `dates`.`client_id`, `dates`.`date` ";
  $sql .= "FROM `reservations` ";
  $sql .= "LEFT JOIN `times` ON `times`.`id` = `reservations`.`time_id` ";
  $sql .= "LEFT JOIN `dates` ON `dates`.`id` = `times`.`date_id` ";
  $sql .= "WHERE `dates`.`date` = :reminder_date";

  $params = array("reminder_date" => $reminder_date);
  $reservations = $DB->PreparedSelect($sql, $params, false, false);
}

if ( $reservations && count($reservations) ) {
  $customers = [];
  $clients = [];
  foreach ($reservations as $value) {
    $name_email = $value['name'].$value['email'];
    if ( !isset($customers[$name_email]) ) {
      $customers[$name_email] = [
        'res_id' => $value['id'],
        'client_id' => $value['client_id'],
        'name' => $value['name'],
        'email' => $value['email'],
        'times' => [$value['time_id']],
      ];
    } else {
      $customers[$name_email]['times'][] = $value['time_id'];
    }
    $clients[$value['client_id']] = [
      'client'=>null,
      'contact'=>null,
    ];
  }
  foreach ($clients as $client_id => $value) {
    $clients[$client_id]['client']    = getClientInfos((int)$client_id);
    $clients[$client_id]['contact']   = getContactInfo((int)$client_id);
  }
  foreach ($customers as $cust) {

    $message  = @$clients[$cust['client_id']]['client']["email_reminder_text"];
    $contact  = $clients[$cust['client_id']]['contact'];
    
    try {
      // Send Reminder e-mail
      $email = sendReminderMail($cust['email'], $cust['name'], $cust['times'], $contact, $message);
    }
    catch (Exception $e){}
  }
}

} // END OF function customersReminderExecute()


function therapistListExecute() {

global $DB;

$reservations_date = date('Y-m-d', strtotime("+1 days"));

/*$sql  = "SELECT `reservations`.*, `times`.`date_id`, `times`.`time_start`, `times`.`time_end`, `dates`.`client_id`, `dates`.`client_id`, `dates`.`masseur_id` AS `date_massuer_id`, `clients`.`contact_masseur_id`, `clients`.`name` AS `client_name`, `clients`.`hashlink` AS `client_hashlink` ";
$sql .= "FROM `reservations` ";
$sql .= "LEFT JOIN `times` ON `times`.`id` = `reservations`.`time_id` ";
$sql .= "LEFT JOIN `dates` ON `dates`.`id` = `times`.`date_id` ";
$sql .= "LEFT JOIN `clients` ON `clients`.`id` = `dates`.`client_id` ";
$sql .= "WHERE `dates`.`date` = :reservations_date ";
$sql .= "ORDER BY `times`.`time_start` ";*/

$sql  = "SELECT `reservations`.*, `times`.`date_id`, `times`.`time_start`, `times`.`time_end`, `dates`.`client_id`, `dates`.`client_id`, `dates`.`masseur_id` AS `date_massuer_id`, `clients`.`contact_masseur_id`, `clients`.`name` AS `client_name`, `clients`.`hashlink` AS `client_hashlink` ";
$sql .= "FROM `times` ";
$sql .= "LEFT JOIN `reservations` ON `reservations`.`time_id` = `times`.`id` ";
$sql .= "LEFT JOIN `dates` ON `dates`.`id` = `times`.`date_id` ";
$sql .= "LEFT JOIN `clients` ON `clients`.`id` = `dates`.`client_id` ";
$sql .= "WHERE `dates`.`date` = :reservations_date ";
$sql .= "ORDER BY `times`.`time_start` ";

// Get reservations, that match reservations_date
$params = array("reservations_date" => $reservations_date);
$reservations = $DB->PreparedSelect($sql, $params, false, false);

if ( $reservations && count($reservations) ) {
  $res_groups = [];
  $massuers = [];
  foreach ($reservations as $value) {
    // Minimalinvasiv: Liste an Hauptmasseur (clients.contact_masseur_id) und ggf. Ersatzmasseur (dates.masseur_id)
    $primary_id     = (int)$value['contact_masseur_id'];
    $replacement_id = (int)$value['date_massuer_id'];

    $targets = [];
    if ($primary_id > 0)     { $targets[$primary_id] = true; }
    if ($replacement_id > 0) { $targets[$replacement_id] = true; }

    foreach (array_keys($targets) as $massuer_id) {
      $value['result_massuer_id'] = $massuer_id;
      $res_groups[$massuer_id."-".$value['client_id']][] = $value;
      $massuers[$massuer_id] = null;
    }
  }

  foreach ($massuers as $massuer_id => $value) {
    $massuers[$massuer_id] = getContactBySelfId($massuer_id);
  }

  foreach ($res_groups as $res_group) {
    $message  = null;
    $massuer  = $massuers[$res_group[0]['result_massuer_id']];
    
    try {
      // Send Reminder e-mail
      $email = sendTherapistListMail($massuer, $res_group, $reservations_date, "Anmeldeliste angehangen ".$reservations_date." - neckAttack");
    }
    catch (Exception $e){}
  }
}

} // END OF function therapistListExecute()


// update last_time_worked to current date
try {
  $fd = fopen($filename, "w");
  fwrite($fd, "$current_time"); 
}
catch (Exception $e){}
finally {
  fclose($fd);
}
