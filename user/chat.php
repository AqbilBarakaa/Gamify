<?php
session_start();
include '../db.php';
$page = "chat";

// Pastikan user telah login dan session user_id tersedia
if (!isset($_SESSION['user'])) {
    die("Harap login terlebih dahulu.");
}

$user = $_SESSION["user"];
$kue_user = mysqli_query($kon, "SELECT * FROM user WHERE nama = '$user'");
$row_user = mysqli_fetch_array($kue_user);

$user_id = $row_user['id_user'];

// Ambil nama admin untuk ditampilkan di chat
$admin_query = "SELECT nama FROM user WHERE level='admin' LIMIT 1";
$admin_result = mysqli_query($kon, $admin_query);
$admin_data = mysqli_fetch_assoc($admin_result);
$admin_name = $admin_data['nama'] ?? 'Admin';

// Jika form dikirim
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = mysqli_real_escape_string($kon, $_POST['message']);
    $query = "INSERT INTO messages (sender, user_id, message) VALUES ('user', '$user_id', '$message')";
    if (!mysqli_query($kon, $query)) {
        echo "Error: " . mysqli_error($kon);
    }
}

// Ambil pesan antara user dan admin
$query = "SELECT * FROM messages WHERE user_id='$user_id' ORDER BY timestamp";
$result = mysqli_query($kon, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>CUSOMER SERVICE</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

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
        body {
            background-color: #f5f5f5;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom : 20px;
            min-height: 450px; /* Ukuran kartu seragam */
        }
        .card-img-top {
            height: 250px; /* Ukuran gambar seragam */
            object-fit: cover; /* Memastikan gambar tidak terdistorsi */
            border-radius: 15px 15px 0 0;
        }
        .card-body {
            min-height: 150px; /* Menyesuaikan tinggi deskripsi produk agar seragam */
        }
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        .btn-wishlist {
            margin-top: 10px;
        }
    </style>
    <?php include 'aset.php'; ?>
</head>

<body>
  <!-- ======= Header ======= -->
  <?php require "atas.php"; ?>
  <!-- End Header -->

  <!-- ======= Sidebar ======= -->
  <?php require "menu.php"; ?>
  <!-- End Sidebar-->
  <main id="main" class="main">
    <div class="container mt-4">
        <h2>Customer Service</h2>
        <div class="border rounded p-3 mb-3" style="height: 400px; overflow-y: auto;">
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <div class="d-flex <?php echo $row['sender'] == 'user' ? 'justify-content-end' : 'justify-content-start'; ?> mb-2">
                    <div class="p-2 <?php echo $row['sender'] == 'user' ? 'bg-primary text-white' : 'bg-light'; ?> rounded" style="max-width: 70%;">
                        <?php echo htmlspecialchars($row['message']); ?>
                    </div>
                </div>
            <?php } ?>
        </div>
        <form method="POST">
            <div class="input-group">
                <input type="text" name="message" class="form-control" placeholder="Ketik pesan..." required>
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit">Kirim</button>
                </div>
            </div>
        </form>
    </div>
</main>
<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

<!-- Vendor JS Files -->
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
