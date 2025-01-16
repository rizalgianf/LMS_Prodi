<?php
// filepath: /E:/GITHUB REPOSITORY/SIAKAD/views/admin/get_detail_absensi.php
include '../../config/database.php'; // Koneksi database

$pertemuan_id = $_GET['id'] ?? '';

if (empty($pertemuan_id)) {
    echo "Pertemuan ID tidak ditemukan.";
    exit();
}

// Ambil data detail absensi dari database
$sql_detail = "SELECT users.nama, absensi.status
               FROM absensi
               JOIN users ON absensi.mahasiswa_id = users.id
               WHERE absensi.pertemuan_id = ?";
$stmt_detail = $conn->prepare($sql_detail);
$stmt_detail->bind_param("i", $pertemuan_id);
$stmt_detail->execute();
$result_detail = $stmt_detail->get_result();
$detail_list = [];
if ($result_detail->num_rows > 0) {
    while ($row_detail = $result_detail->fetch_assoc()) {
        $detail_list[] = $row_detail;
    }
}
$stmt_detail->close();
$conn->close();
?>

<table class="data-table">
    <thead>
        <tr>
            <th>Nama Mahasiswa</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($detail_list as $detail): ?>
            <tr>
                <td><?php echo $detail['nama']; ?></td>
                <td><?php echo $detail['status']; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>