<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../config/database.php'; // Koneksi database

$page_title = "Jadwal Kuliah";
include '../../includes/header.php';

// Ambil data jadwal dari database
$sort_semester = $_GET['sort_semester'] ?? '';
$sql = "SELECT jadwal_kuliah.*, mata_kuliah.nama AS mata_kuliah_nama, users.nama AS dosen_nama, semester.nama_semester
        FROM jadwal_kuliah
        JOIN mata_kuliah ON jadwal_kuliah.mata_kuliah = mata_kuliah.id
        JOIN users ON jadwal_kuliah.dosen_id = users.id
        JOIN semester ON mata_kuliah.semester_id = semester.id
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

// Ambil data mata kuliah dari database
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
    <h2 class="page-title">Pengaturan Jadwal Kuliah</h2>
    <form action="simpan_jadwal.php" method="POST">
        <input type="hidden" name="id" id="id">
        <label for="mata_kuliah">Mata Kuliah:</label>
        <select name="mata_kuliah" id="mata_kuliah" required>
            <?php foreach ($mata_kuliah_list as $mk): ?>
                <option value="<?php echo $mk['id']; ?>"><?php echo $mk['nama']; ?> - <?php echo $mk['nama_semester']; ?></option>
            <?php endforeach; ?>
        </select>
        <label for="dosen">Dosen:</label>
        <select name="dosen" id="dosen" required>
            <?php foreach ($dosen_list as $dosen): ?>
                <option value="<?php echo $dosen['id']; ?>"><?php echo $dosen['nama']; ?></option>
            <?php endforeach; ?>
        </select>
        <label for="tanggal">Tanggal:</label>
        <input type="date" name="tanggal" id="tanggal" required onchange="setDay()">
        <label for="hari">Hari:</label>
        <input type="text" name="hari" id="hari" readonly required>
        <label for="waktu_mulai">Waktu Mulai:</label>
        <input type="time" name="waktu_mulai" id="waktu_mulai" required>
        <label for="waktu_selesai">Waktu Selesai:</label>
        <input type="time" name="waktu_selesai" id="waktu_selesai" required>
        <button type="submit">Simpan</button>
    </form>

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
                <th>Hari</th>
                <th>Tanggal</th>
                <th>Waktu Mulai</th>
                <th>Waktu Selesai</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($jadwal as $jdwl): ?>
                <tr>
                    <td><?php echo $jdwl['mata_kuliah_nama']; ?></td>
                    <td><?php echo $jdwl['dosen_nama']; ?></td>
                    <td><?php echo $jdwl['nama_semester']; ?></td>
                    <td><?php echo $jdwl['hari']; ?></td>
                    <td><?php echo $jdwl['tanggal']; ?></td>
                    <td><?php echo $jdwl['waktu_mulai']; ?></td>
                    <td><?php echo $jdwl['waktu_selesai']; ?></td>
                    <td>
                        <button onclick="editJadwal(<?php echo htmlspecialchars(json_encode($jdwl)); ?>)">Edit</button>
                        <a href="hapus_jadwal.php?id=<?php echo $jdwl['id']; ?>">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<script src="../../js/batas_tanggal.js"></script>
<script src="../../js/edit_jadwal.js"></script>
<script src="../../js/batas_jam.js"></script>
<script>
function setDay() {
    const dateInput = document.getElementById('tanggal').value;
    const dayInput = document.getElementById('hari');
    const date = new Date(dateInput);
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const dayName = days[date.getDay()];
    dayInput.value = dayName;
}
</script>

<?php include '../../includes/footer.php'; ?>
</body>
</html>