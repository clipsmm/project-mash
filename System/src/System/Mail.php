<?php
/**
 * Created by PhpStorm.
 * User: Oscar
 * Date: 7/5/2015
 * Time: 11:03 PM
 */

namespace System;

use PHPMailer;
class Mail {

    public static function Send(array $emails,$subject,$body,$from = null)
    {

        //Create a new PHPMailer instance
        $mail = new PHPMailer;

        $mail->isSMTP();

        $mail->SMTPDebug = 0;
        $mail->Debugoutput = 'html';
        $mail->Host = MAIL_HOST;

        $mail->Port = MAIL_PORT;

        $mail->SMTPSecure = MAIL_ENCRYPTION;

        //Whether to use SMTP authentication
        $mail->SMTPAuth = true;

        //Username to use for SMTP authentication - use full email address for gmail
        $mail->Username = MAIL_USERNAME;

        //Password to use for SMTP authentication
        $mail->Password = MAIL_PASSWORD;

        //Set who the message is to be sent from
        $mail->setFrom(MAIL_USERNAME, MAIL_NAME);

        //Set who the message is to be sent to
        foreach ($emails as $email){
            if (!filter_var($email,FILTER_VALIDATE_EMAIL)){
                dump("Invalid email supplied : $email");
            }
            $mail->addAddress($email);

        }


        //Set the subject line
        $mail->Subject = $subject;
        $mail->Body = $body;

        //Replace the plain text body with one created manually
        $mail->AltBody = $body;


        //send the message, check for errors
        if (!$mail->send()) {
            dump($mail->ErrorInfo);
        } else {
            return true;
        }



    }

}