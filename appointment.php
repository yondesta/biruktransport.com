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
<?php
// Database connection details - IMPORTANT: Replace with your actual credentials
define('DB_SERVER', 'localhost'); // Usually 'localhost'
define('DB_USERNAME', 'biruktzw_yonas'); // Your database username
define('DB_PASSWORD', 'Biruk@123'); // Your database password
define('DB_NAME', 'biruktzw_biruktransport'); // The database name you created

// Attempt to connect to MySQL database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn === false) {
    die("ERROR: Could not connect. " . $conn->connect_error);
}

$message = ''; // To store success or error messages


// Process form submission when form is posted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data and sanitize
    $appointtName = $conn->real_escape_string(trim($_POST['appname']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $phone = $conn->real_escape_string(trim($_POST['phone']));
    $appointsubject = $conn->real_escape_string(trim($_POST['subject']));
    $appoint_date = $conn->real_escape_string(trim($_POST['appdate']));
    $appmessage = $conn->real_escape_string(trim($_POST['message']));

  
    // Simple validation for text fields
    if (empty($appointtName) || empty($email) || empty($phone) || empty($appointsubject) || empty($appoint_date) || empty($appmessage)) {
        $message = '<div style="color: red;">All text fields are required!</div>';
    }
    // Only proceed with DB insert if no error messages accumulated
    else if (empty($message)) {
        // Prepare an insert statement
        // Added image_filename to the insert query
        $sql = "INSERT INTO appointment (appoint_name, phone, email, appoint_date, service_type,  message) VALUES (?, ?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            // Bind parameters
            $stmt->bind_param("ssssss", $appointtName, $phone, $email, $appoint_date, $appointsubject,  $appmessage);

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                $message = '<div style="color: green;">Thank you for contacting us your appointment is  ' . $appoint_date . '</div>';
                // Clear POST data to reset form
                $_POST = array();
            } else {
                $message = '<div style="color: red;">Error: Could not execute query. ' . $stmt->error . '</div>';
            }
            // Close statement
            $stmt->close();
        } else {
            $message = '<div style="color: red;">Error: Could not prepare query. ' . $conn->error . '</div>';
        }
    }
}

// Close connection before HTML output
$conn->close();
?>
<!DOCTYPE html>
<html lang="zxx">
<head>

  <!-- ** Basic Page Needs ** -->
  <meta charset="utf-8">
  <title>Appointment | Biruk Transport</title>

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
<section class="page-title text-center" style="background-image:url(images/background/appoint.jpg);">
    <div class="container">
        <div class="title-text">
            <h1>appointment</h1>
            <ul class="title-menu clearfix">
                <li>
                    <a href="index.php">home &nbsp;/</a>
                </li>
                <li>appointment</li>
            </ul>
        </div>
    </div>
</section>
<!--End Page Title-->

<!-- Section -->
<section class="section style-three pb-0">
  <div class="container">
    <div class="row">
      <div class="col-lg-6 order-1 order-lg-0">
        <div class="contact-area style-two pl-0 pr-0 pr-lg-4">
          <div class="section-title">
            <h3>Request <span>Appointment</span></h3>
          </div>
          <div class="message"><?php echo $message; ?></div>
          <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" id="contact-form" class="row">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <input class="form-control" type="text" name="appname" placeholder="Name" required>
                </div>
                <div class="form-group">
                  <input class="form-control" type="email" name="email" placeholder="Email" required>
                </div>
                <div class="form-group">
                  <select class="form-control" name="subject">
                    <option>Service Type</option>
                    <option>Import Transport</option>
                    <option>Export cargo</option>
                    <option>Local transport</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <input class="form-control" type="text" name="phone" placeholder="Phone" required>
                </div>
                <div class="form-group">
                  <input type="date" id="appdate" name="appdate" class="form-control" value="<?php echo isset($_POST['event_date']) ? htmlspecialchars($_POST['appdate']) : ''; ?>"  placeholder="Date"  id="datepicker" autocomplete="off" required>
                  <i class="fa fa-calendar" aria-hidden="true"></i>
                </div>
              </div>
              <div class="col-md-12">
                <div class="form-group">
                  <textarea class="form-control" name="message" placeholder="Your Message" required=""></textarea>
                </div>
                <div class="form-group text-center">
                  <button type="submit" class="btn-style-one">submit now</button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
      <div class="col-lg-6 mb-4 mb-lg-0 order-0 order-lg-1">
        <div class="appointment-image-holder ml-0 ml-lg-4">
          <figure>
            <img loading="lazy" class="w-100" src="images/background/appoint2.jpg" alt="Appointment">
          </figure>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- End Section -->

<!--team section-->
<section class="team-section section">
  <div class="container">
    <div class="section-title text-center">
      <h3>Meet Our Team
        <span>Members</span>
      </h3>
      
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-4 col-md-6">
        <div class="team-member">
          <img loading="lazy" src="images/team/team-1.jpg" alt="doctor" class="img-fluid">
          <div class="contents text-center">
            <h4>Biruk  neguse</h4>
            <p>General Manager<br>biruk.nigusse@biruktransport.com</p>
            <a href="mailto:biruk.nigusse@biruktransport.com" class="btn btn-main">Make Appointment</a>
          </div>
        </div>
      </div>
      <div class="col-lg-4 col-md-6">
        <div class="team-member">
          <img loading="lazy" src="images/team/team-2.jpg" alt="doctor" class="img-fluid">
          <div class="contents text-center">
            <h4>Asnake abebe</h4>
            <p>Chief Operation Manager<br>asnakeabebe@biruktransport.com</p>
            <a href="mailto:asnakeabebe@biruktransport.com" class="btn btn-main">Book Appointment</a>
          </div>
        </div>
      </div>
      <div class="col-lg-4 col-md-6">
        <div class="team-member">
          <img loading="lazy" src="images/team/team-3.jpg" alt="doctor" class="img-fluid">
          <div class="contents text-center">
            <h4>Temesgen taye</h4>
            <p>Administration and Marketing Manager<br>temesgentaye@biruktransport.com</p>
            <a href="mailto:temesgentaye@biruktransport.com" class="btn btn-main">Book Appointment</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<!--End team section-->

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
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCcABaamniA6OL5YvYSpB3pFMNrXwXnLwU" defer></script>
<script src="plugins/google-map/gmap.js" defer></script>

<!-- jquery-ui -->
<script src="plugins/jquery-ui/jquery-ui.js" defer></script>
<!-- timePicker -->
<script src="plugins/timePicker/timePicker.js" defer></script>

<!-- script js -->
<script src="js/script.js"></script>
</body>

</html>
