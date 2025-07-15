<?php
session_start();
require_once 'db_connect.php';

if (isset($_GET['id'])) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("DELETE FROM menu_items WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        
        $_SESSION['menu_message'] = 'Menu item deleted successfully!';
    } catch (Exception $e) {
        $_SESSION['menu_message'] = 'Error deleting menu item: ' . $e->getMessage();
    }
}

header('Location: owner_dashboard.php');
exit();