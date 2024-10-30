<!-- views/admin_dashboard.php -->
<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>Selamat datang, Admin <?php echo $_SESSION['username']; ?>!</h1>
    <p>Ini adalah halaman dashboard admin.</p>
    <a href="../logout.php">Logout</a>
</body>
</html>
