<?php
// Mulai session dan pastikan pengguna telah login sebagai admin
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../config/database.php'; // Koneksi database

// Mengatur judul halaman
$page_title = "Daftar Dosen";
include '../../includes/header.php'; // Menggunakan header khusus untuk admin

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['daftar_dosen'])) {
    // Mengambil data dari form
    $nama = $_POST['nama'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = 'dosen'; // Set role sebagai dosen

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

// Ambil data dosen dari database
$search = $_GET['search'] ?? '';
$sql_dosen = "SELECT id, nama, username FROM users WHERE role='dosen' AND nama LIKE ?";
$stmt = $conn->prepare($sql_dosen);
$search_param = "%$search%";
$stmt->bind_param("s", $search_param);
$stmt->execute();
$result_dosen = $stmt->get_result();
$dosen_list = [];
if ($result_dosen->num_rows > 0) {
    while ($row_dosen = $result_dosen->fetch_assoc()) {
        $dosen_list[] = $row_dosen;
    }
}
$stmt->close();

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
    <h2 class="page-title">Daftar Dosen</h2>
    <form action="daftar_dosen.php" method="POST">
        <label for="nama">Nama:</label>
        <input type="text" name="nama" id="nama" required>
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required>
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>
        <button type="submit" name="daftar_dosen">Daftar</button>
    </form>

    <form action="daftar_dosen.php" method="GET" class="search-form">
        <label for="search" class="sr-only">Cari Nama Dosen:</label>
        <div class="search-container">
            <input type="text" name="search" id="search" placeholder="Cari Nama Dosen" value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit"><i class="fas fa-search"></i></button>
        </div>
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
            <?php foreach ($dosen_list as $dosen): ?>
                <tr>
                    <td><?php echo $dosen['nama']; ?></td>
                    <td><?php echo $dosen['username']; ?></td>
                    <td class="action-buttons">
                        <a href="edit_dosen.php?id=<?php echo $dosen['id']; ?>" class="edit">Edit</a>
                        <a href="hapus_dosen.php?id=<?php echo $dosen['id']; ?>" class="delete" onclick="return confirm('Apakah Anda yakin ingin menghapus dosen ini?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php include '../../includes/footer.php'; ?>
</body>
</html>