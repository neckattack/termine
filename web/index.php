<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * index.php
 * The landing page for the customer
 */

error_reporting(-1);				// Enable PHP error reporting (set to 0 to disable)
$PAGE = basename(__FILE__);
require "_root_.php";				// Defines the ROOT constant
require ROOT."/inc/_include.php";

$date_id  = 0;
$hashlink = (isset($_REQUEST["h"])) ? $_REQUEST["h"] : false;		// The hash link which identifies the client


// Access the database
$DB = Database::getInstance();	// Create the database object. See sql.pdo.php for the Database class
$DB->connect($config["server"], $config["database"], $config["user"], $config["pass"]);	// Connect to the database

// Get client infos and check if the hashlink exists
$sql    = "SELECT `id` FROM `clients` WHERE `hashlink` = :hashlink AND `enabled` = '1'";
$client = $DB->PreparedSelect($sql, array("hashlink" => $hashlink), false, false);


// No client found
// Display something. Welcome page?
if (!$client || $client[0]["id"] < 1) {
	// Redirect to welcome page
	#header("Location: welcome");
	header("Location: welcome.php");
}


// The client landing page (reservations)
else {
	// Include the landing page
	$client     = getClientInfos((int) $client[0]["id"]);
	$clientId   = (int) $client["id"];
	$date_id    = 0;
	$times      = false;
	$toolate    = false;
	$success    = false;
	$taken      = array();
	$reserved   = array();
	$messages   = array();
	$error      = 0;
	$hasImage   = false;
	$imageLink  = "";
	$contact_id = (int) $client["contact_masseur_id"];
	$message    = $client["email_text"];



	// No-JavaScript fallback
	if (isset($_POST) && count($_POST) > 0) {
		$P       = $_POST;
		$date_id = (isset($P["date"])) ? (int) $P["date"] : 0;

		// Submit button has been triggered
		if (isset($P["submit"])) {
			while ($error === 0) {
				// Time missing
				if (!isset($P["times"][0])) {
					$messages[] = "Bitte wählen Sie mindestens einen Termin aus";
					$error++;
				}

				// Name missing
				if (!isset($P["name"][1]) || $P["name"] == "Name") {
					$messages[] = "Bitte geben Sie Ihren Namen an";
					$error++;
				}

				// Email missing
				if (!isset($P["email"][1]) || $P["email"] == "E-Mail") {
					$messages[] = "Bitte geben Sie eine gültige E-Mail-Adresse an";
					$error++;
				}

				if ($error > 0) {
					break;
				}


				// Insert the reservation
				$times = $P["times"];
				$name  = $P["name"];
				$email = $P["email"];

				// Register to times
				$checkAvail = checkTimesAvailable($times);

				// All times are free, set reservation
				if ($checkAvail["allAvailable"] === true) {
					$reserved = setReservations($times, $name, $email);

					if ($reserved !== false) {
						$success = true;

						// Get the contact information for this client
						$contact = getContactInfo($clientId);


						// Send confirmation e-mail
						// No HTML
						$email = sendConfirmationMail($email, $name, $times, $contact, $message);
					}
				}

				// Some were taken, return these
				else {
					$toolate = true;
					$taken   = $checkAvail["taken"];
				}

				break;
			}
		}
	}


	// Get the result for this client
	$result = getDates($client["id"], false, true);	// set $alltimes to true so will return all times in "times_assoc" array

	// Get the date id of the first entry, only if date_id is 0
	if ($date_id === 0) {
		$date_id  = (isset($result["dates"][0])) ? (int) $result["dates"][0]["id"] : 0;
	}


	// Get the image
	$sql  = "SELECT ";
	$sql .= "IF(`c`.`image` IS NULL OR `c`.`image` = '', 0, 1) AS `client_has_image`, \n";
	$sql .= "IF(`g`.`image` IS NULL OR `g`.`image` = '', 0, 1) AS `group_has_image` \n";
	$sql .= "FROM `clients` AS `c` \n";
	$sql .= "LEFT JOIN `groups` AS `g` ON (`g`.`group_id` = `c`.`group_id`) \n";
	$sql .= "WHERE `c`.`id` = :id";
	$params = array(
		"id" => $clientId
	);
	$res = $DB->PreparedSelect($sql, $params, false, false);

	if (isset($res[0]["client_has_image"]) && $res[0]["client_has_image"] == 1) {
		$type     = "client";
		$owner_id = $clientId;
		$hasImage = true;

	}
	elseif (isset($res[0]["group_has_image"]) && $res[0]["group_has_image"] == 1) {
		$type     = "group";
		$owner_id = (int) $client["group_id"];
		$hasImage = true;
	}

	if ($hasImage === true) {
		$imageLink = "id=".$owner_id."&type=".$type;
		$imageLink = "s=".base64_encode($imageLink);
	}

	// Include the template files
	require ROOT."/tpl/_include.php";
}
?>
