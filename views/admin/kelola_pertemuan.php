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
$sql_pertemuan = "SELECT pertemuan.id, pertemuan.tanggal, pertemuan.topik, pertemuan.metode_pembelajaran_id, cohort.nama_cohort, mata_kuliah.nama AS mata_kuliah, users.nama AS dosen, mata_kuliah.semester_id
                  FROM pertemuan
                  JOIN kelas ON pertemuan.kelas_id = kelas.id
                  JOIN cohort ON kelas.id_cohort = cohort.id
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

// Ambil data metode pembelajaran dari database
$sql_metode = "SELECT id, nama_metode FROM metode_pembelajaran";
$result_metode = $conn->query($sql_metode);
$metode_list = [];
if ($result_metode->num_rows > 0) {
    while ($row_metode = $result_metode->fetch_assoc()) {
        $metode_list[] = $row_metode;
    }
}

// Proses form untuk update metode pembelajaran
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_metode'])) {
    $metode_pembelajaran_id = $_POST['metode_pembelajaran'];

    $sql = "UPDATE pertemuan SET metode_pembelajaran_id = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $metode_pembelajaran_id, $pertemuan_id);

    if ($stmt->execute()) {
        // Refresh the page to reflect the updated method
        header("Location: kelola_pertemuan.php?id=$pertemuan_id");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
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
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_absensi'])) {
    foreach ($_POST['absensi'] as $mahasiswa_id => $status) {
        $sql = "INSERT INTO absensi (pertemuan_id, mahasiswa_id, status) VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE status = VALUES(status)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $pertemuan_id, $mahasiswa_id, $status);
        $stmt->execute();
        $stmt->close();
    }
    echo "Absensi berhasil disimpan!";
}

// Ambil data mahasiswa untuk absensi berdasarkan semester mata kuliah
$sql_mahasiswa = "SELECT users.id, users.nama 
                  FROM users 
                  JOIN semester ON users.semester_id = semester.id 
                  WHERE users.role = 'mahasiswa' AND semester.id = ?";
$stmt_mahasiswa = $conn->prepare($sql_mahasiswa);
$stmt_mahasiswa->bind_param("i", $pertemuan['semester_id']);
$stmt_mahasiswa->execute();
$result_mahasiswa = $stmt_mahasiswa->get_result();
$mahasiswa_list = [];
if ($result_mahasiswa->num_rows > 0) {
    while ($row_mahasiswa = $result_mahasiswa->fetch_assoc()) {
        $mahasiswa_list[] = $row_mahasiswa;
    }
}
$stmt_mahasiswa->close();

// Ambil data absensi dari database
$sql_absensi = "SELECT absensi.mahasiswa_id, users.nama, absensi.status
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
        $absensi_list[$row_absensi['mahasiswa_id']] = $row_absensi;
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
    <table class="data-table">
        <tr>
            <th>Kelas</th>
            <td><?php echo $pertemuan['nama_cohort']; ?></td>
        </tr>
        <tr>
            <th>Mata Kuliah</th>
            <td><?php echo $pertemuan['mata_kuliah']; ?></td>
        </tr>
        <tr>
            <th>Dosen</th>
            <td><?php echo $pertemuan['dosen']; ?></td>
        </tr>
        <tr>
            <th>Tanggal</th>
            <td><?php echo $pertemuan['tanggal']; ?></td>
        </tr>
    </table>

    <h3>Metode Pembelajaran</h3>
    <form action="kelola_pertemuan.php?id=<?php echo $pertemuan_id; ?>" method="POST">
        <label for="metode_pembelajaran">Metode Pembelajaran:</label>
        <select name="metode_pembelajaran" id="metode_pembelajaran" required>
            <?php foreach ($metode_list as $metode): ?>
                <option value="<?php echo $metode['id']; ?>" <?php echo ($metode['id'] == $pertemuan['metode_pembelajaran_id']) ? 'selected' : ''; ?>>
                    <?php echo $metode['nama_metode']; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="update_metode">Update Metode</button>
    </form>

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
                        <a href="<?php echo $file['path_file']; ?>" class="download" download>Download</a>
                        <form action="kelola_pertemuan.php?id=<?php echo $pertemuan_id; ?>" method="POST" style="display:inline;">
                            <input type="hidden" name="file_id" value="<?php echo $file['id']; ?>">
                            <input type="hidden" name="path_file" value="<?php echo $file['path_file']; ?>">
                            <button type="submit" name="hapus_file" class="hapus">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Absensi Mahasiswa</h3>
    <form id="absensi-form" action="kelola_pertemuan.php?id=<?php echo $pertemuan_id; ?>" method="POST" onsubmit="return validateAbsensiForm()">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nama Mahasiswa</th>
                    <th>Hadir</th>
                    <th>Izin</th>
                    <th>Sakit</th>
                    <th>Tanpa Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mahasiswa_list as $mahasiswa): ?>
                    <tr>
                        <td><?php echo $mahasiswa['nama']; ?></td>
                        <td><input type="radio" name="absensi[<?php echo $mahasiswa['id']; ?>]" value="Hadir" <?php echo (isset($absensi_list[$mahasiswa['id']]) && $absensi_list[$mahasiswa['id']]['status'] == 'Hadir') ? 'checked' : ''; ?>></td>
                        <td><input type="radio" name="absensi[<?php echo $mahasiswa['id']; ?>]" value="Izin" <?php echo (isset($absensi_list[$mahasiswa['id']]) && $absensi_list[$mahasiswa['id']]['status'] == 'Izin') ? 'checked' : ''; ?>></td>
                        <td><input type="radio" name="absensi[<?php echo $mahasiswa['id']; ?>]" value="Sakit" <?php echo (isset($absensi_list[$mahasiswa['id']]) && $absensi_list[$mahasiswa['id']]['status'] == 'Sakit') ? 'checked' : ''; ?>></td>
                        <td><input type="radio" name="absensi[<?php echo $mahasiswa['id']; ?>]" value="Tanpa Keterangan" <?php echo (isset($absensi_list[$mahasiswa['id']]) && $absensi_list[$mahasiswa['id']]['status'] == 'Tanpa Keterangan') ? 'checked' : ''; ?>></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" name="submit_absensi">Simpan Absensi</button>
    </form>

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

<script>
function validateAbsensiForm() {
    const radios = document.querySelectorAll('input[type="radio"]');
    const mahasiswaIds = new Set();
    radios.forEach(radio => {
        if (radio.checked) {
            mahasiswaIds.add(radio.name);
        }
    });

    const totalMahasiswa = document.querySelectorAll('input[type="radio"][name^="absensi"]').length / 4;
    if (mahasiswaIds.size !== totalMahasiswa) {
        alert("Semua status kehadiran harus diisi sebelum menyimpan absensi.");
        return false;
    }
    return true;
}
</script>

</body>
</html>