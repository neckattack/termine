<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */

/**
 * Get infos for a user
 * @param  $id     int    ID for the user
 * @return $group  array  User information
 */
function getUserInfos($id) {
	global $DB;
	global $config;
	
	$id     = (int) $id;
	$params = array("id" => $id);

	$sql  = "SELECT `a`.`id`, `a`.`username`, `a`.`email`, `a`.`gender`, `a`.`first_name`, `a`.`last_name`, `a`.`phone`, `a`.`address`, `a`.`street`, `a`.`house_no`, `a`.`zip`, `a`.`city`, `a`.`tax_number`, `a`.`iban`, `a`.`vat_exempt_reason`, `a`.`profession`, `a`.`diagnosis` \n";
	$sql .= "FROM `admin` AS `a` \n";
	$sql .= "WHERE `a`.`id` = :id";
	
	$group  = $DB->PreparedSelect($sql, $params, false, false);
	
	if (!isset($group[0]["id"])) {
		return false;
	}


	// Memberships
	$group = $group[0];
	$group["memberships"] = array(
		"groups"   => array(),
		"clients"  => array(),
		"contacts" => array(),
	);
	
	
	// Get all group memberships
	$sql  = "SELECT `g`.`group_id`, `g`.`group_name`, `g`.`group_info` \n";
	$sql .= "FROM `group_members` AS `m` \n";
	$sql .= "LEFT JOIN `groups` AS `g` ON (`g`.`group_id` = `m`.`group_id`) \n";
	$sql .= "WHERE `m`.`admin_id` = :id \n";
	$sql .= "ORDER BY `g`.`group_name` ASC";
	$memberships = $DB->PreparedSelect($sql, $params, false, false);
	
	foreach ($memberships AS $membership) {
		$group["memberships"]["groups"][] = array(
			"group_id"   => $membership["group_id"],
			"group_name" => $membership["group_name"],
			"group_info" => $membership["group_info"],
		);
	}


	// Get all client memberships
	$sql  = "SELECT `c`.`id`, `c`.`name` \n";
	$sql .= "FROM `client_members` AS `m` \n";
	$sql .= "LEFT JOIN `clients` AS `c` ON (`c`.`id` = `m`.`client_id`) \n";
	$sql .= "WHERE `m`.`admin_id` = :id \n";
	$sql .= "ORDER BY `c`.`name` ASC";
	$memberships = $DB->PreparedSelect($sql, $params, false, false);
	
	foreach ($memberships AS $membership) {
		$group["memberships"]["clients"][] = array(
			"client_id"   => $membership["id"],
			"client_name" => $membership["name"],
		);
	}

	// Get all contacts
	$sql  = "SELECT `c`.`id`, `c`.`name` \n";
	$sql .= "FROM `clients` AS `c`  \n";
	$sql .= "WHERE `c`.`contact_masseur_id` = :id OR `c`.`contact_client_id` = :id \n";
	$sql .= "ORDER BY `c`.`name` ASC";
	$memberships = $DB->PreparedSelect($sql, $params, false, false);
	
	foreach ($memberships AS $membership) {
		$group["memberships"]["contacts"][] = array(
			"client_id"   => $membership["id"],
			"client_name" => $membership["name"],
		);
	}


	return $group;
}



/**
 * Edit a user
 * @param  $data     array  Array with the user data
 * @return $success  mixed  ID of the changed user or FALSE on error
 */
function editUser($data) {
	global $DB;
	global $config;
	require_once ROOT."/inc/salt.php";
	
	$id         = (int) $data["id"];
	$name       = $data["user_username"];
	$email      = $data["user_email"];
	$phone      = $data["user_phone"];
	$gender     = (int) $data["user_gender"];
	$first_name = $data["user_first_name"];
	$last_name  = $data["user_last_name"];
	    // Neue optionale Felder der rechten Spalte
    $address    = isset($data['user_address']) ? $data['user_address'] : null;
    $street     = isset($data['user_street']) ? $data['user_street'] : null;
    $house_no   = isset($data['user_house_no']) ? $data['user_house_no'] : null;
    $zip        = isset($data['user_zip']) ? $data['user_zip'] : null;
    $city       = isset($data['user_city']) ? $data['user_city'] : null;
	$tax_number = isset($data['user_tax_number']) ? $data['user_tax_number'] : null;
	$iban       = isset($data['user_iban']) ? $data['user_iban'] : null;
	$vat_exempt_reason = isset($data['user_vat_exempt_reason']) ? $data['user_vat_exempt_reason'] : 'none';
	$allowed_vat = array('§19 UStG','§4 Nr.14 UStG','none');
	if (!in_array($vat_exempt_reason, $allowed_vat, true)) { $vat_exempt_reason = 'none'; }
	$profession = isset($data['user_profession']) ? $data['user_profession'] : null;
	$diagnosis  = isset($data['user_diagnosis']) ? $data['user_diagnosis'] : null;
	$pwd1       = (isset($data["user_password1"])) ? $data["user_password1"] : null;
	$pwd2       = (isset($data["user_password2"])) ? $data["user_password2"] : null;
	$hash       = null;

	// Remove invalid characters from the user name
	$pattern = "#[^a-z0-9äÄöÖüÜßáÁàÀâÂéÉèÈêÊóÓòÒôÔúÚùÙûÛ\._\-]#i";
	$name    = strtolower(preg_replace($pattern, "", $name));

	    // Falls keine Freitext-Adresse übergeben wurde, aus strukturierten Feldern zusammensetzen (Rückwärtskompatibilität)
    if ((is_null($address) || $address === '') && (!is_null($street) || !is_null($house_no) || !is_null($zip) || !is_null($city))) {
        $parts = array();
        if (!empty($street))   { $parts[] = $street; }
        if (!empty($house_no)) { $parts[] = $house_no; }
        if (!empty($zip))      { $parts[] = $zip; }
        if (!empty($city))     { $parts[] = $city; }
        $address = trim(implode(' ', $parts));
    }

    $params = array(
        "name"       => $name,
        "email"      => $email,
        "first_name" => $first_name,
        "last_name"  => $last_name,
        "gender"     => $gender,
        "phone"      => $phone,
        "address"    => $address,
        "street"     => $street,
        "house_no"   => $house_no,
        "zip"        => $zip,
        "city"       => $city,
        "tax_number" => $tax_number,
        "iban"       => $iban,
        "vat_exempt_reason" => $vat_exempt_reason,
        "profession" => $profession,
        "diagnosis"  => $diagnosis,
    );
	

	// Edit a user or Add a user?
	switch ($id) {
		// Add
		case 0:
			$sql  = "INSERT INTO `admin` (`username`, `email`, `first_name`, `last_name`, `gender`, `phone`, `address`, `street`, `house_no`, `zip`, `city`, `tax_number`, `iban`, `vat_exempt_reason`, `profession`, `diagnosis`, `created_by`, `created_at`) \n";
			$sql .= "VALUES (:name, :email, :first_name, :last_name, :gender, :phone, :address, :street, :house_no, :zip, :city, :tax_number, :iban, :vat_exempt_reason, :profession, :diagnosis, :userid, NOW())";
			$params["userid"] = $_SESSION["userid"];
		break;
		
		// Edit
		default:
			$sql  = "UPDATE `admin` \n";
			$sql .= "SET `username` = :name, `email` = :email, `first_name` = :first_name, `last_name` = :last_name, `gender` = :gender, `phone` = :phone, `address` = :address, `street` = :street, `house_no` = :house_no, `zip` = :zip, `city` = :city, `tax_number` = :tax_number, `iban` = :iban, `vat_exempt_reason` = :vat_exempt_reason, `profession` = :profession, `diagnosis` = :diagnosis \n";
			$sql .= "WHERE `id` = :id";
			$params["id"] = $id;
		break;
	}
	
	// Insert into / update the database
	$result = $DB->PreparedStatement($sql, $params, false, false);
	
	// Error
	if ($result === false || is_null($result)) {
		return false;
	}
	
	// Set the id if new entry
	$id = ($id > 0) ? $id : $DB->lastInsertId();


	// Passwords given?
	if (!is_null($pwd1) && !is_null($pwd2) && ($pwd1 === $pwd2)) {
		$hash = generateSalt($pwd1);

		// Update Database
		$sql = "UPDATE `admin` SET `password` = :hash WHERE `id` = :id";
		$params = array(
			"hash" => $hash,
			"id"   => $id
		);
		$result = $DB->PreparedStatement($sql, $params, false, false);

		// Error
		if ($result === false || is_null($result)) {
			return false;
		}
	}
	
	
	// Return the id of added/edited entry on success
	return $id;
}



/**
 * Delete a user
 * @param  int $id      The ID of the user
 * @return int $success Number of deleted rows (n on success, 0 on failure)
 */
function deleteUser($id) {
	global $DB;
	$id = (int) $id;
	
	$sql  = "DELETE `admin`.*, `group_members`.*, `client_members`.*  \n";
	$sql .= "FROM `admin` \n";
	$sql .= "LEFT JOIN `group_members` ON (`group_members`.`admin_id` = `admin`.`id`) \n";
	$sql .= "LEFT JOIN `client_members` ON (`client_members`.`admin_id` = `admin`.`id`) \n";
	$sql .= "WHERE `admin`.`id` = :id ";
	$params = array("id" => $id);
	
	$delete = $DB->PreparedStatement($sql, $params, false, false);
	
	return $delete;
}



/**
 * Remove a user from a group
 * @param  int  $group   The group id
 * @param  int  $user    The user id
 * @return int  $delete  Number of deleted rows (n on success, 0 on failure)
 */
function removeUserFromGroup($group, $user) {
	global $DB;
	$user  = (int) $user;
	$group = (int) $group;
	
	// Safety check: User 1 cannot delete himself from group 1
	if ($user === 1 && $group === 1) {
		return -1;
	}


	$sql    = "DELETE FROM `group_members` WHERE `admin_id` = :user AND `group_id` = :group";
	$params = array("user" => $user, "group" => $group);
	$delete = $DB->PreparedStatement($sql, $params, false, false);
	
	return $delete;
}



/**
 * Add a user to a group
 * @param  int  $group The group id
 * @param  int  $user  The user id
 * @return int  $add   Number of added rows (n on success, 0 on failure)
 */
function addUserToGroup($group, $user) {
	global $DB;
	$user  = (int) $user;
	$group = (int) $group;
	

	$sql    = "INSERT INTO `group_members` (`group_id`, `admin_id`) VALUES (:group, :user)";
	$params = array("user" => $user, "group" => $group);
	$add    = $DB->PreparedStatement($sql, $params, false, false);
	
	return $add;
}



/**
 * Remove a user from a client
 * @param  int  $client  The client id
 * @param  int  $user    The user id
 * @return int  $delete  Number of deleted rows (n on success, 0 on failure)
 */
function removeUserFromClient($client, $user) {
	global $DB;
	$user   = (int) $user;
	$client = (int) $client;
	
	$sql    = "DELETE FROM `client_members` WHERE `admin_id` = :user AND `client_id` = :client";
	$params = array("user" => $user, "client" => $client);
	$delete = $DB->PreparedStatement($sql, $params, false, false, false);
	
	return $delete;
}



/**
 * Add a user to a client
 * @param  int  $client The client id
 * @param  int  $user   The user id
 * @return int  $add    Number of added rows (n on success, 0 on failure)
 */
function addUserToClient($client, $user) {
	global $DB;
	$user   = (int) $user;
	$client = (int) $client;
	
	$sql    = "INSERT INTO `client_members` (`client_id`, `admin_id`) VALUES (:client, :user)";
	$params = array("user" => $user, "client" => $client);
	$add    = $DB->PreparedStatement($sql, $params, false, false);
	
	return $add;
}
?>