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
$sort_cohort = $_GET['sort_cohort'] ?? '';
$sort_semester = $_GET['sort_semester'] ?? '';

$sql_kelas = "SELECT kelas.id, cohort.nama_cohort, mata_kuliah.nama AS mata_kuliah, users.nama AS dosen, semester.nama_semester
              FROM kelas
              JOIN cohort ON kelas.id_cohort = cohort.id
              JOIN mata_kuliah ON kelas.mata_kuliah_id = mata_kuliah.id
              JOIN users ON kelas.dosen_id = users.id
              JOIN semester ON mata_kuliah.semester_id = semester.id
              WHERE kelas.dosen_id = ?";

if ($sort_cohort) {
    $sql_kelas .= " AND cohort.nama_cohort = ?";
}

if ($sort_semester) {
    $sql_kelas .= " AND semester.nama_semester = ?";
}

$sql_kelas .= " ORDER BY cohort.nama_cohort ASC, semester.nama_semester ASC";
$stmt_kelas = $conn->prepare($sql_kelas);
if ($sort_cohort && $sort_semester) {
    $stmt_kelas->bind_param("iss", $dosen_id, $sort_cohort, $sort_semester);
} elseif ($sort_cohort) {
    $stmt_kelas->bind_param("is", $dosen_id, $sort_cohort);
} elseif ($sort_semester) {
    $stmt_kelas->bind_param("is", $dosen_id, $sort_semester);
} else {
    $stmt_kelas->bind_param("i", $dosen_id);
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

// Ambil data cohort dari database
$sql_cohort = "SELECT id, nama_cohort FROM cohort";
$result_cohort = $conn->query($sql_cohort);
$cohort_list = [];
if ($result_cohort->num_rows > 0) {
    while ($row_cohort = $result_cohort->fetch_assoc()) {
        $cohort_list[] = $row_cohort;
    }
}

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
    
    <h2 class="page-title">Pengaturan Kegiatan Belajar Mengajar</h2>
    <h3>Daftar Kelas</h3>
    <form action="kbm_dosen.php" method="GET" class="search-form">
        <div class="search-container">
            <select name="sort_cohort" id="sort_cohort">
                <option value="">Pilih Cohort</option>
                <?php foreach ($cohort_list as $cohort): ?>
                    <option value="<?php echo $cohort['nama_cohort']; ?>" <?php if ($sort_cohort == $cohort['nama_cohort']) echo 'selected'; ?>>
                        <?php echo $cohort['nama_cohort']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="sort_semester" id="sort_semester">
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