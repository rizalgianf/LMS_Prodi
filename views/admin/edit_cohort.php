<?php
// Mulai session dan pastikan pengguna telah login sebagai admin
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../config/database.php'; // Koneksi database

$id = $_GET['id'] ?? '';
if (empty($id)) {
    header("Location: daftar_cohort.php");
    exit();
}

// Ambil data cohort berdasarkan ID
$sql = "SELECT * FROM cohort WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$cohort = $result->fetch_assoc();
$stmt->close();

if (!$cohort) {
    echo "Cohort tidak ditemukan.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_cohort'])) {
    $nama_cohort = $_POST['nama_cohort'];
    $tahun_masuk = $_POST['tahun_masuk'];
    $jumlah_mahasiswa = $_POST['jumlah_mahasiswa'];

    $sql = "UPDATE cohort SET nama_cohort = ?, tahun_masuk = ?, jumlah_mahasiswa = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siii", $nama_cohort, $tahun_masuk, $jumlah_mahasiswa, $id);

    if ($stmt->execute()) {
        header("Location: daftar_cohort.php");
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
    <title>Edit Cohort</title>
    <link rel="stylesheet" href="../../css/style_daftar.css">
</head>
<body>
<main class="main-content">
    <h2 class="page-title">Edit Cohort</h2>
    <form action="edit_cohort.php?id=<?php echo $id; ?>" method="POST">
        <label for="nama_cohort">Nama Cohort:</label>
        <select name="nama_cohort" id="nama_cohort" required>
            <?php for ($i = 1; $i <= 100; $i++): ?>
                <option value="Cohort <?php echo $i; ?>" <?php echo ($cohort['nama_cohort'] == "Cohort $i") ? 'selected' : ''; ?>>Cohort <?php echo $i; ?></option>
            <?php endfor; ?>
        </select>
        <label for="tahun_masuk">Tahun Masuk:</label>
        <select name="tahun_masuk" id="tahun_masuk" required>
            <?php for ($year = 2020; $year <= date("Y"); $year++): ?>
                <option value="<?php echo $year; ?>" <?php echo ($cohort['tahun_masuk'] == $year) ? 'selected' : ''; ?>><?php echo $year; ?></option>
            <?php endfor; ?>
        </select>
        <label for="jumlah_mahasiswa">Jumlah Mahasiswa:</label>
        <input type="number" name="jumlah_mahasiswa" id="jumlah_mahasiswa" value="<?php echo $cohort['jumlah_mahasiswa']; ?>" required>
        <button type="submit" name="update_cohort">Update Cohort</button>
    </form>
    <a href="daftar_cohort.php" class="button kembali">Kembali</a>
    <?php include '../../includes/footer.php'; ?>
<style>
    .button {
        display: inline-block;
        padding: 10px 20px;
        font-size: 16px;
        color: #fff;
        background-color: #007bff;
        border: none;
        border-radius: 5px;
        text-decoration: none;
        text-align: center;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .button:hover {
        background-color: #0056b3;
    }

    .button.kembali {
        background-color: #6c757d;
    }

    .button.kembali:hover {
        background-color: #5a6268;
    }
</style>
</main>


</body>
</html>