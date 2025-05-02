<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * Handle image save / delete
 */

/**
 * Save an image
 * @param  array  $FILES  The $_FILES array
 * @param  int    $id     The id of the client/group
 * @param  string $type   "client" or "group"
 * @return array  Returned messages. "success" => true if successful
 */
function saveImage($FILES, $id, $type="client") {
	global $DB;
	global $config;
	
	$IMAGE = $FILES["upload_image"];
	$id    = (int) $id;

	// Possible error messages
	$errorMessages = array(
		1 => 'Max. Dateigröße überschritten (php.ini)',
		2 => "Max. Dateigröße überschritten",
		3 => "Dateiupload nicht vollständig",
		4 => "Es wurde keine Datei ausgewählt",
		"no-image1" => "Die ausgewählte Datei ist keine Bilddatei",
		"no-image2" => "Die ausgewählte Datei ist keine Bilddatei",
		"filesize"  => "Max. Dateigröße überschritten (max ".($config["maxImageFileSize"]/1024)." kB)",
		"database"  => "Konnte Bild nicht in die Datenbank einfügen",
	);

	// Extensions
	$extensions = array_flip(array(
		"jpg", "jpeg", "gif", "png"
	));

	// MIME types
	$types = array_flip(array(
		"image/jpeg", "image/pjpeg", "image/gif", "image/png", "image/x-png"
	));
	
	// Uploading error
	if ($IMAGE["error"] > 0) {
		return array(
			"success" => false,
			"error"   => $IMAGE["error"],
			"message" => $errorMessages[$IMAGE["error"]],
		);
	}

	// Image types
	$filecheck = basename($IMAGE["name"]);
	$ext = strtolower(substr($filecheck, strrpos($filecheck, '.') + 1));

	if (!isset($extensions[$ext]) || !isset($types[$IMAGE["type"]])) {
		return array(
			"success" => false,
			"error"   => "no-image1",
			"message" => $errorMessages["no-image1"],
			"data"    => $ext,
			"type"    => $IMAGE["type"],
		);
	}

	// Check with the GD library as well
	$imageData = @getimagesize($IMAGE["tmp_name"]);

    if ($imageData === false || !($imageData[2] == IMAGETYPE_GIF || $imageData[2] == IMAGETYPE_JPEG || $imageData[2] == IMAGETYPE_PNG)) {
		return array(
			"success" => false,
			"error"   => "no-image2",
			"message" => $errorMessages["no-image2"],
		);
    }

	// File too big
   //echo $config["maxImageFileSize"];
   //echo $IMAGE["size"];
   //exit;
	if ($IMAGE["size"] > $config["maxImageFileSize"]) {
		return array(
			"success" => false,
			"error"   => "filesize",
			"message" => $errorMessages["filesize"],
		);
	}


	// No error? Upload image to database
	$imageStream = file_get_contents($IMAGE["tmp_name"]);
	$table  = ($type === "client") ? "clients" : "groups";
	$field  = ($type === "client") ? "id"      : "group_id";
	$sql    = "UPDATE `".$table."` SET `image` = :image WHERE `".$field."` = :id";
	$params = array(
		"image" => $imageStream,
		"id"    => $id
	);
	$result = $DB->PreparedStatement($sql, $params, false, false);


	if (!isset($result) || $result === false) {
		return array(
			"success" => false,
			"error"   => "database",
			"message" => $errorMessages["database"],
			"data"    => $result,
		);
	}


	// Success
	// 1 for updated image, 0 for same image uploaded
	return array(
		"success" => true
	);
}


/**
 * Delete an image
 * @param  int    $id    The id of the client/group
 * @param  string $type  "client" or "group"
 * @return 
 */
function deleteImage($id, $type="client") {
	global $DB;

	$table  = ($type === "client") ? "clients" : "groups";
	$sql    = "UPDATE `".$table."` SET `image` = :image WHERE `id` = :id";
	$params = array(
		"image" => "",
		"id"    => $id
	);
	$result = $DB->PreparedStatement($sql, $params, false, false);


	if (!isset($result) || $result === false) {
		return array(
			"success" => false,
			"error"   => "database",
			"message" => "Fehler beim Löschen des Bildes",
			"data"    => $result,
		);
	}


	// Success
	return array(
		"success" => true
	);
}
?>