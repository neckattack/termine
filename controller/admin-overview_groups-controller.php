<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * Retrieve all user groups
 * @param  void
 * @return $groups  array  The groups
 */
function getAllGroups() {
	global $DB;
	$sql  = "SELECT `g`.`group_id`, `g`.`group_name`, `g`.`group_info`, COUNT(`m`.`id`) AS `num_members` ";
	$sql .= "FROM `groups` AS `g` ";
	$sql .= "LEFT JOIN `group_members` AS `m` ON (`m`.`group_id` = `g`.`group_id`) ";
	$sql .= "GROUP BY `g`.`group_id` ";
	$sql .= "ORDER BY `g`.`group_id` ";
	
	$groups = $DB->PreparedSelect($sql, array(), false, false);
	
	return $groups;
}


/**
 * Retrieve all user groups for a specific user
 * @param  $userid  int    The user id
 * @return $groups  array  The groups
 */
function getAllGroupsForUser($userid) {
	global $DB;

	$sql  = "SELECT `g`.`group_id`, `g`.`group_name`, `g`.`group_info` \n";
	$sql .= "FROM `group_members` AS `m` \n";
	$sql .= "LEFT JOIN `groups` AS `g` ON (`g`.`group_id` = `m`.`group_id`) \n";
	$sql .= "WHERE `m`.`admin_id` = :id";
	$params = array("id" => (int) $userid);
	
	
	$groups = $DB->PreparedSelect($sql, $params, false, false);
	
	return $groups;
}