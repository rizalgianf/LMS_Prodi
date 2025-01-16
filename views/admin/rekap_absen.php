<?php
// Mulai session dan pastikan pengguna telah login sebagai admin
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../config/database.php'; // Koneksi database

$page_title = "Rekapitulasi Absensi";
include '../../includes/header.php'; // Ensure this path is correct

// Ambil data rekap absensi dari database
$sql_rekap = "SELECT pertemuan.id, pertemuan.tanggal, pertemuan.topik, 
              COUNT(CASE WHEN absensi.status = 'Tidak Hadir' THEN 1 END) AS jumlah_tidak_hadir
              FROM pertemuan
              LEFT JOIN absensi ON pertemuan.id = absensi.pertemuan_id
              GROUP BY pertemuan.id, pertemuan.tanggal, pertemuan.topik
              ORDER BY pertemuan.tanggal ASC";
$result_rekap = $conn->query($sql_rekap);
$rekap_list = [];
if ($result_rekap->num_rows > 0) {
    while ($row_rekap = $result_rekap->fetch_assoc()) {
        $rekap_list[] = $row_rekap;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../../css/style_rekap.css">
</head>
<body>
<main class="main-content">
    <h2 class="page-title">Rekapitulasi Absensi</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Topik</th>
                <th>Jumlah Tidak Hadir</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rekap_list as $rekap): ?>
                <tr>
                    <td><?php echo $rekap['tanggal']; ?></td>
                    <td><?php echo $rekap['topik']; ?></td>
                    <td><?php echo $rekap['jumlah_tidak_hadir']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php include '../../includes/footer.php'; ?>
</body>
</html>