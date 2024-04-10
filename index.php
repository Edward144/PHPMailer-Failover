<?php

use Html2Text\Html2Text;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once('vendor/autoload.php');

$body = 
    '<p>Hi Test Name,</p>
    
    <p>This is a test message. <strong>This is some bold text.</strong></p>
    
    <p style="color: blue;">Kind Regards, Testing Mailer</p>';

$send = sendmail([ 'user@example.com', 'other.user@example.com' => 'Other User' ], 'Test 2', $body, [ dirname(__FILE__) . '/attachment.txt', dirname(__FILE__) . '/attachment.txt' => 'other_attachment.txt' ]);

if($send === true) {
    echo 'Mail sent successfully';
}
else {
    echo 'Mail failed to send: ' . $send;
}