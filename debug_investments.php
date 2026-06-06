<?php
// debug_investments.php
require_once 'includes/db.php';

echo "<h2>SparkX Database Debugger</h2>";

// 1. Check Database Connection
if ($conn) {
    echo "<p style='color: green;'>✓ Database connection successful on port " . $port . "</p>";
} else {
    echo "<p style='color: red;'>✗ Database connection failed: " . mysqli_connect_error() . "</p>";
    exit();
}

// 2. Count Users
$users_cnt = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM users"))['cnt'] ?? 0;
echo "<p>Total Users in DB: <strong>$users_cnt</strong></p>";

// 3. Count Active Plans
$plans_q = mysqli_query($conn, "SELECT id, name, status, hourly_rate FROM plans");
echo "<h3>Plans in Database:</h3>";
if (mysqli_num_rows($plans_q) > 0) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Plan ID</th><th>Name</th><th>Status</th><th>Hourly Rate (%)</th></tr>";
    while ($p = mysqli_fetch_assoc($plans_q)) {
        echo "<tr><td>{$p['id']}</td><td>{$p['name']}</td><td>{$p['status']}</td><td>{$p['hourly_rate']}%</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>✗ No plans found in database!</p>";
}

// 4. Count Investments
$inv_q = mysqli_query($conn, "SELECT * FROM investments");
echo "<h3>Investments in Database:</h3>";
if (mysqli_num_rows($inv_q) > 0) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>User ID</th><th>Plan ID</th><th>Amount</th><th>Status</th><th>Daily ROI</th><th>Hourly Rate</th><th>Created At</th></tr>";
    while ($i = mysqli_fetch_assoc($inv_q)) {
        echo "<tr>
                <td>{$i['id']}</td>
                <td>{$i['user_id']}</td>
                <td>{$i['plan_id']}</td>
                <td>\${$i['amount']}</td>
                <td style='font-weight: bold; color: " . ($i['status'] === 'active' ? 'green' : 'orange') . ";'>{$i['status']}</td>
                <td>{$i['daily_roi']}%</td>
                <td>{$i['hourly_rate']}%</td>
                <td>" . ($i['created_at'] ?? 'N/A') . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>✗ No investments found in the database!</p>";
}

// 5. Test Join Query
echo "<h3>Simulating Cron Join Query:</h3>";
$test_q = mysqli_query($conn, "SELECT i.*, p.name as plan_name 
                               FROM investments i 
                               JOIN plans p ON i.plan_id = p.id 
                               WHERE i.status = 'active'");
if ($test_q) {
    $count = mysqli_num_rows($test_q);
    echo "<p>Join query executed successfully. Found <strong>$count</strong> active investments that match the join condition.</p>";
    if ($count > 0) {
        echo "<ul>";
        while ($row = mysqli_fetch_assoc($test_q)) {
            echo "<li>Investment ID: {$row['id']} | User ID: {$row['user_id']} | Plan: {$row['plan_name']} | Amount: \${$row['amount']}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: orange;'>⚠ No active investments match the JOIN query! This means either:
              <br>1. There are NO records in <code>investments</code> table with <code>status = 'active'</code>.
              <br>2. The <code>plan_id</code> in the investments table does not match any existing <code>id</code> in the <code>plans</code> table.</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Join Query Error: " . mysqli_error($conn) . "</p>";
}
?>
