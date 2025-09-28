<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
// Simple ICS generator for a given time slot (t.id)
// Usage: /web/ics.php?tid=123

error_reporting(0);
$PAGE = basename(__FILE__);
require "_root_.php";               // Defines the ROOT constant
require ROOT."/inc/_include.php";   // DB + helpers + config

header('Expires: 0');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');

$tid = isset($_GET['t']) ? (int)$_GET['t'] : (isset($_GET['tid']) ? (int)$_GET['tid'] : 0);
if ($tid <= 0) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Missing or invalid parameter: tid";
    exit;
}

// Fetch slot details
$sql  = "SELECT t.id AS time_id, d.date, t.time_start, t.time_end, c.name AS client_name, c.hashlink AS client_hash "
       ."FROM times t "
       ."JOIN dates d ON t.date_id = d.id "
       ."JOIN clients c ON d.client_id = c.id "
       ."WHERE t.id = :tid LIMIT 1";
$row = $DB->PreparedSelect($sql, [ 'tid' => $tid ], false, false);
if (!$row || !isset($row[0]['time_id'])) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Time slot not found";
    exit;
}
$r = $row[0];

// Build start/end in Europe/Berlin (fallback UTC)
$tzid   = 'Europe/Berlin';
try { $tz = new DateTimeZone($tzid); } catch (Exception $e) { $tz = new DateTimeZone('UTC'); $tzid = 'UTC'; }
$start  = new DateTime($r['date'].' '.$r['time_start'], $tz);
$end    = new DateTime($r['date'].' '.$r['time_end'], $tz);

$uid    = 'na-time-'.$r['time_id'].'@neckattack.net';
$stamp  = (new DateTime('now', new DateTimeZone('UTC')))->format('Ymd\THis\Z');
$dtStartLocal = $start->format('Ymd\THis');
$dtEndLocal   = $end->format('Ymd\THis');

// Event meta
$summary = 'neckAttack Massage - '.($r['client_name'] ?? 'Appointment');
$description = 'Booking via neckAttack';
$location = $r['client_name'] ?? '';
$filename = 'neckattack-'.$r['client_hash'].'-'.$r['date'].'-'.$r['time_id'].'.ics';

// Output ICS
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="'.$filename.'"');

// Minimal VCALENDAR with VTIMEZONE for Europe/Berlin
$ics = "BEGIN:VCALENDAR\r\n".
       "PRODID:-//neckAttack//Appointments//DE\r\n".
       "VERSION:2.0\r\n".
       "CALSCALE:GREGORIAN\r\n".
       "METHOD:PUBLISH\r\n".
       "BEGIN:VTIMEZONE\r\n".
       "TZID:$tzid\r\n".
       "END:VTIMEZONE\r\n".
       "BEGIN:VEVENT\r\n".
       "UID:$uid\r\n".
       "DTSTAMP:$stamp\r\n".
       "DTSTART;TZID=$tzid:$dtStartLocal\r\n".
       "DTEND;TZID=$tzid:$dtEndLocal\r\n".
       "SUMMARY:".escapeIcs($summary)."\r\n".
       ($location !== '' ? ("LOCATION:".escapeIcs($location)."\r\n") : '') .
       "DESCRIPTION:".escapeIcs($description)."\r\n".
       "END:VEVENT\r\n".
       "END:VCALENDAR\r\n";

echo $ics;

function escapeIcs($s) {
    // Escape commas, semicolons, and backslashes; fold long lines at 75 octets
    $s = str_replace("\\", "\\\\", $s);
    $s = str_replace("\n", "\\n", $s);
    $s = str_replace(",", "\\,", $s);
    $s = str_replace(";", "\\;", $s);
    return $s;
}
