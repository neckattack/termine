<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * image_handler.php
 * Edit/add a client AJAX calls
 */

error_reporting(-1);					// Enable PHP error reporting (set to 0 to disable)
$AJAX = true;
$PAGE = basename(__FILE__);
require "_root_.php";					// Defines the ROOT constant
require ROOT."/inc/_include.php";
require ROOT."/inc/admincheck.php";		// Check if admin logged in

$R      = $_REQUEST;
$action = $R["action"];
$id     = (int) $R["owner_id"];
$type   = $R["owner_type"];				// "client" or "group"
$output = array(
	"success" => 0
);

switch ($action) {
	case "save":
		$output = saveImage($_FILES, $id, $type);
		echo json_encode($output);		// Output into an iframe
		exit;
	break;

	case "delete":
		$output = deleteImage($id, $type);
	break;
}

JSON($output);
?>