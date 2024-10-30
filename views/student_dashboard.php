<!-- views/student_dashboard.php -->
<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mahasiswa Dashboard</title>
</head>
<body>
    <h1>Selamat datang, Mahasiswa <?php echo $_SESSION['username']; ?>!</h1>
    <p>Ini adalah halaman dashboard mahasiswa.</p>
    <a href="../logout.php">Logout</a>
</body>
</html>

