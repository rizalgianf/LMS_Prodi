<?php
// filepath: /E:/GITHUB REPOSITORY/SIAKAD/views/admin/get_mata_kuliah.php
include '../../config/database.php'; // Koneksi database

$semester_id = $_GET['semester_id'] ?? '';

if ($semester_id) {
    $sql_mata_kuliah = "SELECT id, nama FROM mata_kuliah WHERE semester_id = ?";
    $stmt = $conn->prepare($sql_mata_kuliah);
    $stmt->bind_param("i", $semester_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $mata_kuliah_list = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $mata_kuliah_list[] = $row;
        }
    }
    $stmt->close();
    echo json_encode($mata_kuliah_list);
}

$conn->close();
?>