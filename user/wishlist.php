<?php
session_start();
include '../db.php';

// Pastikan user sudah login
if (!isset($_SESSION['user'])) {
    header("Location: login.php"); 
    exit();
}

$user_id = $_SESSION['user'];
$kue_user = mysqli_query($kon, "SELECT * FROM user WHERE nama = '$user_id'");
$row_user = mysqli_fetch_array($kue_user);

$sql = "SELECT p.*, w.id_wishlist AS wishlist_id FROM wishlist w 
        JOIN produk p ON w.id_produk = p.id_produk 
        WHERE w.user_id = ?";

$stmt = $kon->prepare($sql);
$stmt->bind_param("i", $row_user);
$stmt->execute();
$result = $stmt->get_result();
$wishlistItems = $result->fetch_all(MYSQLI_ASSOC);

// Tambahkan fungsi untuk mengecek stok produk
function checkProductStock($kon, $productId) {
    $stockSql = "SELECT stok FROM produk WHERE id_produk = ?";
    $stockStmt = $kon->prepare($stockSql);
    $stockStmt->bind_param("i", $productId);
    $stockStmt->execute();
    $stockResult = $stockStmt->get_result();
    $product = $stockResult->fetch_assoc();
    return $product['stok'] > 0;
}

// Proses penghapusan dari wishlist
if (isset($_GET['remove'])) {
    $productId = $_GET['remove'];
    $checkSql = "SELECT * FROM wishlist WHERE user_id = ? AND id_produk = ?";
    $checkStmt = $kon->prepare($checkSql);
    $checkStmt->bind_param("ii", $row_user, $productId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $deleteSql = "DELETE FROM wishlist WHERE user_id = ? AND produk_id = ?";
        $deleteStmt = $kon->prepare($deleteSql);
        $deleteStmt->bind_param("ii", $row_user, $productId);
        $deleteStmt->execute();
    }
    header("Location: wishlist.php");
    exit();
}

// Proses menambah ke keranjang
if (isset($_POST['add_to_cart'])) {
    $productId = $_POST['product_id'];
    // Menambahkan ke keranjang
    $addToCartSql = "INSERT INTO keranjang (user_id, id_produk, jumlah) VALUES (?, ?, 1)";
    $addToCartStmt = $kon->prepare($addToCartSql);
    $addToCartStmt->bind_param("ii", $row_user, $productId);
    $addToCartStmt->execute();

    // Hapus dari wishlist
    $removeWishlistSql = "DELETE FROM wishlist WHERE user_id = ? AND id_produk = ?";
    $removeWishlistStmt = $kon->prepare($removeWishlistSql);
    $removeWishlistStmt->bind_param("ii", $row_user, $productId);
    $removeWishlistStmt->execute();

    header("Location: add_to_cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Wishlist</title>
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <?php include 'aset.php'; ?>
</head>
<body>

    <!-- HEADER -->
    <?php require "atas.php"; ?>

    <!-- SIDEBAR -->
    <?php require "menu.php"; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1><i class="bi bi-heart"></i>&nbsp; Wishlist</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">DASHBOARD</a></li>
                    <li class="breadcrumb-item active">Wishlist</li>
                </ol>
            </nav>
        </div>

        <div class="container mt-5">
            <?php if (empty($wishlistItems)): ?>
                <p>Wishlist Anda kosong.</p>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($wishlistItems as $item): ?>
                        <div class="col-md-4">
                            <div class="card">
                                <a href="detail_produk.php?product_id=<?= $item['id_produk']; ?>">
                                    <img src="../uploads/<?= $item['gambar']; ?>" class="card-img-top" alt="<?= $item['nama_produk']; ?>">
                                </a>
                                <div class="card-body">
                                    <h5 class="card-title"><?= $item['nama_produk']; ?></h5>
                                    <p class="card-text"><strong>Harga: </strong>Rp <?= number_format($item['harga'], 0, ',', '.'); ?></p>
                                    <p class="card-text"><strong>Stok: </strong><?= checkProductStock($kon, $item['id_produk']) ? 'Tersedia' : 'Tidak Tersedia'; ?></p>
                                    <div class="d-flex justify-content-between">
                                        <?php if (checkProductStock($kon, $item['id_produk'])): ?>
                                            <form action="" method="POST" style="display:inline;">
                                                <input type="hidden" name="product_id" value="<?= $item['id_produk']; ?>">
                                                <button type="submit" name="add_to_cart" class="btn btn-success btn-sm">Tambah ke Keranjang</button>
                                            </form>
                                        <?php endif; ?>
                                        <a href="wishlist.php?remove=<?= $item['id_produk']; ?>" class="btn btn-danger btn-sm">Hapus</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="text-center mt-4">
                <a href="produk.php" class="btn btn-secondary">Lihat Produk Lainnya</a>
            </div>
        </div>
    </main><!-- End #main -->

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <!-- Vendor JS Files -->
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>

<?php
$kon->close();
?>