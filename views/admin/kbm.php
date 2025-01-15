<?php
// Mulai session dan pastikan pengguna telah login sebagai admin
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../config/database.php'; // Koneksi database

$page_title = "Kegiatan Belajar Mengajar";
include '../../includes/header.php';

// Ambil data mata kuliah dan semester dari database
$sql_mk = "SELECT mata_kuliah.*, semester.nama_semester 
           FROM mata_kuliah 
           JOIN semester ON mata_kuliah.semester_id = semester.id";
$result_mk = $conn->query($sql_mk);
$mata_kuliah_list = [];
if ($result_mk->num_rows > 0) {
    while ($row_mk = $result_mk->fetch_assoc()) {
        $mata_kuliah_list[] = $row_mk;
    }
}

// Ambil data dosen dari tabel users dengan role dosen
$sql_dosen = "SELECT id, nama FROM users WHERE role = 'dosen'";
$result_dosen = $conn->query($sql_dosen);
$dosen_list = [];
if ($result_dosen->num_rows > 0) {
    while ($row_dosen = $result_dosen->fetch_assoc()) {
        $dosen_list[] = $row_dosen;
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

// Ambil data cohort dari database
$sql_cohort = "SELECT id, nama_cohort FROM cohort";
$result_cohort = $conn->query($sql_cohort);
$cohort_list = [];
if ($result_cohort->num_rows > 0) {
    while ($row_cohort = $result_cohort->fetch_assoc()) {
        $cohort_list[] = $row_cohort;
    }
}

// Ambil data kelas yang sudah ada
$sql_existing_classes = "SELECT id_cohort, mata_kuliah_id FROM kelas";
$result_existing_classes = $conn->query($sql_existing_classes);
$existing_classes = [];
if ($result_existing_classes->num_rows > 0) {
    while ($row_existing_class = $result_existing_classes->fetch_assoc()) {
        $existing_classes[$row_existing_class['id_cohort']][] = $row_existing_class['mata_kuliah_id'];
    }
}

// Proses form untuk membuat kelas baru
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['buat_kelas'])) {
    $id_cohort = $_POST['id_cohort'];
    $mata_kuliah_id = $_POST['mata_kuliah'];
    $dosen_id = $_POST['dosen'];

    $sql = "INSERT INTO kelas (id_cohort, mata_kuliah_id, dosen_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $id_cohort, $mata_kuliah_id, $dosen_id);

    if ($stmt->execute()) {
        echo "Kelas berhasil dibuat!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Ambil data kelas dari database
$sort_semester = $_GET['sort_semester'] ?? '';
$sql_kelas = "SELECT kelas.id, mata_kuliah.nama AS mata_kuliah, users.nama AS dosen, semester.nama_semester
              FROM kelas
              JOIN mata_kuliah ON kelas.mata_kuliah_id = mata_kuliah.id
              JOIN users ON kelas.dosen_id = users.id
              JOIN semester ON mata_kuliah.semester_id = semester.id
              WHERE users.role = 'dosen'";
if ($sort_semester) {
    $sql_kelas .= " AND semester.nama_semester = ?";
}
$sql_kelas .= " ORDER BY semester.nama_semester ASC";
$stmt = $conn->prepare($sql_kelas);
if ($sort_semester) {
    $stmt->bind_param("s", $sort_semester);
}
$stmt->execute();
$result_kelas = $stmt->get_result();
$kelas_list = [];
if ($result_kelas->num_rows > 0) {
    while ($row_kelas = $result_kelas->fetch_assoc()) {
        $kelas_list[] = $row_kelas;
    }
}
$stmt->close();

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
    <form action="kbm.php" method="POST">
        <label for="id_cohort">Nama Kelas:</label>
        <select name="id_cohort" id="id_cohort" required>
            <?php foreach ($cohort_list as $cohort): ?>
                <option value="<?php echo $cohort['id']; ?>"><?php echo $cohort['nama_cohort']; ?></option>
            <?php endforeach; ?>
        </select>
        <label for="mata_kuliah">Mata Kuliah:</label>
        <select name="mata_kuliah" id="mata_kuliah" required>
            <?php foreach ($mata_kuliah_list as $mk): ?>
                <?php if (!isset($existing_classes[$_POST['id_cohort']]) || !in_array($mk['id'], $existing_classes[$_POST['id_cohort']])): ?>
                    <option value="<?php echo $mk['id']; ?>"><?php echo $mk['nama']; ?> - <?php echo $mk['nama_semester']; ?></option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>
        <label for="dosen">Dosen:</label>
        <select name="dosen" id="dosen" required>
            <?php foreach ($dosen_list as $dosen): ?>
                <option value="<?php echo $dosen['id']; ?>"><?php echo $dosen['nama']; ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="buat_kelas">Buat Kelas</button>
    </form>

    <h3>Daftar Kelas</h3>
    <form action="kbm.php" method="GET" class="search-form">
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
                <th>Semester</th>
                <th>Dosen</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($kelas_list as $kelas): ?>
                <tr>
                    <td><?php echo $kelas['mata_kuliah']; ?></td>
                    <td><?php echo $kelas['nama_semester']; ?></td>
                    <td><?php echo $kelas['dosen']; ?></td>
                    <td>
                        <a href="kelola_kelas.php?id=<?php echo $kelas['id']; ?>" class="kelola">Kelola</a>
                        <a href="edit_kelas.php?id=<?php echo $kelas['id']; ?>" class="edit">Edit</a>
                        <a href="hapus_kelas.php?id=<?php echo $kelas['id']; ?>" class="hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus kelas ini?')">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php include '../../includes/footer.php'; ?>
</body>
</html>