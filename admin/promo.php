<?php
session_start();
require "../db.php";
$page = "promo";

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to generate a promo code
function generatePromoCode($length = 5) {
    return strtoupper(substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, $length));
}

// Handle promo code generation
if (isset($_POST['generate_promo'])) {
    $promo_code = generatePromoCode();
    $discount_type = $_POST['discount_type'];
    $discount_value = floatval($_POST['discount_value']);
    $usage_limit = intval($_POST['usage_limit']);

    // Validate input
    if ($discount_value <= 0 || $usage_limit <= 0) {
        echo "<script>alert('Invalid discount value or usage limit.');</script>";
    } else {
        $stmt = $kon->prepare("INSERT INTO promo (code, discount_type, discount_value, usage_limit) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssdi", $promo_code, $discount_type, $discount_value, $usage_limit);
        
        if ($stmt->execute()) {
            echo "<script>alert('Promo code successfully created: $promo_code');</script>";
        } else {
            echo "<script>alert('Failed to save promo code: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Apply Promo Code</title>
    <link rel="stylesheet" href="assets/css/styles.css">

    <!-- Favicons -->
    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">
    
    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
    <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
    <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="assets/css/style.css" rel="stylesheet">
    <?php include 'aset.php'; ?>
</head>
<body>
    <?php include 'atas.php'; ?>
    <?php include 'menu.php'; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1><i class="bi bi-percent"></i>&nbsp; PROMO DISCOUNT</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">HOME</a></li>
                    <li class="breadcrumb-item active">CODE PROMO</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mt-5">Generate Promo Code</h5>
                            <form method="post">
                                <div class="mb-3">
                                    <label for="discount_type" class="form-label">Discount Type</label>
                                    <select class="form-select" id="discount_type" name="discount_type" required>
                                        <option value="fixed">Fixed Amount</option>
                                        <option value="percentage">Percentage</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="discount_value" class="form-label">Discount Value</label>
                                    <input type="number" class="form-control" id="discount_value" name="discount_value" required>
                                </div>
                                <div class="mb-3">
                                    <label for="usage_limit" class="form-label">Usage Limit</label>
                                    <input type="number" class="form-control" id="usage_limit" name="usage_limit" required>
                                </div>
                                <button type="submit" name="generate_promo" class="btn btn-primary">Generate Promo Code</button>
                            </form>

                            <h5 class="card-title mt-5">Available Promo Codes</h5>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Promo Code</th>
                                        <th>Discount Type</th>
                                        <th>Discount Value</th>
                                        <th>Usage Limit</th>
                                        <th>Times Used</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $promo_query = "SELECT * FROM promo";
                                    $promo_result = $kon->query($promo_query);
                                    while ($promo_row = $promo_result->fetch_assoc()) {
                                        $status = $promo_row['times_used'] >= $promo_row['usage_limit'] ? 'Expired' : 'Available';
                                        $discount_value = $promo_row['discount_type'] == 'fixed' ? 'Rp. ' . number_format($promo_row['discount_value'], 0, ',', '.') : $promo_row['discount_value'] . '%';

                                        echo "<tr>";
                                        echo "<td>{$promo_row['code']}</td>";
                                        echo "<td>" . ($promo_row['discount_type'] == 'fixed' ? 'Fixed Amount' : 'Percentage') . "</td>";
                                        echo "<td>$discount_value</td>";
                                        echo "<td>{$promo_row['usage_limit']}</td>";
                                        echo "<td>{$promo_row['times_used']}</td>";
                                        echo "<td>$status</td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer and script includes remain the same -->
</body>
</html>
