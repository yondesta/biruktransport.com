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
    // Get ID from URL
    $event_id = trim($_GET["id"]);

    // --- First, retrieve the image filename associated with the event ---
    $image_to_delete = '';
    $sql_get_image = "SELECT image_filename FROM blog WHERE id = ?";
    if ($stmt_get_image = $conn->prepare($sql_get_image)) {
        $stmt_get_image->bind_param("i", $event_id);
        if ($stmt_get_image->execute()) {
            $stmt_get_image->bind_result($image_to_delete);
            $stmt_get_image->fetch();
        }
        $stmt_get_image->close();
    }

    // --- Prepare a delete statement for the event ---
    $sql_delete_event = "DELETE FROM blog WHERE id = ?";

    if ($stmt_delete = $conn->prepare($sql_delete_event)) {
        // Bind parameters
        $stmt_delete->bind_param("i", $event_id);

        // Attempt to execute the prepared statement
        if ($stmt_delete->execute()) {
            // --- If event record deleted successfully, delete the image file ---
            if (!empty($image_to_delete)) {
                $file_path = __DIR__ . "images/uploads/" . $image_to_delete;
                if (file_exists($file_path)) {
                    unlink($file_path); // Delete the actual file
                }
            }
            // Redirect to admin events page after successful deletion
            header("location: admin_events.php");
            exit;
        } else {
            echo "ERROR: Could not delete event. " . $stmt_delete->error;
        }
        $stmt_delete->close();
    } else {
        echo "ERROR: Could not prepare delete statement. " . $conn->error;
    }
} else {
    // If ID is not provided, redirect to admin events page
    header("location: admin_events.php");
    exit;
}

// Close connection
$conn->close();
?>