<?php
// Mulai session dan pastikan pengguna telah login sebagai admin
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../config/database.php'; // Koneksi database

$id = $_GET['id'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Mengambil data dari form
    $id = $_POST['id'];
    $nama_matkul = $_POST['nama_matkul'];
    $semester_id = $_POST['semester'];

    // Mengupdate nama mata kuliah dan semester_id di database
    $sql = "UPDATE mata_kuliah SET nama=?, semester_id=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $nama_matkul, $semester_id, $id);

    if ($stmt->execute()) {
        header("Location: daftar_matkul.php");
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
} else {
    // Ambil data mata kuliah berdasarkan id
    $sql = "SELECT nama, semester_id FROM mata_kuliah WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($nama_matkul, $semester_id);
    $stmt->fetch();
    $stmt->close();

    // Ambil data semester dari database
    $sql_semester = "SELECT id, nama_semester FROM semester";
    $result_semester = $conn->query($sql_semester);
    $semester_list = [];
    if ($result_semester->num_rows > 0) {
        while ($row_semester = $result_semester->fetch_assoc()) {
            $semester_list[] = $row_semester;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Mata Kuliah</title>
    <link rel="stylesheet" href="../../css/style_edit.css">
</head>
<body>
<main class="main-content">
    <h2 class="page-title">Edit Mata Kuliah</h2>
    <form action="edit_matkul.php?id=<?php echo $id; ?>" method="POST">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <label for="nama_matkul">Nama Mata Kuliah:</label>
        <input type="text" name="nama_matkul" id="nama_matkul" value="<?php echo $nama_matkul; ?>" required>
        <label for="semester">Semester:</label>
        <select name="semester" id="semester" required>
            <?php foreach ($semester_list as $semester): ?>
                <option value="<?php echo $semester['id']; ?>" <?php if ($semester['id'] == $semester_id) echo 'selected'; ?>>
                    <?php echo $semester['nama_semester']; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Update</button>
    </form>
    <?php include '../../includes/footer.php'; ?>
</main>
</body>
</html>