<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * search_reservations.php
 * Admin-Suche nach Reservierungen über E-Mail
 */
error_reporting(-1);
$PAGE = 'search_reservations';
require "_root_.php";
// Debugmodus: ?dbg=1 zeigt auch fatale Fehler schon vor dem Template
if (isset($_GET['dbg']) && $_GET['dbg'] == '1') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
    register_shutdown_function(function(){
        $e = error_get_last();
        if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            header('Content-Type: text/plain; charset=utf-8');
            echo "Fatal error before render:\n";
            echo $e['message']."\n";
            echo $e['file'].':'.$e['line']."\n";
        }
    });
}
require ROOT."/inc/_include.php";
require ROOT."/inc/admincheck.php";
require ROOT."/controller/admin-search_reservations-controller.php";
// kein require von index-controller.php nötig

$ADMIN = true; // für header-tpl Anzeige

$Q = $_GET;
$email = isset($Q['email']) ? trim($Q['email']) : '';
$rows = [];
if ($email !== '') {
    try {
        $rows = searchReservationsByEmail($email);
    } catch (Exception $e) {
        if (isset($ADMIN) && $ADMIN === true) {
            header('Content-Type: text/html; charset=utf-8');
            echo '<pre style="white-space:pre-wrap;">Suche-Fehler: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "\n";
            echo htmlspecialchars($e->getFile().':'.$e->getLine(), ENT_QUOTES, 'UTF-8') . "\n";
            echo htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8');
            echo '</pre>';
        }
        $rows = [];
    }
}

if (isset($_GET['dbg']) && $_GET['dbg'] == '1' && isset($ADMIN) && $ADMIN === true) {
    echo '<pre>PAGE=' . htmlspecialchars(PAGE, ENT_QUOTES, 'UTF-8') . "\n";
    $tplPath = ROOT.'/tpl/'.PAGE.'-tpl.php';
    echo 'TPL exists: ' . (is_file($tplPath) ? 'yes' : 'no') . ' => ' . htmlspecialchars($tplPath, ENT_QUOTES, 'UTF-8') . "\n";
    $ctrlPath = ROOT.'/controller/'.PAGE.'-controller.php';
    echo 'CTRL exists: ' . (is_file($ctrlPath) ? 'yes' : 'no') . ' => ' . htmlspecialchars($ctrlPath, ENT_QUOTES, 'UTF-8') . "\n";
    echo 'email=' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . "\n";
    echo '</pre>';
}

require ROOT."/tpl/_include.php";
