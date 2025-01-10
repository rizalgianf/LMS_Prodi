<?php
// Check if a session is already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../config/database.php'; // Koneksi database

$page_title = "Kelola Pertemuan";
include '../../includes/header.php';

$pertemuan_id = $_GET['id'] ?? '';
if (empty($pertemuan_id)) {
    header("Location: kbm.php");
    exit();
}

// Ambil data pertemuan
$sql_pertemuan = "SELECT pertemuan.id, pertemuan.tanggal, pertemuan.topik, kelas.nama_kelas, mata_kuliah.nama AS mata_kuliah, users.nama AS dosen
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

// Proses form untuk upload file
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_file'])) {
    $nama_file = $_FILES['file']['name'];
    $path_file = "../../uploads/" . basename($nama_file);

    // Pastikan direktori uploads ada
    if (!is_dir('../../uploads')) {
        mkdir('../../uploads', 0777, true);
    }

    if (move_uploaded_file($_FILES['file']['tmp_name'], $path_file)) {
        $sql = "INSERT INTO file_pertemuan (pertemuan_id, nama_file, path_file) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $pertemuan_id, $nama_file, $path_file);

        if ($stmt->execute()) {
            echo "File berhasil diunggah!";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error: Gagal mengunggah file.";
    }
}

// Proses form untuk menghapus file
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['hapus_file'])) {
    $file_id = $_POST['file_id'];
    $path_file = $_POST['path_file'];

    // Hapus file dari sistem file
    if (file_exists($path_file)) {
        unlink($path_file);
    }

    // Hapus file dari database
    $sql = "DELETE FROM file_pertemuan WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $file_id);

    if ($stmt->execute()) {
        echo "File berhasil dihapus!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
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

// Proses form untuk absensi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['absensi'])) {
    $mahasiswa_id = $_POST['mahasiswa_id'];
    $status = $_POST['status'];

    $sql = "INSERT INTO absensi (pertemuan_id, mahasiswa_id, status) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $pertemuan_id, $mahasiswa_id, $status);

    if ($stmt->execute()) {
        echo "Absensi berhasil disimpan!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Proses form untuk menghapus absensi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['hapus_absensi'])) {
    $absensi_id = $_POST['absensi_id'];

    $sql = "DELETE FROM absensi WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $absensi_id);

    if ($stmt->execute()) {
        echo "Absensi berhasil dihapus!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Ambil data mahasiswa untuk absensi
$sql_mahasiswa = "SELECT id, nama FROM users WHERE role = 'mahasiswa'";
$result_mahasiswa = $conn->query($sql_mahasiswa);
$mahasiswa_list = [];
if ($result_mahasiswa->num_rows > 0) {
    while ($row_mahasiswa = $result_mahasiswa->fetch_assoc()) {
        $mahasiswa_list[] = $row_mahasiswa;
    }
}

// Ambil data absensi dari database
$sql_absensi = "SELECT absensi.id, users.nama, absensi.status
                FROM absensi
                JOIN users ON absensi.mahasiswa_id = users.id
                WHERE absensi.pertemuan_id = ?";
$stmt_absensi = $conn->prepare($sql_absensi);
$stmt_absensi->bind_param("i", $pertemuan_id);
$stmt_absensi->execute();
$result_absensi = $stmt_absensi->get_result();
$absensi_list = [];
if ($result_absensi->num_rows > 0) {
    while ($row_absensi = $result_absensi->fetch_assoc()) {
        $absensi_list[] = $row_absensi;
    }
}
$stmt_absensi->close();

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
    <h2 class="page-title">Kelola Pertemuan: <?php echo $pertemuan['topik']; ?></h2>
    <p>Kelas: <?php echo $pertemuan['nama_kelas']; ?></p>
    <p>Mata Kuliah: <?php echo $pertemuan['mata_kuliah']; ?></p>
    <p>Dosen: <?php echo $pertemuan['dosen']; ?></p>
    <p>Tanggal: <?php echo $pertemuan['tanggal']; ?></p>

    <h3>Upload File</h3>
    <form action="kelola_pertemuan.php?id=<?php echo $pertemuan_id; ?>" method="POST" enctype="multipart/form-data">
        <label for="file">Pilih File:</label>
        <input type="file" name="file" id="file" required>
        <button type="submit" name="upload_file">Upload</button>
    </form>
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
                        <form action="kelola_pertemuan.php?id=<?php echo $pertemuan_id; ?>" method="POST" style="display:inline;">
                            <input type="hidden" name="file_id" value="<?php echo $file['id']; ?>">
                            <input type="hidden" name="path_file" value="<?php echo $file['path_file']; ?>">
                            <button type="submit" name="hapus_file">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Absensi Mahasiswa</h3>
    <form action="kelola_pertemuan.php?id=<?php echo $pertemuan_id; ?>" method="POST">
        <label for="mahasiswa_id">Mahasiswa:</label>
        <select name="mahasiswa_id" id="mahasiswa_id" required>
            <?php foreach ($mahasiswa_list as $mahasiswa): ?>
                <option value="<?php echo $mahasiswa['id']; ?>"><?php echo $mahasiswa['nama']; ?></option>
            <?php endforeach; ?>
        </select>
        <label for="status">Status:</label>
        <select name="status" id="status" required>
            <option value="Hadir">Hadir</option>
            <option value="Izin">Izin</option>
            <option value="Tanpa Keterangan">Tanpa Keterangan</option>
        </select>
        <button type="submit" name="absensi">Simpan Absensi</button>
    </form>
    <h3>Daftar Absensi</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Nama Mahasiswa</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($absensi_list as $absensi): ?>
                <tr>
                    <td><?php echo $absensi['nama']; ?></td>
                    <td><?php echo $absensi['status']; ?></td>
                    <td>
                        <form action="kelola_pertemuan.php?id=<?php echo $pertemuan_id; ?>" method="POST" style="display:inline;">
                            <input type="hidden" name="absensi_id" value="<?php echo $absensi['id']; ?>">
                            <button type="submit" name="hapus_absensi">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Forum Diskusi</h3>
    <form action="kelola_pertemuan.php?id=<?php echo $pertemuan_id; ?>" method="POST">
        <label for="pesan">Pesan:</label>
        <textarea name="pesan" id="pesan" rows="4" required></textarea>
        <button type="submit" name="kirim_pesan">Kirim Pesan</button>
    </form>
    <h3>Daftar Pesan</h3>
    <div class="forum-diskusi">
        <?php foreach ($pesan_list as $pesan): ?>
            <div class="pesan">
                <p><strong><?php echo $pesan['nama']; ?>:</strong> <?php echo $pesan['pesan']; ?></p>
                <p><small><?php echo $pesan['waktu']; ?></small></p>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>
</body>
</html>