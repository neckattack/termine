<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * editclient.php
 * Edit/add a client AJAX calls
 */

error_reporting(-1);					// Enable PHP error reporting (set to 0 to disable)
$AJAX = true;
$PAGE = basename(__FILE__);
require "_root_.php";					// Defines the ROOT constant
require ROOT."/inc/_include.php";
require ROOT."/inc/admincheck.php";		// Check if admin logged in

$output = array(
	"success" => 0,
);

// Check if user is allowed to edit this client
if ($SUPERADMIN !== true) {
	$group_id = (isset($_REQUEST["id"]) && $_REQUEST["id"] > 0) ? (int) $_REQUEST["id"] : 0;
	if ($group_id > 0) {
		// $client_id = ??
        // if ( !isset($_SESSION["client_ids"][$client_id]) ) {
		if ( !isset($_SESSION["ISADMIN"]) || !$_SESSION["ISADMIN"] || !isset($_SESSION["LOGGED_IN"]) || !$_SESSION["LOGGED_IN"] ) {
			JSON($output);
			exit;
		}
	}
}

// Check for AJAX requests
if (isset($_REQUEST["action"])) {
	switch ($_REQUEST["action"]) {

		// Form was sent. Edit/add the client
		case "ajaxSend":
			$result = editClient($_POST);	// Specifically POST variables

			// Success
			if ($result > 0) {
				$output["success"] = 1;
				$output["id"] = $result;
			}
		break;


		// Get a new hash link
		case "newHash":
			$output["hash"] = generateHashLink();
		break;


		// Enable / Disable a client
		case "enableclient":
		case "disableclient":
			$id    = (int) $_REQUEST["id"];
			$state = ($_REQUEST["action"] == "disableclient") ? 0 : 1;
			$output["success"] = setClientState($id, $state);
			$output["id"]      = $id;
		break;

// copy a date
		case "copydate":
			$id = (int) $_REQUEST["id"];
			$dates = $_REQUEST['newDates'];
			$cid = $_REQUEST['cid'];
		
			$copied = false;
			$copied = copyDate($id, $dates, $cid);
			if ($copied) {
				$output["success"] = 1;
				$output["rows"]    = $copied;
				$output["id"]      = $id;
			}
		break;


		// Delete a date
		case "deletedate":
			$id = (int) $_REQUEST["id"];
			
			$deleted = false;
			$deleted = deleteDate($id);
			if ($deleted) {
				$output["success"] = 1;
				$output["rows"]    = $deleted;
				$output["id"]      = $id;
			}
		break;
		
		// Add a new date
		case "savedate":
			$date = $_REQUEST["date"];
			$cid  = (int) $_REQUEST["cid"];
			$add  = false;
			$add  = addDate($cid, $date);
			if ($add) {
				$output["success"] = $add["rows"];
				$output["id"]      = $add["id"];
			}

		break;


		// Change date masseur_id
		case "datemasseur":
			$date_id = (int) $_REQUEST["date_id"];
			$masseur_id  = (int) $_REQUEST["masseur_id"];
			$changed  = false;
			$changed  = changeDateMasseur($date_id, $masseur_id);
			if ($changed) {
				$output["success"] = true;
			}

		break;



		// Remove a client from a group
		case "removeclient":
			$target = (int) $_REQUEST["tid"];
			$client = (int) $_REQUEST["id"];
			$msg    = "";

			$remove = removeClientFromGroup($target, $client);
			$msg    = "Dieser Kunde kann nicht aus dieser Gruppe entfernt werden.";

			if ($remove) {
				$output["success"] = $remove;

				if ($remove === -1) {
					$output["message"] = $msg;
				}
			}
		break;


		// Add a client to a group
		case "addclient":
			$target = (int) $_REQUEST["target"];
			$client = (int) $_REQUEST["self"];
			$msg    = "";

			$add    = addClientToGroup($target, $client);
			$msg    = "Dieser Kunde kann nicht zu dieser Gruppe hinzugefügt werden.";


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
