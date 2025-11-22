<?php
session_start();
require "../db.php";
$page = "grafik_penjualan";
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION["admin"])) {
    header("Location: ../login.php");
    exit;
}

// Total Produk
$total_products_query = mysqli_query($kon, "SELECT COUNT(id_produk) as total_produk FROM produk");
$total_products = mysqli_fetch_assoc($total_products_query)['total_produk'];

// Produk Terjual
$total_sold_query = mysqli_query($kon, "SELECT COUNT(id_pesanan) as total_terjual FROM pesanan WHERE status_pesanan != 'Dibatalkan'");
$total_sold = mysqli_fetch_assoc($total_sold_query)['total_terjual'];

// Grafik Data (X: Semua Produk, Y: Total Penjualan per Produk)
$chart_data_query = mysqli_query($kon, "
    SELECT produk.nama_produk, COUNT(pesanan.id_produk) as jumlah_terjual 
    FROM produk 
    LEFT JOIN pesanan ON produk.id_produk = pesanan.id_produk AND pesanan.status_pesanan != 'Dibatalkan'
    GROUP BY produk.id_produk
    ORDER BY jumlah_terjual DESC
");

$chart_labels = [];
$chart_values = [];
while ($row = mysqli_fetch_assoc($chart_data_query)) {
    $chart_labels[] = $row['nama_produk'];
    $chart_values[] = $row['jumlah_terjual'];
}

// Produk Paling Laris
$top_products_query = mysqli_query($kon, "
    SELECT produk.nama_produk, produk.harga, SUM(pesanan.jumlah) as jumlah_terjual
    FROM produk
    JOIN pesanan ON produk.id_produk = pesanan.id_produk
    WHERE pesanan.status_pesanan != 'Dibatalkan'
    GROUP BY produk.id_produk
    ORDER BY jumlah_terjual DESC
    LIMIT 5
");
$top_products = mysqli_fetch_all($top_products_query, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Grafik Penjualan</title>
    <meta name="robots" content="noindex, nofollow" />
    <meta content="" name="description" />
    <meta content="" name="keywords" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php include "aset.php"; ?>
</head>
<body>
    <!-- HEADER -->
    <?php require "atas.php"; ?>

    <!-- SIDEBAR -->
    <?php require "menu.php"; ?>

    <main id="main" class="main">
        <div class="container">
            <div class="pagetitle">
                <h1><i class="bi bi-bar-chart"></i>&nbsp; Grafik Total Penjualan</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Grafik Penjualan</li>
                    </ol>
                </nav>
            </div>

            <!-- Total Produk & Produk Terjual -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Total Produk</h5>
                            <h3 class="text-primary"><?= $total_products ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Produk Terjual</h5>
                            <h3 class="text-success"><?= $total_sold ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grafik Penjualan -->
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Grafik Penjualan</h5>
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Produk Paling Laris -->
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Produk Paling Laris</h5>
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nama Produk</th>
                                        <th>Harga</th>
                                        <th>Jumlah Terjual</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($top_products)) : ?>
                                        <?php foreach ($top_products as $index => $product) : ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td><?= htmlspecialchars($product['nama_produk']) ?></td>
                                                <td>Rp <?= number_format($product['harga'], 0, ',', '.') ?></td>
                                                <td><?= $product['jumlah_terjual'] ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="4">Tidak ada data produk.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Chart.js Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('salesChart').getContext('2d');
            const salesChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($chart_labels) ?>,
                    datasets: [{
                        label: 'Jumlah Terjual',
                        data: <?= json_encode($chart_values) ?>,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
