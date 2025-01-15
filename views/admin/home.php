<?php
// Mulai session dan pastikan pengguna telah login sebagai admin
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}
// Mengatur judul halaman
$page_title = "Admin Dashboard";
include '../../includes/header.php'; // Menggunakan header khusus untuk admin
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    
    <!-- Title Tag -->
    <title>Learning Management System Informatika Unhan RI</title>
<!--

November Template

http://www.templatemo.com/tm-473-november

-->
    <!-- <<Mobile Viewport Code>> -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
            
    <!-- <<Attched Stylesheets>> -->
    <link rel="stylesheet" href="../../landing_page/css/theme.css" type="text/css" />
    <link rel="stylesheet" href="../../landing_page/css/media.css" type="text/css" />
    <link rel="stylesheet" href="../../landing_page/css/font-awesome.min.css" type="text/css" />
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,600italic,400italic,800,700' rel='stylesheet' type='text/css'>    
    <link href='https://fonts.googleapis.com/css?family=Oswald:400,700,300' rel='stylesheet' type='text/css'>

</head>


    <body>

    <!-- \\ Begin Holder \\ -->
    <div class="DesignHolder">
        <!-- \\ Begin Frame \\ -->
        <div class="LayoutFrame">
            <!-- \\ Begin Header \\ -->
        
            <!-- // End Header // -->
            <!-- \\ Begin Banner Section \\ -->
            <div class="Banner_sec" id="home">
                <!--  \\ Begin banner Side -->
                <div class="bannerside">
                    <div class="Center">
                        <!--  \\ Begin Left Side -->
                        <div class="leftside">
                            <h3>Selamat Datang <span>Class Plus</span></h3>
                            <p>Learning Management System Informatika Unhan RI </p>
                            <a href="#about">MORE DETAILS</a>
                        </div>                        								
                        <!--  // End Left Side // -->
                    <!--  \\ Begin Right Side -->
                    <div class="rightside">
                            <ul id="slider">	
                                <li>
                                    <div class="Slider">
                                        <figure><img src="../../images/home.JPG" alt="image"></figure>
                                        <div class="text">           								
                                            <div class="Lorem">
                                                <p>Fakultas Sains dan Teknologi Pertahanan<span>Prodi Informatika</span></p>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>                                                                                                                  
                        </div>
                        <!--  // End Right Side // -->
                    
                    </div>
                </div>
                <!--  // End banner Side // -->
                <div class="clear"></div>
            </div>
            <!-- // End Banner Section // -->
            <div class="bgcolor"></div>
            <!-- \\ Begin Container \\ -->
            <div id="Container">
                <!-- \\ Begin About Section \\ -->
                <div class="About_sec" id="about">
                    <div class="Center">            	
                        <h2>about us</h2>            		
                        <p>Program Studi Informatika adalah Program Studi yang mengembangkan disiplin Ilmu Komputer (Computer Science) sebagai ilmu teknik agar para Kadet Mahasiswa lulusannya memiliki keahlian dan kemampuan untuk mengaplikasikan tiga bidang konsentrasi ilmu Informatika</p>
                        <div class="Line"></div>	
                        <!-- \\ Begin Tab side \\ -->
                        <div class="Tabside">
                            
                        <div class="clear"></div>
                            <div class="tabcontent" id="cont-1-1">
                                <div class="TabImage">
                                    <div class="img1">
                                        <figure><img src="../../images/about1.JPG" alt="image"></figure>	
                                    </div>
                                    <div class="img2">
                                        <figure><img src="../../images/about2.JPG" alt="image"></figure>
                                    </div>
                                </div>
                                <div class="Description">
                                    <h3>Fakultas Sains dan Teknologi Pertahanan<span>Program Studi Informatika</span></h3>
                                    <p>Ketiga konsentrasi ilmu Informatika tersebut difokuskan untuk mendukung Kepentingan Militer / Pertahanan sebagai ciri khas Universitas Pertahanan RI. Diharapkan para lulusan Prodi Informatika dapat segera terserap bekerja mengisi kebutuhan TNI / Kemhan / Kementerian Lembaga dan stakeholders terkait (link & match) akan kebutuhan Sumber Daya Manusia bidang TIK (Teknologi Informasi & Komunikasi), Artificilal Intelligence, Cyber Security dan Cyber Defense. Sistem SKS dalam Kurikulum Program Studi Informatika memiliki bobot 20% dari total 144 SKS yang konten mata kuliahnya didisain untuk mendukung kepentingan Militer / Pertahanan.</p>
                                    <p>Di dalam melaksanakan sistim perkuliahannya, Program Studi Informatika mengadopsi konsep Belajar Merdeka (Kampus Merdeka) dengan komposisi 3 Semester (60 SKS) perkuliahan bisa dilakukan diluar Program Studi atau diluar Kampus Unhan RI termasuk melakukan OJT (On the Job Training), dan KKN (Kuliah Kerja Nyata). Para Kadet Mahasiswa lulusan Program Studi Informatika merupakan Kader Intelektual Bela Negara yang nantinya siap ditempatkan di seluruh penjuru Nusantara dalam rangka mengemban misi Pertahanan Negara.</p>
                                </div>
                            </div>
                            </div>
                            <div class="clear"></div>	
                        </div>                    
                        <!-- // End Tab Side // -->
                    </div>
                </div>
                <!-- // End About Section // -->

    
    </body>
    <?php include '../../includes/footer.php'; ?>
</html>

