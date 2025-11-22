<?php
require '../db.php';
session_start();
$page = "pesanan_selesai";

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}   

// Ambil filter status pesanan dari checkbox (bisa lebih dari satu)
$status_filter = isset($_GET['filter']) ? $_GET['filter'] : ['Sudah Dinilai'];

// Pastikan `$status_filter` adalah array
if (!is_array($status_filter)) {
    $status_filter = [$status_filter];
}

// Query untuk mengambil data pesanan berdasarkan filter
$query = "SELECT p.*, pr.nama_produk, pr.harga, pr.gambar, 
                 CASE WHEN EXISTS (
                     SELECT 1 FROM review_produk r WHERE r.id_pesanan = p.id_pesanan
                 ) THEN 'Sudah Dinilai' ELSE 'Menunggu Dinilai' END AS review_status
          FROM pesanan p
          JOIN produk pr ON p.id_produk = pr.id_produk
          WHERE p.status_pesanan = 'Selesai'";

if (!empty($status_filter)) {
    $placeholders = implode(',', array_fill(0, count($status_filter), '?'));
    $query .= " AND (CASE WHEN EXISTS (
                     SELECT 1 FROM review_produk r WHERE r.id_pesanan = p.id_pesanan
                 ) THEN 'Sudah Dinilai' ELSE 'Menunggu Dinilai' END) IN ($placeholders)";
}

$stmt = $kon->prepare($query);

// Bind parameter untuk setiap filter
if (!empty($status_filter)) {
    $stmt->bind_param(str_repeat('s', count($status_filter)), ...$status_filter);
}

$stmt->execute();
$result = $stmt->get_result();
$pesanan = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Dinilai</title>
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
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            min-height: 450px; /* Ukuran kartu seragam */
        }
        .card-img-top {
            height: 250px; /* Ukuran gambar seragam */
            object-fit: cover; /* Memastikan gambar tidak terdistorsi */
            border-radius: 15px 15px 0 0;
        }
        .card-body {
            min-height: 150px; /* Menyesuaikan tinggi deskripsi produk agar seragam */
        }
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        .btn-wishlist {
            margin-top: 10px;
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

<!-- ... existing code ... -->
<?php require "atas.php"; ?>
<!-- End Header -->

<!-- ======= Sidebar ======= -->
<?php require "menu.php"; ?>
<!-- End Sidebar-->
<main id="main" class="main">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>Pesanan</h4>
            <div>
                <form method="get" class="d-inline">
                    <input type="radio" name="filter" value="Sudah Dinilai" 
                        <?php echo in_array('Sudah Dinilai', $status_filter) ? 'checked' : ''; ?>> Sudah Dinilai
                    <input type="radio" name="filter" value="Menunggu Dinilai" 
                        <?php echo in_array('Menunggu Dinilai', $status_filter) ? 'checked' : ''; ?>> Menunggu Dinilai
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                </form>
            </div>
        </div>

        <?php if (count($pesanan) > 0): ?>
            <?php foreach ($pesanan as $row): ?>
                <div class="pesanan-card d-flex align-items-center">
                    <div>
                        <img src="../uploads/<?php echo $row['gambar']; ?>" alt="<?php echo $row['nama_produk']; ?>">
                    </div>
                    <div class="ms-3">
                        <h5><?php echo $row['nama_produk']; ?></h5>
                        <p class="mb-1">Jumlah: <?php echo $row['jumlah'] ?></p>
                        <p><?php echo date('d-m-Y', strtotime($row['tanggal_pesanan'])); ?></p>
                    </div>
                    <div class="ms-auto text-end">
                        <p>IDR <?php echo number_format($row['harga'], 0, ',', '.'); ?></p>
                        <p>Total: IDR <?php echo number_format($row['total_harga'], 0, ',', '.'); ?></p>
                        <a href="pembayaran.php?id_pesanan=<?php echo $row['id_pesanan']; ?>" class="btn btn-orange">Beli Lagi</a>
                        <?php if ($row['review_status'] === 'Menunggu Dinilai'): ?>
                            <a href="review.php?id=<?php echo $row['id_pesanan']; ?>" class="btn btn-secondary">Nilai</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Tidak ada pesanan untuk ditampilkan.</p>
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