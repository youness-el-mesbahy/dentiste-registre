<?php
// Script to create a test user with dentiste role
include "connection.php";

// Check if user already exists
$email = "dentiste@example.com";
$checkQuery = "SELECT * FROM users WHERE email = ?";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows > 0) {
    echo "User already exists!";
} else {
    // Create new user
    $password = "password123"; // This is just for testing
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $role = "dentiste";
    
    $query = "INSERT INTO users (email, password, role, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $email, $hashedPassword, $role);
    
    if ($stmt->execute()) {
        echo "Test user created successfully!<br>";
        echo "Email: " . $email . "<br>";
        echo "Password: " . $password . "<br>";
        echo "Role: " . $role . "<br>";
        echo "<p>You can now <a href='../../login.php'>login</a> with these credentials.</p>";
    } else {
        echo "Error creating user: " . $stmt->error;
    }
}
?>
