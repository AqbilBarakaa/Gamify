<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

    <h5 class="text-center">MAIN MENU</h5>

      <li class="nav-item">
        <a class="nav-link <?php if($page == "dashboard"){ echo "collapsed"; } ?>" href="index.php">
          <i class="bi bi-grid"></i>
          <span>DASHBOARD</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link <?php if($page == "user"){ echo "collapsed"; } ?>" href="user.php">
          <i class="bi bi-person"></i>
          <span>DATA USER</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link <?php if($page == "produk"){ echo "collapsed"; } ?>" href="produk.php">
          <i class="bi bi-box-seam-fill"></i>
          <span>DATA PRODUK</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link <?php if($page == "kategori"){ echo "collapsed"; } ?>" href="kategori.php">
          <i class="bi bi-list-nested"></i>
          <span>DATA KATEGORI</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link <?php if($page == "info"){ echo "collapsed"; } ?>" href="informasipromo.php">
          <i class="bi bi-megaphone"></i>
          <span>INFORMASI PROMO</span>
        </a>
      </li>
      

      <li class="nav-item">
        <a class="nav-link <?php if($page == "order"){ echo "collapsed"; } ?>" href="order.php">
          <i class="bi bi-currency-dollar"></i>
          <span>ORDER MASUK</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link <?php if($page == "penjualan"){ echo "collapsed"; } ?>" href="penjualan.php">
          <i class="bi bi-graph-up"></i>
          <span>PENJUALAN</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link <?php if($page == "promo"){ echo "collapsed"; } ?>" href="promo.php">
          <i class="bi bi-tags"></i>
          <span>PROMO</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link <?php if($page == "statistik"){ echo "collapsed"; } ?>" href="stok_produk.php">
          <i class="bi bi-clipboard-data"></i>
          <span>STOK & STATISTIK</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link <?php if($page == "komunitas"){ echo "collapsed"; } ?>" href="forum_komunitas.php">
          <i class="bi bi-headset"></i>
          <span>COMMUNITY</span>
        </a>
      </li> 

      <li class="nav-item">
        <a class="nav-link" href="logout.php">
          <i class="bi bi-box-arrow-right"></i>
          <span>LOG-OUT</span>
        </a>
      </li>

    </ul>

</aside>