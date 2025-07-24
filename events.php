<?php
// Database connection details - IMPORTANT: Replace with your actual credentials
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

$events = []; // Initialize an empty array to store events

// SQL query to fetch events, ordered by event_date (upcoming first)
// You might add WHERE event_date >= CURDATE() to only show future events
$sql = "SELECT id, event_name, event_detail, event_date, category FROM blog ORDER BY event_date ASC";

if ($result = $conn->query($sql)) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
        $result->free(); // Free result set
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
    <title>Our Events | Biruk Transport</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .container { max-width: 960px; margin: auto; padding: 20px; }
        h1 { text-align: center; color: #333; margin-bottom: 30px; }
        .event-list { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .event-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            background-color: #f9f9f9;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .event-card h3 { color: #007bff; margin-top: 0; }
        .event-card p { margin-bottom: 10px; }
        .event-card .meta { font-size: 0.9em; color: #666; margin-bottom: 10px; }
        .event-card .read-more { display: inline-block; background-color: #28a745; color: white; padding: 8px 12px; border-radius: 5px; text-decoration: none; }
        .event-card .read-more:hover { background-color: #218838; }
        .no-events { text-align: center; color: #777; font-style: italic; }
    </style>
    <?php // include 'header.php'; ?>
</head>
<body>
    <div class="container">
        <h1>Latest Events from Biruk Transport</h1>

        <?php if (!empty($events)): ?>
            <div class="event-list">
                <?php foreach ($events as $event): ?>
                    <div class="event-card">
                        <h3><?php echo htmlspecialchars($event['event_name']); ?></h3>
                        <div class="meta">
                            <strong>Date:</strong> <?php echo date('F j, Y', strtotime($event['event_date'])); ?> |
                            <strong>Category:</strong> <?php echo htmlspecialchars($event['category']); ?>
                        </div>
                        <p>
                            <?php
                            // Display a snippet of the detail
                            $snippet = strip_tags($event['event_detail']); // Remove any HTML for snippet
                            if (strlen($snippet) > 150) {
                                $snippet = substr($snippet, 0, 150) . '...';
                            }
                            echo htmlspecialchars($snippet);
                            ?>
                        </p>
                        <a href="event_detail.php?id=<?php echo $event['id']; ?>" class="read-more">Read More</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="no-events">No events found at the moment. Please check back later!</p>
        <?php endif; ?>
    </div>

    <?php // include 'footer.php'; ?>
</body>
</html>