<?php
// Redirect to dashboard if user is logged in (Optional)
// session_start();
// if (isset($_SESSION['user_id'])) {
//     header("Location: customer_dashboard.php");
//     exit();
// }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
        }
        h2 { color: #333; }
        .menu { margin-top: 20px; }
        .menu a {
            display: block;
            padding: 10px;
            margin: 10px auto;
            width: 250px;
            text-decoration: none;
            background: #3498db;
            color: white;
            border-radius: 5px;
        }
        .menu a:hover { background: #2980b9; }
    </style>
</head>
<body>

    <h2>Welcome to Restaurant Management System</h2>

    <div class="menu">
        <a href="owner_dashboard.php">Owner Dashboard</a>
        <a href="customer_dashboard.php">Customer Dashboard</a>
        <a href="save_order.php">Save Order</a>
        <a href="save_menu_item.php">Save Menu Item</a>
        <a href="delete_menu_item.php">Delete Menu Item</a>
        <a href="save_wastage.php">Save Wastage</a>
        <a href="sales_analysis.php">Sales Analysis</a>
    </div>

</body>
</html>
