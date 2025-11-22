<header id="header" class="header fixed-top d-flex align-items-center">

  <div class="container-fluid d-flex align-items-center justify-content-between">

    <div class="d-flex align-items-center">
      <a href="index.php" class="logo d-flex align-items-center">
        <span class="d-none d-lg-block"><i class="bi bi-controller"></i>&nbsp; Gamify</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn ms-3"></i>
    </div><!-- End Logo -->

    <nav class="header-nav">
      <ul class="d-flex align-items-center mb-0">

        <li class="nav-item d-block d-lg-none">
          <a class="nav-link nav-icon search-bar-toggle" href="search.php">
            <i class="bi bi-search"></i>
          </a>
        </li><!-- End Search Icon-->

        <li class="nav-item dropdown pe-3">

          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            <span class="d-none d-md-block dropdown-toggle ps-2"><i style="font-size: 20px" class="bi bi-person"></i>&nbsp; Halo, <?= $_SESSION["user"] ?>!</span>
          </a><!-- End Profile Image Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6><?= $_SESSION["user"] ?></h6>
              <span>Selamat Berbelanja!</span>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="profil.php">
                <i class="bi bi-person"></i>
                <span>Profile</span>
              </a>
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="notifikasi.php">
                <i class="bi bi-bell-fill"></i>
                <span>Notifikasi</span>
              </a>
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="chat.php">
                <i class="bi bi-headset"></i>
                <span>Costumer Service</span>
              </a>
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="add_to_cart.php">
                <i class="bi bi-cart"></i>
                <span>Shopping Cart</span>
              </a>
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="wishlist.php">
                <i class="bi bi-bookmark-heart"></i>
                <span>Wishlist</span>
              </a>
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="feedback.php">
                <i class="bi bi-chat-square-text"></i>
                <span>Feedback</span>
              </a>
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="logout.php">
                <i class="bi bi-box-arrow-right"></i>
                <span>Log-Out</span>
              </a>
            </li>

          </ul><!-- End Profile Dropdown Items -->
        </li><!-- End Profile Nav -->

      </ul>
    </nav><!-- End Icons Navigation -->

  </div><!-- End Container -->
</header><!-- End Header -->
