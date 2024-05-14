<?php
require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../functions/database.php";
use PHPMailer\PHPMailer\PHPMailer;

function sendMail($to, $subject, $body) : bool
{
    $db = db();
    $mail_host = $db->querySingle("SELECT value FROM config WHERE key = 'mail_host'");
    $mail_address = $db->querySingle("SELECT value FROM config WHERE key = 'mail_address'");
    $mail_pswd = $db->querySingle("SELECT value FROM config WHERE key = 'mail_pswd'");

    if (!empty($mail_host) && !empty($mail_address) && !empty($mail_pswd) && PHPMailer::validateAddress($to)) {
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->setLanguage("en", "../../vendor/phpmailer/phpmailer/language");
        $mail->CharSet = PHPMailer::CHARSET_UTF8;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->SMTPAuth = true;
        $mail->Host = $mail_host;
        $mail->Username = $mail_address;
        $mail->Password = $mail_pswd;
        $mail->setFrom($mail_address, "NPMplus");
        $mail->addAddress($to);
        $mail->Subject = "[NPMplus] " . $subject;
        $mail->Body = $body;
        return $mail->send();
    } else {
        return false;
    }
}

function validateEmail($email) : bool
{
    return PHPMailer::validateAddress($email);
}
