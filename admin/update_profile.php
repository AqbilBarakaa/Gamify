<?php
session_start();
require "../db.php";

if (!isset($_POST['admin_id'])) {
    header("Location: profil.php");
    exit;
}

$admin_id = $_POST['admin_id'];
$nama = mysqli_real_escape_string($kon, $_POST['nama']);
$email = mysqli_real_escape_string($kon, $_POST['email']);
$no_tlp = mysqli_real_escape_string($kon, $_POST['no_tlp']);
$alamat = mysqli_real_escape_string($kon, $_POST['alamat']);
$foto_lama = isset($_POST['foto_lama']) ? $_POST['foto_lama'] : null;

// Proses Upload Foto Baru
$foto_baru = $foto_lama; // Default: gunakan foto lama jika tidak ada file baru
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $target_dir = "../uploads/";
    $foto_baru = time() . "_" . basename($_FILES['foto']['name']);
    $target_file = $target_dir . $foto_baru;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Validasi file
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $allowed_types)) {
        $_SESSION['error'] = "Format file tidak valid. Hanya JPG, JPEG, PNG, atau GIF yang diperbolehkan.";
        header("Location: profil.php");
        exit;
    }

    // Upload file
    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
        $_SESSION['error'] = "Gagal mengupload file. Silakan coba lagi.";
        header("Location: profil.php");
        exit;
    }

    // Hapus foto lama jika ada
    if ($foto_lama && file_exists("../uploads/" . $foto_lama)) {
        unlink("../uploads/" . $foto_lama);
    }
}

// Update data di database
$query = "UPDATE user SET 
            nama = '$nama', 
            email = '$email', 
            no_tlp = '$no_tlp', 
            alamat = '$alamat', 
            foto = '$foto_baru' 
          WHERE id_user = '$admin_id'";

if (mysqli_query($kon, $query)) {
    $_SESSION['success'] = "Profil berhasil diperbarui.";
    header("Location: profil.php");
} else {
    $_SESSION['error'] = "Terjadi kesalahan saat memperbarui profil: " . mysqli_error($kon);
    header("Location: profil.php");
}
?>
