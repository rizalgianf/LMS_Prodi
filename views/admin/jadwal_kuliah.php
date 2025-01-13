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
$sql = "SELECT jadwal_kuliah.*, mata_kuliah.nama AS mata_kuliah_nama, users.nama AS dosen_nama, semester.nama_semester
        FROM jadwal_kuliah
        JOIN mata_kuliah ON jadwal_kuliah.mata_kuliah = mata_kuliah.id
        JOIN users ON jadwal_kuliah.dosen_id = users.id
        JOIN semester ON mata_kuliah.semester_id = semester.id
        WHERE users.role = 'dosen'";
$result = $conn->query($sql);
$jadwal = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $jadwal[] = $row;
    }
}

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../../css/style_jadwal.css">
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
        <label for="hari">Hari:</label>
        <select name="hari" id="hari" required>
            <option value="Senin">Senin</option>
            <option value="Selasa">Selasa</option>
            <option value="Rabu">Rabu</option>
            <option value="Kamis">Kamis</option>
            <option value="Jumat">Jumat</option>
        </select>
        <label for="tanggal">Tanggal:</label>
        <input type="date" name="tanggal" id="tanggal" required>
        <label for="waktu_mulai">Waktu Mulai:</label>
        <input type="time" name="waktu_mulai" id="waktu_mulai" required>
        <label for="waktu_selesai">Waktu Selesai:</label>
        <input type="time" name="waktu_selesai" id="waktu_selesai" required>
        <button type="submit">Simpan</button>
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
                        <a href="hapus_jadwal.php?id=<?php echo $jdwl['id']; ?>">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<script src="../../js/batas_tanggal.js"></script>
<script src="../../js/edit_jadwal.js"></script>
<script src="../../js/batas_jam.js"></script>

<?php include '../../includes/footer.php'; ?>
</body>
</html>