<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * editgroup.php
 * Edit/add a group AJAX calls
 */

error_reporting(-1);					// Enable PHP error reporting (set to 0 to disable)
$AJAX = true;
$PAGE = basename(__FILE__);
require "_root_.php";					// Defines the ROOT constant
require ROOT."/inc/_include.php";
require ROOT."/inc/admincheck.php";		// Check if admin logged in


$R = $_REQUEST;
$P = $_POST;
$output = array(
	"success" => 0
);


// No access if not super admin
if ($SUPERADMIN !== true) {
	JSON($output);
	exit;
}


// Check for AJAX requests
if (isset($R["action"])) {
	switch ($R["action"]) {
		
		// Form was sent. Edit/add the user
		case "ajaxSend":
			$result = editGroup($P);	// Specifically POST variables
			
			// Success
			if ($result > 0) {
				$output["success"] = 1;
				$output["id"] = $result;
			}
		break;
		
		
		// Delete a group
		case "deletegroup":
			$id = (int) $R["id"];
			$deleted = false;
			$deleted = deleteGroup($id);
			if ($deleted) {
				$output["success"] = 1;
				$output["rows"]    = $deleted;
				$output["id"]      = $id;
			}
		break;
	}
}





// Ouput the JSON string
JSON($output);
?> 