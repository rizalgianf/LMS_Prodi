<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'dosen') {
    header("Location: ../login.php");
    exit();
}

include '../../config/database.php'; // Koneksi database

$dosen_id = $_SESSION['user_id'];

// Ambil data jadwal dari database
$sql_jadwal = "SELECT jadwal_kuliah.*, mata_kuliah.nama AS mata_kuliah_nama
               FROM jadwal_kuliah
               JOIN mata_kuliah ON jadwal_kuliah.mata_kuliah = mata_kuliah.id
               WHERE jadwal_kuliah.dosen_id = ?";
$stmt_jadwal = $conn->prepare($sql_jadwal);
$stmt_jadwal->bind_param("i", $dosen_id);
$stmt_jadwal->execute();
$result_jadwal = $stmt_jadwal->get_result();
$jadwal_list = [];
if ($result_jadwal->num_rows > 0) {
    while ($row_jadwal = $result_jadwal->fetch_assoc()) {
        $jadwal_list[] = $row_jadwal;
    }
}
$stmt_jadwal->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Kuliah Dosen</title>
    <link rel="stylesheet" href="../../css/style_jadwal.css">
</head>
<body>
    <?php include '../../includes/header_dosen.php'; ?>
    <main class="main-content">
        <h1>Jadwal Kuliah</h1>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Mata Kuliah</th>
                    <th>Hari</th>
                    <th>Tanggal</th>
                    <th>Waktu Mulai</th>
                    <th>Waktu Selesai</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jadwal_list as $jadwal): ?>
                    <tr>
                        <td><?php echo $jadwal['mata_kuliah_nama']; ?></td>
                        <td><?php echo $jadwal['hari']; ?></td>
                        <td><?php echo $jadwal['tanggal']; ?></td>
                        <td><?php echo $jadwal['waktu_mulai']; ?></td>
                        <td><?php echo $jadwal['waktu_selesai']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
    <?php include '../../includes/footer.php'; ?>
</body>
</html>