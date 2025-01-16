<?php
// filepath: /E:/GITHUB REPOSITORY/SIAKAD/views/admin/kelola_kelas.php
// Mulai session dan pastikan pengguna telah login sebagai admin
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../config/database.php'; // Koneksi database

$page_title = "Kelola Kelas";
include '../../includes/header.php';

$kelas_id = $_GET['id'] ?? '';
if (empty($kelas_id)) {
    header("Location: kbm.php");
    exit();
}

// Ambil data kelas
$sql_kelas = "SELECT kelas.id, cohort.nama_cohort, mata_kuliah.nama AS mata_kuliah, mata_kuliah.jumlah_sks, users.nama AS dosen
              FROM kelas
              JOIN cohort ON kelas.id_cohort = cohort.id
              JOIN mata_kuliah ON kelas.mata_kuliah_id = mata_kuliah.id
              JOIN users ON kelas.dosen_id = users.id
              WHERE kelas.id = ?";
$stmt_kelas = $conn->prepare($sql_kelas);
$stmt_kelas->bind_param("i", $kelas_id);
$stmt_kelas->execute();
$result_kelas = $stmt_kelas->get_result();
$kelas = $result_kelas->fetch_assoc();
$stmt_kelas->close();

if (!$kelas) {
    echo "Kelas tidak ditemukan.";
    exit();
}

// Proses form untuk membuat pertemuan baru
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['buat_pertemuan'])) {
    $tanggal = $_POST['tanggal'];
    $hari = $_POST['hari'];
    $waktu_mulai = $_POST['waktu_mulai'];
    $waktu_selesai = $_POST['waktu_selesai'];
    $topik = $_POST['topik'];

    $sql = "INSERT INTO pertemuan (kelas_id, tanggal, hari, waktu_mulai, waktu_selesai, topik) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssss", $kelas_id, $tanggal, $hari, $waktu_mulai, $waktu_selesai, $topik);

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
    <h2 class="page-title">Kelola Kelas: <?php echo $kelas['nama_cohort']; ?></h2>
    <table class="data-table">
        <tr>
            <th>Mata Kuliah</th>
            <td><?php echo $kelas['mata_kuliah']; ?></td>
        </tr>
        <tr>
            <th>SKS</th>
            <td><?php echo $kelas['jumlah_sks']; ?></td>
        </tr>
        <tr>
            <th>Dosen</th>
            <td><?php echo $kelas['dosen']; ?></td>
        </tr>
    </table>
    <form action="kelola_kelas.php?id=<?php echo $kelas_id; ?>" method="POST">
        <label for="tanggal">Tanggal:</label>
        <input type="date" name="tanggal" id="tanggal" required onchange="updateHari()">
        <label for="hari">Hari:</label>
        <input type="text" name="hari" id="hari" readonly>
        <label for="waktu_mulai">Waktu Mulai:</label>
        <input type="time" name="waktu_mulai" id="waktu_mulai" required onchange="updateWaktuSelesai()">
        <label for="waktu_selesai">Waktu Selesai:</label>
        <input type="time" name="waktu_selesai" id="waktu_selesai" readonly>
        <label for="topik">Topik:</label>
        <input type="text" name="topik" id="topik" required>
        <button type="submit" name="buat_pertemuan">Buat Pertemuan</button>
    </form>
    <h2>Daftar Pertemuan</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Hari</th>
                <th>Waktu Mulai</th>
                <th>Waktu Selesai</th>
                <th>Topik</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pertemuan_list as $pertemuan): ?>
                <tr>
                    <td><?php echo $pertemuan['tanggal']; ?></td>
                    <td><?php echo $pertemuan['hari']; ?></td>
                    <td><?php echo $pertemuan['waktu_mulai']; ?></td>
                    <td><?php echo $pertemuan['waktu_selesai']; ?></td>
                    <td><?php echo $pertemuan['topik']; ?></td>
                    <td>
                        <a class="kelola" href="kelola_pertemuan.php?id=<?php echo $pertemuan['id']; ?>">Kelola</a>
                        <a class="edit" href="edit_pertemuan.php?id=<?php echo $pertemuan['id']; ?>">Edit</a>
                        <a class="hapus" href="hapus_pertemuan.php?id=<?php echo $pertemuan['id']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus pertemuan ini?')">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<script>
function updateHari() {
    const tanggal = document.getElementById('tanggal').value;
    if (!tanggal) return;
    const hari = new Date(tanggal).toLocaleDateString('id-ID', { weekday: 'long' });
    const hariIndo = {
        'Minggu': 'Minggu',
        'Senin': 'Senin',
        'Selasa': 'Selasa',
        'Rabu': 'Rabu',
        'Kamis': 'Kamis',
        'Jumat': 'Jumat',
        'Sabtu': 'Sabtu'
    };
    document.getElementById('hari').value = hariIndo[hari] || 'Tidak diketahui';
}

function updateWaktuSelesai() {
    const waktuMulai = document.getElementById('waktu_mulai').value;
    if (!waktuMulai) return; // Ensure waktuMulai is not empty
    const sks = <?php echo $kelas['jumlah_sks']; ?>;
    const waktuMulaiDate = new Date(`1970-01-01T${waktuMulai}:00`);
    const waktuSelesaiDate = new Date(waktuMulaiDate.getTime() + sks * 50 * 60000);
    const waktuSelesai = waktuSelesaiDate.toTimeString().split(' ')[0].substring(0, 5);
    document.getElementById('waktu_selesai').value = waktuSelesai;
}
</script>

<?php include '../../includes/footer.php'; ?>
</body>
</html>