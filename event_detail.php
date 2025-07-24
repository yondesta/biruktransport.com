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

// Check if an 'id' is provided in the URL
if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
    $event_id = $conn->real_escape_string(trim($_GET['id']));

    // Prepare a select statement to fetch a single event
    $sql = "SELECT id, event_name, event_detail, event_date, category, created_at FROM blog WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters
        $stmt->bind_param("i", $event_id); // "i" for integer

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                // Fetch result row as an associative array
                $event = $result->fetch_assoc();
            } else {
                // No event found with that ID
                echo "<p style='text-align:center; color:red;'>No event found with the specified ID.</p>";
            }
        } else {
            echo "ERROR: Could not execute query. " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "ERROR: Could not prepare query. " . $conn->error;
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
        .event-detail { margin-top: 20px; }
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
            <div class="event-detail">
                <p><?php echo nl2br(htmlspecialchars($event['event_detail'])); ?></p>
            </div>
            <a href="events.php" class="back-link">← Back to All Events</a>
        <?php endif; ?>
    </div>

    <?php // include 'footer.php'; ?>
</body>
</html>