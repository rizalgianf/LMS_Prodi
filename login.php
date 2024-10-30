<?php
session_start();
include 'config/database.php'; // Pastikan file database.php sudah ada dan benar

// Ambil data dari form menggunakan metode POST
$user = $_POST['username'] ?? ''; // Gunakan null coalescing untuk menghindari notice jika field tidak diisi
$pass = $_POST['password'] ?? '';

// Periksa jika username dan password tidak kosong
if (empty($user) || empty($pass)) {
    // Redirect ke login.html dengan parameter error
    header("Location: login.html?error=emptyfields");
    exit();
}

// Query untuk memeriksa user berdasarkan username
$sql = "SELECT * FROM users WHERE username=?";
$stmt = $conn->prepare($sql);

// Periksa apakah statement SQL berhasil disiapkan
if ($stmt) {
    // Bind parameter dan eksekusi statement
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    // Periksa apakah ada hasil dari query
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Verifikasi password yang diinput dengan password yang ada di database
        if (password_verify($pass, $row['password'])) {
            // Set session berdasarkan data user
            $_SESSION['username'] = $user;
            $_SESSION['role'] = $row['role'];

            // Arahkan berdasarkan peran pengguna
            switch ($row['role']) {
                case 'admin':
                    header("Location: views/admin_dashboard.php");
                    break;
                case 'dosen':
                    header("Location: views/lecturer_dashboard.php");
                    break;
                case 'mahasiswa':
                    header("Location: views/student_dashboard.php");
                    break;
                default:
                    // Jika role tidak dikenali, kembalikan ke login.html
                    header("Location: login.html?error=invalidrole");
                    break;
            }
            exit();
        } else {
            // Password salah, arahkan kembali ke halaman login.html dengan pesan error
            header("Location: login.html?error=wrongpassword");
            exit();
        }
    } else {
        // Username tidak ditemukan
        header("Location: login.html?error=usernotfound");
        exit();
    }

    // Tutup statement
    $stmt->close();
} else {
    // Jika statement SQL gagal disiapkan
    header("Location: login.html?error=sqlerror");
    exit();
}

// Tutup koneksi
$conn->close();
?>
