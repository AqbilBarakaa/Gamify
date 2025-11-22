<?php
session_start();
include '../db.php';
$page = "komunitas";

// Cek apakah user adalah admin
if (!isset($_SESSION["admin"])){
    header("Location: ../login.php");
    exit;
}

// Gunakan nama admin dari session
$user = $_SESSION["admin"];
$kue_user = mysqli_query($kon, "SELECT * FROM user WHERE nama = '$user'");
$row_user = mysqli_fetch_array($kue_user);
$nama_admin = $_SESSION["admin"];

// Ambil data recent komunitas
$query_recent = mysqli_query($kon, "SELECT * FROM komunitas ORDER BY created_at DESC LIMIT 5");

// Ambil data top komunitas (komunitas dengan lebih dari 10 balasan)
$query_top = mysqli_query($kon, "
    SELECT k.*, COUNT(t.id_topik) AS jumlah_topik, 
           (SELECT COUNT(*) 
            FROM topik tp 
            JOIN komentar km ON tp.id_topik = km.id_topik 
            WHERE tp.id_komunitas = k.id_komunitas) AS jumlah_komentar
    FROM komunitas k
    LEFT JOIN topik t ON k.id_komunitas = t.id_komunitas
    GROUP BY k.id_komunitas
    HAVING jumlah_komentar > 10
    ORDER BY jumlah_komentar DESC
    LIMIT 5
");

// Ambil daftar topik
$query_topik = mysqli_query($kon, "
    SELECT t.id_topik, t.judul_topik, t.deskripsi_topik, u.nama AS pembuat 
    FROM topik t 
    JOIN user u ON t.dibuat_oleh = u.id_user 
    ORDER BY t.id_topik DESC
");

// Tambah komunitas (proses langsung dalam file)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_forum = mysqli_real_escape_string($kon, $_POST['nama_forum']);
    $deskripsi = mysqli_real_escape_string($kon, $_POST['deskripsi']);
    $created_by = $row_user['id_user'];

    // File upload handling
    $upload_dir = '../uploads/komunitas/';
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Check if file was uploaded
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $file_name = uniqid() . '_' . basename($_FILES['gambar']['name']);
        $target_path = $upload_dir . $file_name;
        $file_type = strtolower(pathinfo($target_path, PATHINFO_EXTENSION));

        // Validate file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($file_type, $allowed_types)) {
            // Max file size 5MB
            if ($_FILES['gambar']['size'] <= 5 * 1024 * 1024) {
                // Move uploaded file
                if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_path)) {
                    // Insert into database with file path
                    $query_insert = "INSERT INTO komunitas (nama_komunitas, deskripsi, dibuat_oleh, gambar) VALUES (?, ?, ?, ?)";
                    $stmt = mysqli_prepare($kon, $query_insert);
                    mysqli_stmt_bind_param($stmt, 'ssss', $nama_forum, $deskripsi, $created_by, $file_name);
                    mysqli_stmt_execute($stmt);
                    
                    if (mysqli_stmt_affected_rows($stmt) > 0) {
                        mysqli_stmt_close($stmt);
                        header("Location: forum_komunitas.php");
                        exit;
                    } else {
                        $upload_error = "Gagal menyimpan data komunitas.";
                    }
                } else {
                    $upload_error = "Gagal mengunggah gambar.";
                }
            } else {
                $upload_error = "Ukuran gambar terlalu besar. Maksimal 5MB.";
            }
        } else {
            $upload_error = "Jenis file tidak diizinkan. Gunakan JPG, JPEG, PNG, atau GIF.";
        }
    } else {
        $upload_error = "Silakan pilih gambar.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum Komunitas</title>
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

        .community-icon {
            font-size: 1.5rem;
            color: #ffc107;
            margin-right: 10px;
        }

        .community-card-title {
            font-weight: bold;
            font-size: 1.1rem;
            color: #343a40;
        }

        .section-title {
            font-weight: bold;
            border-bottom: 2px solid #ffc107;
            display: inline-block;
            margin-bottom: 15px;
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
        <!-- Title -->
        <h1 class="text-center mb-5">COMMUNITY</h1>
        <!-- Recent dan Top Community -->
        <div class="row mb-4">
            <div class="col-md-6">
                <h4 class="section-title">Recent Communities</h4>
                <?php while ($recent = mysqli_fetch_assoc($query_recent)): ?>
                    <a href="detail_komunitas.php?id=<?php echo $recent['id_komunitas']; ?>">
                        <div class="card mb-3 card-custom">
                            <div class="card-body d-flex align-items-center">
                                <i class="fas fa-users community-icon"></i>
                                <span class="community-card-title"><?php echo htmlspecialchars($recent['nama_komunitas']); ?></span>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
            <div class="col-md-6">
                <h4 class="section-title">Top Communities</h4>
                <?php while ($top = mysqli_fetch_assoc($query_top)): ?>
                    <a href="detail_komunitas.php?id=<?php echo $top['id_komunitas']; ?>">
                        <div class="card mb-3 card-custom">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-trophy community-icon"></i>
                                    <span class="community-card-title"><?php echo htmlspecialchars($top['nama_komunitas']); ?></span>
                                </div>
                                <span class="badge badge-warning"><?php echo $top['jumlah_komentar']; ?> Komentar</span>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Search dan Tambah Komunitas -->
        <div class="d-flex justify-content-between mb-4">
            <div class="input-group" style="max-width: 400px;">
                <input type="text" class="form-control" placeholder="Search Community">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="button"><i class="fas fa-search"></i></button>
                </div>
            </div>
            <button class="btn btn-warning" data-toggle="modal" data-target="#tambahKomunitasModal"><i class="fas fa-plus"></i> CREATE NEW COMMUNITY</button>
        </div>

        <!-- Daftar Topik -->
        <div class="list-group">
            <?php while ($topik = mysqli_fetch_assoc($query_topik)): ?>
                <a href="topik.php?id=<?php echo $topik['id_topik']; ?>" class="list-group-item list-group-item-action">
                    <h5 class="mb-1"><?php echo htmlspecialchars($topik['judul_topik']); ?></h5>
                    <p class="mb-1"><?php echo nl2br(htmlspecialchars($topik['deskripsi_topik'])); ?></p>
                    <small class="text-muted">By: <?php echo htmlspecialchars($topik['pembuat']); ?></small>
                </a>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Modal Tambah Komunitas -->
    <div class="modal fade" id="tambahKomunitasModal" tabindex="-1" aria-labelledby="tambahKomunitasModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahKomunitasModalLabel">Tambah Komunitas</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="nama_forum">Nama Komunitas</label>
                            <input type="text" class="form-control" id="nama_forum" name="nama_forum" required>
                        </div>
                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</ button>
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

  <!-- Template Main JS File -->
  <script src="../assets/js/main.js"></script>
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>