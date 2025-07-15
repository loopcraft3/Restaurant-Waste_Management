<?php
include 'db_connect.php';

$conn = getConnection(); // Get the PDO connection

// 🔹 Best Selling Items
$bestSellingQuery = "SELECT menu_items.name, SUM(order_items.quantity) AS total_sold 
                     FROM order_items 
                     JOIN menu_items ON order_items.menu_item_id = menu_items.id 
                     GROUP BY menu_items.id 
                     ORDER BY total_sold DESC 
                     LIMIT 5";

$stmt = $conn->prepare($bestSellingQuery);
$stmt->execute();
$bestSellingResult = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 🔹 Peak Sales Hours
$peakHoursQuery = "SELECT HOUR(order_date) as hour, COUNT(id) as total_orders 
                   FROM orders 
                   GROUP BY hour 
                   ORDER BY total_orders DESC 
                   LIMIT 5";

$stmt = $conn->prepare($peakHoursQuery);
$stmt->execute();
$peakHoursResult = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 🔹 Sales Trends (Last 30 Days)
$salesTrendsQuery = "SELECT DATE(order_date) as order_day, SUM(total_amount) as total_sales 
                     FROM orders 
                     WHERE order_date >= CURDATE() - INTERVAL 30 DAY 
                     GROUP BY order_day 
                     ORDER BY order_day ASC";

$stmt = $conn->prepare($salesTrendsQuery);
$stmt->execute();
$salesTrendsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 🔹 Display Best-Selling Items
echo "<h3>Best Selling Items</h3>";
foreach ($bestSellingResult as $item) {
    echo "Item: " . $item['name'] . " - Sold: " . $item['total_sold'] . "<br>";
}

// 🔹 Display Peak Sales Hours
echo "<h3>Peak Sales Hours</h3>";
foreach ($peakHoursResult as $hour) {
    echo "Hour: " . $hour['hour'] . " - Orders: " . $hour['total_orders'] . "<br>";
}

// 🔹 Display Sales Trends
echo "<h3>Sales Trends (Last 30 Days)</h3>";
foreach ($salesTrendsResult as $trend) {
    echo "Date: " . $trend['order_day'] . " - Sales: " . $trend['total_sales'] . "<br>";
}
?>
