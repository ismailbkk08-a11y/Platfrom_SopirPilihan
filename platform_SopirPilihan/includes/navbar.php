<nav class="navbar">
    <div class="container">
        <a href="../index.php" class="logo">SopirPilihan<span>.id</span></a>
        <ul class="nav-links">
            <li><a href="../index.php">Home</a></li>
            <li><a href="../pages/daftar_driver.php">Cari Sopir</a></li>
            <li><a href="../pages/pengiriman_barang.php">Kirim Barang</a></li>
            <li><a href="../pages/tentang.php">Tentang Kami</a></li>
            <?php if(isset($_SESSION['login'])): ?>
                <li><a href="../auth/logout.php" class="btn-logout">Logout</a></li>
            <?php else: ?>
                <li><a href="../auth/login.php" class="btn-login">Login</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>