<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * index.php
 * The landing page for the customer
 */

error_reporting(-1);				// Enable PHP error reporting (set to 0 to disable)
$PAGE = basename(__FILE__);
require "_root_.php";				// Defines the ROOT constant
require ROOT."/inc/_include.php";
include ROOT."/controller/index-controller.php";
include ROOT."/controller/admin-editslots-controller.php";

$emailHash = @$_GET["e"];

$reservations = [];

if ( $emailHash) {

	$toolate    = false;
	$error    = false;
	$success    = false;

	// Access the database
	$DB = Database::getInstance();	// Create the database object. See sql.pdo.php for the Database class
	$DB->connect($config["server"], $config["database"], $config["user"], $config["pass"]);	// Connect to the database

	$reservations = getAllReservationsForEmailHash($emailHash);
	if ($reservations) {
		$reservations_times = array_map(function($r){return $r['time_id'];}, $reservations);

		$client = getClientInfos($reservations[0]["client_id"]);

		$result = getDates($client["id"], false, true); // array of ["dates", "times", "times_assoc"]
		$date_id  = (isset($result["dates"][0])) ? (int) $result["dates"][0]["id"] : 0;
	}
}



// Include the template files
require ROOT."/tpl/_include.php";