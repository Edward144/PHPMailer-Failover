<?php

use Edward144\PhpMailerFailover\Mailer;

require dirname(__DIR__) . '/vendor/autoload.php';
include_once(__DIR__ . '/config.php');

$mail = new Mailer();

//Add a single address
$mail->addTo('single.address@example.com');

//Add a single address with friendly
$mail->addTo('single.friendly@example.com', 'Single Friendly');

//Add an array of addresses
$mail->addTo([
    'array.1@example.com',
    'array.2@example.com'
]);

//Add an array of addresses with friendlies
$mail->addTo([
    'array.friendly.1@example.com' => 'Array Friendly 1',
    'array.friendly.2@example.com' => 'Array Friendly 2'
]);

$mail->subject('This is the subject');
$mail->Body(
    '<p>Hello Test Name,</p>
    
    <p>This is HTML body content. <strong>This is some bold text.</strong></p>
    
    <p>Kind Regards<br>
    <i>Example System</i></p>'
);

//Add a single attachment
$mail->addAttachment(__DIR__ . '/attachments/attachment_1.txt');

//Add a single attachment with different filename
$mail->addAttachment(__DIR__ . '/attachments/attachment_1.txt', 'attachment_1_renamed.txt');

//Add an array of attachments
$mail->addAttachment([
    __DIR__ . '/attachments/attachment_2.txt',
    __DIR__ . '/attachments/attachment_3.txt'
]);

//Add an array of attachments with different filenames
$mail->addAttachment([
    __DIR__ . '/attachments/attachment_2.txt' => 'attachment_2_renamed.txt',
    __DIR__ . '/attachments/attachment_3.txt' => 'attachment_3_renamed.txt'
]);

$send = $mail->send();

if($send !== true) {
    echo 'Mail failed to send. ' . $send;
}
else {
    echo 'Mail sent successfully. ' . $send;
}