<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */

/**
 * @param  int    $int   The ID of the owner
 * @param  string $type  "client" or "group"
 * @return mixed  $image false or the image
 */
function getImage($id, $type="client") {
	global $DB;
	$id     = (int) $id;
	$image  = false;
	$table  = ($type === "client") ? "clients" : "groups";
	$field  = ($type === "client") ? "id"      : "group_id";
	$sql    = "SELECT `image` FROM `".$table."` WHERE `".$field."` = :id";
	$params = array(
		"id" => $id
	);
	$result = $DB->PreparedSelect($sql, $params);

	if (isset($result[0]["image"][10])) {
		$image = $result[0]["image"];
	}

	return $image;
}



/**
 * Outputs an image
 * If no image is given, reverts to the /images/no-image.png
 * @param mixed $image The image stream
 * @return void
 */
function displayImage($image=false) {
	if ($image === false) {
		$path  = "images/no-image.png";
		$image = file_get_contents($path);
	}

	//$info = getimagesizefromstring($image);
	//$mime = image_type_to_mime_type($info[2]);

	// Display image
	header("Content-type: image/jpeg");
	echo $image;
	exit;
}
?>
