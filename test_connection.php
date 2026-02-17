<?php
include 'includes/config.php';

echo "<h2>Database Connection Test</h2>";

// Check connection
if ($conn) {
    echo "✅ Database connected successfully!<br>";
} else {
    echo "❌ Database connection failed!<br>";
}

// Check if admin table exists
$result = mysqli_query($conn, "SHOW TABLES LIKE 'admin'");
if (mysqli_num_rows($result) > 0) {
    echo "✅ Admin table exists<br>";

    // Check if admin user exists
    $admin_check = mysqli_query($conn, "SELECT * FROM admin WHERE username = 'admin'");
    if (mysqli_num_rows($admin_check) > 0) {
        $admin = mysqli_fetch_assoc($admin_check);
        echo "✅ Admin user found: " . $admin['username'] . "<br>";
        echo "✅ Admin password in database: " . $admin['password'] . "<br>";
    } else {
        echo "❌ Admin user not found!<br>";

        // Insert admin if not exists
        $insert = mysqli_query($conn, "INSERT INTO admin (username, password) VALUES ('admin', 'admin123')");
        if ($insert) {
            echo "✅ Admin user inserted successfully!<br>";
        } else {
            echo "❌ Failed to insert admin: " . mysqli_error($conn) . "<br>";
        }
    }
} else {
    echo "❌ Admin table does not exist!<br>";
    echo "Please run the SQL setup first.<br>";
}

// Show all tables
echo "<h3>Tables in database:</h3>";
$tables = mysqli_query($conn, "SHOW TABLES");
if (mysqli_num_rows($tables) > 0) {
    while ($table = mysqli_fetch_array($tables)) {
        echo "📁 " . $table[0] . "<br>";
    }
} else {
    echo "No tables found!";
}
