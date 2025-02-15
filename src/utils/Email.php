<?php
namespace App\Utils;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Email {
    private $mail;

    /**
     * Constructor to initialize PHPMailer.
     */
    public function __construct() {
        // Initialize PHPMailer
        $this->mail = new PHPMailer(true);

        // Configure SMTP settings
        $this->mail->isSMTP();
        $this->mail->Host = $_ENV['SMTP_HOST'] ?? 'smtp.example.com'; // SMTP server
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $_ENV['SMTP_USERNAME'] ?? 'your-email@example.com'; // SMTP username
        $this->mail->Password = $_ENV['SMTP_PASSWORD'] ?? 'your-password'; // SMTP password
        $this->mail->SMTPSecure = $_ENV['SMTP_ENCRYPTION'] ?? 'tls'; // Encryption (e.g., tls or ssl)
        $this->mail->Port = $_ENV['SMTP_PORT'] ?? 587; // SMTP port

        // Set default sender
        $this->mail->setFrom($_ENV['EMAIL_FROM'] ?? 'no-reply@example.com', $_ENV['EMAIL_FROM_NAME'] ?? 'Your Application');
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
     * @param object $user The user object containing email, first_name, and activation_token.
     * @return bool Returns true if the email was sent successfully, false otherwise.
     */
    public static function sendActivationEmail($user) {
        $email = new self(); // Create an instance of the Email class
        $activationLink = "http://yourdomain.com/index.php?token=" . urlencode($user->activation_token);
        $subject = "Activate Your Account";
        $body = "
            <h1>Welcome, {$user->first_name}!</h1>
            <p>Please click the link below to activate your account:</p>
            <a href='$activationLink'>$activationLink</a>
        ";
        return $email->sendHtml($user->email, $subject, $body);
    }

    /**
     * Send a password reset email.
     *
     * @param object $user The user object containing email and first_name.
     * @param string $resetToken The password reset token.
     * @return bool Returns true if the email was sent successfully, false otherwise.
     */
    public static function sendPasswordResetEmail($user, $resetToken) {
        $email = new self(); // Create an instance of the Email class
        $resetLink = "http://yourdomain.com/reset-password.php?token=" . urlencode($resetToken);
        $subject = "Password Reset Request";
        $body = "
            <h1>Hello, {$user->first_name}!</h1>
            <p>You requested a password reset. Please click the link below to reset your password:</p>
            <a href='$resetLink'>$resetLink</a>
        ";
        return $email->sendHtml($user->email, $subject, $body);
    }
}