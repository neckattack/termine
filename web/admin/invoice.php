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

// (Fallback auf contact_masseur_id wird weiter unten nach $sender-Initialisierung ausgeführt)

// Platzhalter; wird nach Datenfetch neu berechnet
$therapeutBlockHtml = '';
$therapeutPanelHtml = '';


// Masseur (Absender) über: reservations -> times -> dates -> admin (d.masseur_id)
try {
    $sql = [];
    $sql[] = "SELECT ";
    $sql[] = "  COALESCE(a_date.id, a_client.id) AS masseur_id, ";
    $sql[] = "  COALESCE(a_date.first_name, a_client.first_name) AS admin_first_name, ";
    $sql[] = "  COALESCE(a_date.last_name, a_client.last_name) AS admin_last_name, ";
    $sql[] = "  COALESCE(a_date.email, a_client.email) AS admin_email, ";
    $sql[] = "  COALESCE(a_date.phone, a_client.phone) AS admin_phone, ";
    $sql[] = "  COALESCE(a_date.address, a_client.address) AS admin_address, ";
    $sql[] = "  COALESCE(a_date.street, a_client.street) AS admin_street, ";
    $sql[] = "  COALESCE(a_date.house_no, a_client.house_no) AS admin_house_no, ";
    $sql[] = "  COALESCE(a_date.zip, a_client.zip) AS admin_zip, ";
    $sql[] = "  COALESCE(a_date.city, a_client.city) AS admin_city, ";
    $sql[] = "  COALESCE(a_date.profession, a_client.profession) AS admin_profession, ";
    $sql[] = "  COALESCE(a_date.tax_number, a_client.tax_number) AS admin_tax_number, ";
    $sql[] = "  COALESCE(a_date.iban, a_client.iban) AS admin_iban, ";
    $sql[] = "  COALESCE(a_date.vat_exempt_reason, a_client.vat_exempt_reason) AS admin_vat_exempt_reason, ";
    $sql[] = "  c.id AS client_id, c.name AS client_name, c.default_diagnosis, c.default_service_ids, r.name AS res_name, r.email AS res_email, d.date AS date_ymd";
    $sql[] = "FROM `reservations` r";
    $sql[] = "JOIN `times` t ON t.id = r.time_id";
    $sql[] = "JOIN `dates` d ON d.id = t.date_id";
    $sql[] = "JOIN `clients` c ON c.id = d.client_id";
    $sql[] = "LEFT JOIN `admin` a_date ON a_date.id = d.masseur_id";
    $sql[] = "LEFT JOIN `admin` a_client ON a_client.id = c.contact_masseur_id";
    $sql[] = "WHERE r.id = :res_id";
    $q = implode("\n", $sql);
    $row = $DB->PreparedSelect($q, [ 'res_id' => $resId ], false, false);
    if (is_array($row) && isset($row[0]) && is_array($row[0])) { $row = $row[0]; }
} catch (Exception $e) {
    $row = false;
}

$masseurName = 'Unbekannter Masseur';
$sender = [ 'name'=>'', 'email'=>'', 'phone'=>'', 'address'=>'', 'profession'=>'', 'tax_number'=>'', 'vat_exempt_reason'=>'', 'street'=>'', 'house_no'=>'', 'zip'=>'', 'city'=>'' ];
if (is_array($row)) {
    $fn = isset($row['admin_first_name']) ? trim((string)$row['admin_first_name']) : '';
    $ln = isset($row['admin_last_name']) ? trim((string)$row['admin_last_name']) : '';
    $full = trim($fn.' '.$ln);
    if ($full !== '') { $masseurName = $full; }
    // kein weiterer Username-Fallback nötig – Name kommt von a_date oder a_client
    $sender['name'] = $masseurName;
    $sender['email'] = isset($row['admin_email']) ? (string)$row['admin_email'] : '';
    $sender['phone'] = isset($row['admin_phone']) ? (string)$row['admin_phone'] : '';
    // Structured address preferred; fallback to legacy address
    $sender['street'] = isset($row['admin_street']) ? (string)$row['admin_street'] : '';
    $sender['house_no'] = isset($row['admin_house_no']) ? (string)$row['admin_house_no'] : '';
    $sender['zip'] = isset($row['admin_zip']) ? (string)$row['admin_zip'] : '';
    $sender['city'] = isset($row['admin_city']) ? (string)$row['admin_city'] : '';
    $sender['address'] = isset($row['admin_address']) ? (string)$row['admin_address'] : '';
    $sender['profession'] = isset($row['admin_profession']) ? (string)$row['admin_profession'] : '';
    $sender['iban']       = isset($row['admin_iban']) ? (string)$row['admin_iban'] : '';
    // Zusätzlicher Name-Fallback: falls kein Name vorhanden, nutze Kunden-Ansprechpartner (clients.contact_client_id)
    if (($sender['name'] === '' || $sender['name'] === 'Unbekannter Masseur') && isset($row['client_id']) && (int)$row['client_id'] > 0) {
        try {
            $cf = $DB->PreparedSelect('SELECT a.first_name, a.last_name, a.username, a.email, a.phone, a.address, a.street, a.house_no, a.zip, a.city, a.profession, a.tax_number, a.vat_exempt_reason FROM clients c LEFT JOIN admin a ON a.id = c.contact_client_id WHERE c.id = :cid LIMIT 1', ['cid'=>(int)$row['client_id']], false, false);
            if (is_array($cf) && isset($cf[0]) && is_array($cf[0])) { $cf = $cf[0]; }
            if (is_array($cf)) {
                $n1 = isset($cf['first_name']) ? trim((string)$cf['first_name']) : '';
                $n2 = isset($cf['last_name']) ? trim((string)$cf['last_name']) : '';
                $fullAlt = trim($n1.' '.$n2);
                $sender['name'] = ($fullAlt !== '') ? $fullAlt : (isset($cf['username']) ? (string)$cf['username'] : $sender['name']);
                $sender['email'] = isset($cf['email']) ? (string)$cf['email'] : $sender['email'];
                $sender['phone'] = isset($cf['phone']) ? (string)$cf['phone'] : $sender['phone'];
                $sender['street'] = isset($cf['street']) ? (string)$cf['street'] : $sender['street'];
                $sender['house_no'] = isset($cf['house_no']) ? (string)$cf['house_no'] : $sender['house_no'];
                $sender['zip'] = isset($cf['zip']) ? (string)$cf['zip'] : $sender['zip'];
                $sender['city'] = isset($cf['city']) ? (string)$cf['city'] : $sender['city'];
                $sender['address'] = isset($cf['address']) ? (string)$cf['address'] : $sender['address'];
                $sender['profession'] = isset($cf['profession']) ? (string)$cf['profession'] : $sender['profession'];
                $sender['tax_number'] = isset($cf['tax_number']) ? (string)$cf['tax_number'] : $sender['tax_number'];
                $sender['vat_exempt_reason'] = isset($cf['vat_exempt_reason']) ? (string)$cf['vat_exempt_reason'] : $sender['vat_exempt_reason'];
            }
        } catch (Exception $e) {}
    }
    $sender['tax_number'] = isset($row['admin_tax_number']) ? (string)$row['admin_tax_number'] : '';
    $sender['vat_exempt_reason'] = isset($row['admin_vat_exempt_reason']) ? (string)$row['admin_vat_exempt_reason'] : '';
    // Anzeige-Block für Therapeut neu berechnen (strukturierte Adresse bevorzugt)
    $addrParts = [];
    if ($sender['street'] !== '' || $sender['house_no'] !== '') {
        $addrParts[] = trim($sender['street'].' '.$sender['house_no']);
    }
    if ($sender['zip'] !== '' || $sender['city'] !== '') {
        $addrParts[] = trim($sender['zip'].' '.$sender['city']);
    }
    $addrDisplay = implode(', ', array_filter($addrParts));
    if ($addrDisplay === '' && $sender['address'] !== '') { $addrDisplay = $sender['address']; }

    $lineName  = htmlspecialchars($sender['name']);
    $lineProf  = ($sender['profession'] !== '' ? htmlspecialchars($sender['profession']) : '&nbsp;');
    $lineAddr  = ($addrDisplay !== '' ? htmlspecialchars($addrDisplay) : '&nbsp;');
    $linePhone = 'Tel.: '.($sender['phone'] !== '' ? htmlspecialchars($sender['phone']) : '&nbsp;');
    $lineEmail = 'E-Mail: '.($sender['email'] !== '' ? htmlspecialchars($sender['email']) : '&nbsp;');
    $lineTax   = 'Steuernummer: '.($sender['tax_number'] !== '' ? htmlspecialchars($sender['tax_number']) : '&nbsp;');
    $lineVat   = ($sender['vat_exempt_reason'] && $sender['vat_exempt_reason'] !== 'none')
                 ? 'USt-Befreiung: '.htmlspecialchars($sender['vat_exempt_reason'])
                 : '&nbsp;';
    $therapeutBlockHtml = $lineName.'<br/>'.$lineProf.'<br/>'.$lineAddr.'<br/>'.$linePhone.'<br/>'.$lineEmail.'<br/>'.$lineTax.'<br/>'.$lineVat;
    $therapeutPanelHtml = '<div class="block" style="margin-top:0;"><strong>Therapeut:</strong><br/>'
        . $lineName
        . ($sender['profession']!=='' ? '<br/>'.htmlspecialchars($sender['profession']) : '')
        . ($sender['street']!==''||$sender['house_no']!==''||$sender['zip']!==''||$sender['city']!==''||$sender['address']!=='' ? '<br/>'.$lineAddr : '')
        . ($sender['email']!=='' ? '<br/>'.$lineEmail : '')
        . ($sender['phone']!=='' ? '<br/>'.$linePhone : '')
        . ($sender['tax_number']!=='' ? '<br/>'.$lineTax : '')
        . ($sender['vat_exempt_reason'] && $sender['vat_exempt_reason']!=='none' ? '<br/>'.$lineVat : '')
        . '</div>'; 
}

// Fallback: sicherstellen, dass mindestens ein Therapeuten-Panel gerendert wird
if ($therapeutPanelHtml === '') {
    $name = htmlspecialchars($sender['name'] !== '' ? $sender['name'] : 'Unbekannter Masseur');
    $addrParts = [];
    if ($sender['street'] !== '' || $sender['house_no'] !== '') { $addrParts[] = trim($sender['street'].' '.$sender['house_no']); }
    if ($sender['zip'] !== '' || $sender['city'] !== '') { $addrParts[] = trim($sender['zip'].' '.$sender['city']); }
    $addrDisplay = implode(', ', array_filter($addrParts));
    $therapeutPanelHtml = '<div class="block"><strong>Therapeut:</strong><br/>'
        . $name
        . ($addrDisplay !== '' ? '<br/>'.htmlspecialchars($addrDisplay) : '')
        . ($sender['email']!=='' ? '<br/>E-Mail: '.htmlspecialchars($sender['email']) : '')
        . ($sender['phone']!=='' ? '<br/>Tel.: '.htmlspecialchars($sender['phone']) : '')
        . ($sender['tax_number']!=='' ? '<br/>Steuernummer: '.htmlspecialchars($sender['tax_number']) : '')
        . (($sender['vat_exempt_reason'] && $sender['vat_exempt_reason']!=='none') ? '<br/>USt-Befreiung: '.htmlspecialchars($sender['vat_exempt_reason']) : '')
        . '</div>';
}

// Wenn mPDF vorhanden ist, nutze es für ein HTML-PDF
$clientName    = isset($row['client_name']) ? (string)$row['client_name'] : '';
$clientId      = isset($row['client_id']) ? (int)$row['client_id'] : 0;
$masseurId     = isset($row['masseur_id']) ? (int)$row['masseur_id'] : 0;
$patientName   = isset($row['res_name']) ? (string)$row['res_name'] : '';
$patientEmail  = isset($row['res_email']) ? (string)$row['res_email'] : '';
$dateYmd       = isset($row['date_ymd']) ? (string)$row['date_ymd'] : '';
$dateStr       = $dateYmd ? date('d.m.Y', strtotime($dateYmd)) : '';
$diagnosis     = isset($row['default_diagnosis']) ? (string)$row['default_diagnosis'] : '';
// Patient aus patients-Tabelle (structured fields + birthdate)
$patient = [ 'name' => $patientName, 'email' => $patientEmail, 'phone' => '', 'address' => '', 'street'=>'', 'house_no'=>'', 'zip'=>'', 'city'=>'', 'birthdate'=>'' ];
if ($patientEmail !== '') {
    try {
        $prow = $DB->PreparedSelect('SELECT first_name, last_name, email, phone, address, street, house_no, zip, city, birthdate, diagnosis FROM patients WHERE LOWER(email) = LOWER(:em) LIMIT 1', ['em' => $patientEmail], false, false);
        if (is_array($prow) && isset($prow[0]) && is_array($prow[0])) { $prow = $prow[0]; }
        if (is_array($prow) && isset($prow['first_name'])) {
            $pname = trim(((string)$prow['first_name']).' '.((string)$prow['last_name']));
            if ($pname !== '') { $patient['name'] = $pname; }
            $patient['email'] = isset($prow['email']) ? (string)$prow['email'] : $patientEmail;
            $patient['phone'] = isset($prow['phone']) ? (string)$prow['phone'] : '';
            $patient['street']   = isset($prow['street']) ? (string)$prow['street'] : '';
            $patient['house_no'] = isset($prow['house_no']) ? (string)$prow['house_no'] : '';
            $patient['zip']      = isset($prow['zip']) ? (string)$prow['zip'] : '';
            $patient['city']     = isset($prow['city']) ? (string)$prow['city'] : '';
            $patient['birthdate']= isset($prow['birthdate']) ? (string)$prow['birthdate'] : '';
            $patient['address']  = isset($prow['address']) ? (string)$prow['address'] : '';
        }
    } catch (Exception $e) {}
}

// Patienten-Diagnose: bevorzugt aus patients, sonst client default
try {
    if (is_array($prow) && isset($prow['diagnosis']) && trim((string)$prow['diagnosis']) !== '') {
        $diagnosis = (string)$prow['diagnosis'];
    }
} catch (Exception $e) {}

// Services/Preise ermitteln (Reservation > Client > fee_mid)
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

if (count($items) === 0) {
    $ids = [];
    if ($serviceIdsCsv !== '') { $ids = array_values(array_filter(array_map('intval', explode(',', $serviceIdsCsv)))); }
    foreach ($ids as $sid) {
        if (!isset($svcById[$sid])) continue;
        $svc = $svcById[$sid];
        $amount = isset($clientPrices[$sid]) ? (float)$clientPrices[$sid] : (isset($svc['fee_mid'])?(float)$svc['fee_mid']:0.0);
        $items[] = [ 'date'=>$dateStr, 'code'=>(string)$svc['code'], 'title'=>(string)$svc['title'], 'amount'=>$amount ];
    }
}

$sum = 0.0; foreach ($items as $it) { $sum += (float)$it['amount']; }

// Rechnungsnummern-Handling: pro Therapeut/Jahr fortlaufend, aber pro Reservierung einmalig
$invoiceNo = '';
try {
    if ($masseurId > 0) {
        $year = $dateYmd ? (int)date('Y', strtotime($dateYmd)) : (int)date('Y');

        // Mapping-Tabelle sicherstellen
        Database::PreparedStatement(
            'CREATE TABLE IF NOT EXISTS `reservation_invoices` (
                `reservation_id` INT NOT NULL PRIMARY KEY,
                `masseur_id` INT NOT NULL,
                `year` INT NOT NULL,
                `invoice_no` VARCHAR(32) NOT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX(`masseur_id`), INDEX(`year`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
            []
        );

        // Bereits vorhandene Rechnungsnummer für diese Reservierung?
        $exist = Database::PreparedSelect(
            'SELECT invoice_no FROM reservation_invoices WHERE reservation_id = :rid LIMIT 1',
            ['rid'=>$resId]
        );
        if (is_array($exist) && isset($exist[0]['invoice_no']) && $exist[0]['invoice_no']!=='') {
            $invoiceNo = (string)$exist[0]['invoice_no'];
        } else {
            // Sequenzzeile sicherstellen
            Database::PreparedStatement(
                'INSERT INTO invoice_numbers (masseur_id, `year`, seq) VALUES (:mid, :yr, 0)
                 ON DUPLICATE KEY UPDATE seq = seq',
                ['mid'=>$masseurId, 'yr'=>$year]
            );
            // Inkrementieren und Lesen
            Database::PreparedStatement(
                'UPDATE invoice_numbers SET seq = seq + 1 WHERE masseur_id = :mid AND `year` = :yr',
                ['mid'=>$masseurId, 'yr'=>$year]
            );
            $rowSeq = Database::PreparedSelect(
                'SELECT seq FROM invoice_numbers WHERE masseur_id = :mid AND `year` = :yr',
                ['mid'=>$masseurId, 'yr'=>$year]
            );
            if (is_array($rowSeq) && isset($rowSeq[0]['seq'])) {
                $seq = (int)$rowSeq[0]['seq'];
                $invoiceNo = 'na-'.sprintf('%04d', $year).'-'.str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
                // Mapping speichern (einmalig pro Reservierung)
                Database::PreparedStatement(
                    'INSERT INTO reservation_invoices (reservation_id, masseur_id, `year`, invoice_no) VALUES (:rid, :mid, :yr, :inv)
                     ON DUPLICATE KEY UPDATE invoice_no = VALUES(invoice_no)',
                    ['rid'=>$resId, 'mid'=>$masseurId, 'yr'=>$year, 'inv'=>$invoiceNo]
                );
            }
        }
    }
} catch (Exception $e) {}

// mPDF Verfügbarkeit
$mpdfAvailable = false;
if (file_exists(ROOT.'/vendor/autoload.php')) {
    require_once ROOT.'/vendor/autoload.php';
    if (class_exists('Mpdf\\Mpdf')) { $mpdfAvailable = true; }
}

if ($mpdfAvailable) {

    // Schöneres Layout mit Beträgen
    $html = '<html><head><meta charset="utf-8"><style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#111; }
        h1 { text-align:center; font-size: 22px; margin: 8px 0 12px; }
        .meta { font-size: 10px; color:#333; display: table; width:100%; margin-bottom:6px; }
        .meta .l{display:table-cell; text-align:left}
        .meta .c{display:table-cell; text-align:center}
        .meta .r{display:table-cell; text-align:right}
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

    // Titel + kleine Rechnungsnummer darunter
    $html .= '<h1>Rechnung</h1>';
    if ($invoiceNo !== '') { $html .= '<div style="text-align:center; font-size:11px; color:#333; margin-top:-4px;">Rechnungs-Nr.: '.htmlspecialchars($invoiceNo).'</div>'; }

    // Patient und Therapeut gemeinsam in einer Zeile (2 Spalten) – gleiche Höhe direkt unter dem Titel
    // Patientendarstellung: strukturierte Adresse in zwei Zeilen (Straße Hausnr. / PLZ Ort) + Geburtsdatum
    $pLine1 = trim(($patient['street']??'').' '.($patient['house_no']??''));
    $pLine2 = trim(($patient['zip']??'').' '.($patient['city']??''));
    $pLegacy = isset($patient['address']) ? trim((string)$patient['address']) : '';
    $bd = ($patient['birthdate']??'');
    if ($bd !== '' && strpos($bd,'-')!==false) { $bd = date('d.m.Y', strtotime($bd)); }
    $leftPatientHtml = '<div class="block" style="margin-top:0;"><strong>Patient:</strong><br/>'
          . htmlspecialchars($patient['name'])
          . ($pLine1 !== '' ? '<br/>'.htmlspecialchars($pLine1) : ($pLegacy!==''?'<br/>'.htmlspecialchars($pLegacy):''))
          . ($pLine2 !== '' ? '<br/>'.htmlspecialchars($pLine2) : '')
          . ($patient['email'] ? '<br/>E-Mail: '.htmlspecialchars($patient['email']) : '')
          . ($patient['phone'] ? '<br/>Tel.: '.htmlspecialchars($patient['phone']) : '')
          . ($bd ? '<br/>Geburtsdatum: '.htmlspecialchars($bd) : '')
          . '</div>';

    // Mehr Abstand zwischen USt-Befreiung im rechten Panel und dem Rechnungsdatum
    $rightTheraHtml = $therapeutPanelHtml
          . '<div class="block" style="margin-top:18px;">Rechnungsdatum: '.htmlspecialchars($dateStr).'</div>';

    $html .= '<div class="grid header">'
          . '<div class="col">'.$leftPatientHtml.'</div>'
          . '<div class="col right">'.$rightTheraHtml.'</div>'
          . '</div>';

    // Trennlinie zwischen Kopf (Therapeut/Patient) und Berechnung – etwas mehr Abstand
    $html .= '<hr style="border:0;border-top:1px solid #ddd; margin:16px 0 12px;" />';

    // Termin (mit Abstand nach unten)
    if ($dateStr) {
        $html .= '<div class="block" style="margin-bottom:8px;"><strong>Behandlungstermin:</strong> '.htmlspecialchars($dateStr).'</div>';
    }

    // Diagnose (mit extra Abstand: mind. zwei Zeilen)
    if ($diagnosis !== '') {
        $html .= '<div class="block" style="margin-bottom:16px;"><strong>Diagnose:</strong><br/>'.nl2br(htmlspecialchars($diagnosis)).'</div>';
    } else {
        // Falls keine Diagnose vorhanden ist, trotzdem Abstand zum nächsten Bereich lassen
        $html .= '<div style="height:16px;"></div>';
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
        $html .= '<tr class="sum"><td colspan="3" class="right">Gesamtbetrag in EURO</td><td class="right">'.number_format($sum, 2, ',', '.').'</td></tr>';
    } else {
        $html .= '<tr><td colspan="4" class="small muted">Keine Leistungen hinterlegt.</td></tr>';
    }
    $html .= '</tbody></table>';

    // Zahlungs-Hinweis und rechtliche Hinweise (Absätze, gleiche Typo wie Body)
    $html .= '<div class="block" style="margin-top:12px;"><em>Betrag wurde bereits entrichtet.</em></div>';
    $html .= '<div class="block" style="margin-top:8px; margin-bottom:8px;">Die erbrachten Leistungen sind gemäß § 4 Nr. 14 UStG von der Umsatzsteuer befreit (Heilbehandlung).</div>';
    $html .= '<div class="block" style="margin-top:8px; margin-bottom:8px;">Hinweis: Diese Rechnung wurde maschinell erstellt und ist ohne Unterschrift gültig.</div>';
    $html .= '<div class="block" style="margin-top:10px; font-size:13px;">'
          . htmlspecialchars($sender['name'])
          . ( ($sender['city']!=='') ? ', '.htmlspecialchars($sender['city']) : '' )
          . ' den '.htmlspecialchars($dateStr)
          . '</div>';
    if (!empty($sender['iban'])) {
        $html .= '<div class="block small" style="margin-top:10px;">Bankverbindung IBAN: '.htmlspecialchars($sender['iban']).'</div>';
    }
    // Zusätzliche VAT-Notiz aus admin.vat_exempt_reason wird hier nicht nochmals wiederholt, um Dopplungen zu vermeiden

    $html .= '</body></html>';

    $mpdf = new \Mpdf\Mpdf();
    $mpdf->WriteHTML($html);
    $mpdf->Output('Rechnung_'.$resId.'.pdf', 'I');
    exit;
}

// HTML-Fallback (kein mPDF installiert)
header('Content-Type: text/html; charset=utf-8');
echo '<!doctype html><html><head><meta charset="utf-8"><title>Rechnung '.$resId.'</title><style>
body{font-family:Arial,Helvetica,sans-serif;color:#111;font-size:13px;margin:20px}
h1{font-size:22px;margin:8px 0 12px;text-align:center}
.meta{font-size:10px;color:#333;display:table;width:100%;margin-bottom:6px}
.meta .l{display:table-cell;text-align:left}
.meta .c{display:table-cell;text-align:center}
.meta .r{display:table-cell;text-align:right}
.grid{display:table;width:100%}.col{display:table-cell;vertical-align:top}.right{text-align:right}
table{width:100%;border-collapse:collapse;margin-top:10px}
th,td{border:1px solid #bbb;padding:6px 8px}th{background:#f2f2f2}
.sum{font-weight:bold}
.print{position:fixed;top:10px;right:10px}
</style></head><body onload="setTimeout(function(){window.print();},200)">';
echo '<button class="print" onclick="window.print()">Drucken</button>';
// Nur Haupttitel ohne Meta-Zeile
echo '<h1>Rechnung</h1>';
if ($invoiceNo !== '') { echo '<div style="text-align:center;font-size:11px;color:#333;margin-top:-4px;">Rechnungs-Nr.: '.htmlspecialchars($invoiceNo).'</div>'; }
// Patient und Therapeut gemeinsam in einer Zeile (2 Spalten)
$pLine1 = trim(($patient['street']??'').' '.($patient['house_no']??''));
$pLine2 = trim(($patient['zip']??'').' '.($patient['city']??''));
$pLegacy = isset($patient['address']) ? trim((string)$patient['address']) : '';
$bd = ($patient['birthdate']??''); if ($bd !== '' && strpos($bd,'-')!==false) { $bd = date('d.m.Y', strtotime($bd)); }
$leftPatientHtml = '<div style="margin-top:0"><strong>Patient:</strong><br>'+htmlspecialchars($patient['name'])+($pLine1!==''?'<br>'+htmlspecialchars($pLine1):($pLegacy!==''?'<br>'+htmlspecialchars($pLegacy):''))+($pLine2!==''?'<br>'+htmlspecialchars($pLine2):'')+($patient['email']?'<br>E-Mail: '+htmlspecialchars($patient['email']):'')+($patient['phone']?'<br>Tel.: '+htmlspecialchars($patient['phone']):'')+($bd?'<br>Geburtsdatum: '+htmlspecialchars($bd):'')+'</div>';
// Mehr Abstand zwischen USt-Befreiung im rechten Panel und dem Rechnungsdatum
$rightTheraHtml = $therapeutPanelHtml.'<div style="margin-top:18px">Rechnungsdatum: '.htmlspecialchars($dateStr).'</div>';
echo '<div class="grid"><div class="col">'+$leftPatientHtml+'</div><div class="col right">'+$rightTheraHtml+'</div></div>';
// Trennlinie vor dem Behandlungstermin – etwas mehr Abstand
echo '<hr style="border:0;border-top:1px solid #ddd;margin:16px 0 12px;" />';
// HTML Fallback: Patient mit strukturierter Adresse + Geburtsdatum
$pAddrParts = [];
if (($patient['street']??'') !== '' || ($patient['house_no']??'') !== '') { $pAddrParts[] = trim(($patient['street']??'').' '.($patient['house_no']??'')); }
if (($patient['zip']??'') !== '' || ($patient['city']??'') !== '') { $pAddrParts[] = trim(($patient['zip']??'').' '.($patient['city']??'')); }
$pAddrDisplay = implode(', ', array_filter($pAddrParts));
if ($pAddrDisplay === '' && ($patient['address']??'') !== '') { $pAddrDisplay = $patient['address']; }
$bd = ($patient['birthdate']??'');
if ($bd !== '') {
    if (strpos($bd,'-')!==false) { $bd = date('d.m.Y', strtotime($bd)); }
}
echo '<div><strong>Patient:</strong><br>'.htmlspecialchars($patient['name']).($pAddrDisplay?'<br>'.htmlspecialchars($pAddrDisplay):'').($patient['email']?'<br>E-Mail: '.htmlspecialchars($patient['email']):'').($patient['phone']?'<br>Tel.: '.htmlspecialchars($patient['phone']):'').($bd?'<br>Geburtsdatum: '.htmlspecialchars($bd):'').'</div>';
if ($dateStr) echo '<div style="margin-bottom:8px"><strong>Behandlungstermin:</strong> '.htmlspecialchars($dateStr).'</div>';
if ($diagnosis!=='') echo '<div style="margin-bottom:16px"><strong>Diagnose:</strong><br>'.nl2br(htmlspecialchars($diagnosis)).'</div>';
else echo '<div style="height:16px"></div>';
echo '<table><thead><tr><th style="width:18%">Datum</th><th style="width:14%">GebüH-Nr.</th><th>Leistung</th><th style="width:16%" class="right">Betrag (€)</th></tr></thead><tbody>';
if (count($items)>0){
  foreach($items as $svc){
    echo '<tr><td>'.htmlspecialchars($svc['date']).'</td><td>'.htmlspecialchars($svc['code']).'</td><td>'.htmlspecialchars($svc['title']).'</td><td class="right">'.number_format((float)$svc['amount'],2,',','.').'</td></tr>';
  }
  echo '<tr class="sum"><td colspan="3" class="right">Gesamtbetrag in EURO</td><td class="right">'.number_format($sum,2,',','.').'</td></tr>';
} else {
  echo '<tr><td colspan="4">Keine Leistungen hinterlegt.</td></tr>';
}
echo '</tbody></table>';
echo '<div style="margin-top:12px"><em>Betrag wurde bereits entrichtet.</em></div>';
echo '<div style="margin-top:8px; margin-bottom:8px">Die erbrachten Leistungen sind gemäß § 4 Nr. 14 UStG von der Umsatzsteuer befreit (Heilbehandlung).</div>';
echo '<div style="margin-top:8px; margin-bottom:8px">Hinweis: Diese Rechnung wurde maschinell erstellt und ist ohne Unterschrift gültig.</div>';
echo '<div style="margin-top:10px; font-size:13px">'.htmlspecialchars($sender['name']).( ($sender['city']!=='') ? ', '.htmlspecialchars($sender['city']) : '' ).' den '.htmlspecialchars($dateStr).'</div>';
if (!empty($sender['iban'])) { echo '<div class="small" style="margin-top:10px">Bankverbindung IBAN: '.htmlspecialchars($sender['iban']).'</div>'; }
echo '</body></html>';
exit;
?>
