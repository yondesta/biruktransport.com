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
    $category_id = trim($_GET["id"]);

    // --- IMPORTANT: Check if there are any events associated with this category ---
    $can_delete = true;
    $category_name_to_check = '';

    // First, get the category name by ID
    $sql_get_category_name = "SELECT category_name FROM event_categories WHERE id = ?";
    if ($stmt_get_name = $conn->prepare($sql_get_category_name)) {
        $stmt_get_name->bind_param("i", $category_id);
        if ($stmt_get_name->execute()) {
            $stmt_get_name->bind_result($category_name_to_check);
            $stmt_get_name->fetch();
            $stmt_get_name->close();

            if (empty($category_name_to_check)) {
                // Category not found, redirect back
                header("location: admin_categories.php");
                exit;
            }

            // Now, count events linked to this category name
            $sql_count_events = "SELECT COUNT(id) AS event_count FROM blog WHERE category = ?";
            if ($stmt_count = $conn->prepare($sql_count_events)) {
                $stmt_count->bind_param("s", $category_name_to_check);
                if ($stmt_count->execute()) {
                    $stmt_count->bind_result($event_count);
                    $stmt_count->fetch();
                    if ($event_count > 0) {
                        $can_delete = false;
                        $_SESSION['delete_error'] = "Cannot delete category '" . htmlspecialchars($category_name_to_check) . "' because " . $event_count . " event(s) are associated with it. Please reassign or delete these events first.";
                    }
                }
                $stmt_count->close();
            }
        } else {
            $_SESSION['delete_error'] = "Error checking category. Please try again.";
            header("location: admin_categories.php");
            exit;
        }
    } else {
        $_SESSION['delete_error'] = "Error preparing category check. Please try again.";
        header("location: admin_categories.php");
        exit;
    }


    // --- Proceed with deletion only if allowed ---
    if ($can_delete) {
        // Prepare a delete statement
        $sql_delete = "DELETE FROM event_categories WHERE id = ?";

        if ($stmt_delete = $conn->prepare($sql_delete)) {
            // Bind parameters
            $stmt_delete->bind_param("i", $category_id);

            // Attempt to execute the prepared statement
            if ($stmt_delete->execute()) {
                $_SESSION['delete_success'] = "Category deleted successfully!";
                header("location: admin_categories.php");
                exit;
            } else {
                $_SESSION['delete_error'] = "ERROR: Could not delete category. " . $stmt_delete->error;
                header("location: admin_categories.php");
                exit;
            }
            $stmt_delete->close();
        } else {
            $_SESSION['delete_error'] = "ERROR: Could not prepare delete statement. " . $conn->error;
            header("location: admin_categories.php");
            exit;
        }
    } else {
        // Redirection for cannot delete scenario already handled above
        header("location: admin_categories.php");
        exit;
    }

} else {
    // If ID is not provided, redirect to admin categories page
    header("location: admin_categories.php");
    exit;
}

// Close connection
$conn->close();
?>