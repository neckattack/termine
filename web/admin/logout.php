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
require ROOT."/inc/salt.php";		// Salt generation


$P = $_POST;
$logged_in = false;

// Check if logged in
if (isset($P["username"]) && isset($P["password"])) {
	session_unset();
	session_destroy();
	session_start();
	$error = 0;

	while ($error == 0) {
		// Check for salt
		$username = $P["username"];
		$password = $P["password"];

		// Request the encrypted password from the database
		$sql    = "SELECT `id`, `password` FROM `admin` WHERE `username` = :username LIMIT 1";
		$params = array("username" => $username);
		$rows   = $DB->PreparedSelect($sql, $params);

		// No result
		if (!isset($rows[0]["password"])) {
			$error++;
			break;
		}

		// Extract salt
		$pwdDB  = $rows[0]["password"];
		$saltDB = substrSalt($pwdDB);

		if (strlen($saltDB) < 10) {
			$error++;
			break;
		}

		// Generate the hash value
		// SHA1(SALT + SHA1(SALT+Password)) = SHA1(Password)
		$hash  = generateSalt($password, $saltDB);
		$match = ($pwdDB === $hash);


		if ($match !== true) {
			$error++;
			break;
		}


		// Get user data
		$sql    = "SELECT `id`, `username` FROM `admin` WHERE SHA1(`username`) = :user AND SHA1(password) = :hash LIMIT 1";
		$params = array("user" => sha1($username), "hash" => sha1($hash));
		$user   = $DB->PreparedSelect($sql, $params, false, false);

		if (!isset($user[0]["id"]) || $user[0]["id"] < 1) {
			$error++;
			break;
		}


		// Successfully logged in
		define('ADMIN', true);
		$logged_in = true;
		$_SESSION["logged_in"] = $logged_in;
		$_SESSION["username"]  = $user[0]["username"];
		$_SESSION["userid"]    = $user[0]["id"];

		break;
	}
}


// Include the landing pages
if ($logged_in !== true) {
	require ROOT."/tpl/_include.php";
}
else {
	// Hmm...
	require ROOT."/tpl/admin-index-tpl.php";
}
