<?php
$to = "yonasdesta37@gmail.com";
$subject = "Test Email";
$message = "This is a test email sent from PHP.";
$headers = "From: yonnny1234@gmail.com"; // Optional, but recommended

if (mail($to, $subject, $message, $headers)) {
    echo "Email sent successfully!";
} else {
    echo "Email sending failed.";
}
?>