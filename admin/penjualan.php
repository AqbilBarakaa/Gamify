<?php
session_start();
$page = "penjualan";
if (!isset($_SESSION["admin"])) {
    header("Location: ../login.php");
    exit;
}

$host = 'localhost';
$dbname = 'gamify';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}

// Filter data berdasarkan periode
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$conditions = "p.status_pesanan != 'Dibatalkan'";
$date_filter = '';

if ($filter === 'harian') {
    $date_filter = "AND DATE(p.tanggal_pesanan) = CURDATE()";
} elseif ($filter === 'bulanan') {
    $date_filter = "AND MONTH(p.tanggal_pesanan) = MONTH(CURDATE()) AND YEAR(p.tanggal_pesanan) = YEAR(CURDATE())";
} elseif ($filter === 'tahunan') {
    $date_filter = "AND YEAR(p.tanggal_pesanan) = YEAR(CURDATE())";
}
$conditions .= " $date_filter";

// Hitung total pendapatan
$total_query = "SELECT SUM(p.total_harga) AS total_pendapatan 
                FROM pesanan p
                WHERE $conditions";
$total_stmt = $pdo->query($total_query);
$total_pendapatan = $total_stmt->fetch(PDO::FETCH_ASSOC)['total_pendapatan'] ?? 0;

// Query data pesanan
$query = "SELECT pr.nama_produk, p.tanggal_pesanan, p.jumlah, pr.harga, p.total_harga 
          FROM pesanan p
          JOIN produk pr ON p.id_produk = pr.id_produk
          WHERE $conditions
          ORDER BY p.tanggal_pesanan DESC";
$stmt = $pdo->query($query);
$pesanan = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Penjualan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table th, .table td {
            vertical-align: middle;
            text-align: center;
        }
    </style>
    <?php include 'aset.php'; ?>
</head>
<body>
<?php include 'atas.php'; ?>
<?php include 'menu.php'; ?>

<main id="main" class="main">
    <div class="container">
        <div class="row mb-4">
            <div class="col">
                <h1><i class="bi bi-graph-up"></i>&nbsp; Data Penjualan</h1>
            </div>
        </div>

        <!-- Total Pendapatan dan Filter -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Pendapatan</h5>
                        <h3 class="text-success">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <form method="GET" class="card">
                    <div class="card-body">
                        <h5 class="card-title">Filter Data</h5>
                        <select name="filter" id="filter" class="form-select" onchange="this.form.submit()">
                            <option value="all" <?= $filter == 'all' ? 'selected' : '' ?>>Semua</option>
                            <option value="harian" <?= $filter == 'harian' ? 'selected' : '' ?>>Harian</option>
                            <option value="bulanan" <?= $filter == 'bulanan' ? 'selected' : '' ?>>Bulanan</option>
                            <option value="tahunan" <?= $filter == 'tahunan' ? 'selected' : '' ?>>Tahunan</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Data Penjualan -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Daftar Penjualan</h5>
                <a href="grafik.php" class="btn btn-primary">Grafik Penjualan</a>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Produk</th>
                            <th>Tanggal</th>
                            <th>Jumlah</th>
                            <th>Harga Satuan</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pesanan)) : ?>
                            <?php foreach ($pesanan as $index => $order) : ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($order['nama_produk']) ?></td>
                                    <td><?= $order['tanggal_pesanan'] ?></td>
                                    <td><?= $order['jumlah'] ?></td>
                                    <td>Rp <?= number_format($order['harga'], 0, ',', '.') ?></td>
                                    <td>Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6">Tidak ada data penjualan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
