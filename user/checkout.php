<?php 
session_start();
include('../db.php'); 

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION["user"];
$kue_user = mysqli_query($kon, "SELECT * FROM user WHERE nama = '$user'");
$row_user = mysqli_fetch_array($kue_user);

if (!$row_user) {
    die("User not found.");
}

$userId = $row_user['id_user'];

$checkoutItems = [];
$promoApplied = false;

if (isset($_POST['selected_items']) && !empty($_POST['selected_items'])) {
    $selectedItems = $_POST['selected_items'];
    $sanitizedItems = array_map('intval', $selectedItems); // Sanitize input

    if (!empty($sanitizedItems)) {
        $itemIds = implode(',', $sanitizedItems);

        // Debugging: Check the selected items
        error_log("Selected Items: " . print_r($selectedItems, true));
        error_log("Sanitized Items: " . print_r($sanitizedItems, true));
        error_log("Item IDs: " . $itemIds);

        $sql = "SELECT p.*, k.id_keranjang, k.id_produk, k.user_id, p.nama_produk, p.harga, p.gambar, k.jumlah
                FROM keranjang k
                JOIN produk p ON k.id_produk = p.id_produk
                WHERE k.id_keranjang IN ($itemIds) AND k.user_id = $userId";
        
        // Debugging: Log the SQL query
        error_log("SQL Query: " . $sql);

        $result = mysqli_query($kon, $sql);

        if ($result) {
            if (mysqli_num_rows($result) > 0) {
                $checkoutItems = mysqli_fetch_all($result, MYSQLI_ASSOC);
                $_SESSION['checkout_items'] = $checkoutItems;
                // Debugging: Log the fetched items
                error_log("Fetched Items: " . print_r($checkoutItems, true));
            } else {
                // Debugging: Log no rows found
                error_log("No rows found for the given item IDs.");
                die('Tidak ada item dalam checkout.');
            }
        } else {
            // Debugging: Log the error message
            error_log("MySQL Error: " . mysqli_error($kon));
            die('Error executing query.');
        }
        unset($_SESSION['temp_cart']);
    } else {
        die('No valid items selected.');
    }
} elseif (isset($_POST['buy_now'])) {
    $productId = intval($_POST['product_id']);
    $productQuery = "SELECT id_produk, nama_produk, harga, gambar, stok FROM produk WHERE id_produk = '$productId'";
    $result = mysqli_query($kon, $productQuery);

    if ($result && $product = mysqli_fetch_assoc($result)) {
        $product['jumlah'] = 1;
        $checkoutItems[] = $product;
        $_SESSION['temp_cart'] = [$productId => $product];
    } else {
        die("Produk tidak ditemukan.");
    }
} elseif (isset($_SESSION['temp_cart']) && !empty($_SESSION['temp_cart'])) {
    // Gunakan session temp_cart jika ada
    $checkoutItems = $_SESSION['temp_cart'];
} else {
    $error = "Tidak ada item yang diproses untuk checkout.";
}

// Hitung ulang grand total
$grandTotal = 0;
foreach ($checkoutItems as $item) {
    $grandTotal += $item['harga'] * $item['jumlah'];
}

// Terapkan diskon
if (isset($_POST['apply_promo'])) {
    try {
        if (empty($_SESSION['checkout_items'])) {
            throw new Exception("Tidak ada item dalam checkout.");
        }

        $checkoutItems = $_SESSION['checkout_items'];

        $promoCode = mysqli_real_escape_string($kon, $_POST['promo_code']);
        if (empty($promoCode)) {
            throw new Exception("Kode promo tidak boleh kosong.");
        }

        // Fetch promo data
        $promoQuery = "SELECT * FROM promo WHERE code = '$promoCode'";
        $promoResult = mysqli_query($kon, $promoQuery);

        if (!$promoResult || mysqli_num_rows($promoResult) <= 0) {
            throw new Exception("Kode promo tidak valid atau tidak ditemukan.");
        }

        $promoData = mysqli_fetch_assoc($promoResult);
        if ($promoData['usage_limit'] <= 0) {
            throw new Exception("Kode promo telah mencapai batas penggunaan.");
        }

        // Hitung total
        $grandTotal = 0;
        foreach ($checkoutItems as $item) {
            $grandTotal += $item['harga'] * $item['jumlah'];
        }

        // Terapkan diskon
        $discount = $promoData['discount_value'];
        $effectiveDiscount = min($discount, $grandTotal);
        $grandTotal -= $effectiveDiscount;

        // Update promo usage
        $updatePromoQuery = "
            UPDATE promo 
            SET usage_limit = usage_limit - 1, 
                times_used = times_used + 1 
            WHERE code = '$promoCode'";
        if (!mysqli_query($kon, $updatePromoQuery)) {
            throw new Exception("Error updating promo usage: " . mysqli_error($kon));
        }

        // Simpan diskon di session
        $_SESSION['promo_applied'] = true;
        $_SESSION['discount'] = $effectiveDiscount;
        $_SESSION['grand_total'] = $grandTotal;

        echo json_encode([
            'grandTotal' => $grandTotal,
            'discount' => $effectiveDiscount,
            'error' => null
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'grandTotal' => isset($grandTotal) ? $grandTotal : 0,
            'discount' => 0,
            'error' => $e->getMessage()
        ]);
    }
    exit();
}

// Jika confirm order
if (isset($_POST['confirm_order'])) {
    if (empty($checkoutItems)) {
        $error = "Tidak ada item yang diproses untuk checkout.";
    } else {
        $name = mysqli_real_escape_string($kon, $_POST['name']);
        $address = mysqli_real_escape_string($kon, $_POST['address']);
        $phone = mysqli_real_escape_string($kon, $_POST['phone']);
        $postal_code = mysqli_real_escape_string($kon, $_POST['postal_code']);
        $payment_method = mysqli_real_escape_string($kon, $_POST['payment_method']);
        $biaya_kirim = 20000;

        $kurir_list = array("JNE", "J&T", "SiCepat", "Pos Indonesia");
        $kurir_terpilih = $kurir_list[array_rand($kurir_list)]; 

        mysqli_begin_transaction($kon);
        try {
            // Hitung ulang grand total
            $grandTotal = 0;
            foreach ($checkoutItems as $item) {
                $grandTotal += $item['harga'] * $item['jumlah'];
            }

            // Kurangi diskon jika tersedia
            if (isset($_SESSION['promo_applied']) && $_SESSION['promo_applied']) {
                $grandTotal -= $_SESSION['discount'] ?? 0; 
            }
            $grandTotal += $biaya_kirim; 

            $orderIds = array();

            foreach ($checkoutItems as $item) {
                $productId = $item['id_produk'];
                $quantity = $item['jumlah'];
                $price = $item['harga'];

                if ($item['stok'] < $quantity) {
                    throw new Exception("Stok produk '{$item['nama_produk']}' tidak mencukupi.");
                }

                $insertOrder = "INSERT INTO pesanan (id_user, total_harga, id_produk, jumlah, status_pesanan, tanggal_pesanan)
                                VALUES ('$userId', '$grandTotal', '$productId', '$quantity', 'Diproses', NOW())";
                if (!mysqli_query($kon, $insertOrder)) {
                    throw new Exception('Error inserting order: ' . mysqli_error($kon));
                }
                $orderId = mysqli_insert_id($kon);
                $orderIds[] = $orderId;

                $updateStock = "UPDATE produk SET stok = stok - $quantity WHERE id_produk = '$productId'";
                if (!mysqli_query($kon, $updateStock)) {
                    throw new Exception('Error updating stock: ' . mysqli_error($kon));
                }

                $insertPayment = "INSERT INTO pembayaran (id_pesanan, metode_pembayaran, status_pembayaran, tanggal_pembayaran)
                                 VALUES ('$orderId', '$payment_method', 'Dibayar', NOW())";
                if (!mysqli_query($kon, $insertPayment)) {
                    throw new Exception('Error inserting payment: ' . mysqli_error($kon));
                }

                // Insert pengiriman_pesanan
                $nomor_resi = 'RSI' . date('YmdHis') . rand(100, 999);
                $tanggal_kirim = date('Y-m-d H:i:s');
                $perkiraan_tiba = date('Y-m-d H:i:s', strtotime('+3 days'));

                $insertShipping = "INSERT INTO pengiriman_pesanan (
                    id_pesanan, 
                    id_user,
                    nomor_resi,
                    nama_kurir,
                    alamat_pengiriman,
                    tanggal_kirim,
                    perkiraan_tiba,
                    status_pengiriman,
                    biaya_kirim
                ) VALUES ('$orderId', '$userId', '$nomor_resi', '$kurir_terpilih', '$address', '$tanggal_kirim', '$perkiraan_tiba', 'dalam_pengiriman', '$biaya_kirim')";

                if (!mysqli_query($kon, $insertShipping)) {
                    throw new Exception('Error inserting shipping: ' . mysqli_error($kon));
                }
            }

            // Hapus item dari keranjang jika berasal dari keranjang
            if (!empty($itemIds)) {
                $deleteCart = "DELETE FROM keranjang WHERE id_keranjang IN ($itemIds)";
                if (!mysqli_query($kon, $deleteCart)) {
                    throw new Exception('Error deleting cart items: ' . mysqli_error($kon));
                }
            }

            mysqli_commit($kon); 
            unset($_SESSION['discount']);
            unset($_SESSION['promo_applied']);

            $_SESSION['checkout_success'] = true;
            $_SESSION['order_ids'] = $orderIds;
            $_SESSION['payment_method'] = $payment_method;
            $_SESSION['shipping_name'] = $name;
            $_SESSION['shipping_address'] = $address;
            $_SESSION['shipping_phone'] = $phone;
            $_SESSION['shipping_postal'] = $postal_code;

            header("Location: history_pembayaran.php");
            exit();

        } catch (Exception $e) {
            mysqli_rollback($kon); 
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Checkout</title>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Custom CSS untuk checkout page -->
    <style>
        /* Styling untuk card */
        .card {
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            border: none;
        }

        .card-body {
            padding: 25px;
        }

        /* Styling untuk heading */
        .card-body h2 {
            color: #012970;
            font-size: 24px;
            margin-bottom: 20px;
            font-weight: 600;
            position: relative;
            padding-bottom: 10px;
        }

        .card-body h2:after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            height: 3px;
            width: 60px;
            background: #4154f1;
        }

        /* Styling untuk tabel */
        .table {
            margin-top: 20px;
        }

        .table thead th {
            background-color: #f6f9ff;
            color: #012970;
            font-weight: 600;
            vertical-align: middle;
        }

        .table tbody td {
            vertical-align: middle;
        }

        .table img {
            border-radius: 8px;
            border: 1px solid #eee;
        }

        .table tfoot th {
            background-color: #f8f9fa;
            font-weight: 700;
            color: #012970;
        }

        /* Styling untuk form */
        .form-label {
            color: #012970;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border-radius: 8px;
            padding: 10px 15px;
            border: 1px solid #ced4da;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #4154f1;
            box-shadow: 0 0 0 0.2rem rgba(65, 84, 241, 0.25);
        }

        textarea.form-control {
            min-height: 100px;
        }

        /* Styling untuk tombol */
        .btn {
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-secondary {
            background-color: #6c757d;
            border: none;
        }

        .btn-success {
            background-color: #4154f1;
            border: none;
        }

        .btn-success:hover {
            background-color: #2536be;
            transform: translateY(-2px);
        }

        /* Styling untuk alert */
        .alert {
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 20px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .card-body {
                padding: 15px;
            }

            .table {
                font-size: 14px;
            }

            .card-body h2 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php require "atas.php"; ?>
    <!-- Sidebar -->
    <?php require "menu.php"; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1><i class="bi bi-cart-check"></i> Pembayaran</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Pembayaran</li>
                </ol>
            </nav>
        </div>

        <div class="container mt-5">
            <!-- Error Message -->
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error; ?></div>
            <?php endif; ?>

            <!-- Ringkasan Pesanan -->
            <section>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h2>Ringkasan Pesanan</h2>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Gambar</th>
                                            <th>Nama Produk</th>
                                            <th>Harga</th>
                                            <th>Jumlah</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($checkoutItems as $item): ?>
                                            <tr>
                                                <td><img src="../uploads/<?= $item['gambar']; ?>" alt="Gambar Produk" width="100"></td>
                                                <td><?= htmlspecialchars($item['nama_produk']); ?></td>
                                                <td>Rp <?= number_format($item['harga'], 0, ',', '.'); ?></td>
                                                <td><?= $item['jumlah']; ?></td>
                                                <td>Rp <?= number_format($item['harga'] * $item['jumlah'], 0, ',', '.'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="4" class="text-end">Total</th>
                                            <th><div id="grand_total">Rp <?= number_format($grandTotal, 0, ',', '.'); ?></div></th>
                                        </tr>
                                    </tfoot>
                                </table>

                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Formulir Informasi Pengiriman -->
            <section>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h2>Informasi Pengiriman</h2>
                                <form method="POST">
                                <?php 
                                    // Pastikan variabel terdefinisi
                                    $selectedItems = isset($selectedItems) && is_array($selectedItems) ? $selectedItems : []; 
                                                                            
                                    foreach ($selectedItems as $itemId): 
                                    ?>
                                        <input type="hidden" name="selected_items[]" value="<?php echo $itemId; ?>">
                                    <?php endforeach; ?>
                                    
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Nama Penerima</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="address" class="form-label">Alamat</label>
                                        <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Nomor Telepon</label>
                                        <input type="text" class="form-control" id="phone" name="phone" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="postal_code" class="form-label">Kode Pos</label>
                                        <input type="text" class="form-control" id="postal_code" name="postal_code" required>
                                    </div>

                                    <!-- Metode Pembayaran -->
                                    <h2>Metode Pembayaran</h2>
                                    <div class="mb-3">
                                        <select class="form-select" name="payment_method" required>
                                            <option value="" disabled selected>Pilih Metode Pembayaran</option>
                                            <option value="Transfer Bank">Transfer Bank</option>
                                            <option value="Kartu Kredit">Kartu Kredit</option>
                                            <option value="COD">Bayar di Tempat (COD)</option>
                                        </select>
                                    </div>

                                    <!-- Kode Promo -->
                                    <div class="mb-3">
                                        <label class="form-label">Kode Promo</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="promo_code" name="promo_code" placeholder="Masukkan kode promo">
                                            <button type="button" id="apply_promo" class="btn btn-primary">Gunakan Promo</button>
                                        </div>
                                    </div>

                                    <!-- Konfirmasi Pembayaran -->
                                    <div class="text-end">
                                        <a href="add_to_cart.php" class="btn btn-secondary">Kembali</a>
                                        <button type="submit" name="confirm_order" class="btn btn-success">Konfirmasi Pesanan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
    <script>
        $('#apply_promo').on('click', function () {
        var promoCode = $('#promo_code').val();
        if (!promoCode) {
            alert("Masukkan kode promo.");
            return;
        }

        $.ajax({
            type: 'POST',
            url: '', // Current PHP file
            data: {
                apply_promo: true,
                promo_code: promoCode
            },
            success: function (response) {
                console.log("Response from server:", response); // Debug
                try {
                    var data = JSON.parse(response);
                    if (data.error) {
                        alert(data.error);
                    } else {
                        $('#grand_total').text('Rp ' + new Intl.NumberFormat().format(data.grandTotal));
                        alert('Diskon berhasil diterapkan: Rp ' + new Intl.NumberFormat().format(data.discount));
                    }
                } catch (e) {
                    console.error("Error parsing response:", e, response);
                    alert("Terjadi kesalahan saat memproses data.");
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", status, error);
                alert("Terjadi kesalahan saat menghubungi server.");
            }
        });
    });
    </script>
    <!-- Vendor JS Files -->
    <script src="../assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/vendor/chart.js/chart.min.js"></script>
    <script src="../assets/vendor/echarts/echarts.min.js"></script>
    <script src="../assets/vendor/quill/quill.min.js"></script>
    <script src="../assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="../assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="../assets/vendor/php-email-form/validate.js"></script>

    <!-- Template Main JS File -->
    <script src="../assets/js/main.js"></script>
</body>
</html>