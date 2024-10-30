<!-- views/lecturer_dashboard.php -->
<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'dosen') {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dosen Dashboard</title>
</head>
<body>
    <h1>Selamat datang, Dosen <?php echo $_SESSION['username']; ?>!</h1>
    <p>Ini adalah halaman dashboard dosen.</p>
    <a href="../logout.php">Logout</a>
</body>
</html>
