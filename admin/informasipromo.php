<?php
session_start();
include('../db.php');
$page = "info";


// Fetch all active informasipromo
$current_date = date('Y-m-d');
$sql = "SELECT * FROM informasipromo WHERE start_date <= ? AND end_date >= ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($kon, $sql);
mysqli_stmt_bind_param($stmt, "ss", $current_date, $current_date);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$informasipromo = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Separate informasipromo by type
$all_informasipromo = $informasipromo;
$discount_informasipromo = array_filter($informasipromo, function($promo) {
    return $promo['promo_type'] === 'discount';
});
$bonus_informasipromo = array_filter($informasipromo, function($promo) {
    return $promo['promo_type'] === 'bonus';
});

// Handle promo deletion
if (isset($_POST['delete_promo']) && isset($_POST['promo_id'])) {
    $promo_id = $_POST['promo_id'];
    $delete_sql = "DELETE FROM informasipromo WHERE id = ?";
    $delete_stmt = mysqli_prepare($kon, $delete_sql);
    mysqli_stmt_bind_param($delete_stmt, "i", $promo_id);
    
    if (mysqli_stmt_execute($delete_stmt)) {
        $_SESSION['success_message'] = "Promo berhasil dihapus!";
        header('Location: informasipromo.php');
        exit();
    } else {
        $_SESSION['error_message'] = "Gagal menghapus promo.";
    }
};
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Promo - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php include 'aset.php'; ?>
    <style>
        .promo-card {
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        .promo-card:hover {
            transform: scale(1.05);
        }
        .promo-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .promo-type-btn {
            background-color: #fff;
            color: #4154f1;
            border: 2px solid #4154f1;
            padding: 10px 20px;
            margin: 5px;
            border-radius: 20px;
            font-weight: bold;
            transition: all 0.3s;
        }
        .promo-type-btn.active {
            background-color: #4154f1;
            color: #fff;
        }
    </style>
    <?php include 'aset.php'; ?>
</head>
<body>
    <?php require "atas.php"; ?>
    <?php require "menu.php"; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1><i class="bi bi-megaphone"></i>&nbsp; MANAJEMEN PROMO</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">HOME</a></li>
                    <li class="breadcrumb-item active">PROMO</li>
                </ol>
            </nav>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_message']); endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message']); endif; ?>

        <section class="section">
            <div class="row mt-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title">Daftar Promo</h5>
                                <a href="tambah_promo.php" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Tambah Promo Baru
                                </a>
                            </div>
                            <div class="text-center mb-4">
                                <button class="promo-type-btn active" data-filter="all">Semua Promo</button>
                                <button class="promo-type-btn" data-filter="discount">Promo Diskon</button>
                                <button class="promo-type-btn" data-filter="bonus">Promo Bonus</button>
                            </div>
                            <div class="row" id="promoContainer">
                                <?php foreach ($informasipromo as $promo): ?>
                                <div class="col-md-3 promo-card" data-type="<?= htmlspecialchars($promo['promo_type']) ?>">
                                    <div class="card">
                                        <?php if (!empty($promo['photo_url'])): ?>
                                            <img src="<?= htmlspecialchars($promo['photo_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($promo['title']) ?>">
                                        <?php else: ?>
                                            <div class="card-img-top bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 200px;">No Image</div>
                                        <?php endif; ?>
                                        <div class="card-body">
                                            <?php if ($promo['promo_type'] === 'discount'): ?>
                                                <p class="card-text"><strong><p class="card-title"><?= htmlspecialchars($promo['title']) ?> Disc <?= $promo['discount_percentage'] ?>%</p></strong></p>
                                            <?php elseif ($promo['promo_type'] === 'bonus'): ?>
                                                <p class="card-text"><strong><p class="card-title"><?= htmlspecialchars($promo['title']) ?> Gratis <?= htmlspecialchars($promo['bonus_item']) ?></p></strong></p>
                                            <?php endif; ?>
                                            <p class="card-text"><?= htmlspecialchars($promo['description']) ?></p>
                                            <p class="card-text">
                                                <small class="text-muted">Berlaku sampai: <?= htmlspecialchars($promo['end_date']) ?></small>
                                            </p>
                                            <div class="d-flex">
                                                <a href="editpromo.php?id=<?= $promo['id'] ?>" class="btn btn-warning me-2">
                                                    <i class="bi bi-pencil"></i>Edit</a>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Anda yakin ingin menghapus promo ini?');">
                                                    <input type="hidden" name="promo_id" value="<?= $promo['id'] ?>">
                                                    <button type="submit" name="delete_promo" class="btn btn-danger"><i class="bi bi-trash"></i> Hapus</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Promo type filtering
            const filterButtons = document.querySelectorAll('.promo-type-btn');
            const promoCards = document.querySelectorAll('.promo-card');

            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');

                    const filter = this.getAttribute('data-filter');

                    promoCards.forEach(card => {
                        const cardType = card.getAttribute('data-type');
                        
                        if (filter === 'all' || cardType === filter) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>