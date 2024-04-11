<?php

use Html2Text\Html2Text;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once('vendor/autoload.php');

$smtpDebug = '';

function sendmail($to = [], $subject, $body, $attachments = [], $from = null, $fromFriendly = null) {
    global $smtpDebug;

    $failureStage = 0;
    $smtpDebug = '';
    
    $debugTo = '';
    $debugAttachments = '';

    //Define basic mail settings
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->isHTML();
        $mail->SMTPAuth = true;
        
        //Enable debug
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = function($str, $level) {
            global $smtpDebug;
            $smtpDebug .= $level . ': ' . $str . "\n";
        };

        //Set mail content
        $htmlBody = new Html2Text($body);

        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = $htmlBody->getText();

        //Set from and add to addresses
        $from = (!empty($from) ? $from : MAIL_FROM_ADDRESS);
        $fromFriendly = (!empty($fromFriendly) ? $fromFriendly : MAIL_FROM_FRIENDLY);

        $mail->setFrom($from, $fromFriendly);

        foreach($to as $key => $value) {
            if(is_numeric($key)) {
                $mail->addAddress($value);
                $debugTo .= $value . '; ';
            }
            else {
                $mail->addAddress($key, $value);
                $debugTo .= $value . ' <' . $key . '>; ';
            }
        }

        //Add attachments if provided
        if(!empty($attachments)) {
            foreach($attachments as $key => $value) {
                if(is_numeric($key)) {
                    $mail->addAttachment($value);
                    $debugAttachments .= $value . '; ';
                }
                else {
                    $mail->addAttachment($key, $value);
                    $debugAttachments .= $key . ' (' . $value .'); ';
                }
            }
        }
    }
    catch (Exception $e) {
        return $e->getMessage(); 
    }

    //Attempt to send via the primary host
    try {
        $mail->Host = SMTP_PRIMARY_HOST;
        $mail->Port = SMTP_PRIMARY_PORT;
        $mail->Username = SMTP_PRIMARY_USERNAME;
        $mail->Password = SMTP_PRIMARY_PASSWORD;

        if(empty(SMTP_PRIMARY_ENCRYPTION)) {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        }
        else {
            $mail->SMTPSecure = SMTP_PRIMARY_ENCRYPTION;
        }

        //throw new Exception('Force primary to fail');
        $mail->Send();
    }
    catch(Exception $e) {
        $failureStage = 1;
        $smtpDebug .= (!empty($smtpDebug) ? '[' . date('Y-m-s H:i:s') . '] ' : '') . $e->getMessage() . "\n";
    }

    //Attempt to send via the secondary host
    if($failureStage === 1) {
        try {
            $mail->Host = SMTP_SECONDARY_HOST;
            $mail->Port = SMTP_SECONDARY_PORT;
            $mail->Username = SMTP_SECONDARY_USERNAME;
            $mail->Password = SMTP_SECONDARY_PASSWORD;

            if(empty(SMTP_SECONDARY_ENCRYPTION)) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            }
            else {
                $mail->SMTPSecure = SMTP_SECONDARY_ENCRYPTION;
            }

            //throw new Exception('Force secondary to fail');
            $mail->Send();
        }
        catch(Exception $e) {
            $failureStage = 2;
            $smtpDebug .= (!empty($smtpDebug) ? '[' . date('Y-m-s H:i:s') . '] ' : '') . $e->getMessage() . "\n";
        }
    }

    //Log the mail to a file if it could not be sent
    if($failureStage === 2 || ($failureStage > 1 && !empty($smtpDebug))) {
        try {
            $filename = 'smtp_failures_' . date('Ymd');

            if(!is_dir(SMTP_DEBUG_DIR)) {
                mkdir(SMTP_DEBUG_DIR, 0775, true);
            }

            $fp = fopen(SMTP_DEBUG_DIR . $filename, 'a+');
            
            fwrite($fp, 
                $smtpDebug . "\n" .
                'To: ' . $debugTo . "\n" .
                'From: ' . $fromFriendly . ' <' . $from . '>' . "\n" . 
                'Subject: ' . $subject . "\n" .
                (!empty($debugAttachments) ? 'Attachments: ' . $debugAttachments . "\n" : '') . 
                'Body: ' . $body . "\n" .
                'Alt Body: ' . $htmlBody->getText() . "\n" .
                "\n--------------------\n\n"
            );

            fclose($fp);
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

    return true;
}