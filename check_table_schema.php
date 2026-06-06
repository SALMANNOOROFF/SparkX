<?php
// check_table_schema.php
require_once 'includes/db.php';

echo "<h2>SparkX Table Schema Inspector</h2>";

function inspect_table($conn, $table_name) {
    echo "<h3>Columns in <code>$table_name</code>:</h3>";
    $q = mysqli_query($conn, "DESCRIBE `$table_name`");
    if ($q) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = mysqli_fetch_assoc($q)) {
            echo "<tr>
                    <td><strong>{$row['Field']}</strong></td>
                    <td>{$row['Type']}</td>
                    <td>{$row['Null']}</td>
                    <td>{$row['Key']}</td>
                    <td>" . ($row['Default'] ?? 'NULL') . "</td>
                    <td>{$row['Extra']}</td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>Error describing $table_name: " . mysqli_error($conn) . "</p>";
    }
}

inspect_table($conn, 'users');
inspect_table($conn, 'wallets');
inspect_table($conn, 'transactions');
inspect_table($conn, 'investments');
?>
