<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../config/database.php'; // Koneksi database

$id = $_POST['id'] ?? '';
$mata_kuliah = $_POST['mata_kuliah'];
$dosen = $_POST['dosen'];
$hari = $_POST['hari'];
$tanggal = $_POST['tanggal'];
$waktu_mulai = $_POST['waktu_mulai'];
$waktu_selesai = $_POST['waktu_selesai'];

if (empty($id)) {
    // Insert data baru
    $sql = "INSERT INTO jadwal_kuliah (mata_kuliah, dosen, hari, tanggal, waktu_mulai, waktu_selesai) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $mata_kuliah, $dosen, $hari, $tanggal, $waktu_mulai, $waktu_selesai);
} else {
    // Update data yang sudah ada
    $sql = "UPDATE jadwal_kuliah SET mata_kuliah=?, dosen=?, hari=?, tanggal=?, waktu_mulai=?, waktu_selesai=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $mata_kuliah, $dosen, $hari, $tanggal, $waktu_mulai, $waktu_selesai, $id);
}

if ($stmt->execute()) {
    header("Location: jadwal_kuliah.php");
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>