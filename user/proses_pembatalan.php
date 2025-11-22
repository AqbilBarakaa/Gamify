<?php
session_start();
require '../db.php'; // File koneksi database Anda

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $id_pesanan = $_POST['id_pesanan'] ?? '';
    $alasan_pembatalan = $_POST['alasan_pembatalan'] ?? '';
    $deskripsi_pembatalan = $_POST['deskripsi_pembatalan'] ?? '';

    // Validasi input
    if (empty($id_pesanan) || empty($alasan_pembatalan)) {
        $_SESSION['error'] = "Alasan pembatalan wajib dipilih.";
        header("Location: form_pembatalan.php?id_pesanan=$id_pesanan");
        exit();
    }

    // Tanggal pembatalan
    $tanggal_pembatalan = date('Y-m-d H:i:s');

    // Query transaksi: insert pembatalan dan update status pesanan
    $query = "INSERT INTO pembatalan_pesanan 
                (id_pesanan, id_user, alasan_pembatalan, deskripsi_pembatalan, tanggal_pembatalan, dibuat_pada) 
              VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP())";

    $query_update = "UPDATE pesanan SET status_pesanan = 'Dibatalkan' WHERE id_pesanan = ?";

    // Eksekusi query
    try {
        // Persiapkan koneksi
        $kon->autocommit(false); // Start transaksi

        // Insert ke pembatalan_pesanan
        $stmt = $kon->prepare($query);
        $id_user = 1; // Gantilah ini sesuai dengan user login
        $stmt->bind_param("iisss", $id_pesanan, $id_user, $alasan_pembatalan, $deskripsi_pembatalan, $tanggal_pembatalan);
        $stmt->execute();

        // Update status pesanan
        $stmt_update = $kon->prepare($query_update);
        $stmt_update->bind_param("i", $id_pesanan);
        $stmt_update->execute();

        // Commit transaksi
        $kon->commit();
        $_SESSION['message'] = "Pesanan berhasil dibatalkan.";
    } catch (Exception $e) {
        $kon->rollback();
        $_SESSION['error'] = "Gagal membatalkan pesanan: " . $e->getMessage();
    }

    // Redirect
    header("Location: history_pembayaran.php");
    exit();
}
?>
