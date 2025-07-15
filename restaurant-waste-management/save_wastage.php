<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item_name = trim($_POST['item_name']);
    $quantity = floatval($_POST['quantity']);
    $cost = floatval($_POST['cost']);
    $reason = trim($_POST['reason']);
    $recorded_by = 1; // Change this to the logged-in user ID (if authentication exists)

    // Validation: Ensure required fields are filled
    if (empty($item_name) || $quantity <= 0 || $cost <= 0) {
        echo "<script>alert('Invalid input! Please check your values.'); window.history.back();</script>";
        exit();
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO wastage (item_name, quantity, cost, reason, recorded_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sddsi", $item_name, $quantity, $cost, $reason, $recorded_by);

    if ($stmt->execute()) {
        echo "<script>alert('Wastage recorded successfully!'); window.location.href='wastage_management.php';</script>";
    } else {
        echo "<script>alert('Error recording wastage.'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
