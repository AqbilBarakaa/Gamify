<?php
session_start();
include('../db.php');

// Function to handle file upload
function uploadFile($file) {
    $target_dir = __DIR__ . "/uploads/promos/"; // Pastikan path absolut
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true); // Buat folder jika belum ada
    }

    $target_file = $target_dir . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Validasi gambar
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return "File is not an image.";
    }

    // Validasi ukuran
    if ($file["size"] > 500000) {
        return "Sorry, your file is too large.";
    }

    // Validasi format file
    if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
        return "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    }

    // Unggah file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return "uploads/promos/" . basename($file["name"]);
    } else {
        return "Sorry, there was an error uploading your file.";
    }
}

// Fetch promo to edit
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM informasipromo WHERE id = ?";
    $stmt = mysqli_prepare($kon, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $promo = mysqli_fetch_assoc($result);

    if (!$promo) {
        die("Promo not found");
    }
} else {
    die("No promo ID specified");
}

// Fetch available products for dropdown
$product_query = "SELECT id_produk, nama_produk FROM produk";
$product_result = mysqli_query($kon, $product_query);
$products = mysqli_fetch_all($product_result, MYSQLI_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $promo_type = $_POST['promo_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $discount_percentage = $_POST['discount_percentage'] ?? null;
    $bonus_item = $_POST['bonus_item'] ?? null;

    // Handle file upload
    $photo_url = $promo['photo_url']; // Keep existing photo if not changed
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $new_photo_url = uploadFile($_FILES['photo']);
        if (strpos($new_photo_url, 'Sorry') === 0) {
            $upload_error = $new_photo_url;
        } else {
            $photo_url = $new_photo_url;
        }
    }

    $sql = "UPDATE informasipromo 
            SET title = ?, description = ?, photo_url = ?, promo_type = ?, start_date = ?, end_date = ?, discount_percentage = ?, bonus_item = ? 
            WHERE id = ?";
    $stmt = mysqli_prepare($kon, $sql);
    mysqli_stmt_bind_param($stmt, "ssssssssi", $title, $description, $photo_url, $promo_type, $start_date, $end_date, $discount_percentage, $bonus_item, $id);
    mysqli_stmt_execute($stmt);

    header("Location: informasipromo.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Promo - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php include 'aset.php'; ?>
</head>
<body>
    <div class="wrapper">
        <?php require "atas.php"; ?>
        <?php require "menu.php"; ?>
    </div>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1><i class="bi bi-megaphone"></i>&nbsp; EDIT PROMO</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">HOME</a></li>
                    <li class="breadcrumb-item"><a href="admin_promos.php">PROMO</a></li>
                    <li class="breadcrumb-item active">EDIT</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Edit Promo</h5>
                            <?php if (isset($upload_error)): ?>
                                <div class="alert alert-danger"><?php echo $upload_error; ?></div>
                            <?php endif; ?>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Judul Promo</label>
                                    <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($promo['title']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Deskripsi</label>
                                    <textarea class="form-control" id="description" name="description" rows="3" required><?= htmlspecialchars($promo['description']) ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="photo" class="form-label">Foto Promo</label>
                                    <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                                    <?php if ($promo['photo_url']): ?>
                                        <img src="<?= htmlspecialchars($promo['photo_url']) ?>" alt="Current Promo Photo" style="max-width: 200px; max-height: 200px; margin-top: 10px;">
                                    <?php endif; ?>
                                </div>
                                <div class="mb-3">
                                    <label for="promo_type" class="form-label">Jenis Promo</label>
                                    <select class="form-select" id="promo_type" name="promo_type" required>
                                        <option value="discount" <?= $promo['promo_type'] === 'discount' ? 'selected' : '' ?>>Diskon</option>
                                        <option value="bonus" <?= $promo['promo_type'] === 'bonus' ? 'selected' : '' ?>>Bonus</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Tanggal Mulai</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $promo['start_date'] ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">Tanggal Berakhir</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $promo['end_date'] ?>" required>
                                </div>
                                <div class="mb-3" id="discount_field" <?= $promo['promo_type'] === 'bonus' ? 'style="display:none;"' : '' ?>>
                                    <label for="discount_percentage" class="form-label">Persentase Diskon</label>
                                    <input type="number" class="form-control" id="discount_percentage" name="discount_percentage" min="0" max="100" value="<?= $promo['discount_percentage'] ?>">
                                </div>
                                <div class="mb-3" id="bonus_field" <?= $promo['promo_type'] === 'discount' ? 'style="display:none;"' : '' ?>>
                                    <label for="bonus_item" class="form-label">Item Bonus</label>
                                    <select class="form-select" id="bonus_item" name="bonus_item">
                                        <option value="">-- Pilih Produk --</option>
                                        <?php foreach ($products as $product): ?>
                                            <option value="<?= $product['id_produk'] ?>" <?= $promo['bonus_item'] == $product['id_produk'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($product['nama_produk']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Update Promo</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('promo_type').addEventListener('change', function () {
            var discountField = document.getElementById('discount_field');
            var bonusField = document.getElementById('bonus_field');
            if (this.value === 'discount') {
                discountField.style.display = 'block';
                bonusField.style.display = 'none';
            } else {
                discountField.style.display = 'none';
                bonusField.style.display = 'block';
            }
        });
    </script>
</body>
</html>
