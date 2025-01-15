<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../config/database.php'; // Koneksi database

$page_title = "Jadwal Kuliah";
include '../../includes/header.php';

// Ambil data jadwal dari tabel pertemuan
$sort_cohort = $_GET['sort_cohort'] ?? '';
$sort_time = $_GET['sort_time'] ?? '7 days';

$sql = "SELECT pertemuan.*, mata_kuliah.nama AS mata_kuliah_nama, users.nama AS dosen_nama, semester.nama_semester, cohort.nama_cohort
        FROM pertemuan
        JOIN kelas ON pertemuan.kelas_id = kelas.id
        JOIN mata_kuliah ON kelas.mata_kuliah_id = mata_kuliah.id
        JOIN users ON kelas.dosen_id = users.id
        JOIN semester ON mata_kuliah.semester_id = semester.id
        JOIN cohort ON kelas.id_cohort = cohort.id
        WHERE users.role = 'dosen'";

if ($sort_cohort) {
    $sql .= " AND cohort.nama_cohort = ?";
}

switch ($sort_time) {
    case '7 days':
        $sql .= " AND pertemuan.tanggal BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
        break;
    case '1 month':
        $sql .= " AND pertemuan.tanggal BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 MONTH)";
        break;
    case '1 year':
        $sql .= " AND pertemuan.tanggal BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 YEAR)";
        break;
}

$sql .= " ORDER BY pertemuan.tanggal ASC";
$stmt = $conn->prepare($sql);
if ($sort_cohort) {
    $stmt->bind_param("s", $sort_cohort);
}
$stmt->execute();
$result = $stmt->get_result();
$jadwal = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $jadwal[] = $row;
    }
}
$stmt->close();

// Ambil data cohort dari database
$sql_cohort = "SELECT id, nama_cohort FROM cohort";
$result_cohort = $conn->query($sql_cohort);
$cohort_list = [];
if ($result_cohort->num_rows > 0) {
    while ($row_cohort = $result_cohort->fetch_assoc()) {
        $cohort_list[] = $row_cohort;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../../css/style_jadwal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<main class="main-content">
    <h2 class="page-title">Daftar Jadwal Kuliah</h2>
    <form action="jadwal_kuliah.php" method="GET" class="search-form">
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
                <th>Dosen</th>
                <th>Semester</th>
                <th>Cohort</th>
                <th>Hari</th>
                <th>Tanggal</th>
                <th>Waktu Mulai</th>
                <th>Waktu Selesai</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($jadwal as $jdwl): ?>
                <tr>
                    <td><?php echo $jdwl['mata_kuliah_nama']; ?></td>
                    <td><?php echo $jdwl['dosen_nama']; ?></td>
                    <td><?php echo $jdwl['nama_semester']; ?></td>
                    <td><?php echo $jdwl['nama_cohort']; ?></td>
                    <td><?php echo $jdwl['hari']; ?></td>
                    <td><?php echo $jdwl['tanggal']; ?></td>
                    <td><?php echo $jdwl['waktu_mulai']; ?></td>
                    <td><?php echo $jdwl['waktu_selesai']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php include '../../includes/footer.php'; ?>
</body>
</html>