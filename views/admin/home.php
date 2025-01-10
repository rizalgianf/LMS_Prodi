<?php
// Mulai session dan pastikan pengguna telah login sebagai admin
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

// Mengatur judul halaman
$page_title = "Admin Dashboard";
include '../../includes/header_admin.php'; // Menggunakan header khusus untuk admin
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
            <h3>Kelola Mahasiswa</h3>
            <a href="daftar_mahasiswa.php">Lihat Daftar Mahasiswa</a>
        </div>
        <div class="card">
            <h3>Kelola Dosen</h3>
            <a href="daftar_dosen.php">Lihat Daftar Dosen</a>
        </div>
        <div class="card">
            <h3>Jadwal Kuliah</h3>
            <a href="jadwal_kuliah.php">Kelola Jadwal Kuliah</a>
        </div>
        <div class="card">
            <h3>KBM</h3>
            <a href="kbm.php">Mengelola Perkuliahan</a>
        </div>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>
</body>
</html>