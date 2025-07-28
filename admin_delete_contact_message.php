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

// Check if ID parameter is set
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $message_id = trim($_GET["id"]);

    // Prepare a delete statement
    $sql = "DELETE FROM contact_comment WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $message_id);

        if ($stmt->execute()) {
            $_SESSION['contact_action_success'] = "Contact message deleted successfully!";
        } else {
            $_SESSION['contact_action_error'] = "ERROR: Could not delete contact message. " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['contact_action_error'] = "ERROR: Could not prepare statement. " . $conn->error;
    }
} else {
    $_SESSION['contact_action_error'] = "No message ID provided for deletion.";
}

// Close connection
$conn->close();

// Redirect back to the contact messages management page
header("location: admin_contact_messages.php");
exit;
?>