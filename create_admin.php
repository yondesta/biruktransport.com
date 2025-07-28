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

$username = "admin"; // Choose your desired admin username
$password = "Admin@123"; // Choose a strong password here

// Hash the password securely
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Prepare an insert statement
$sql = "INSERT INTO admin_users (username, password_hash) VALUES (?, ?)";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ss", $username, $password_hash);
    if ($stmt->execute()) {
        echo "Admin user '" . htmlspecialchars($username) . "' created successfully!";
    } else {
        echo "ERROR: Could not create admin user. " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "ERROR: Could not prepare statement. " . $conn->error;
}

$conn->close();
?>