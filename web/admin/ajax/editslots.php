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

$cancelRequestByUser = isset($_REQUEST['byUser']) ? 1 : '';

if(!$cancelRequestByUser) {
	require ROOT."/inc/admincheck.php";		// Check if admin logged in
} 

$R = $_REQUEST;
$P = $_POST;
$output = array(
	"success" => 0
);


// Check for AJAX requests
if (isset($R["action"])) {
	switch ($R["action"]) {
    case "deletetimeentry":
        $id = (int) $R["id"];
        $deleted = deleteTimeEntry($id);
        if ($deleted) {
            $output["success"] = 1;
            $output["id"] = $id;
        } else {
            $output["error_message"] = "Failed to delete time entry with ID $id";
        }
        break;

    case "deletereservation":
        $id = (int) $R["id"];
        // $allowed = checkIfAllowed_Reservation($_SESSION["userid"], $id);
        // if ($allowed !== true) {
        //     $output["error_message"] = "Reservation deletion not allowed for ID $id";
        //     break;
        // }

        $deleted = deleteReservation($id);
        if ($deleted) {
            $output["success"] = 1;
            $output["rows"] = $deleted;
            $output["id"] = $id;
        } else {
            $output["error_message"] = "Failed to delete reservation with ID $id";
        }
        break;

    case "checkifavailable":
        $date_id = (int) $R["date_id"];
        $startTimes = $R["starttimes"];
        $endTimes = $R["endtimes"];
        $maxKey = max(max(array_keys($startTimes)), max(array_keys($endTimes)));
        $times = array("start" => array(), "end" => array());
        $errors = array();

        for ($x = 0; $x <= $maxKey; $x++) {
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

            $tmpArr = array("start" => $startTimes[$x], "end" => $endTimes[$x]);

            if ($error === 1) {
                $errors[$x] = $tmpArr;
            } else {
                $times["start"][$x] = $tmpArr["start"];
                $times["end"][$x] = $tmpArr["end"];
            }
        }

        if (count($errors) > 0) {
            $output["error_message"] = "Errors in time input: " . json_encode($errors);
            $output["success"] = 0;
            break;
        }

        $result = checkIfAvailable($times, $date_id);
        
        if ($result["allow"] === true) {
            $output["success"] = 1;
        } else {
            $output["success"] = 0;
            $output["errors"] = $result["errors"];
            $output["error_message"] = "Time block not available";
        }
        break;
}

}





// Ouput the JSON string
JSON($output);
?> 