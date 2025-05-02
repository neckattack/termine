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