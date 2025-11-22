<?php
session_start();
if (!isset($_GET['id_pesanan'])) {
    die("ID Pesanan tidak ditemukan.");
}
$id_pesanan = $_GET['id_pesanan'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Form Pembatalan Pesanan</title>
    <!-- Bootstrap CSS -->
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- HEADER -->
    <?php include 'atas.php'; ?>
    <!-- SIDEBAR -->
    <?php include 'menu.php'; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1><i class="bi bi-x-circle"></i> Pembatalan Pesanan</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Form Pembatalan</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Ajukan Pembatalan Pesanan</h5>
                        </div>
                        <div class="card-body">
                            <form action="proses_pembatalan.php" method="POST">
                                <!-- ID Pesanan (Hidden) -->
                                <input type="hidden" name="id_pesanan" value="<?= htmlspecialchars($id_pesanan); ?>">

                                <!-- Alasan Pembatalan -->
                                <div class="mb-3">
                                    <label for="alasan" class="form-label">Alasan Pembatalan</label>
                                    <select name="alasan_pembatalan" id="alasan" class="form-select" required>
                                        <option value="">-- Pilih Alasan --</option>
                                        <option value="berubah_pikiran">Berubah Pikiran</option>
                                        <option value="harga_lebih_murah">Harga Lebih Murah</option>
                                        <option value="barang_salah">Barang Salah</option>
                                        <option value="pengiriman_lama">Pengiriman Lama</option>
                                        <option value="masalah_pembayaran">Masalah Pembayaran</option>
                                        <option value="lainnya">Lainnya</option>
                                    </select>
                                </div>

                                <!-- Deskripsi Pembatalan -->
                                <div class="mb-3">
                                    <label for="deskripsi" class="form-label">Deskripsi Pembatalan</label>
                                    <textarea name="deskripsi_pembatalan" id="deskripsi" class="form-control" rows="4" placeholder="Jelaskan alasan pembatalan..."></textarea>
                                </div>

                                <!-- Tombol Submit -->
                                <div class="text-end">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="bi bi-send"></i> Kirim Pembatalan
                                    </button>
                                    <a href="index.php" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> Kembali
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Back to Top Button -->
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
        <i class="bi bi-arrow-up-short"></i>
    </a>

    <!-- Bootstrap JS -->
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
