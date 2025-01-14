<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../config/database.php'; // Koneksi database

$id = $_GET['id'] ?? '';
if (empty($id)) {
    header("Location: daftar_mahasiswa.php");
    exit();
}

// Ambil data mahasiswa berdasarkan ID
$sql = "SELECT * FROM users WHERE id = ? AND role = 'mahasiswa'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$mahasiswa = $result->fetch_assoc();
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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_mahasiswa'])) {
    $nama = $_POST['nama'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $semester_id = $_POST['semester'];

    if (!empty($password)) {
        // Hash password jika diubah
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET nama = ?, username = ?, password = ?, semester_id = ? WHERE id = ? AND role = 'mahasiswa'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssii", $nama, $username, $hashed_password, $semester_id, $id);
    } else {
        $sql = "UPDATE users SET nama = ?, username = ?, semester_id = ? WHERE id = ? AND role = 'mahasiswa'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssii", $nama, $username, $semester_id, $id);
    }

    if ($stmt->execute()) {
        header("Location: daftar_mahasiswa.php");
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
    <title>Edit Mahasiswa</title>
    <link rel="stylesheet" href="../../css/style_daftar.css">
</head>
<body>
<main class="main-content">
    <h2 class="page-title">Edit Mahasiswa</h2>
    <form action="edit_mahasiswa.php?id=<?php echo $id; ?>" method="POST">
        <label for="nama">Nama:</label>
        <input type="text" name="nama" id="nama" value="<?php echo $mahasiswa['nama']; ?>" required>
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" value="<?php echo $mahasiswa['username']; ?>" required>
        <label for="password">Password (kosongkan jika tidak ingin mengubah):</label>
        <input type="password" name="password" id="password">
        <label for="semester">Semester:</label>
        <select name="semester" id="semester" required>
            <?php foreach ($semester_list as $semester): ?>
                <option value="<?php echo $semester['id']; ?>" <?php echo ($semester['id'] == $mahasiswa['semester_id']) ? 'selected' : ''; ?>>
                    <?php echo $semester['nama_semester']; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="update_mahasiswa">Update</button>
    </form>
</main>

<?php include '../../includes/footer.php'; ?>
</body>
</html>