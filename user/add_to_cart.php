<?php
session_start();
include('../db.php');

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$userName = $_SESSION['user'];

$kue_user = mysqli_query($kon, "SELECT * FROM user WHERE nama = '$userName'");
$row_user = mysqli_fetch_array($kue_user);
$user_id = $row_user['id_user']; 

// Add product to cart
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $jumlah = 1; 

    // Check if product already exists in the cart
    $query_check = "SELECT * FROM keranjang WHERE user_id = '$user_id' AND id_produk = '$product_id'";
    $result_check = mysqli_query($kon, $query_check);

    if (mysqli_num_rows($result_check) > 0) {
        $query_update = "UPDATE keranjang SET jumlah = jumlah + $jumlah WHERE user_id = '$user_id' AND id_produk = '$product_id'";
        mysqli_query($kon, $query_update);
    } else {
        $query_insert = "INSERT INTO keranjang (user_id, id_produk, jumlah) VALUES ('$user_id', '$product_id', '$jumlah')";
        mysqli_query($kon, $query_insert);
    }

    header("Location: produk.php?cart_success=1");
    exit();
}

// Update cart quantity via AJAX
if (isset($_POST['update_quantity'])) {
    $cart_id = $_POST['cart_id'];
    $new_quantity = $_POST['quantity'];

    if ($new_quantity <= 0) {
        $sql = "DELETE FROM keranjang WHERE id_keranjang = '$cart_id'";
        mysqli_query($kon, $sql);
        echo json_encode(['action' => 'removed']);  // Respond that the item was removed
        exit();
    } else {
        $sql = "UPDATE keranjang SET jumlah = '$new_quantity' WHERE id_keranjang = '$cart_id'";
        mysqli_query($kon, $sql);
    }

    // Calculate updated total for the item
    $query_total = "SELECT k.jumlah, p.harga FROM keranjang k 
                    JOIN produk p ON k.id_produk = p.id_produk 
                    WHERE k.id_keranjang = '$cart_id'";
    $result_total = mysqli_query($kon, $query_total);
    $item = mysqli_fetch_assoc($result_total);

    $updated_total = $item ? $item['jumlah'] * $item['harga'] : 0;

    echo json_encode(['item_total' => $updated_total]);
    exit();
}

// Remove item from cart
if (isset($_POST['remove_item'])) {
    $cart_id = $_POST['cart_id'];
    $sql = "DELETE FROM keranjang WHERE id_keranjang = '$cart_id'";
    mysqli_query($kon, $sql);

    // After successful deletion, return JSON response
    echo json_encode(['action' => 'removed']);  // Respond that the item was removed
    exit();
}

// Get the products in the cart
$cartItems = mysqli_query($kon, "SELECT k.*, p.nama_produk, p.harga, p.gambar 
    FROM keranjang k 
    JOIN produk p ON k.id_produk = p.id_produk 
    WHERE k.user_id = $user_id");

$row_keranjang = ($cartItems && mysqli_num_rows($cartItems) > 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <title>Keranjang Belanja</title>
    <?php include "aset.php"; ?>
</head>
<body>
    <!-- Header -->
    <?php require "atas.php"; ?>
    <!-- Sidebar -->
    <?php require "menu.php"; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1><i class="bi bi-cart"></i> Keranjang Belanja</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Keranjang Belanja</li>
                </ol>
            </nav>
        </div>

        <div class="container mt-5">
            <?php if ($row_keranjang): ?>
                <form method="POST" action="checkout.php">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Pilih</th>
                                <th>Gambar</th>
                                <th>Nama Produk</th>
                                <th>Harga</th>
                                <th>Jumlah</th>
                                <th>Total</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $grandTotal = 0; ?>
                            <?php while ($item = mysqli_fetch_assoc($cartItems)): ?>
                                <tr>
                                    <td><input type="checkbox" name="selected_items[]" value="<?= $item['id_keranjang']; ?>"></td>
                                    <td><img src="../uploads/<?= htmlspecialchars($item['gambar']); ?>" alt="Gambar Produk" width="100"></td>
                                    <td><?= htmlspecialchars($item['nama_produk']); ?></td>
                                    <td>Rp <?= number_format($item['harga'], 0, ',', '.'); ?></td>
                                    <td>
                                        <input type="number" name="quantity" value="<?= $item['jumlah']; ?>" 
                                               class="form-control quantity-input" style="width: 80px;"
                                               data-cart-id="<?= $item['id_keranjang']; ?>">
                                    </td>
                                    <td class="item-total">Rp <?= number_format($item['harga'] * $item['jumlah'], 0, ',', '.'); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger remove-item" data-cart-id="<?= $item['id_keranjang']; ?>">Hapus</button>
                                    </td>
                                </tr>
                                <?php $grandTotal += $item['harga'] * $item['jumlah']; ?>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-between">
                        <a href="produk.php" class="btn btn-secondary">Kembali</a>
                        <button type="submit" name="checkout" class="btn btn-success">Checkout</button>
                    </div>
                </form>
                <br>
                <div class="d-flex justify-content-end">
                    <h4>Total Pembayaran: <span id="grand-total">Rp <?= number_format($grandTotal, 0, ',', '.'); ?></span></h4>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">Keranjang Anda kosong.</div>
                <a href="produk.php" class="btn btn-secondary">Kembali ke Halaman Produk</a>
            <?php endif; ?>
        </div>
    </main>
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        // Event listener for quantity changes
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', function () {
                const cartId = this.dataset.cartId;
                const newQuantity = this.value;

                // Send AJAX request to update quantity
                fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `update_quantity=1&cart_id=${cartId}&quantity=${newQuantity}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.action === 'removed') {
                        // If item is removed, reload the page
                        location.reload();
                    } else {
                        // Update total for the item
                        const itemRow = this.closest('tr');
                        itemRow.querySelector('.item-total').textContent = 
                            `Rp ${new Intl.NumberFormat('id-ID').format(data.item_total)}`;

                        // Recalculate grand total
                        let grandTotal = 0;
                        document.querySelectorAll('.item-total').forEach(total => {
                            grandTotal += parseInt(total.textContent.replace(/[^\d]/g, '')) || 0;
                        });
                        document.getElementById('grand-total').textContent = 
                            `Rp ${new Intl.NumberFormat('id-ID').format(grandTotal)}`;
                    }
                });
            });
        });

        // Event listener for remove item button
        document.querySelectorAll('.remove-item').forEach(button => {
            button.addEventListener('click', function () {
                const cartId = this.dataset.cartId;

                // Send AJAX request to remove item
                fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `remove_item=1&cart_id=${cartId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.action === 'removed') {
                        // If item is removed, reload the page
                        location.reload();
                    }
                });
            });
        });
    </script>
</body>
</html>
