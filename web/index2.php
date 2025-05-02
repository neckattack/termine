<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * index.php
 * The landing page for the customer
 */

error_reporting(-1);				// Enable PHP error reporting (set to 0 to disable)
$PAGE = basename(__FILE__);
require "_root_.php";				// Defines the ROOT constant
require ROOT."/inc/_include.php";
require ROOT."/inc/email_text.php";

$timeFormat = array(
	"10.01.2013" => array(
		array(
			"start" => "11:00",
			"end"   => "12:00"
		),
		array(
			"start" => "12:00",
			"end"   => "13:00"
		),
		array(
			"start" => "13:00",
			"end"   => "13:30"
		),
	),

	"12.01.2013" => array(
		array(
			"start" => "11:00",
			"end"   => "12:00"
		),
		array(
			"start" => "12:00",
			"end"   => "13:00"
		),
		array(
			"start" => "13:00",
			"end"   => "13:30"
		),
	)
);

$message_time = "";
foreach ($timeFormat AS $date => $entries) {
	$message_time .= $date."\n";
	foreach ($entries AS $key => $entry) {
		$message_time .= $entry["start"]." - ".$entry["end"]."\n";
	}
	$message_time .= "\n";
}

$variables = array(
	"Username" => "Heinz Kunz",
	"Termine"  => $message_time,
	#"Masseurtelefonnummer" => "0176 20171195",
);


$message = $email_default_text;
$matches = array();

// Variables are contained in <<VAR>>
preg_match_all("#<<(\w+)>>#i", $message, $matches);

// Replace the variables
foreach ($matches[1] AS $key) {
	if (!isset($variables[$key])) continue;

	$pattern = "#<<".$key.">>#i";
	$replace = $variables[$key];
	$message = preg_replace($pattern, $replace, $message);

	echo <<<EOF
<textarea cols="80" rows="20">
$message
</textarea>
EOF;
}



?>
<textarea cols="80" rows="20">
<?php print_r($matches); ?>
</textarea>