<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * add.php
 * Add a client
 */

error_reporting(-1);				// Enable PHP error reporting (set to 0 to disable)
$PAGE = basename(__FILE__);
require "_root_.php";				// Defines the ROOT constant
require ROOT."/inc/_include.php";
require ROOT."/inc/salt.php";		// Salt generation


$P = $_POST;
$R = $_REQUEST;

$error      = 0;
$LOGGED_IN  = false;
$REDIRECT   = false;
$ADMIN      = false;
$SUPERADMIN = false;



// Logout
if (@$R["do"] && $R["do"] == "logout") {
	$LOGGED_IN = false;
	$ADMIN     = false;
	session_unset();
	session_destroy();
}


// Check if already logged in
if (isset($_SESSION)) {
	if (@$_SESSION["LOGGED_IN"] === true && @$_SESSION["ISADMIN"] === true) {
		// Extract salt
		$userid      = @$_SESSION["userid"];
		$username    = @$_SESSION["username"];
		$salt        = @substrSalt($_SESSION["ADMINHASH"]);
		$hash        = @generateSalt($userid."---".$username, $salt);
		$match       = @($_SESSION["ADMINHASH"] === $hash);
		$ADMIN       = @$match;
		$SUPERADMIN  = @($ADMIN === true && $_SESSION["SUPERADMIN"] === true && $_SESSION["group_id"] === 1);
		$LOGGED_IN   = @$_SESSION["LOGGED_IN"];
		$group_ids   = @$_SESSION["group_ids"];
		$group_names = @$_SESSION["group_names"];
		$client_ids  = @$_SESSION["client_ids"];
		$group_names = @$_SESSION["client_names"];

		unset($hash);
		unset($salt);
	}
}


// Check if username and password were sent
if (isset($P["username"]) && isset($P["password"])) {
	//session_destroy();
	if (!session_id()) {
		session_start();
	}

	while ($error == 0) {
		// Check for saltDB
		$username = trim($P["username"]);
		$password = $P["password"];

		// Request the encrypted password from the database
		$sql    = "SELECT `id`, `password` FROM `admin` WHERE `username` = :username LIMIT 1";
		$params = array("username" => $username);
		$rows   = $DB->PreparedSelect($sql, $params);

		// No result
		if (!isset($rows[0]["password"])) {
			error_log("[admin-login] user not found: ".$username);
			$error++;
			break;
		}

		// Extract salt
		$pwdDB  = $rows[0]["password"];
		$saltDB = substrSalt($pwdDB);

		if (strlen($saltDB) < 10) {
			error_log("[admin-login] invalid salt length for user id ".(int)$rows[0]['id']);
			$error++;
			break;
		}

		// Generate the hash value
		// SHA1(SALT + SHA1(SALT+Password)) = SHA1(Password)
		$hash  = generateSalt($password, $saltDB);
		$match = ($pwdDB === $hash);

		if ($match !== true) {
			error_log("[admin-login] password mismatch for user: ".$username);
			$error++;
			break;
		}


		// Get user data
		$sql    = "SELECT `a`.`id`, `a`.`username` \n";
		$sql   .= "FROM `admin` AS `a` \n";
		$sql   .= "WHERE SHA1(`a`.`username`) = :user AND SHA1(`a`.`password`) = :hash \n";
		$sql   .= "LIMIT 1";
		$params = array("user" => sha1($username), "hash" => sha1($hash));
		$user   = $DB->PreparedSelect($sql, $params, false, false);

		if (!isset($user[0]["id"]) || $user[0]["id"] < 1) {
			error_log("[admin-login] second-stage lookup failed for user: ".$username);
			$error++;
			break;
		}

		$user_id = (int) $user[0]["id"];

		// Get all group memberships
		$sql  = "SELECT `m`.`group_id`, `g`.`group_name` \n";
		$sql .= "FROM `group_members` AS `m` \n";
		$sql .= "LEFT JOIN `groups` AS `g` ON (`g`.`group_id` = `m`.`group_id`) \n";
		$sql .= "WHERE `m`.`admin_id` = :id";
		$params = array("id" => $user_id);
		$groups = $DB->PreparedSelect($sql, $params, false, false);

		$group_ids   = array();
		$group_names = array();
		foreach ($groups AS $group) {
			$group_id = (int) $group["group_id"];
			$group_ids[$group_id]   = $group_id;
			$group_names[$group_id] = $group["group_name"];
		}


		// Get all direct associations
		$sql  = "SELECT `m`.`client_id`, `c`.`name` \n";
		$sql .= "FROM `client_members` AS `m` \n";
		$sql .= "LEFT JOIN `clients` AS `c` ON (`c`.`id` = `m`.`client_id`) \n";
		$sql .= "WHERE `m`.`admin_id` = :id";
		$params = array("id" => $user_id);
		$clients = $DB->PreparedSelect($sql, $params, false, false);

		$client_ids   = array();
		$client_names = array();
		foreach ($clients AS $client) {
			$client_id = (int) $client["client_id"];
			$client_ids[$client_id]  = $client_id;
			$cient_names[$client_id] = $client["name"];
		}



		// Successfully logged in
		$ADMIN     = true;
		$LOGGED_IN = true;
		$_SESSION["LOGGED_IN"]    = $LOGGED_IN;
		$_SESSION["ISADMIN"]      = $ADMIN;
		$_SESSION["SUPERADMIN"]   = ( isset($group_ids[1]) );
		$_SESSION["ADMINHASH"]    = generateSalt($user_id."---".$user[0]["username"]);
		$_SESSION["userid"]       = $user_id;
		$_SESSION["username"]     = $user[0]["username"];
		$_SESSION["group_ids"]    = $group_ids;
		$_SESSION["group_names"]  = $group_names;
		$_SESSION["client_ids"]   = $client_ids;
		$_SESSION["client_names"] = $client_names;

		break;
	}

	// Bei erfolgreichem Login sofort auf Terminübersicht weiterleiten
	if (isset($LOGGED_IN) && $LOGGED_IN === true) {
		header('Location: overview_clients.php?sort_by=upcoming');
		exit;
	}
}



// Redirection if successful happens here
require ROOT."/tpl/_include.php";
?>
