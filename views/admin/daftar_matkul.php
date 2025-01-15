<?php
// Mulai session dan pastikan pengguna telah login sebagai admin
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../config/database.php'; // Koneksi database

// Mengatur judul halaman
$page_title = "Daftar Mata Kuliah";
include '../../includes/header.php'; // Menggunakan header khusus untuk admin

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Mengambil data dari form
    $nama_matkul = $_POST['nama_matkul'];
    $semester_id = $_POST['semester'];

    // Menyimpan nama mata kuliah dan semester_id ke database
    $sql = "INSERT INTO mata_kuliah (nama, semester_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $nama_matkul, $semester_id);

    if ($stmt->execute()) {
        echo "Mata kuliah berhasil ditambahkan!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Ambil data semester dari database
$sql_semester = "SELECT id, nama_semester FROM semester";
$result_semester = $conn->query($sql_semester);
$semester_list = [];
if ($result_semester->num_rows > 0) {
    while ($row_semester = $result_semester->fetch_assoc()) {
        $semester_list[] = $row_semester;
    }
}

// Ambil data mata kuliah dari database
$search = $_GET['search'] ?? '';
$sort_semester = $_GET['sort_semester'] ?? '';
$sql_matkul = "SELECT mk.id, mk.nama, s.nama_semester 
               FROM mata_kuliah mk 
               JOIN semester s ON mk.semester_id = s.id 
               WHERE mk.nama LIKE ?";
if ($sort_semester) {
    $sql_matkul .= " AND s.nama_semester = ?";
}
$sql_matkul .= " ORDER BY s.nama_semester ASC";
$stmt = $conn->prepare($sql_matkul);
$search_param = "%$search%";
if ($sort_semester) {
    $stmt->bind_param("ss", $search_param, $sort_semester);
} else {
    $stmt->bind_param("s", $search_param);
}
$stmt->execute();
$result_matkul = $stmt->get_result();
$matkul_list = [];
if ($result_matkul->num_rows > 0) {
    while ($row_matkul = $result_matkul->fetch_assoc()) {
        $matkul_list[] = $row_matkul;
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
    <h2 class="page-title">Input Mata Kuliah</h2>
    <form action="daftar_matkul.php" method="POST">
        <label for="nama_matkul">Nama Mata Kuliah:</label>
        <input type="text" name="nama_matkul" id="nama_matkul" required>
        <label for="semester">Semester:</label>
        <select name="semester" id="semester" required>
            <?php foreach ($semester_list as $semester): ?>
                <option value="<?php echo $semester['id']; ?>"><?php echo $semester['nama_semester']; ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Tambah</button>
    </form>

    <h2 class="page-title">Daftar Mata Kuliah</h2>
    <form action="daftar_matkul.php" method="GET" class="search-form">
        <label for="search" class="sr-only">Cari Nama Mata Kuliah:</label>
        <div class="search-container">
            <input type="text" name="search" id="search" placeholder="Cari Nama Mata Kuliah" value="<?php echo htmlspecialchars($search); ?>">
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
                <th>Nama Mata Kuliah</th>
                <th>Semester</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($matkul_list as $matkul): ?>
                <tr>
                    <td><?php echo $matkul['nama']; ?></td>
                    <td><?php echo $matkul['nama_semester']; ?></td>
                    <td>
                        <a class="edit" href="edit_matkul.php?id=<?php echo $matkul['id']; ?>">Edit</a>
                        <a class="delete" href="hapus_matkul.php?id=<?php echo $matkul['id']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus mata kuliah ini?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php include '../../includes/footer.php'; ?>
</body>
</html>