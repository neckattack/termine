<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
error_reporting(-1);					// Enable PHP error reporting (set to 0 to disable)
$AJAX = true;
$PAGE = basename(__FILE__);
require "_root_.php";					// Defines the ROOT constant
require ROOT."/inc/_include.php";


$R      = $_REQUEST;
$output = array(
	"success" => 0
);


switch ($R["action"]) {
	
	// Get the time values for a specific date
	case "getTime":
		$dateID = (int) $R["id"];
		$times  = getTimesForDate($dateID);
		$output = array(
			"success" => 1,
			"data" =>$times
		);
	break;
	

	// Send data
	case "ajaxSend":
		$clientID = (int) $R["id"];
		$client   = getClientInfos($clientID);
		$times    = @$R["times"];
		$name     = @$R["name"];
		$email    = @strtolower($R["email"]);
		$message  = $client["email_text"];
		
		
		// Register to times
		$checkAvail = checkTimesAvailable($times);
		
		// All times are free, set reservation
		if ($checkAvail["allAvailable"] === true) {
			$success = setReservations($times, $name, $email);
			if ($success !== false) {
				$output["success"] = 1;
				$output["data"] = $success;

				// Get the contact information for this client
				$contact = getContactInfo($clientID);
				
				// Send confirmation e-mail
				$email = sendConfirmationMail($email, $name, $times, $contact, $message);
				$output["email"] = $email;
			}
		}
		
		// Some were taken, return these 
		else {
			$output["taken"] = $checkAvail["taken"];
		}
		
		
	break;
}


echo json_encode($output);

?>