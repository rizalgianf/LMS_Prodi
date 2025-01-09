<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../config/database.php'; // Koneksi database

$id = $_GET['id'] ?? '';
if (empty($id)) {
    header("Location: kbm.php");
    exit();
}

// Hapus entri terkait di tabel file_pertemuan
$sql_file_pertemuan = "DELETE file_pertemuan FROM file_pertemuan 
                       JOIN pertemuan ON file_pertemuan.pertemuan_id = pertemuan.id 
                       WHERE pertemuan.kelas_id = ?";
$stmt_file_pertemuan = $conn->prepare($sql_file_pertemuan);
$stmt_file_pertemuan->bind_param("i", $id);
$stmt_file_pertemuan->execute();
$stmt_file_pertemuan->close();

// Hapus entri terkait di tabel forum_diskusi
$sql_forum = "DELETE forum_diskusi FROM forum_diskusi 
              JOIN pertemuan ON forum_diskusi.pertemuan_id = pertemuan.id 
              WHERE pertemuan.kelas_id = ?";
$stmt_forum = $conn->prepare($sql_forum);
$stmt_forum->bind_param("i", $id);
$stmt_forum->execute();
$stmt_forum->close();

// Hapus entri terkait di tabel pertemuan
$sql_pertemuan = "DELETE FROM pertemuan WHERE kelas_id = ?";
$stmt_pertemuan = $conn->prepare($sql_pertemuan);
$stmt_pertemuan->bind_param("i", $id);
$stmt_pertemuan->execute();
$stmt_pertemuan->close();

// Hapus entri di tabel kelas
$sql_kelas = "DELETE FROM kelas WHERE id = ?";
$stmt_kelas = $conn->prepare($sql_kelas);
$stmt_kelas->bind_param("i", $id);

if ($stmt_kelas->execute()) {
    header("Location: kbm.php");
} else {
    echo "Error: " . $stmt_kelas->error;
}

$stmt_kelas->close();
$conn->close();
?>