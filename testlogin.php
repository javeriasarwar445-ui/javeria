<?php
// Test if the hash works
$stored_hash = '$2y$10$C6UzMDM.H6dfI/f/IKcEe.Ol2M0QOeTP1fM6ypu0WnU4Gxv8xP8dq';
$password = 'admin123';

echo "Testing password verification:<br><br>";
echo "Password: " . $password . "<br>";
echo "Hash: " . $stored_hash . "<br><br>";

if (password_verify($password, $stored_hash)) {
    echo "✅ Password verification: SUCCESS<br>";
} else {
    echo "❌ Password verification: FAILED<br>";
}

echo "<br>--- Database Test ---<br>";

// Test database connection
require_once 'config/database.php';
$db = new Database();
$conn = $db->getConnection();

$username = 'admin';
$stmt = $conn->prepare("SELECT id, username, password FROM admin WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    echo "✅ User found in database<br>";
    $admin = $result->fetch_assoc();
    echo "ID: " . $admin['id'] . "<br>";
    echo "Username: " . $admin['username'] . "<br>";
    echo "Hash from DB: " . $admin['password'] . "<br><br>";
    
    if (password_verify('admin123', $admin['password'])) {
        echo "✅ Password matches database hash<br>";
    } else {
        echo "❌ Password does NOT match database hash<br>";
    }
} else {
    echo "❌ User NOT found in database<br>";
}

$stmt->close();
?>