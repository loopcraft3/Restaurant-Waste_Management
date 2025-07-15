<?php include 'header.php'; include 'db_connect.php';
$conn = getConnection(); ?>

<div class="container">
    <h2 class="text-center mb-4">📊 Owner Dashboard</h2>
    
    <div class="row">
       
        <div class="col-md-4">
            <a href="wastage_management.php" class="btn btn-warning w-100 mb-3">📉 Wastage Report</a>
        </div>
        <div class="col-md-4">
            <a href="sales_analysis.php" class="btn btn-success w-100 mb-3">📊 Sales Report</a>
        </div>
    </div>

    <h3 class="mt-4">📌 Menu Items</h3>
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Available</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php


$stmt = $conn->prepare("SELECT * FROM menu_items");
$stmt->execute();
$menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($menuItems as $row) {
    echo "<tr>
        <td>{$row['id']}</td>
        <td>{$row['name']}</td>
        <td>{$row['category']}</td>
        <td>\${$row['price']}</td>
        <td>" . ($row['available'] ? '✅' : '❌') . "</td>
        <td>
            <a href='delete_menu_item.php?id={$row['id']}' class='btn btn-danger btn-sm'>🗑 Delete</a>
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
date_default_timezone_set('Asia/Kolkata');

// Initialize database connection
$conn = getConnection();

// Check if connection is successful
if (!$conn) {
    die("Connection failed: Unable to connect to database");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Management - Owner Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between">
                <div class="flex space-x-7">
                    <div class="flex items-center py-4">
                        <span class="font-semibold text-gray-500 text-lg">Restaurant Management</span>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Add this before the Sales Overview div -->
            <?php
            // Fetch last 7 days sales data
            $stmt = $conn->query("
                SELECT 
                    DATE(order_date) as sale_date,
                    SUM(total_amount) as daily_sales
                FROM orders
                WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                GROUP BY DATE(order_date)
                ORDER BY sale_date ASC
            ");
            $salesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Prepare data for chart
            $dates = [];
            $sales = [];
            $currentDate = new DateTime();
            $currentDate->modify('-6 days');

            // Initialize arrays with zeros for all days
            for ($i = 0; $i < 7; $i++) {
                $dates[] = $currentDate->format('D');
                $sales[] = 0;
                $currentDate->modify('+1 day');
            }

            // Fill in actual sales data
            foreach ($salesData as $data) {
                $dayIndex = array_search(date('D', strtotime($data['sale_date'])), $dates);
                if ($dayIndex !== false) {
                    $sales[$dayIndex] = floatval($data['daily_sales']);
                }
            }

            // Convert to JSON for JavaScript
            $chartData = json_encode([
                'dates' => $dates,
                'sales' => $sales
            ]);
            ?>

            <!-- Sales Overview -->
            <div class="bg-white rounded-lg shadow-md p-6 md:col-span-2">
                <h2 class="text-2xl font-semibold mb-4">Sales Overview</h2>
                <canvas id="salesChart"></canvas>
            </div>

            <!-- Quick Stats -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-semibold mb-4">Today's Stats</h2>
                <?php
                $conn = getConnection();
                // Get today's stats
                $stmt = $conn->query("SELECT 
                    COUNT(*) as total_orders,
                    SUM(total_amount) as total_sales
                    FROM orders 
                    WHERE DATE(order_date) = CURDATE()");
                $stats = $stmt->fetch();
                ?>
                <div class="space-y-4">
                    <div class="p-4 bg-green-100 rounded-lg">
                        <div class="text-green-800 text-sm font-medium">Total Orders</div>
                        <div class="text-2xl font-bold"><?php echo $stats['total_orders']; ?></div>
                    </div>
                    <div class="p-4 bg-blue-100 rounded-lg">
                        <div class="text-blue-800 text-sm font-medium">Total Sales</div>
                        <div class="text-2xl font-bold">₹<?php echo number_format($stats['total_sales'], 2); ?></div>
                    </div>
                </div>
            </div>

            <!-- Order Input Form -->
            <div class="bg-white rounded-lg shadow-md p-6 col-span-full">
                <h2 class="text-2xl font-semibold mb-4">Record New Order</h2>
                <?php if (isset($_SESSION['order_message'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                        <?php echo htmlspecialchars($_SESSION['order_message']); ?>
                        <?php unset($_SESSION['order_message']); ?>
                    </div>
                <?php endif; ?>
                <form action="save_order.php" method="POST" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Menu Item</label>
                            <select name="menu_item_id" required class="w-full px-3 py-2 border rounded">
                                <?php
                                $menu_items = $conn->query("SELECT id, name, price FROM menu_items WHERE available = 1");
                                while($item = $menu_items->fetch()) {
                                    echo "<option value='{$item['id']}' data-price='{$item['price']}'>";
                                    echo htmlspecialchars($item['name']) . " - ₹" . number_format($item['price'], 2);
                                    echo "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Quantity</label>
                            <input type="number" name="quantity" required min="1" class="w-full px-3 py-2 border rounded">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Time of Order</label>
                            <input type="time" name="order_time" required class="w-full px-3 py-2 border rounded" 
                                value="<?php 
                                    date_default_timezone_set('Asia/Kolkata');
                                    echo date('H:i'); 
                                ?>" 
                                readonly>
                        </div>
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Record Order
                    </button>
                </form>
            </div>

            <!-- Wastage Analytics -->
            <div class="bg-white rounded-lg shadow-md p-6 md:col-span-2">
                <h2 class="text-2xl font-semibold mb-4">Wastage Analytics</h2>
                <?php
                // Get top wasted items
                $stmt = $conn->query("
                    SELECT item_name, 
                           SUM(quantity) as total_quantity,
                           COUNT(*) as frequency,
                           GROUP_CONCAT(DISTINCT reason) as reasons
                    FROM wastage 
                    GROUP BY item_name 
                    ORDER BY total_quantity DESC 
                    LIMIT 5
                ");
                $wasteAnalytics = $stmt->fetchAll();
                ?>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Wasted</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Frequency</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Common Reasons</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recommendation</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($wasteAnalytics as $item) { ?>
                            <tr>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($item['item_name']); ?></td>
                                <td class="px-6 py-4"><?php echo $item['total_quantity']; ?> units</td>
                                <td class="px-6 py-4"><?php echo $item['frequency']; ?> times</td>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($item['reasons']); ?></td>
                                <td class="px-6 py-4">
                                    <?php
                                    // Generate recommendations based on waste patterns
                                    if (strpos($item['reasons'], 'expired') !== false) {
                                        echo "Reduce order quantity and monitor expiration dates";
                                    } elseif (strpos($item['reasons'], 'overcooked') !== false) {
                                        echo "Review cooking procedures and staff training";
                                    } elseif (strpos($item['reasons'], 'leftover') !== false) {
                                        echo "Consider adjusting portion sizes";
                                    } else {
                                        echo "Monitor usage patterns and adjust inventory";
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Sales Analytics -->
            <div class="bg-white rounded-lg shadow-md p-6 md:col-span-2">
                <h2 class="text-2xl font-semibold mb-4">Sales Analytics</h2>
                <?php
                // Best selling items
                $stmt = $conn->query("
                    SELECT m.name, COUNT(oi.id) as order_count, SUM(oi.quantity) as total_quantity
                    FROM menu_items m
                    JOIN order_items oi ON m.id = oi.menu_item_id
                    GROUP BY m.id
                    ORDER BY total_quantity DESC
                    LIMIT 5
                ");
                $bestSellers = $stmt->fetchAll();

                // Peak hours analysis
                $stmt = $conn->query("
                    SELECT HOUR(order_date) as hour,
                           COUNT(*) as order_count,
                           SUM(total_amount) as total_sales
                    FROM orders
                    WHERE DATE(order_date) = CURDATE()
                    GROUP BY HOUR(order_date)
                    ORDER BY order_count DESC
                ");
                $peakHours = $stmt->fetchAll();
                ?>
                
                <!-- Best Sellers Table -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold mb-3">Best Selling Items</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Orders</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity Sold</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bestSellers as $item): ?>
                                <tr class="bg-white">
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td class="px-6 py-4"><?php echo $item['order_count']; ?></td>
                                    <td class="px-6 py-4"><?php echo $item['total_quantity']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Peak Hours Analysis -->
                <div>
                    <h3 class="text-lg font-semibold mb-3">Peak Hours Today</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($peakHours as $hour): ?>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <div class="text-lg font-medium">
                                <?php echo date('g:i A', strtotime($hour['hour'] . ':00')); ?>
                            </div>
                            <div class="text-sm text-gray-600">
                                Orders: <?php echo $hour['order_count']; ?><br>
                                Sales: ₹<?php echo number_format($hour['total_sales'], 2); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Wastage -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-semibold mb-4">Recent Wastage</h2>
                <?php
                $stmt = $conn->query("SELECT * FROM wastage ORDER BY date_recorded DESC LIMIT 5");
                while($row = $stmt->fetch()) {
                ?>
                <div class="border-b py-2">
                    <div class="flex justify-between items-center">
                        <span class="font-medium"><?php echo htmlspecialchars($row['item_name']); ?></span>
                        <span class="text-red-600"><?php echo $row['quantity']; ?> units</span>
                    </div>
                    <div class="text-sm text-gray-600">
                        <?php echo date('M d, Y', strtotime($row['date_recorded'])); ?>
                    </div>
                </div>
                <?php } ?>
            </div>

            <!-- Menu Management -->
            <div class="bg-white rounded-lg shadow-md p-6 col-span-full">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-semibold">Menu Management</h2>
                    <button onclick="showAddMenuForm()" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                        Add New Item
                    </button>
                </div>

                <!-- Add/Edit Menu Form (Hidden by default) -->
                <div id="menuForm" class="hidden mb-6 p-4 bg-gray-50 rounded-lg">
                    <form action="save_menu_item.php" method="POST" class="space-y-4">
                        <input type="hidden" name="item_id" id="editItemId">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Name</label>
                                <input type="text" name="name" id="itemName" required 
                                    class="w-full px-3 py-2 border rounded">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Price</label>
                                <input type="number" name="price" id="itemPrice" required step="0.01" min="0" 
                                    class="w-full px-3 py-2 border rounded">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Category</label>
                                <input type="text" name="category" id="itemCategory" required 
                                    class="w-full px-3 py-2 border rounded">
                            </div>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Description</label>
                            <textarea name="description" id="itemDescription" 
                                class="w-full px-3 py-2 border rounded"></textarea>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="available" id="itemAvailable" class="mr-2">
                            <label class="text-gray-700 text-sm font-bold">Available</label>
                        </div>
                        <div class="flex justify-end space-x-2">
                            <button type="button" onclick="hideMenuForm()" 
                                class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                                Cancel
                            </button>
                            <button type="submit" 
                                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                Save Item
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Menu Items Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Available</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php
                            $menu_items = $conn->query("SELECT * FROM menu_items ORDER BY category, name");
                            while($item = $menu_items->fetch()) {
                            ?>
                            <tr>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($item['name']); ?></td>
                                <td class="px-6 py-4">₹<?php echo number_format($item['price'], 2); ?></td>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($item['category']); ?></td>
                                <td class="px-6 py-4">
                                    <span class="<?php echo $item['available'] ? 'text-green-600' : 'text-red-600'; ?>">
                                        <?php echo $item['available'] ? 'Yes' : 'No'; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <button onclick="editMenuItem(<?php echo htmlspecialchars(json_encode($item)); ?>)" 
                                        class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                                    <button onclick="deleteMenuItem(<?php echo $item['id']; ?>)" 
                                        class="text-red-600 hover:text-red-900">Delete</button>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Sales Chart
    const chartData = <?php echo $chartData; ?>;
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.dates,
            datasets: [{
                label: 'Daily Sales (₹)',
                data: chartData.sales,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Weekly Sales Overview'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₹' + value.toLocaleString('en-IN');
                        }
                    }
                }
            }
        }
    });

        function showAddMenuForm() {
            document.getElementById('menuForm').classList.remove('hidden');
            document.getElementById('editItemId').value = '';
            document.getElementById('itemName').value = '';
            document.getElementById('itemPrice').value = '';
            document.getElementById('itemCategory').value = '';
            document.getElementById('itemDescription').value = '';
            document.getElementById('itemAvailable').checked = true;
        }

        function hideMenuForm() {
            document.getElementById('menuForm').classList.add('hidden');
        }

        function editMenuItem(item) {
            document.getElementById('menuForm').classList.remove('hidden');
            document.getElementById('editItemId').value = item.id;
            document.getElementById('itemName').value = item.name;
            document.getElementById('itemPrice').value = item.price;
            document.getElementById('itemCategory').value = item.category;
            document.getElementById('itemDescription').value = item.description;
            document.getElementById('itemAvailable').checked = item.available == 1;
        }

        function deleteMenuItem(itemId) {
            if (confirm('Are you sure you want to delete this menu item?')) {
                window.location.href = `delete_menu_item.php?id=${itemId}`;
            }
        }
    </script>
</body>
</html>