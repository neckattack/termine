<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * editslots_ajax.php
 * Show/Add/Edit reservation slots for a time vaue AJAX calls
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


// Check for AJAX requests
if (isset($R["action"])) {
	switch ($R["action"]) {

		// Delete a time entry
		// Will also delete the reservation
		case "deletetimeentry":
			$id = (int) $R["id"];
			$deleted = false;
			$deleted = deleteTimeEntry($id);
			if ($deleted) {
				$output["success"] = 1;
				$output["id"]      = $id;
			}
		break;
		

		// Delete a reservation
		// Will leave the time entry
		case "deletereservation":
			$id = (int) $R["id"];
			$allowed = checkIfAllowed_Reservation($_SESSION["userid"], $id);
			if ($allowed !== true) {
				break;
			}

			$deleted = false;
			$deleted = deleteReservation($id);
			if ($deleted) {
				$output["success"] = 1;
				$output["rows"]    = $deleted;
				$output["id"]      = $id;
			}
		break;
		

		// Check if a new block is available
		case "checkifavailable":
			$date_id    = (int) $R["date_id"];
			$startTimes = $R["starttimes"];
			$endTimes   = $R["endtimes"];
			$maxKey     = max(max(array_keys($startTimes)), max(array_keys($endTimes)));
			$times      = array(
				"start" => array(),
				"end"   => array(),
			);
			$error      = 0;
			$errors     = array();
			
			// The array entry is there even if it is empty
			for ($x=0; $x<=$maxKey; $x++) {
				$error = 0;
				if ($startTimes[$x] == "") {
					$error = 1;
				}
				if ($endTimes[$x] == "") {
					if ($error == 1) {
						continue;	// Skip this line
					}
					$error = 1;
				}

				$tmpArr = array(
					"start" => $startTimes[$x],
					"end"   => $endTimes[$x],
				);
				

				// Start or end time not entered
				if ($error === 1) {
					$errors[$x] = $tmpArr;
				}

				// Both times entered
				else {
					#$times[$x] = $tmpArr;
					$times["start"][$x] = $tmpArr["start"];
					$times["end"][$x]   = $tmpArr["end"];
				}
			}
			
			#print_r($times);

			// Error
			if (count($errors) > 0) {
				$output["success"] = 0;
				break;
			}

			
			$result = checkIfAvailable($times, $date_id);
			#var_dump($result);
			
			if ($result["allow"] === true) {
				$output["success"] = 1;
			}
			else {
				$output["success"] = 0;
				$output["errors"]  = $result["errors"];
			}
		break;
	}
}





// Ouput the JSON string
JSON($output);
?> 