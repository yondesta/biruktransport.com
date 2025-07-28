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

$categories = [];

// Fetch all categories from the database
// We also count how many events are in each category to show it
$sql = "
    SELECT
        ec.id,
        ec.category_name,
        COUNT(e.id) AS event_count
    FROM
        event_categories ec
    LEFT JOIN
        blog e ON ec.category_name = e.category
    GROUP BY
        ec.id, ec.category_name
    ORDER BY
        ec.category_name ASC
";

if ($result = $conn->query($sql)) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
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
    <title>Manage Categories | Admin Panel</title>
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
            <a href="admin_events.php">Manage Events</a>
            <a href="admin_logout.php" class="logout-link">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <?php
        // Display success/error messages from deletion
        if (isset($_SESSION['delete_success'])) {
            echo '<div class="alert-success">' . $_SESSION['delete_success'] . '</div>';
            unset($_SESSION['delete_success']); // Clear the message after displaying
        }
        if (isset($_SESSION['delete_error'])) {
            echo '<div class="alert-danger">' . $_SESSION['delete_error'] . '</div>';
            unset($_SESSION['delete_error']); // Clear the message after displaying
        }
        ?>
        <h2>
            Manage Categories
            <a href="admin_add_category.php" class="add-new-button">Add New Category</a>
        </h2>

        <?php if (!empty($categories)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category Name</th>
                        <th>Events Count</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($category['id']); ?></td>
                            <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                            <td><?php echo htmlspecialchars($category['event_count']); ?></td>
                            <td class="actions">
                                <a href="admin_edit_category.php?id=<?php echo $category['id']; ?>" class="edit-btn">Edit</a>
                                <?php if ($category['event_count'] == 0): // Only allow deletion if no events are linked ?>
                                    <a href="admin_delete_category.php?id=<?php echo $category['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this category? This cannot be undone.');">Delete</a>
                                <?php else: ?>
                                    <span title="Cannot delete category with associated events." style="color:#777; cursor:not-allowed;">Delete</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-records">No categories found. Click "Add New Category" to create your first category!</p>
        <?php endif; ?>
    </div>
</body>
</html>