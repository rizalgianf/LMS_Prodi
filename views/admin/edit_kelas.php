<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../config/database.php'; // Koneksi database

$id = $_GET['id'] ?? '';
if (empty($id)) {
    header("Location: kbm.php");
    exit();
}

// Ambil data kelas berdasarkan ID
$sql = "SELECT * FROM kelas WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$kelas = $result->fetch_assoc();
$stmt->close();

if (!$kelas) {
    echo "Kelas tidak ditemukan.";
    exit();
}

// Ambil data mata kuliah dan semester dari database
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

// Ambil data cohort dari database
$sql_cohort = "SELECT id, nama_cohort FROM cohort";
$result_cohort = $conn->query($sql_cohort);
$cohort_list = [];
if ($result_cohort->num_rows > 0) {
    while ($row_cohort = $result_cohort->fetch_assoc()) {
        $cohort_list[] = $row_cohort;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_kelas'])) {
    $nama_kelas = $_POST['nama_kelas'];
    $mata_kuliah_id = $_POST['mata_kuliah'];
    $dosen_id = $_POST['dosen'];

    $sql = "UPDATE kelas SET nama_kelas = ?, mata_kuliah_id = ?, dosen_id = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siii", $nama_kelas, $mata_kuliah_id, $dosen_id, $id);

    if ($stmt->execute()) {
        header("Location: kbm.php");
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
    <title>Edit Kelas</title>
    <link rel="stylesheet" href="../../css/style_edit.css">
</head>
<body>
<main class="main-content">
    <h2 class="page-title">Edit Kelas</h2>
    <form action="edit_kelas.php?id=<?php echo $id; ?>" method="POST">
        <label for="nama_kelas">Nama Kelas:</label>
        <select name="nama_kelas" id="nama_kelas" required>
            <?php foreach ($cohort_list as $cohort): ?>
                <option value="<?php echo $cohort['nama_cohort']; ?>" <?php echo ($cohort['nama_cohort'] == $kelas['nama_kelas']) ? 'selected' : ''; ?>>
                    <?php echo $cohort['nama_cohort']; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label for="mata_kuliah">Mata Kuliah:</label>
        <select name="mata_kuliah" id="mata_kuliah" required>
            <?php foreach ($mata_kuliah_list as $mk): ?>
                <option value="<?php echo $mk['id']; ?>" <?php echo ($mk['id'] == $kelas['mata_kuliah_id']) ? 'selected' : ''; ?>>
                    <?php echo $mk['nama']; ?> - <?php echo $mk['nama_semester']; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label for="dosen">Dosen:</label>
        <select name="dosen" id="dosen" required>
            <?php foreach ($dosen_list as $dosen): ?>
                <option value="<?php echo $dosen['id']; ?>" <?php echo ($dosen['id'] == $kelas['dosen_id']) ? 'selected' : ''; ?>>
                    <?php echo $dosen['nama']; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <div class="button-container">
            <button type="submit" name="update_kelas" class="button update">Update</button>
            <a href="kbm.php" class="button kembali">Kembali</a>
        </div>
    </form>
    <?php include '../../includes/footer.php'; ?>
</main>
</body>
</html>