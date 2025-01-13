<?php
// Mulai session dan pastikan pengguna telah login sebagai mahasiswa
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../../login.php");
    exit();
}

include '../../config/database.php'; // Koneksi database

$page_title = "Kelola Mata Kuliah";
include '../../includes/header_mahasiswa.php';

$kelas_id = $_GET['id'] ?? '';
if (empty($kelas_id)) {
    header("Location: kbm_mahasiswa.php");
    exit();
}

// Ambil data semester mahasiswa
$mahasiswa_id = $_SESSION['user_id'];
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
              WHERE kelas.id = ? AND mata_kuliah.semester_id = ?";
$stmt_kelas = $conn->prepare($sql_kelas);
$stmt_kelas->bind_param("ii", $kelas_id, $semester['semester_id']);
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
    <h2 class="page-title">Mata Kuliah: <?php echo $kelas['mata_kuliah']; ?></h2>
    <p>Nama Kelas: <?php echo $kelas['nama_kelas']; ?></p>
    <p>Dosen: <?php echo $kelas['dosen']; ?></p>
    <h3>Daftar Pertemuan</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Topik</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pertemuan_list as $pertemuan): ?>
                <tr>
                    <td><?php echo $pertemuan['tanggal']; ?></td>
                    <td><?php echo $pertemuan['topik']; ?></td>
                    <td>
                        <a class="kelola" href="pertemuan_mhs.php?id=<?php echo $pertemuan['id']; ?>">Masuk</a>
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