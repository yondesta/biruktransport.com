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

  <title>Blog | Biruk Transport</title>

  <?php include 'header.php'; ?>

</head>


<body>
  

  

<!--header top-->

<!--header top-->

<!--Header Upper-->

<!--Header Upper-->


<!--Main Header-->

<!--End Main Header -->

<!--Page Title-->
<section class="page-title text-center" style="background-image:url(images/blog/blog.jpg);">
    <div class="container">
        <div class="title-text">
            <h1>Blog</h1>
            <ul class="title-menu clearfix">
                <li>
                    <a href="index.php">home &nbsp;/</a>
                </li>
                <li>Blog</li>
            </ul>
        </div>
    </div>
</section>
<!--End Page Title-->

<section class="blog-section style-four section">
  <div class="container">
    <div class="row">
      <div class="col-lg-9">
        <div class="left-side">
          <div class="item-holder">
            <div class="section-title text-center">
              <h3>Welcome to Biruk  
                <span>Transport!</span>
              </h3>
              <p>Discover the latest happenings, announcements, and special gatherings from Biruk Transport. Here, we share exciting news about
              our community engagements, special offers, industry participation, and milestones. Stay updated with all our activities and find out
              how you can be a part of our journey. We frequently update this section with details on upcoming events, past highlights, and important
              notices. We look forward to seeing you at our next event or sharing our latest achievements with you!</p>
            </div>
            <h3 class="text-center">
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
            </h3>
            <div class="image-box">     
              <?php if (!empty($events)): ?>
              <div class="event-list content-text" >
                <?php foreach ($events as $event): ?>
                    <div class="event-card">
                        <a href="event_detail.php?id=<?php echo $event['id']; ?>"><h3><?php echo htmlspecialchars($event['event_name']); ?></h3></a>
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
                        <a href="blog_details.php?id=<?php echo $event['id']; ?>" class="btn-style-one">Read More</a><hr>
                    </div>
                <?php endforeach; ?>
            
              <?php else: ?>
                  <p class="no-events">No events found at the moment. Please check back later!</p>
              <?php endif; ?>
           </div>
        </div>
      </div>
    </div>
          <div>
          <?php if ($total_pages > 1): ?>
                    <ul class="pagination"> 
                        <li class="<?php if($current_page <= 1){ echo 'disabled'; } ?>">
                            <a class="prev" href="<?php echo build_query_string($current_page - 1, $selected_category, $search_query); ?>"><span class="fas fa-angle-left" aria-hidden="true"></span></a>
                        </li>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="<?php if($current_page == $i){ echo 'active'; } ?>">
                                <a href="<?php echo build_query_string($i, $selected_category, $search_query); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="<?php if($current_page >= $total_pages){ echo 'disabled'; } ?>">
                            <a class="next" href="<?php echo build_query_string($current_page + 1, $selected_category, $search_query); ?>"><span class="fas fa-angle-right" aria-hidden="true"></span></a>
                        </li>
                    </ul>
                <?php endif; ?>
          </div>
        </div>
      

      <div class="col-lg-3">
        <div class="right-side">
          <div class="text-title">
            <h6>Search</h6>
          </div>
          <div class="search-box">
            <form method="post" action="blog.php">
              <div class="input-group">
                <input class="form-control" type="search" name="search" placeholder="Enter to Search" value="<?php echo htmlspecialchars($search_query); ?>" required="" autocomplete="off">
                <?php if (!empty($selected_category)): ?>
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($selected_category); ?>">
                <?php endif; ?>
              </div>
            </form>
          </div>
          
            <ul class="categorise-list">
              <div class="text-title">
              <h6><a href="<?php echo build_query_string(1, '', $search_query); ?>" class="all-categories-link <?php echo empty($selected_category) ? 'active-category' : ''; ?>">All Categories</a></h6>
              <?php foreach ($categories_with_counts as $cat): ?>
                <li>
                    <a href="<?php echo build_query_string(1, $cat['category_name'], $search_query); ?>"
                       class="<?php echo ($selected_category == $cat['category_name']) ? 'active-category' : ''; ?>">
                        <?php echo htmlspecialchars($cat['category_name']); ?>
                        <span class="category-count">(<?php echo $cat['event_count']; ?>)</span>
                    </a>
                </li>
                <?php endforeach; ?>
              </div>
            </ul>
          
          
        </div>
      </div>
    </div>
  </div>
</section>
<!-- End Contact Section -->

<!--footer-main-->
<?php include 'footer.php'; ?>
<!--End footer-main-->

<!-- scroll-to-top -->
<div id="back-to-top" class="back-to-top">
  <i class="fa fa-angle-up"></i>
</div>

</div>
<!--End pagewrapper-->


<!--Scroll to top-->
<div class="scroll-to-top scroll-to-target" data-target=".header-top">
  <span class="icon fa fa-angle-up"></span>
</div>


<!-- jquery -->
<script src="plugins/jquery.min.js"></script>
<!-- bootstrap -->
<script src="plugins/bootstrap/bootstrap.min.js"></script>
<!-- Slick Slider -->
<script src="plugins/slick/slick.min.js"></script>
<script src="plugins/slick/slick-animation.min.js"></script>
<!-- FancyBox -->
<script src="plugins/fancybox/jquery.fancybox.min.js" defer></script>
<!-- Google Map -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCcABaamniA6OL5YvYSpB3pFMNrXwXnLwU" defer></script>
<script src="plugins/google-map/gmap.js" defer></script>

<!-- jquery-ui -->
<script src="plugins/jquery-ui/jquery-ui.js" defer></script>
<!-- timePicker -->
<script src="plugins/timePicker/timePicker.js" defer></script>

<!-- script js -->
<script src="js/script.js"></script>
</body>

</html>
