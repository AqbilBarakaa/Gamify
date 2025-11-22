<?php
$host = 'localhost';
$dbname = 'gamify';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id_pesanan = $_POST['id_pesanan'];
        $status_pesanan = $_POST['status_pesanan'];

        $query = "UPDATE pesanan SET status_pesanan = :status_pesanan WHERE id_pesanan = :id_pesanan";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':status_pesanan', $status_pesanan);
        $stmt->bindParam(':id_pesanan', $id_pesanan);

        if ($stmt->execute()) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
} catch (PDOException $e) {
    echo 'error';
}
?>
