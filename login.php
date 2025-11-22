<?php
session_start();
require "db.php";
$error = "";

if (isset($_POST["login"])) {
  // LOGIN USER
  $email = mysqli_real_escape_string($kon, isset($_POST["email"]) ? $_POST["email"] : "");
  $pwd = mysqli_real_escape_string($kon, isset($_POST["pwd"]) ? $_POST["pwd"] : "");

  // CEK APAKAH MASIH KOSONG
  if (empty($email) || empty($pwd)) {
      $msg = '
          <div class="alert alert-warning">
              &nbsp; MAAF, EMAIL / PASSWORD ANDA MASIH KOSONG. SILAHKAN ISI DENGAN BENAR!
          </div>
      ';
  } else {
      // CEK DAN HAPUS AKUN YANG TIDAK AKTIF SELAMA 30 HARI
      // $query_delete = "DELETE FROM user WHERE DATE_ADD(active, INTERVAL 30 DAY) < NOW()";
      // mysqli_query($kon, $query_delete);

      // PROSEDUR LOGIN UNTUK ADMIN
      $kue_admin = mysqli_query($kon, "SELECT * FROM user WHERE email = '$email' AND password = '$pwd'");
      $row_admin = mysqli_fetch_array($kue_admin);

      if (!$row_admin) {
          $msg = '
              <div class="alert alert-danger">
                  &nbsp; MAAF, EMAIL / PASSWORD ANDA SALAH. SILAHKAN ULANGI LAGI!
              </div>
          ';
      } else {
          if ($row_admin["level"] == "admin") {
              // UPDATE ACTIVE FIELD
              $update_admin_active = "UPDATE user SET active = NOW() WHERE email = '$email'";
              mysqli_query($kon, $update_admin_active);

              // SET SESSION DAN REDIRECT
              $_SESSION["admin"] = $row_admin["nama"];
              header("Location: admin/index.php");
              exit;
          }
      }

      // PROSEDUR LOGIN UNTUK USER
      $kue_user = mysqli_query($kon, "SELECT * FROM user WHERE email = '$email' AND password = '$pwd'");
      $row_user = mysqli_fetch_array($kue_user);

      if (!$row_user) {
          $msg = '
              <div class="alert alert-danger">
                  <i class="bi bi-exclamation-circle-fill"></i>&nbsp; MAAF, EMAIL / PASSWORD ANDA SALAH. SILAHKAN ULANGI LAGI!
              </div>
          ';
      } else {
          if ($row_user["level"] == "user") {
              // UPDATE ACTIVE FIELD
              $update_user_active = "UPDATE user SET active = NOW() WHERE email = '$email'";
              mysqli_query($kon, $update_user_active);

              // SET SESSION DAN REDIRECT
              $_SESSION["user"] = $row_user["nama"];
              header("Location: user/index.php");
              exit;
          }
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

  <title>LOGIN USER</title>
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
                    <h5 class="card-title text-center pb-0 fs-4"><i class="bi bi-person"></i>&nbsp; LOGIN ACCOUNT</h5>
                    <p class="text-center small">Masukkan identitas Anda disini</p>
                  </div>

                  <form method="post" class="row g-3 needs-validation">

                    <div class="col-12">
                      <label class="form-label"><i class="bi bi-envelope"></i>&nbsp; EMAIL</label>
                      <div class="input-group has-validation">
                        <input type="text" name="email" class="form-control" placeholder="Masukkan email Anda" required>
                        <div class="invalid-feedback">Masukkan email anda dengan benar.</div>
                      </div>
                    </div>

                    <div class="col-12">
                      <label class="form-label"><i class="bi bi-lock"></i>&nbsp; PASSWORD</label>
                      <input type="password" name="pwd" class="form-control" placeholder="Masukkan password Anda" required>
                      <div class="invalid-feedback">Masukkan password Anda dengan benar.</div>
                    </div>

                    <div class="col-12">
                      <button name="login" class="btn btn-primary w-100" type="submit"><i class="bi bi-arrow-right"></i>&nbsp; LOGIN</button>
                    </div>

                    <div class="col-12">
                      <a href="register.php" class="btn btn-secondary w-100"><i class="bi bi-pencil"></i>&nbsp; REGISTER</a>
                    </div>
              
                  </form>

                </div>
              </div>
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
