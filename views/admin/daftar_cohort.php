<?php
// Mulai session dan pastikan pengguna telah login sebagai admin
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../config/database.php'; // Koneksi database

// Mengatur judul halaman
$page_title = "Daftar Cohort";
include '../../includes/header.php'; // Menggunakan header khusus untuk admin

// Cek apakah kolom jumlah_mahasiswa sudah ada
$result = $conn->query("SHOW COLUMNS FROM cohort LIKE 'jumlah_mahasiswa'");
$exists = ($result->num_rows > 0) ? true : false;

if (!$exists) {
    // Tambahkan kolom jumlah_mahasiswa jika belum ada
    $conn->query("ALTER TABLE cohort ADD COLUMN jumlah_mahasiswa INT DEFAULT 0");
}

// Proses form untuk menambah cohort baru
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['daftar_cohort'])) {
    $nama_cohort = $_POST['nama_cohort'];
    $tahun_masuk = $_POST['tahun_masuk'];
    $jumlah_mahasiswa = $_POST['jumlah_mahasiswa'];

    $sql = "INSERT INTO cohort (nama_cohort, tahun_masuk, jumlah_mahasiswa) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $nama_cohort, $tahun_masuk, $jumlah_mahasiswa);

    if ($stmt->execute()) {
        echo "Cohort berhasil ditambahkan!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Ambil data cohort dari database
$sql_cohort = "SELECT * FROM cohort ORDER BY tahun_masuk ASC";
$result_cohort = $conn->query($sql_cohort);
$cohort_list = [];
$used_cohorts = [];
if ($result_cohort->num_rows > 0) {
    while ($row_cohort = $result_cohort->fetch_assoc()) {
        $cohort_list[] = $row_cohort;
        $used_cohorts[] = $row_cohort['nama_cohort'];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../../css/style_daftar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<main class="main-content">
    <h2 class="page-title">Daftar Cohort</h2>
    <form action="daftar_cohort.php" method="POST">
        <label for="nama_cohort">Nama Cohort:</label>
        <select name="nama_cohort" id="nama_cohort" required>
            <?php for ($i = 1; $i <= 100; $i++): ?>
                <?php $cohort_name = "Cohort $i"; ?>
                <?php if (!in_array($cohort_name, $used_cohorts)): ?>
                    <option value="<?php echo $cohort_name; ?>"><?php echo $cohort_name; ?></option>
                <?php endif; ?>
            <?php endfor; ?>
        </select>
        <label for="tahun_masuk">Tahun Masuk:</label>
        <select name="tahun_masuk" id="tahun_masuk" required>
            <?php for ($year = 2020; $year <= date("Y"); $year++): ?>
                <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
            <?php endfor; ?>
        </select>
        <label for="jumlah_mahasiswa">Jumlah Mahasiswa:</label>
        <input type="number" name="jumlah_mahasiswa" id="jumlah_mahasiswa" required>
        <button type="submit" name="daftar_cohort">Tambah Cohort</button>
    </form>

    <h3>Daftar Cohort</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Nama Cohort</th>
                <th>Tahun Masuk</th>
                <th>Jumlah Mahasiswa</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cohort_list as $cohort): ?>
                <tr>
                    <td><?php echo $cohort['nama_cohort']; ?></td>
                    <td><?php echo $cohort['tahun_masuk']; ?></td>
                    <td><?php echo $cohort['jumlah_mahasiswa']; ?></td>
                    <td>
                        <a href="edit_cohort.php?id=<?php echo $cohort['id']; ?>" class="edit">Edit</a>
                        <a href="hapus_cohort.php?id=<?php echo $cohort['id']; ?>" class="delete" onclick="return confirm('Apakah Anda yakin ingin menghapus cohort ini?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php include '../../includes/footer.php'; ?>
</body>
</html>