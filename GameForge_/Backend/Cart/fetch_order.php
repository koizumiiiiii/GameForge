<?php
include '../../Backend/session_start.php'; // Start the session
include '../../dbconnection/dbconnect.php'; // Include database connection file

$user_id = $_SESSION['user_id']; // Retrieve user ID from session

// SQL query to fetch order details along with product images and names
$query = "SELECT o.id, o.total, o.created_at, 
                 COALESCE(GROUP_CONCAT(DISTINCT p.image_url SEPARATOR ','), 'default.png') AS image_urls, 
                 COALESCE(GROUP_CONCAT(DISTINCT p.name SEPARATOR ', '), 'No Product') AS product_names,
                 SUBSTRING_INDEX(COALESCE(GROUP_CONCAT(DISTINCT p.name ORDER BY oi.id SEPARATOR ', '), 'No Product'), ',', 1) AS order_name
          FROM orders o
          LEFT JOIN order_items oi ON o.id = oi.order_id
          LEFT JOIN products p ON oi.product_id = p.id
          WHERE o.user_id = ?
          GROUP BY o.id, o.total, o.created_at
          ORDER BY o.created_at DESC";

$stmt = $conn->prepare($query); // Prepare the SQL statement
$stmt->bind_param("i", $user_id); // Bind user_id as an integer
$stmt->execute(); // Execute the query
$result = $stmt->get_result(); // Get the result set

$stmt->close(); // Close the statement
$conn->close(); // Close the database connection
?>
