<?php
session_start();
$page = "order";
if (!isset($_SESSION["admin"])) {
    header("Location: ../login.php");
    exit;
}

$host = 'localhost';
$dbname = 'gamify';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}

// Filter status jika ada
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Query untuk menampilkan daftar pesanan dengan join tabel user dan produk
$query = "SELECT p.id_pesanan, u.nama AS customer_name, pr.nama_produk, 
                 p.tanggal_pesanan, p.total_harga, p.status_pesanan
          FROM pesanan p
          JOIN user u ON p.id_user = u.id_user
          JOIN produk pr ON p.id_produk = pr.id_produk";

if (!empty($status_filter)) {
    $query .= " WHERE p.status_pesanan = :status";
}
$query .= " ORDER BY p.tanggal_pesanan DESC";

$stmt = $pdo->prepare($query);

if (!empty($status_filter)) {
    $stmt->bindParam(':status', $status_filter);
}
$stmt->execute();
$pesanan = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table th, .table td {
            vertical-align: middle;
            text-align: center;
        }
    </style>
    <?php include 'aset.php'; ?>
</head>
<body>
<?php include 'atas.php'; ?>
<?php include 'menu.php'; ?>

<main id="main" class="main">
    <div class="container">
        <div class="row">
            <div class="col">
                <h1><i class="bi bi-grid"></i>&nbsp; Daftar Pesanan</h1>
            </div>
        </div>

        <!-- Filter Status -->
        <form method="GET" class="mb-3">
            <label for="status" class="form-label">Filter Status:</label>
            <select name="status" id="status" class="form-select" onchange="this.form.submit()">
                <option value="">Semua</option>
                <option value="Diproses" <?= $status_filter == 'Diproses' ? 'selected' : '' ?>>Diproses</option>
                <option value="Dikirim" <?= $status_filter == 'Dikirim' ? 'selected' : '' ?>>Dikirim</option>
                <option value="Dibatalkan" <?= $status_filter == 'Dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
            </select>
        </form>

        <div class="card">
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Pembeli</th>
                            <th>Produk</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!empty($pesanan)) {
                            foreach ($pesanan as $index => $order) {
                                if($order['status_pesanan'] !== "Selesai"){
                                ?>
                                
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                    <td><?= htmlspecialchars($order['nama_produk']) ?></td>
                                    <td><?= $order['tanggal_pesanan'] ?></td>
                                    <td>Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></td>
                                    <td>
                                        <select class="form-select status-dropdown" 
                                                data-id="<?= $order['id_pesanan'] ?>">
                                            <option value="Diproses" <?= $order['status_pesanan'] == 'Diproses' ? 'selected' : '' ?>>Diproses</option>
                                            <option value="Dikirim" <?= $order['status_pesanan'] == 'Dikirim' ? 'selected' : '' ?>>Dikirim</option>
                                            <option value="Dibatalkan" <?= $order['status_pesanan'] == 'Dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                                        </select>
                                    </td>
                                </tr>
                                <?php
                                }
                            } 
                        }else{
                            echo "<tr><td colspan='6'>Tidak ada data pesanan.</td></tr>";
                        }
                        ?>
                        
                    
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const statusDropdowns = document.querySelectorAll('.status-dropdown');
    
    statusDropdowns.forEach(dropdown => {
        dropdown.addEventListener('change', function () {
            const id = this.getAttribute('data-id');
            const newStatus = this.value;

            fetch('update_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `id_pesanan=${id}&status_pesanan=${newStatus}`
            })
            .then(response => response.text())
            .then(data => {
                if (data === 'success') {
                    location.reload();
                } else {
                    alert('Gagal memperbarui status.');
                }
            });
        });
    });
});
</script>
</body>
</html>
