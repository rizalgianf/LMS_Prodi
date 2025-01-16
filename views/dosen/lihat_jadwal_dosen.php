<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'dosen') {
    header("Location: ../login.php");
    exit();
}

include '../../config/database.php'; // Koneksi database

$dosen_id = $_SESSION['user_id'];

// Ambil data jadwal dari tabel pertemuan
$sort_cohort = $_GET['sort_cohort'] ?? '';
$sort_time = $_GET['sort_time'] ?? '7 days';

$sql_jadwal = "SELECT pertemuan.*, mata_kuliah.nama AS mata_kuliah_nama, semester.nama_semester, cohort.nama_cohort
               FROM pertemuan
               JOIN kelas ON pertemuan.kelas_id = kelas.id
               JOIN mata_kuliah ON kelas.mata_kuliah_id = mata_kuliah.id
               JOIN semester ON mata_kuliah.semester_id = semester.id
               JOIN cohort ON kelas.id_cohort = cohort.id
               WHERE kelas.dosen_id = ?";

if ($sort_cohort) {
    $sql_jadwal .= " AND cohort.nama_cohort = ?";
}

switch ($sort_time) {
    case '7 days':
        $sql_jadwal .= " AND pertemuan.tanggal BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
        break;
    case '1 month':
        $sql_jadwal .= " AND pertemuan.tanggal BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 MONTH)";
        break;
    case '1 year':
        $sql_jadwal .= " AND pertemuan.tanggal BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 YEAR)";
        break;
}

$sql_jadwal .= " ORDER BY pertemuan.tanggal ASC";
$stmt_jadwal = $conn->prepare($sql_jadwal);
if ($sort_cohort) {
    $stmt_jadwal->bind_param("is", $dosen_id, $sort_cohort);
} else {
    $stmt_jadwal->bind_param("i", $dosen_id);
}
$stmt_jadwal->execute();
$result_jadwal = $stmt_jadwal->get_result();
$jadwal_list = [];
if ($result_jadwal->num_rows > 0) {
    while ($row_jadwal = $result_jadwal->fetch_assoc()) {
        $jadwal_list[] = $row_jadwal;
    }
}
$stmt_jadwal->close();

// Ambil data cohort dari database
$sql_cohort = "SELECT id, nama_cohort FROM cohort";
$result_cohort = $conn->query($sql_cohort);
$cohort_list = [];
if ($result_cohort->num_rows > 0) {
    while ($row_cohort = $result_cohort->fetch_assoc()) {
        $cohort_list[] = $row_cohort;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Kuliah Dosen</title>
    <link rel="stylesheet" href="../../css/style_jadwal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include '../../includes/header_dosen.php'; ?>
    <main class="main-content">
    <h2 class="page-title">Daftar Jadwal Kuliah</h2>
        <form action="lihat_jadwal_dosen.php" method="GET" class="search-form">
            <label for="sort_cohort" class="sr-only">Urutkan berdasarkan Cohort:</label>
            <div class="search-container">
                <select name="sort_cohort" id="sort_cohort" onchange="this.form.submit()">
                    <option value="">Pilih Cohort</option>
                    <?php foreach ($cohort_list as $cohort): ?>
                        <option value="<?php echo $cohort['nama_cohort']; ?>" <?php if ($sort_cohort == $cohort['nama_cohort']) echo 'selected'; ?>>
                            <?php echo $cohort['nama_cohort']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="sort_time" id="sort_time" onchange="this.form.submit()">
                    <option value="7 days" <?php if ($sort_time == '7 days') echo 'selected'; ?>>7 Hari Kedepan</option>
                    <option value="1 month" <?php if ($sort_time == '1 month') echo 'selected'; ?>>1 Bulan Kedepan</option>
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
                    <th>Cohort</th>
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
                        <td><?php echo $jadwal['nama_cohort']; ?></td>
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