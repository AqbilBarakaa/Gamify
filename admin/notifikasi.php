<?php
session_start();
include '../db.php';
include '../notification_functions.php';

// Fungsi untuk memeriksa stok produk
function checkProductStock($kon) {
    $query = "SELECT id_produk, nama_produk, stok FROM produk WHERE stok < 15";
    $result = mysqli_query($kon, $query);
    
    while ($product = mysqli_fetch_assoc($result)) {
        // Periksa apakah notifikasi sudah dibuat sebelumnya
        $query_notif = "SELECT * FROM notifications WHERE type = 'admin' AND title = 'Stok Produk Rendah' AND message LIKE '%{$product['nama_produk']}%' AND id_produk = {$product['id_produk']}";
        $result_notif = mysqli_query($kon, $query_notif);
        
        if (mysqli_num_rows($result_notif) == 0) {
            // Jika notifikasi belum dibuat, maka buat notifikasi
            createNotification($kon, 'admin', 'Stok Produk Rendah', 
                "Produk {$product['nama_produk']} tersisa {$product['stok']} unit", 
                null, $product['id_produk']);
        }
    }
}

// Validasi Login Admin
if (!isset($_SESSION['admin']) || empty($_SESSION['admin'])) {
    echo "<script>
            alert('Anda harus login sebagai admin');
            window.location.href='../login.php';
          </script>";
    exit();
}

// Generate admin notifications
checkProductStock($kon);
monitorPesananChanges($kon);
monitorReviewChanges($kon);

// Tandai Semua Notifikasi Dibaca
if (isset($_POST['mark_all_read'])) {
    try {
        $query_mark = "UPDATE notifications SET is_read = 1 WHERE type = 'admin'";
        $stmt = mysqli_prepare($kon, $query_mark);
        mysqli_stmt_execute($stmt);
        
        if (mysqli_stmt_error($stmt)) {
            throw new Exception("Error marking notifications as read");
        }
        
        mysqli_stmt_close($stmt);
        
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } catch (Exception $e) {
        error_log($e->getMessage());
    }
}

// Hapus Semua Notifikasi
if (isset($_POST['clear_notifications'])) {
    try {
        $query_clear = "DELETE FROM notifications WHERE type = 'admin'";
        $stmt = mysqli_prepare($kon, $query_clear);
        mysqli_stmt_execute($stmt);
        
        if (mysqli_stmt_error($stmt)) {
            throw new Exception("Error clearing notifications");
        }
        
        mysqli_stmt_close($stmt);
        
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } catch (Exception $e) {
        error_log($e->getMessage());
    }
}

// Query untuk Menampilkan Notifikasi
try {
    $query_notif = "SELECT * FROM notifications WHERE type = 'admin' ORDER BY created_at DESC LIMIT 5000";
    $result_notif = mysqli_query($kon, $query_notif);

    // Hitung Notifikasi Belum Dibaca
    $query_unread = "SELECT COUNT(*) as unread_count FROM notifications WHERE type = 'admin' AND is_read = 0";
    $result_unread = mysqli_query($kon, $query_unread);
    $unread = mysqli_fetch_assoc($result_unread)['unread_count'];
} catch (Exception $e) {
    error_log($e->getMessage());
    $result_notif = null;
    $unread = 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .notification-container {
            max-width: 800px;
            margin: 30px auto;
        }
        .notification-card {
            margin-bottom: 15px;
            transition: all 0.3s ease;
            position: relative;
        }
        .notification-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
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
    </style>
    <?php include 'aset.php'; ?>
</head>
<body>
    <?php 
    require "atas.php"; 
    require "menu.php"; 
    ?>    
    <main id="main" class="main">
        <div class="container notification-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    Notifikasi Admin
                    <?php if ($unread > 0): ?>
                        <span class="badge bg-danger"><?= $unread ?></span>
                    <?php endif; ?>
                </h2>
                
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

            <?php if (!$result_notif || mysqli_num_rows($result_notif) == 0): ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <p>Tidak ada notifikasi</p>
                </div>
            <?php else: ?>
                <?php 
                // Reset internal pointer
                mysqli_data_seek($result_notif, 0);
                while ($notif = mysqli_fetch_assoc($result_notif)): 
                ?>
                    <div class="card notification-card <?= $notif['is_read'] == 0 ? 'unread' : '' ?>">
                        <div class="card-body d-flex">
                            <div class="notification-icon">
                                <?php 
                                $icon = match(true) {
                                    stripos($notif['title'], 'Pesanan') !== false => 'fa-shopping-cart',
                                    stripos($notif['title'], 'Review') !== false => 'fa-comment',
                                    stripos($notif['title'], 'Stok') !== false => 'fa-box-open',
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
                                
                            
                                    <a href="detail_produk.php?product_id=<?= $notif['id_produk'] ?>" class="btn btn-sm btn-outline-primary mt-2">
                                        Lihat Produk
                                    </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>