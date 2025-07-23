<!--
 // WEBSITE: https://themefisher.com
 // TWITTER: https://twitter.com/themefisher
 // FACEBOOK: https://www.facebook.com/themefisher
 // GITHUB: https://github.com/themefisher/
-->

<!-- 
THEME: Medic | Medical HTML Template
VERSION: 1.0.0
AUTHOR: Themefisher

HOMEPAGE: https://themefisher.com/products/medic-medical-template/
DEMO: https://demo.themefisher.com/themefisher/medic/
GITHUB: https://github.com/themefisher/Medic-Bootstrap-Medical-Template

WEBSITE: https://themefisher.com
TWITTER: https://twitter.com/themefisher
FACEBOOK: https://www.facebook.com/themefisher
-->

<!DOCTYPE html>
 
 
 
 <?php
// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Define the email address where you want to receive messages
    $to_email = "yonasdesta37@gmail.com"; // <-- IMPORTANT: Change this to your actual email address

    // Get form data (and sanitize them)
    $name = filter_var(trim($_POST["name"]), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $subject = filter_var(trim($_POST["subject"]), FILTER_SANITIZE_STRING);
    $message = filter_var(trim($_POST["message"]), FILTER_SANITIZE_STRING);

    // Basic validation
    if (empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($subject) || empty($message)) {
        echo "<p style='color:red; text-align:center;'>Please fill in all required fields and provide a valid email address.</p>";
        exit; // Stop script execution
    }

    // Construct the email body
    $email_body = "Name: " . $name . "\n";
    $email_body .= "Email: " . $email . "\n";
    $email_body .= "Subject: " . $subject . "\n\n";
    $email_body .= "Message:\n" . $message;

    // Set email headers
    $headers = "From: Biruk Transport <noreply@biruktransport.com>\r\n";
    $headers .= "Reply-To: " . $name . " <" . $email . ">\r\n";
    $headers .= "Content-type: text/plain; charset=UTF-8\r\n";

    // Attempt to send the email
    
}
?>
<?php
$form_message = ""; // Initialize message variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ... (your existing PHP code for processing form) ...

    if (mail($to_email, $subject, $email_body, $headers)) {
        $form_message = "<p style='color:green;'>Thank you! Your message has been sent successfully.</p>";
    } else {
        $form_message = "<p style='color:red;'>Sorry, there was an error sending your message. Please try again later.</p>";
    }
}
?>
<html lang="zxx">
<head>

 <title>Contact | Biruk Transport</title>

  <?php include 'header.php'; ?>

</head>


<body>
 

  

<!--header top-->

<!--header top-->

<!--Header Upper-->

<!--Header Upper-->


<!--Main Header-->

<!--End Main Header -->

<!--Page Title-->
<section class="page-title text-center" style="background-image:url(images/background/contact.jpg);">
    <div class="container">
        <div class="title-text">
            <h1>Contact</h1>
            <ul class="title-menu clearfix">
                <li>
                    <a href="index.php">home &nbsp;/</a>
                </li>
                <li>Contact</li>
            </ul>
        </div>
    </div>
</section>
<!--End Page Title-->

<section class="section contact">
  <!-- container start -->
  <div class="container">
    <div class="row">
      <div class="col-lg-4 col-md-5 ">
        <!-- address start -->
        <div class="address-block">
          <!-- Location -->
          <div class="media">
            <i class="far fa-map"></i>
            <div class="media-body">
              <h3>Location</h3>
              <p>Saris Road, Dawi Tower, 5th floor,  <br>Addis Ababa, Ethiopia</p>
            </div>
          </div>
          <!-- Phone -->
          <div class="media">
            <i class="fas fa-phone"></i>
            <div class="media-body">
              <h3>Phone</h3>
              <p>
                (+251)  911 31 4885
                <br>(+251)  913 73 7398
                <br>(+251)  925 24 9190
              </p>
            </div>
          </div>
          <!-- Email -->
          <div class="media">
            <i class="far fa-envelope"></i>
            <div class="media-body">
              <h3>Email</h3>
              <p>
                contact@biruktransport.com
                <br>support@biruktransport.com
              </p>
            </div>
          </div>
        </div>
        <!-- address end -->
      </div>
      <div class="col-lg-8 col-md-7">
        <div class="contact-form">
          <!-- contact form start -->
          <div id="cf-msg" class="form-message text-center">
              <?php echo $form_message; ?>
            </div>
          <form action="contact.php" class="row" id="contact-form" method="POST">
            <!-- name -->
            <div class="col-lg-6">
              <input type="text" name="name" class="form-control main" placeholder="Name" required>
            </div>
            <!-- email -->
            <div class="col-lg-6">
              <input type="email" name=email class="form-control main" placeholder="Email" required>
            </div>
            <!-- Subject -->
            <div class="col-lg-6">
              <input type="text" name="subject" class="form-control main" placeholder="Subject" required>
            </div>
            <!-- phone -->
            <div class="col-lg-6">
              <input type="text" class="form-control main" placeholder="Phone" required>
            </div>
            <!-- message -->
            <div class="col-lg-12">
              <textarea name="message" rows="10" class="form-control main" placeholder="Your message"></textarea>
            </div>
            <!-- submit button -->
            <div class="col-md-12 text-right">
              <button class="btn btn-style-one" type="submit">Send Message</button>
            </div>
          </form>
          <!-- contact form end -->
        </div>
      </div>
    </div>
  </div>
  <!-- container end -->
</section>
<!--====  End of Contact Form  ====-->

<section class="map">
  <!-- Google Map -->
  <div id="map">
   <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d63057.99271274911!2d38.764099!3d8.96067!3m2!1i1024!
   2i768!4f13.1!3m3!1m2!1s0x164b838994d314bf%3A0x7593bd3e0cbd78e!2sDawi%20Building%2C%20A1%2C%20Addis%20Ababa%201000%2C%
   20Ethiopia!5e0!3m2!1sen!2sus!4v1753219377021!5m2!1sen!2sus" width="100%" height="450" style="border:0;"
           allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
  </div>
</section>
<!--====  End of Google Map  ====-->

<!--footer-main-->
<?php include 'footer.php'; ?>
<!--End footer-main-->

<!-- scroll-to-top -->
<div id="back-to-top" class="back-to-top">
  <i class="fa fa-angle-up"></i>
</div>

</div>
<!--End pagewrapper-->


<!--Scroll to top-->
<div class="scroll-to-top scroll-to-target" data-target=".header-top">
  <span class="icon fa fa-angle-up"></span>
</div>


<!-- jquery -->
<script src="plugins/jquery.min.js"></script>
<!-- bootstrap -->
<script src="plugins/bootstrap/bootstrap.min.js"></script>
<!-- Slick Slider -->
<script src="plugins/slick/slick.min.js"></script>
<script src="plugins/slick/slick-animation.min.js"></script>
<!-- FancyBox -->
<script src="plugins/fancybox/jquery.fancybox.min.js" defer></script>
<!-- Google Map -->


<!-- jquery-ui -->
<script src="plugins/jquery-ui/jquery-ui.js" defer></script>
<!-- timePicker -->
<script src="plugins/timePicker/timePicker.js" defer></script>

<!-- script js -->
<script src="js/script.js"></script>
</body>

</html>