<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../config/database.php'; // Koneksi database

$id = $_GET['id'] ?? '';
if (empty($id)) {
    header("Location: kelola_kelas.php");
    exit();
}

// Hapus pertemuan berdasarkan ID
$sql = "DELETE FROM pertemuan WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: kelola_kelas.php?id=" . $_GET['kelas_id']);
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>