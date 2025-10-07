<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

// Retrieve and sanitize form data
$name = filter_var($_POST['name'] ?? '', FILTER_SANITIZE_STRING);
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$subject = filter_var($_POST['subject'] ?? 'New Contact Form Submission', FILTER_SANITIZE_STRING);
$message = filter_var($_POST['message'] ?? '', FILTER_SANITIZE_STRING);

// Validation
$errors = [];
if (empty($name)) $errors[] = 'Name is required';
if (empty($email)) $errors[] = 'Email is required';
if (empty($message)) $errors[] = 'Message is required';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'messages' => $errors]);
    exit;
}

$mail = new PHPMailer(true);

try {
    // Basic configuration
    $mail->isMail();
    
    // Set sender and recipients
    $mail->setFrom($email, $name);
    $mail->addAddress('info@bilays.com', 'Bilays Info');
    $mail->addCC('zamzam@bilays.com');
    $mail->addReplyTo($email, $name);
    
    // Email content
    $mail->Subject = $subject;
    
    $mail->isHTML(true);
    $mail->Body = "
        <h2>New Contact Form Submission</h2>
        <p><strong>From:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <h3>Subject:</h3>
        <p>$subject</p>
        <h3>Message:</h3>
        <p>" . nl2br($message) . "</p>
    ";
    
    $mail->AltBody = "From: $name\nEmail: $email\nSubject: $subject\n\nMessage:\n$message";

    if ($mail->send()) {
        echo json_encode(['status' => 'success', 'message' => 'Your message has been sent. Thank you!']);
    } else {
        throw new Exception('Mail sending failed');
    }
} catch (Exception $e) {
    // Fallback to basic mail()
    $headers = "From: $name <$email>\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "CC: zamzam@bilays.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    $body = "From: $name\nEmail: $email\nSubject: $subject\n\nMessage:\n$message";

    if (mail('info@bilays.com', $subject, $body, $headers)) {
        echo json_encode(['status' => 'success', 'message' => 'Your message has been sent. Thank you!']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to send message. Please try again later.']);
    }
}
?>