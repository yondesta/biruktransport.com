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
$categories_with_counts = []; // Array to store categories and their event counts
$total_pages = 0;
$records_per_page = 1; // Maximum 10 events per page

// --- Determine Current Page, Selected Category, and Search Query ---
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$selected_category = isset($_GET['category']) ? $conn->real_escape_string(trim($_GET['category'])) : '';
$search_query = isset($_GET['q']) ? $conn->real_escape_string(trim($_GET['q'])) : '';


// --- Fetch Categories with Event Counts ---
// (This query is not affected by search query as it counts all events per category)
$sql_category_counts = "
    SELECT ec.category_name, COUNT(e.id) AS event_count
    FROM event_categories ec
    LEFT JOIN blog e ON ec.category_name = e.category
    GROUP BY ec.category_name
    ORDER BY ec.category_name ASC
";
if ($result_cat_counts = $conn->query($sql_category_counts)) {
    while ($row_cat = $result_cat_counts->fetch_assoc()) {
        $categories_with_counts[] = $row_cat;
    }
    $result_cat_counts->free();
} else {
    // Handle error if categories cannot be fetched
    // echo "Error fetching category counts: " . $conn->error;
}


// --- Get Total Number of Events (for pagination calculation, considering category AND search filter) ---
$total_events_sql = "SELECT COUNT(id) AS total_count FROM blog";
$total_events_params = [];
$total_events_types = "";
$where_clauses = [];

if (!empty($selected_category)) {
    $where_clauses[] = "category = ?";
    $total_events_params[] = $selected_category;
    $total_events_types .= "s";
}
if (!empty($search_query)) {
    $where_clauses[] = "(event_name LIKE ? OR event_detail LIKE ?)";
    $total_events_params[] = "%" . $search_query . "%";
    $total_events_params[] = "%" . $search_query . "%";
    $total_events_types .= "ss"; // two 's' for two LIKE parameters
}

if (!empty($where_clauses)) {
    $total_events_sql .= " WHERE " . implode(" AND ", $where_clauses);
}

if ($stmt_total = $conn->prepare($total_events_sql)) {
    if (!empty($total_events_params)) {
        // Correct way to bind parameters when using call_user_func_array with references
        $bind_total_params = [];
        $bind_total_params[] = &$total_events_types;
        foreach ($total_events_params as $key => &$value) {
            $bind_total_params[] = &$value;
        }
        call_user_func_array([$stmt_total, 'bind_param'], $bind_total_params);
    }
    $stmt_total->execute();
    $total_events_result = $stmt_total->get_result();
    $total_events_row = $total_events_result->fetch_assoc();
    $total_events = $total_events_row['total_count'];
    $stmt_total->close();
} else {
    echo "ERROR: Could not prepare total events query. " . $conn->error;
}


// Calculate total pages
$total_pages = ceil($total_events / $records_per_page);

// Ensure current_page is not less than 1 or greater than total_pages
if ($current_page < 1) {
    $current_page = 1;
} elseif ($current_page > $total_pages && $total_pages > 0) {
    $current_page = $total_pages;
}
$offset = ($current_page - 1) * $records_per_page;


// --- Fetch Events for the Current Page (considering category AND search filter) ---
$sql_events = "SELECT id, event_name, event_detail, event_date, category, image_filename FROM blog";
$params_values = []; // Store parameter values
$types = ""; // Store parameter types
$where_clauses_events = [];

if (!empty($selected_category)) {
    $where_clauses_events[] = "category = ?";
    $params_values[] = $selected_category;
    $types .= "s";
}
if (!empty($search_query)) {
    $where_clauses_events[] = "(event_name LIKE ? OR event_detail LIKE ?)";
    $params_values[] = "%" . $search_query . "%";
    $params_values[] = "%" . $search_query . "%";
    $types .= "ss"; // two 's' for two LIKE parameters
}

if (!empty($where_clauses_events)) {
    $sql_events .= " WHERE " . implode(" AND ", $where_clauses_events);
}

$sql_events .= " ORDER BY event_date DESC LIMIT ? OFFSET ?";
$params_values[] = $records_per_page;
$params_values[] = $offset;
$types .= "ii";

if ($stmt_events = $conn->prepare($sql_events)) {
    $bind_params = [];
    $bind_params[] = &$types; // First argument is the types string

    foreach ($params_values as $key => &$value) {
        $bind_params[] = &$value; // Each parameter value must be a reference
    }

    call_user_func_array([$stmt_events, 'bind_param'], $bind_params);

    if ($stmt_events->execute()) {
        $result_events = $stmt_events->get_result();
        if ($result_events->num_rows > 0) {
            while ($row = $result_events->fetch_assoc()) {
                $events[] = $row;
            }
        }
    } else {
        echo "ERROR: Could not execute events query. " . $stmt_events->error;
    }
    $stmt_events->close();
} else {
    echo "ERROR: Could not prepare events query. " . $conn->error;
}

// Close connection
$conn->close();

// Helper function to build query string parameters
function build_query_string($page, $category, $q) {
    $params = [];
    if ($page > 1) { // Only add page if it's not the first page
        $params[] = 'page=' . $page;
    }
    if (!empty($category)) {
        $params[] = 'category=' . urlencode($category);
    }
    if (!empty($q)) {
        $params[] = 'q=' . urlencode($q);
    }
    return empty($params) ? '' : '?' . implode('&', $params);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Events | Biruk Transport</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .wrapper {
            display: flex;
            max-width: 1200px;
            margin: auto;
            padding: 20px;
        }
        .main-content {
            flex: 3;
            padding-right: 30px;
        }
        .sidebar {
            flex: 1;
            padding-left: 20px;
            border-left: 1px solid #eee;
        }
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
        .event-card-image {
            max-width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        /* Pagination Styling */
        .pagination {
            display: flex;
            justify-content: center;
            padding: 20px 0;
            list-style: none;
        }
        .pagination li {
            margin: 0 5px;
        }
        .pagination li a,
        .pagination li span {
            display: block;
            padding: 8px 15px;
            border: 1px solid #ddd;
            text-decoration: none;
            color: #007bff;
            border-radius: 5px;
            background-color: #fff;
        }
        .pagination li a:hover {
            background-color: #e9ecef;
        }
        .pagination li.active a,
        .pagination li.active span {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
        .pagination li.disabled a,
        .pagination li.disabled span {
            color: #6c757d;
            pointer-events: none;
            background-color: #e9ecef;
        }

        /* Sidebar Styling */
        .sidebar h2 {
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }
        .category-list {
            list-style: none;
            padding: 0;
        }
        .category-list li {
            margin-bottom: 8px;
        }
        .category-list li a {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
            display: block;
            padding: 5px 0;
        }
        .category-list li a:hover {
            color: #0056b3;
            text-decoration: underline;
        }
        .category-list li a.active-category {
            color: #28a745;
            text-decoration: none;
        }
        .category-count {
            float: right;
            background-color: #e9ecef;
            padding: 2px 7px;
            border-radius: 12px;
            font-size: 0.85em;
            color: #555;
        }
        .all-categories-link {
            display: block;
            margin-bottom: 15px;
            font-weight: bold;
            color: #007bff;
            text-decoration: none;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        .all-categories-link:hover {
            text-decoration: underline;
        }
        .all-categories-link.active-category {
             color: #28a745;
             text-decoration: none;
         }

        /* Search Form Styling */
        .search-form {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            gap: 10px;
        }
        .search-form input[type="text"] {
            flex-grow: 1; /* Takes up most space */
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }
        .search-form button {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .search-form button:hover {
            background-color: #0056b3;
        }
    </style>
    <?php // include 'header.php'; ?>
</head>
<body>
    <div class="wrapper">
        <div class="main-content">
            <h1>
                <?php
                if (!empty($search_query)) {
                    echo "Search Results for: \"" . htmlspecialchars($search_query) . "\"";
                    if (!empty($selected_category)) {
                        echo " in " . htmlspecialchars($selected_category) . " category";
                    }
                } elseif (!empty($selected_category)) {
                    echo "Events in: " . htmlspecialchars($selected_category);
                } else {
                    echo "Latest Events from Biruk Transport";
                }
                ?>
            </h1>

            <form action="events.php" method="get" class="search-form">
                <input type="text" name="q" placeholder="Search events..." value="<?php echo htmlspecialchars($search_query); ?>">
                <?php if (!empty($selected_category)): ?>
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($selected_category); ?>">
                <?php endif; ?>
                <button type="submit">Search</button>
            </form>

            <?php if (!empty($events)): ?>
                <div class="event-list">
                    <?php foreach ($events as $event): ?>
                        <div class="event-card">
                            <?php if (!empty($event['image_filename'])): ?>
                                <img src="images/uploads/<?php echo htmlspecialchars($event['image_filename']); ?>"
                                     alt="<?php echo htmlspecialchars($event['event_name']); ?>"
                                     class="event-card-image">
                            <?php endif; ?>
                            <h3><?php echo htmlspecialchars($event['event_name']); ?></h3>
                            <div class="meta">
                                <strong>Date:</strong> <?php echo date('F j, Y', strtotime($event['event_date'])); ?> |
                                <strong>Category:</strong> <?php echo htmlspecialchars($event['category']); ?>
                            </div>
                            <p>
                                <?php
                                $snippet = strip_tags($event['event_detail']);
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

                <?php if ($total_pages > 1): ?>
                    <ul class="pagination">
                        <li class="<?php if($current_page <= 1){ echo 'disabled'; } ?>">
                            <a href="<?php echo build_query_string($current_page - 1, $selected_category, $search_query); ?>">Previous</a>
                        </li>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="<?php if($current_page == $i){ echo 'active'; } ?>">
                                <a href="<?php echo build_query_string($i, $selected_category, $search_query); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="<?php if($current_page >= $total_pages){ echo 'disabled'; } ?>">
                            <a href="<?php echo build_query_string($current_page + 1, $selected_category, $search_query); ?>">Next</a>
                        </li>
                    </ul>
                <?php endif; ?>

            <?php else: ?>
                <p class="no-events">
                    <?php
                    if (!empty($search_query) && !empty($selected_category)) {
                        echo "No events found matching \"" . htmlspecialchars($search_query) . "\" in the category \"" . htmlspecialchars($selected_category) . "\".";
                    } elseif (!empty($search_query)) {
                        echo "No events found matching \"" . htmlspecialchars($search_query) . "\".";
                    } elseif (!empty($selected_category)) {
                        echo "No events found in the category \"" . htmlspecialchars($selected_category) . "\".";
                    } else {
                        echo "No events found at the moment. Please check back later!";
                    }
                    ?>
                </p>
            <?php endif; ?>
        </div>

        <div class="sidebar">
            <h2>Event Categories</h2>
            <ul class="category-list">
                <li>
                    <a href="<?php echo build_query_string(1, '', $search_query); ?>" class="all-categories-link <?php echo empty($selected_category) ? 'active-category' : ''; ?>">All Categories</a>
                </li>
                <?php foreach ($categories_with_counts as $cat): ?>
                    <li>
                        <a href="<?php echo build_query_string(1, $cat['category_name'], $search_query); ?>"
                           class="<?php echo ($selected_category == $cat['category_name']) ? 'active-category' : ''; ?>">
                            <?php echo htmlspecialchars($cat['category_name']); ?>
                            <span class="category-count">(<?php echo $cat['event_count']; ?>)</span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <?php // include 'footer.php'; ?>
</body>
</html>