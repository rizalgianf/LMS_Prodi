<!-- views/admin/daftar_mahasiswa.php -->
<?php
// Mulai session dan pastikan pengguna telah login sebagai admin
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

// Mengatur judul halaman
$page_title = "Daftar Mahasiswa";
include '../../includes/header_admin.php'; // Menggunakan header khusus untuk admin

// Ambil data mahasiswa dari database (sementara menggunakan data dummy)
// Simulasikan data dengan array asosiatif sementara
$mahasiswa = [
    ["id" => 1, "nama" => "Andi", "nilai" => 85],
    ["id" => 2, "nama" => "Budi", "nilai" => 90],
    ["id" => 3, "nama" => "Citra", "nilai" => 88],
];

?>

<main class="main-content">
    <h2 class="page-title">Daftar Mahasiswa</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Nilai</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($mahasiswa as $mhs): ?>
                <tr>
                    <td><?php echo $mhs['id']; ?></td>
                    <td><?php echo $mhs['nama']; ?></td>
                    <td><?php echo $mhs['nilai']; ?></td>
                    <td><a href="edit_mahasiswa.php?id=<?php echo $mhs['id']; ?>">Edit Nilai</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php include '../../includes/footer.php'; ?>

