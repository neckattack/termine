<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * export.php
 * Functions to send a file to the browser
 */

/**
 * exportAsCSV()
 * Exports an array as a CSV file
 * @param array  $array     The array to export
 * @param array  $headlines (optional) Array with headlines
 * @param string $delimiter (optional) The delimiter for the CSV file
 * @return void  Directly sends the CSV file to the browser to download
 */
function exportAsCSV($array, $headlines=false, $delimiter=";") {
	ob_start();		// Start the output buffer to catch any output

	// File name
	$fName = "export.".str_replace(".", "", microtime(true)).".csv";

	// Export from UTF8 to Windows-1252
    ini_set('default_charset', 'Windows-1252');

	// Directly send as CSV to the browser
	function utf8($s)     {return utf8_decode($s);}										// String decode from UTF-8 to ASCII
	function utf16le($s)  {return mb_convert_encoding($s, "UTF-16LE", "UTF-8");}		// String decode from UTF-8 to UTF-16LE
	function windows1($s) {return mb_convert_encoding($s, "Windows-1252", "UTF-8");}	// String decode from UTF-8 to CP1252 (Windows-1252)
	function windows2($s) {return mb_convert_encoding($s, "CP1252", "UTF-8");}			// String decode from UTF-8 to CP1252 (Windows-1252)

	$output = fopen('php://output', 'w');		// Use the PHP output buffer as the "file" to write to


	// Prevent a possible error message in Excel
	// It occurs if the first column in the first line contains an "ID" value
	// See: http://support.microsoft.com/?scid=kb%3Ben-us%3B323626&x=19&y=5
	if (is_array($headlines) && count($headlines) > 0 && (trim($headlines[0][0]) === "I" && trim($headlines[0][1]) === "D")) {
		$headlines[0] = strtolower($headlines[0]);	// Simplay display it as lowercase instead
	}


	// Parse the headlines
	if ($headlines) {
		#$headlines = array_map("utf8",    $headlines);		// UTF8 -> ASCII
		#$headlines = array_map("utf16le", $headlines);		// UTF8 -> UTF16LE
		#$headlines = array_map("windows2", $headlines);	// UTF8 -> Windows 1252
		$headlines = array_map("windows1", $headlines);		// UTF8 -> Windows 1252 -- this is the correct one

		// fputcsv ( resource $handle  , array $fields  [, string $delimiter  [, string $enclosure ]] )
		fputcsv($output, $headlines, $delimiter);
	}


	// Parse the content
	foreach ($array AS $line) {
		$line = array_map("windows1", $line);	// UTF8 -> Windows 1252
		fputcsv($output, $line, $delimiter);
	}


	// Set the headers to force a file download
	// Since we're using the output buffer, we can set the headers anywhere we want
	header("Content-Type: application/csv-tab-delimited-table; charset=Windows-1252");		// Output as Windows 1252
	header("Content-Transfer-Encoding: text");
	header("Cache-Control: must-revalidate, post-check=1, pre-check=0");
	header("Pragma: no-cache");
	header("Expires: 0");
	header("Content-Type: application/force-download");
	header("Content-Type: application/octet-stream");
	header("Content-Type: application/download");
	header("Content-type: application/csv");
	header("Content-Description: File Transfer");
	header("Content-Disposition: attachment; filename=\"" . urlencode($fName)."\";");

	$contLen = ob_get_length();		// Calculate the length of the content
	header("Content-Length: ".$contLen);

	fclose($output);				// Close the "file" (i.e. end writing to the PHP output buffer)

	ob_end_flush();					// Flush the output buffer and send the data to the browser
}
?>
