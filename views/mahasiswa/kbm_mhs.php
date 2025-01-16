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

// Ambil data cohort mahasiswa
$sql_cohort = "SELECT cohort_id FROM users WHERE id = ? AND role = 'mahasiswa'";
$stmt_cohort = $conn->prepare($sql_cohort);
$stmt_cohort->bind_param("i", $mahasiswa_id);
$stmt_cohort->execute();
$result_cohort = $stmt_cohort->get_result();
$cohort = $result_cohort->fetch_assoc();
$stmt_cohort->close();

if (!$cohort) {
    echo "Cohort tidak ditemukan.";
    exit();
}

// Ambil data kelas berdasarkan cohort mahasiswa
$sort_semester = $_GET['sort_semester'] ?? '';

$sql_kelas = "SELECT kelas.id, cohort.nama_cohort, mata_kuliah.nama AS mata_kuliah, users.nama AS dosen, semester.nama_semester
              FROM kelas
              JOIN cohort ON kelas.id_cohort = cohort.id
              JOIN mata_kuliah ON kelas.mata_kuliah_id = mata_kuliah.id
              JOIN users ON kelas.dosen_id = users.id
              JOIN semester ON mata_kuliah.semester_id = semester.id
              WHERE cohort.id = ?";

if ($sort_semester) {
    $sql_kelas .= " AND semester.nama_semester = ?";
}

$sql_kelas .= " ORDER BY semester.nama_semester ASC";
$stmt_kelas = $conn->prepare($sql_kelas);
if ($sort_semester) {
    $stmt_kelas->bind_param("is", $cohort['cohort_id'], $sort_semester);
} else {
    $stmt_kelas->bind_param("i", $cohort['cohort_id']);
}
$stmt_kelas->execute();
$result_kelas = $stmt_kelas->get_result();
$kelas_list = [];
if ($result_kelas->num_rows > 0) {
    while ($row_kelas = $result_kelas->fetch_assoc()) {
        $kelas_list[] = $row_kelas;
    }
}
$stmt_kelas->close();

// Ambil data semester dari database
$sql_semester = "SELECT id, nama_semester FROM semester";
$result_semester = $conn->query($sql_semester);
$semester_list = [];
if ($result_semester->num_rows > 0) {
    while ($row_semester = $result_semester->fetch_assoc()) {
        $semester_list[] = $row_semester;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../../css/style_kbm.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<main class="main-content">
    <h2 class="page-title">Daftar Kelas</h2>
    <form action="kbm_mhs.php" method="GET" class="search-form">
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
                <th>Nama Cohort</th>
                <th>Mata Kuliah</th>
                <th>Semester</th>
                <th>Dosen</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($kelas_list as $kelas): ?>
                <tr>
                    <td><?php echo $kelas['nama_cohort']; ?></td>
                    <td><?php echo $kelas['mata_kuliah']; ?></td>
                    <td><?php echo $kelas['nama_semester']; ?></td>
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