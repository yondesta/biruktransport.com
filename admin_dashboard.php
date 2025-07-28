<?php
// Start the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: admin_login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Biruk Transport</title>
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
        }
        .admin-header a:hover {
            background-color: #0056b3;
        }
        .welcome-message {
            text-align: right;
            font-size: 16px;
            margin-right: 20px;
        }
        .dashboard-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .dashboard-card {
            background-color: #e9ecef;
            padding: 25px;
            border-radius: 8px;
            text-align: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        }
        .dashboard-card h3 {
            color: #333;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 20px;
        }
        .dashboard-card a {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        .dashboard-card a:hover {
            background-color: #218838;
        }
        .logout-link {
            background-color: #dc3545 !important;
        }
        .logout-link:hover {
            background-color: #c82333 !important;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1>Biruk Transport - Admin Dashboard</h1>
        <div class="welcome-section">
            <span class="welcome-message">Welcome, <strong><?php echo htmlspecialchars($_SESSION["username"]); ?></strong>!</span>
            <a href="admin_logout.php" class="logout-link">Logout</a>
        </div>
    </div>

    <div class="dashboard-container">
        <h2>Dashboard Overview</h2>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h3>Event Management</h3>
                <p>Add, edit, or delete events.</p>
                <a href="admin_events.php">Go to Events</a>
            </div>

            <div class="dashboard-card">
                <h3>Category Management</h3>
                <p>Manage event categories.</p>
                <a href="admin_categories.php">Go to Categories</a>
            </div>
                             
            <div class="dashboard-card">
                <h3>Comment Moderation</h3>
                <p>View and manage user comments.</p>
                <a href="admin_comments.php">Go to Comments</a>
            </div>
            
            <div class="dashboard-card">
                <h3>Manage Contact Messages</h3>
                <p>View and manage user comments.</p>
              <a href="admin_contact_messages.php" >Go to Messages</a>
             </div>
            
            <div class="dashboard-card">
                <h3>Manage Appointments</h3>
                <p>View and Manage Appointments</p>
                <a href="admin_appointments.php">Go to Appointments</a>
            </div>
            
        </div>
    </div>
</body>
</html>