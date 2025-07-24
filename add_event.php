<?php
// Database connection details - IMPORTANT: Replace with your actual credentials
define('DB_SERVER', 'localhost'); // Usually 'localhost'
define('DB_USERNAME', 'yonas'); // Your database username
define('DB_PASSWORD', 'Biruk@123'); // Your database password
define('DB_NAME', 'biruktransport'); // The database name you created

// Attempt to connect to MySQL database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn === false) {
    die("ERROR: Could not connect. " . $conn->connect_error);
}

$message = ''; // To store success or error messages

// Process form submission when form is posted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data and sanitize
    $eventName = $conn->real_escape_string(trim($_POST['event_name']));
    $eventDetail = $conn->real_escape_string(trim($_POST['event_detail']));
    $eventDate = $conn->real_escape_string(trim($_POST['event_date']));
    $category = $conn->real_escape_string(trim($_POST['category']));

    // Simple validation
    if (empty($eventName) || empty($eventDetail) || empty($eventDate) || empty($category)) {
        $message = '<div style="color: red;">All fields are required!</div>';
    } else {
        // Prepare an insert statement
        $sql = "INSERT INTO blog (event_name, event_detail, event_date, category) VALUES (?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            // Bind parameters
            $stmt->bind_param("ssss", $eventName, $eventDetail, $eventDate, $category);

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                $message = '<div style="color: green;">Event added successfully!</div>';
                // Clear form fields after successful submission (optional)
                $_POST = array(); // Clear POST array to reset form
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

// Close connection
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
        textarea {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
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
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <label for="event_name">Event Name:</label>
            <input type="text" id="event_name" name="event_name" value="<?php echo isset($_POST['event_name']) ? htmlspecialchars($_POST['event_name']) : ''; ?>" required>

            <label for="event_detail">Event Detail:</label>
            <textarea id="event_detail" name="event_detail" required><?php echo isset($_POST['event_detail']) ? htmlspecialchars($_POST['event_detail']) : ''; ?></textarea>

            <label for="event_date">Event Date:</label>
            <input type="date" id="event_date" name="event_date" value="<?php echo isset($_POST['event_date']) ? htmlspecialchars($_POST['event_date']) : ''; ?>" required>

            <label for="category">Category:</label>
            <input type="text" id="category" name="category" value="<?php echo isset($_POST['category']) ? htmlspecialchars($_POST['category']) : ''; ?>" required>

            <button type="submit">Add Event</button>
        </form>
    </div>
</body>
</html>