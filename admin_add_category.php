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
$category_name = "";
$category_name_err = "";
$success_message = $error_message = "";

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate category name
    if (empty(trim($_POST["category_name"]))) {
        $category_name_err = "Please enter a category name.";
    } else {
        // Check if category already exists
        $sql_check = "SELECT id FROM event_categories WHERE category_name = ?";
        if ($stmt_check = $conn->prepare($sql_check)) {
            $stmt_check->bind_param("s", $param_category_name);
            $param_category_name = trim($_POST["category_name"]);
            if ($stmt_check->execute()) {
                $stmt_check->store_result();
                if ($stmt_check->num_rows == 1) {
                    $category_name_err = "This category already exists.";
                } else {
                    $category_name = trim($_POST["category_name"]);
                }
            } else {
                $error_message = "Oops! Something went wrong. Please try again later.";
            }
            $stmt_check->close();
        } else {
            $error_message = "ERROR: Could not prepare check statement. " . $conn->error;
        }
    }

    // Check input errors before inserting into database
    if (empty($category_name_err) && empty($error_message)) {
        // Prepare an insert statement
        $sql_insert = "INSERT INTO event_categories (category_name) VALUES (?)";

        if ($stmt_insert = $conn->prepare($sql_insert)) {
            $stmt_insert->bind_param("s", $param_category_name);

            // Set parameters
            $param_category_name = $category_name;

            // Attempt to execute the prepared statement
            if ($stmt_insert->execute()) {
                $success_message = "Category added successfully!";
                // Clear form field after successful submission
                $category_name = "";
                // Optional: Redirect back to the categories list after a short delay
                header("refresh:3;url=admin_categories.php");
            } else {
                $error_message = "ERROR: Could not add category. " . $stmt_insert->error;
            }
            $stmt_insert->close();
        } else {
            $error_message = "ERROR: Could not prepare insert statement. " . $conn->error;
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
    <title>Add New Category | Admin Panel</title>
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
            max-width: 600px;
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
        input[type="text"] {
            width: calc(100% - 22px);
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
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
            text-decoration: none;
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
            <a href="admin_categories.php">Manage Categories</a>
            <a href="admin_logout.php" class="logout-link">Logout</a>
        </div>
    </div>

    <div class="container">
        <h2>Add New Category</h2>

        <?php if (!empty($success_message)): ?>
            <div class="alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="category_name">Category Name</label>
                <input type="text" name="category_name" id="category_name" value="<?php echo htmlspecialchars($category_name); ?>" required>
                <span class="help-block"><?php echo $category_name_err; ?></span>
            </div>

            <div class="form-group">
                <input type="submit" class="btn-submit" value="Add Category">
                <a href="admin_categories.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>