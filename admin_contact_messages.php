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

$contact_messages = [];

// Fetch all contact messages from the database
// Assuming an 'id' and 'submission_date' or 'created_at' column exists for ordering
$sql = "SELECT id, commenter_name, email, comment_subject, phone, Message, created_at FROM contact_comment ORDER BY created_at DESC";

// If you don't have a 'submission_date' or 'created_at' column, you can order by 'id' DESC:
// $sql = "SELECT id, commenter_name, email, comment_subject, Message FROM contact_comment ORDER BY id DESC";


if ($result = $conn->query($sql)) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $contact_messages[] = $row;
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
    <title>Manage Contact Messages | Admin Panel</title>
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
            max-width: 1100px;
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
            vertical-align: top;
        }
        th {
            background-color: #f2f2f2;
            color: #333;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .message-content {
            max-height: 80px; /* Limit height of message content */
            overflow-y: auto; /* Add scroll if content overflows */
            font-size: 0.9em;
            white-space: pre-wrap; /* Preserves whitespace and wraps text */
        }
        .actions a {
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 4px;
            margin-right: 5px;
            font-size: 0.9em;
            display: inline-block;
            margin-bottom: 5px;
        }
        .view-btn {
            background-color: #007bff;
            color: white;
        }
        .view-btn:hover {
            background-color: #0056b3;
        }
        .delete-btn {
            background-color: #dc3545;
            color: white;
        }
        .delete-btn:hover {
            background-color: #c82333;
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
            <a href="admin_comments.php">Manage Comments</a>
            <a href="admin_logout.php" class="logout-link">Logout</a>
        </div>
    </div>

    <div class="container">
        <h2>Manage Contact Messages</h2>

        <?php
        // Display success/error messages from contact actions
        if (isset($_SESSION['contact_action_success'])) {
            echo '<div class="alert-success">' . $_SESSION['contact_action_success'] . '</div>';
            unset($_SESSION['contact_action_success']); // Clear the message after displaying
        }
        if (isset($_SESSION['contact_action_error'])) {
            echo '<div class="alert-danger">' . $_SESSION['contact_action_error'] . '</div>';
            unset($_SESSION['contact_action_error']); // Clear the message after displaying
        }
        ?>

        <?php if (!empty($contact_messages)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Message Preview</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contact_messages as $message): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($message['id']); ?></td>
                            <td><?php echo htmlspecialchars($message['commenter_name']); ?></td>
                            <td><?php echo htmlspecialchars($message['email']); ?></td>
                            <td><?php echo htmlspecialchars($message['comment_subject']); ?></td>
                            <td><div class="message-content"><?php echo nl2br(htmlspecialchars($message['Message'])); ?></div></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($message['created_at'])); ?></td>
                            <td class="actions">
                                <a href="admin_view_contact_message.php?id=<?php echo $message['id']; ?>" class="view-btn">View Full</a>
                                <a href="admin_delete_contact_message.php?id=<?php echo $message['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this contact message? This cannot be undone.');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-records">No contact messages found yet.</p>
        <?php endif; ?>
    </div>
</body>
</html>