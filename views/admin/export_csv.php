<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../config/database.php'; // Koneksi database

$sort_cohort = $_POST['sort_cohort'] ?? '';
$sort_semester = $_POST['sort_semester'] ?? '';
$sort_mata_kuliah = $_POST['sort_mata_kuliah'] ?? '';

$sql_rekap = "SELECT pertemuan.id, pertemuan.tanggal, pertemuan.topik, cohort.nama_cohort, mata_kuliah.nama AS mata_kuliah, users.nama AS dosen, semester.nama_semester,
              COUNT(absensi.id) AS total_mahasiswa,
              COUNT(CASE WHEN absensi.status = 'Hadir' THEN 1 END) AS jumlah_hadir,
              COUNT(CASE WHEN absensi.status != 'Hadir' OR absensi.status IS NULL THEN 1 END) AS jumlah_tidak_hadir
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

if ($sort_mata_kuliah) {
    $conditions[] = "mata_kuliah.id = ?";
    $params[] = $sort_mata_kuliah;
    $types .= 'i';
}

if ($conditions) {
    $sql_rekap .= " WHERE " . implode(" AND ", $conditions);
}

$sql_rekap .= " GROUP BY pertemuan.id, pertemuan.tanggal, pertemuan.topik, cohort.nama_cohort, mata_kuliah.nama, users.nama, semester.nama_semester
                ORDER BY cohort.nama_cohort ASC, semester.nama_semester ASC, mata_kuliah.nama ASC, pertemuan.tanggal ASC";

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

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename="rekap_absensi.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Cohort', 'Mata Kuliah', 'Dosen', 'Semester', 'Tanggal', 'Topik', 'Total Mahasiswa', 'Jumlah Hadir', 'Jumlah Tidak Hadir', 'Persentase Kehadiran']);

foreach ($rekap_list as $rekap) {
    $persentase_kehadiran = $rekap['total_mahasiswa'] > 0 ? round(($rekap['jumlah_hadir'] / $rekap['total_mahasiswa']) * 100, 2) : 0;
    fputcsv($output, [
        $rekap['nama_cohort'],
        $rekap['mata_kuliah'],
        $rekap['dosen'],
        $rekap['nama_semester'],
        $rekap['tanggal'],
        $rekap['topik'],
        $rekap['total_mahasiswa'],
        $rekap['jumlah_hadir'],
        $rekap['jumlah_tidak_hadir'],
        $persentase_kehadiran . '%'
    ]);
}

fclose($output);
exit;
?>