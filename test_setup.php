<?php
require_once 'db_connect.php';

echo "<h1>MoovyDoovy Admin Setup Test</h1>";

// Check if new admin users exist
$result = $conn->query("SELECT u.first_name, u.last_name, u.email, a.user_id as is_admin 
                        FROM users u 
                        LEFT JOIN admin a ON u.user_id = a.user_id 
                        WHERE u.email IN ('admin1@email.com', 'admin2@email.com')");

echo "<h2>Admin Users Status:</h2>";
if ($result && $result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Name</th><th>Email</th><th>Admin Status</th></tr>";
    while ($row = $result->fetch_assoc()) {
        $admin_status = $row['is_admin'] ? '✅ Admin' : '❌ Not Admin';
        echo "<tr>";
        echo "<td>{$row['first_name']} {$row['last_name']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "<td>$admin_status</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ No admin users found. Run add_admins.sql first!</p>";
}

// Test database tables
echo "<h2>Database Tables Status:</h2>";
$tables = ['users', 'admin', 'movie', 'hall', 'showtime', 'booking'];
echo "<ul>";
foreach ($tables as $table) {
    $count_result = $conn->query("SELECT COUNT(*) as count FROM $table");
    $count = $count_result->fetch_assoc()['count'];
    echo "<li><strong>$table</strong>: $count records</li>";
}
echo "</ul>";

echo "<h2>Quick Links:</h2>";
echo "<p><a href='index.php'>Login Page</a></p>";
echo "<p><a href='admin_page.php'>Admin Dashboard</a> (login as admin first)</p>";
echo "<p><a href='Movies.php'>Movies Management</a> (admin only)</p>";
echo "<p><a href='showtimes.php'>Showtimes Management</a> (admin only)</p>";

echo "<h2>Test Login Credentials:</h2>";
echo "<ul>";
echo "<li><strong>Admin 1:</strong> admin1@email.com / admin12</li>";
echo "<li><strong>Admin 2:</strong> admin2@email.com / admin12</li>";
echo "<li><strong>Customer:</strong> john@example.com / password123</li>";
echo "</ul>";

$conn->close();
?>