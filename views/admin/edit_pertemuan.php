<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../config/database.php'; // Koneksi database

$id = $_GET['id'] ?? '';
if (empty($id)) {
    header("Location: kelola_kelas.php");
    exit();
}

// Ambil data pertemuan berdasarkan ID
$sql = "SELECT * FROM pertemuan WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$pertemuan = $result->fetch_assoc();
$stmt->close();

if (!$pertemuan) {
    echo "Pertemuan tidak ditemukan.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_pertemuan'])) {
    $tanggal = $_POST['tanggal'];
    $hari = $_POST['hari'];
    $waktu_mulai = $_POST['waktu_mulai'];
    $waktu_selesai = $_POST['waktu_selesai'];
    $topik = $_POST['topik'];

    $sql = "UPDATE pertemuan SET tanggal = ?, hari = ?, waktu_mulai = ?, waktu_selesai = ?, topik = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $tanggal, $hari, $waktu_mulai, $waktu_selesai, $topik, $id);

    if ($stmt->execute()) {
        header("Location: kelola_kelas.php?id=" . $pertemuan['kelas_id']);
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Pertemuan</title>
    <link rel="stylesheet" href="../../css/style_edit.css">
</head>
<body>
<main class="main-content">
    <h2 class="page-title">Edit Pertemuan</h2>
    <form action="edit_pertemuan.php?id=<?php echo $id; ?>" method="POST">
        <label for="tanggal">Tanggal:</label>
        <input type="date" name="tanggal" id="tanggal" value="<?php echo $pertemuan['tanggal']; ?>" required onchange="updateHari()">
        <label for="hari">Hari:</label>
        <input type="text" name="hari" id="hari" value="<?php echo $pertemuan['hari']; ?>" readonly>
        <label for="waktu_mulai">Waktu Mulai:</label>
        <input type="time" name="waktu_mulai" id="waktu_mulai" value="<?php echo $pertemuan['waktu_mulai']; ?>" required onchange="updateWaktuSelesai()">
        <label for="waktu_selesai">Waktu Selesai:</label>
        <input type="time" name="waktu_selesai" id="waktu_selesai" value="<?php echo $pertemuan['waktu_selesai']; ?>" readonly>
        <label for="topik">Topik:</label>
        <input type="text" name="topik" id="topik" value="<?php echo $pertemuan['topik']; ?>" required>
        <div class="button-container">
            <button type="submit" name="update_pertemuan" class="button update">Update</button>
            <a href="kelola_kelas.php?id=<?php echo $pertemuan['kelas_id']; ?>" class="button kembali">Kembali</a>
        </div>
    </form>
</main>

<script>
function updateHari() {
    const tanggal = document.getElementById('tanggal').value;
    const hari = new Date(tanggal).toLocaleDateString('id-ID', { weekday: 'long' });
    const hariIndo = {
        'Sunday': 'Minggu',
        'Monday': 'Senin',
        'Tuesday': 'Selasa',
        'Wednesday': 'Rabu',
        'Thursday': 'Kamis',
        'Friday': 'Jumat',
        'Saturday': 'Sabtu'
    };
    document.getElementById('hari').value = hariIndo[hari];
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