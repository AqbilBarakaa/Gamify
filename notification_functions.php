Coba AI secara langsung di aplikasi favorit Anda … Gunakan Gemini untuk membuat draf dan menyempurnakan konten, serta dapatkan Gemini Advanced dengan akses ke AI generasi berikutnya dari Google seharga Rp 309.000,00 Rp 154.500,00 selama 2 bulan
<?php
function createNotification($kon, $type, $title, $message, $user_id = null, $id_produk = null, $image = null) {
    $query = "INSERT INTO notifications (type, title, message, id_user, id_produk, image) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($kon, $query);
    mysqli_stmt_bind_param($stmt, "sssiis", $type, $title, $message, $user_id, $id_produk, $image);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

if (!function_exists('checkProductStock')) {
    function checkProductStock($kon) {
        $query = "SELECT id_produk, nama_produk, stok FROM produk WHERE stok < 15";
        $result = mysqli_query($kon, $query);
        
        while ($product = mysqli_fetch_assoc($result)) {
            createNotification($kon, 'admin', 'Stok Produk Rendah', 
                "Produk {$product['nama_produk']} tersisa {$product['stok']} unit", 
                null, $product['id_produk']);
        }
    }
}

function monitorPesananChanges($kon) {
    // Ambil pesanan yang belum dinotifikasi (baik pesanan baru maupun perubahan status)
    $query = "SELECT p.*, u.nama AS nama_user 
              FROM pesanan p
              JOIN user u ON p.id_user = u.id_user
              WHERE p.notifikasi_status = 0 
              OR (p.status_pesanan IN ('Diproses', 'Dikirim', 'dibatalkan', 'Selesai') 
                  AND p.notifikasi_status = 0)";
    $result = mysqli_query($kon, $query);
    
    while ($pesanan = mysqli_fetch_assoc($result)) {
        // Notifikasi untuk pesanan baru (jika notifikasi_status = 0)
        if ($pesanan['notifikasi_status'] == 0 AND $pesanan['status_pesanan'] == 'Diproses') {
            // Notifikasi untuk admin
            createNotification(
                $kon, 
                'admin', 
                'Pesanan Baru', 
                "Pesanan baru dari {$pesanan['nama_user']} dengan total Rp " . 
                    number_format($pesanan['total_harga'], 0, ',', '.'), 
                null, 
                null
            );
            
            // Notifikasi untuk user
            createNotification(
                $kon, 
                'user', 
                'Pesanan Diterima', 
                "Pesanan Anda telah kami terima dan sedang diproses. " .
                "Total pembayaran: Rp " . number_format($pesanan['total_harga'], 0, ',', '.'), 
                $pesanan['id_user'], 
                null
            );
        }
        
        // Notifikasi perubahan status (jika status_notifikasi = 0)
        if ($pesanan['notifikasi_status'] == 0 && 
            in_array($pesanan['status_pesanan'], ['Dikirim', 'Dibatalkan', 'Selesai'])) {
            
            // Tentukan pesan berdasarkan status
            switch ($pesanan['status_pesanan']) {
                case 'Dikirim':
                    $title = 'Pesanan Dikirim';
                    $message = "Pesanan #{$pesanan['id_pesanan']} sedang dalam pengiriman. " .
                              "Mohon tunggu sampai pesanan tiba.";
                    break;
                    
                case 'Dibatalkan':
                    $title = 'Pesanan Dibatalkan';
                    $message = "Pesanan #{$pesanan['id_pesanan']} telah dibatalkan. " .
                              "Mohon hubungi kami jika ada pertanyaan.";
                    mysqli_query($kon, "UPDATE pesanan SET notifikasi_status = 1 WHERE id_pesanan = {$pesanan['id_pesanan']} AND status_pesanan = 'Dibatalkan'");
                    break;
                    
                case 'Selesai':
                    $title = 'Pesanan Selesai';
                    $message = "Pesanan #{$pesanan['id_pesanan']} telah selesai. " .
                              "Terima kasih telah berbelanja!";
                    mysqli_query($kon, "UPDATE pesanan SET notifikasi_status = 1 WHERE id_pesanan = {$pesanan['id_pesanan']} AND status_pesanan = 'Selesai'");
                    break;
            }
            
            // Kirim notifikasi ke user
            createNotification(
                $kon, 
                'user', 
                $title, 
                $message, 
                $pesanan['id_user'], 
                null
            );
        }
    }
}

function monitorReviewChanges($kon) {
    // Ambil review terbaru yang belum dinotifikasi
    $query = "SELECT r.*, u.nama AS nama_user, p.nama_produk 
              FROM review_produk r
              JOIN user u ON r.id_user = u.id_user
              JOIN produk p ON r.id_produk = p.id_produk
              WHERE r.notifikasi_status = 0";
    $result = mysqli_query($kon, $query);
    
    while ($review = mysqli_fetch_assoc($result)) {
        // Notifikasi untuk Admin
        createNotification($kon, 'admin', 'Review Baru', 
            "Review baru dari {$review['nama_user']} untuk produk {$review['nama_produk']} 
            dengan rating {$review['rating']}/5", 
            null, $review['id_produk']);
        
        // Update status notifikasi
        $update_query = "UPDATE review_produk SET notifikasi_status = 1 WHERE id_review = ?";
        $stmt = mysqli_prepare($kon, $update_query);
        mysqli_stmt_bind_param($stmt, "i", $review['id_review']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

function checkWishlistRestock($kon, $user_id) {
    // Ambil wishlist produk yang kosong sebelumnya dan sekarang tersedia
    $query = "SELECT w.id_produk, p.nama_produk 
              FROM wishlist w
              JOIN produk p ON w.id_produk = p.id_produk
              WHERE w.user_id = ? AND p.stok > 0 
              AND w.notifikasi_restock = 0";
    $stmt = mysqli_prepare($kon, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($produk = mysqli_fetch_assoc($result)) {
        // Periksa apakah notifikasi sudah dibuat sebelumnya
        $query_notif = "SELECT * FROM notifications WHERE type = 'user' AND title = 'Produk Tersedia' AND message LIKE ? AND user_id = ?";
        $stmt_notif = mysqli_prepare($kon, $query_notif);
        $message_pattern = "%{$produk['nama_produk']}%";
        mysqli_stmt_bind_param($stmt_notif, "si", $message_pattern, $user_id);
        mysqli_stmt_execute($stmt_notif);
        $result_notif = mysqli_stmt_get_result($stmt_notif);
        
        if (mysqli_num_rows($result_notif) == 0) {
            // Jika notifikasi belum dibuat, maka buat notifikasi
            createNotification($kon, 'user', 'Produk Tersedia', 
                "Produk {$produk['nama_produk']} di wishlist Anda sekarang tersedia!", 
                $user_id, $produk['id_user']);
        }
        
        // Update status notifikasi restock
        $update_query = "UPDATE wishlist SET notifikasi_restock = 1 
                         WHERE user_id = ? AND id_produk = ?";
        $stmt_update = mysqli_prepare($kon, $update_query);
        mysqli_stmt_bind_param($stmt_update, "ii", $user_id, $produk['produk_id']);
        mysqli_stmt_execute($stmt_update);
        mysqli_stmt_close($stmt_update);
    }
}

function checkActivePromo($kon, $user_id) {
    // Ambil promo aktif dari tabel informasipromo
    $query = "SELECT title, description, promo_type, 
                     discount_percentage, bonus_item 
              FROM informasipromo 
              WHERE start_date <= CURRENT_DATE 
              AND end_date >= CURRENT_DATE 
              AND (promo_type = 'discount' OR promo_type = 'bonus')";
    $result = mysqli_query($kon, $query);
    
    while ($promo = mysqli_fetch_assoc($result)) {
        // Gunakan prepared statement untuk memeriksa notifikasi sebelumnya
        $query_notif = "SELECT * FROM notifications 
                        WHERE type = 'user' 
                        AND title = 'Promo Tersedia' 
                        AND message LIKE ? 
                        AND id_user = ?";
        $stmt_notif = mysqli_prepare($kon, $query_notif);
        $message_pattern = "%{$promo['title']}%";
        mysqli_stmt_bind_param($stmt_notif, "si", $message_pattern, $user_id);
        mysqli_stmt_execute($stmt_notif);
        $result_notif = mysqli_stmt_get_result($stmt_notif);
        
        if (mysqli_num_rows($result_notif) == 0) {
            // Buat pesan notifikasi berdasarkan tipe promo
            $notifMessage = '';
            if ($promo['promo_type'] == 'discount') {
                $notifMessage = "Promo {$promo['title']} sedang berlangsung! Diskon hingga {$promo['discount_percentage']}%. {$promo['description']}";
            } elseif ($promo['promo_type'] == 'bonus') {
                $notifMessage = "Promo {$promo['title']} sedang berlangsung! Dapatkan bonus {$promo['bonus_item']}. {$promo['description']}";
            }
            
            createNotification($kon, 'user', 'Promo Tersedia', 
                $notifMessage, 
                $user_id);
        }
        
        // Tutup statement
        mysqli_stmt_close($stmt_notif);
    }
}