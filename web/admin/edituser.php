<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * edituser.php
 * Edit/add a user
 */

error_reporting(-1);				// Enable PHP error reporting (set to 0 to disable)
$PAGE = basename(__FILE__);
require "_root_.php";				// Defines the ROOT constant
require ROOT."/inc/_include.php";
require ROOT."/inc/admincheck.php";	// Check if admin logged in

$R           = $_REQUEST;
$id          = (isset($R["id"]))              ? (int) $R["id"]          : 0;
$username    = (isset($R["user_username"]))   ? $R["user_username"]     : "";
$email       = (isset($R["user_email"]))      ? $R["user_email"]        : "";
$pwd1        = (isset($R["user_password1"]))  ? $R["user_password1"]    : "";
$pwd2        = (isset($R["user_password2"]))  ? $R["user_password2"]    : "";
$phone       = (isset($R["user_phone"]))      ? $R["user_phone"]        : "";
$gender      = (isset($R["user_gender"]))     ? (int) $R["user_gender"] : 0;
$first_name  = (isset($R["user_first_name"])) ? $R["user_first_name"]   : "";
$last_name   = (isset($R["user_last_name"]))  ? $R["user_last_name"]    : "";
// Neue Felder rechte Spalte: Defaults aus Request, sonst leer
$user_address            = isset($R['user_address']) ? $R['user_address'] : '';
$user_street             = isset($R['user_street']) ? $R['user_street'] : '';
$user_house_no           = isset($R['user_house_no']) ? $R['user_house_no'] : '';
$user_zip                = isset($R['user_zip']) ? $R['user_zip'] : '';
$user_city               = isset($R['user_city']) ? $R['user_city'] : '';
$user_tax_number         = isset($R['user_tax_number']) ? $R['user_tax_number'] : '';
$iban                    = isset($R['user_iban']) ? $R['user_iban'] : '';
$user_vat_exempt_reason  = isset($R['user_vat_exempt_reason']) ? $R['user_vat_exempt_reason'] : '';
$user_profession         = isset($R['user_profession']) ? $R['user_profession'] : '';
$user_diagnosis          = isset($R['user_diagnosis']) ? $R['user_diagnosis'] : '';
$memberships = array(
	"clients" => array(),
	"groups"  => array(),
);


// Access only for superadmins
// Or when id == userid
if ($SUPERADMIN !== true && (int) $_SESSION["userid"] !== $id) {
	header("Location: index.php");
	exit;
}

// Get the group and client list if super admin
if ($SUPERADMIN === true) {
	require ROOT."/controller/admin-overview_groups-controller.php";
	require ROOT."/controller/admin-overview_clients-controller.php";
	$groupList  = getAllGroups();
	$clientList = getAllClients();
}




// Get the previous page
$previous  = $breadcrumbs[PAGE];



// Edit a user
if ($id > 0) {
	$entry = getUserInfos($id);

	if ($entry !== false) {
		$username    = $entry["username"];
		$email       = $entry["email"];
		$phone       = $entry["phone"];
		$gender      = (int) $entry["gender"];
		$first_name  = $entry["first_name"];
		$last_name   = $entry["last_name"];
		// Neue Felder aus DB in Template-Variablen übernehmen
		$user_address           = isset($entry['address']) ? $entry['address'] : '';
		$user_street            = isset($entry['street']) ? $entry['street'] : '';
		$user_house_no          = isset($entry['house_no']) ? $entry['house_no'] : '';
		$user_zip               = isset($entry['zip']) ? $entry['zip'] : '';
		$user_city              = isset($entry['city']) ? $entry['city'] : '';
		$user_tax_number        = isset($entry['tax_number']) ? $entry['tax_number'] : '';
		$iban                   = isset($entry['iban']) ? $entry['iban'] : '';
		$user_vat_exempt_reason = isset($entry['vat_exempt_reason']) ? $entry['vat_exempt_reason'] : '';
		$user_profession        = isset($entry['profession']) ? $entry['profession'] : '';
		$user_diagnosis         = isset($entry['diagnosis']) ? $entry['diagnosis'] : '';
		$memberships = $entry["memberships"];
		
		#pre($memberships);
	}
	
}

// Add a user
else {
	
	
}




// Include the template
require ROOT."/tpl/_include.php";
?> 