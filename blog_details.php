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

$event = null; // Initialize event variable
$comments = []; // Initialize comments array
$comment_message = ''; // To store messages related to comment submission

// --- Handle Comment Submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_comment'])) {
    $event_id = filter_var($_POST['event_id'], FILTER_VALIDATE_INT);
    $commenter_name = $conn->real_escape_string(trim($_POST['commenter_name']));
    $commenter_email = $conn->real_escape_string(trim($_POST['commenter_email']));
    $comment_text = $conn->real_escape_string(trim($_POST['comment_text']));

    if (!$event_id || empty($commenter_name) || empty($comment_text)) {
        $comment_message = '<div style="color: red;">Please fill in all required comment fields.</div>';
    } else {
        // Prepare an insert statement for comments
        $sql_insert_comment = "INSERT INTO event_comments (event_id, commenter_name, commenter_email, comment_text) VALUES (?, ?, ?, ?)";
        if ($stmt_comment = $conn->prepare($sql_insert_comment)) {
            $stmt_comment->bind_param("isss", $event_id, $commenter_name, $commenter_email, $comment_text);
            if ($stmt_comment->execute()) {
                $comment_message = '<div style="color: green;">Your comment has been submitted successfully!</div>';
                // Redirect to self to prevent form resubmission and clear POST data
                header("Location: event_detail.php?id=" . $event_id . "&comment_status=success");
                exit();
            } else {
                $comment_message = '<div style="color: red;">Error submitting comment: ' . $stmt_comment->error . '</div>';
            }
            $stmt_comment->close();
        } else {
            $comment_message = '<div style="color: red;">Error preparing comment statement: ' . $conn->error . '</div>';
        }
    }
}

// Display success message if redirected after successful comment submission
if (isset($_GET['comment_status']) && $_GET['comment_status'] == 'success') {
    $comment_message = '<div style="color: green;">Your comment has been submitted successfully!</div>';
}


// --- Fetch Event Details ---
if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
    $event_id = $conn->real_escape_string(trim($_GET['id']));

    // Fetch event details
    $sql_event = "SELECT id, event_name, event_detail, event_date, category, image_filename, created_at FROM blog WHERE id = ? order by event_date desc";
    if ($stmt_event = $conn->prepare($sql_event)) {
        $stmt_event->bind_param("i", $event_id);
        if ($stmt_event->execute()) {
            $result_event = $stmt_event->get_result();
            if ($result_event->num_rows == 1) {
                $event = $result_event->fetch_assoc();
            } else {
                echo "<p style='text-align:center; color:red;'>No event found with the specified ID.</p>";
            }
        } else {
            echo "ERROR: Could not execute event query. " . $stmt_event->error;
        }
        $stmt_event->close();
    } else {
        echo "ERROR: Could not prepare event query. " . $conn->error;
    }

    // --- Fetch Comments for this Event ---
    if ($event) { // Only fetch comments if an event was found
        $sql_comments = "SELECT commenter_name, comment_text, created_at FROM event_comments WHERE event_id = ? ORDER BY created_at DESC";
        if ($stmt_comments = $conn->prepare($sql_comments)) {
            $stmt_comments->bind_param("i", $event_id);
            if ($stmt_comments->execute()) {
                $result_comments = $stmt_comments->get_result();
                while ($row_comment = $result_comments->fetch_assoc()) {
                    $comments[] = $row_comment;
                }
            } else {
                echo "ERROR: Could not execute comments query. " . $stmt_comments->error;
            }
            $stmt_comments->close();
        } else {
            echo "ERROR: Could not prepare comments query. " . $conn->error;
        }
    }
} else {
    echo "<p style='text-align:center; color:red;'>No event ID provided. Please select an event from the <a href='events.php'>events list</a>.</p>";
}

// Close connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="zxx">
<head>

  <title>Detail | Biruk Transport</title>

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
<section class="page-title text-center" style="background-image:url(images/background/3.jpg);">
    <div class="container">
        <div class="title-text">
            <h1>Blog Details</h1>
            <ul class="title-menu clearfix">
                <li>
                    <a href="index.php">home &nbsp;/</a>
                </li>
                <li>Blog Details</li>
            </ul>
        </div>
    </div>
</section>
<!--End Page Title-->

<!-- Contact Section -->
<section class="blog-section section style-four style-five">
  <div class="container">
    <div class="row">
      <div class="col-lg-9">
        <div class="left-side">
          <div class="item-holder">
            
            <div class="content-text">
        <?php if ($event): ?>
            <h1><?php echo htmlspecialchars($event['event_name']); ?></h1>
            <div class="meta">
                <strong>Date:</strong> <?php echo date('F j, Y', strtotime($event['event_date'])); ?> |
                <strong>Category:</strong> <?php echo htmlspecialchars($event['category']); ?> |
                <span title="Date Added">Added On: <?php echo date('F j, Y', strtotime($event['created_at'])); ?></span>
            </div>

            <?php if (!empty($event['image_filename'])): ?>
                <img src="images/uploads/<?php echo htmlspecialchars($event['image_filename']); ?>"
                     alt="<?php echo htmlspecialchars($event['event_name']); ?>"
                     class="event-image">
            <?php endif; ?>

            <div class="event-detail">
                <p><?php echo nl2br(htmlspecialchars($event['event_detail'])); ?></p>
            </div>

            <div class="comments-section">
              
                <h2>Comments</h2><hr>
                <?php if (!empty($comments)): ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment">
                            <strong><?php echo htmlspecialchars($comment['commenter_name']); ?></strong>
                            <span class="comment-date"><?php echo date('F j, Y, g:i a', strtotime($comment['created_at'])); ?></span>
                            <p><?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-comments">No comments yet. Be the first to comment!</p>
                <?php endif; ?>
            </div>

            <div class="comments-area">
                <h3>Leave a Comment</h3>
                <div class="message"><?php echo $comment_message; ?></div>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . htmlspecialchars($event['id']); ?>" method="post">
                    <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event['id']); ?>">

                    <label for="commenter_name">Your Name:</label>
                    <input type="text" id="commenter_name" name="commenter_name" value="<?php echo isset($_POST['commenter_name']) ? htmlspecialchars($_POST['commenter_name']) : ''; ?>" required>

                    <label for="commenter_email">Your Email (optional):</label>
                    <input type="email" id="commenter_email" name="commenter_email" value="<?php echo isset($_POST['commenter_email']) ? htmlspecialchars($_POST['commenter_email']) : ''; ?>">

                    <label for="comment_text">Your Comment:</label>
                    <textarea id="comment_text" name="comment_text" required><?php echo isset($_POST['comment_text']) ? htmlspecialchars($_POST['comment_text']) : ''; ?></textarea>

                    <button type="submit" name="submit_comment">Post Comment</button>
                </form>
            </div>

            <a href="blog.php" class="back-link">← Back to All Events</a>
        <?php endif; ?>
    </div>          
         
          </div>
        </div>
        <div class="comments-area">
          <div class="sec-title">
            <h6>Comments (3)</h6>
          </div>
          <div class="comment">
            <div class="image-holder">
              <figure>
                <img loading="lazy" src="images/blog/1.png" alt="">
              </figure>
            </div>
            <div class="image-text">
              <h6>Jacky Chan<span>April 06, 2020</span></h6>
              <h5><a href="blog-details.php">
                <i class="fa fa-reply-all" aria-hidden="true"></i>Comment Back
              </a></h5>
              <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Aliquam sequi praesentium, veritatis nulla
                quam at exercitationem? Nesciunt molestiae neque modi.</p>
            </div>
          </div>
          <div class="comment reply-comment">
            <div class="image-holder">
              <figure>
                <img loading="lazy" src="images/blog/2.png" alt="">
              </figure>
            </div>
            <div class="image-text">
              <h6>Robben Oti<span>April 07, 2020</span></h6>
              <h5><a href="blog-details.php">
                <i class="fa fa-reply-all" aria-hidden="true"></i>Comment Back
              </a></h5>
              <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Facilis, a! Lorem ipsum dolor, sit amet
                consectetur adipisicing elit. Excepturi, obcaecati!</p>
            </div>
          </div>
          <div class="comment">
            <div class="image-holder">
              <figure>
                <img loading="lazy" src="images/blog/3.png" alt="">
              </figure>
            </div>
            <div class="image-text">
              <h6>Chang Lingma<span>May 12, 2020</span></h6>
              <h5><a href="blog-details.php">
                <i class="fa fa-reply-all" aria-hidden="true"></i>Comment Back
              </a></h5>
              <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Molestiae possimus suscipit esse quia
                sapiente obcaecati consequuntur dolor sequi adipisci unde!</p>
            </div>
          </div>
        </div>
        <div class="form-area">
          <div class="sec-title">
            <h6>Leave A Comment</h6>
          </div>
          <form id="contact_form" name="contact_form" class="default-form">
            <div class="row">
              <div class="col-lg-12">
                <div class="form-group">
                  <textarea name="form_message" class="form-control textarea" placeholder="Your Review" required></textarea>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <input type="text" name="form_name" class="form-control" placeholder="Your Name" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <input type="email" name="form_email" class="form-control email" placeholder="Your Email" required>
                </div>
              </div>
              <div class="col-lg-12">
                <div class="form-group form-bottom">
                  <button class="btn-style-one" type="submit">Comment</button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
      <div class="col-lg-3">
        <div class="right-side">
          <div class="text-title">
            <h6>Search</h6>
          </div>
          <div class="search-box">
            <form method="post" action="index.php">
              <input class="form-control" type="search" name="search" placeholder="Enter to Search" required="">
            </form>
          </div>
          <div class="categorise-menu">
            <div class="text-title">
              <h6>Categories</h6>
            </div>
            <ul class="categorise-list">
              <li><a href="blog.php">Pulmonary <span>(12)</span></a></li>
              <li><a href="blog.php">Neurology <span>(22)</span></a></li>
              <li><a href="blog.php">X - Ray <span>(18)</span></a></li>
              <li><a href="blog.php">Cardiogram <span>(32)</span></a></li>
              <li><a href="blog.php">Diagnostic <span>(21)</span></a></li>
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
