<?php
session_start();
require "db.php";
$error = "";

if (isset($_POST["register"])){
  $nama_user = mysqli_real_escape_string($kon, isset($_POST["nama_user"]) ? $_POST["nama_user"] : "");
  $email = mysqli_real_escape_string($kon, isset($_POST["email"]) ? $_POST["email"] : "");
  $password = mysqli_real_escape_string($kon, isset($_POST["password"]) ? $_POST["password"] : "");
  $no_tlp = mysqli_real_escape_string($kon, isset($_POST["no_tlp"]) ? $_POST["no_tlp"] : "");
  $alamat = mysqli_real_escape_string($kon, isset($_POST["alamat"]) ? $_POST["alamat"] : "");
  $level = mysqli_real_escape_string($kon, isset($_POST["level"]) ? $_POST["level"] : "");

  if (empty($nama_user) or empty($email) or empty($password) or empty($no_tlp) or empty($alamat) or empty($level)){
    $msg = '
      <div class="alert alert-warning">
        &nbsp; MAAF, SEMUA FIELD HARUS DIISI. SILAHKAN ISI DENGAN BENAR !
      </div>
    ';
  } else {
    $query = mysqli_query($kon, "INSERT INTO user (nama, email, password, no_tlp, alamat, level) VALUES ('$nama_user', '$email', '$password', '$no_tlp', '$alamat', '$level')");
    if ($query){
      $msg = '
        <div class="alert alert-success">
          &nbsp; REGISTER BERHASIL. SILAHKAN LOGIN !
        </div>
      ';
      header("Location: login.php");
      exit(); // Pastikan script berhenti setelah redirect
    } else {
      $msg = '
        <div class="alert alert-danger">
          &nbsp; REGISTER GAGAL. SILAHKAN ULANGI LAGI !
        </div>
      ';
    }
  }

  $error = true;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>REGISTER USER</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i" rel="stylesheet">

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

</head>

<body>

  <main>
    <div class="container">

      <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-9 col-md-6 d-flex flex-column align-items-center justify-content-center">

              <div class="d-flex justify-content-center py-4">
                <a href="#" class="logo d-flex align-items-center w-auto">
                  <span class="d-none d-lg-block"><i class="bi bi-controller"></i>&nbsp; Gamify</span>
                </a>
              </div><!-- End Logo -->

              <?php
              if ($error){
                echo $msg ;
              }
              ?>

              <div class="card mb-3">

                <div class="card-body">

                  <div class="pt-4 pb-2">
                    <h5 class="card-title text-center pb-0 fs-4"><i class="bi bi-person"></i>&nbsp; REGISTER ACCOUNT</h5>
                    <p class="text-center small">Masukkan informasi Anda disini</p>
                  </div>

                  <form method="post" class="row g-3 needs-validation">

                    <div class="col-12">
                      <label class="form-label"><i class="bi bi-person"></i>&nbsp; NAMA LENGKAP</label>
                      <div class="input-group has-validation">
                        <input type="text" name="nama_user" class="form-control" placeholder="Masukkan nama lengkap anda" required>
                        <div class="invalid-feedback">Masukkan nama anda dengan benar.</div>
                      </div>
                    </div>

                    <div class="col-12">
                      <label class="form-label"><i class="bi bi-envelope"></i>&nbsp; EMAIL</label>
                      <div class="input-group has-validation">
                        <input type="text" name="email" class="form-control" placeholder="Masukkan Email Anda" required>
                        <div class="invalid-feedback">Masukkan Email Anda dengan benar.</div>
                      </div>
                    </div>

                    <div class="col-12">
                      <label class="form-label"><i class="bi bi-telephone"></i>&nbsp; NOMOR TELEPON</label>
                      <div class="input-group has-validation">
                        <input type="text" name="no_tlp" class="form-control" placeholder="Masukkan Nomor Telepon Anda" required>
                        <div class="invalid-feedback">Masukkan Nomor Anda dengan benar.</div>
                      </div>
                    </div>

                    <div class="col-12">
                      <label class="form-label"><i class="bi bi-house"></i>&nbsp; ALAMAT</label>
                      <div class="input-group has-validation">
                        <input type="text" name="alamat" class="form-control" placeholder="Masukkan Alamat Anda" required>
                        <div class="invalid-feedback">Masukkan Alamat Anda dengan benar.</div>
                      </div>
                    </div>

                    <div class="col-12">
                      <label class="form-label"><i class="bi bi-lock"></i>&nbsp; PASSWORD</label>
                      <input type="password" name="password" class="form-control" placeholder="Masukkan password Anda" required>
                      <div class="invalid-feedback">Masukkan password Anda dengan benar.</div>
                    </div>

                    <div class="col-12">
                      <label class="form-label"><i class="bi bi-people"></i>&nbsp; Level</label>
                      <select name="level" class="form-control" required>
                        <option value="">Pilih Level Anda Sebagai User</option>
                        <option value="user">User</option>
                      </select>
                      <div class="invalid-feedback">Pilih level anda sebagai user.</div>
                    </div>

                    <div class="col-12">
                      <button name="register" class="btn btn-primary w-100" type="submit"><i class="bi bi-pencil"></i>&nbsp; REGISTER</button>
                    </div>
                    
                  </form>

                </div>
              </div>

              <!-- <div class="credits">
                Copyright &copy; <?= date("Y") ?> <b>SPPTels</b>, All Rights Reserved.
                <br>
                <center>HTML only to HTML-PHP-jQuery by <b><i>XperiorDev.</i></b></center>
              </div> -->

            </div>
          </div>
        </div>

      </section>

    </div>
  </main><!-- End #main -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/chart.js/chart.umd.js"></script>
  <script src="assets/vendor/echarts/echarts.min.js"></script>
  <script src="assets/vendor/quill/quill.min.js"></script>
  <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

</body>

</html>
