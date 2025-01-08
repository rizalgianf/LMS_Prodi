<?php
// Mulai session dan pastikan pengguna telah login sebagai admin
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../config/database.php'; // Koneksi database

$page_title = "Kegiatan Belajar Mengajar";
include '../../includes/header_admin.php';

// Ambil data mata kuliah dan dosen dari database
$sql_mk = "SELECT * FROM mata_kuliah";
$result_mk = $conn->query($sql_mk);
$mata_kuliah_list = [];
if ($result_mk->num_rows > 0) {
    while ($row_mk = $result_mk->fetch_assoc()) {
        $mata_kuliah_list[] = $row_mk;
    }
}

$sql_dosen = "SELECT * FROM dosen";
$result_dosen = $conn->query($sql_dosen);
$dosen_list = [];
if ($result_dosen->num_rows > 0) {
    while ($row_dosen = $result_dosen->fetch_assoc()) {
        $dosen_list[] = $row_dosen;
    }
}

// Proses form untuk membuat kelas baru
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['buat_kelas'])) {
    $nama_kelas = $_POST['nama_kelas'];
    $mata_kuliah_id = $_POST['mata_kuliah'];
    $dosen_id = $_POST['dosen'];

    $sql = "INSERT INTO kelas (nama_kelas, mata_kuliah_id, dosen_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $nama_kelas, $mata_kuliah_id, $dosen_id);

    if ($stmt->execute()) {
        echo "Kelas berhasil dibuat!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Ambil data kelas dari database
$sql_kelas = "SELECT kelas.id, kelas.nama_kelas, mata_kuliah.nama AS mata_kuliah, dosen.nama AS dosen
              FROM kelas
              JOIN mata_kuliah ON kelas.mata_kuliah_id = mata_kuliah.id
              JOIN dosen ON kelas.dosen_id = dosen.id";
$result_kelas = $conn->query($sql_kelas);
$kelas_list = [];
if ($result_kelas->num_rows > 0) {
    while ($row_kelas = $result_kelas->fetch_assoc()) {
        $kelas_list[] = $row_kelas;
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
</head>
<body>
<main class="main-content">
    <h2 class="page-title">Pengaturan Kegiatan Belajar Mengajar</h2>
    <form action="kbm.php" method="POST">
        <label for="nama_kelas">Nama Kelas:</label>
        <input type="text" name="nama_kelas" id="nama_kelas" required>
        <label for="mata_kuliah">Mata Kuliah:</label>
        <select name="mata_kuliah" id="mata_kuliah" required>
            <?php foreach ($mata_kuliah_list as $mk): ?>
                <option value="<?php echo $mk['id']; ?>"><?php echo $mk['nama']; ?></option>
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
    <table class="data-table">
        <thead>
            <tr>
                <th>Nama Kelas</th>
                <th>Mata Kuliah</th>
                <th>Dosen</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($kelas_list as $kelas): ?>
                <tr>
                    <td><?php echo $kelas['nama_kelas']; ?></td>
                    <td><?php echo $kelas['mata_kuliah']; ?></td>
                    <td><?php echo $kelas['dosen']; ?></td>
                    <td>
                        <a href="kelola_kelas.php?id=<?php echo $kelas['id']; ?>">Kelola</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php include '../../includes/footer.php'; ?>
</body>
</html>