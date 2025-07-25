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

$events = []; // Initialize an empty array to store events

// SQL query to fetch events, ordered by event_date (upcoming first)
// You might add WHERE event_date >= CURDATE() to only show future events
$sql = "SELECT id, event_name, event_detail, event_date, category FROM blog ORDER BY event_date DESC";

if ($result = $conn->query($sql)) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
        $result->free(); // Free result set
    }
} else {
    echo "ERROR: Could not able to execute $sql. " . $conn->error;
}

// Close connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="zxx">
<head>

  <title>Blog | Biruk Transport</title>

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
<section class="page-title text-center" style="background-image:url(images/blog/blog.jpg);">
    <div class="container">
        <div class="title-text">
            <h1>Blog</h1>
            <ul class="title-menu clearfix">
                <li>
                    <a href="index.php">home &nbsp;/</a>
                </li>
                <li>Blog</li>
            </ul>
        </div>
    </div>
</section>
<!--End Page Title-->

<!-- Contact Section -->
<section class="blog-section style-four section">
  <div class="container">
    <div class="row">
      <div class="col-lg-9">
        <div class="left-side">
          <div class="item-holder">
            <div class="image-box">     
              <?php if (!empty($events)): ?>
            <div class="event-list content-text" >
                <?php foreach ($events as $event): ?>
                    <div class="event-card">
                        <a href="event_detail.php?id=<?php echo $event['id']; ?>"><h3><?php echo htmlspecialchars($event['event_name']); ?></h3></a>
                        <div class="meta">
                            <strong>Date:</strong> <?php echo date('F j, Y', strtotime($event['event_date'])); ?> |
                            <strong>Category:</strong> <?php echo htmlspecialchars($event['category']); ?>
                        </div>
                        <p>
                            <?php
                            // Display a snippet of the detail
                            $snippet = strip_tags($event['event_detail']); // Remove any HTML for snippet
                            if (strlen($snippet) > 150) {
                                $snippet = substr($snippet, 0, 150) . '...';
                            }
                            echo htmlspecialchars($snippet);
                            ?>
                        </p>
                        <a href="event_detail.php?id=<?php echo $event['id']; ?>" class="btn-style-one">Read More</a><hr>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="no-events">No events found at the moment. Please check back later!</p>
        <?php endif; ?>
    </div>
    </div>
        </div>
                
          <div class="styled-pagination">
            <ul>
              <li><a class="prev" href="blog.php"><span class="fas fa-angle-left" aria-hidden="true"></span></a></li>
              <li><a href="blog.php" class="active">1</a></li>
              <li><a href="blog.php">2</a></li>
              <li><a href="blog.php">3</a></li>
              <li><a class="next" href="blog.php"><span class="fas fa-angle-right" aria-hidden="true"></span></a></li>
            </ul>
          </div>
        </div>
      

      <div class="col-lg-3">
        <div class="right-side">
          <div class="text-title">
            <h6>Search</h6>
          </div>
          <div class="search-box">
            <form method="post" action="index.php">
              <div class="input-group">
                <input class="form-control" type="search" name="search" placeholder="Enter to Search" required="" autocomplete="off">
              </div>
            </form>
          </div>
          <div class="categorise-menu">
            <div class="text-title">
              <h6>Categories</h6>
            </div>
            <ul class="categorise-list">
              <li><a href="blog.php">Alumni <span>(20)</span></a></li>
              <li><a href="blog.php">Psycology <span>(4)</span></a></li>
              <li><a href="blog.php">Sonogram <span>(2)</span></a></li>
              <li><a href="blog.php">x-ray <span>(9)</span></a></li>
              <li><a href="blog.php">Dental <span>(2)</span></a></li>
            </ul>
          </div>
          
        </div>
      </div>
    </div>
  </div>
</section>
<!-- End Contact Section -->

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
