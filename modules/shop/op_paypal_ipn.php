<?php

// namespace Listener;

$log->write('shop', 'ipn_request', 'Requested IPN.');

// Set this to true to use the sandbox endpoint during testing:
if ((int)$fabShop->config['useSandbox'] === 1) {

    $relog->write(['type'      => '1',
                   'module'    => 'SHOP',
                   'operation' => 'shop_ipn_sandbox',
                   'details'   => 'Using sandbox while processing IPN. ',
    ]);

    $enable_sandbox = true;
} else {

    $relog->write(['type'      => '1',
                   'module'    => 'SHOP',
                   'operation' => 'shop_ipn_live',
                   'details'   => 'Using live while processing IPN. ',
    ]);

}

// Use this to specify all of the email addresses that you have attached to paypal:
$my_email_addresses = [$fabShop->config['businessEmail']];

// Set this to true to send a confirmation email:
$send_confirmation_email = true;
$confirmation_email_address = "My Name <my_email_address@gmail.com>";
$from_email_address = "My Name <my_email_address@gmail.com>";

// Set this to true to save a log file:
$save_log_file = true;
$log_file_dir = __DIR__ . "/log";

// Here is some information on how to configure sendmail:
// http://php.net/manual/en/function.mail.php#118210
require_once(__DIR__ . '/lib/class_paypal_ipn.php');

use PaypalIPN;

$ipn = new PaypalIPN();
if ($enable_sandbox) {
    $ipn->useSandbox();
}

$verified = $ipn->verifyIPN();
$data_text = "";

foreach ($_POST as $key => $value) {
    $data_text .= $key . " = " . $value . "\r\n";
}
$test_text = "";
if ($_POST["test_ipn"] == 1) {
    $test_text = "Test ";
}
// Check the receiver email to see if it matches your list of paypal email addresses
$receiver_email_found = false;
foreach ($my_email_addresses as $a) {
    if (strtolower($_POST["receiver_email"]) == strtolower($a)) {
        $receiver_email_found = true;
        break;
    }
}

// date_default_timezone_set("America/Los_Angeles");

list($year, $month, $day, $hour, $minute, $second, $timezone) = explode(":", date("Y:m:d:H:i:s:T"));
$date = $year . "-" . $month . "-" . $day;
$timestamp = $date . " " . $hour . ":" . $minute . ":" . $second . " " . $timezone;
$dated_log_file_dir = $log_file_dir . "/" . $year . "/" . $month;
$paypal_ipn_status = "VERIFICATION FAILED";

if ($verified) {
    $paypal_ipn_status = "RECEIVER EMAIL MISMATCH";

    if ($receiver_email_found) {

        $paypal_ipn_status = "Completed Successfully";
        // Process IPN
        // A list of variables are available here:
        // https://developer.paypal.com/webapps/developer/docs/classic/ipn/integration-guide/IPNandPDTVariables/
        // This is an example for sending an automated email to the customer when they purchases an item for a specific amount:

        if ($_POST["payment_status"] == "Completed") {
            $link = $URI->getBaseUri() . $this->routed . '/orders/';

            $fabShop->updateDbCartStatus(['status' => 1,
                                          'ID'     => (int) $_POST ['custom'],
            ]);


            $fabShop->processTriggers( ( int) $_POST['custom']);

            $email_to = $_POST["first_name"] . " " . $_POST["last_name"] . " <" . $_POST["payer_email"] . ">";
            $email_subject = "{$conf['site']['name']} Conferma ordine";

            $email_body = sprintf($language->get('shop', 'ipn_checkout_complete_email_link'), $conf['site']['name'], $link);

            // Mail it
            $fabmail->addFrom($fabShop->config['businessEmail'], $conf['site']['name'] . ' - Shop'); // We use the global reply name, such as noreply@domain.tld
            $fabmail->addSubject($email_subject);
            $fabmail->addTo($_POST['payer_email'], $_POST['first_name'] . ' ' . $_POST['last_name']);
            $fabmail->addMessage($email_body);

            if (!$fabmail->sendEmail()) {
                $relog->write(['type'      => '3',
                               'module'    => 'SHOP',
                               'operation' => 'shop_ipn_send_mail_error',
                               'details'   => 'Error while sending email. ' . $fabmail->lastError,
                ]);

                return false;
            } else {
                $relog->write(['type'      => '3',
                               'module'    => 'SHOP',
                               'operation' => 'shop_ipn_send_mail_ok',
                               'details'   => 'Mail sent. ' . $email_body,
                ]);

                return true;
            }

        }
    } else {

        $relog->write(['type'      => '1',
                       'module'    => 'SHOP',
                       'operation' => 'shop_ipn_sandbox',
                       'details'   => 'Using sandbox while processing IPN. ',
        ]);

    }
} else if ($enable_sandbox) {
    if ($_POST["test_ipn"] != 1) {

        $relog->write(['type'      => '1',
                       'module'    => 'SHOP',
                       'operation' => 'shop_ipn_received_sandbox',
                       'details'   => 'Received IPN from sandobox',
        ]);

        $paypal_ipn_status = "RECEIVED FROM LIVE WHILE SANDBOXED";
    }
} else if ($_POST["test_ipn"] == 1) {

    $relog->write(['type'      => '1',
                   'module'    => 'SHOP',
                   'operation' => 'shop_ipn_received_live',
                   'details'   => 'Received IPN from live',
    ]);

    $paypal_ipn_status = "RECEIVED FROM SANDBOX WHILE LIVE";
}

if ($save_log_file) {
    // Create log file directory
    if (!is_dir($dated_log_file_dir)) {
        if (!file_exists($dated_log_file_dir)) {
            mkdir($dated_log_file_dir, 0777, true);
            if (!is_dir($dated_log_file_dir)) {
                $save_log_file = false;
            }
        } else {
            $save_log_file = false;
        }
    }

    // Restrict web access to files in the log file directory
    $htaccess_body = "RewriteEngine On" . "\r\n" . "RewriteRule .* - [L,R=404]";
    if ($save_log_file && (!is_file($log_file_dir . "/.htaccess") || file_get_contents($log_file_dir . "/.htaccess") !== $htaccess_body)) {
        if (!is_dir($log_file_dir . "/.htaccess")) {
            file_put_contents($log_file_dir . "/.htaccess", $htaccess_body);
            if (!is_file($log_file_dir . "/.htaccess") || file_get_contents($log_file_dir . "/.htaccess") !== $htaccess_body) {
                $save_log_file = false;
            }
        } else {
            $save_log_file = false;
        }
    }

    if ($save_log_file) {
        // Save data to text file
        file_put_contents($dated_log_file_dir . "/" . $test_text . "paypal_ipn_" . $date . ".txt", "paypal_ipn_status = " . $paypal_ipn_status . "\r\n" . "paypal_ipn_date = " . $timestamp . "\r\n" . $data_text . "\r\n", FILE_APPEND);
    }

}

if ($send_confirmation_email) {
    // Send confirmation email
    mail($confirmation_email_address, $test_text . "PayPal IPN : " . $paypal_ipn_status, "paypal_ipn_status = " . $paypal_ipn_status . "\r\n" . "paypal_ipn_date = " . $timestamp . "\r\n" . $data_text, "From: " . $from_email_address);
}

// Reply with an empty 200 response to indicate to paypal the IPN was received correctly
header("HTTP/1.1 200 OK");