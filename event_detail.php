<?php
// Database connection details - IMPORTANT: Replace with your actual credentials
define('DB_SERVER', 'localhost'); // Usually 'localhost'
define('DB_USERNAME', 'yonas'); // Your database username
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
    $sql_event = "SELECT id, event_name, event_detail, event_date, category, image_filename, created_at FROM blog WHERE id = ?";
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $event ? htmlspecialchars($event['event_name']) : 'Event Not Found'; ?> | Biruk Transport</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .container { max-width: 800px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1 { color: #007bff; margin-top: 0; text-align: center; }
        .meta { font-size: 0.9em; color: #666; margin-bottom: 20px; text-align: center; }
        .event-image {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto 20px auto;
            border-radius: 5px;
        }
        .event-detail { margin-top: 20px; padding-bottom: 20px; border-bottom: 1px dashed #ccc; }

        /* Comments Section Styling */
        .comments-section { margin-top: 30px; }
        .comments-section h2 { color: #333; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .comment { background-color: #fff; border: 1px solid #eee; padding: 15px; margin-bottom: 15px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .comment strong { color: #555; }
        .comment .comment-date { font-size: 0.8em; color: #999; float: right; }
        .comment p { margin: 5px 0 0; }
        .no-comments { text-align: center; color: #777; font-style: italic; padding: 10px; }

        /* Comment Form Styling */
        .comment-form { background-color: #fff; border: 1px solid #ddd; padding: 20px; border-radius: 8px; margin-top: 30px; }
        .comment-form h3 { color: #333; margin-top: 0; margin-bottom: 15px; }
        .comment-form label { display: block; margin-bottom: 5px; font-weight: bold; }
        .comment-form input[type="text"],
        .comment-form input[type="email"],
        .comment-form textarea {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .comment-form textarea { resize: vertical; min-height: 80px; }
        .comment-form button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .comment-form button:hover { background-color: #0056b3; }
        .message { text-align: center; margin-bottom: 15px; }
        .back-link { display: inline-block; margin-top: 20px; background-color: #6c757d; color: white; padding: 8px 15px; border-radius: 5px; text-decoration: none; }
        .back-link:hover { background-color: #5a6268; }
    </style>
    <?php // include 'header.php'; ?>
</head>
<body>
    <div class="container">
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
                <h2>Comments</h2>
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

            <div class="comment-form">
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

            <a href="events.php" class="back-link">← Back to All Events</a>
        <?php endif; ?>
    </div>

    <?php // include 'footer.php'; ?>
</body>
</html>