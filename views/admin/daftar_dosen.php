<!-- views/admin/daftar_dosen.php -->
<?php
// Mulai session dan pastikan pengguna telah login sebagai admin
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

// Mengatur judul halaman
$page_title = "Daftar Dosen";
include '../../includes/header_admin.php'; // Menggunakan header khusus untuk admin

// Ambil data dosen dari database (sementara menggunakan data dummy)
// Simulasikan data dengan array asosiatif sementara
$dosen = [
    ["id" => 1, "nama" => "Dr. Yanto", "departemen" => "Informatika"],
    ["id" => 2, "nama" => "Prof. Surya", "departemen" => "Matematika"],
    ["id" => 3, "nama" => "Dr. Rina", "departemen" => "Fisika"],
];

?>

<main class="main-content">
    <h2 class="page-title">Daftar Dosen</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Departemen</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dosen as $dsn): ?>
                <tr>
                    <td><?php echo $dsn['id']; ?></td>
                    <td><?php echo $dsn['nama']; ?></td>
                    <td><?php echo $dsn['departemen']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php include '../../includes/footer.php'; ?>
