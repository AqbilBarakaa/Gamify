<?php
session_start();
include '../db.php';
include '../notification_functions.php'; // Include the notification functions

// Validasi Login User
if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    echo "<script>
            alert('Anda harus login');
            window.location.href='../login.php';
          </script>";
    exit();
}

$user = $_SESSION["user"];
$kue_user = mysqli_query($kon, "SELECT * FROM user WHERE nama = '$user'");
$row_user = mysqli_fetch_array($kue_user);

// Ambil ID User yang sedang login
$user_id = $row_user['id_user'];
$user_name = $_SESSION['user'] ?? 'Pengguna';

// Generate notifications for user
checkWishlistRestock($kon, $user_id);
checkActivePromo($kon, $user_id);

// Proses Tandai Semua Dibaca
if (isset($_POST['mark_all_read'])) {
    try {
        $query_mark = "UPDATE notifications SET is_read = 1 WHERE type = 'user' AND id_user = ?";
        $stmt = mysqli_prepare($kon, $query_mark);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        
        if (mysqli_stmt_error($stmt)) {
            throw new Exception("Error marking notifications as read");
        }
        
        mysqli_stmt_close($stmt);
        
        // Redirect untuk mencegah pengiriman ulang form
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } catch (Exception $e) {
        // Log error or handle it appropriately
        error_log($e->getMessage());
        // Optionally show error to user
    }
}

// Hapus Notifikasi Lama
if (isset($_POST['clear_notifications'])) {
    try {
        $query_clear = "DELETE FROM notifications WHERE type = 'user' AND id_user = ?";
        $stmt = mysqli_prepare($kon, $query_clear);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        
        if (mysqli_stmt_error($stmt)) {
            throw new Exception("Error clearing notifications");
        }
        
        mysqli_stmt_close($stmt);
        
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } catch (Exception $e) {
        // Log error or handle it appropriately
        error_log($e->getMessage());
    }
}

// Query Ambil Notifikasi User
try {
    $query_notif = "SELECT 
                        id, 
                        title, 
                        message, 
                        is_read, 
                        created_at,
                        image,
                        id_produk
                    FROM notifications 
                    WHERE type = 'user' AND id_user = ?
                    ORDER BY created_at DESC 
                    LIMIT 50";

    $stmt_notif = mysqli_prepare($kon, $query_notif);
    mysqli_stmt_bind_param($stmt_notif, "i", $user_id);
    mysqli_stmt_execute($stmt_notif);
    $result_notif = mysqli_stmt_get_result($stmt_notif);

    // Hitung Notifikasi Belum Dibaca
    $query_unread = "SELECT COUNT(*) as unread_count 
                     FROM notifications 
                     WHERE type = 'user' AND id_user = ? AND is_read = 0";
    $stmt_unread = mysqli_prepare($kon, $query_unread);
    mysqli_stmt_bind_param($stmt_unread, "i", $user_id);
    mysqli_stmt_execute($stmt_unread);
    $result_unread = mysqli_stmt_get_result($stmt_unread);
    $unread = mysqli_fetch_assoc($result_unread)['unread_count'];
} catch (Exception $e) {
    // Log error or handle it appropriately
    error_log($e->getMessage());
    $result_notif = null;
    $unread = 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <!-- Meta Tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Pengguna</title>

    <!-- Favicon -->
    <link href="../assets/img/favicon.png" rel="icon">
    <link href="../assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- CSS Dependencies -->
    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="../assets/vendor/quill/quill.snow.css" rel="stylesheet">
    <link href="../assets/vendor/quill/quill.bubble.css" rel="stylesheet">
    <link href="../assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="../assets/vendor/simple-datatables/style.css" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="../assets/css/style.css" rel="stylesheet">
    
    <!-- Custom Styles -->
    <style>
        body {
            background-color: #f4f6f9;
        }
        .notification-container {
            max-width: 800px;
            margin: 30px auto;
        }
        .notification-card {
            margin-bottom: 15px;
            transition: all 0.3s ease;
            position: relative;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .notification-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        .notification-card.unread {
            border-left: 4px solid #007bff;
            background-color: #f1f7ff;
        }
        .notification-icon {
            position: absolute;
            top: 15px;
            left: 15px;
            font-size: 2rem;
            color: #007bff;
        }
        .notification-content {
            margin-left: 70px;
        }
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
    </style>

    <!-- Additional Assets -->
    <?php include 'aset.php'; ?>
</head>

<body>
    <!-- Header -->
    <?php require "atas.php"; ?>

    <!-- Sidebar -->
    <?php require "menu.php"; ?>

    <!-- Main Content -->
    <main id="main" class="main">
        <div class="container notification-container">
            <!-- Notification Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    Notifikasi <?= htmlspecialchars($user_name) ?>
                    <?php if ($unread > 0): ?>
                        <span class="badge bg-danger"><?= $unread ?></span>
                    <?php endif; ?>
                </h2>
                
                <!-- Notification Actions -->
                <?php if ($result_notif && mysqli_num_rows($result_notif) > 0): ?>
                    <div class="btn-group">
                        <form method="POST" class="me-2">
                            <button type="submit" name="mark_all_read" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-check-double"></i> Tandai Dibaca
                            </button>
                        </form>
                        <form method="POST">
                            <button type="submit" name="clear_notifications" class="btn btn-outline-danger btn-sm" 
                                    onclick="return confirm('Yakin ingin menghapus semua notifikasi?');">
                                <i class="fas fa-trash"></i> Hapus Semua
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Notification List -->
            <?php if (!$result_notif || mysqli_num_rows($result_notif) == 0): ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <p>Tidak ada notifikasi</p>
                </div>
            <?php else: ?>
                <?php 
                mysqli_data_seek($result_notif, 0);
                while ($notif = mysqli_fetch_assoc($result_notif)): 
                ?>
                    <!-- Individual Notification Card -->
                    <div class="card notification-card <?= $notif['is_read'] == 0 ? 'unread' : '' ?>">
                        <div class="card-body d-flex">
                            <div class="notification-icon">
                                <?php 
                                // Tentukan icon berdasarkan judul
                                $icon = match(true) {
                                    stripos($notif['title'], 'pesanan') !== false => 'fa-shopping-cart',
                                    stripos($notif['title'], 'pembayaran') !== false => 'fa-money-bill-wave',
                                    stripos($notif['title'], 'promo') !== false => 'fa-tags',
                                    stripos($notif['title'], 'pengiriman') !== false => 'fa-truck',
                                    stripos($notif['title'], 'tersedia') !== false => 'fa-box-open',
                                    default => 'fa-bell'
                                };
                                ?>
                                <i class="fas <?= $icon ?>"></i>
                            </div>
                            
                            <div class="notification-content flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="card-title mb-0">
                                        <?= htmlspecialchars($notif['title']) ?>
                                        <?php if($notif['is_read'] == 0): ?>
                                            <span class="badge bg-primary ms-2">Baru</span>
                                        <?php endif; ?>
                                    </h5>
                                    <small class="text-muted">
                                        <?= date('d M H:i', strtotime($notif['created_at'])) ?>
                                    </small>
                                </div>
                                <p class="card-text"><?= htmlspecialchars($notif['message']) ?></p>
                                <?php 
                                // Tambahkan link ke produk jika ada id_produk
                                if (!empty($notif['id_produk'])): ?>
                                    <a href="detail_produk.php?product_id=<?= $notif['id_produk'] ?>" class="btn btn-sm btn-outline-primary mt-2">
                                        Lihat Produk
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Back to Top Button -->
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
        <i class="bi bi-arrow-up-short"></i>
    </a>

    <!-- JavaScript Dependencies -->
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