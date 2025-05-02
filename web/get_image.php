<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * get_image.php
 * Request an image from the database
 */

error_reporting(-1);				// Enable PHP error reporting (set to 0 to disable)
$PAGE = basename(__FILE__);
require "_root_.php";				// Defines the ROOT constant
require ROOT."/inc/_include.php";


$R      = $_REQUEST;
$id     = (isset($R["id"]))   ? $R["id"]   : 0;
$type   = (isset($R["type"])) ? $R["type"] : "";

// Parameters may be base64 encoded
$string = @base64_decode($R["s"]);
$params = @parse_str($string);		// Directly allocates the given variables

$id     = (int) $id;
$image  = getImage($id, $type);

displayImage($image);
?>