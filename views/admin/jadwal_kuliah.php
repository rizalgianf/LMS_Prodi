<!-- views/admin/jadwal_kuliah.php -->
<?php
// Mulai session dan pastikan pengguna telah login sebagai admin
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

// Mengatur judul halaman
$page_title = "Jadwal Kuliah";
include '../../includes/header_admin.php'; // Menggunakan header khusus untuk admin

// Ambil data jadwal dari database (sementara menggunakan data dummy)
// Simulasikan data dengan array asosiatif sementara
$jadwal = [
    ["id" => 1, "mata_kuliah" => "Pemrograman Web", "dosen" => "Dr. Yanto", "hari" => "Senin", "waktu" => "10:00 - 12:00"],
    ["id" => 2, "mata_kuliah" => "Matematika Diskrit", "dosen" => "Prof. Surya", "hari" => "Selasa", "waktu" => "08:00 - 10:00"],
];

?>

<main class="main-content">
    <h2 class="page-title">Jadwal Kuliah</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Mata Kuliah</th>
                <th>Dosen</th>
                <th>Hari</th>
                <th>Waktu</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($jadwal as $jdwl): ?>
                <tr>
                    <td><?php echo $jdwl['id']; ?></td>
                    <td><?php echo $jdwl['mata_kuliah']; ?></td>
                    <td><?php echo $jdwl['dosen']; ?></td>
                    <td><?php echo $jdwl['hari']; ?></td>
                    <td><?php echo $jdwl['waktu']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php include '../../includes/footer.php'; ?>
