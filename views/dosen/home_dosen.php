<?php
// Mulai session dan pastikan pengguna telah login sebagai admin
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'dosen') {
    header("Location: ../../login.php");
    exit();
}

// Mengatur judul halaman
$page_title = "Admin Dashboard";
include '../../includes/header_dosen.php'; // Menggunakan header khusus untuk admin
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../../css/style_home.css">
</head>
<body>
<main class="main-content">
    <h2 class="page-title">Admin Dashboard Overview</h2>
    <div class="dashboard-cards">
        <div class="card">
            <h3>Kelola Kelas</h3>
            <a href="kelola_kelas_dosen.php">Kelola Kelas</a>
        </div>
        <div class="card">
            <h3>Lihat Jadwal</h3>
            <a href="lihat_jadwal_dosen.php">Lihat Jadwal</a>
        </div>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>
</body>
</html>