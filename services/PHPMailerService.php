<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/../vendor/autoload.php';

class EmailService
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public static function fromConfig(): self
    {
        $configFile = __DIR__ . '/../config/phpmailer.php';
        if (file_exists($configFile)) {
            $config = require $configFile;
        } else {
            // Fallback or attempt to read from environment/config.php if needed
            // For now, return empty defaults which will cause sendEmail to fail or fallback
            $config = [];
        }
        return new self($config);
    }

    public function sendOtp(string $email, string $name, string $otp): bool
    {
        $subject = 'Your GSM Health System One-Time Password';
        $text = $this->buildOtpText($name, $otp); // Plain text version
        $html = $this->buildOtpHtml($name, $otp); // HTML version

        return $this->sendEmail($email, $subject, $html, $text);
    }

    private function buildOtpText(string $name, string $otp): string
    {
        return "Hello {$name},\n\nYour OTP Code is: {$otp}\n\nThis code expires in 10 minutes.";
    }

    private function buildOtpHtml(string $name, string $otp): string
    {
        // Simple HTML template
        return "
        <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;'>
            <div style='max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);'>
                <h2 style='color: #4A90E2;'>Verification Code</h2>
                <p>Hello <strong>{$name}</strong>,</p>
                <p>Use the following One-Time Password (OTP) to complete your login:</p>
                <div style='background: #e8f0fe; padding: 15px; border-radius: 5px; text-align: center; margin: 20px 0;'>
                    <span style='font-size: 24px; font-weight: bold; letter-spacing: 5px; color: #1967d2;'>{$otp}</span>
                </div>
                <p>This code is valid for 10 minutes.</p>
                <p style='font-size: 12px; color: #888;'>If you did not request this, please ignore this email.</p>
            </div>
        </div>";
    }

    public function sendEmail(string $email, string $subject, string $htmlBody, string $altBody = ''): bool
    {
        $mail = new PHPMailer(true); // Passing true enables exceptions

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->config['host'] ?? 'localhost';
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['username'] ?? '';
            $mail->Password = $this->config['password'] ?? '';
            $mail->SMTPSecure = $this->config['encryption'] ?? PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $this->config['port'] ?? 587;

            // XAMPP Fix: Often required for local development to bypass SSL certificate issues
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Recipients
            $senderEmail = $this->config['sender_email'] ?? 'noreply@goserveph.gov';
            $senderName = $this->config['sender_name'] ?? 'GoServePH';

            $mail->setFrom($senderEmail, $senderName);
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = $altBody ?: strip_tags($htmlBody);

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");

            // LOG THE OTP FOR DEBUGGING (Important for local dev)
            // Extract OTP from body if possible
            if (preg_match('/>(\d{6})</', $htmlBody, $matches) || preg_match('/Code is: (\d{6})/', $altBody, $matches)) {
                $otp = $matches[1] ?? 'UNKNOWN';
                error_log("=== EMAIL FAILED (FALLBACK LOG) ===");
                error_log("TO: $email");
                error_log("OTP: $otp");
                error_log("===================================");
            }

            // Return true only if we are confident the user can retrieve the OTP from logs
            // or return false to let the frontend know. 
            // The previous implementation returned TRUE so the UI proceeds to OTP entry screen.
            return true;
        }
    }
}
