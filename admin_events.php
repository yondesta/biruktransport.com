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

$events = [];

// Fetch all events from the database
$sql = "SELECT id, event_name, event_date, category FROM blog ORDER BY created_at DESC";
if ($result = $conn->query($sql)) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
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
    <title>Manage Events | Admin Panel</title>
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
            margin-left: 10px; /* Space out links */
        }
        .admin-header a:hover {
            background-color: #0056b3;
        }
        .welcome-message {
            font-size: 16px;
            margin-right: 20px;
        }
        .container {
            max-width: 1000px;
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .add-new-button {
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        .add-new-button:hover {
            background-color: #218838;
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
        }
        th {
            background-color: #f2f2f2;
            color: #333;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .actions a {
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 4px;
            margin-right: 5px;
            font-size: 0.9em;
            display: inline-block;
        }
        .edit-btn {
            background-color: #ffc107;
            color: #333;
        }
        .edit-btn:hover {
            background-color: #e0a800;
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
            <a href="admin_logout.php" class="logout-link">Logout</a>
        </div>
    </div>

    <div class="container">
        <h2>
            Manage Events
            <a href="admin_add_event.php" class="add-new-button">Add New Event</a>
        </h2>

        <?php if (!empty($events)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Event Name</th>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($event['id']); ?></td>
                            <td><?php echo htmlspecialchars($event['event_name']); ?></td>
                            <td><?php echo date('F j, Y', strtotime($event['event_date'])); ?></td>
                            <td><?php echo htmlspecialchars($event['category']); ?></td>
                            <td class="actions">
                                <a href="admin_edit_event.php?id=<?php echo $event['id']; ?>" class="edit-btn">Edit</a>
                                <a href="admin_delete_event.php?id=<?php echo $event['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this event?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-records">No events found. Click "Add New Event" to create your first event!</p>
        <?php endif; ?>
    </div>
</body>
</html>