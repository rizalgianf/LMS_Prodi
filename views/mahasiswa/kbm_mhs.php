<?php
// Mulai session dan pastikan pengguna telah login sebagai mahasiswa
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../../login.php");
    exit();
}

include '../../config/database.php'; // Koneksi database

$page_title = "Kelola Kelas";
include '../../includes/header_mahasiswa.php';

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

// Ambil data kelas berdasarkan semester mahasiswa
$sql_kelas = "SELECT kelas.id, kelas.nama_kelas, mata_kuliah.nama AS mata_kuliah, users.nama AS dosen
              FROM kelas
              JOIN mata_kuliah ON kelas.mata_kuliah_id = mata_kuliah.id
              JOIN users ON kelas.dosen_id = users.id
              WHERE mata_kuliah.semester_id = ?";
$stmt_kelas = $conn->prepare($sql_kelas);
$stmt_kelas->bind_param("i", $semester['semester_id']);
$stmt_kelas->execute();
$result_kelas = $stmt_kelas->get_result();
$kelas_list = [];
if ($result_kelas->num_rows > 0) {
    while ($row_kelas = $result_kelas->fetch_assoc()) {
        $kelas_list[] = $row_kelas;
    }
}
$stmt_kelas->close();

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
    <h2 class="page-title">Daftar Kelas</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Nama Kelas</th>
                <th>Mata Kuliah</th>
                <th>Dosen</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($kelas_list as $kelas): ?>
                <tr>
                    <td><?php echo $kelas['nama_kelas']; ?></td>
                    <td><?php echo $kelas['mata_kuliah']; ?></td>
                    <td><?php echo $kelas['dosen']; ?></td>
                    <td>
                        <a class="kelola" href="kelas_mhs.php?id=<?php echo $kelas['id']; ?>">Masuk</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<script src="../../js/batas_tanggal.js"></script>

<?php include '../../includes/footer.php'; ?>
</body>
</html>