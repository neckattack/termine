<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * config.php
 * Set configuration parameters for the scripts
 */
 

// Configuration array
$config = array(
	/* Database Config */

	// Database access
	/*
	"database" => "db1066545-skaar001",
	"user"     => "db1066545-sk001",
	"pass"     => "sashimi7842",
	"server"   => "localhost",
	*/

	"database" => "neckatta_termine",
	"user"     => "neckatta_termine",
	"pass"     => "$@uan9)b{tDh",
	"server"   => "localhost",

	
	/* General Config */
	// Set the date format
	"dateFormat"  => array("d", "m", "Y", "."),		// array("d", "m", "Y", "."): Order is Day, Month, Year, using "." as the separator (e.g. 31.12.2011 - German format)
													// array("Y", "m", "d", "-"): Order is Year, Month, Day, using "-" as the separator (e.g. 2011-12-31 - MySQL format)
													// array("m", "d", "Y", "/"): Order is Month, Day, Year, using "/" as the separator (e.g. 12/31/2011 - US format)
													// Change the order and/or seperator to change how the dates will be displayed
	
	// Set the time format
	"timeFormat"  => array("H", "i", "", ":"),		// http://de3.php.net/manual/de/function.date.php
													
	// Timezone
	"timeZone"    => "Europe/Berlin",				// Change to your time zone
													// For a full list see here: http://www.php.net/manual/en/timezones.php

	// Maximum file size for uploaded images
	"maxImageFileSize" => 10000 * 1024,				// 10000 kB

	
	// E-Mail and Telephone Number
	// Used as a fallback for the reservation emails if no masseur contact info is available
	"email" => "info@neckattack.net",
	"phone" => "+49 711/3 58 36 09",

	// PayPal data
	"paypal_email" => "chris@neckattack.net",
	"paypal_currency" => "EUR",
	"paypal_ptd_token" => "---_----_---",

	// Stripe data
	"stripe_email" => "chris@neckattack.net",
	"stripe_currency" => "eur",
	"stripe_public_token" => "pk_live_----",
	"stripe_secret_token" => "sk_live_----",
);


// Set the default time zone
date_default_timezone_set($config["timeZone"]);

// Connect to the database
$DB = Database::getInstance();	// Create the database object
$DB->connect($config["server"], $config["database"], $config["user"], $config["pass"]);
?>