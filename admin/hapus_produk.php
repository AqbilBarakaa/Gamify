<?php
session_start();
include('../db.php'); // Pastikan koneksi database tersambung

// Periksa apakah ID produk diberikan
if (isset($_POST['product_id'])) {
    $productId = $_POST['product_id'];

    // Query untuk menghapus produk dari database
    $query = "DELETE FROM produk WHERE id_produk = ?";
    $stmt = mysqli_prepare($kon, $query);
    mysqli_stmt_bind_param($stmt, "i", $productId); // Menggunakan ID produk sebagai parameter

    if (mysqli_stmt_execute($stmt)) {
        // Hapus produk dari session jika ada
        if (isset($_SESSION['products'][$productId])) {
            unset($_SESSION['products'][$productId]);
            $_SESSION['products'] = array_values($_SESSION['products']); // Reindex array
        }

        // Redirect dengan pesan sukses
        header("Location: produk.php?delete_success=1");
        exit();
    } else {
        // Redirect dengan pesan error
        header("Location: produk.php?delete_error=1");
        exit();
    }
} else {
    // Jika tidak ada ID yang diberikan
    header("Location: produk.php?delete_error=1");
    exit();
}
?>