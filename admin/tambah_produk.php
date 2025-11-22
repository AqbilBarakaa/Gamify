<?php
session_start();
include('../db.php');

// Jika produk belum ada di session, inisialisasi array produk
if (!isset($_SESSION['products'])) {
    $_SESSION['products'] = [];
}

$categories = [];
$categoryQuery = $kon->query("SELECT id_kategori, nama_kategori FROM kategori");
while ($row = $categoryQuery->fetch_assoc()) {
    $categories[] = $row;
}



// Proses penambahan produk jika form di-submit
if (isset($_POST['add_product'])) {
    // Validasi input
    $requiredFields = ['name', 'category', 'price', 'description', 'stock']; 
    $missingFields = [];

    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $missingFields[] = $field;
        }
    }

    if (!empty($missingFields)) {
        $errorMessage = "Harap isi semua field yang diperlukan.";
    } else {
        // Proses upload gambar
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageName = basename($_FILES['image']['name']);
            $targetDir = "../uploads/";
            $targetFilePath = $targetDir . $imageName;

            // Hanya izinkan beberapa ekstensi file
            $allowedFileTypes = ['jpg', 'jpeg', 'png', 'gif'];
            $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

            if (in_array($fileType, $allowedFileTypes)) {
                // Pastikan nama file unik
                $imageName = uniqid() . '_' . $imageName;
                $targetFilePath = $targetDir . $imageName;

                // Upload file ke direktori target
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
                    $imageUploaded = true;
                } else {
                    $imageUploaded = false;
                    $errorMessage = "Gagal mengunggah gambar.";
                }
            } else {
                $imageUploaded = false;
                $errorMessage = "Format file gambar tidak valid. Hanya JPG, JPEG, PNG, dan GIF yang diperbolehkan.";
            }
        } else {
            $imageUploaded = false;
            $errorMessage = "Tidak ada gambar yang dipilih atau terjadi kesalahan saat mengunggah.";
        }

        // Jika gambar berhasil diunggah, lanjutkan dengan validasi kategori
        if ($imageUploaded) {
            $nama_produk = htmlspecialchars($_POST['name']);
            $harga_produk = floatval($_POST['price']);
            $deskripsi_produk = htmlspecialchars($_POST['description']);
            $kategori_produk = intval($_POST['category']); // Ambil ID kategori

            // Tambahkan validasi kategori sebelum insert
            $check_kategori = "SELECT COUNT(*) as count FROM kategori WHERE id_kategori = ?";
            $stmt_check = $kon->prepare($check_kategori);
            $stmt_check->bind_param("i", $kategori_produk);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            $row_check = $result_check->fetch_assoc();

            if ($row_check['count'] > 0) {
                // Kategori valid, lanjutkan insert
                $query = "INSERT INTO produk (nama_produk, harga, deskripsi, id_kategori, stok, gambar) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $kon->prepare($query);
                $stmt->bind_param("sssiis", $nama_produk, $harga_produk, $deskripsi_produk, $kategori_produk, $_POST['stock'], $imageName);
                
                if ($stmt->execute()) {
                    // Berhasil
                    header("Location: produk.php?add_success=1");
                    exit();
                } else {
                    $errorMessage = "Error: " . $stmt->error;
                    header("Location: tambah_produk.php?add_error=1&message=" . urlencode($errorMessage));
                    exit();
                }
            } else {
                // Kategori tidak valid
                $errorMessage = "Kategori produk tidak valid";
                header("Location: tambah_produk.php?add_error=1&message=" . urlencode($errorMessage));
                exit();
            }
        } else {
            // Redirect ke halaman tambah_produk.php jika terjadi error
            header("Location: tambah_produk.php?add_error=1&message=" . urlencode($errorMessage));
            exit();
        }
    }
}

// Proses edit produk
if (isset($_POST['edit_product'])) {
    $product_id = $_POST['product_id'];
    $nama_produk = htmlspecialchars($_POST['name']);
    $harga_produk = floatval($_POST['price']);
    $deskripsi_produk = htmlspecialchars($_POST['description']);
    $kategori_produk = intval($_POST['category']);
    $stok_produk = intval($_POST['stock']);

    // Cek jika ada file gambar baru
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageName = basename($_FILES['image']['name']);
        $targetDir = "../admin/uploads/";
        $imageName = uniqid() . '_' . $imageName;
        $targetFilePath = $targetDir . $imageName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
            $query = "UPDATE produk SET nama_produk=?, harga=?, deskripsi=?, id_kategori=?, stok=?, gambar=? WHERE id_produk=?";
            $stmt = $kon->prepare($query);
            $stmt->bind_param("sssiisi", $nama_produk, $harga_produk, $deskripsi_produk, $kategori_produk, $stok_produk, $imageName, $product_id);
        } else {
            $errorMessage = "Gagal memperbarui gambar.";
        }
    } else {
        $query = "UPDATE produk SET nama_produk=?, harga=?, deskripsi=?, id_kategori=?, stok=? WHERE id_produk=?";
        $stmt = $kon->prepare($query);
        $stmt->bind_param("sssiii", $nama_produk, $harga_produk, $deskripsi_produk, $kategori_produk, $stok_produk, $product_id);
    }

    if ($stmt->execute()) {
        header("Location: produk.php?edit_success=1");
        exit();
    } else {
        header("Location: edit_produk.php?edit_error=1&message=" . urlencode($stmt->error));
        exit();
    }
}

// Proses hapus produk
if (isset($_GET['delete_product'])) {
    $product_id = intval($_GET['delete_product']);
    $query = "DELETE FROM produk WHERE id_produk=?";
    $stmt = $kon->prepare($query);
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        header("Location: produk.php?delete_success=1");
        exit();
    } else {
        header("Location: produk.php?delete_error=1&message=" . urlencode($stmt->error));
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Tambah Produk - Admin</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="../assets/img/favicon.png" rel="icon">
    <link href="../assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
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
        }
    </style>
    <?php 
    include 'aset.php';
    ?>
</head>
<body>
<div class="container mt-5">

    <!-- ======= Header ======= -->
    <?php require "atas.php"; ?>
    <!-- End Header -->

    <!-- ======= Sidebar ======= -->
    <?php require "menu.php"; ?>
    <!-- End Sidebar-->

    <main id="main" class="main">
        <div class="pagetitle">
            <h1><i class="bi bi-grid"></i>&nbsp; DASHBOARD</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">HOME</a></li>
                    <li class="breadcrumb-item active">Tambah Produk</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <?php if (isset($_GET['add_error'])): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['add_success'])): ?>
            <div class="alert alert-success">
                Produk berhasil ditambahkan!
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Produk</label>
                                <input type="text" class="form-control" id="name" name="name" required value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="category" class="form-label">Kategori</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="" disabled>Pilih Kategori</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category['id_kategori'] ?>" 
                                                <?= $category['id_kategori'] ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($category['nama_kategori']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                </select>
                            </div>



                            <div class="mb-3">
                                <label for="price" class="form-label">Harga</label>
                                <input type="number" class="form-control" id="price" name="price" required value="<?= isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="stock" class="form-label">Stok</label>
                                <input type="number" class="form-control" id="stock" name="stock" required value="<?= isset($_POST['stock']) ? htmlspecialchars($_POST['stock']) : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Deskripsi Produk</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="image" class="form-label">Unggah Gambar Produk</label>
                                <input type="file" class="form-control" id="image" name="image" required>
                            </div>

                            <button type="submit" name="add_product" class="btn btn-primary">Tambah Produk</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="produk.php" class="btn btn-secondary">Kembali ke Daftar Produk</a>
        </div>
    </main>

</div>

<!-- End #main -->

<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

<!-- Vendor JS Files -->
<script src="../assets/vendor/apexcharts/apexcharts.min.js"></script>
<script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/vendor/chart.js/chart.umd.js"></script>
<script src="../assets/vendor/echarts/echarts.min.js"></script>
<script src="../assets/vendor/quill/quill.min.js"></script>
<script src="../assets/vendor/simple-datatables/simple-datatables.js"></script>
<script src="../assets/vendor/tinymce/tinymce.min.js"></script>
<script src="../assets/vendor/php-email-form/validate.js"></script>

<!-- Template Main JS File -->
<script src="../assets/js/main.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>