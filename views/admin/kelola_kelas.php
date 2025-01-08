<?php
// Mulai session dan pastikan pengguna telah login sebagai admin
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../config/database.php'; // Koneksi database

$page_title = "Kelola Kelas";
include '../../includes/header_admin.php';

$kelas_id = $_GET['id'] ?? '';
if (empty($kelas_id)) {
    header("Location: kbm.php");
    exit();
}

// Ambil data kelas
$sql_kelas = "SELECT kelas.id, kelas.nama_kelas, mata_kuliah.nama AS mata_kuliah, dosen.nama AS dosen
              FROM kelas
              JOIN mata_kuliah ON kelas.mata_kuliah_id = mata_kuliah.id
              JOIN dosen ON kelas.dosen_id = dosen.id
              WHERE kelas.id = ?";
$stmt_kelas = $conn->prepare($sql_kelas);
$stmt_kelas->bind_param("i", $kelas_id);
$stmt_kelas->execute();
$result_kelas = $stmt_kelas->get_result();
$kelas = $result_kelas->fetch_assoc();
$stmt_kelas->close();

// Proses form untuk membuat pertemuan baru
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['buat_pertemuan'])) {
    $tanggal = $_POST['tanggal'];
    $topik = $_POST['topik'];

    $sql = "INSERT INTO pertemuan (kelas_id, tanggal, topik) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $kelas_id, $tanggal, $topik);

    if ($stmt->execute()) {
        echo "Pertemuan berhasil dibuat!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Ambil data pertemuan dari database
$sql_pertemuan = "SELECT * FROM pertemuan WHERE kelas_id = ?";
$stmt_pertemuan = $conn->prepare($sql_pertemuan);
$stmt_pertemuan->bind_param("i", $kelas_id);
$stmt_pertemuan->execute();
$result_pertemuan = $stmt_pertemuan->get_result();
$pertemuan_list = [];
if ($result_pertemuan->num_rows > 0) {
    while ($row_pertemuan = $result_pertemuan->fetch_assoc()) {
        $pertemuan_list[] = $row_pertemuan;
    }
}
$stmt_pertemuan->close();

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
    <h2 class="page-title">Kelola Kelas: <?php echo $kelas['nama_kelas']; ?></h2>
    <p>Mata Kuliah: <?php echo $kelas['mata_kuliah']; ?></p>
    <p>Dosen: <?php echo $kelas['dosen']; ?></p>
    <form action="kelola_kelas.php?id=<?php echo $kelas_id; ?>" method="POST">
        <label for="tanggal">Tanggal:</label>
        <input type="date" name="tanggal" id="tanggal" required>
        <label for="topik">Topik:</label>
        <input type="text" name="topik" id="topik" required>
        <button type="submit" name="buat_pertemuan">Buat Pertemuan</button>
    </form>
    <table class="data-table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Topik</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pertemuan_list as $pertemuan): ?>
                <tr>
                    <td><?php echo $pertemuan['tanggal']; ?></td>
                    <td><?php echo $pertemuan['topik']; ?></td>
                    <td>
                        <a href="kelola_pertemuan.php?id=<?php echo $pertemuan['id']; ?>">Kelola</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php include '../../includes/footer.php'; ?>
</body>
</html>