<?php include 'header.php'; include 'db_connect.php'; ?>

<div class="container">
    <h2 class="text-center mb-4">🍽 Customer Dashboard</h2>

    <h3 class="mt-4">📌 Available Menu</h3>
    <table class="table table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Item</th>
                <th>Category</th>
                <th>Price</th>
                <th>Order</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = $conn->query("SELECT * FROM menu_items WHERE available = 1");
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['name']}</td>
                    <td>{$row['category']}</td>
                    <td>\${$row['price']}</td>
                    <td>
                        <form action='save_order.php' method='POST'>
                            <input type='hidden' name='menu_id' value='{$row['id']}'>
                            <input type='number' name='quantity' min='1' max='10' class='form-control mb-2' required>
                            <button type='submit' class='btn btn-success btn-sm'>🛒 Order</button>
                        </form>
                    </td>
                </tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php
session_start();
require_once 'db_connect.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Portal - Customer Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between">
                <div class="flex space-x-7">
                    <div class="flex items-center py-4">
                        <span class="font-semibold text-gray-500 text-lg">Restaurant Portal</span>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Menu Section -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-semibold mb-4">Today's Menu</h2>
                <div class="space-y-4">
                    <?php
                    // Fetch menu items from database
                    $conn = getConnection();
                    $stmt = $conn->query("SELECT * FROM menu_items WHERE available = 1");
                    while($row = $stmt->fetch()) {
                    ?>
                    <div class="flex justify-between items-center border-b pb-2">
                        <div>
                            <h3 class="font-medium"><?php echo htmlspecialchars($row['name']); ?></h3>
                            <p class="text-gray-600"><?php echo htmlspecialchars($row['description']); ?></p>
                        </div>
                        <div class="text-lg font-semibold">
                            ₹<?php echo number_format($row['price'], 2); ?>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>

            <!-- Wastage Report Form -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-semibold mb-4">Report Food Wastage</h2>
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['success_message']); ?></span>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['error_message']); ?></span>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>
                <form action="save_wastage.php" method="POST" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Menu Item
                            </label>
                            <select name="item_name" required 
                                class="shadow border rounded w-full py-2 px-3 text-gray-700">
                                <?php
                                $menu_items = $conn->query("SELECT name FROM menu_items WHERE available = 1 ORDER BY name");
                                while($item = $menu_items->fetch()) {
                                    echo "<option value='" . htmlspecialchars($item['name']) . "'>";
                                    echo htmlspecialchars($item['name']);
                                    echo "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Quantity (in units)
                            </label>
                            <input type="number" name="quantity" required min="1"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Reason
                            </label>
                            <select name="reason" required
                                class="shadow border rounded w-full py-2 px-3 text-gray-700">
                                <option value="expired">Expired</option>
                                <option value="damaged">Damaged</option>
                                <option value="overcooked">Overcooked</option>
                                <option value="leftover">Customer Leftover</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" 
                        class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">
                        Submit Report
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>