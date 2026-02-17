<?php
echo "<h2>System Check for 'grocery' folder</h2>";

echo "<h3>Folder Structure:</h3>";
$folders_to_check = [
    'includes' => 'includes/config.php',
    'auth' => 'auth/login.php',
    'pages' => 'pages/dashboard.php',
    'assets/css' => 'assets/css/style.css',
    'assets/js' => 'assets/js/validation.js'
];

foreach ($folders_to_check as $folder => $file) {
    if (file_exists($folder)) {
        echo "✅ Folder '$folder' exists<br>";
        if (file_exists($file)) {
            echo "&nbsp;&nbsp;&nbsp;✅ File '" . basename($file) . "' exists<br>";
        } else {
            echo "&nbsp;&nbsp;&nbsp;❌ File '" . basename($file) . "' missing<br>";
        }
    } else {
        echo "❌ Folder '$folder' missing<br>";
    }
}

echo "<h3>Database Connection:</h3>";
if (file_exists('includes/config.php')) {
    include 'includes/config.php';

    if ($conn) {
        echo "✅ Database connected successfully<br>";

        // Check tables
        $tables = ['admin', 'categories', 'products', 'customers', 'suppliers'];
        foreach ($tables as $table) {
            $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
            if (mysqli_num_rows($result) > 0) {
                echo "✅ Table '$table' exists<br>";
            } else {
                echo "❌ Table '$table' missing<br>";
            }
        }
    } else {
        echo "❌ Database connection failed<br>";
    }
} else {
    echo "❌ config.php not found<br>";
}
