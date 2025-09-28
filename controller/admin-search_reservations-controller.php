<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * admin-search_reservations-controller.php
 * Suche nach Reservierungen anhand der E-Mail-Adresse
 */

function searchReservationsByEmail($email)
{
    global $DB;
    $email = trim(strtolower($email));
    if ($email === '') { return array(); }

    $sql = "SELECT 
                r.id              AS reservation_id,
                r.name            AS booker_name,
                r.email           AS booker_email,
                t.id              AS time_id,
                TIME_FORMAT(t.time_start, '%H:%i') AS time_start,
                TIME_FORMAT(t.time_end,   '%H:%i') AS time_end,
                d.id              AS date_id,
                d.date            AS date,
                c.id              AS client_id,
                c.name            AS client_name,
                c.hashlink        AS client_hashlink
            FROM reservations r
            JOIN times t  ON r.time_id = t.id
            JOIN dates d  ON t.date_id = d.id
            JOIN clients c ON d.client_id = c.id
            WHERE LOWER(r.email) = LOWER(:email)
            ORDER BY d.date DESC, t.time_start DESC";

    $rows = $DB->PreparedSelect($sql, array('email' => $email), false, false);
    return is_array($rows) ? $rows : array();
}
