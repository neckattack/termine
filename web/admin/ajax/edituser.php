<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * edituser.php
 * Edit/add a user AJAX calls
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
if ($SUPERADMIN !== true && (int) $_SESSION["userid"] !== (int) $_REQUEST["id"]) {
	JSON($output);
	exit;
}


// Check for AJAX requests
if (isset($R["action"])) {
	switch ($R["action"]) {
		
		// Form was sent. Edit/add the user
		case "ajaxSend":
			$result = editUser($P);	// Specifically POST variables
			
			// Success
			if ($result > 0) {
				$output["success"] = 1;
				$output["id"] = $result;
			}
			elseif ($result === 0) {
				$output["message"] = "Keine Änderung notwendig.";
			}
		break;
		
		
		// Delete a user
		case "deleteuser":
			$id = (int) $R["id"];
			$deleted = false;
			$deleted = deleteUser($id);
			if ($deleted) {
				$output["success"] = 1;
				$output["rows"]    = $deleted;
				$output["id"]      = $id;
			}
		break;


		// Remove a user from a group / client
		case "removeuser":
			$type   = $R["type"];
			$target = (int) $R["tid"];
			$user   = (int) $R["id"];
			$msg    = "";
			
			switch ($type) {
				case "group":
				case "user":
					$remove = removeUserFromGroup($target, $user);
					$msg = "Dieser Benutzer kann nicht aus dieser Gruppe entfernt werden.";
				break;

				case "client":
					$remove = removeUserFromClient($target, $user);
					$msg = "Dieser Benutzer kann nicht von diesem Kunden entfernt werden.";
				break;
			}
			

			if ($remove) {
				$output["success"] = $remove;

				if ($remove === -1) {
					$output["message"] = $msg;
				}
			}
		break;


		// Add a user to a group / client
		case "adduser":
			$type   = $R["type"];
			$target = (int) $R["target"];
			$user   = (int) $R["self"];
			$msg    = "";

			switch ($type) {
				case "group":
				case "user":
					$add = addUserToGroup($target, $user);
					$msg = "Dieser Benutzer kann nicht zu dieser Gruppe hinzugefügt werden.";
				break;

				case "client":
					$add = addUserToClient($target, $user);
					$msg = "Dieser Benutzer kann nicht zu diesem Kunden hinzugefügt werden.";
				break;
			}


			if ($add) {
				$output["success"] = $add;

				if ($add === -1) {
					$output["message"] = $msg;
				}
			}
		break;
	}
}





// Ouput the JSON string
JSON($output);
?> 