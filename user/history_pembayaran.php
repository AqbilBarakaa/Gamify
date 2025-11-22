<?php
session_start();
include('../db.php'); // Koneksi ke database
$page = "history_pembayaran";

// Pastikan user login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$userName = $_SESSION['user'];
$kue_user = mysqli_query($kon, "SELECT * FROM user WHERE nama = '$userName'");
$row_user = mysqli_fetch_array($kue_user);
$userId = $row_user['id_user']; 

// Tambahkan penanganan filter tanggal dengan validasi
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

// Validasi rentang tanggal
$dateQuery = "";
$bindTypes = "i";
$bindParams = [&$userId];

if ($start_date && $end_date) {
    // Pastikan start_date tidak lebih besar dari end_date
    if (strtotime($start_date) <= strtotime($end_date)) {
        $dateQuery = " AND p.tanggal_pesanan BETWEEN ? AND ?";
        $bindTypes = "iss";
        $bindParams[] = &$start_date;
        $bindParams[] = &$end_date;
    } else {
        // Jika tanggal tidak valid, reset filter
        $start_date = null;
        $end_date = null;
    }
}

// Perbaikan query dengan filter tanggal
$sql = "SELECT DISTINCT p.id_pesanan, p.total_harga, p.status_pesanan, p.tanggal_pesanan,
        pb.metode_pembayaran, pb.status_pembayaran, 
        pg.alamat_pengiriman, pg.nomor_resi, pg.nama_kurir, pg.tanggal_kirim, pg.perkiraan_tiba,
        pr.nama_produk, pr.harga AS harga_produk, pr.gambar,
        p.jumlah AS jumlah_produk,
        r.id_pesanan AND r.id_review AS sudah_dinilai
        FROM pesanan p
        LEFT JOIN pembayaran pb ON p.id_pesanan = pb.id_pesanan
        LEFT JOIN pengiriman_pesanan pg ON p.id_pesanan = pg.id_pesanan
        LEFT JOIN produk pr ON p.id_produk = pr.id_produk
        LEFT JOIN review_produk r ON p.id_user = r.id_user AND p.id_produk = r.id_produk AND p.id_pesanan = r.id_pesanan
        WHERE p.id_user = ? 
        $dateQuery
        GROUP BY p.id_pesanan
        ORDER BY p.tanggal_pesanan DESC";

// Persiapkan statement
$stmt = mysqli_prepare($kon, $sql);
if (!$stmt) {
    error_log("Prepare statement gagal: " . mysqli_error($kon));
    die("Terjadi kesalahan prepare statement: " . mysqli_error($kon));
}

// Bind parameter dinamis
$refs = [];
foreach ($bindParams as $key => $value) {
    $refs[$key] = &$bindParams[$key];
}

// Gunakan call_user_func_array untuk binding parameter
call_user_func_array(
    [$stmt, 'bind_param'], 
    array_merge([$bindTypes], $refs)
);

// Eksekusi query
if (!mysqli_stmt_execute($stmt)) {
    error_log("Eksekusi query gagal: " . mysqli_stmt_error($stmt));
    die("Terjadi kesalahan eksekusi query: " . mysqli_stmt_error($stmt));
}

$result = mysqli_stmt_get_result($stmt);
if (!$result) {
    error_log("Pengambilan hasil query gagal: " . mysqli_stmt_error($stmt));
    die("Terjadi kesalahan pengambilan hasil: " . mysqli_stmt_error($stmt));
}

$orders = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Tambahkan pengecekan jika tidak ada pesanan
$noOrdersMessage = empty($orders) ? "Tidak ada pesanan dalam rentang tanggal yang dipilih" : null;

// Bersihkan resources
mysqli_stmt_close($stmt);
mysqli_free_result($result);

$pembatalanQuery = "SELECT * FROM pembatalan_pesanan";
$resultPembatalan = mysqli_query($kon, $pembatalanQuery);
$alasanPembatalan = mysqli_fetch_all($resultPembatalan, MYSQLI_ASSOC);

$result_enum = mysqli_query($kon, "SHOW COLUMNS FROM pembatalan_pesanan LIKE 'alasan_pembatalan'");
$row_enum = mysqli_fetch_array($result_enum);

// Ambil daftar nilai ENUM
$enum_values = [];
if ($row_enum) {
    $enum_string = $row_enum['Type']; // e.g., enum('berubah_pikiran','harga_lebih_murah',...)
    preg_match_all("/'([^']+)'/", $enum_string, $matches);
    $enum_values = $matches[1]; // Array dari nilai ENUM
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Histori Pembelian</title>
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
        /* Card & Table Styling */
        .table {
            background: #fff;
            border-radius: 8px;
            margin: 15px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .table th {
            background: #f0f0f0;
            color: #333;
            padding: 10px;
            text-transform: uppercase;
            font-size: 0.9rem;
        }

        .table td {
            padding: 10px;
            vertical-align: middle;
            background: #fff;
        }

        .table tr:hover td {
            background: #f9f9f9;
        }

        /* Product Styling */
        .product-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }

        .product-name {
            font-weight: 500;
            color: #333;
        }

        /* Button Styling */
        .btn-detail {
            background: none;
            border: none;
            color: #007bff;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .btn-detail:hover {
            color: #0056b3;
        }

        /* Detail Panel */
       /* Tambahkan ini untuk memastikan warna h6 tetap terlihat */
        .collapse h6 {
            color: #333; /* Atur ulang warna teks */
            font-weight: 600; /* Atur ketebalan huruf */
            margin: 10px 0; /* Jarak atas dan bawah */
        }

        /* Tambahkan gaya lain jika perlu */
        .collapse td {
            background: #f8f9fa; /* Latar belakang kolom collapse */
            padding: 15px; /* Padding pada kolom */
        }

        .rotate {
            transform: rotate(180deg);
            transition: transform 0.3s ease;
        }

        .card-title {
            margin-bottom: 20px; /* Jarak bawah untuk judul */
            font-size: 1.5rem; /* Ukuran font untuk judul */
            font-weight: 600; /* Ketebalan huruf */
            color: #333; /* Warna teks */
        }

        /* Tambahkan ini untuk mengubah kursor menjadi pointer pada input date */
        input[type="date"] {
            cursor: pointer; /* Mengubah kursor menjadi pointer */
        }

        /* Tambahkan ini untuk menyamakan ukuran tombol dengan input date */
        .input-group .btn {
            height: calc(2.25rem + 2px); /* Sesuaikan tinggi tombol dengan input */
            padding: 0.375rem 0.75rem; /* Sesuaikan padding tombol */
        }

    </style>
</head>
<body>
    <!-- Header -->
    <?php require "atas.php"; ?>
    <!-- Sidebar -->
    <?php require "menu.php"; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1><i class="bi bi-box-arrow-in-left"></i> Histori Pembelian</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Histori Pembelian</li>
                </ol>
            </nav>
        </div>
        
        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="card-title">Filter Pesanan</h2>
                            <form method="GET" action="" onsubmit="return validateDateRange()">
                                <div class="input-group mb-3">
                                    <input type="date" name="start_date" id="start_date" class="form-control" 
                                           placeholder="Tanggal Mulai" 
                                           value="<?= htmlspecialchars($start_date ?? ''); ?>" 
                                           required>
                                    <input type="date" name="end_date" id="end_date" class="form-control" 
                                           placeholder="Tanggal Akhir" 
                                           value="<?= htmlspecialchars($end_date ?? ''); ?>" 
                                           required>
                                    <button class="btn btn-primary" type="submit">Filter</button>
                                    <?php if ($start_date && $end_date): ?>
                                        <a href="history_pembayaran.php" class="btn btn-secondary">Reset</a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                        <h2 class="card-title">Daftar Pesanan</h2>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Detail</th>
                                            <th>Tanggal</th>
                                            <th>Produk</th>
                                            <th>Jumlah</th>
                                            <th>Harga</th>
                                            <th>Total</th>
                                            <th>Status Pesanan</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php if ($noOrdersMessage): ?>
                                        <div class="alert alert-info" role="alert">
                                            <?= $noOrdersMessage ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php foreach ($orders as $order): ?>
                                            <tr>
                                                <td>
                                                <button class="btn btn-link btn-detail" type="button" 
                                                    data-bs-toggle="collapse" 
                                                    data-bs-target="#collapseDetail<?= $order['id_pesanan'] ?>" 
                                                    aria-expanded="false" 
                                                    aria-controls="collapseDetail<?= $order['id_pesanan'] ?>">
                                                    <i class="bi bi-chevron-down"></i>
                                                </button>


                                                </td>
                                                <td><?= date('d/m/Y', strtotime($order['tanggal_pesanan'])); ?></td>
                                                <td>
                                                    <div class="product-container">
                                                        <img src="../uploads/<?= $order['gambar']; ?>" 
                                                             alt="<?= htmlspecialchars($order['nama_produk']); ?>" 
                                                             class="product-img">
                                                        <div class="product-name">
                                                            <?= htmlspecialchars($order['nama_produk']); ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?= htmlspecialchars($order['jumlah_produk']); ?></td>
                                                <td>Rp <?= number_format($order['harga_produk'], 0, ',', '.'); ?></td>
                                                <td>Rp <?= number_format($order['total_harga'], 0, ',', '.'); ?></td>
                                                <td><?= htmlspecialchars($order['status_pesanan']); ?></td>
                                                <td>
                                                    <!-- Selesai Button -->
                                                    <?php if ($order['status_pesanan'] !== 'Selesai' AND $order['status_pesanan'] !== 'Dibatalkan'  AND $order['status_pesanan'] !== 'Diproses'): ?>
                                                        <button class="btn btn-warning btn-sm selesai-btn" 
                                                                data-id="<?= $order['id_pesanan']; ?>">Selesai</button>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Nilai Button -->
                                                    <?php if ($order['status_pesanan'] === 'Selesai' && empty($order['sudah_dinilai'])): ?>
                                                        <a href="review.php?id=<?= $order['id_pesanan'] ?>" 
                                                           class="btn btn-primary btn-sm">Nilai</a>
                                                    <?php endif; ?>

                                                    <!-- Beli Lagi Button -->
                                                    <?php if ($order['status_pesanan'] === 'Selesai' && !empty($order['sudah_dinilai'])): ?>
                                                        <a href="beli_lagi.php?id=<?= $order['id_pesanan'] ?>" 
                                                           class="btn btn-success btn-sm">Beli Lagi</a>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Lacak Button -->
                                                    <?php if ($order['status_pesanan'] !== 'Selesai'  AND $order['status_pesanan'] !== 'Dibatalkan' AND $order['status_pesanan'] !== 'Diproses' ): ?>
                                                        <a href="lacak.php?id=<?= $order['id_pesanan'] ?>" 
                                                           class="btn btn-info btn-sm">Lacak</a>
                                                    <?php endif; ?>
                                                    <!-- Tombol Batalkan -->
                                                    <?php if ($order['status_pesanan'] === 'Diproses'): ?>
                                                       <a href="form_pembatalan.php?id_pesanan=<?= urlencode($order['id_pesanan']); ?>"class="btn btn-danger btn-sm batalkan-btn" >Batalkan</a>
                                                    <?php endif; ?>
                                                </td>


                                            </tr>
                                            <tr class="collapse" id="collapseDetail<?= $order['id_pesanan'] ?>">
                                                <td colspan="9">
                                                    <div class="p-3">
                                                        <h6>Informasi Pembayaran</h6>
                                                        <p>
                                                            <strong>Metode Pembayaran:</strong> <?= htmlspecialchars($order['metode_pembayaran']); ?><br>
                                                            <strong>Status Pembayaran:</strong> <?= htmlspecialchars($order['status_pembayaran']); ?>
                                                        </p>
                                                        <h6>Informasi Pengiriman</h6>
                                                        <p>
                                                            <strong>Alamat:</strong> <?= htmlspecialchars($order['alamat_pengiriman'] ?? 'Tidak tersedia'); ?><br>
                                                            <strong>Nomor Resi:</strong> <?= htmlspecialchars($order['nomor_resi'] ?? 'Tidak tersedia'); ?><br>
                                                            <strong>Ekspedisi:</strong> <?= htmlspecialchars($order['nama_kurir'] ?? 'Tidak tersedia'); ?><br>
                                                            <strong>Tanggal Kirim:</strong> <?= htmlspecialchars($order['tanggal_kirim'] ?? 'Tidak tersedia'); ?><br>
                                                            <strong>Perkiraan Tiba:</strong> <?= htmlspecialchars($order['perkiraan_tiba'] ?? 'Tidak tersedia'); ?><br>
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <!-- Vendor JS Files -->
    <script src="../assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/vendor/chart.js/chart.min.js"></script>
    <script src="../assets/vendor/echarts/echarts.min.js"></script>
    <script src="../assets/vendor/quill/quill.min.js"></script>
    <script src="../assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="../assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="../assets/vendor/php-email-form/validate.js"></script>
                                            
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const detailButtons = document.querySelectorAll('.btn-detail');

            detailButtons.forEach(button => {
                const targetId = button.getAttribute('data-bs-target');
                const targetCollapse = document.querySelector(targetId);

                // Tambahkan event listener untuk menunjukkan/menyembunyikan
                targetCollapse.addEventListener('show.bs.collapse', function () {
                    button.classList.add('rotate'); // Rotate saat buka
                });

                targetCollapse.addEventListener('hide.bs.collapse', function () {
                    button.classList.remove('rotate'); // Kembalikan rotasi saat tutup
                });

                // Pastikan satu tombol hanya mengontrol satu collapse
                button.addEventListener('click', function () {
                    const isCollapsed = targetCollapse.classList.contains('show');
                    detailButtons.forEach(btn => {
                        const collapse = document.querySelector(btn.getAttribute('data-bs-target'));
                        if (collapse !== targetCollapse && collapse.classList.contains('show')) {
                            collapse.classList.remove('show'); // Tutup semua collapse lain
                            btn.classList.remove('rotate');
                        }
                    });
                    if (isCollapsed) {
                        targetCollapse.classList.remove('show'); // Tutup target jika sedang terbuka
                        button.classList.remove('rotate');
                    } else {
                        targetCollapse.classList.add('show'); // Buka target jika tertutup
                        button.classList.add('rotate');
                    }
                });
            });
        });

    </script>
    
    <script>
    function validateDateRange() {
        const startDate = new Date(document.getElementById('start_date').value);
        const endDate = new Date(document.getElementById('end_date').value);
        
        if (startDate > endDate) {
            alert('Tanggal mulai harus sebelum atau sama dengan tanggal akhir');
            return false;
        }
        return true;
    }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selesaiButtons = document.querySelectorAll('.selesai-btn');

            selesaiButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const pesananId = this.getAttribute('data-id');

                    if (confirm('Apakah Anda yakin ingin menyelesaikan pesanan ini?')) {
                        fetch('update_status.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `id_pesanan=${pesananId}&status=selesai`,
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Pesanan berhasil diselesaikan!');
                                location.reload(); // Reload to reflect changes
                            } else {
                                alert('Gagal menyelesaikan pesanan. Silakan coba lagi.');
                            }
                        })
                        .catch(err => {
                            console.error('Error:', err);
                            alert('Terjadi kesalahan. Silakan coba lagi.');
                        });
                    }
                });
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Tangkap klik button Batalkan
            document.querySelectorAll('.batalkan-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const pesananId = this.getAttribute('data-id');
                    document.getElementById('id_pesanan').value = pesananId;
                    const batalkanModal = new bootstrap.Modal(document.getElementById('batalkanModal'));
                    batalkanModal.show();
                });
            });
        
            // Form Submit untuk pembatalan
            document.getElementById('formPembatalan').addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(this);
            
                fetch('proses_pembatalan.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Pesanan berhasil dibatalkan!');
                        location.reload();
                    } else {
                        alert('Gagal membatalkan pesanan. Silakan coba lagi.');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Terjadi kesalahan. Silakan coba lagi.');
                });
            });
        });
    </script>

</body>
</html>
