<?php
session_start();
require "../db.php";
$page = "produk";

if (!isset($_SESSION["user"])) {
    header("Location: ../login.php");
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$nama = $_SESSION['user']; 
$user_id = null;

$user_query = "SELECT id_user FROM user WHERE nama = ?";
$stmt_user = mysqli_prepare($kon, $user_query);
mysqli_stmt_bind_param($stmt_user, "s", $nama);
mysqli_stmt_execute($stmt_user);
mysqli_stmt_bind_result($stmt_user, $user_id);
mysqli_stmt_fetch($stmt_user);
mysqli_stmt_close($stmt_user);

if (!$user_id) {
    echo "<div class='alert alert-danger'>User not found.</div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Available Promo Codes</title>
    
    <!-- Bootstrap CSS -->
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        .promo-card {
            background: linear-gradient(135deg, #6B73FF 0%, #000DFF 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
        }
        .promo-code {
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }
        .promo-value {
            font-size: 18px;
            margin-bottom: 5px;
        }
        .promo-type {
            font-size: 14px;
            opacity: 0.8;
        }
    </style>
    <?php include 'aset.php'; ?>
</head>
<body>
    <?php include 'atas.php'; ?>
    <?php include 'menu.php'; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1><i class="bi bi-gift"></i> Your Special Promo Codes</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">HOME</a></li>
                    <li class="breadcrumb-item active">PROMO CODES</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Available Promo Codes</h5>
                            <div class="row">
                                <?php
                                try {
                                    if (!$kon) {
                                        throw new Exception("Database connection failed");
                                    }

                                    $num_promos = 2;
                                    $promo_query = "SELECT p.id, p.code, p.discount_type, p.discount_value 
                                                    FROM promo p
                                                    LEFT JOIN user_promo_codes upc ON p.id = upc.promo_id AND upc.user_id = ?
                                                    WHERE upc.id_user_promo_code IS NULL 
                                                      AND p.times_used < p.usage_limit  
                                                    LIMIT ?";
                                    
                                    $stmt = mysqli_prepare($kon, $promo_query);
                                    if ($stmt) {
                                        mysqli_stmt_bind_param($stmt, "ii", $user_id, $num_promos);
                                        mysqli_stmt_execute($stmt);
                                        $result = mysqli_stmt_get_result($stmt);

                                        if ($result && mysqli_num_rows($result) > 0) {
                                            while ($promo_row = mysqli_fetch_assoc($result)) {
                                                ?>
                                                <div class="col-md-6">
                                                    <div class="promo-card">
                                                        <div class="promo-code">
                                                            <?php echo htmlspecialchars($promo_row['code']); ?>
                                                        </div>
                                                        <div class="promo-value">
                                                            <?php
                                                            if ($promo_row['discount_type'] == 'fixed') {
                                                                echo 'Rp. ' . number_format($promo_row['discount_value'], 0, ',', '.');
                                                            } else {
                                                                echo htmlspecialchars($promo_row['discount_value']) . '%';
                                                            }
                                                            ?>
                                                        </div>
                                                        <div class="promo-type">
                                                            <?php echo ($promo_row['discount_type'] == 'fixed' ? 'Fixed Discount' : 'Percentage Discount'); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                        } else {
                                            echo "<div class='alert alert-info'>No promo codes available at the moment.</div>";
                                        }
                                        mysqli_stmt_close($stmt);
                                    } else {
                                        echo "<div class='alert alert-warning'>Error preparing database query.</div>";
                                    }
                                } catch (Exception $e) {
                                    echo "<div class='alert alert-danger'>An error occurred: " . htmlspecialchars($e->getMessage()) . "</div>";
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Core Bootstrap JS -->
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <!-- Vendor JS Files -->
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html> 
