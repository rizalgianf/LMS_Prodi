<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../config/database.php'; // Koneksi database

$page_title = "Admin Dashboard";
include '../../includes/header.php'; // Menggunakan header khusus untuk admin

// Ambil data dari database
$sql_dosen = "SELECT COUNT(*) AS jumlah_dosen FROM users WHERE role = 'dosen'";
$result_dosen = $conn->query($sql_dosen);
$jumlah_dosen = $result_dosen->fetch_assoc()['jumlah_dosen'];

$sql_mahasiswa = "SELECT COUNT(*) AS jumlah_mahasiswa FROM users WHERE role = 'mahasiswa'";
$result_mahasiswa = $conn->query($sql_mahasiswa);
$jumlah_mahasiswa = $result_mahasiswa->fetch_assoc()['jumlah_mahasiswa'];

$sql_cohort = "SELECT COUNT(*) AS jumlah_cohort FROM cohort";
$result_cohort = $conn->query($sql_cohort);
$jumlah_cohort = $result_cohort->fetch_assoc()['jumlah_cohort'];

$sql_mata_kuliah = "SELECT COUNT(*) AS jumlah_mata_kuliah FROM mata_kuliah";
$result_mata_kuliah = $conn->query($sql_mata_kuliah);
$jumlah_mata_kuliah = $result_mata_kuliah->fetch_assoc()['jumlah_mata_kuliah'];

$sql_pertemuan = "SELECT COUNT(*) AS jumlah_pertemuan FROM pertemuan";
$result_pertemuan = $conn->query($sql_pertemuan);
$jumlah_pertemuan = $result_pertemuan->fetch_assoc()['jumlah_pertemuan'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Roboto', sans-serif;
        }
        .carousel-item {
            height: 400px;
            background-size: cover;
            background-position: center;
            position: relative;
        }
        .carousel-item::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }
        .carousel-caption {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: #fff;
        }
        .card {
            margin: 10px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }
        .card-header {
            font-size: 1.4rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        .card-title {
            font-size: 2.5rem;
            font-weight: bold;
        }
        .dashboard-header {
            margin: 50px 0;
            color: #007bff;
            font-size: 2.5rem;
            font-weight: bold;
            text-align: center;
        }
        .icon {
            font-size: 3rem;
            margin-right: 15px;
        }
        footer {
            margin-top: 50px;
            text-align: center;
            font-size: 0.9rem;
            color: #6c757d;
        }
        .card-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
        }
        main.container {
            max-width: 1200px;
        }
    </style>
</head>
<body>

<main class="container">
    <!-- Slider -->
    <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active" style="background-image: url('../../images/about2.JPG');">
                <div class="carousel-caption d-none d-md-block">
                    <h5>Selamat Datang di Dashboard Admin</h5>
                    <p>Kelola semua data dengan mudah dan cepat.</p>
                </div>
            </div>
            <div class="carousel-item" style="background-image: url('../../images/about1.JPG');">
                <div class="carousel-caption d-none d-md-block">
                    <h5>Data Terintegrasi</h5>
                    <p>Semua data terintegrasi dalam satu platform.</p>
                </div>
            </div>
            <div class="carousel-item" style="background-image: url('https://source.unsplash.com/1600x400/?data,analytics');">
                <div class="carousel-caption d-none d-md-block">
                    <h5>Analisis Data yang Mudah</h5>
                    <p>Analisis data dengan cepat dan efisien.</p>
                </div>
            </div>
        </div>
        <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>

    
    <div class="card-container">
        <div class="card text-white bg-primary">
            <div class="card-header"><i class="fas fa-chalkboard-teacher icon"></i>Jumlah Dosen</div>
            <div class="card-body">
                <h5 class="card-title text-center"><?php echo $jumlah_dosen; ?></h5>
            </div>
        </div>
        <div class="card text-white bg-success">
            <div class="card-header"><i class="fas fa-user-graduate icon"></i>Jumlah Mahasiswa</div>
            <div class="card-body">
                <h5 class="card-title text-center"><?php echo $jumlah_mahasiswa; ?></h5>
            </div>
        </div>
        <div class="card text-white bg-info">
            <div class="card-header"><i class="fas fa-users icon"></i>Jumlah Cohort</div>
            <div class="card-body">
                <h5 class="card-title text-center"><?php echo $jumlah_cohort; ?></h5>
            </div>
        </div>
        <div class="card text-white bg-warning">
            <div class="card-header"><i class="fas fa-book icon"></i>Jumlah Mata Kuliah</div>
            <div class="card-body">
                <h5 class="card-title text-center"><?php echo $jumlah_mata_kuliah; ?></h5>
            </div>
        </div>
        <div class="card text-white bg-danger">
            <div class="card-header"><i class="fas fa-calendar-alt icon"></i>Jumlah Pertemuan</div>
            <div class="card-body">
                <h5 class="card-title text-center"><?php echo $jumlah_pertemuan; ?></h5>
            </div>
        </div>
    </div>
</main>

<footer>
    &copy; 2025 Sistem Informasi Akademik. All rights reserved.
</footer>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
