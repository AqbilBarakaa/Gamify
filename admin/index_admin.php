<?php
session_start();
include '../db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit();
}

// Mengambil daftar user yang memiliki pesan dengan menghubungkan tabel messages dan user berdasarkan id_user
$query = "SELECT user.nama, messages.user_id, MAX(messages.timestamp) as last_message_time
          FROM messages
          JOIN user ON messages.user_id = user.id_user
          GROUP BY messages.user_id
          ORDER BY last_message_time DESC";
$result = mysqli_query($kon, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>CUSTOMER SERVICE</title>
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
    <div class="container">
        <h2 class="mt-4">Daftar Chat User</h2>
        <ul class="list-group mt-3">
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span><?php echo htmlspecialchars($row['nama']); ?></span>
                    <a class="btn btn-primary btn-sm" href="chat.php?user_id=<?php echo $row['user_id']; ?>">Buka Chat</a>
                </li>
            <?php } ?>
        </ul>
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
