<?php
// Start the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: admin_login.php");
    exit;
}

// Database connection details
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

$comments = [];

// Fetch all comments from the database, joining with events table to show event name
$sql = "
    SELECT
        c.id AS comment_id,
        c.comment_author,
        c.comment_email,
        c.comment_content,
        c.comment_date,
        c.is_approved,
        e.event_name,
        e.id AS event_id
    FROM
        event_comments c
    LEFT JOIN
        blog e ON c.event_id = e.id
    ORDER BY
        c.comment_date DESC
";

if ($result = $conn->query($sql)) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $comments[] = $row;
        }
        $result->free();
    }
} else {
    echo "ERROR: Could not able to execute $sql. " . $conn->error;
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Comments | Admin Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
        }
        .admin-header {
            background-color: #333;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .admin-header h1 {
            margin: 0;
            font-size: 24px;
        }
        .admin-header a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            background-color: #007bff;
            transition: background-color 0.3s ease;
            margin-left: 10px;
        }
        .admin-header a:hover {
            background-color: #0056b3;
        }
        .welcome-message {
            font-size: 16px;
            margin-right: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333;
            margin-bottom: 25px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
            vertical-align: top; /* Align content to top for longer comments */
        }
        th {
            background-color: #f2f2f2;
            color: #333;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .comment-content {
            max-height: 100px; /* Limit height of comment content */
            overflow-y: auto; /* Add scroll if content overflows */
            font-size: 0.9em;
        }
        .actions a {
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 4px;
            margin-right: 5px;
            font-size: 0.9em;
            display: inline-block;
            margin-bottom: 5px; /* Spacing between action buttons */
        }
        .approve-btn {
            background-color: #28a745;
            color: white;
        }
        .approve-btn:hover {
            background-color: #218838;
        }
        .unapprove-btn {
            background-color: #ffc107;
            color: #333;
        }
        .unapprove-btn:hover {
            background-color: #e0a800;
        }
        .delete-btn {
            background-color: #dc3545;
            color: white;
        }
        .delete-btn:hover {
            background-color: #c82333;
        }
        .status-approved {
            color: #28a745;
            font-weight: bold;
        }
        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }
        .no-records {
            text-align: center;
            padding: 20px;
            color: #777;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1>Admin Panel</h1>
        <div>
            <span class="welcome-message">Welcome, <strong><?php echo htmlspecialchars($_SESSION["username"]); ?></strong>!</span>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="admin_events.php">Manage Events</a>
            <a href="admin_categories.php">Manage Categories</a>
            <a href="admin_logout.php" class="logout-link">Logout</a>
        </div>
    </div>

    <div class="container">
        <h2>Manage Comments</h2>

        <?php
        // Display success/error messages from comment actions
        if (isset($_SESSION['comment_action_success'])) {
            echo '<div class="alert-success">' . $_SESSION['comment_action_success'] . '</div>';
            unset($_SESSION['comment_action_success']); // Clear the message after displaying
        }
        if (isset($_SESSION['comment_action_error'])) {
            echo '<div class="alert-danger">' . $_SESSION['comment_action_error'] . '</div>';
            unset($_SESSION['comment_action_error']); // Clear the message after displaying
        }
        ?>

        <?php if (!empty($comments)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Author</th>
                        <th>Email</th>
                        <th>Comment</th>
                        <th>Event</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comments as $comment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($comment['comment_id']); ?></td>
                            <td><?php echo htmlspecialchars($comment['comment_author']); ?></td>
                            <td><?php echo htmlspecialchars($comment['comment_email']); ?></td>
                            <td><div class="comment-content"><?php echo nl2br(htmlspecialchars($comment['comment_content'])); ?></div></td>
                            <td>
                                <?php if (!empty($comment['event_name'])): ?>
                                    <a href="event_detail.php?id=<?php echo htmlspecialchars($comment['event_id']); ?>" target="_blank">
                                        <?php echo htmlspecialchars($comment['event_name']); ?>
                                    </a>
                                <?php else: ?>
                                    <em>Event Deleted</em>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('Y-m-d H:i', strtotime($comment['comment_date'])); ?></td>
                            <td>
                                <?php if ($comment['is_approved']): ?>
                                    <span class="status-approved">Approved</span>
                                <?php else: ?>
                                    <span class="status-pending">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <?php if ($comment['is_approved']): ?>
                                    <a href="admin_unapprove_comment.php?id=<?php echo $comment['comment_id']; ?>" class="unapprove-btn">Unapprove</a>
                                <?php else: ?>
                                    <a href="admin_approve_comment.php?id=<?php echo $comment['comment_id']; ?>" class="approve-btn">Approve</a>
                                <?php endif; ?>
                                <a href="admin_delete_comment.php?id=<?php echo $comment['comment_id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this comment? This cannot be undone.');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-records">No comments found yet.</p>
        <?php endif; ?>
    </div>
</body>
</html>