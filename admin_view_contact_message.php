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

$message_details = null;
$error_message = "";

// Check if an ID is provided in the URL
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $message_id = trim($_GET["id"]);

    // Prepare a select statement to get contact message details
    // Assuming 'submission_date' or 'created_at' column exists
    $sql_select = "SELECT id, commenter_name, email, comment_subject, phone, Message, created_at FROM contact_comment WHERE id = ?";

    // If you don't have 'submission_date', adjust the SELECT query:
    // $sql_select = "SELECT id, commenter_name, email, comment_subject, Message FROM contact_comment WHERE id = ?";

    if ($stmt_select = $conn->prepare($sql_select)) {
        $stmt_select->bind_param("i", $param_id);
        $param_id = $message_id;

        if ($stmt_select->execute()) {
            $result = $stmt_select->get_result();
            if ($result->num_rows == 1) {
                $message_details = $result->fetch_assoc();
            } else {
                $error_message = "No contact message found with the specified ID.";
                // Redirect back if not found
                $_SESSION['contact_action_error'] = $error_message;
                header("location: admin_contact_messages.php");
                exit();
            }
        } else {
            $error_message = "Oops! Something went wrong. Please try again later.";
        }
        $stmt_select->close();
    } else {
        $error_message = "ERROR: Could not prepare select statement. " . $conn->error;
    }
} else {
    // ID not provided, redirect to manage messages page
    $_SESSION['contact_action_error'] = "No message ID provided for viewing.";
    header("location: admin_contact_messages.php");
    exit();
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Contact Message | Admin Panel</title>
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
            max-width: 800px;
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
        .message-detail-item {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #eee;
        }
        .message-detail-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .message-detail-item strong {
            display: block;
            color: #555;
            margin-bottom: 5px;
        }
        .message-content-full {
            background-color: #f9f9f9;
            border: 1px solid #eee;
            padding: 15px;
            border-radius: 5px;
            white-space: pre-wrap; /* Preserves whitespace and wraps text */
            word-wrap: break-word; /* Breaks long words */
        }
        .actions {
            margin-top: 30px;
            text-align: right;
        }
        .actions a {
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin-left: 10px;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        .back-btn {
            background-color: #6c757d;
            color: white;
        }
        .back-btn:hover {
            background-color: #5a6268;
        }
        .delete-btn {
            background-color: #dc3545;
            color: white;
        }
        .delete-btn:hover {
            background-color: #c82333;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: left;
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
            <a href="admin_comments.php">Manage Comments</a>
            <a href="admin_contact_messages.php">Manage Contact Messages</a>
            <a href="admin_logout.php" class="logout-link">Logout</a>
        </div>
    </div>

    <div class="container">
        <h2>View Contact Message</h2>

        <?php if (!empty($error_message)): ?>
            <div class="alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <?php if ($message_details): ?>
            
            <div class="message-detail-item">
                <strong>Sender Name:</strong>
                <span><?php echo htmlspecialchars($message_details['commenter_name']); ?></span>
            </div>
            <div class="message-detail-item">
                <strong>Sender Phone:</strong>
                <span><?php echo htmlspecialchars($message_details['phone']); ?></span>
            </div>
            <div class="message-detail-item">
                <strong>Sender Email:</strong>
                <span><?php echo htmlspecialchars($message_details['email']); ?></span>
            </div>
            <div class="message-detail-item">
                <strong>Subject:</strong>
                <span><?php echo htmlspecialchars($message_details['comment_subject']); ?></span>
            </div>
            <?php if (isset($message_details['submission_date'])): ?>
            <div class="message-detail-item">
                <strong>Date Submitted:</strong>
                <span><?php echo date('Y-m-d H:i:s', strtotime($message_details['created_at'])); ?></span>
            </div>
            <?php endif; ?>
            <div class="message-detail-item">
                <strong>Message Content:</strong>
                <div class="message-content-full"><?php echo nl2br(htmlspecialchars($message_details['Message'])); ?></div>
            </div>

            <div class="actions">
                <a href="admin_contact_messages.php" class="back-btn">Back to Messages</a>
                <a href="admin_delete_contact_message.php?id=<?php echo $message_details['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this contact message? This cannot be undone.');">Delete Message</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>