<?php
// file: Utils/Email.php
declare(strict_types=1);

namespace App\Utils;

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use App\Core\Config;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Email {
    private $mail;

    /**
     * Constructor to initialize PHPMailer.
     */
    public function __construct() {
        // Initialize PHPMailer
        $this->mail = new PHPMailer();

        // Configure SMTP settings
        $this->mail->isSMTP();
        $this->mail->Host = $_ENV['SMTP_HOST']; // SMTP server
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $_ENV['SMTP_USERNAME']; // SMTP username
        $this->mail->Password = $_ENV['SMTP_PASSWORD']; // SMTP password
        $this->mail->Port = $_ENV['SMTP_PORT']; // SMTP port

        // Set default sender
        $this->mail->setFrom($_ENV['EMAIL_FROM'], $_ENV['EMAIL_FROM_NAME']);
    }

    /**
     * Send a plain text email.
     *
     * @param string $to The recipient's email address.
     * @param string $subject The email subject.
     * @param string $body The email body (plain text).
     * @return bool Returns true if the email was sent successfully, false otherwise.
     */
    public function sendPlainText($to, $subject, $body) {
        try {
            $this->mail->addAddress($to);
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;
            $this->mail->isHTML(false); // Plain text email
            $this->mail->send();
            $this->clear();
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed: " . $this->mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Send an HTML email.
     *
     * @param string $to The recipient's email address.
     * @param string $subject The email subject.
     * @param string $htmlBody The email body (HTML).
     * @return bool Returns true if the email was sent successfully, false otherwise.
     */
    public function sendHtml($to, $subject, $htmlBody) {
        try {
            $this->mail->addAddress($to);
            $this->mail->Subject = $subject;
            $this->mail->Body = $htmlBody;
            $this->mail->isHTML(true); // HTML email
            $this->mail->AltBody = strip_tags($htmlBody); // Fallback plain text for non-HTML clients
            $this->mail->send();
            $this->clear();
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed: " . $this->mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Add an attachment to the email.
     *
     * @param string $filePath The path to the file to attach.
     * @return void
     */
    public function addAttachment($filePath) {
        $this->mail->addAttachment($filePath);
    }

    /**
     * Clear all recipients and attachments from the email.
     *
     * @return void
     */
    public function clear() {
        $this->mail->clearAddresses();
        $this->mail->clearAttachments();
    }

    /**
     * Send an account activation email.
     *
     * @param string $email The user's email
     * @param string $activationToken The activation token
     * @return bool Returns true if the email was sent successfully, false otherwise.
     */
    public static function sendActivationEmail(string $email, string $activationToken): bool
    {
        $emailInstance = new self(); // Create an instance of the Email class

        $domain = Config::get('domain');
        $scheme = Config::get('scheme');
        $activationLink = "$scheme://$domain/activate/{$activationToken}";
        
        $subject = "Activate Your Account";
        
        $body = "<h1>Welcome!</h1>
            <p>Please click the link below to activate your account:</p>
            <a href='$activationLink'>$activationLink</a>";
        
        return $emailInstance->sendHtml($email, $subject, $body);
    }

    /**
     * Send a password reset email.
     *
     * @param string $email The user's email
     * @param string $resetToken The password reset token
     * @return bool Returns true if the email was sent successfully, false otherwise.
     */
    public static function sendPasswordResetEmail(string $email, string $resetToken): bool
    {
        $emailInstance = new self(); // Create an instance of the Email class

        $domain = Config::get('domain');
        $scheme = Config::get('scheme');
        $resetLink = "$scheme://$domain/reset-password/{$resetToken}";

        $subject = "Password Reset Request";

        $body = "<h1>Password Reset</h1>
            <p>You requested a password reset. Please click the link below to reset your password:</p>
            <a href='$resetLink'>$resetLink</a>";

        return $emailInstance->sendHtml($email, $subject, $body);
    }
}