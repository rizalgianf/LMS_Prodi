<?php
// Mulai session dan pastikan pengguna telah login sebagai dosen
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'dosen') {
    header("Location: ../../login.php");
    exit();
}

include '../../config/database.php'; // Koneksi database

$page_title = "Kelola Kelas Dosen";
include '../../includes/header_dosen.php';

$kelas_id = $_GET['id'] ?? '';
if (empty($kelas_id)) {
    header("Location: kbm_dosen.php");
    exit();
}

// Ambil data kelas
$sql_kelas = "SELECT kelas.id, cohort.nama_cohort, mata_kuliah.nama AS mata_kuliah, mata_kuliah.jumlah_sks, users.nama AS dosen
              FROM kelas
              JOIN cohort ON kelas.id_cohort = cohort.id
              JOIN mata_kuliah ON kelas.mata_kuliah_id = mata_kuliah.id
              JOIN users ON kelas.dosen_id = users.id
              WHERE kelas.id = ?";
$stmt_kelas = $conn->prepare($sql_kelas);
$stmt_kelas->bind_param("i", $kelas_id);
$stmt_kelas->execute();
$result_kelas = $stmt_kelas->get_result();
$kelas = $result_kelas->fetch_assoc();
$stmt_kelas->close();

if (!$kelas) {
    echo "Kelas tidak ditemukan.";
    exit();
}

// Ambil data pertemuan dari database
$sql_pertemuan = "SELECT * FROM pertemuan WHERE kelas_id = ?";
$stmt_pertemuan = $conn->prepare($sql_pertemuan);
$stmt_pertemuan->bind_param("i", $kelas_id);
$stmt_pertemuan->execute();
$result_pertemuan = $stmt_pertemuan->get_result();
$pertemuan_list = [];
if ($result_pertemuan->num_rows > 0) {
    while ($row_pertemuan = $result_pertemuan->fetch_assoc()) {
        $pertemuan_list[] = $row_pertemuan;
    }
}
$stmt_pertemuan->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../../css/style_kbm.css">
</head>
<body>
<main class="main-content">
    <h2 class="page-title">Kelola Kelas: <?php echo $kelas['nama_cohort']; ?></h2>
    <h2>Mata Kuliah: <?php echo $kelas['mata_kuliah']; ?></h2>
    <p>SKS: <?php echo $kelas['jumlah_sks']; ?></p>
    <p>Dosen: <?php echo $kelas['dosen']; ?></p>
    <p>Hubungi admin untuk perubahan jadwal</p>

    <table class="data-table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Hari</th>
                <th>Waktu Mulai</th>
                <th>Waktu Selesai</th>
                <th>Topik</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pertemuan_list as $pertemuan): ?>
                <tr>
                    <td><?php echo $pertemuan['tanggal']; ?></td>
                    <td><?php echo $pertemuan['hari']; ?></td>
                    <td><?php echo $pertemuan['waktu_mulai']; ?></td>
                    <td><?php echo $pertemuan['waktu_selesai']; ?></td>
                    <td><?php echo $pertemuan['topik']; ?></td>
                    <td>
                        <a class="kelola" href="kelola_pertemuan_dosen.php?id=<?php echo $pertemuan['id']; ?>">Kelola</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php include '../../includes/footer.php'; ?>
</body>
</html>