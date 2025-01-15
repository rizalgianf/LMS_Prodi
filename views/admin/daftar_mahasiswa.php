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
include '../../includes/header.php'; // Menggunakan header khusus untuk admin

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['daftar_mahasiswa'])) {
    $nama = $_POST['nama'];
    $username = $_POST['username'];
    $nim = $_POST['nim'];
    $password = $_POST['password'];
    $semester_id = $_POST['semester'];
    $cohort_id = $_POST['cohort'];
    $role = 'mahasiswa';

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (nama, username, nim, password, semester_id, cohort_id, role) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssiss", $nama, $username, $nim, $hashed_password, $semester_id, $cohort_id, $role);

    if ($stmt->execute()) {
        echo "Pendaftaran berhasil!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Ambil data mahasiswa dari database
$search = $_GET['search'] ?? '';
$sort_semester = $_GET['sort_semester'] ?? '';
$sql_mahasiswa = "SELECT users.id, users.nama, users.username, users.nim, semester.nama_semester, cohort.nama_cohort 
                  FROM users 
                  LEFT JOIN semester ON users.semester_id = semester.id 
                  LEFT JOIN cohort ON users.cohort_id = cohort.id 
                  WHERE users.role='mahasiswa' AND users.nama LIKE ?";
if ($sort_semester) {
    $sql_mahasiswa .= " AND semester.nama_semester = ?";
}
$sql_mahasiswa .= " ORDER BY semester.nama_semester ASC";
$stmt = $conn->prepare($sql_mahasiswa);
$search_param = "%$search%";
if ($sort_semester) {
    $stmt->bind_param("ss", $search_param, $sort_semester);
} else {
    $stmt->bind_param("s", $search_param);
}
$stmt->execute();
$result_mahasiswa = $stmt->get_result();
$mahasiswa_list = [];
if ($result_mahasiswa->num_rows > 0) {
    while ($row_mahasiswa = $result_mahasiswa->fetch_assoc()) {
        $mahasiswa_list[] = $row_mahasiswa;
    }
}
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

// Ambil data cohort dari database
$sql_cohort = "SELECT id, nama_cohort FROM cohort";
$result_cohort = $conn->query($sql_cohort);
$cohort_list = [];
if ($result_cohort->num_rows > 0) {
    while ($row_cohort = $result_cohort->fetch_assoc()) {
        $cohort_list[] = $row_cohort;
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
    <h2 class="page-title">Daftar Mahasiswa</h2>
    <form action="daftar_mahasiswa.php" method="POST">
        <label for="nama">Nama:</label>
        <input type="text" name="nama" id="nama" required>
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required>
        <label for="nim">NIM:</label>
        <input type="text" name="nim" id="nim" required>
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>
        <label for="semester">Semester:</label>
        <select name="semester" id="semester" required>
            <?php foreach ($semester_list as $semester): ?>
                <option value="<?php echo $semester['id']; ?>"><?php echo $semester['nama_semester']; ?></option>
            <?php endforeach; ?>
        </select>
        <label for="cohort">Cohort:</label>
        <select name="cohort" id="cohort" required>
            <?php foreach ($cohort_list as $cohort): ?>
                <option value="<?php echo $cohort['id']; ?>"><?php echo $cohort['nama_cohort']; ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="daftar_mahasiswa">Daftar</button>
    </form>

    <form action="daftar_mahasiswa.php" method="GET" class="search-form">
        <label for="search" class="sr-only">Cari Nama Mahasiswa:</label>
        <div class="search-container">
            <input type="text" name="search" id="search" placeholder="Cari Nama Mahasiswa" value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit"><i class="fas fa-search"></i></button>
        </div>
        <label for="sort_semester" class="sr-only">Urutkan berdasarkan Semester:</label>
        <div class="search-container">
            <select name="sort_semester" id="sort_semester" onchange="this.form.submit()">
                <option value="">Pilih Semester</option>
                <?php foreach ($semester_list as $semester): ?>
                    <option value="<?php echo $semester['nama_semester']; ?>" <?php if ($sort_semester == $semester['nama_semester']) echo 'selected'; ?>>
                        <?php echo $semester['nama_semester']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit"><i class="fas fa-sort"></i></button>
        </div>
    </form>

    <table class="data-table">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Username</th>
                <th>NIM</th>
                <th>Semester</th>
                <th>Cohort</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($mahasiswa_list as $mahasiswa): ?>
                <tr>
                    <td><?php echo $mahasiswa['nama']; ?></td>
                    <td><?php echo $mahasiswa['username']; ?></td>
                    <td><?php echo $mahasiswa['nim']; ?></td>
                    <td><?php echo $mahasiswa['nama_semester']; ?></td>
                    <td><?php echo $mahasiswa['nama_cohort']; ?></td>
                    <td class="action-buttons">
                        <a href="edit_mahasiswa.php?id=<?php echo $mahasiswa['id']; ?>" class="edit">Edit</a>
                        <a href="hapus_mahasiswa.php?id=<?php echo $mahasiswa['id']; ?>" class="delete" onclick="return confirm('Apakah Anda yakin ingin menghapus mahasiswa ini?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php include '../../includes/footer.php'; ?>
</body>
</html>