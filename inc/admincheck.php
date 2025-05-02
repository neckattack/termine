<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * Check if admin logged in
 */
require ROOT."/inc/salt.php";		// Salt generation

if (!session_id()) {
	session_start();
}
$ADMIN      = false;
$SUPERADMIN = false;

if (isset($_SESSION)) {
	if (@$_SESSION["LOGGED_IN"] === true && @$_SESSION["ISADMIN"] === true) {
		// Extract salt
		$userid     = @$_SESSION["userid"];
		$username   = @$_SESSION["username"];
		$salt       = @substrSalt($_SESSION["ADMINHASH"]);
		$hash       = @generateSalt($userid."---".$username, $salt);
		$match      = @($_SESSION["ADMINHASH"] === $hash);
		$ADMIN      = @$match;
		$SUPERADMIN = @($ADMIN === true && $_SESSION["SUPERADMIN"] === true && isset($_SESSION["group_ids"][1]) && $_SESSION["group_ids"][1] === 1);

		unset($hash);
		unset($salt);
	}
}


if ($ADMIN !== true) {
	// Set to 403 forbidden if not allowed and an AJAX request
	if (isset($AJAX) && $AJAX === true) {
		setHeader(403);
		exit;
	}

	// If no AJAX, send back to the index
	header("Location: index.php");
	exit;
}
?>
