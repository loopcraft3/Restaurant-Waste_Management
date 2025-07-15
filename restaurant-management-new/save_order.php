<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $menu_item_id = $_POST['menu_item_id'];
    $quantity = $_POST['quantity'];
    $order_time = $_POST['order_time'];

    try {
        $conn = getConnection();
        $conn->beginTransaction();

        // Get menu item price
        $stmt = $conn->prepare("SELECT price FROM menu_items WHERE id = ?");
        $stmt->execute([$menu_item_id]);
        $menu_item = $stmt->fetch();
        $total_amount = $menu_item['price'] * $quantity;

        // Create order without customer_id
        $stmt = $conn->prepare("INSERT INTO orders (order_date, total_amount, customer_id) VALUES (?, ?, NULL)");
        $stmt->execute([date('Y-m-d ' . $order_time), $total_amount]);
        $order_id = $conn->lastInsertId();

        // Create order item
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, price_at_time) VALUES (?, ?, ?, ?)");
        $stmt->execute([$order_id, $menu_item_id, $quantity, $menu_item['price']]);

        // Update daily summary
        $stmt = $conn->prepare("
            INSERT INTO daily_sales_summary (date, total_orders, total_sales, total_items_sold)
            VALUES (CURDATE(), 1, ?, ?)
            ON DUPLICATE KEY UPDATE
                total_orders = total_orders + 1,
                total_sales = total_sales + ?,
                total_items_sold = total_items_sold + ?
        ");
        $stmt->execute([$total_amount, $quantity, $total_amount, $quantity]);

        $conn->commit();
        $_SESSION['order_message'] = 'Order recorded successfully!';
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['order_message'] = 'Error recording order: ' . $e->getMessage();
    }

    header('Location: owner_dashboard.php');
    exit();
}