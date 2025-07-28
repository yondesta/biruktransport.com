<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables
$errors = [];
$success = '';
$name = $email = $message = $subject = $phone = $company = '';

// Form submission handling
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate inputs
    $name = sanitizeInput($_POST["name"] ?? '');
    $email = sanitizeInput($_POST["email"] ?? '');
    $phone = sanitizeInput($_POST["phone"] ?? '');
    $company = sanitizeInput($_POST["company"] ?? '');
    $subject = sanitizeInput($_POST["subject"] ?? '');
    $message = sanitizeInput($_POST["message"] ?? '');

    // Validation
    if (empty($name)) {
        $errors['name'] = "Name is required";
    } elseif (!preg_match("/^[a-zA-Z ]*$/", $name)) {
        $errors['name'] = "Only letters and spaces allowed";
    }

    if (empty($email)) {
        $errors['email'] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format";
    }

    if (empty($subject)) {
        $errors['subject'] = "Subject is required";
    }

    if (empty($message)) {
        $errors['message'] = "Message is required";
    }

    // If no errors, send email
    if (empty($errors)) {
        require 'PHPMailer/src/Exception.php';
        require 'PHPMailer/src/PHPMailer.php';
        require 'PHPMailer/src/SMTP.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.biruktransport.com'; // Your SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'info@test.biruktransport.com'; // SMTP username
            $mail->Password = 'Test@123.Info'; // SMTP password
            $mail->SMTPSecure = 'ssl'; // Encryption: 'tls' or 'ssl'
            $mail->Port = 465; // TCP port

            // Recipients
            $mail->setFrom('noreply@biruktransport.com', 'Website Contact Form');
            $mail->addAddress('info@biruktransport.com'); // Recipient
            $mail->addReplyTo($email, $name);

            // Content
            $mail->isHTML(false);
            $mail->Subject = "Contact Form: $subject";
            $mail->Body = "Name: $name\n" .
                         "Email: $email\n" .
                         "Phone: $phone\n" .
                         "Company: $company\n\n" .
                         "Message:\n$message";

            $mail->send();
            $success = "Your message has been sent successfully!";
            
            // Clear form fields
            $name = $email = $message = $subject = $phone = $company = '';
        } catch (Exception $e) {
            $errors['mail'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>