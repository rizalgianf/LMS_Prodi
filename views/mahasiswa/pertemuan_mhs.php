<?php
// Check if a session is already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../../login.php");
    exit();
}

include '../../config/database.php'; // Koneksi database

$page_title = "Detail Pertemuan";
include '../../includes/header_mahasiswa.php';

$pertemuan_id = $_GET['id'] ?? '';
if (empty($pertemuan_id)) {
    header("Location: kelas_mhs.php");
    exit();
}

// Ambil data pertemuan
$sql_pertemuan = "SELECT pertemuan.id, pertemuan.tanggal, pertemuan.topik, kelas.nama_kelas, mata_kuliah.nama AS mata_kuliah, users.nama AS dosen, mata_kuliah.semester_id
                  FROM pertemuan
                  JOIN kelas ON pertemuan.kelas_id = kelas.id
                  JOIN mata_kuliah ON kelas.mata_kuliah_id = mata_kuliah.id
                  JOIN users ON kelas.dosen_id = users.id
                  WHERE pertemuan.id = ?";
$stmt_pertemuan = $conn->prepare($sql_pertemuan);
$stmt_pertemuan->bind_param("i", $pertemuan_id);
$stmt_pertemuan->execute();
$result_pertemuan = $stmt_pertemuan->get_result();
$pertemuan = $result_pertemuan->fetch_assoc();
$stmt_pertemuan->close();

if (!$pertemuan) {
    echo "Pertemuan tidak ditemukan.";
    exit();
}

// Ambil data file yang diunggah dari database
$sql_file = "SELECT * FROM file_pertemuan WHERE pertemuan_id = ?";
$stmt_file = $conn->prepare($sql_file);
$stmt_file->bind_param("i", $pertemuan_id);
$stmt_file->execute();
$result_file = $stmt_file->get_result();
$file_list = [];
if ($result_file->num_rows > 0) {
    while ($row_file = $result_file->fetch_assoc()) {
        $file_list[] = $row_file;
    }
}
$stmt_file->close();

// Ambil data absensi dari database
$mahasiswa_id = $_SESSION['user_id'];
$sql_absensi = "SELECT absensi.id, users.nama, absensi.status
                FROM absensi
                JOIN users ON absensi.mahasiswa_id = users.id
                WHERE absensi.pertemuan_id = ? AND absensi.mahasiswa_id = ?";
$stmt_absensi = $conn->prepare($sql_absensi);
$stmt_absensi->bind_param("ii", $pertemuan_id, $mahasiswa_id);
$stmt_absensi->execute();
$result_absensi = $stmt_absensi->get_result();
$absensi = $result_absensi->fetch_assoc();
$stmt_absensi->close();

// Ambil nama mahasiswa dari database
$sql_mahasiswa = "SELECT nama FROM users WHERE id = ?";
$stmt_mahasiswa = $conn->prepare($sql_mahasiswa);
$stmt_mahasiswa->bind_param("i", $mahasiswa_id);
$stmt_mahasiswa->execute();
$result_mahasiswa = $stmt_mahasiswa->get_result();
$mahasiswa = $result_mahasiswa->fetch_assoc();
$stmt_mahasiswa->close();

// Proses form untuk forum diskusi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['kirim_pesan'])) {
    if (isset($_SESSION['user_id'])) {
        $pengguna_id = $_SESSION['user_id'];
        $pesan = $_POST['pesan'];

        $sql = "INSERT INTO forum_diskusi (pertemuan_id, pengguna_id, pesan) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $pertemuan_id, $pengguna_id, $pesan);

        if ($stmt->execute()) {
            echo "Pesan berhasil dikirim!";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error: User ID tidak ditemukan dalam sesi.";
    }
}

// Ambil data pesan dari forum diskusi
$sql_pesan = "SELECT forum_diskusi.id, users.nama, forum_diskusi.pesan, forum_diskusi.waktu
              FROM forum_diskusi
              JOIN users ON forum_diskusi.pengguna_id = users.id
              WHERE forum_diskusi.pertemuan_id = ?
              ORDER BY forum_diskusi.waktu ASC";
$stmt_pesan = $conn->prepare($sql_pesan);
$stmt_pesan->bind_param("i", $pertemuan_id);
$stmt_pesan->execute();
$result_pesan = $stmt_pesan->get_result();
$pesan_list = [];
if ($result_pesan->num_rows > 0) {
    while ($row_pesan = $result_pesan->fetch_assoc()) {
        $pesan_list[] = $row_pesan;
    }
}
$stmt_pesan->close();

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
    <h2 class="page-title">Detail Pertemuan: <?php echo $pertemuan['topik']; ?></h2>
    <p>Kelas: <?php echo $pertemuan['nama_kelas']; ?></p>
    <p>Mata Kuliah: <?php echo $pertemuan['mata_kuliah']; ?></p>
    <p>Dosen: <?php echo $pertemuan['dosen']; ?></p>
    <p>Tanggal: <?php echo $pertemuan['tanggal']; ?></p>

    <h3>Daftar File</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Nama File</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($file_list as $file): ?>
                <tr>
                    <td><?php echo $file['nama_file']; ?></td>
                    <td>
                        <a href="<?php echo $file['path_file']; ?>" download>Download</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Absensi</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Nama Mahasiswa</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?php echo $mahasiswa['nama']; ?></td>
                <td><?php echo $absensi ? $absensi['status'] : 'Belum diabsen'; ?></td>
            </tr>
        </tbody>
    </table>

    <h3>Forum Diskusi</h3>
    <form action="pertemuan_mhs.php?id=<?php echo $pertemuan_id; ?>" method="POST">
        <label for="pesan">Pesan:</label>
        <textarea name="pesan" id="pesan" rows="4" required></textarea>
        <button type="submit" name="kirim_pesan">Kirim Pesan</button>
    </form>
    <h3>Daftar Pesan</h3>
    <div class="forum-diskusi">
        <?php if (!empty($pesan_list)): ?>
            <?php foreach ($pesan_list as $pesan): ?>
                <div class="pesan">
                    <p><strong><?php echo $pesan['nama']; ?>:</strong> <?php echo $pesan['pesan']; ?></p>
                    <p><small><?php echo $pesan['waktu']; ?></small></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Belum ada pesan di forum diskusi.</p>
        <?php endif; ?>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>
</body>
</html>