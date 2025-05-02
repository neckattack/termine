<?php
/**
 * Set additional constants
 */
if (!defined('PAGE') && isset($PAGE)) {
	// Strip .php and -xxx.php
	define('PAGE', DIRPATH.preg_replace("#\.php|-([a-z0-9]+)#i", "", $PAGE));
}
?>