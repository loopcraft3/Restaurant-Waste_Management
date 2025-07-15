<?php include 'header.php'; include 'db_connect.php';
$conn = getConnection(); ?>

<div class="container">
    <h2 class="text-center mb-4">📉 Wastage Management</h2>

    <form action="save_wastage.php" method="POST" class="p-4 border bg-light">
        <div class="mb-3">
            <label class="form-label">Item Name:</label>
            <select name="item_name" class="form-control" required>
                <option value="">Select Item</option>
                <?php
                $stmt = $conn->prepare("SELECT name FROM menu_items WHERE available = 1 ORDER BY name");
                $stmt->execute();
                while ($row = $stmt->fetch()) {
                    echo "<option value='" . htmlspecialchars($row['name']) . "'>" . htmlspecialchars($row['name']) . "</option>";
                }
                ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Quantity:</label>
            <input type="number" name="quantity" class="form-control" min="1" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Reason:</label>
            <select name="reason" class="form-control" required>
                <option value="">Select Reason</option>
                <option value="Expired">Expired/Near expiry</option>
                <option value="Overcooked">Overcooked/Cooking error</option>
                <option value="Leftover">Customer leftover</option>
                <option value="Damaged">Damaged in storage/transit</option>
                <option value="Quality">Quality issues</option>
                <option value="Over preparation">Over preparation</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <button type="submit" class="btn btn-warning w-100">Submit Wastage Report</button>
    </form>

    <h3 class="mt-4">Recent Wastage Reports</h3>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Reason</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $query = "SELECT * FROM wastage WHERE date_recorded >= NOW() - INTERVAL 30 DAY ORDER BY date_recorded DESC";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $wastageData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $index = 1;
            foreach ($wastageData as $row) {
                echo "<tr>
                    <td>{$index}</td>
                    <td>{$row['item_name']}</td>
                    <td>{$row['quantity']}</td>
                    <td>{$row['reason']}</td>
                    <td>{$row['date_recorded']}</td>
                </tr>";
                $index++;
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
<script>
document.querySelector('select[name="reason"]').addEventListener('change', function() {
    const otherReasonDiv = document.getElementById('otherReasonDiv');
    otherReasonDiv.style.display = this.value === 'Other' ? 'block' : 'none';
});
</script>
