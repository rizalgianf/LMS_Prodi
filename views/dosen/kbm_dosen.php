<?php
// Mulai session dan pastikan pengguna telah login sebagai dosen
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'dosen') {
    header("Location: ../../login.php");
    exit();
}

include '../../config/database.php'; // Koneksi database

$page_title = "Kegiatan Belajar Mengajar";
include '../../includes/header_dosen.php';

// Ambil data kelas dari database berdasarkan dosen yang login
$dosen_id = $_SESSION['user_id'];
$sql_kelas = "SELECT kelas.id, kelas.nama_kelas, mata_kuliah.nama AS mata_kuliah, users.nama AS dosen, semester.nama_semester
              FROM kelas
              JOIN mata_kuliah ON kelas.mata_kuliah_id = mata_kuliah.id
              JOIN users ON kelas.dosen_id = users.id
              JOIN semester ON mata_kuliah.semester_id = semester.id
              WHERE kelas.dosen_id = ?";
$stmt_kelas = $conn->prepare($sql_kelas);
$stmt_kelas->bind_param("i", $dosen_id);
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
    <h2 class="page-title">Pengaturan Kegiatan Belajar Mengajar</h2>
    <h3>Daftar Kelas</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Nama Kelas</th>
                <th>Mata Kuliah</th>
                <th>Semester</th>
                <th>Dosen</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($kelas_list as $kelas): ?>
                <tr>
                    <td><?php echo $kelas['nama_kelas']; ?></td>
                    <td><?php echo $kelas['mata_kuliah']; ?></td>
                    <td><?php echo $kelas['nama_semester']; ?></td>
                    <td><?php echo $kelas['dosen']; ?></td>
                    <td>
                        <a href="kelola_kelas_dosen.php?id=<?php echo $kelas['id']; ?>" class="kelola">Kelola</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php include '../../includes/footer.php'; ?>
</body>
</html>