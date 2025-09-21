<?php
// IMAP server credentials
$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
$username = 'saleheen@astutesol.com';
$password = 'Saleheen123';

// Try to connect
$inbox = imap_open($hostname, $username, $password) or die('Cannot connect to IMAP server: ' . imap_last_error());

// Check connection status
if ($inbox) {
    echo "Connected successfully to the IMAP server.";
    // Close the connection
    imap_close($inbox);
} else {
    echo "Failed to connect to the IMAP server.";
}




// Turn off all error reporting
error_reporting(0);

// Configuration
$user = 'saleheen@astutesol.com';
$password = 'Saleheen123';
$host = 'imap.gmail.com';

// Default data array

// Open an IMAP stream to a mailbox
$mailbox = imap_open("{" . $host . ":993/imap/ssl}INBOX", $user, $password, OP_READONLY);

if ($mailbox === false) {
	echo 'Error : ' . imap_last_error();
} else {
	// Gets status information about the given mailbox
	/* $status = imap_status($mailbox, "{" . $host . "}INBOX", SA_ALL);
	if ($status === false) {
		echo 'Error : ' . imap_last_error();
	} else {
		$items['msg']['all'] = $status->messages;
		// Gets UIDs for unseen messages
        $messages = imap_search($mailbox, 'ALL');
            
        if ($messages === false) {
            echo 'Error : ' . imap_last_error();
        } else {
            // For each all unseen messages
            foreach($messages as $uid ) {
                // Get header of the message
                $message = imap_headerinfo($mailbox, $uid);
                // echo '<pre>'; print_r($message); die;

                if ($message) {
                    // From message
                    $from = $message->from[0];
                    $from_addres = $from->mailbox . "@" . $from->host;

                    // Subject
                    $subject = isset($message->subject)
                        ? imap_utf8($message->subject) : 'No subject';

                    // Re-format date
                    $date = date("Y-m-d H:i", strtotime($message->date));

                    // Add to data array
                    $items['msg']['all_items'][] = array(
                        $uid, $from_addres, $date, $subject
                    );
                }
            }
        } 
    }*/


$now = time();
$yesterday = strtotime('-7 day', $now);
$yesterday_date = date('d-M-Y', $yesterday);

$status = imap_status($mailbox, "{" . $host . "}INBOX", SA_ALL);
if ($status === false) {
    echo 'Error : ' . imap_last_error();
} else {
    
    // Gets UIDs for messages from the last 24 hours
    $messages = imap_search($mailbox, 'SINCE "' . $yesterday_date . '"');
        
    if ($messages === false) {
        echo 'Error : ' . imap_last_error();
    } else {
        // For each message from the last 24 hours
        foreach ($messages as $uid) {
            // Get header of the message
            $message = imap_headerinfo($mailbox, $uid);
            // echo '<pre>'; print_r($message); die;

            if ($message) {
                // From message
                $from = $message->from[0];
                $from_addres = $from->mailbox . "@" . $from->host;

                // Subject
                $subject = isset($message->subject)
                    ? imap_utf8($message->subject) : 'No subject';

                // Re-format date
                $date = date("Y-m-d H:i", strtotime($message->date));

                // Add to data array
                $items['emails'][] = array(
                    $uid, $from_addres, $date, $subject
                );
            }
        }
    }
}

	

	// Close an IMAP stream
	imap_close($mailbox);
}

// Show messages from data array
echo '<pre>' . print_r($items, true) . '</pre>';



?>
