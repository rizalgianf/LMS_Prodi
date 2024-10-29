<?php
session_start();
include 'config/database.php'; // Koneksi database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Mengambil data dari form
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role']; // Mengambil nilai role dari form

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Menyimpan username, hashed password, dan role ke database
    $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $hashed_password, $role); // Bind role juga

    if ($stmt->execute()) {
        echo "Registrasi berhasil!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>
