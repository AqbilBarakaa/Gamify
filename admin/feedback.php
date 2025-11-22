<?php
session_start();
require "../db.php";

if (!isset($_SESSION["admin"])) {
    header("Location: ../login.php");
    exit;
}

// Aktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch feedback
$fetch_query = "SELECT f.id, f.content, f.created_at, u.nama 
                FROM feedback f 
                JOIN user u ON f.user_id = u.nama
                ORDER BY f.created_at DESC";

$feedbacks = mysqli_query($kon, $fetch_query);

if (!$feedbacks) {
    echo '<script>alert("Query gagal: ' . mysqli_error($kon) . '");</script>';
}

if (mysqli_num_rows($feedbacks) == 0) {
    echo '<script>alert("Tidak ada feedback ditemukan.");</script>';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Feedback</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <!-- Favicons -->
    <link href="../assets/img/favicon.png" rel="icon">
    <link href="../assets/img/apple-touch-icon.png" rel="apple-touch-icon">
    <!-- Google Fonts -->
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
</head>
<body>
    <!-- HEADER -->
    <?php include 'atas.php'; ?>
    <!-- SIDEBAR -->
    <?php include 'menu.php'; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1><i class="bi bi-chat-dots"></i>&nbsp; Feedback</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Feedback</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Daftar Feedback</h5>
                            <div class="accordion" id="feedbackAccordion">
                                <?php if (mysqli_num_rows($feedbacks) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($feedbacks)): ?>
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="heading<?php echo $row['id']; ?>">
                                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $row['id']; ?>" aria-expanded="true" aria-controls="collapse<?php echo $row['id']; ?>">
                                                    <?php echo $row['nama']; ?> memberikan feedback pada <?php echo date('d-m-Y H:i', strtotime($row['created_at'])); ?>
                                                </button>
                                            </h2>
                                            <div id="collapse<?php echo $row['id']; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $row['id']; ?>" data-bs-parent="#feedbackAccordion">
                                                <div class="accordion-body">
                                                    <p><?php echo $row['content']; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="alert alert-warning" role="alert">
                                        Tidak ada feedback ditemukan.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <!-- Bootstrap JS -->
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
