<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../config/database.php'; // Koneksi database

$page_title = "Admin Dashboard";
include '../../includes/header.php'; // Menggunakan header khusus untuk admin

// Ambil data Nama database
$username = $_SESSION['username'];
$sql_name = "SELECT nama FROM users WHERE username = '$username'";
$result_name = $conn->query($sql_name);
$name = '';
if ($result_name->num_rows > 0) {
    $name = $result_name->fetch_assoc()['nama'];
    // Batasi nama maksimal 5 kata
    $name_words = explode(' ', $name);
    if (count($name_words) > 5) {
        $name = implode(' ', array_slice($name_words, 0, 5));
    }
}

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

$sql_pertemuan = "SELECT COUNT(*) AS jumlah_pertemuan FROM pertemuan";
$result_pertemuan = $conn->query($sql_pertemuan);
$jumlah_pertemuan = $result_pertemuan->fetch_assoc()['jumlah_pertemuan'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title><?php echo $page_title; ?></title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="../../assets/img/favicon.png" rel="icon">
  <link href="../../assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900&family=Inter:wght@100;200;300;400;500;600;700;800;900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../../assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="../../assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="../../assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="../../assets/css/main.css" rel="stylesheet">

  <!-- =======================================================
  * Template Name: iLanding
  * Template URL: https://bootstrapmade.com/ilanding-bootstrap-landing-page-template/
  * Updated: Nov 12 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body class="index-page">

  <main class="main">

    <!-- Hero Section -->
    <section id="hero" class="hero section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row align-items-center">
          <div class="col-lg-6">
            <div class="hero-content" data-aos="fade-up" data-aos-delay="200">
                <div class="date-badge mb-4">
                <i class="bi bi-calendar-event-fill me-2"></i>
                <span id="current-date"></span>
                </div>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                  const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                  const today = new Date().toLocaleDateString('en-US', options);
                  document.getElementById('current-date').textContent = today;
                });
                </script>

              <h1 class="mb-4">
                Selamat Datang,<br>
                <?php echo htmlspecialchars($name); ?><br>
                LMS 
                <span class="accent-text">ClassTIcs</span>
              </h1>

              <p class="mb-4 mb-md-5">
              Program Studi Informatika adalah Program Studi yang mengembangkan disiplin Ilmu Komputer (Computer Science) sebagai ilmu teknik agar para 
              Kadet Mahasiswa lulusannya memiliki keahlian dan kemampuan untuk mengaplikasikan tiga bidang konsentrasi ilmu Informatika
              </p>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="hero-image" data-aos="zoom-out" data-aos-delay="300">
              <img src="../../assets/img/illustration-1.webp" alt="Hero Image" class="img-fluid">
            </div>
          </div>
        </div>

        <div class="row stats-row gy-4 mt-5" data-aos="fade-up" data-aos-delay="500">
          <div class="col-lg-3 col-md-6">
            <div class="stat-item">
              <div class="stat-icon">
              <i class="bi bi-person"></i>
              </div>
              <div class="stat-content">
                <h4><?php echo $jumlah_dosen; ?> Dosen</h4>
                <p class="mb-0">Jumlah Dosen</p>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="stat-item">
              <div class="stat-icon">
                <i class="bi bi-person"></i>
              </div>
              <div class="stat-content">
                <h4><?php echo $jumlah_mahasiswa; ?> Mahasiswa</h4>
                <p class="mb-0">Jumlah Mahasiswa</p>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="stat-item">
              <div class="stat-icon">
                <i class="bi bi-people"></i>
              </div>
              <div class="stat-content">
                <h4><?php echo $jumlah_cohort; ?> Cohort</h4>
                <p class="mb-0">Jumlah Cohort</p>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="stat-item">
              <div class="stat-icon">
                <i class="bi bi-book"></i>
              </div>
              <div class="stat-content">
                <h4><?php echo $jumlah_pertemuan; ?> Pertemuan</h4>
                <p class="mb-0">Jumlah Pertemuan</p>
              </div>
            </div>
          </div>
        </div>

      </div>
    </section><!-- /About Section -->

  </main>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../../assets/vendor/php-email-form/validate.js"></script>
  <script src="../../assets/vendor/aos/aos.js"></script>
  <script src="../../assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="../../assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="../../assets/vendor/purecounter/purecounter_vanilla.js"></script>

  <!-- Main JS File -->
  <script src="../../assets/js/main.js"></script>

  <?php include '../../includes/footer.php'; ?>
</body>
</html>