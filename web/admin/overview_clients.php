<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * overview_clients.php
 * Show a list of clients
 */
error_reporting(-1);				// Enable PHP error reporting (set to 0 to disable)
$PAGE = basename(__FILE__);
require "_root_.php";				// Defines the ROOT constant
require ROOT."/inc/_include.php";
require ROOT."/inc/admincheck.php";	// Check if admin logged in
require ROOT."/controller/admin-overview_groups-controller.php";


// Get the group list if super admin
if ($SUPERADMIN === true) {
	$groupList1 = array(array(
		"group_id"   => 0,
		"group_name" => "Nicht zugeordnet",
	));
	$groupList2 = getAllGroups();
	$groupList  = array_merge($groupList1, $groupList2);
}

// Else get only the groups the user is a member of
else {
	$groupList = getAllGroupsForUser($_SESSION["userid"]);
}

$group_id   = (isset($_REQUEST["group_id"])) ? (int)$_REQUEST["group_id"] : null;
$clientList = getAllClients($group_id);
require ROOT."/tpl/_include.php";
?> 