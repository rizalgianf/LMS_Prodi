<?php
// filepath: /E:/GITHUB REPOSITORY/SIAKAD/views/admin/rekap_absen.php
// Mulai session dan pastikan pengguna telah login sebagai admin
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../config/database.php'; // Koneksi database

$page_title = "Rekapitulasi Absensi";
include '../../includes/header.php'; // Ensure this path is correct

// Ambil data cohort dan semester dari database
$sql_cohort = "SELECT id, nama_cohort FROM cohort";
$result_cohort = $conn->query($sql_cohort);
$cohort_list = [];
if ($result_cohort->num_rows > 0) {
    while ($row_cohort = $result_cohort->fetch_assoc()) {
        $cohort_list[] = $row_cohort;
    }
}

$sql_semester = "SELECT id, nama_semester FROM semester";
$result_semester = $conn->query($sql_semester);
$semester_list = [];
if ($result_semester->num_rows > 0) {
    while ($row_semester = $result_semester->fetch_assoc()) {
        $semester_list[] = $row_semester;
    }
}

// Ambil data rekap absensi dari database
$sort_cohort = $_GET['sort_cohort'] ?? '';
$sort_semester = $_GET['sort_semester'] ?? '';

$sql_rekap = "SELECT pertemuan.id, pertemuan.tanggal, pertemuan.topik, cohort.nama_cohort, mata_kuliah.nama AS mata_kuliah, users.nama AS dosen, semester.nama_semester,
              COUNT(absensi.id) AS total_mahasiswa,
              COUNT(CASE WHEN absensi.status = 'Hadir' THEN 1 END) AS jumlah_hadir,
              COUNT(CASE WHEN absensi.status = 'Tidak Hadir' THEN 1 END) AS jumlah_tidak_hadir
              FROM pertemuan
              LEFT JOIN absensi ON pertemuan.id = absensi.pertemuan_id
              JOIN kelas ON pertemuan.kelas_id = kelas.id
              JOIN cohort ON kelas.id_cohort = cohort.id
              JOIN mata_kuliah ON kelas.mata_kuliah_id = mata_kuliah.id
              JOIN users ON kelas.dosen_id = users.id
              JOIN semester ON mata_kuliah.semester_id = semester.id";

$conditions = [];
$params = [];
$types = '';

if ($sort_cohort) {
    $conditions[] = "cohort.id = ?";
    $params[] = $sort_cohort;
    $types .= 'i';
}

if ($sort_semester) {
    $conditions[] = "semester.id = ?";
    $params[] = $sort_semester;
    $types .= 'i';
}

if ($conditions) {
    $sql_rekap .= " WHERE " . implode(" AND ", $conditions);
}

$sql_rekap .= " GROUP BY pertemuan.id, pertemuan.tanggal, pertemuan.topik, cohort.nama_cohort, mata_kuliah.nama, users.nama, semester.nama_semester
                ORDER BY pertemuan.tanggal ASC";

$stmt_rekap = $conn->prepare($sql_rekap);
if ($params) {
    $stmt_rekap->bind_param($types, ...$params);
}
$stmt_rekap->execute();
$result_rekap = $stmt_rekap->get_result();
$rekap_list = [];
if ($result_rekap->num_rows > 0) {
    while ($row_rekap = $result_rekap->fetch_assoc()) {
        $rekap_list[] = $row_rekap;
    }
}

$stmt_rekap->close();
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
    <h2 class="page-title">Rekapitulasi Absensi</h2>
    <form action="rekap_absen.php" method="GET" class="search-form">
        <div class="search-container">
            <select name="sort_cohort" id="sort_cohort" onchange="this.form.submit()">
                <option value="">Pilih Cohort</option>
                <?php foreach ($cohort_list as $cohort): ?>
                    <option value="<?php echo $cohort['id']; ?>" <?php if ($sort_cohort == $cohort['id']) echo 'selected'; ?>>
                        <?php echo $cohort['nama_cohort']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="sort_semester" id="sort_semester" onchange="this.form.submit()">
                <option value="">Pilih Semester</option>
                <?php foreach ($semester_list as $semester): ?>
                    <option value="<?php echo $semester['id']; ?>" <?php if ($sort_semester == $semester['id']) echo 'selected'; ?>>
                        <?php echo $semester['nama_semester']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit"><i class="fas fa-search"></i></button>
        </div>
    </form>
    <table class="data-table">
        <thead>
            <tr>
                <th>Cohort</th>
                <th>Mata Kuliah</th>
                <th>Dosen</th>
                <th>Semester</th>
                <th>Tanggal</th>
                <th>Topik</th>
                <th>Total Mahasiswa</th>
                <th>Jumlah Hadir</th>
                <th>Jumlah Tidak Hadir</th>
                <th>Persentase Kehadiran</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rekap_list as $rekap): ?>
                <tr>
                    <td><?php echo $rekap['nama_cohort']; ?></td>
                    <td><?php echo $rekap['mata_kuliah']; ?></td>
                    <td><?php echo $rekap['dosen']; ?></td>
                    <td><?php echo $rekap['nama_semester']; ?></td>
                    <td><?php echo $rekap['tanggal']; ?></td>
                    <td><?php echo $rekap['topik']; ?></td>
                    <td><?php echo $rekap['total_mahasiswa']; ?></td>
                    <td><?php echo $rekap['jumlah_hadir']; ?></td>
                    <td><?php echo $rekap['jumlah_tidak_hadir']; ?></td>
                    <td><?php echo $rekap['total_mahasiswa'] > 0 ? round(($rekap['jumlah_hadir'] / $rekap['total_mahasiswa']) * 100, 2) . '%' : '0%'; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php include '../../includes/footer.php'; ?>
</body>
</html>