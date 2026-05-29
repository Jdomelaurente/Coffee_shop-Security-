<?php
// includes/mailer.php

// Manual PHPMailer include (using existing path from your project)
require_once dirname(__FILE__) . '/PHPMailer/Exception.php';
require_once dirname(__FILE__) . '/PHPMailer/PHPMailer.php';
require_once dirname(__FILE__) . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    /**
     * Send a verification email to a new user using PHPMailer (Gmail SMTP)
     */
    public static function sendVerificationEmail($email, $name, $token) {
        $verifyUrl = "http://" . $_SERVER['HTTP_HOST'] . "/verify_email.php?token=" . $token;
        
        $subject = "Verify Your Kalinga Coffee Account";
        
        $message = "
        <html>
        <head>
            <style>
                .email-container { font-family: 'Poppins', Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #3e2723; }
                .header { background: #6c4e31; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { border: 1px solid #d7ccc8; padding: 30px; border-top: none; border-radius: 0 0 10px 10px; line-height: 1.6; }
                .btn { display: inline-block; padding: 12px 25px; background: #6c4e31; color: #ffffff !important; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 20px; }
                .footer { font-size: 0.8rem; color: #8d6e63; text-align: center; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='header'>
                    <h1 style='color: white; margin: 0;'>KALINGA COFFEE</h1>
                </div>
                <div class='content'>
                    <h2>Welcome, $name!</h2>
                    <p>Thank you for joining our team. Your account has been successfully created. To ensure your security and activate your account features, please verify your email address by clicking the button below:</p>
                    <div style='text-align: center;'>
                        <a href='$verifyUrl' class='btn'>Verify Email Address</a>
                    </div>
                    <p>If the button doesn't work, you can copy and paste this link into your browser:</p>
                    <p style='font-size: 0.9rem; word-break: break-all; color: #6c4e31;'>$verifyUrl</p>
                    <p>Best Regards,<br>The Kalinga Coffee Management</p>
                </div>
                <div class='footer'>
                    &copy; " . date('Y') . " Kalinga Coffee - Masang Kape. All rights reserved.
                </div>
            </div>
        </body>
        </html>
        ";

        $mail = new PHPMailer(true);

        try {
            // Server settings (Mirroring forgot_password settings)
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'jdomelaurente@gmail.com';  
            $mail->Password   = 'uiwy uymv zksh dfor'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Recipients
            $mail->setFrom('jdomelaurente@gmail.com', 'Kalinga Coffee');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $message;

            $sent = $mail->send();
            
            // Log for debugging
            self::logEmail($email, $subject, $message, true);
            
            return true;
        } catch (Exception $e) {
            self::logEmail($email, $subject, $message, false, $mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Log email to a file for debugging/simulation
     */
    private static function logEmail($to, $subject, $message, $success, $error = '') {
        $logDir = dirname(__DIR__) . '/logs';
        if (!is_dir($logDir)) mkdir($logDir, 0777, true);
        
        $status = $success ? "SENT" : "FAILED";
        $logFile = $logDir . '/email_logs.log';
        $entry = "[" . date('Y-m-d H:i:s') . "] Status: $status | To: $to | Subject: $subject" . ($error ? " | Error: $error" : "") . "\n";
        $entry .= "Message Preview: " . substr(strip_tags($message), 0, 100) . "...\n";
        $entry .= "--------------------------------------------------\n";
        
        file_put_contents($logFile, $entry, FILE_APPEND);
    }
}
