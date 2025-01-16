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

// Ambil data jadwal dari tabel pertemuan berdasarkan semester mahasiswa
$sort_time = $_GET['sort_time'] ?? '7 days';

$sql_jadwal = "SELECT pertemuan.*, mata_kuliah.nama AS mata_kuliah_nama, users.nama AS dosen_nama, semester.nama_semester
               FROM pertemuan
               JOIN kelas ON pertemuan.kelas_id = kelas.id
               JOIN mata_kuliah ON kelas.mata_kuliah_id = mata_kuliah.id
               JOIN users ON kelas.dosen_id = users.id
               JOIN semester ON mata_kuliah.semester_id = semester.id
               WHERE mata_kuliah.semester_id = ?";

switch ($sort_time) {
    case '7 days':
        $sql_jadwal .= " AND pertemuan.tanggal BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
        break;
    case '1 month':
        $sql_jadwal .= " AND pertemuan.tanggal BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 MONTH)";
        break;
    case '3 months':
        $sql_jadwal .= " AND pertemuan.tanggal BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 MONTH)";
        break;
    case '6 months':
        $sql_jadwal .= " AND pertemuan.tanggal BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 6 MONTH)";
        break;
    case '1 year':
        $sql_jadwal .= " AND pertemuan.tanggal BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 YEAR)";
        break;
}

$sql_jadwal .= " ORDER BY pertemuan.tanggal ASC";
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include '../../includes/header_mahasiswa.php'; ?>
    <main class="main-content">
        <h2 class="page-title">Daftar Jadwal Kuliah</h2>
        <form action="lihat_jadwal_mhs.php" method="GET" class="search-form">
            <div class="search-container">
                <select name="sort_time" id="sort_time" onchange="this.form.submit()">
                    <option value="7 days" <?php if ($sort_time == '7 days') echo 'selected'; ?>>7 Hari Kedepan</option>
                    <option value="1 month" <?php if ($sort_time == '1 month') echo 'selected'; ?>>1 Bulan Kedepan</option>
                    <option value="3 months" <?php if ($sort_time == '3 months') echo 'selected'; ?>>3 Bulan Kedepan</option>
                    <option value="6 months" <?php if ($sort_time == '6 months') echo 'selected'; ?>>6 Bulan Kedepan</option>
                    <option value="1 year" <?php if ($sort_time == '1 year') echo 'selected'; ?>>1 Tahun Kedepan</option>
                </select>
                <button type="submit"><i class="fas fa-search"></i></button>
            </div>
        </form>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Mata Kuliah</th>
                    <th>Semester</th>
                    <th>Hari</th>
                    <th>Tanggal</th>
                    <th>Waktu Mulai</th>
                    <th>Waktu Selesai</th>
                    <th>Dosen</th>
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
                        <td><?php echo $jadwal['dosen_nama']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
    <?php include '../../includes/footer.php'; ?>
</body>
</html>