<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * Retrieve all users
 * @param  void
 * @return $users  array  The users
 */
function getAllUsers() {
	global $DB;
	// Get all users
	$sql  = "SELECT `a`.`id`, `a`.`username`, `a`.`email`, `a`.`gender`, `a`.`first_name`, `a`.`last_name` \n";
	$sql .= "FROM `admin` AS `a` \n";
	$sql .= "ORDER BY `a`.`last_name` ASC, `a`.`first_name` ASC, `a`.`username` ASC";
	$users = $DB->PreparedSelect($sql);


	// Process the user list and add a "name" field which contains last_name, first_name [username]
	foreach ($users AS $key => $entry) {
		$users[$key]["name"] = "";

		if (isset($entry["first_name"][1]) || isset($entry["last_name"][1])) {
			$users[$key]["name"] = $entry["last_name"] .", ". $entry["first_name"]. " ";
		}
		$users[$key]["name"] .= "[".$entry["username"]."]";
	}

	return $users;
}
