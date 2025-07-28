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
        $sql_insert_comment = "INSERT INTO event_comments (event_id, comment_author, comment_email, comment_content) VALUES (?, ?, ?, ?)";
        if ($stmt_comment = $conn->prepare($sql_insert_comment)) {
            $stmt_comment->bind_param("isss", $event_id, $commenter_name, $commenter_email, $comment_text);
            if ($stmt_comment->execute()) {
                $comment_message = '<div style="color: green;">Your comment has been submitted successfully!</div>';
                // Redirect to self to prevent form resubmission and clear POST data
                header("Location: blog_details.php?id=" . $event_id . "&comment_status=success");
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

// --- Get Total Number of Events (for pagination calculation, considering category AND search filter) ---
$total_events_sql = "SELECT COUNT(id) AS total_count FROM blog";
$total_events_params = [];
$total_events_types = "";
$where_clauses = [];

if (!empty($selected_category)) {
    $where_clauses[] = "category = ?";
    $total_events_params[] = $selected_category;
    $total_events_types .= "s";
}
if (!empty($search_query)) {
    $where_clauses[] = "(event_name LIKE ? OR event_detail LIKE ?)";
    $total_events_params[] = "%" . $search_query . "%";
    $total_events_params[] = "%" . $search_query . "%";
    $total_events_types .= "ss"; // two 's' for two LIKE parameters
}

if (!empty($where_clauses)) {
    $total_events_sql .= " WHERE " . implode(" AND ", $where_clauses);
}

if ($stmt_total = $conn->prepare($total_events_sql)) {
    if (!empty($total_events_params)) {
        // Correct way to bind parameters when using call_user_func_array with references
        $bind_total_params = [];
        $bind_total_params[] = &$total_events_types;
        foreach ($total_events_params as $key => &$value) {
            $bind_total_params[] = &$value;
        }
        call_user_func_array([$stmt_total, 'bind_param'], $bind_total_params);
    }
    $stmt_total->execute();
    $total_events_result = $stmt_total->get_result();
    $total_events_row = $total_events_result->fetch_assoc();
    $total_events = $total_events_row['total_count'];
    $stmt_total->close();
} else {
    echo "ERROR: Could not prepare total events query. " . $conn->error;
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
        $sql_comments = "SELECT comment_author, comment_content, comment_date FROM event_comments WHERE event_id = ? ORDER BY comment_date DESC";
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
    echo "<p style='text-align:center; color:red;'>No event ID provided. Please select an event from the <a href='blog.php'>events list</a>.</p>";
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
<section class="service-tab-section section">
  <div class="outer-box clearfix">
    <div class="container">
      <div class="service-box tab-pane fade show active" id="dormitory">
       <div class="row">
        <div class="col-lg-6">
             <?php if (!empty($event['image_filename'])): ?>
                    <img src="images/uploads/<?php echo htmlspecialchars($event['image_filename']); ?>"
                         alt="<?php echo htmlspecialchars($event['event_name']); ?>"
                         class="event-image">
                <?php endif; ?>
        </div>
        <div class="col-lg-6">
          <div class="contents">
            <div class="section-title">     
              <h4> <?php if ($event): ?>
              <?php echo htmlspecialchars($event['event_name']); ?></h4>
            </div>
            
                <div class="text">
                    <strong>Date:</strong> <?php echo date('F j, Y', strtotime($event['event_date'])); ?> |
                    <strong>Category:</strong> <?php echo htmlspecialchars($event['category']); ?> |
                    <span title="Date Added">Added On: <?php echo date('F j, Y', strtotime($event['created_at'])); ?></span>
                </div>
                
            <div class="text">
                <p><?php echo nl2br(htmlspecialchars($event['event_detail'])); ?></p><hr>
            </div>
          </div>
        </div>
        <div class="col-lg-3">
          &nbsp;
        </div>
        <div class="col-lg-6">
            <div class="comments-section">        
               <hr> <h2>Comments</h2><hr>
                <?php if (!empty($comments)): ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment">
                            <h6><strong><?php echo htmlspecialchars($comment['comment_author']); ?></strong>
                            <span><?php echo date('F j, Y, g:i a', strtotime($comment['comment_date'])); ?></span></h6>
                            <p><?php echo nl2br(htmlspecialchars($comment['comment_content'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-comments">No comments yet. Be the first to comment!</p>
                <?php endif; ?>
            </div>
        
            <div class="comments-area">
                <h3>Leave a Comment</h3>
                <div class="message"><?php echo $comment_message; ?></div>
            <div class="form-area">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . htmlspecialchars($event['id']); ?>" class="default-form" method="post">
                <div class="row">
                  <div class="col-md-5">
                    <div class="form-group">
                      <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event['id']); ?>">
                       
                      <input type="text" class="form-control" placeholder="Your Name" id="commenter_name" name="commenter_name" value="<?php echo isset($_POST['commenter_name']) ? htmlspecialchars($_POST['commenter_name']) : ''; ?>" required>
                    </div>
                  </div>
                <div class="col-md-7">
                  <div class="form-group">   
                    <input type="email" id="commenter_email" class="form-control email" placeholder="Your Email (optional)" name="commenter_email" value="<?php echo isset($_POST['commenter_email']) ? htmlspecialchars($_POST['commenter_email']) : ''; ?>">
                  </div>
                </div>
                <div class="col-lg-12">
                  <div class="form-group">
                    
                    <textarea id="comment_text" name="comment_text" class="form-control textarea" placeholder="Your Comment" required><?php echo isset($_POST['comment_text']) ? htmlspecialchars($_POST['comment_text']) : ''; ?></textarea>
                  </div>
                </div>
                <div class="col-lg-12">
                  <div class="form-group form-bottom">
                    <button type="submit" class="btn-style-one" name="submit_comment">Post Comment</button>
                  </div>
                </div>
              </div>
                </div>
                </form>
             </div> 
        <div class="text-right"><a href="blog.php" class="btn-style-two">← Back to All Events</a></div>
        <?php endif; ?></div>
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
