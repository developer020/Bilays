<?php
class PHP_Email_Form {
    public $to = '';
    public $from_name = '';
    public $from_email = '';
    public $subject = '';
    public $message = '';
    public $headers = '';
    public $ajax = false;
    public $cc = '';

    public function add_message($content, $label, $priority = 1) {
        $this->message .= "<strong>$label:</strong> $content<br>";
    }

    public function send() {
        if (empty($this->to) || empty($this->from_email) || empty($this->message)) {
            return "Error: Missing required fields!";
        }

        $this->headers = "From: " . $this->from_name . " <" . $this->from_email . ">\r\n";
        $this->headers .= "Reply-To: " . $this->from_email . "\r\n";
        if (!empty($this->cc)) {
            $this->headers .= "Cc: " . $this->cc . "\r\n";
        }
        $this->headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        if (mail($this->to, $this->subject, $this->message, $this->headers)) {
            return "Success: Email sent!";
        } else {
            return "Error: Email could not be sent!";
        }
    }
}
?>
