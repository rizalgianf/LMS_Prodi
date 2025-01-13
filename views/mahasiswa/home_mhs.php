<?php
// Mulai session dan pastikan pengguna telah login sebagai admin
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../../login.php");
    exit();
}

// Mengatur judul halaman
$page_title = "Mahasiswa Dashboard";
include '../../includes/header_mahasiswa.php'; // Menggunakan header khusus untuk admin
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
    <h2 class="page-title">Mahasiswa Dashboard Overview</h2>
    <div class="dashboard-cards">
        <div class="card">
            <h3>Kegiatan Belajar Mengajar</h3>
            <a href="kbm_mhs.php">KBM</a>
        </div>
        <div class="card">
            <h3>Lihat Jadwal</h3>
            <a href="lihat_jadwal_mhs.php">Lihat Jadwal</a>
        </div>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>
</body>
</html>