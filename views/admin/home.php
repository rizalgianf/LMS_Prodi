<?php
// filepath: /E:/GITHUB REPOSITORY/SIAKAD/views/admin/home.php
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
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #e9ecef;
        }
        .carousel-item {
            height: 400px;
            background: no-repeat center center scroll;
            background-size: cover;
        }
        .card {
            margin: 20px;
            border-radius: 10px;
            transition: transform 0.2s;
        }
        .card:hover {
            transform: scale(1.05);
        }
        .card-title {
            font-size: 2rem;
        }
        .card-header {
            font-size: 1.2rem;
            font-weight: bold;
        }
        .dashboard-header {
            margin-bottom: 30px;
            color: #007bff;
        }
        .icon {
            font-size: 3rem;
            margin-right: 10px;
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

    <h2 class="text-center my-4 dashboard-header">Admin Dashboard</h2>
    <div class="row">
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-header"><i class="fas fa-chalkboard-teacher icon"></i>Jumlah Dosen</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $jumlah_dosen; ?></h5>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-header"><i class="fas fa-user-graduate icon"></i>Jumlah Mahasiswa</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $jumlah_mahasiswa; ?></h5>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-info mb-3">
                <div class="card-header"><i class="fas fa-users icon"></i>Jumlah Cohort</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $jumlah_cohort; ?></h5>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-warning mb-3">
                <div class="card-header"><i class="fas fa-book icon"></i>Jumlah Mata Kuliah</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $jumlah_mata_kuliah; ?></h5>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-danger mb-3">
                <div class="card-header"><i class="fas fa-calendar-alt icon"></i>Jumlah Pertemuan</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $jumlah_pertemuan; ?></h5>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> ```php
<?php
