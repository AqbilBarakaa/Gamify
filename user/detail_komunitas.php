<?php
session_start();
include '../db.php';

// Cek apakah user sudah login
if (!isset($_SESSION["user"])) {
    header("Location: ../login.php");
    exit;
}

// Gunakan ID user dari session
$user = $_SESSION["user"];
$kue_user = mysqli_query($kon, "SELECT * FROM user WHERE nama = '$user'");
$row_user = mysqli_fetch_array($kue_user);
$id_user = $row_user["id_user"];

// Cek apakah ID komunitas dikirim di URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Komunitas tidak ditemukan!";
    exit;
}
$id_komunitas = $_GET['id'];

// Ambil data komunitas berdasarkan ID
$query = mysqli_query($kon, "SELECT * FROM komunitas WHERE id_komunitas = '$id_komunitas'");
if (mysqli_num_rows($query) == 0) {
    echo "Komunitas tidak ditemukan!";
    exit;
}
$komunitas = mysqli_fetch_assoc($query);

// Tambah topik baru
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul_topik = mysqli_real_escape_string($kon, $_POST['judul_topik']);
    $deskripsi = mysqli_real_escape_string($kon, $_POST['deskripsi']);
    $id_user = $row_user['id_user'];

    if (!empty($judul_topik) && !empty($deskripsi)) {
        $query_insert = "INSERT INTO topik (judul_topik, deskripsi_topik, id_komunitas, dibuat_oleh) 
                         VALUES ('$judul_topik', '$deskripsi', '$id_komunitas', '$id_user' )";
        mysqli_query($kon, $query_insert);
        header("Location: detail_komunitas.php?id=$id_komunitas");
        exit;
    }
}

// Ambil daftar topik dari komunitas ini dengan nama pembuat
$query_topik = mysqli_query($kon, "
    SELECT t.*, u.nama AS pembuat 
    FROM topik t
    JOIN user u ON t.dibuat_oleh = u.id_user 
    WHERE t.id_komunitas = '$id_komunitas'
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Komunitas</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .card-custom {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .card-custom:hover {
            transform: scale(1.05);
        }

        .topic-card-title {
            font-weight: bold;
            font-size: 1.1rem;
            color: #343a40;
        }

        .topic-card-author {
            color: #6c757d;
            font-size: 0.9rem;
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
        <a href="forum_komunitas.php" class="btn btn-light mb-3">← Kembali</a>
        <!-- Judul Komunitas dan Deskripsi -->
        <div class="jumbotron text-center bg-info text-white">
            <h1 class="display-4"><?php echo htmlspecialchars($komunitas['nama_komunitas']); ?></h1>
            <p class="lead"><?php echo nl2br(htmlspecialchars($komunitas['deskripsi'])); ?></p>
        </div>

        <!-- Tombol Tambah Topik -->
        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-warning" data-toggle="modal" data-target="#tambahTopikModal"><i class="fas fa-plus"></i> Tambah Topik</button>
        </div>

        <!-- Daftar Topik -->
        <h4 class="mb-3">Daftar Topik</h4>
        <div class="row">
            <?php while ($topik = mysqli_fetch_assoc($query_topik)): ?>
                <div class="col-md-4 mb-4">
                    <a href="topik.php?id=<?php echo $topik['id_topik']; ?>">
                        <div class="card card-custom h-100">
                            <div class="card-body">
                                <h5 class="card-title topic-card-title"><?php echo htmlspecialchars($topik['judul_topik']); ?></h5>
                                <p class="card-text text-truncate"><?php echo nl2br(htmlspecialchars($topik['deskripsi_topik'])); ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="topic-card-author">
                                        <i class="fas fa-user mr-1"></i> 
                                        <?php echo htmlspecialchars($topik['pembuat']); ?>
                                    </small>
                                    <small class="text-muted"><?php echo date('d M Y', strtotime($topik['created_at'])); ?></small>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>

    </div>

    <!-- Modal Tambah Topik -->
    <div class="modal fade" id="tambahTopikModal" tabindex="-1" aria-labelledby="tambahTopikModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahTopikModalLabel">Tambah Topik Baru</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="judul_topik">Judul Topik</label>
                            <input type="text" class="form-control" id="judul_topik" name="judul_topik" required>
                        </div>
                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Tambah</button>
                    </div>
                </form>
            </div>
        </div>
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
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
   <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

  <!-- Template Main JS File -->
  <script src="../assets/js/main.js"></script>
</body>
</html>