<?php
session_start();
require_once '../config/database.php';

// Filter
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$kota_asal = isset($_GET['kota_asal']) ? clean_input($_GET['kota_asal']) : '';
$kota_tujuan = isset($_GET['kota_tujuan']) ? clean_input($_GET['kota_tujuan']) : '';
$jenis_mobil = isset($_GET['jenis_mobil']) ? intval($_GET['jenis_mobil']) : 0;

// Query driver
$where_conditions = ["d.status_verifikasi = 'verified'", "u.status = 'active'"];

if($search) {
    $search_safe = mysqli_real_escape_string($conn, $search);
    $where_conditions[] = "(d.nama_lengkap LIKE '%$search_safe%' OR d.alamat LIKE '%$search_safe%' OR d.deskripsi LIKE '%$search_safe%')";
}

if($kota_asal && $kota_tujuan) {
    $asal_safe = mysqli_real_escape_string($conn, $kota_asal);
    $tujuan_safe = mysqli_real_escape_string($conn, $kota_tujuan);
    $where_conditions[] = "d.id_driver IN (SELECT id_driver FROM rute_driver WHERE kota_asal LIKE '%$asal_safe%' AND kota_tujuan LIKE '%$tujuan_safe%')";
} elseif($kota_asal) {
    $asal_safe = mysqli_real_escape_string($conn, $kota_asal);
    $where_conditions[] = "d.id_driver IN (SELECT id_driver FROM rute_driver WHERE kota_asal LIKE '%$asal_safe%')";
} elseif($kota_tujuan) {
    $tujuan_safe = mysqli_real_escape_string($conn, $kota_tujuan);
    $where_conditions[] = "d.id_driver IN (SELECT id_driver FROM rute_driver WHERE kota_tujuan LIKE '%$tujuan_safe%')";
}

if($jenis_mobil > 0) {
    $where_conditions[] = "d.id_driver IN (SELECT id_driver FROM mobil_driver WHERE id_jenis_mobil = $jenis_mobil)";
}

$where_clause = implode(' AND ', $where_conditions);

$query = "SELECT d.*, u.status,
          (SELECT AVG(rating) FROM rating_driver WHERE id_driver = d.id_driver) as avg_rating,
          (SELECT COUNT(*) FROM rating_driver WHERE id_driver = d.id_driver) as total_rating
          FROM driver d
          JOIN users u ON d.id_user = u.id_user
          WHERE $where_clause
          ORDER BY d.created_at DESC";

$result = mysqli_query($conn, $query);

// Ambil jenis mobil untuk filter
$query_jenis = "SELECT * FROM jenis_mobil ORDER BY nama_jenis";
$result_jenis = mysqli_query($conn, $query_jenis);

// Ambil kota unik untuk autocomplete
$query_kota = "SELECT DISTINCT kota_asal FROM rute_driver 
               UNION SELECT DISTINCT kota_tujuan FROM rute_driver 
               ORDER BY kota_asal";
$result_kota = mysqli_query($conn, $query_kota);
$kota_list = [];
while($kota = mysqli_fetch_assoc($result_kota)) {
    $kota_list[] = $kota['kota_asal'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cari Driver - SopirPilihan.id</title>
    <meta name="description" content="Temukan driver terpercaya untuk perjalanan antar kota Anda. Filter berdasarkan rute, jenis mobil, dan rating.">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<style>
    body {
        /* 1. Pasang gambar latar belakang ke seluruh body */
        background: linear-gradient(rgba(19, 21, 26, 0.28), rgba(8, 11, 23, 0.39)), 
                    url('../assets/images/BG18.png') no-repeat center center fixed;
        
        /* 2. Pastikan gambar menutupi seluruh layar */
        background-size: cover !important;
        
        /* 3. Menjaga konten tetap di atas */
        min-height: 100vh;
    }

    header {
    /* Background putih transparan */
    background: rgba(255, 255, 255, 0.9) !important; 
    
    /* Efek blur pada objek di belakang navbar */
    backdrop-filter: blur(12px) !important;
    -webkit-backdrop-filter: blur(12px) !important; /* Dukungan Safari */
    
    /* Border tipis untuk kesan kilauan kaca */
    border-bottom: 1px solid rgba(255, 255, 255, 0.2) !important;
    
    /* Bayangan lembut agar navbar terpisah dari konten */
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1) !important;
    
    position: sticky;
    top: 0;
    z-index: 1000;
    padding: 1rem 0;
    transition: all 0.3s ease;
}

/* Pastikan teks navbar kontras dengan efek kaca */
nav a {
    color: #2563eb !important; /* Warna putih agar terlihat di atas hero dark */
    font-weight: 500;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.logo {
    color: #2563eb !important;
    font-weight: 800;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

/* Hover effect pada link agar tetap elegan */
nav a:hover {
    color: rgba(37, 99, 235, 1) !important; /* Berubah jadi biru saat hover */
    background: rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    padding: 0.5rem 1rem;
}


    /* Membuat section filter dan card sedikit transparan agar terlihat modern */
    .section, .driver-card {
        background: rgba(255, 255, 255, 0.9) !important;
        backdrop-filter: blur(10px); /* Efek kaca buram */
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .hero-search {
        background: transparent !important; /* Karena background sudah di body */
        color: #1a1a1a;
        padding: 4rem 2rem;
        text-align: center;
    }

    .hero-search h1 {
        color: #1e40af;
        text-shadow: none;
    }

    /* Styling Tombol Cari */
    .btn-cari-warna {
        flex: 2;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.7rem;
        padding: 0.9rem;
        background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        text-decoration: none;
    }

    .btn-cari-warna:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
        background: linear-gradient(135deg, #1d4ed8 0%, #1e3a8a 100%);
    }

    /* Styling Tombol Reset */
    .btn-reset-warna {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.7rem;
        padding: 0.9rem;
        background: #fff1f2;
        color: #e11d48;
        border: 1px solid #fecdd3;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .btn-reset-warna:hover {
        background: #ffe4e6;
        color: #be123c;
        border-color: #fda4af;
    }

</style>
<body>
    <header>
        <nav>
            <div class="logo">SopirPilihan.id</div>
            <ul>
                <li><a href="../index.php">Beranda</a></li>
                <li><a href="daftar_driver.php">Cari Driver</a></li>
                <li><a href="pengiriman_barang.php">Kirim Barang</a></li>
                <li><a href="tentang.php">Tentang</a></li>
                <?php if(isset($_SESSION['id_user']) && !empty($_SESSION['id_user'])): ?>
                    <li><a href="../<?php echo $_SESSION['role']; ?>/dashboard.php">Dashboard</a></li>
                    <li><a href="../auth/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="../auth/login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="section" style="text-align: center; margin-bottom: 2rem;">
            <h1 style="font-size: 2.5rem; margin-bottom: 1rem; color: #1a1a1a;"> Cari Driver Antar Kota</h1>
            <p style="font-size: 1.1rem; color: #64748b;">Temukan driver terpercaya untuk perjalanan Anda dengan mudah</p>
        </div>

        <div class="section">
            <h2 style="font-size: 1.5rem; margin-bottom: 1.5rem; color: #1a1a1a;"> Filter Pencarian</h2>
            <form method="GET" action="">
                <div class="grid-2" style="margin-bottom: 1rem;">
                    <div class="form-group">
                        <label>Cari Nama Driver</label>
                        <input type="text" name="search" placeholder="Masukkan nama driver..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Jenis Mobil</label>
                        <select name="jenis_mobil">
                            <option value="">Semua Jenis Mobil</option>
                            <?php 
                            mysqli_data_seek($result_jenis, 0);
                            while($jenis = mysqli_fetch_assoc($result_jenis)): 
                            ?>
                            <option value="<?php echo $jenis['id_jenis_mobil']; ?>" 
                                    <?php echo $jenis_mobil == $jenis['id_jenis_mobil'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($jenis['nama_jenis']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                
                <div class="grid-2" style="margin-bottom: 1.5rem;">
                    <div class="form-group">
                        <label>Kota Asal</label>
                        <input type="text" name="kota_asal" placeholder="Contoh: Makassar" 
                               value="<?php echo htmlspecialchars($kota_asal); ?>" 
                               list="kota-list">
                    </div>
                    
                    <div class="form-group">
                        <label>Kota Tujuan</label>
                        <input type="text" name="kota_tujuan" placeholder="Contoh: Bone" 
                               value="<?php echo htmlspecialchars($kota_tujuan); ?>" 
                               list="kota-list">
                    </div>
                </div>
                
                <datalist id="kota-list">
                    <?php foreach($kota_list as $kota): ?>
                    <option value="<?php echo htmlspecialchars($kota); ?>">
                    <?php endforeach; ?>
                </datalist>
                
<div style="display: flex; gap: 1rem; margin-top: 2rem;">
    <button type="submit" class="btn-cari-warna">
        <span></span> Cari Driver Sekarang
    </button>
    
    <a href="daftar_driver.php" class="btn-reset-warna">
        <span></span> Reset Filter
    </a>
</div>
            </form>
        </div>

        <div style="margin-bottom: 2rem; padding: 1rem; background: white; border-radius: 12px; border-left: 4px solid #2563eb;">
            <strong style="color: #2563eb; font-size: 1.1rem;"><?php echo mysqli_num_rows($result); ?> driver ditemukan</strong>
            <?php if($search || $kota_asal || $kota_tujuan || $jenis_mobil): ?>
            <span style="color: #64748b; margin-left: 1rem;">dengan filter aktif</span>
            <?php endif; ?>
        </div>

        <?php if(mysqli_num_rows($result) > 0): ?>
        <div class="driver-grid">
            <?php while($driver = mysqli_fetch_assoc($result)): ?>
            <div class="driver-card">
                <img src="<?php echo !empty($driver['foto_profil']) ? '../uploads/drivers/profile/'.htmlspecialchars($driver['foto_profil']) : '../assets/images/default-avatar.png'; ?>" 
                     alt="<?php echo htmlspecialchars($driver['nama_lengkap']); ?>" 
                     class="driver-photo"
                     onerror="this.src='../assets/images/default-avatar.png'">
                <div class="driver-info">
                    <div class="driver-name"><?php echo htmlspecialchars($driver['nama_lengkap']); ?></div>
                    <div class="driver-rating">
                        <?php if($driver['avg_rating']): ?>
                            ⭐ <?php echo number_format($driver['avg_rating'], 1); ?> 
                            <span style="color: #64748b; font-weight: 500;">(<?php echo $driver['total_rating']; ?> ulasan)</span>
                        <?php else: ?>
                            ⭐ <span style="color: #64748b; font-weight: 500;">Belum ada rating</span>
                        <?php endif; ?>
                    </div>
                    
                    <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1rem; color: #64748b; font-size: 0.9rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span></span>
                            <span><?php echo htmlspecialchars($driver['no_hp']); ?></span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span></span>
                            <span><?php echo htmlspecialchars(substr($driver['alamat'], 0, 50)); ?>...</span>
                        </div>
                    </div>
                    
                    <p style="color: #64748b; margin-bottom: 1rem; line-height: 1.6;">
                        <?php echo !empty($driver['deskripsi']) ? htmlspecialchars(substr($driver['deskripsi'], 0, 100)) : 'Driver profesional dengan pengalaman terpercaya'; ?>...
                    </p>
                    
                    <div class="driver-contact">
<a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $driver['no_whatsapp']); ?>" 
   target="_blank" 
   class="btn btn-whatsapp btn-hitung-klik" 
   data-id="<?php echo $driver['id_driver']; ?>"> 
   WhatsApp
</a>
                        <a href="detail_driver.php?id=<?php echo $driver['id_driver']; ?>" 
                           class="btn btn-detail"> Detail</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="section" style="text-align: center; padding: 4rem 2rem;">
            <div style="font-size: 4rem; margin-bottom: 1rem;">😔</div>
            <h2 style="color: #2563eb; margin-bottom: 1rem; font-size: 1.75rem;">Driver Tidak Ditemukan</h2>
            <p style="color: #64748b; margin-bottom: 2rem;">Maaf, tidak ada driver yang sesuai dengan kriteria pencarian Anda.</p>
            <a href="daftar_driver.php" class="btn btn-primary"> Reset Pencarian</a>
        </div>
        <?php endif; ?>
    </div>

    <footer>
        <div style="max-width: 1200px; margin: 0 auto;">
            <h3 style="font-size: 1.5rem; margin-bottom: 1rem;"> SopirPilihan.id</h3>
            <p style="margin-bottom: 0.5rem;">Solusi Transportasi Pribadi Anti-Ribet</p>
            <p style="margin-bottom: 1.5rem; opacity: 0.8;">Platform gratis tanpa biaya atau komisi</p>
            
            <div style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1.5rem; margin-top: 1.5rem;">
                <p>&copy; <?php echo date('Y'); ?> SopirPilihan.id - All Rights Reserved</p>
            </div>
        </div>
    </footer>
 <script>
document.querySelectorAll('.btn-hitung-klik').forEach(button => {
    button.addEventListener('click', function() {
        const driverId = this.getAttribute('data-id');
        
        // Gunakan path absolut dari root agar lebih aman
        // Ganti '/platform_SopirPilihan/' sesuai dengan nama folder project Anda di htdocs
        fetch('/platform_SopirPilihan/admin/hitung_klik_ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + driverId
        })
        .then(response => response.text())
        .then(data => {
            console.log('Klik tercatat:', data);
        })
        .catch(err => console.error('Error:', err));
    });
});
</script>
</body>
</html>