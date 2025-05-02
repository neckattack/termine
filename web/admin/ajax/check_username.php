<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * check_username.php
 * Edit/add a client AJAX calls
 */

error_reporting(-1);					// Enable PHP error reporting (set to 0 to disable)
$AJAX = true;
$PAGE = basename(__FILE__);
require "_root_.php";					// Defines the ROOT constant
require ROOT."/inc/_include.php";
require ROOT."/inc/admincheck.php";		// Check if admin logged in


// No access if not super admin
if ($SUPERADMIN !== true && (int) $_SESSION["userid"] !== (int) $_REQUEST["id"]) {
	exit;
}

JSON($allowed);
?> 