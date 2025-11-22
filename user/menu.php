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
        <a class="nav-link <?php if($page == "produk"){ echo "collapsed"; } ?>" href="produk.php">
          <i class="bi bi-box-seam"></i>
          <span>PRODUK</span>
        </a>
      </li> 

      <li class="nav-item">
        <a class="nav-link <?php if($page == "info"){ echo "collapsed"; } ?>" href="informasipromo.php">
          <i class="bi bi-megaphone"></i>
          <span>INFORMASI PROMO</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link <?php if($page == "promo"){ echo "collapsed"; } ?>" href="promo.php">
          <i class="bi bi-tags"></i>
          <span>PROMO</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link <?php if($page == "history_pembayaran"){ echo "collapsed"; } ?>" href="history_pembayaran.php">
          <i class="bi bi-cash-stack"></i>
          <span>HISTORY PEMBAYARAN</span>
        </a>
      </li> 

      <li class="nav-item">
        <a class="nav-link <?php if($page == "pesanan_selesai"){ echo "collapsed"; } ?>" href="pesanan_selesai.php">
          <i class="bi bi-bag-check"></i>
          <span>PESANAN SELESAI</span>
        </a>
      </li>
      

      <li class="nav-item">
        <a class="nav-link <?php if($page == "pesanan_diproses"){ echo "collapsed"; } ?>" href="pesanan_diproses.php">
          <i class="bi bi-clock-history"></i>
          <span>PESANAN DIPROSES</span>
        </a>
      </li>
      
      <li class="nav-item">
        <a class="nav-link <?php if($page == "pesanan_dikirim"){ echo "collapsed"; } ?>" href="pesanan_dikirim.php">
          <i class="bi bi-truck"></i>
          <span>PESANAN DIKIRIM</span>
        </a>
      </li> 

      <li class="nav-item">
        <a class="nav-link <?php if($page == "pesanan_dibatalkan"){ echo "collapsed"; } ?>" href="pesanan_dibatalkan.php">
          <i class="bi bi-x-lg"></i>
          <span>PESANAN DIBATALKAN</span>
        </a>
      </li>
      
      <li class="nav-item">
        <a class="nav-link <?php if($page == "fav"){ echo "collapsed"; } ?>" href="most_favorite.php">
          <i class="bi bi-star-fill"></i>
          <span>FAVORITE</span>
        </a>
      </li>
      
      <li class="nav-item">
        <a class="nav-link <?php if($page == "history"){ echo "collapsed"; } ?>" href="pengeluaran.php">
          <i class="bi bi-wallet2"></i>
          <span>PENGELUARAN</span>
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