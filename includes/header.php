<!-- includes/header.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../../css/style_header.css">
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <img src="../../images/logo.png" alt="Logo SIAKAD" class="logo"> <!-- Menambahkan logo -->
            <h1 class="site-title">Learning Management System Admin </h1>
            <nav class="main-nav">
                <ul>
                    <li><a href="home.php">Home</a></li>
                    <li class="dropdown">
                        <a href="javascript:void(0)" class="dropbtn">Daftar</a>
                        <div class="dropdown-content">
                            <a href="daftar_mahasiswa.php">Daftar Mahasiswa</a>
                            <a href="daftar_dosen.php">Daftar Dosen</a>
                            <a href="daftar_matkul.php">Daftar Mata Kuliah</a>
                            <a href="daftar_cohort.php">Daftar Cohort</a>
                            <a href="rekap_absen.php">Daftar Rekapitulasi</a>
                        </div>
                    </li>
                    <li><a href="jadwal_kuliah.php">Jadwal Kuliah</a></li>
                    <li><a href="kbm.php">KBM</a></li>
                    <li><a href="../../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <div class="content-container">