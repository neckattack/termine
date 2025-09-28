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


		// Send bulk mail to client contacts
		case "sendbulkmail":
			$clientIds = array();
			if (isset($_POST['client_ids']) && is_array($_POST['client_ids'])) {
				$clientIds = array_map('intval', $_POST['client_ids']);
			} elseif (isset($_REQUEST['client_ids'])) {
				$clientIds = array_map('intval', explode(',', $_REQUEST['client_ids']));
			}
			$clientIds = array_values(array_filter($clientIds, function($v){ return $v > 0; }));
			$subject = isset($_REQUEST['subject']) ? trim($_REQUEST['subject']) : '';
			$message = isset($_REQUEST['message']) ? (string)$_REQUEST['message'] : '';
			if ($subject === '') { $subject = 'Kommender Termin bitte bewerben'; }

			if (count($clientIds) === 0) {
				$output['success'] = 0;
				$output['message'] = 'No valid client ids';
				break;
			}

			global $DB;
			$idStr = implode("', '", $clientIds);
			$sql = "SELECT c.id AS client_id, c.name AS client_name, a.email, a.first_name, a.last_name
			        FROM clients c
			        LEFT JOIN admin a ON a.id = c.contact_client_id
			        WHERE c.id IN ('".$idStr."')";
			$rows = $DB->PreparedSelect($sql, array(), false, false);

			$sent = 0; $skipped = array();
			if (is_array($rows)) {
				foreach ($rows as $row) {
					$to = trim(isset($row['email']) ? $row['email'] : '');
					if ($to === '') { $skipped[] = (int)$row['client_id']; continue; }
					$name = trim((isset($row['first_name'])?$row['first_name']:'') . ' ' . (isset($row['last_name'])?$row['last_name']:''));
					if ($name === '') { $name = isset($row['client_name']) ? $row['client_name'] : 'Ansprechpartner'; }
					$from = 'neckAttack Ltd. <termine@neckattack.net>';
					// HTML-Body mit Signatur und optionalem Bild (nur wenn Datei existiert)
					$signatureImgUrl = null;
					$signaturePath = ROOT.'/web/images/email-signature.png';
					if (file_exists($signaturePath)) {
						$signatureImgUrl = ABSURL.'web/images/email-signature.png';
					}
					$userMsgHtml = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
					$signatureHtml = '<div style="margin-top:16px;font:14px/1.4 -apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#222;">'
						.($signatureImgUrl ? '<div style="margin-bottom:12px;"><img src="'.$signatureImgUrl.'" alt="neckattack" style="max-width:520px;width:100%;height:auto;border:0;display:block;"/></div>' : '')
						.'<div style="font-size:12px;color:#444;white-space:pre-line;">'
						.'neckattack ltd.\n'
						.'landhausstrasse 90 | d-70190 stuttgart | germany | tel.: +49.711.3 58 36-09 | fax: +49.711.3 58 36-11 | e-mail: hallo@neckattack.net\n'
						.'sitz der gesellschaft: stuttgart | amtsgericht stuttgart | hrb 25076\n'
						.'ust-idnr. DE 239 255 541\n'
						.'geschäftsführer: dipl.-kfm. chris walther'
						.'</div>'
					.'</div>';
					$htmlBody = '<!DOCTYPE html><html><head><meta charset="utf-8"><meta http-equiv="x-ua-compatible" content="ie=edge"><meta name="viewport" content="width=device-width, initial-scale=1"><title>'.htmlspecialchars($subject, ENT_QUOTES, 'UTF-8').'</title></head><body style="margin:0;padding:0;background:#ffffff;">'
					.'<div style="max-width:680px;margin:0 auto;padding:16px 14px;font:16px/1.55 -apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#222;">'
					.$userMsgHtml
					.$signatureHtml
					.'</div>'
					.'</body></html>';
					$ok = sendMail($to, $name, $from, $subject, $htmlBody, null, true);
					if ($ok) { $sent++; } else { $skipped[] = (int)$row['client_id']; }
				}
			}

			$output['success'] = 1;
			$output['sent'] = $sent;
			$output['skipped'] = $skipped;
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
