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
$sort_semester = $_GET['sort_semester'] ?? '';
$sql = "SELECT pertemuan.*, mata_kuliah.nama AS mata_kuliah_nama, users.nama AS dosen_nama, semester.nama_semester, cohort.nama_cohort
        FROM pertemuan
        JOIN kelas ON pertemuan.kelas_id = kelas.id
        JOIN mata_kuliah ON kelas.mata_kuliah_id = mata_kuliah.id
        JOIN users ON kelas.dosen_id = users.id
        JOIN semester ON mata_kuliah.semester_id = semester.id
        JOIN cohort ON kelas.id_cohort = cohort.id
        WHERE users.role = 'dosen'";
if ($sort_semester) {
    $sql .= " AND semester.nama_semester = ?";
}
$sql .= " ORDER BY semester.nama_semester ASC";
$stmt = $conn->prepare($sql);
if ($sort_semester) {
    $stmt->bind_param("s", $sort_semester);
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

// Ambil data semester dari database
$sql_semester = "SELECT id, nama_semester FROM semester";
$result_semester = $conn->query($sql_semester);
$semester_list = [];
if ($result_semester->num_rows > 0) {
    while ($row_semester = $result_semester->fetch_assoc()) {
        $semester_list[] = $row_semester;
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
        <label for="sort_semester" class="sr-only">Urutkan berdasarkan Semester:</label>
        <div class="search-container">
            <select name="sort_semester" id="sort_semester" onchange="this.form.submit()">
                <option value="">Pilih Semester</option>
                <?php foreach ($semester_list as $semester): ?>
                    <option value="<?php echo $semester['nama_semester']; ?>" <?php if ($sort_semester == $semester['nama_semester']) echo 'selected'; ?>>
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