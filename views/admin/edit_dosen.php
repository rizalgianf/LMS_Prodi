<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../config/database.php'; // Koneksi database

$id = $_GET['id'] ?? '';
if (empty($id)) {
    header("Location: daftar_dosen.php");
    exit();
}

// Ambil data dosen berdasarkan ID
$sql = "SELECT * FROM users WHERE id = ? AND role = 'dosen'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$dosen = $result->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_dosen'])) {
    $nama = $_POST['nama'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (!empty($password)) {
        // Hash password jika diubah
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET nama = ?, username = ?, password = ? WHERE id = ? AND role = 'dosen'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $nama, $username, $hashed_password, $id);
    } else {
        $sql = "UPDATE users SET nama = ?, username = ? WHERE id = ? AND role = 'dosen'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $nama, $username, $id);
    }

    if ($stmt->execute()) {
        header("Location: daftar_dosen.php");
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
    <title>Edit Dosen</title>
    <link rel="stylesheet" href="../../css/style_edit.css">
</head>
<body>
<main class="main-content">
    <h2 class="page-title">Edit Dosen</h2>
    <form action="edit_dosen.php?id=<?php echo $id; ?>" method="POST">
        <label for="nama">Nama:</label>
        <input type="text" name="nama" id="nama" value="<?php echo $dosen['nama']; ?>" required>
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" value="<?php echo $dosen['username']; ?>" required>
        <label for="password">Password (kosongkan jika tidak ingin mengubah):</label>
        <input type="password" name="password" id="password">
        <button type="submit" name="update_dosen">Update</button>
    </form>
</main>

<?php include '../../includes/footer.php'; ?>
</body>
</html>