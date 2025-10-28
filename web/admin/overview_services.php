<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * overview_services.php
 * GebüH Services Übersicht
 */

error_reporting(-1);                // Enable PHP error reporting (set to 0 to disable)
$PAGE = basename(__FILE__);
require "_root_.php";              // Defines the ROOT constant
require ROOT."/inc/_include.php";
require ROOT."/inc/admincheck.php";    // Check if admin logged in

// Access only for superadmins
if ($SUPERADMIN !== true) {
    header("Location: index.php");
    exit;
}

$services = getAllGebuehServices();

require ROOT."/tpl/_include.php";
?> 
