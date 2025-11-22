<?php
session_start();
include '../db.php';

// Jika produk belum ada di session, arahkan ke halaman utama
if (!isset($_SESSION['products']) || !isset($_GET['product_id'])) {
    header("Location: produk.php");
    exit();
}

$productId = intval($_GET['product_id']);
$product = null;

// Ambil produk dari database berdasarkan ID
$query = $kon->prepare("SELECT p.*, k.nama_kategori FROM produk p LEFT JOIN kategori k ON p.id_kategori = k.id_kategori WHERE p.id_produk = ?");
$query->bind_param("i", $productId);
$query->execute();
$result = $query->get_result();

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
} else {
    header("Location: produk.php");
    exit();
}

// Ambil semua kategori dari database
$categories = [];
$categoryQuery = $kon->query("SELECT id_kategori, nama_kategori FROM kategori");
while ($row = $categoryQuery->fetch_assoc()) {
    $categories[] = $row;
}

// Update informasi produk jika form di-submit
if (isset($_POST['edit_product'])) {
    $updatedName = $_POST['name'];
    $updatedCategory = $_POST['category'];
    $updatedPrice = $_POST['price'];
    $updatedStock = $_POST['stock'];
    $updatedDescription = $_POST['description'];

    // Proses gambar
    if ($_FILES['image']['name'] != '') {
        // Gambar baru diunggah
        $imageName = $_FILES['image']['name'];
        $imagePath = '../uploads/' . $imageName; // Update path sesuai dengan direktori
        move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
    } else {
        // Gunakan gambar lama jika tidak ada unggahan baru
        $imageName = $product['gambar'];
    }

    // Update data di database
    $stmt = $kon->prepare("UPDATE produk SET nama_produk = ?, id_kategori = ?, harga = ?, stok = ?, deskripsi = ?, gambar = ? WHERE id_produk = ?");
    $stmt->bind_param("siisssi", $updatedName, $updatedCategory, $updatedPrice, $updatedStock, $updatedDescription, $imageName, $productId);
    $stmt->execute();

    // Redirect untuk menghindari resubmission form
    header("Location: detail_produk.php?product_id=$productId&success=1");
    exit();
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Produk - <?= htmlspecialchars($product['nama_produk']) ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Vendor CSS Files -->
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    
    <style>
        .product-image {
            max-height: 500px;
            width: 100%;
            object-fit: cover;
        }
        .product-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
        }
        .btn-custom {
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            transform: scale(1.05);
        }
        .edit-product-form {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            overflow-y: auto;
        }
        .edit-product-form .card {
            width: 90%;
            max-width: 600px;
            margin: auto;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            background-color: white;
            padding: 20px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include 'atas.php'; ?>
    <?php include 'menu.php'; ?>
    
    <main id="main" class="main">
        <div class="container mt-5">
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    Produk berhasil diperbarui!
                </div>
            <?php endif; ?>
            
            <div class="row">
                <!-- Gambar Produk -->
                <div class="col-md-6">
                    <img 
                        src="../uploads/<?= htmlspecialchars($product['gambar']) ?>" 
                        alt="<?= htmlspecialchars($product['nama_produk']) ?>" 
                        class="img-fluid product-image rounded shadow"
                    >
                </div>
                
                <!-- Detail Produk -->
                <div class="col-md-6 product-details">
                    <h1 class="mb-3"><?= htmlspecialchars($product['nama_produk']) ?></h1>
                    
                    <!-- Kategori -->
                    <div class="mb-3">
                        <span class="badge bg-primary">
                            <?= htmlspecialchars($product['nama_kategori'] ?? 'Tidak Berkategori') ?>
                        </span>
                    </div>
                    
                    <!-- Harga -->
                    <h3 class="text-danger mb-3">
                        Rp. <?= number_format($product['harga'], 0, ',', '.') ?>
                    </h3>
                    
                    <!-- Deskripsi -->
                    <p class="mb-4"><?= htmlspecialchars($product['deskripsi']) ?></p>
                    
                    <!-- Informasi Tambahan -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <strong>Stok:</strong> 
                            <span class="<?= $product['stok'] > 0 ? 'text-success' : 'text-danger' ?>">
                                <?= $product['stok'] > 0 ? $product['stok'] : 'Habis' ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Tombol Aksi -->
                    <div class="mt-4">
                        <button class="btn btn-warning btn- custom" id="edit-product-btn">Edit Produk</button>
                        <div id="edit-product-form" class="edit-product-form" style="display: none;">
                            <div class="card card-body">
                                <h4>Edit Detail Produk</h4>
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Nama Produk</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($product['nama_produk']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="category" class="form-label">Kategori</label>
                                        <select class="form-select" id="category" name="category" required>
                                            <option value="" disabled>Pilih Kategori</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?= $category['id_kategori'] ?>" 
                                                    <?= $product['id_kategori'] == $category['id_kategori'] ? 'selected' : ''; ?>>
                                                    <?= htmlspecialchars($category['nama_kategori']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="price" class="form-label">Harga</label>
                                        <input type="number" class="form-control" id="price" name="price" value="<?= htmlspecialchars($product['harga']); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="stock" class="form-label">Stok</label>
                                        <input type="number" class="form-control" id="stock" name="stock" value="<?= htmlspecialchars($product['stok']); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">Deskripsi Produk</label>
                                        <textarea class="form-control" id="description" name="description" rows="4" required><?= htmlspecialchars($product['deskripsi']); ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="image" class="form-label">Gambar Produk (Biarkan kosong jika tidak ingin mengubah gambar)</label>
                                        <input type="file" class="form-control" id="image" name="image">
                                        <img src="../admin/uploads/<?= htmlspecialchars($product['gambar']); ?>" alt="Gambar Produk" class="mt-3" style="max-width: 200px;">
                                    </div>

                                    <button type="submit" name="edit_product" class="btn btn-primary">Update Produk</button>
                                    <button type="button" class="btn btn-secondary" id="close-edit-form">Batal</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <div class="text-center mt-4">
        <a href="produk.php" class="btn btn-secondary">Kembali ke Daftar Produk</a>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/vendor/chart.js/chart.umd.js"></script>
    <script src="../assets/vendor/echarts/echarts.min.js"></script>
    <script src="../assets/vendor/quill/quill.min.js"></script>
    <script src="../assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="../assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="../assets/vendor/php-email-form/validate.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#edit-product-btn').click(function() {
            $('#edit-product-form').fadeIn();
        });

        $('#close-edit-form').click(function() {
            $('#edit-product-form').fadeOut();
        });
    });
    </script>
</body>
</html>

<?php
// Tutup statement dan koneksi
$query->close();
$kon->close();
?>