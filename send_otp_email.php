<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/email_config.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

function sendStudyPalOtp(string $recipient, int $otp, string $subject, string $message): void
{
    if (str_contains(STUDYPAL_SMTP_USERNAME, 'your-email') || str_contains(STUDYPAL_SMTP_PASSWORD, 'your-16-character')) {
        throw new RuntimeException('SMTP is not configured. Update email_config.php with your SMTP account details.');
    }

    $mailer = new PHPMailer(true);
    try {
        $mailer->isSMTP();
        $mailer->Host = STUDYPAL_SMTP_HOST;
        $mailer->SMTPAuth = true;
        $mailer->Username = STUDYPAL_SMTP_USERNAME;
        $mailer->Password = STUDYPAL_SMTP_PASSWORD;
        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->Port = STUDYPAL_SMTP_PORT;
        $mailer->setFrom(STUDYPAL_MAIL_FROM, STUDYPAL_MAIL_FROM_NAME);
        $mailer->addAddress($recipient);
        $mailer->Subject = $subject;
        $mailer->Body = $message;
        $mailer->send();
    } catch (Exception $exception) {
        throw new RuntimeException('OTP email could not be sent: ' . $exception->getMessage(), 0, $exception);
    }
}
?>
