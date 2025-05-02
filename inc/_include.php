<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
require "_root.php";
require ROOT."/inc/session.php";	// Session stuff
require ROOT."/inc/sql.pdo.php";	// Database class
require ROOT."/inc/helpers.php";	// Helper functions
require ROOT."/config/config.php";	// Config file
require ROOT."/inc/general.php";	// General stuff
include ROOT."/inc/constants.php";

// Include the controller for this page
include ROOT."/controller/_include.php";

?>