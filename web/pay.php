<?php

/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * pay.php
 * Pay a client
 */

if(isset($_GET['test'])){
    echo "It works!";
    logPayPal("TEST");
    exit;
}

error_reporting(-1);             // Enable PHP error reporting (set to 0 to disable)
$PAGE = basename(__FILE__);
require "_root_.php";               // Defines the ROOT constant
require ROOT."/inc/_include.php";
include ROOT."/controller/index-controller.php";
require "PaypalIPN.php";

function logPayPal($post_data){
  try {
    $fd = fopen(ROOT."/logs/paypal_logs.log", "a");
    fwrite($fd, gmdate("[Y-m-d H:i:s \G\M\T]")."\n".json_encode($post_data)."\n");
    fclose($fd);
  }
  catch (Exception $e){}
}

if ( !$_POST || !count($_POST) ) {
    header("HTTP/1.1 400 BAD REQUEST");
    logPayPal("Finish 400");
    return null;
}

logPayPal("Start");

logPayPal([
    'REQ_Headers'=>getallheaders(),
    'REQ_POST_count'=>count($_POST),
]);

// log post data from php://input
try {
    $raw_post_data = file_get_contents('php://input');
    $raw_post_array = [];
    array_map(function($map_item) use (&$raw_post_array) {
        $map_item_exploded = explode('=', $map_item);
        $raw_post_array[$map_item_exploded[0]] = @$map_item_exploded[1];
        if (!empty($raw_post_array[$map_item_exploded[0]])) {
            $raw_post_array[$map_item_exploded[0]] = urldecode($raw_post_array[$map_item_exploded[0]]);
        }
    }, explode('&', $raw_post_data));
    logPayPal($raw_post_data);
    logPayPal($raw_post_array);
} catch (Exception $e){

}

logPayPal($_POST);

// verify request is valid and from PayPal's servers\
/*
$ipn = new PaypalIPN();
$ipn->usePHPCerts();
$verifiedIPN = false;
try {
    $verifiedIPN = $ipn->verifyIPN();
} catch (Exception $e) {
    logPayPal([
        'Success'=>false,
        'Details'=>$e->getMessage(),
    ]);
}
*/
$verifiedIPN = true;
logPayPal([
    'verifiedIPN'=>$verifiedIPN ? 'true' : 'false',
]);

$status = @$_POST['payment_status'];

if ($verifiedIPN && strtoupper($status) == "COMPLETED") {

    $hash = @$_POST['item_number'];

    $custom = mb_convert_encoding(@$_POST['custom'], 'utf-8');
    $custom = json_decode($custom, true);

    $clientID = (int) $custom["id"];
    $client   = getClientInfos($clientID);
    $times    = $custom["times"];
    $name     = $custom["name"];
    $email    = $custom["email"];

    $paymentCorrectText = 'ClientID: '.$clientID.'; ';

    $paymentCorrect = ($client['price'] && $client['price'] > 0);
    $paymentCorrectText .= $paymentCorrect ? '' : 'Client have not set price!!! ';

    $amount = floatval(@$_POST['mc_gross']);
    $currency = @$_POST['mc_currency'];

    $checkAmount = $paymentCorrect ? count($times) * floatval(str_replace(',', '.', $client['price'])) : 0;
    $paymentCorrect = $paymentCorrect && ( abs($amount - $checkAmount) < 0.1 );
    $paymentCorrectText .= 'Amount expected: '.$checkAmount.', Amount given: '.$amount;

    $checkCurrency = $config['paypal_currency'];
    $paymentCorrect = $paymentCorrect && $currency == $checkCurrency;
    $paymentCorrectText .= '; Currency expected: '.$checkCurrency.', Currency given: '.$currency;

    // Register to times
    $checkAvail = checkTimesAvailable($times, true);
    $paymentCorrectText .= '; Times requested: ['.implode(',',$times).'], Times not available: [';
    if ( $checkAvail["allAvailable"] ) {
        $paymentCorrectText .= ']';
    } else {
        $paymentCorrectText .= implode(',',$checkAvail['taken']).'], Times not available details: [';
        try {
            $notAvailableDetails = array_map(function($var){
                        return 'id:'.$var['id'].
                            ', name:'.$var['name'].
                            ', email:'.$var['email'].
                            ', time_id:'.$var['time_id'].
                            ', registered_at:'.$var['registered_at']
                        ;
                    }, $checkAvail['takenObj']);
            $paymentCorrectText .= implode(';',$notAvailableDetails).']';
        } catch (Exception $e) {
            $paymentCorrectText .= ']';
        }
    }
    $paymentCorrectText .= '; By '.$name.' ('.$email.')';

    // All times are free, set reservation
    if ($checkAvail["allAvailable"] === true && $paymentCorrect) {
      $success = setReservations($times, $name, $email);
      if ($success !== false) {

        $message  = $client["email_text"];
        $contact  = getContactInfo($clientID);

        // Send confirmation e-mail
        $emailStatus = sendConfirmationMail($email, $name, $times, $contact, $message);

        logPayPal([
            'Success'=>true,
            'Details'=>$paymentCorrectText,
        ]);
      }
    } else {
        // do smth if not all available
        logPayPal([
            'Success'=>false,
            'Details'=>$paymentCorrectText,
        ]);
    }
} else {
    logPayPal([
        'Success'=>false,
        'Details'=>'Request not verified or status is not Completed.',
    ]);
}

logPayPal("Finish 200");

// Reply with an empty 200 response to indicate to paypal the IPN was received correctly
header("HTTP/1.1 200 OK");
?>
