<?php
session_start();
include 'config/database.php'; // File untuk koneksi database

// Ambil data dari form
$user = $_POST['username'];
$pass = $_POST['password'];

if (!empty($user) && !empty($pass)) {
    // Query untuk memeriksa user
    $sql = "SELECT * FROM users WHERE username=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($pass, $row['password'])) {
            // Set session berdasarkan peran
            $_SESSION['username'] = $user;
            $_SESSION['role'] = $row['role']; // Menyimpan peran pengguna di session

            // Arahkan berdasarkan peran
            if ($row['role'] == 'admin') {
                header("Location: views/admin_dashboard.php");
            } elseif ($row['role'] == 'dosen') {
                header("Location: views/lecturer_dashboard.php");
            } elseif ($row['role'] == 'mahasiswa') {
                header("Location: views/student_dashboard.php");
            }
            exit();
        } else {
            echo "Password salah!";
        }
    } else {
        echo "Username tidak ditemukan!";
    }
    $stmt->close();
} else {
    echo "Silakan isi form login terlebih dahulu.";
}

$conn->close();
?>
