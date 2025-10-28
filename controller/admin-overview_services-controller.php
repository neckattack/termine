<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */

/**
 * Controller: Übersicht GebüH-Services
 */
function getAllGebuehServices() {
    global $DB;
    $sql  = "SELECT `id`,`code`,`title`,`short_desc`,`fee_min`,`fee_mid`,`fee_max`,`is_analog`,`analog_ref`,`active` \n";
    $sql .= "FROM `gebueh_services` \n";
    $sql .= "ORDER BY CAST(code AS UNSIGNED), code"; // 1,5,19,20,...,45a
    return $DB->PreparedSelect($sql, array(), false, false);
}
