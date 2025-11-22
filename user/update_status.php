<?php
session_start();
include('../db.php'); // Include database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pesanan = intval($_POST['id_pesanan']);
    $status = mysqli_real_escape_string($kon, $_POST['status']);

    // Validate status
    if ($status !== 'selesai') {
        echo json_encode(['success' => false, 'message' => 'Status tidak valid']);
        exit();
    }

    // Update query
    $query = "UPDATE pesanan SET status_pesanan = ? WHERE id_pesanan = ?";
    $stmt = mysqli_prepare($kon, $query);
    mysqli_stmt_bind_param($stmt, 'si', $status, $id_pesanan);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mengupdate status']);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($kon);
}
?>
