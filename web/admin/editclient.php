<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * editclient.php
 * Edit/add a client
 */

error_reporting(-1);				// Enable PHP error reporting (set to 0 to disable)
$PAGE = basename(__FILE__);
require "_root_.php";				// Defines the ROOT constant
require ROOT."/inc/_include.php";
require ROOT."/inc/admincheck.php";	// Check if admin logged in
require ROOT."/inc/email_text.php";

// Get the previous page
$previous  = $breadcrumbs[PAGE];


$R                  = $_REQUEST;
$clientID           = (isset($R["cid"]))                ? (int) $R["cid"]                     : 0;
$cName              = (isset($R["cName"]))              ? trim($R["cName"])                   : "";
$cEnabled           = (isset($R["cEnabled"]))           ? $R["cEnabled"]                      : "1";
$cText              = (isset($R["cText"]))              ? trim(stripslashes($R["cText"]))     : "";
$emailText          = (isset($R["emailText"]))          ? trim(stripslashes($R["emailText"])) : $email_default_text;
$contact_client_id  = (isset($R["contact_client_id"]))  ? (int) $R["contact_client_id"]       : 0;
$contact_masseur_id = (isset($R["contact_masseur_id"])) ? (int) $R["contact_masseur_id"]      : 0;
$cDays              = (isset($R["cDays[]"]))            ? $R["cDays[]"]                       : array(array("date" => "", "id" => ""));
$group_id           = (isset($R["group_id"]))           ? (int) $R["group_id"]                : 0;
$price							= (isset($R["price"]))              ? trim($R["price"])     : "";
$hasImage           = false;


// Get the group list if super admin
if ($SUPERADMIN === true) {
	require ROOT."/controller/admin-overview_groups-controller.php";
	$groupList = getAllGroups();
}


// Get the user list
require ROOT."/controller/admin-overview_users-controller.php";
$userList = getAllUsers();


// Edit a client
if ($clientID > 0) {
	$client   = getClientInfos($clientID);
	#pre($client);
	#exit;

	if ($client !== false) {
		$cName              = trim($client["name"]);
		$cEnabled           = $client["enabled"];
		$cText              = htmlspecialchars(trim(stripslashes($client["greeting_text"])));
		$emailText          = (isset($client["email_text"][5])) ? htmlspecialchars(trim(stripslashes($client["email_text"]))) : $email_default_text;
		$contact_client_id  = (int) ($client["contact_client_id"]);
		$contact_masseur_id = (int) ($client["contact_masseur_id"]);
		$cDays              = $client["dates"];
		$group_id           = (int) $client["group_id"];
		$price				= $client["price"];
		$hash               = generateHashLink($client["hashlink"]);	// Use existing hash link
		$hasImage           = ( isset($client["image"][10]) );
		$associated_users   = $client["contacts"]["mixed"];

		// Buchungs-/Stornofrist korrekt setzen
		$booking_deadline_hours = isset($client["booking_deadline_hours"]) ? $client["booking_deadline_hours"] : 0;
		// Doppelbuchungsmodus laden
		$avoid_double_bookings_mode = isset($client["avoid_double_bookings_mode"]) ? $client["avoid_double_bookings_mode"] : 'none';

		// All users that are directly associated with this client
		$user_ids           = $client["contacts"]["direct"];
	}


	// If not super admin, check if allowed to edit this client
	if ($SUPERADMIN !== true) {
		if (isset($_SESSION["group_ids"][$group_id]) || isset($associated_users[$_SESSION["userid"]])) {

		}
		else {
			header("Location: ".$previous);
			exit;
		}
	}
}

// Add a new client
else {
	// Generate a new hash link
	$hash  = generateHashLink();
	// Defaults für neue Kunden
	$avoid_double_bookings_mode = 'none';
}


// Include the template
require ROOT."/tpl/_include.php";
