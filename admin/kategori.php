<?php
session_start();
include('../db.php'); 
$page = "kategori";

if (!isset($_SESSION["admin"])) {
    header("Location: ../login.php");
    exit;
}

// Inisialisasi kategori dari database jika session categories kosong
if (empty($_SESSION['categories'])) {
    $_SESSION['categories'] = [];

    // Query untuk mengambil data kategori dari database
    $sql = "SELECT id_kategori, nama_kategori FROM kategori";
    $result = mysqli_query($kon, $sql);

    // Simpan kategori ke dalam session
    while ($row = mysqli_fetch_assoc($result)) {
        $_SESSION['categories'][$row['id_kategori']] = $row['nama_kategori'];
    }
}

// Proses penambahan kategori
if (isset($_POST['add_category'])) {
    $newCategory = $_POST['category_name'];
    if (!empty($newCategory)) {
        // Tambahkan kategori baru ke database
        $sql = "INSERT INTO kategori (nama_kategori, deskripsi, created_at) VALUES ('$newCategory', '', NOW())";
        if (mysqli_query($kon, $sql)) {
            // Ambil ID kategori yang baru ditambahkan
            $newId = mysqli_insert_id($kon);
            // Tambahkan kategori baru ke session
            $_SESSION['categories'][$newId] = $newCategory;
        }
    }
}

// Proses penghapusan kategori
if (isset($_GET['delete_category'])) {
    $categoryId = $_GET['delete_category'];

    // Hapus kategori dari database
    $sql = "DELETE FROM kategori WHERE id_kategori = $categoryId";
    if (mysqli_query($kon, $sql)) {
        // Hapus kategori dari session
        unset($_SESSION['categories'][$categoryId]);
    }
}

// Menghitung jumlah produk per kategori
$categoryCounts = [];
if (isset($_SESSION['products'])) {
    foreach ($_SESSION['categories'] as $id => $category) {
        $count = 0;
        foreach ($_SESSION['products'] as $product) {
            if ($product['category'] == $category) {
                $count++;
            }
        }
        $categoryCounts[$id] = $count;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>DASHBOARD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <?php include 'aset.php'; ?>
</head>
<body>
<div class="container mt-5">
    <?php require "atas.php"; ?>
    <?php require "menu.php"; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1><i class="bi bi-grid"></i>&nbsp; KATEGORI</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">HOME</a></li>
                    <li class="breadcrumb-item active">KATEGORI</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="card-title">Daftar Kategori</h2>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                Tambah Kategori
                            </button>

                            <table class="table table-bordered mt-3">
                                <thead>
                                    <tr>
                                        <th>Nama Kategori</th>
                                        <th>Jumlah Produk</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($_SESSION['categories'] as $id => $category): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($category) ?></td>
                                            <td><?= $categoryCounts[$id] ?? 0 ?></td>
                                            <td>
                                                <a href="kategori.php?delete_category=<?= $id ?>" class="btn btn-danger btn-sm">Hapus</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section </main>
    </div>

    <!-- Modal for Adding Category -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">Tambah Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="kategori.php" method="POST">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="category_name">Nama Kategori:</label>
                            <input type="text" class="form-control" id="category_name" name="category_name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="add_category" class="btn btn-primary">Tambah Kategori</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>