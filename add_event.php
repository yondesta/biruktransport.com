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

$message = ''; // To store success or error messages
$categories = []; // Array to store categories for the dropdown

// --- Fetch Categories for the Dropdown ---
$sql_categories = "SELECT category_name FROM event_categories ORDER BY category_name ASC";
if ($result_categories = $conn->query($sql_categories)) {
    if ($result_categories->num_rows > 0) {
        while ($row_cat = $result_categories->fetch_assoc()) {
            $categories[] = $row_cat['category_name'];
        }
        $result_categories->free();
    }
} else {
    $message = '<div style="color: red;">Error fetching categories: ' . $conn->error . '</div>';
}
// --- End Fetch Categories ---


// Process form submission when form is posted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data and sanitize
    $eventName = $conn->real_escape_string(trim($_POST['event_name']));
    $eventDetail = $conn->real_escape_string(trim($_POST['event_detail']));
    $eventDate = $conn->real_escape_string(trim($_POST['event_date']));
    $category = $conn->real_escape_string(trim($_POST['category']));

    $imageFilename = NULL; // Initialize image filename as NULL

    // --- Image Upload Handling ---
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "images/uploads/"; // Directory where images will be saved
        $file_extension = pathinfo($_FILES["event_image"]["name"], PATHINFO_EXTENSION);
        $unique_filename = uniqid() . "." . $file_extension; // Generate a unique name for the file
        $target_file = $target_dir . $unique_filename;
        $imageFileType = strtolower($file_extension);

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["event_image"]["tmp_name"]);
        if ($check === false) {
            $message = '<div style="color: red;">File is not an image.</div>';
        } else {
            // Check file size (e.g., max 5MB)
            if ($_FILES["event_image"]["size"] > 5000000) { // 5MB in bytes
                $message = '<div style="color: red;">Sorry, your file is too large. Max 5MB.</div>';
            }
            // Allow certain file formats
            if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
                $message = '<div style="color: red;">Sorry, only JPG, JPEG, PNG & GIF files are allowed.</div>';
            }

            // If no error message yet, attempt to upload
            if (empty($message)) {
                if (move_uploaded_file($_FILES["event_image"]["tmp_name"], $target_file)) {
                    $imageFilename = $unique_filename; // Save only the unique filename to the database
                } else {
                    $message = '<div style="color: red;">Sorry, there was an error uploading your file.</div>';
                }
            }
        }
    }
    // --- End Image Upload Handling ---


    // Simple validation for text fields
    if (empty($eventName) || empty($eventDetail) || empty($eventDate) || empty($category)) {
        $message = '<div style="color: red;">All text fields are required!</div>';
    }
    // Only proceed with DB insert if no error messages accumulated
    else if (empty($message)) {
        // Prepare an insert statement
        // Added image_filename to the insert query
        $sql = "INSERT INTO blog (event_name, event_detail, event_date, category, image_filename) VALUES (?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            // Bind parameters
            // Added 's' for image_filename (string)
            $stmt->bind_param("sssss", $eventName, $eventDetail, $eventDate, $category, $imageFilename);

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                $message = '<div style="color: green;">Event added successfully!</div>';
                // Clear POST data to reset form
                $_POST = array();
            } else {
                $message = '<div style="color: red;">Error: Could not execute query. ' . $stmt->error . '</div>';
            }
            // Close statement
            $stmt->close();
        } else {
            $message = '<div style="color: red;">Error: Could not prepare query. ' . $conn->error . '</div>';
        }
    }
}

// Close connection before HTML output
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Event</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        h2 { text-align: center; color: #333; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"],
        input[type="date"],
        textarea,
        select {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="file"] {
            width: 100%; /* Adjust as needed */
            margin-bottom: 15px;
        }
        textarea { resize: vertical; min-height: 100px; }
        button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .message { text-align: center; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Add New Event</h2>
        <div class="message"><?php echo $message; ?></div>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data"> <label for="event_name">Event Name:</label>
            <input type="text" id="event_name" name="event_name" value="<?php echo isset($_POST['event_name']) ? htmlspecialchars($_POST['event_name']) : ''; ?>" required>

            <label for="event_detail">Event Detail:</label>
            <textarea id="event_detail" name="event_detail" required><?php echo isset($_POST['event_detail']) ? htmlspecialchars($_POST['event_detail']) : ''; ?></textarea>

            <label for="event_date">Event Date:</label>
            <input type="date" id="event_date" name="event_date" value="<?php echo isset($_POST['event_date']) ? htmlspecialchars($_POST['event_date']) : ''; ?>" required>

            <label for="category">Category:</label>
            <select id="category" name="category" required>
                <option value="">-- Select a Category --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>"
                        <?php echo (isset($_POST['category']) && $_POST['category'] == $cat) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="event_image">Event Image:</label>
            <input type="file" id="event_image" name="event_image" accept="image/jpeg, image/png, image/gif"> <button type="submit">Add Event</button>
        </form>
    </div>
</body>
</html>
