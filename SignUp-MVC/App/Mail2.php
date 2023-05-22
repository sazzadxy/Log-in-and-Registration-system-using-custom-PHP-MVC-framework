<?php

namespace App;
use \PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class Mail2  
{
    // Send a message
    public static function send2($to, $subject, $text, $html) 
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPDebug = 0; // SMTP::DEBUG_SERVER; 
        $mail->Host = Config::MAIL_HOST;
        $mail->Username = Config::MAIL_USERNAME;
        $mail->Password = Config::MAIL_PASSWORD;
        $mail->SMTPSecure = Config::MAIL_SMTPSECURE;
        $mail->Port = Config::MAIL_PORT;

        if (!empty($to)) {
            $mail->setFrom('admin@mydomain.com', 'Mailer');
            $mail->FromName = "Admin";
            $mail->addReplyTo('admin@mydomain.com', 'Information');
            $mail->addAddress($to);
            // $mail->addCC('cc@example.com');
            // $mail->addBCC('bcc@example.com');

            //Attachments
            // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

            $mail->isHTML(true);
            $mail->Subject    = $subject;
            $mail->Body       = $html;
            $mail->AltBody    = $text;

            if (!$mail->send()) {
                return false;
            } else {
                return true;
            }

        }
    }
}
