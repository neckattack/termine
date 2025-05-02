<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
// Check if a user name already exists

$R       = $_REQUEST;
$action  = $R["action"];
$name    = strtolower(trim($R["user_username"]));
$id      = (int) $R["id"];


/**
 * Check if the username is already existing (of course only if it's not the same user)
 */
function checkExists($name, $id) {
	global $DB;
	$sql     = "SELECT COUNT(*) AS `count` FROM `admin` WHERE `username` = :name AND `id` != :id";
	$params  = array(
		"id"   => $id,
		"name" => $name,
	);
	$res     = $DB->PreparedSelect($sql, $params, false, false);
	$allowed = (isset($res[0]["count"]) && (int) $res[0]["count"] > 0) ? false : true;

	return $allowed;
}

/**
 * Check if the username contains only alphanumeric chars (including some special ones)
 */
function checkValid($name) {
	$pattern = "#[^a-z0-9äÄöÖüÜßáÁàÀâÂéÉèÈêÊóÓòÒôÔúÚùÙûÛ\._\-]#i";
	$check   = preg_match($pattern, $name);
	$allowed = ($check === 1) ? false : true;

	return $allowed;
}


switch ($action) {
	case "available":
		$allowed = checkExists($name, $id);
	break;


	case "valid":
		$allowed = checkValid($name);
	break;


	case "both":
		$allowed = checkExists($name, $id);
		if ($allowed !== true) {
			$allowed = "Dieser Benutzername ist bereits vergeben.";
			break;
		}

		$allowed = checkValid($name);
		if ($allowed !== true) {
			$allowed = "Bitte geben sie nur A-Z, 0-9, Punkt, Binde- oder Unterstrich bei Benutzername ein.";
		}
	break;


	default:
		$allowed = false;
	break;
}


?>