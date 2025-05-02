<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
define('SALT_LENGTH', 10);

/**
 * Generate a salt for a password
 * @param  $plainText  string  Password in plain text
 * @param  $encrypted  string  If given, uses the existing salt to create a new hash
 * @return             string  The newly created and encrypted password
 */
function generateSalt($plainText, $encrypted = null) {
	// Only plain text given, create a salt for it
	if ($encrypted === null) {
		$salt = substrSalt(md5(uniqid(rand(), true)));
	}
	// Plain text and encryped given, extract the salt from the encrypted password
	else {
		$salt = substrSalt($encrypted);
	}
	
	// Return the encrypted and salted password
	return $salt . sha1($salt . $plainText);
}

/**
 * Return the salt from a password string
 * @param  string  The password
 * @return string  The salt
 */
function substrSalt($pwd) {
	return substr($pwd, 0, SALT_LENGTH);
}
?>