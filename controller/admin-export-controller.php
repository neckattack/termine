<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * export_controller.php
 * Export a client to a CSV file
 */

/**
 * Get the data for a client
 * @param  $id     int    The ID of the client
 * @return $array  array  Array with the client's infos
 */
function getExportData($id) {
	global $DB;
	
	$sql  = "SELECT `c`.`id`, `c`.`name`, `d`.`date`, `t`.`time_start`, `t`.`time_end`, `r`.`name` AS `res_name`, `r`.`email` ";
	$sql .= "FROM `clients` AS `c` ";
	$sql .= "LEFT JOIN `dates` AS `d` ON (`c`.`id` = `d`.`client_id`) ";
	$sql .= "LEFT JOIN `times` AS `t` ON (`d`.`id` = `t`.`date_id`) ";
	$sql .= "LEFT JOIN `reservations` AS `r` ON (`t`.`id` = `r`.`time_id`) ";
	$sql .= "WHERE `c`.`id` = :id ";
	$sql .= "ORDER BY `c`.`id`, `d`.`date`, `t`.`time_start`";
	
	$params = array("id" => (int)$id);
	$client = $DB->PreparedSelect($sql, $params, false, false);
	
	return $client;
}


/**
 * Format the data for exporting
 * @param  $array  array  The raw array from the database
 * @return $array  array  Formatted array for exporting
 */
function formatExportData($array) {
	global $config;
	
	$return = array();
	$return[] = array($array[0]["name"]); 	// First line
	
	$date = "";
	foreach ($array AS $row) {
		if ($date !== $row["date"]) {
			$date = $row["date"];
			$return[] = array();
			$return[] = array($date);
		}
		$return[] = array(
			$row["time_start"],
			$row["time_end"],
			$row["res_name"],
			$row["email"]
		);
	}
	
	return $return;
}
?>
