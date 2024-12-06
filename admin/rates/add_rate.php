<?php
// Include your database connection file
require_once 'db_connection.php';  // Or wherever your DB connection is defined

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get data from POST request
    $activity_class = trim($_POST['activity_class']);
    $rate_per_hour = trim($_POST['rate_per_hour']);
    
    // Sanitize inputs (basic sanitization for safety)
    $activity_class = mysqli_real_escape_string($conn, $activity_class);
    $rate_per_hour = mysqli_real_escape_string($conn, $rate_per_hour);

    // Prepare SQL to insert the new rate into the database
    $sql = "INSERT INTO `rates` (activity_class, rate_per_hour) VALUES ('$activity_class', '$rate_per_hour')";
    
    // Execute the query and check for success
    if ($conn->query($sql) === TRUE) {
        // Respond with success
        echo json_encode(['status' => 'success']);
    } else {
        // If error occurs, respond with failure
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
}
?>
