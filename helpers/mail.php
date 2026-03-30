<?php
/**
 * Mail Helper
 * 
 * Handles sending emails for verification and password reset.
 * Supports two modes:
 *   - "log"  : Writes emails to storage/mail_log.txt (local development)
 *   - "smtp" : Sends real emails via PHP mail() or SMTP
 */

/**
 * Send an email. In "log" mode, writes to a file instead.
 */
function sendMail(string $to, string $subject, string $htmlBody): bool
{
    $method = $_ENV['MAIL_METHOD'] ?? 'log';

    if ($method === 'log') {
        return logMail($to, $subject, $htmlBody);
    }

    return smtpMail($to, $subject, $htmlBody);
}

/**
 * Log email to file (for local development)
 */
function logMail(string $to, string $subject, string $htmlBody): bool
{
    $logDir = ROOT_PATH . '/storage';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $logFile = $logDir . '/mail_log.txt';
    $separator = str_repeat('=', 60);
    $timestamp = date('Y-m-d H:i:s');

    // Strip HTML tags for the log (but keep the links readable)
    $plainBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));

    $entry = "\n{$separator}\n"
           . "DATE:    {$timestamp}\n"
           . "TO:      {$to}\n"
           . "SUBJECT: {$subject}\n"
           . "{$separator}\n"
           . "{$plainBody}\n"
           . "{$separator}\n";

    return file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX) !== false;
}

/**
 * Send email via PHP mail() with proper headers.
 * For production on cPanel shared hosting, this usually works out of the box.
 */
function smtpMail(string $to, string $subject, string $htmlBody): bool
{
    $fromAddress = $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@mimos.my';
    $fromName    = $_ENV['MAIL_FROM_NAME'] ?? 'Training Exam System';

    $headers  = "From: {$fromName} <{$fromAddress}>\r\n";
    $headers .= "Reply-To: {$fromAddress}\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

    return mail($to, $subject, $htmlBody, $headers);
}

/**
 * Build the base URL for email links.
 * Detects the current host and protocol automatically.
 */
function getBaseUrl(): string
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Detect the subdirectory (e.g., /training-exam-system)
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    // Go up one level if we're inside /admin
    if (basename($scriptDir) === 'admin') {
        $scriptDir = dirname($scriptDir);
    }
    $basePath = rtrim($scriptDir, '/\\');

    return "{$protocol}://{$host}{$basePath}";
}

/**
 * Send email verification link
 */
function sendVerificationEmail(string $email, string $fullName, string $token): bool
{
    $baseUrl = getBaseUrl();
    $verifyUrl = "{$baseUrl}/admin/verify.php?token=" . urlencode($token);

    $subject = 'Verify Your Email — ' . APP_NAME;

    $htmlBody = "
    <div style='font-family: Inter, Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
        <div style='background: #2563eb; color: #ffffff; padding: 24px; text-align: center; border-radius: 8px 8px 0 0;'>
            <h2 style='margin: 0;'>" . e(APP_NAME) . "</h2>
        </div>
        <div style='background: #ffffff; padding: 32px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 8px 8px;'>
            <p>Hi <strong>" . e($fullName) . "</strong>,</p>
            <p>Thank you for registering! Please verify your email address by clicking the button below:</p>
            <div style='text-align: center; margin: 32px 0;'>
                <a href='{$verifyUrl}' 
                   style='background: #2563eb; color: #ffffff; padding: 12px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; display: inline-block;'>
                   Verify Email Address
                </a>
            </div>
            <p style='color: #6b7280; font-size: 14px;'>Or copy and paste this link into your browser:</p>
            <p style='color: #2563eb; font-size: 14px; word-break: break-all;'>{$verifyUrl}</p>
            <hr style='border: none; border-top: 1px solid #e5e7eb; margin: 24px 0;'>
            <p style='color: #9ca3af; font-size: 12px;'>If you did not create an account, no further action is required.</p>
        </div>
    </div>";

    return sendMail($email, $subject, $htmlBody);
}

/**
 * Send password reset link
 */
function sendPasswordResetEmail(string $email, string $fullName, string $token): bool
{
    $baseUrl = getBaseUrl();
    $resetUrl = "{$baseUrl}/admin/reset_password.php?token=" . urlencode($token);

    $subject = 'Reset Your Password — ' . APP_NAME;

    $htmlBody = "
    <div style='font-family: Inter, Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
        <div style='background: #dc2626; color: #ffffff; padding: 24px; text-align: center; border-radius: 8px 8px 0 0;'>
            <h2 style='margin: 0;'>" . e(APP_NAME) . "</h2>
        </div>
        <div style='background: #ffffff; padding: 32px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 8px 8px;'>
            <p>Hi <strong>" . e($fullName) . "</strong>,</p>
            <p>We received a request to reset your password. Click the button below to set a new password:</p>
            <div style='text-align: center; margin: 32px 0;'>
                <a href='{$resetUrl}' 
                   style='background: #dc2626; color: #ffffff; padding: 12px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; display: inline-block;'>
                   Reset Password
                </a>
            </div>
            <p style='color: #6b7280; font-size: 14px;'>Or copy and paste this link into your browser:</p>
            <p style='color: #dc2626; font-size: 14px; word-break: break-all;'>{$resetUrl}</p>
            <p style='color: #6b7280; font-size: 14px;'>This link will expire in <strong>" . RESET_TOKEN_EXPIRY_MINUTES . " minutes</strong>.</p>
            <hr style='border: none; border-top: 1px solid #e5e7eb; margin: 24px 0;'>
            <p style='color: #9ca3af; font-size: 12px;'>If you did not request a password reset, no further action is required.</p>
        </div>
    </div>";

    return sendMail($email, $subject, $htmlBody);
}
