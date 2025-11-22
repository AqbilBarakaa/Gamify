<?php
session_start();
include('../db.php'); 
$page = "produk";

// Pastikan pengguna sudah login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user'];
$kue_user = mysqli_query($kon, "SELECT * FROM user WHERE nama = '$user_id'");
$row_user = mysqli_fetch_array($kue_user);

// Ambil kategori dari database
$sql = "SELECT id_kategori, nama_kategori FROM kategori";
$result = mysqli_query($kon, $sql);
$categories = [];
while ($row = mysqli_fetch_assoc($result)) {
    $categories[$row['id_kategori']] = $row['nama_kategori'];
}

// Ambil produk dari database setiap kali halaman dimuat
$sql = "SELECT p.*, k.nama_kategori 
        FROM produk p 
        JOIN kategori k ON p.id_kategori = k.id_kategori
        WHERE p.id_produk NOT IN (SELECT bonus_item FROM informasipromo)";
$result = mysqli_query($kon, $sql);
$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = [
        'id' => $row['id_produk'],
        'name' => $row['nama_produk'],
        'category' => $row['nama_kategori'], 
        'price' => $row['harga'],
        'stock' => $row['stok'],
        'image' => $row['gambar'],
        'description' => $row['deskripsi'] 
    ];
}

// Pastikan kategori sudah dimuat di session
if (!isset($_SESSION['categories'])) {
    $_SESSION['categories'] = [];
}

$_SESSION['products'] = $products; 

$filteredProducts = $products ?? []; 
// Filter kategori dan harga seperti semula
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filter_category'])) {
    $selectedCategory = $_POST['category'] ?? '';
    $selectedPriceOrder = $_POST['price'] ?? '';

    if (!empty($selectedCategory)) {
        $filteredProducts = array_filter($filteredProducts, function($product) use ($selectedCategory) {
            return $product['category'] === $selectedCategory;
        });
    }

    if ($selectedPriceOrder === 'asc') {
        usort($filteredProducts, function($a, $b) {
            return $a['price'] - $b['price'];
        });
    } elseif ($selectedPriceOrder === 'desc') {
        usort($filteredProducts, function($a, $b) {
            return $b['price'] - $a['price'];
        });
    }
}

// Wishlist logic tetap
if (isset($_POST['add_to_wishlist'])) {
    $productIndex = $_POST['product_id'];
    $productId = $products[$productIndex]['id'];

    $checkSql = "SELECT * FROM wishlist WHERE user_id = ? AND id_produk = ?";
    $checkStmt = $kon->prepare($checkSql);
    $checkStmt->bind_param("ii", $row_user, $productId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows == 0) {
        $insertSql = "INSERT INTO wishlist (user_id, id_produk) VALUES (?, ?)";
        $insertStmt = $kon->prepare($insertSql);
        $insertStmt->bind_param("ii", $row_user, $productId);
        $insertStmt->execute();
    }
    header("Location: produk.php?wishlist_success=1");
    exit();
}

// Wishlist saat ini
$wishlist = isset($_SESSION['wishlist']) ? $_SESSION['wishlist'] : [];

// Cek notifikasi sukses
$wishlistSuccess = isset($_GET['wishlist_success']);
$cartSuccess = isset($_GET['cart_success']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>PESANAN</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

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
        body {
            background-color: #f5f5f5;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom : 20px;
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
    </style>
    <?php include 'aset.php'; ?>
</head>

<body>

  <!-- ======= Header ======= -->
  <?php require "atas.php"; ?>
  <!-- End Header -->

  <!-- ======= Sidebar ======= -->
  <?php require "menu.php"; ?>
  <!-- End Sidebar-->
  <main id="main" class="main">
    <div class="pagetitle">
      <h1><i class="bi bi-grid"></i>&nbsp;Produk</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">HOME</a></li>
          <li class="breadcrumb-item active">PRODUK</li>
        </ol>
      </nav>
    </div>

    <div class="container mt-5">
        <!-- Notifikasi Sukses Wishlist -->
        <?php if ($wishlistSuccess): ?>
            <div class="alert alert-success notification">Produk berhasil ditambahkan ke wishlist!</div>
        <?php endif; ?>
        <!-- Notifikasi Sukses Keranjang -->
        <?php if ($cartSuccess): ?>
            <div class="alert alert-success notification">Produk berhasil ditambahkan ke keranjang!</div>
        <?php endif; ?>

        <!-- Filter Kategori -->
        <form method="POST" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <select name="category" class="form-select">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($categories as $id => $category): ?>
                            <option value="<?= htmlspecialchars($category) ?>"
                                <?= (isset($_POST['category']) && $_POST['category'] == $category) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" name="filter_category" class="btn btn-primary">Filter</button>
                </div>
            </div>
        </form>

        <!-- Tampilkan Produk -->
        <div class="row">
            <?php if (count($filteredProducts) > 0): ?>
                <?php foreach ($filteredProducts as $index => $product): ?>
                    <div class="col-md-4">
                        <div class="card">
                            <a href="detail_produk.php?product_id=<?= urlencode($product['id']); ?>">
                                <img src="../uploads/<?= $product['image']; ?>" class="card-img-top" alt="Gambar Produk">
                            </a>
                            <div class="card-body">
                                <h5 class="card-title"><?= $product['name']; ?></h5>
                                <p class="card-text"><?= $product['category']; ?></p>
                                <p class="card-text"><strong>Harga: </strong>Rp <?= number_format($product['price'], 0, ',', '.'); ?></p>
                                <?php if ($product['stock'] > 0): ?>
                                    <!-- Form untuk menambah ke keranjang -->
                                    <form method="POST" action="add_to_cart.php" class="d-inline">
                                        <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
                                        <button type="submit" name="add_to_cart" class="btn btn-success">Add to Cart</button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-secondary" disabled>Stok Habis</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

            <?php else: ?>
                <div class="col-md-12">
                    <div class="alert alert-warning text-center">
                        Tidak ada produk ditemukan untuk kategori yang dipilih.
                    </div>
                </div>
            <?php endif; ?>
        </div>
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