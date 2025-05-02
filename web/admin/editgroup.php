<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * editgroup.php
 * Edit/add a group
 */

error_reporting(-1);				// Enable PHP error reporting (set to 0 to disable)
$PAGE = basename(__FILE__);
require "_root_.php";				// Defines the ROOT constant
require ROOT."/inc/_include.php";
require ROOT."/inc/admincheck.php";	// Check if admin logged in

// Get the previous page
$previous  = $breadcrumbs[PAGE];


// Access only for superadmins
if ($SUPERADMIN !== true) {
	header("Location: index.php");
	exit;
}

// Get the user list if super admin
if ($SUPERADMIN === true) {
	require ROOT."/controller/admin-overview_users-controller.php";
	$userList = getAllUsers();

	require ROOT."/controller/admin-overview_clients-controller.php";
	$clientList = getAllClients();
}



$R       = $_REQUEST;
$id      = (isset($R["id"]))         ? (int)$R["id"]    : 0;
$name    = (isset($R["group_name"])) ? $R["group_name"] : "";
$info    = (isset($R["group_info"])) ? $R["group_info"] : "";
$members = array(
	"users"   => array(),
	"clients" => array(),
);




// Edit a group
if ($id > 0) {
	$entry = getGroupInfos($id);

	if ($entry !== false) {
		$name    = $entry["group_name"];
		$info    = $entry["group_info"];
		$members = $entry["members"];
	}

	// If not super admin, check if allowed to edit this group
	if ($SUPERADMIN !== true) {
		if (!isset($_SESSION["group_ids"][$id])) {
			header("Location: ".$previous);
		}
	}
}

// Add a group
else {
	
	
}




// Include the template
require ROOT."/tpl/_include.php";
?> 