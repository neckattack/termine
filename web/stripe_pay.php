<?php
    $PAGE = basename(__FILE__);
    require "_root_.php";               // Defines the ROOT constant
    require ROOT."/inc/_include.php";
    include ROOT."/controller/index-controller.php";
    require_once(ROOT.'/lib/stripe-php-6.6.0/init.php');
  
    function logStripe($post_data){
        try {
            $fd = fopen(ROOT."/logs/stripe_logs.log", "a");
            fwrite($fd, gmdate("[Y-m-d H:i:s \G\M\T]")."\n".json_encode($post_data)."\n"); 
            fclose($fd); 
        }
        catch (Exception $e){}
    }

    logStripe("Start");

    if ( isset($_GET['token']) &&
        isset($_GET['amount']) &&
        isset($_GET['currency']) &&
        isset($_GET['description']) &&
        isset($_GET['email']) &&
        isset($_GET['name']) &&
        isset($_GET['times']) &&
        isset($_GET['clientId']) ){

        $token = $_GET['token'];
        $clientID = (int) $_GET['clientId'];
        $client   = getClientInfos($clientID);
        $times    = $_GET["times"];
        $name     = $_GET["name"];
        $email    = $_GET["email"];
        $description    = $_GET["description"];

        $paymentCorrect = ($client['price'] && floatval(str_replace(',', '.', $client['price'])) > 0);

        $amount = $_GET['amount'];
        $currency = $_GET['currency'];

        $checkAmount = $paymentCorrect ? count($times) * floatval(str_replace(',', '.', $client['price'])) : 0;
        $paymentCorrect = $paymentCorrect && ( abs($amount - $checkAmount*100) < 10 ); // not more than 10 cents
        $paymentCorrectText = '$checkAmount == '.($checkAmount*100).' (cents)';

        $checkCurrency = $config['stripe_currency'];
        $paymentCorrect = $paymentCorrect && $currency == $checkCurrency;
        $paymentCorrectText .= ', $checkCurrency == '.$checkCurrency;

        // Register to times
        $checkAvail = checkTimesAvailable($times);
        $paymentCorrectText .= ', $checkAvail[\'allAvailable\'] === '.$checkAvail["allAvailable"];

        if ($checkAvail["allAvailable"] === true && $paymentCorrect) {
            
            try {
                \Stripe\Stripe::setApiKey($config['stripe_secret_token']);
                $charge = \Stripe\Charge::create(['amount' => $amount, 
                                                    'currency' => $currency, 
                                                    'source' => $token,
                                                    'description' => $description,
                                                    'receipt_email' => $email]);
            }
            catch (Exception $e){
                logStripe(['Error_Stripe'=>$e]);
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                die();
            }

            logStripe($charge); // log result of charge

            if($charge->paid){
                $success = setReservations($times, $name, $email);
                if ($success !== false) {
            
                    $message  = $client["email_text"];
                    $contact  = getContactInfo($clientID);

                    // Send confirmation e-mail
                    $email = sendConfirmationMail($email, $name, $times, $contact, $message);

                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Reservations not completed']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Payment not finished yet']);
            }
        } else {
            // do smth if not all available
            logStripe([
                'Error'=>'!allAvailable || !paymentCorrect',
                'Details'=>$paymentCorrectText,
                '_GET'=>$_GET,
            ]);
            if ( !$checkAvail["allAvailable"] ) {
                echo json_encode(['success' => false, 'message' => 'Not all times available']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Wrong amount passed']);
            }
        }
    } else {
        logStripe([
            'Error'=>'!allAvailable || !paymentCorrect',
            'Details'=>'Not all fields passed.',
        ]);
        echo json_encode(['success' => false, 'message' => 'Not all fields passed']);
    }
    logStripe("Finish 200");
?>