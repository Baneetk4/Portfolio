<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Composer autoload

// (reuse same validation and sanitization as earlier)
$name = substr(trim($_POST['name'] ?? ''),0,100);
$email = substr(trim($_POST['email'] ?? ''),0,200);
$subject = substr(trim($_POST['subject'] ?? ''),0,200);
$message = trim($_POST['message'] ?? '');
// validate as before...

$mail = new PHPMailer(true);

try {
    // SMTP server config
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';           // SMTP server
    $mail->SMTPAuth   = true;
    $mail->Username   = 'your.email@gmail.com';     // SMTP username
    $mail->Password   = 'your_app_password';        // APP password or SMTP password
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('no-reply@yourdomain.com', 'Website Contact');
    $mail->addAddress('you@yourdomain.com', 'Your Name'); // destination
    $mail->addReplyTo($email, $name);

    $mail->Subject = (!empty($subject) ? $subject : 'Website Contact Form') . ' — ' . $name;

    $body = "Name: $name\nEmail: $email\n\nMessage:\n$message\n";
    $body .= "\nIP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\nUA: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown');

    $mail->Body = $body;
    $mail->send();
    echo 'Message sent successfully';
} catch (Exception $e) {
    http_response_code(500);
    echo "Mailer Error: " . $mail->ErrorInfo;
}
