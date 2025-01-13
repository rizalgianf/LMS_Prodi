<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

include '../../config/database.php'; // Koneksi database

$mahasiswa_id = $_SESSION['user_id'];

// Ambil data semester mahasiswa
$sql_semester = "SELECT semester_id FROM users WHERE id = ? AND role = 'mahasiswa'";
$stmt_semester = $conn->prepare($sql_semester);
$stmt_semester->bind_param("i", $mahasiswa_id);
$stmt_semester->execute();
$result_semester = $stmt_semester->get_result();
$semester = $result_semester->fetch_assoc();
$stmt_semester->close();

if (!$semester) {
    echo "Semester tidak ditemukan.";
    exit();
}

// Ambil data jadwal dari database berdasarkan semester mahasiswa
$sql_jadwal = "SELECT jadwal_kuliah.*, mata_kuliah.nama AS mata_kuliah_nama, semester.nama_semester
               FROM jadwal_kuliah
               JOIN mata_kuliah ON jadwal_kuliah.mata_kuliah = mata_kuliah.id
               JOIN semester ON mata_kuliah.semester_id = semester.id
               WHERE semester.id = ?";
$stmt_jadwal = $conn->prepare($sql_jadwal);
$stmt_jadwal->bind_param("i", $semester['semester_id']);
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
    <title>Jadwal Kuliah Mahasiswa</title>
    <link rel="stylesheet" href="../../css/style_jadwal.css">
</head>
<body>
    <?php include '../../includes/header_mahasiswa.php'; ?>
    <main class="main-content">
        <h1>Jadwal Kuliah</h1>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Mata Kuliah</th>
                    <th>Semester</th>
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
                        <td><?php echo $jadwal['nama_semester']; ?></td>
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