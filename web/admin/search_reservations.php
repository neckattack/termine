<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * search_reservations.php
 * Admin-Suche nach Reservierungen über E-Mail
 */
error_reporting(-1);
$PAGE = basename(__FILE__);
require "_root_.php";
require ROOT."/inc/_include.php";
require ROOT."/inc/admincheck.php";
require ROOT."/controller/admin-search_reservations-controller.php";
require ROOT."/controller/index-controller.php"; // für getTimeFormatForEmail falls benötigt

$ADMIN = true; // für header-tpl Anzeige

$Q = $_GET;
$email = isset($Q['email']) ? trim($Q['email']) : '';
$rows = [];
if ($email !== '') {
    $rows = searchReservationsByEmail($email);
}

require ROOT."/tpl/_include.php";
