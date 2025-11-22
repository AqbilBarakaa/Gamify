<?php
// Koneksi ke database
require_once '../db.php';
session_start();
$page = "pesanan_dikirim";

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

// Ambil filter status pesanan (default: hanya 'Dikirim')
$status_filter = isset($_GET['filter']) ? $_GET['filter'] : ['Dikirim'];

// Pastikan `$status_filter` adalah array
if (!is_array($status_filter)) {
    $status_filter = [$status_filter];
}

// Query untuk mengambil data pesanan dengan status "Dikirim"
$query = "SELECT p.*, pr.nama_produk, pr.harga, pr.gambar
          FROM pesanan p
          JOIN produk pr ON p.id_produk = pr.id_produk
          WHERE p.status_pesanan = 'Dikirim'";

$stmt = $kon->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
$pesanan = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Dikirim</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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

    <style>
        body {
            background-color: #f5f5f5;
        }
        .pesanan-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .pesanan-card img {
            max-width: 100px;
            border-radius: 8px;
        }
        .btn-orange {
            background-color: orange;
            color: white;
        }
    </style>
    <?php include 'aset.php'; ?>
</head>
<body>

<!-- Header -->
<?php require "atas.php"; ?>
<!-- End Header -->

<!-- Sidebar -->
<?php require "menu.php"; ?>
<!-- End Sidebar -->

<main id="main" class="main">
    <div class="container mt-5">
        <h4 class="mb-4">Pesanan Dikirim</h4>

        <?php if (count($pesanan) > 0): ?>
            <?php foreach ($pesanan as $row): ?>
                <div class="pesanan-card d-flex align-items-center">
                    <div>
                        <img src="../uploads/<?php echo $row['gambar']; ?>" alt="<?php echo $row['nama_produk']; ?>">
                    </div>
                    <div class="ms-3">
                        <h5><?php echo $row['nama_produk']; ?></h5>
                        <p class="mb-1">Jumlah: x<?php echo $row['jumlah']; ?></p>
                        <p><?php echo date('d-m-Y', strtotime($row['tanggal_pesanan'])); ?></p>
                    </div>
                    <div class="ms-auto text-end">
                        <p>IDR <?php echo number_format($row['harga'], 0, ',', '.'); ?></p>
                        <p>Total: IDR <?php echo number_format($row['total_harga'], 0, ',', '.'); ?></p>
                        <a href="lacak.php?id=<?php echo $row['id_pesanan']; ?>" class="btn btn-primary">Lacak Pengiriman</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Tidak ada pesanan yang sedang dikirim.</p>
        <?php endif; ?>
    </div>
</main>

<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

<!-- Vendor JS Files -->
<script src="../assets/vendor/apexcharts/apexcharts.min.js"></script>
<script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/vendor/chart.js/chart.umd.js"></script>
<script src="../assets/vendor/echarts/echarts.min.js"></script>
<script src="../assets/vendor/quill/quill.min.js"></script>
<script src="../assets/vendor/simple-datatables/simple-datatables.js"></script>
<script src="../assets/vendor/php-email-form/validate.js"></script>

<!-- Template Main JS File -->
<script src="../assets/js/main.js"></script>
</body>
</html>
