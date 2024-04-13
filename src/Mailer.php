<?php

namespace Edward144\PhpMailerFailover;

use Html2Text\Html2Text;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $to = [];
    private $subject;
    private $body;
    private $altBody;
    private $from;
    private $fromFriendly;
    private $attachments = [];

    private $failureStage = 0;
    private $smtpDebug = '';
    private $debugTo = '';
    private $debugAttachments = '';

    public function __construct() {
        if(defined('MAILER_FAILOVER_FROM') && !empty(MAILER_FAILOVER_FROM)) {
            $this->from = MAILER_FAILOVER_FROM;
        }

        if(defined('MAILER_FAILOVER_FROM_FRIENDLY') && !empty(MAILER_FAILOVER_FROM_FRIENDLY)) {
            $this->fromFriendly = MAILER_FAILOVER_FROM_FRIENDLY;
        }
    }

    public function send() {
        //Set the mail content
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->isHTML();
            $mail->SMTPAuth = true;

            $mail->SMTPDebug = defined('MAILER_FAILOVER_DEBUG_LEVEL') ? MAILER_FAILOVER_DEBUG_LEVEL : 2;
            $mail->Debugoutput = function($str, $level) {
                $this->smtpDebug .= $level . ': ' . $str . "\n";
            };

            $mail->Subject = $this->subject;
            $mail->Body = $this->body;
            $mail->AltBody = $this->altBody;
            $mail->SetFrom($this->from, $this->fromFriendly);

            foreach($this->to as $key => $value) {
                if(is_numeric($key)) {
                    $mail->AddAddress($value);
                    $this->debugTo .= $value . '; ';
                }
                else {
                    $mail->AddAddress($key, $value);
                    $this->debugTo .= $value . ' <' . $key . '>; ';
                }
            }

            if(!empty($this->attachments)) {
                foreach($this->attachments as $key => $value) {
                    if(is_numeric($key)) {
                        $mail->AddAttachment($value);
                        $this->debugAttachments .= $value . '; ';
                    }
                    else {
                        $mail->AddAttachment($key, $value);
                        $this->debugAttachments .= $key . ' (' . $value . '); ';
                    }
                }
            }
        }
        catch(Exception $e) {
            return $e->getMessage();
        }

        //Check that SMTP services have been defined
        try {
            if(defined('MAILER_FAILOVER_SMTP') && is_array(MAILER_FAILOVER_SMTP)) {
                $services = MAILER_FAILOVER_SMTP;
            }
            else {
                throw new Exception('Failed to locate SMTP services');
            }
        }
        catch(Exception $e) {
            return $e->getMessage();
        }

        //Attempt to send via each SMTP service
        foreach($services as $service) {
            try {
                $mail->Host = $service['host'];
                $mail->Port = $service['port'];
                $mail->Username = $service['username'];
                $mail->Password = $service['password'];
                
                if(empty($service['encryption'])) {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                }
                else {
                    $mail->SMTPSecure = $service['encryption'];
                }
                
                if($mail->Send()) {
                    break;
                }
            }
            catch(Exception $e) {
                $this->failureStage++;
                $this->smtpDebug .= (!empty($smtpDebug) ? '[' . date('Y-m-d H:i:s') . '] ' : '') . $e->getMessage() . "\n";
            }
        }

        //Log the mail if all services fail
        if($this->failureStage >= count($services) || ($this->failureStage > 0 && !empty($this->smtpDebug))) {
            try {
                $filename = 'smtp_failures_' . date('Ymd');

                if(!is_dir(MAILER_FAILOVER_DEBUG_LOCATION)) {
                    mkdir(MAILER_FAILOVER_DEBUG_LOCATION, 0775, true);
                }

                $fp = fopen(MAILER_FAILOVER_DEBUG_LOCATION . $filename, 'a+');

                if(!$fp) {
                    throw new Exception('Failed to store mail');
                }

                fwrite($fp, 
                    $this->smtpDebug . "\n" . 
                    'To: ' . $this->debugTo . "\n" .
                    'From: ' . $this->fromFriendly . ' <' . $this->from . '>' . "\n" .
                    'Subject: ' . $this->subject . "\n" . 
                    (!empty($this->debugAttachments) ? 'Attachments: ' . $this->debugAttachments . "\n" : '') .
                    'Body: ' . $this->body . "\n" .
                    'Alt Body: ' . $this->altBody . "\n" .
                    "\n--------------------\n\n"
                );

                fclose($fp);
            }
            catch(Exception $e) {
                return $e->getMessage();
            }
        }

        if($this->failureStage < count($services)) {
            return true;
        }
         
        return false;
    }

    public function addTo($to, $friendly = null) {
        if(!is_array($to) && !empty($friendly)) {
            $this->to[$to] = $friendly; 
        }
        elseif(!is_array($to)) {
            $this->to[] = $to;
        }
        else {
            foreach($to as $key => $value) {
                if(!is_numeric($key)) {
                    $this->to[$key] = $value;
                }
                else {
                    $this->to[] = $value;
                }
            }
        }
    }

    public function subject($subject) {
        $this->subject = $subject;
    }

    //HTML body
    public function body($body, $generatePlaintext = true) {
        $this->body = $body;

        if($generatePlaintext === true) {
            $htmlBody = new Html2Text($body);

            $this->altBody = $htmlBody->getText();
        }
    }

    //Plaintext body
    public function altBody($altBody) {
        if($altBody != strip_tags($altBody)) {
            $htmlBody = new Html2Text($altBody);

            $this->altBody = $htmlBody->getText();
        }
        else {
            $this->altBody = $altBody;
        }
    }

    public function addAttachment($attachment, $filename = null) {
        if(!is_array($attachment) && !empty($filename)) {
            $this->attachments[$attachment] = $filename; 
        }
        elseif(!is_array($attachment)) {
            $this->attachments[] = $attachment;
        }
        else {
            foreach($attachment as $key => $value) {
                if(!is_numeric($key)) {
                    $this->attachments[$key] = $value;
                }
                else {
                    $this->attachments[] = $value;
                }
            }
        }
    }

    public function from($from, $friendly = null) {
        $this->from = $from;
        $this->fromFriendly = $friendly;
    }
}