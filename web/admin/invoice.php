<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * invoice.php
 * Dummy PDF für eine Reservierung. Zeigt nur den Namen des Masseurs (aus Clients.contact_masseur_id),
 * ohne externe Bibliothek. Später ersetzen wir dies durch mPDF.
 */

error_reporting(-1);
$PAGE = basename(__FILE__);
require "_root_.php";            // Defines the ROOT constant
require ROOT."/inc/_include.php";
require ROOT."/inc/admincheck.php"; // Check if admin logged in

$R = $_REQUEST;
$resId = isset($R['res_id']) ? (int)$R['res_id'] : 0;
if ($resId <= 0) {
    header('HTTP/1.1 400 Bad Request');
    echo 'res_id fehlt';
    exit;
}

// Masseur-Name über: reservations -> times -> dates -> clients.contact_masseur_id -> admin
try {
    $sql = [];
    $sql[] = "SELECT a.first_name, a.last_name, c.name AS client_name";
    $sql[] = "FROM `reservations` r";
    $sql[] = "JOIN `times` t ON t.id = r.time_id";
    $sql[] = "JOIN `dates` d ON d.id = t.date_id";
    $sql[] = "JOIN `clients` c ON c.id = d.client_id";
    $sql[] = "LEFT JOIN `admin` a ON a.id = c.contact_masseur_id";
    $sql[] = "WHERE r.id = :res_id";
    $q = implode("\n", $sql);
    $row = $DB->PreparedSelect($q, [ 'res_id' => $resId ], true, false);
} catch (Exception $e) {
    $row = false;
}

$masseurName = 'Unbekannter Masseur';
if (is_array($row)) {
    $fn = isset($row['first_name']) ? trim((string)$row['first_name']) : '';
    $ln = isset($row['last_name']) ? trim((string)$row['last_name']) : '';
    $full = trim($fn.' '.$ln);
    if ($full !== '') {
        $masseurName = $full;
    } else {
        // Fallback: Client-Name ausgeben
        $masseurName = isset($row['client_name']) ? (string)$row['client_name'] : $masseurName;
    }
}

// Einfaches PDF ohne externe Abhängigkeiten erzeugen
// Eine Seite, Helvetica, zwei Zeilen Text
function buildSimplePdf($line1, $line2) {
    $pdf = "%PDF-1.4\n";
    $objects = [];

    // 1: Catalog
    $objects[1] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
    // 2: Pages
    $objects[2] = "2 0 obj\n<< /Type /Pages /Count 1 /Kids [3 0 R] >>\nendobj\n";
    // 5: Font
    $objects[5] = "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
    // 4: Content stream
    // Position: (72,770) für Titel, dann 40pt nach unten
    $stream = "BT /F1 24 Tf 72 770 Td (".pdfEscape($line1).") Tj 0 -40 Td (".pdfEscape($line2).") Tj ET";
    $objects[4] = "4 0 obj\n<< /Length ".strlen($stream)." >>\nstream\n".$stream."\nendstream\nendobj\n";
    // 3: Page
    $objects[3] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>\nendobj\n";

    // Reihenfolge
    $order = [1,2,3,4,5];

    // Offsets berechnen
    $offsets = [];
    $offsets[0] = 0; // free object
    $pos = strlen($pdf);
    foreach ($order as $objNum) {
        $offsets[$objNum] = $pos;
        $pdf .= $objects[$objNum];
        $pos = strlen($pdf);
    }

    // xref
    $xrefPos = strlen($pdf);
    $pdf .= "xref\n";
    $pdf .= "0 ".(count($order)+1)."\n";
    // free object
    $pdf .= sprintf("%010d %05d f \n", 0, 65535);
    foreach ($order as $objNum) {
        $pdf .= sprintf("%010d %05d n \n", $offsets[$objNum], 0);
    }

    // trailer
    $pdf .= "trailer\n";
    $pdf .= "<< /Size ".(count($order)+1)." /Root 1 0 R >>\n";
    $pdf .= "startxref\n".$xrefPos."\n%%EOF";

    return $pdf;
}

function pdfEscape($text) {
    // Klammern und Backslashes maskieren
    return str_replace(["\\", "(", ")"], ["\\\\", "\\(", "\\)"], $text);
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="Rechnung_'.$resId.'.pdf"');
$line1 = 'Rechnung (Dummy)';
$line2 = 'Masseur: '.$masseurName;

echo buildSimplePdf($line1, $line2);
exit;
?>
