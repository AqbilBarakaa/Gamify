<?php
session_start();
include('../db.php'); // Koneksi ke database

// Pastikan user login
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

$user = $_SESSION["user"];
$kue_user = mysqli_query($kon, "SELECT * FROM user WHERE nama = '$user'");
$row_user = mysqli_fetch_array($kue_user);
$userId = $row_user['id_user'];

// Ambil ID pesanan dari parameter URL
$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Validasi ID pesanan
if ($orderId <= 0) {
    die("ID pesanan tidak valid.");
}

// Ambil data pengiriman dari database
$sql = "SELECT pg.nomor_resi, pg.nama_kurir, pg.alamat_pengiriman, 
        pg.tanggal_kirim, pg.perkiraan_tiba, pg.tanggal_tiba, 
        pg.status_pengiriman, pg.biaya_kirim
        FROM pengiriman_pesanan pg
        JOIN pesanan p ON pg.id_pesanan = p.id_pesanan
        WHERE pg.id_pesanan = ? AND pg.id_user = ?";
$stmt = mysqli_prepare($kon, $sql);

if (!$stmt) {
    error_log("Prepare statement gagal: " . mysqli_error($kon));
    die("Terjadi kesalahan sistem.");
}

mysqli_stmt_bind_param($stmt, "ii", $orderId, $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$shippingData = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

// Pastikan $shippingData tidak null
if (!$shippingData) {
    die("Data pengiriman tidak ditemukan.");
}

// Tentukan progress berdasarkan status pengiriman
$statusMap = [
    'dalam_pengiriman' => 2,
    'sudah_sampai' => 3,
    'terlambat' => 3,
];
$currentStep = isset($statusMap[$shippingData['status_pengiriman']]) ? $statusMap[$shippingData['status_pengiriman']] : 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Detail Pengiriman</title>
    
    <!-- Favicons -->
    <link href="../assets/img/favicon.png" rel="icon">
    <link href="../assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="../assets/vendor/quill/quill.snow.css" rel="stylesheet">
    <link href="../assets/vendor/quill/quill.bubble.css" rel="stylesheet">
    <link href="../assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="../assets/vendor/simple-datatables/style.css" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        /* Timeline Styling */
        .timeline {
            position: relative;
            margin: 20px 0;
            padding: 0;
            list-style: none;
        }

        .timeline::before {
            content: '';
            position: absolute;
            top: 0;
            left: 20px;
            bottom: 0;
            width: 4px;
            background: #ddd;
        }

        .timeline-step {
            position: relative;
            margin: 0 0 20px 50px;
            padding: 0 0 0 20px;
        }

        .timeline-step.active .timeline-icon {
            background: #007bff;
            color: #fff;
        }

        .timeline-step .timeline-icon {
            position: absolute;
            top: 0;
            left: -30px;
            width: 30px;
            height: 30px;
            line-height: 30px;
            text-align: center;
            border-radius: 50%;
            background: #ddd;
            color: #fff;
        }

        .timeline-step .timeline-content {
            font-size: 14px;
            color: #555;
        }

        .timeline-step .timeline-content strong {
            font-size: 16px;
            color: #333;
        }

        h2 {
            font-size: 32px; /* Ukuran font untuk h2 yang lebih besar */
            margin-bottom: 20px; /* Jarak bawah untuk h2 */
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php require "atas.php"; ?>
    <!-- Sidebar -->
    <?php require "menu.php"; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1><i class="bi bi-truck"></i> Lacak Pesanan</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Lacak Pesanan</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="card-title">Timeline Pengiriman</h2>
                            <ul class="timeline">
                                <li class="timeline-step <?= $currentStep >= 1 ? 'active' : ''; ?>">
                                    <div class="timeline-icon"><i class="bi bi-box"></i></div>
                                    <div class="timeline-content">
                                        <strong>Dipesan</strong>
                                        <p><?= isset($shippingData['tanggal_kirim']) ? date('d/m/Y H:i', strtotime($shippingData['tanggal_kirim'])) : 'Tidak tersedia'; ?></p>
                                    </div>
                                </li>
                                <li class="timeline-step <?= $currentStep >= 2 ? 'active' : ''; ?>">
                                    <div class="timeline-icon"><i class="bi bi-truck"></i></div>
                                    <div class="timeline-content">
                                        <strong>Dalam Pengiriman</strong>
                                        <p><?= $currentStep >= 2 ? 'Sedang dalam perjalanan' : 'Belum dalam pengiriman'; ?></p>
                                    </div>
                                </li>
                                <li class="timeline-step <?= $currentStep >= 3 ? 'active' : ''; ?>">
                                    <div class="timeline-icon"><i class="bi bi-house-door"></i></div>
                                    <div class="timeline-content">
                                        <strong><?= isset($shippingData['status_pengiriman']) && $shippingData['status_pengiriman'] === 'terlambat' ? 'Terlambat' : 'Tiba di Tujuan'; ?></strong>
                                        <p><?= isset($shippingData['tanggal_tiba']) ? ($shippingData['tanggal_tiba'] ? date('d/m/Y H:i', strtotime($shippingData['tanggal_tiba'])) : 'Belum tiba') : 'Tidak tersedia'; ?></p>
                                    </div>
                                </li>
                            </ul>
                            <h2 class="card-title mt-4">Detail Pengiriman</h2>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Nomor Resi</th>
                                    <td><?= isset($shippingData['nomor_resi']) ? htmlspecialchars($shippingData['nomor_resi']) : 'Tidak tersedia'; ?></td>
                                </tr>
                                <tr>
                                    <th>Nama Kurir</th>
                                    <td><?= isset($shippingData['nama_kurir']) ? htmlspecialchars($shippingData['nama_kurir']) : 'Tidak tersedia'; ?></td>
                                </tr>
                                <tr>
                                    <th>Alamat Pengiriman</th>
                                    <td><?= isset($shippingData['alamat_pengiriman']) ? htmlspecialchars($shippingData['alamat_pengiriman']) : 'Tidak tersedia'; ?></td>
                                </tr>
                                <tr>
                                    <th>Estimasi Waktu Tiba</th>
                                    <td><?= isset($shippingData['perkiraan_tiba']) ? ($shippingData['perkiraan_tiba'] ? date('d/m/Y H:i', strtotime($shippingData['perkiraan_tiba'])) : 'Tidak tersedia') : 'Tidak tersedia'; ?></td>
                                </tr>
                                <tr>
                                    <th>Biaya Kirim</th>
                                    <td><?= isset($shippingData['biaya_kirim']) ? 'Rp ' . number_format($shippingData['biaya_kirim'], 0, ',', '.') : 'Tidak tersedia'; ?></td>
                                </tr>
                            </table>
                            <a href="history_pembayaran.php" class="btn btn-secondary mt-3">Kembali</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <!-- Vendor JS Files -->
    <script src="../assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/vendor/chart.js/chart.min.js"></script>
    <script src="../assets/vendor/echarts/echarts.min.js"></script>
    <script src="../assets/vendor/quill/quill.min.js"></script>
    <script src="../assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="../assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="../assets/vendor/php-email-form/validate.js"></script>

    <!-- Template Main JS File -->
    <script src="../assets/js/main.js"></script>
</body>
</html>
