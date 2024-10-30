<!-- views/admin/monitor_absen.php -->
<?php
// Mulai session dan pastikan pengguna telah login sebagai admin
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

// Mengatur judul halaman
$page_title = "Monitor Absensi";
include '../../includes/header_admin.php'; // Menggunakan header khusus untuk admin

// Ambil data absensi dari database (sementara menggunakan data dummy)
// Simulasikan data dengan array asosiatif sementara
$absensi = [
    ["id" => 1, "nama_mahasiswa" => "Andi", "mata_kuliah" => "Pemrograman Web", "dosen" => "Dr. Yanto", "tanggal" => "2024-11-01", "status" => "Hadir"],
    ["id" => 2, "nama_mahasiswa" => "Budi", "mata_kuliah" => "Matematika Diskrit", "dosen" => "Prof. Surya", "tanggal" => "2024-11-01", "status" => "Tidak Hadir"],
];

?>

<main class="main-content">
    <h2 class="page-title">Monitor Absensi Mahasiswa</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Mahasiswa</th>
                <th>Mata Kuliah</th>
                <th>Dosen</th>
                <th>Tanggal</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($absensi as $abs): ?>
                <tr>
                    <td><?php echo $abs['id']; ?></td>
                    <td><?php echo $abs['nama_mahasiswa']; ?></td>
                    <td><?php echo $abs['mata_kuliah']; ?></td>
                    <td><?php echo $abs['dosen']; ?></td>
                    <td><?php echo $abs['tanggal']; ?></td>
                    <td><?php echo $abs['status']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php include '../../includes/footer.php'; ?>
