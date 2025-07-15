<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = $_POST['item_id'] ?? null;
    $name = $_POST['name'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $available = isset($_POST['available']) ? 1 : 0;

    try {
        $conn = getConnection();
        
        if ($item_id) {
            // Update existing item
            $stmt = $conn->prepare("UPDATE menu_items SET name = ?, price = ?, category = ?, 
                                  description = ?, available = ? WHERE id = ?");
            $stmt->execute([$name, $price, $category, $description, $available, $item_id]);
        } else {
            // Add new item
            $stmt = $conn->prepare("INSERT INTO menu_items (name, price, category, description, available) 
                                  VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $price, $category, $description, $available]);
        }
        
        $_SESSION['menu_message'] = 'Menu item saved successfully!';
    } catch (Exception $e) {
        $_SESSION['menu_message'] = 'Error saving menu item: ' . $e->getMessage();
    }

    header('Location: owner_dashboard.php');
    exit();
}