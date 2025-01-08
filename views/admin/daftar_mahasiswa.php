<?php
// Mulai session dan pastikan pengguna telah login sebagai admin
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../config/database.php'; // Koneksi database

// Mengatur judul halaman
$page_title = "Daftar Mahasiswa";
include '../../includes/header_admin.php'; // Menggunakan header khusus untuk admin

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Mengambil data dari form
    $nama = $_POST['nama'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = 'mahasiswa'; // Set role sebagai mahasiswa

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Menyimpan nama, username, hashed password, dan role ke database
    $sql = "INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nama, $username, $hashed_password, $role);

    if ($stmt->execute()) {
        echo "Pendaftaran berhasil!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Ambil data mahasiswa dari database
$sql_mahasiswa = "SELECT id, nama, username FROM users WHERE role='mahasiswa'";
$result_mahasiswa = $conn->query($sql_mahasiswa);
$mahasiswa_list = [];
if ($result_mahasiswa->num_rows > 0) {
    while ($row_mahasiswa = $result_mahasiswa->fetch_assoc()) {
        $mahasiswa_list[] = $row_mahasiswa;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../../css/style_daftarmahasiswa.css">
</head>
<body>
<main class="main-content">
    <h2 class="page-title">Daftar Mahasiswa</h2>
    <form action="daftar_mahasiswa.php" method="POST">
        <label for="nama">Nama:</label>
        <input type="text" name="nama" id="nama" required>
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required>
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>
        <button type="submit">Daftar</button>
    </form>
    <table class="data-table">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Username</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($mahasiswa_list as $mahasiswa): ?>
                <tr>
                    <td><?php echo $mahasiswa['nama']; ?></td>
                    <td><?php echo $mahasiswa['username']; ?></td>
                    <td>
                        <a href="hapus_mahasiswa.php?id=<?php echo $mahasiswa['id']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus mahasiswa ini?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php include '../../includes/footer.php'; ?>
</body>
</html>