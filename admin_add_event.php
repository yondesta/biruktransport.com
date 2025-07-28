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

// Define variables and initialize with empty values
$event_name = $event_detail = $event_date = $category = $image_filename = "";
$event_name_err = $event_detail_err = $event_date_err = $category_err = $image_err = "";
$success_message = $error_message = "";

// Fetch categories for the dropdown
$categories = [];
$sql_categories = "SELECT category_name FROM event_categories ORDER BY category_name ASC";
if ($result_categories = $conn->query($sql_categories)) {
    while ($row = $result_categories->fetch_assoc()) {
        $categories[] = $row['category_name'];
    }
    $result_categories->free();
} else {
    $error_message = "ERROR: Could not fetch categories. " . $conn->error;
}


// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate event name
    if (empty(trim($_POST["event_name"]))) {
        $event_name_err = "Please enter an event name.";
    } else {
        $event_name = trim($_POST["event_name"]);
    }

    // Validate event detail
    if (empty(trim($_POST["event_detail"]))) {
        $event_detail_err = "Please enter event details.";
    } else {
        $event_detail = trim($_POST["event_detail"]);
    }

    // Validate event date
    if (empty(trim($_POST["event_date"]))) {
        $event_date_err = "Please select an event date.";
    } else {
        $event_date = trim($_POST["event_date"]);
        // Basic date format validation (YYYY-MM-DD)
        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $event_date)) {
            $event_date_err = "Invalid date format. Please use YYYY-MM-DD.";
        }
    }

    // Validate category
    if (empty(trim($_POST["category"]))) {
        $category_err = "Please select a category.";
    } else {
        $category = trim($_POST["category"]);
        // Ensure selected category actually exists in the fetched categories
        if (!in_array($category, $categories)) {
            $category_err = "Selected category is invalid.";
        }
    }

    // Validate image upload
    if (isset($_FILES["event_image"]) && $_FILES["event_image"]["error"] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5 MB

        $file_type = $_FILES["event_image"]["type"];
        $file_size = $_FILES["event_image"]["size"];
        $file_name = basename($_FILES["event_image"]["name"]);

        if (!in_array($file_type, $allowed_types)) {
            $image_err = "Only JPG, PNG, GIF, or WEBP images are allowed.";
        } elseif ($file_size > $max_size) {
            $image_err = "File size must be less than 5MB.";
        } else {
            // Generate a unique filename to prevent overwrites
            $new_filename = uniqid('event_', true) . "." . pathinfo($file_name, PATHINFO_EXTENSION);
            $upload_dir = __DIR__ . "images/uploads/"; // Assumes 'uploads' folder is in the same directory as this script

            // Create 'uploads' directory if it doesn't exist
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $target_file = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES["event_image"]["tmp_name"], $target_file)) {
                $image_filename = $new_filename;
            } else {
                $image_err = "Failed to upload image.";
            }
        }
    } else if (isset($_FILES["event_image"]) && $_FILES["event_image"]["error"] != UPLOAD_ERR_NO_FILE) {
        // Handle other upload errors
        $image_err = "File upload error: " . $_FILES["event_image"]["error"];
    }


    // Check input errors before inserting into database
    if (empty($event_name_err) && empty($event_detail_err) && empty($event_date_err) && empty($category_err) && empty($image_err)) {
        // Prepare an insert statement
        $sql = "INSERT INTO blog (event_name, event_detail, event_date, category, image_filename) VALUES (?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sssss", $param_event_name, $param_event_detail, $param_event_date, $param_category, $param_image_filename);

            // Set parameters
            $param_event_name = $event_name;
            $param_event_detail = $event_detail;
            $param_event_date = $event_date;
            $param_category = $category;
            $param_image_filename = $image_filename; // This will be empty string if no image uploaded

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                $success_message = "Event added successfully!";
                // Clear form fields after successful submission
                $event_name = $event_detail = $event_date = $category = $image_filename = "";
                // Optional: Redirect back to the events list after a short delay
                header("refresh:3;url=admin_events.php");
            } else {
                $error_message = "ERROR: Could not add event. " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_message = "ERROR: Could not prepare statement. " . $conn->error;
        }
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Event | Admin Panel</title>
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
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: bold;
        }
        input[type="text"],
        input[type="date"],
        textarea,
        select {
            width: calc(100% - 22px);
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }
        textarea {
            resize: vertical;
            min-height: 120px;
        }
        input[type="file"] {
            padding: 5px; /* Less padding for file input */
        }
        .help-block {
            color: #dc3545; /* Red for errors */
            font-size: 0.9em;
            margin-top: 5px;
            display: block;
        }
        .btn-submit {
            background-color: #28a745;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 18px;
            transition: background-color 0.3s ease;
        }
        .btn-submit:hover {
            background-color: #218838;
        }
        .btn-cancel {
            background-color: #6c757d;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 18px;
            transition: background-color 0.3s ease;
            margin-left: 10px;
            text-decoration: none; /* For the anchor tag */
            display: inline-block;
        }
        .btn-cancel:hover {
            background-color: #5a6268;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: left;
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
            <a href="admin_logout.php" class="logout-link">Logout</a>
        </div>
    </div>

    <div class="container">
        <h2>Add New Event</h2>

        <?php if (!empty($success_message)): ?>
            <div class="alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="event_name">Event Name</label>
                <input type="text" name="event_name" id="event_name" value="<?php echo htmlspecialchars($event_name); ?>" required>
                <span class="help-block"><?php echo $event_name_err; ?></span>
            </div>

            <div class="form-group">
                <label for="event_detail">Event Details</label>
                <textarea name="event_detail" id="event_detail" required><?php echo htmlspecialchars($event_detail); ?></textarea>
                <span class="help-block"><?php echo $event_detail_err; ?></span>
            </div>

            <div class="form-group">
                <label for="event_date">Event Date</label>
                <input type="date" name="event_date" id="event_date" value="<?php echo htmlspecialchars($event_date); ?>" required>
                <span class="help-block"><?php echo $event_date_err; ?></span>
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <select name="category" id="category" required>
                    <option value="">Select a Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($cat == $category) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="help-block"><?php echo $category_err; ?></span>
            </div>

            <div class="form-group">
                <label for="event_image">Event Image (Optional)</label>
                <input type="file" name="event_image" id="event_image" accept="image/jpeg,image/png,image/gif,image/webp">
                <span class="help-block"><?php echo $image_err; ?></span>
            </div>

            <div class="form-group">
                <input type="submit" class="btn-submit" value="Add Event">
                <a href="admin_events.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>