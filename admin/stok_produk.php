<?php
session_start();
include('../db.php'); 
$page = "statistik";

if (!isset($_SESSION["admin"])) {
    header("Location: ../login.php");
    exit;
}

// Statistik produk
$sqlStats = "
    SELECT 
        COUNT(id_produk) AS total_produk, 
        SUM(CASE WHEN stok = 0 THEN 1 ELSE 0 END) AS unavailable_produk,
        (SELECT SUM(jumlah) FROM pesanan) AS total_terjual
    FROM produk
";
$resultStats = mysqli_query($kon, $sqlStats);
if (!$resultStats) {
    die("Query gagal: " . mysqli_error($kon));
}
$stats = mysqli_fetch_assoc($resultStats);

// Data stok produk
$sqlProducts = "
    SELECT 
        p.id_produk, 
        p.nama_produk, 
        p.stok, 
        (SELECT SUM(jumlah) FROM pesanan WHERE id_produk = p.id_produk) AS total_terjual, 
        (SELECT AVG(rating) FROM review_produk WHERE id_produk = p.id_produk) AS rata_rating 
    FROM produk p
";
$resultProducts = mysqli_query($kon, $sqlProducts);
if (!$resultProducts) {
    die("Query gagal: " . mysqli_error($kon));
}
$products = mysqli_fetch_all($resultProducts, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok Produk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <?php 
        include 'aset.php';
    ?>
    <style>
        body {
            background-color: #f5f5f5;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .table th, .table td {
            vertical-align: middle;
        }

        .text-success {
            font-weight: bold;
        }

        .text-danger {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- ======= Header ======= -->
    <?php require "atas.php"; ?>
    <!-- End Header -->

    <!-- ======= Sidebar ======= -->
    <?php require "menu.php"; ?>
    <!-- End Sidebar-->
    <main id="main" class="main">
        <!-- Product Statistics -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light text-black">
                        <h4>Product Statistics</h4>
                    </div>
                    <br>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <h5>Total Produk</h5>
                                <p class="h3"><?= $stats['total_produk'] ?></p>
                            </div>
                            <div class="col-md-4">
                                <h5>Unavailable Produk</h5>
                                <p class="h3 text-danger"><?= $stats['unavailable_produk'] ?></p>
                            </div>
                            <div class="col-md-4">
                                <h5>Total Terjual</h5>
                                <p class="h3"><?= $stats['total_terjual'] ?? 0 ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light text-black">
                        <h4>Stok Produk</h4>
                    </div>
                    <br>
                    <div class="card-body">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Produk</th>
                                    <th>Performance</th>
                                    <th>Stok</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($product['nama_produk']) ?></td>
                                        <td>
                                            <strong>Total Terjual:</strong> <?= $product['total_terjual'] ?? 0 ?> <br>
                                            <strong>Rata-rata Rating:</strong> <?= number_format($product['rata_rating'] ?? 0, 1) ?>/5
                                        </td>
                                        <td><?= $product['stok'] ?></td>
                                        <td>
                                            <?php if ($product['stok'] > 0): ?>
                                                <span class="text-success">Available</span>
                                            <?php else: ?>
                                                <span class="text-danger">Unavailable</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php if (empty($products)): ?>
                            <div class="alert alert-warning text-center">
                                Tidak ada produk ditemukan.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
