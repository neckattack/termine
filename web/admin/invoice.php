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
    $sql[] = "SELECT a.first_name, a.last_name, c.id AS client_id, c.name AS client_name, c.default_diagnosis, c.default_service_ids, r.name AS res_name, r.email AS res_email, d.date AS date_ymd";
    $sql[] = "FROM `reservations` r";
    $sql[] = "JOIN `times` t ON t.id = r.time_id";
    $sql[] = "JOIN `dates` d ON d.id = t.date_id";
    $sql[] = "JOIN `clients` c ON c.id = d.client_id";
    $sql[] = "LEFT JOIN `admin` a ON a.id = c.contact_masseur_id";
    $sql[] = "WHERE r.id = :res_id";
    $q = implode("\n", $sql);
    $row = $DB->PreparedSelect($q, [ 'res_id' => $resId ], false, false);
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

// Wenn mPDF vorhanden ist, nutze es für ein HTML-PDF
$mpdfAvailable = false;
if (file_exists(ROOT.'/vendor/autoload.php')) {
    require_once ROOT.'/vendor/autoload.php';
    if (class_exists('Mpdf\\Mpdf')) {
        $mpdfAvailable = true;
    }
}

if ($mpdfAvailable) {
    $clientName    = isset($row['client_name']) ? (string)$row['client_name'] : '';
    $clientId      = isset($row['client_id']) ? (int)$row['client_id'] : 0;
    $patientName   = isset($row['res_name']) ? (string)$row['res_name'] : '';
    $patientEmail  = isset($row['res_email']) ? (string)$row['res_email'] : '';
    $dateYmd       = isset($row['date_ymd']) ? (string)$row['date_ymd'] : '';
    $dateStr       = $dateYmd ? date('d.m.Y', strtotime($dateYmd)) : '';
    $diagnosis     = isset($row['default_diagnosis']) ? (string)$row['default_diagnosis'] : '';
    $serviceIdsCsv = isset($row['default_service_ids']) ? trim((string)$row['default_service_ids']) : '';

    // Services-Liste und Clientpreise laden
    require_once ROOT.'/controller/admin-overview_services-controller.php';
    $svcList = getAllGebuehServices();
    $svcById = [];
    if (is_array($svcList)) {
        foreach ($svcList as $svc) { $svcById[(int)$svc['id']] = $svc; }
    }
    $clientPrices = [];
    if ($clientId > 0) {
        try {
            $rowsCP = $DB->PreparedSelect('SELECT service_id, price_amount FROM client_service_prices WHERE client_id = :cid', ['cid'=>$clientId], false, false);
            if (is_array($rowsCP)) { foreach ($rowsCP as $cp) { $clientPrices[(int)$cp['service_id']] = (float)$cp['price_amount']; } }
        } catch (Exception $e) {}
    }

    // Reservation-spezifische Preise (bevorzugt)
    $items = [];
    try {
        $rowsR = $DB->PreparedSelect('SELECT service_id, price_amount FROM reservation_service_prices WHERE reservation_id = :rid', ['rid'=>$resId], false, false);
        if (is_array($rowsR) && count($rowsR) > 0) {
            foreach ($rowsR as $r) {
                $sid = (int)$r['service_id'];
                if (!isset($svcById[$sid])) continue;
                $svc = $svcById[$sid];
                $items[] = [
                    'date'   => $dateStr,
                    'code'   => (string)$svc['code'],
                    'title'  => (string)$svc['title'],
                    'amount' => (float)$r['price_amount'],
                ];
            }
        }
    } catch (Exception $e) {}

    // Falls keine reservation-spezifischen Preise: aus Default-Services mit Client/Fee ermitteln
    if (count($items) === 0) {
        $ids = [];
        if ($serviceIdsCsv !== '') {
            $ids = array_values(array_filter(array_map('intval', explode(',', $serviceIdsCsv))));
        }
        foreach ($ids as $sid) {
            if (!isset($svcById[$sid])) continue;
            $svc = $svcById[$sid];
            $amount = isset($clientPrices[$sid]) ? (float)$clientPrices[$sid] : (isset($svc['fee_mid'])?(float)$svc['fee_mid']:0.0);
            $items[] = [
                'date'   => $dateStr,
                'code'   => (string)$svc['code'],
                'title'  => (string)$svc['title'],
                'amount' => $amount,
            ];
        }
    }

    // Summe
    $sum = 0.0; foreach ($items as $it) { $sum += (float)$it['amount']; }

    // Schöneres Layout mit Beträgen
    $html = '<html><head><meta charset="utf-8"><style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#111; }
        h1 { text-align:center; font-size: 22px; margin: 0 0 14px; }
        .header { margin-bottom: 12px; }
        .block { margin: 6px 0; }
        .muted { color:#444; }
        .small { font-size: 11px; }
        .grid { display: table; width:100%; table-layout: fixed; }
        .col { display: table-cell; vertical-align: top; }
        .right { text-align:right; }
        table { width:100%; border-collapse: collapse; margin-top:10px; }
        th, td { border:1px solid #bbb; padding:6px 8px; }
        th { background:#f2f2f2; }
        .sum { font-weight:bold; }
    </style></head><body>';

    $html .= '<h1>Rechnung</h1>';

    // Kopf links: Absender/Client, rechts: Datum/Nummer (Dummy-Nummer)
    $html .= '<div class="grid header">'
          . '<div class="col">'
          . '<div class="block"><strong>'.htmlspecialchars($clientName).'</strong></div>'
          . '<div class="block">Masseur: '.htmlspecialchars($masseurName).'</div>'
          . '</div>'
          . '<div class="col right">'
          . '<div class="block">Rechnungsdatum: '.htmlspecialchars(date('d.m.Y')).'</div>'
          . '<div class="block">Rechnungs-Nr.: RES-'.(int)$resId.'-'.date('Ymd').'</div>'
          . '</div>'
          . '</div>';

    // Patient
    $html .= '<div class="block"><strong>Patient:</strong> '
          . htmlspecialchars($patientName)
          . ($patientEmail ? ' &lt;'.htmlspecialchars($patientEmail).'&gt;' : '')
          . '</div>';

    // Termin
    if ($dateStr) {
        $html .= '<div class="block"><strong>Behandlungstermin:</strong> '.htmlspecialchars($dateStr).'</div>';
    }

    // Diagnose
    if ($diagnosis !== '') {
        $html .= '<div class="block"><strong>Diagnose:</strong><br/>'.nl2br(htmlspecialchars($diagnosis)).'</div>';
    }

    // Tabelle: Datum | GebüH-Nr. | Leistung | Betrag (€)
    $html .= '<table><thead><tr>'
          . '<th style="width:18%">Datum</th>'
          . '<th style="width:14%">GebüH-Nr.</th>'
          . '<th>Leistung</th>'
          . '<th style="width:16%" class="right">Betrag (€)</th>'
          . '</tr></thead><tbody>';

    if (count($items) > 0) {
        foreach ($items as $svc) {
            $html .= '<tr>'
                  . '<td>'.htmlspecialchars($svc['date']).'</td>'
                  . '<td>'.htmlspecialchars($svc['code']).'</td>'
                  . '<td>'.htmlspecialchars($svc['title']).'</td>'
                  . '<td class="right">'.number_format((float)$svc['amount'], 2, ',', '.').'</td>'
                  . '</tr>';
        }
        $html .= '<tr class="sum"><td colspan="3" class="right">Gesamtbetrag</td><td class="right">'.number_format($sum, 2, ',', '.').'</td></tr>';
    } else {
        $html .= '<tr><td colspan="4" class="small muted">Keine Leistungen hinterlegt.</td></tr>';
    }
    $html .= '</tbody></table>';

    // Zahlungs-Hinweis
    $html .= '<div class="block" style="margin-top:10px;"><em>Der Betrag wurde bereits entrichtet.</em></div>';

    $html .= '</body></html>';

    $mpdf = new \Mpdf\Mpdf();
    $mpdf->WriteHTML($html);
    $mpdf->Output('Rechnung_'.$resId.'.pdf', 'I');
    exit;
}

// Einfaches PDF ohne externe Abhängigkeiten erzeugen (Fallback)
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
