<?php
// Koneksi ke database
include '../db.php';
session_start();
$page = "fav";

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

// Query untuk mendapatkan produk yang paling banyak dibeli
$query = "SELECT produk.id_produk, produk.nama_produk, produk.deskripsi, produk.harga, produk.gambar, 
                 SUM(pesanan.jumlah) AS total_dibeli
          FROM produk
          JOIN pesanan ON produk.id_produk = pesanan.id_produk
          GROUP BY produk.id_produk
          HAVING total_dibeli > 10
          ORDER BY total_dibeli DESC";
$result = mysqli_query($kon, $query);

// Mulai halaman HTML
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Most Favorite Items</title>
    <!-- Link Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
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
    </style><?php include 'aset.php'; ?>
</head>

<body>

  <!-- ======= Header ======= -->
  <?php require "atas.php"; ?>
  <!-- End Header -->

  <!-- ======= Sidebar ======= -->
  <?php require "menu.php"; ?>
  <!-- End Sidebar-->
  <main id="main" class="main">
    <div class="container mt-5">
        <h1 class="text-center mb-4">Most Favorite Items</h1>
        <div class="row">
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <a href="detail_produk.php?product_id=<?= $row['id_produk']; ?>">
                            <img src="../uploads/<?= htmlspecialchars($row['gambar']); ?>" class="card-img-top" alt="Gambar Produk">
                        </a>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($row['nama_produk']); ?></h5>
                            <p class="card-text">Harga: Rp <?= number_format($row['harga'], 2, ',', '.'); ?></p>
                            <p class="card-text">Total Dibeli: <?= htmlspecialchars($row['total_dibeli']); ?> kali</p>
                            <p class="card-text text-truncate">Deskripsi: <?= htmlspecialchars($row['deskripsi']); ?></p>
                            <form method="POST" action="add_to_cart.php" class="d-inline">
                                <input type="hidden" name="product_id" value="<?= $row['id_produk']; ?>">
                                <button type="submit" name="add_to_cart" class="btn btn-success">Add to Cart</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</main>

    <!-- Link Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>