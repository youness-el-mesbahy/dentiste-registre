<?php
// Script to create an admin user
include "connection.php";

// Check if user already exists
$email = "admin@example.com";
$checkQuery = "SELECT * FROM users WHERE email = ?";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows > 0) {
    echo "Admin user already exists!";
} else {
    // Create new admin user
    $password = "admin123"; // This is just for testing
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $role = "admin";
    $nom = "Admin";
    $prenom = "User";
    
    $query = "INSERT INTO users (email, nom, prenom, password, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssss", $email, $nom, $prenom, $hashedPassword, $role);
    
    if ($stmt->execute()) {
        echo "Admin user created successfully!<br>";
        echo "Email: " . $email . "<br>";
        echo "Password: " . $password . "<br>";
        echo "Role: " . $role . "<br>";
        echo "<p>You can now <a href='../../login.php'>login</a> with these credentials.</p>";
    } else {
        echo "Error creating user: " . $stmt->error;
    }
}
?>
