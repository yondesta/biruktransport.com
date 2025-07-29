<?php
// Database connection details - IMPORTANT: Replace with your actual credentials


// Attempt to connect to MySQL database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn === false) {
    die("ERROR: Could not connect. " . $conn->connect_error);
}

$events = []; // Initialize an empty array to store events
$categories_with_counts = []; // Array to store categories and their event counts
$total_pages = 0;
$records_per_page = 2; // Maximum 10 events per page

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
    $params[] = 'page=' . $page; // Always add the page parameter - FIX HERE
    if (!empty($category)) {
        $params[] = 'category=' . urlencode($category);
    }
    if (!empty($q)) {
        $params[] = 'q=' . urlencode($q);
    }
    return '?' . implode('&', $params); // Always return with a '?' for consistency
}
?>



<!DOCTYPE html>
<html lang="zxx">
<head>

  <!-- ** Basic Page Needs ** -->
  <meta charset="utf-8">
  <title>Medic | Medical HTML Template</title>

  <!-- ** Mobile Specific Metas ** -->
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description" content="Medical HTML Template">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
  <meta name="author" content="Themefisher">
  <meta name="generator" content="Themefisher Medical HTML Template v1.0">
  
  <!-- theme meta -->
  <meta name="theme-name" content="medic" />
  
  <!-- ** Plugins Needed for the Project ** -->
  <!-- bootstrap -->
  <link rel="stylesheet" href="plugins/bootstrap/bootstrap.min.css">
  <!-- Slick Carousel -->
  <link rel="stylesheet" href="plugins/slick/slick.css">
  <link rel="stylesheet" href="plugins/slick/slick-theme.css">
  <!-- FancyBox -->
  <link rel="stylesheet" href="plugins/fancybox/jquery.fancybox.min.css">
  <!-- fontawesome -->
  <link rel="stylesheet" href="plugins/fontawesome/css/all.min.css">
  <!-- animate.css -->
  <link rel="stylesheet" href="plugins/animation/animate.min.css">
  <!-- jquery-ui -->
  <link rel="stylesheet" href="plugins/jquery-ui/jquery-ui.css">
  <!-- timePicker -->
  <link rel="stylesheet" href="plugins/timePicker/timePicker.css">
  
  <!-- Stylesheets -->
  <link href="css/style.css" rel="stylesheet">
  
  <!--Favicon-->
  <link rel="icon" href="images/favicon.png" type="image/x-icon">

</head>


<body>
<footer class="footer-main">
  <div class="footer-top">
    <div class="container">
      <div class="row justify-content-between">
        <div class="col-lg-4 mb-5 mb-lg-0">
          <div class="about-widget">
            <div class="footer-logo">
              <figure>
                <a href="index.php">
                  <img loading="lazy" class="img-fluid" src="images/logo.png" alt="medic">
                </a>
              </figure>
            </div>
            <ul class="location-link">
              <li class="item">
                <i class="fas fa-map-marker-alt"></i>
                <p>Saris Road, Dawi Tower, 5th floor, Addis Ababa, Ethiopia</p>
              </li>
              <li class="item">
                <i class="far fa-envelope" aria-hidden="true"></i>
                <a href="contact@biruktransport.com">
                  <p>contact@biruktransport.com</p>
                </a>
              </li>
              <li class="item">
                <i class="fas fa-phone" aria-hidden="true"></i>
                <p> +251 911 31 4885</p>
                </li>
              <li class="item">
                <i class="fas fa-phone" aria-hidden="true"></i>
                <p> +251 925 24 9190</p>
              </li>
              <li class="item">
                <i class="fas fa-phone" aria-hidden="true"></i>
                <p>+251 913 73 7398</p>
              </li>
            </ul>
            <ul class="list-inline social-icons">
              <li class="list-inline-item"><a href="https://facebook.com/themefisher" aria-label="facebook"><i class="fab fa-facebook-f"></i></a></li>
              <li class="list-inline-item"><a href="https://twitter.com/themefisher" aria-label="twitter"><i class="fab fa-twitter"></i></a></li>
              <li class="list-inline-item"><a href="https://instagram.com/themefisher" aria-label="instagram"><i class="fab fa-instagram"></i></a></li>
              <li class="list-inline-item"><a href="https://github.com/themefisher" aria-label="github"><i class="fab fa-github"></i></a></li>
            </ul>
          </div>
        </div>
        <div class="col-lg-3 col-md-5 mb-3 mb-md-0">
          <h2>Services</h2>
          <ul class="menu-link">
            <li>
              <a href="service.php">
                <i class="fa fa-angle-right" aria-hidden="true"></i>Import Transport</a>
            </li>
            <li>
              <a href="service.php">
                <i class="fa fa-angle-right" aria-hidden="true"></i>Export cargo</a>
            </li>
            <li>
              <a href="service.php">
                <i class="fa fa-angle-right" aria-hidden="true"></i>Local transport</a>
            </li>
          </ul>
        </div>
        <div class="col-lg-4 col-md-7">
          <div class="social-links">
            <h2>Recent Posts</h2>
            <div>     
              <?php if (!empty($events)): ?>
              <div class="event-list content-text" >
                <?php foreach ($events as $event): ?>
                    <div>
                        <a href="blog_details.php?id=<?php echo $event['id']; ?>"><h3><?php echo htmlspecialchars($event['event_name']); ?></h3></a>
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
                    </div>
                <?php endforeach; ?>
            
              <?php else: ?>
                  <p class="no-events">No events found at the moment. Please check back later!</p>
              <?php endif; ?>
           </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="footer-bottom">
    <div class="container clearfix">
      <div class="copyright-text">
        <p>&copy; Copyright 2025 @ Biruk Transport. Designed &amp; Developed by <a href="https://biruktransport.com/">Yonny Computer</a></p>
      </div>
      <ul class="footer-bottom-link">
        <li>
          <a href="index.php">Home</a>
        </li>
        <li>
          <a href="about.php">About</a>
        </li>
        <li>
          <a href="contact.php">Contact</a>
        </li>
      </ul>
    </div>
  </div>
</footer>
</body>
</html>