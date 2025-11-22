<?php
session_start();
require "../db.php"; 

if (!isset($_SESSION["admin"])) {
    header("Location: ../login.php");
    exit;
}

$admin_id = $_SESSION["admin"]; 
$result = mysqli_query($kon, "SELECT * FROM user WHERE nama = '$admin_id'");

if (!$result) {
    die('Invalid query: ' . mysqli_error($kon));
}

$admin = mysqli_fetch_assoc($result);

if (!$admin) {
    echo "Error: Petugas not found.";
    exit;
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Profil Admin</title>
    <meta name="robots" content="noindex, nofollow" />
    <meta content="" name="description" />
    <meta content="" name="keywords" />
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
    <link href="../assets/vendor/quill.quill.bubble.css" rel="stylesheet">
    <link href="../assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="../assets/vendor/simple-datatables/style.css" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="../assets/css/style.css" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
</head>

<body>

    <!-- HEADER -->
    <?php require "atas.php"; ?>

    <!-- SIDEBAR -->
    <?php require "menu.php"; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1><i class="bi bi-person-circle"></i>&nbsp; Profil Admin</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">DASHBOARD</a></li>
                    <li class="breadcrumb-item active">Profil</li>
                </ol>
            </nav>
        </div>
        
        <div class="container mt-5">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <div class="card">
                        <div class="card-body">
                            <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success">
                                <?php 
                                echo $_SESSION['success']; 
                                unset($_SESSION['success']); 
                                ?>
                            </div>
                            <?php elseif (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?php 
                                echo $_SESSION['error']; 
                                unset($_SESSION['error']); 
                                ?>
                            </div>
                            <?php endif; ?>
                            <h2 class="text-center mb-4">Profil</h2>
                            <div class="row">
                                <div class="col-md-4">
                                    <img src="../uploads/<?php echo htmlspecialchars($admin['foto']); ?>" alt="Profile Image" class="img-fluid rounded mb-4" />
                                </div>
                                <div class="col-md-8">
                                    <h3><?php echo htmlspecialchars($admin['nama']); ?></h3>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($admin['email']); ?></p>
                                    <p><strong>No Telp:</strong> <?php echo htmlspecialchars($admin['no_tlp']); ?></p>
                                    <p><strong>Alamat:</strong> <?php echo htmlspecialchars($admin['alamat']); ?></p>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                                        <i class="bi bi-pencil-square"></i>&nbsp; Edit Profil
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Edit Profil -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="update_profile.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editProfileModalLabel">Edit Profil</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="admin_id" value="<?php echo $admin['id_user']; ?>">
                        <input type="hidden" name="foto_lama" value="<?php echo htmlspecialchars($admin['foto']); ?>">
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama</label>
                            <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($admin['nama']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="no_tlp" class="form-label">No Telp</label>
                            <input type="text" class="form-control" id="no_tlp" name="no_tlp" value="<?php echo htmlspecialchars($admin['no_tlp']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?php echo htmlspecialchars($admin['alamat']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="foto" class="form-label">Ganti Foto Profil</label>
                            <input type="file" class="form-control" id="foto" name="foto">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <!-- Vendor JS Files -->
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="../assets/vendor/chart.js/chart.umd.js"></script>
    <script src="../assets/vendor/echarts/echarts.min.js"></script>
    <script src="../assets/vendor/quill/quill.min.js"></script>
    <script src="../assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="../assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="../assets/vendor/php-email-form/validate.js"></script>

    <!-- Template Main JS File -->
    <script src="../assets/js/main.js"></script>

</body>

</html>
