<?php
// filepath: /E:/GITHUB REPOSITORY/SIAKAD/views/admin/rekap_absen.php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../config/database.php'; // Koneksi database

$page_title = "Rekapitulasi Absensi";
include '../../includes/header.php'; // Ensure this path is correct

// Ambil data cohort dan semester dari database
$sql_cohort = "SELECT id, nama_cohort FROM cohort";
$result_cohort = $conn->query($sql_cohort);
$cohort_list = [];
if ($result_cohort->num_rows > 0) {
    while ($row_cohort = $result_cohort->fetch_assoc()) {
        $cohort_list[] = $row_cohort;
    }
}

$sql_semester = "SELECT id, nama_semester FROM semester";
$result_semester = $conn->query($sql_semester);
$semester_list = [];
if ($result_semester->num_rows > 0) {
    while ($row_semester = $result_semester->fetch_assoc()) {
        $semester_list[] = $row_semester;
    }
}

// Ambil data rekap absensi dari database
$sort_cohort = $_GET['sort_cohort'] ?? '';
$sort_semester = $_GET['sort_semester'] ?? '';
$sort_mata_kuliah = $_GET['sort_mata_kuliah'] ?? '';

$sql_rekap = "SELECT pertemuan.id, pertemuan.tanggal, pertemuan.topik, cohort.nama_cohort, mata_kuliah.nama AS mata_kuliah, users.nama AS dosen, semester.nama_semester,
              COUNT(absensi.id) AS total_mahasiswa,
              COUNT(CASE WHEN absensi.status = 'Hadir' THEN 1 END) AS jumlah_hadir,
              COUNT(CASE WHEN absensi.status != 'Hadir' OR absensi.status IS NULL THEN 1 END) AS jumlah_tidak_hadir
              FROM pertemuan
              LEFT JOIN absensi ON pertemuan.id = absensi.pertemuan_id
              JOIN kelas ON pertemuan.kelas_id = kelas.id
              JOIN cohort ON kelas.id_cohort = cohort.id
              JOIN mata_kuliah ON kelas.mata_kuliah_id = mata_kuliah.id
              JOIN users ON kelas.dosen_id = users.id
              JOIN semester ON mata_kuliah.semester_id = semester.id";

$conditions = [];
$params = [];
$types = '';

if ($sort_cohort) {
    $conditions[] = "cohort.id = ?";
    $params[] = $sort_cohort;
    $types .= 'i';
}

if ($sort_semester) {
    $conditions[] = "semester.id = ?";
    $params[] = $sort_semester;
    $types .= 'i';
}

if ($sort_mata_kuliah) {
    $conditions[] = "mata_kuliah.id = ?";
    $params[] = $sort_mata_kuliah;
    $types .= 'i';
}

if ($conditions) {
    $sql_rekap .= " WHERE " . implode(" AND ", $conditions);
}

$sql_rekap .= " GROUP BY pertemuan.id, pertemuan.tanggal, pertemuan.topik, cohort.nama_cohort, mata_kuliah.nama, users.nama, semester.nama_semester
                ORDER BY cohort.nama_cohort ASC, semester.nama_semester ASC, mata_kuliah.nama ASC, pertemuan.tanggal ASC";

$stmt_rekap = $conn->prepare($sql_rekap);
if ($params) {
    $stmt_rekap->bind_param($types, ...$params);
}
$stmt_rekap->execute();
$result_rekap = $stmt_rekap->get_result();
$rekap_list = [];
if ($result_rekap->num_rows > 0) {
    while ($row_rekap = $result_rekap->fetch_assoc()) {
        $rekap_list[] = $row_rekap;
    }
}

$stmt_rekap->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../../css/style_kbm.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 8px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .detail-btn {
            background-color: #FFD700; /* Yellow color */
            color:rgb(0, 0, 0);
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .detail-btn:hover {
            background-color: #FFC107; /* Darker yellow on hover */
        }
    </style>
</head>
<body>
<main class="main-content">
    <h2 class="page-title">Rekapitulasi Absensi</h2>
    <form action="rekap_absen.php" method="GET" class="search-form">
        <div class="search-container">
            <select name="sort_cohort" id="sort_cohort" onchange="this.form.submit()">
                <option value="">Pilih Cohort</option>
                <?php foreach ($cohort_list as $cohort): ?>
                    <option value="<?php echo $cohort['id']; ?>" <?php if ($sort_cohort == $cohort['id']) echo 'selected'; ?>>
                        <?php echo $cohort['nama_cohort']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="sort_semester" id="sort_semester" onchange="updateMataKuliahOptions(); this.form.submit();">
                <option value="">Pilih Semester</option>
                <?php foreach ($semester_list as $semester): ?>
                    <option value="<?php echo $semester['id']; ?>" <?php if ($sort_semester == $semester['id']) echo 'selected'; ?>>
                        <?php echo $semester['nama_semester']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="sort_mata_kuliah" id="sort_mata_kuliah" onchange="this.form.submit()">
                <option value="">Pilih Mata Kuliah</option>
                <!-- Mata kuliah options will be populated by JavaScript -->
            </select>
            <button type="submit"><i class="fas fa-search"></i></button>
        </div>
    </form>
    <table class="data-table">
        <thead>
            <tr>
                <th>Cohort</th>
                <th>Mata Kuliah</th>
                <th>Dosen</th>
                <th>Semester</th>
                <th>Tanggal</th>
                <th>Topik</th>
                <th>Total Mahasiswa</th>
                <th>Jumlah Hadir</th>
                <th>Jumlah Tidak Hadir</th>
                <th>Persentase Kehadiran</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rekap_list as $rekap): ?>
                <?php $persentase_kehadiran = $rekap['total_mahasiswa'] > 0 ? round(($rekap['jumlah_hadir'] / $rekap['total_mahasiswa']) * 100, 2) : 0; ?>
                <tr>
                    <td><?php echo $rekap['nama_cohort']; ?></td>
                    <td><?php echo $rekap['mata_kuliah']; ?></td>
                    <td><?php echo $rekap['dosen']; ?></td>
                    <td><?php echo $rekap['nama_semester']; ?></td>
                    <td><?php echo $rekap['tanggal']; ?></td>
                    <td><?php echo $rekap['topik']; ?></td>
                    <td><?php echo $persentase_kehadiran > 0 ? $rekap['total_mahasiswa'] : '-'; ?></td>
                    <td><?php echo $persentase_kehadiran > 0 ? $rekap['jumlah_hadir'] : '-'; ?></td>
                    <td><?php echo $persentase_kehadiran > 0 ? $rekap['jumlah_tidak_hadir'] : '-'; ?></td>
                    <td><?php echo $persentase_kehadiran > 0 ? $persentase_kehadiran . '%' : '0%'; ?></td>
                    <td><?php echo $persentase_kehadiran > 0 ? '<button class="detail-btn" data-id="' . $rekap['id'] . '">Detail</button>' : '-'; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<!-- Modal -->
<div id="detailModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Detail Absensi</h3>
        <div id="modal-body">
            <!-- Detail content will be loaded here -->
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
    // Get the modal
    var modal = document.getElementById("detailModal");

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];

    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        modal.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    // Get all detail buttons
    var detailButtons = document.getElementsByClassName("detail-btn");

    // Add click event to each detail button
    for (var i = 0; i < detailButtons.length; i++) {
        detailButtons[i].onclick = function() {
            var pertemuanId = this.getAttribute("data-id");
            // Fetch detail data using AJAX
            fetch('get_detail_absensi.php?id=' + pertemuanId)
                .then(response => response.text())
                .then(data => {
                    document.getElementById("modal-body").innerHTML = data;
                    modal.style.display = "block";
                });
        }
    }

    // Function to update Mata Kuliah options based on selected Semester
    function updateMataKuliahOptions() {
        var semesterId = document.getElementById("sort_semester").value;
        var mataKuliahSelect = document.getElementById("sort_mata_kuliah");

        // Clear existing options
        mataKuliahSelect.innerHTML = '<option value="">Pilih Mata Kuliah</option>';

        if (semesterId) {
            fetch('get_mata_kuliah.php?semester_id=' + semesterId)
                .then(response => response.json())
                .then(data => {
                    data.forEach(function(mataKuliah) {
                        var option = document.createElement("option");
                        option.value = mataKuliah.id;
                        option.text = mataKuliah.nama;
                        mataKuliahSelect.add(option);
                    });
                });
        }
    }

    // Initialize Mata Kuliah options if a Semester is already selected
    if (document.getElementById("sort_semester").value) {
        updateMataKuliahOptions();
    }
</script>
</body>
<form action="export_csv.php" method="POST">
    <input type="hidden" name="sort_cohort" value="<?php echo $sort_cohort; ?>">
    <input type="hidden" name="sort_semester" value="<?php echo $sort_semester; ?>">
    <input type="hidden" name="sort_mata_kuliah" value="<?php echo $sort_mata_kuliah; ?>">
    <button type="submit" name="export_csv">Export to CSV</button>
</form>
</html>