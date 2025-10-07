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
$phone = filter_var($_POST['phone'] ?? '', FILTER_SANITIZE_STRING);
$message = filter_var($_POST['message'] ?? '', FILTER_SANITIZE_STRING);

// Validation
$errors = [];
if (empty($name)) $errors[] = 'Name is required';
if (empty($email)) $errors[] = 'Email is required';
if (empty($phone)) $errors[] = 'Phone is required';
if (empty($message)) $errors[] = 'Message is required';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'messages' => $errors]);
    exit;
}

$mail = new PHPMailer(true);

try {
    $mail->isMail();
    
    // PROPER SENDER CONFIGURATION (using user's email)
    $mail->setFrom($email, $name); // Actual sender
    $mail->addReplyTo($email, $name); // For replies
    $mail->addAddress('info@bilays.com', 'Bilays Info'); // Recipient
    $mail->addCC('zamzam@bilays.com'); // CC
    
    $mail->Subject = 'New Quote Request: ' . $name;
    
    $mail->isHTML(true);
    $mail->Body = "
        <h2>New Quote Request</h2>
        <p><strong>From:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Phone:</strong> $phone</p>
        <h3>Message:</h3>
        <p>" . nl2br($message) . "</p>
    ";
    
    $mail->AltBody = "Name: $name\nEmail: $email\nPhone: $phone\n\nMessage:\n$message";

    // Instead of json_encode():
    if ($mail->send()) {
        http_response_code(200);
        echo "Your quote request has been sent successfully. Thank you!";
    } else {
        http_response_code(500);
        echo "Failed to send message. Please try again later.";
    }
    // if ($mail->send()) {
    //     echo json_encode(['status' => 'success', 'message' => 'Your quote request has been sent successfully. Thank you!']);
    // } else {
    //     throw new Exception('Mail sending failed');
    // }
} catch (Exception $e) {
    // Fallback using proper sender headers
    $headers = "From: $name <$email>\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "CC: zamzam@bilays.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    $body = "New Quote Request\n\nFrom: $name\nEmail: $email\nPhone: $phone\n\nMessage:\n$message";

    if (mail('info@bilays.com', 'New Quote Request: ' . $name, $body, $headers)) {
        echo json_encode(['status' => 'success', 'message' => 'Your quote request has been sent successfully. Thank you!']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to send message. Please try again later.']);
    }
}
?>