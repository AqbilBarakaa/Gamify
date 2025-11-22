<?php
session_start();
include '../db.php'; // File koneksi ke database

// Cek apakah user sudah login
if (!isset($_SESSION["user"])) {
    header("Location: ../login.php");
    exit;
}

$user = $_SESSION["user"];
$kue_user = mysqli_query($kon, "SELECT * FROM user WHERE nama = '$user'");
$row_user = mysqli_fetch_array($kue_user);

// Gunakan ID user dari session
$id_user = $row_user["id_user"]; // Menggunakan ID user dari session

// Ambil topik berdasarkan ID
$id_topik = $_GET['id'] ?? 0; // Changed from 'id_topik' to 'id'
$query_topik = mysqli_query($kon, "SELECT t.*, u.nama AS pembuat 
                                   FROM topik t 
                                   JOIN user u ON t.dibuat_oleh = u.id_user 
                                   WHERE t.id_topik = '$id_topik'");

if (mysqli_num_rows($query_topik) == 0) {
    echo "Topik tidak ditemukan!";
    exit;
}

$topik = mysqli_fetch_assoc($query_topik);

// Proses kirim komentar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $id_user) {
    $isi_komentar = mysqli_real_escape_string($kon, $_POST['isi_komentar']);
    $query = "INSERT INTO komentar (id_topik, id_user, isi_komentar) VALUES ('$id_topik', '$id_user', '$isi_komentar')";
    mysqli_query($kon, $query);
    header("Location: topik.php?id=$id_topik");
    exit;
}

// Ambil semua komentar untuk topik ini
$query_komentar = mysqli_query($kon, "
    SELECT k.*, u.nama, u.level 
    FROM komentar k
    JOIN user u ON k.id_user = u.id_user
    WHERE k.id_topik = '$id_topik'
    ORDER BY k.created_at ASC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Topik Diskusi</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #2e2e2e;
            color: white;
            font-family: Arial, sans-serif;
        }
        .container {
            margin-top: 20px;
        }
        .topik-box, .komentar-box {
            background-color: whitesmoke;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .admin-label {
            color: #ffcc00;
            font-weight: bold;
            margin-left: 5px;
        }
        .comment-input {
            background-color: white;
            color: black;
            border: none;
            border-radius: 5px;
        }
        .comment-input:focus {
            outline: none;
            box-shadow: 0 0 5px #ffcc00;
        }
        button[type="submit"] {
            background-color: #ffcc00;
            border: none;
            color: #2e2e2e;
            font-weight: bold;
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
        <!-- Tombol Kembali -->
        <a href="detail_komunitas.php?id=<?php echo $topik['id_komunitas']; ?>" class="btn btn-light mb-3">← Kembali</a>

        <!-- Topik -->
        <div class="topik-box">
            <h5><?= htmlspecialchars($topik['judul_topik']) ?></h5>
            <p><?= nl2br(htmlspecialchars($topik['deskripsi_topik'])) ?></p>
            <small>Dibuat oleh: <?= htmlspecialchars($topik['pembuat']) ?> pada <?= date('d M Y H:i', strtotime($topik['created_at'])) ?></small>
        </div>

        <!-- Daftar Komentar -->
        <h6>Komentar</h6>
        <?php while ($komentar = mysqli_fetch_assoc($query_komentar)): ?>
            <div class="komentar-box">
                <strong>
                    <?= htmlspecialchars($komentar['nama']) ?>
                    <?php if ($komentar['level'] == 'admin'): ?>
                        <span class="admin-label">admin</span>
                    <?php endif; ?>
                </strong>
                <p><?= nl2br(htmlspecialchars($komentar['isi_komentar'])) ?></p>
                <small><?= date('d M Y H:i', strtotime($komentar['created_at'])) ?></small>
            </div>
        <?php endwhile; ?>

        <!-- Form Komentar -->
        <?php if ($id_user): ?>
            <form method="POST" class="mt-3">
                <div class="form-group">
                    <textarea name="isi_komentar" rows="3" class="form-control comment-input" placeholder="Tulis komentar..."></textarea>
                </div>
                <button type="submit" class="btn btn-warning">Kirim</button>
            </form>
        <?php else: ?>
            <p><a href="../login.php" class="text-warning">Login</a> untuk memberikan komentar.</p>
        <?php endif; ?>
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