<!-- views/admin/home.php -->
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
            <h3>Monitor Absensi</h3>
            <a href="monitor_absen.php">Lihat Absensi Mahasiswa & Dosen</a>
        </div>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>
