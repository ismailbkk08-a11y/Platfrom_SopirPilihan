<?php
session_start();
require_once 'config/database.php';

// Ambil driver terverifikasi terbaru
$query_driver = "SELECT d.*, u.status, 
                 (SELECT AVG(rating) FROM rating_driver WHERE id_driver = d.id_driver) as avg_rating,
                 (SELECT COUNT(*) FROM rating_driver WHERE id_driver = d.id_driver) as total_rating
                 FROM driver d 
                 JOIN users u ON d.id_user = u.id_user
                 WHERE d.status_verifikasi = 'verified' AND u.status = 'active'
                 ORDER BY d.created_at DESC LIMIT 6";
$result_driver = mysqli_query($conn, $query_driver);

// Ambil statistik
$query_stats = "SELECT 
                (SELECT COUNT(*) FROM driver WHERE status_verifikasi = 'verified') as total_driver,
                (SELECT COUNT(*) FROM pelanggan) as total_pelanggan,
                (SELECT COUNT(*) FROM rute_driver) as total_rute";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $query_stats));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SopirPilihan.id - Solusi Transportasi Pribadi Anti-Ribet</title>
    <meta name="description" content="Platform terpercaya untuk menemukan driver berpengalaman dan layanan pengiriman barang antar kota. Gratis tanpa biaya atau komisi.">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
</head>
<style>
    .hero {
        /* Gabungkan gradasi dan URL gambar dalam satu deklarasi */
        background: linear-gradient(135deg, rgba(30, 64, 175, 0.13), rgba(15, 23, 42, 0.7)), 
                    url('assets/images/BG6.jpg') no-repeat center center !important;
        
        /* Pastikan background-size juga dipaksa cover */
        background-size: cover !important;
        
        /* Memberikan tinggi minimal agar gambar terlihat proporsional */
        min-height: 70vh; 
        display: flex;
        align-items: center;
        justify-content: center;
    }

/* ================================================================
   NAVBAR KONSISTEN: PENYESUAIAN UKURAN PERSIS HALAMAN UTAMA
   ================================================================ */
    header {
    /* Background putih transparan */
    background: rgba(255, 255, 255, 0.48) !important; 
    
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


    /* Hero Section Styling */
    .hero {
        background: linear-gradient(135deg, rgba(30, 64, 175, 0.07), rgba(15, 23, 42, 0.8)), 
                    url('assets/images/BetterI.jpeg.') no-repeat center center !important;
        background-size: cover !important;
        min-height: 70vh; 
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .hero h1 {
        text-shadow: 2px 4px 15px rgba(0, 0, 0, 0.5);
    }

    /* MENAMPILKAN GARIS BIRU DI ATAS SETIAP CARD */
    .stat-card, .driver-card, .card {
        position: relative;
        overflow: hidden; /* Penting agar ujung garis mengikuti lengkungan card */
    }

    .stat-card::before, 
    .driver-card::before, 
    .card::before {
        content: "" !important; /* Memunculkan kembali elemen */
        display: block !important;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px; /* Ketebalan garis */
        background: linear-gradient(90deg, #2563eb, #1e40af) !important;
        z-index: 10;
    }


    /* Memastikan teks tetap terbaca jelas */
    .hero h1 {
        text-shadow: 2px 4px 15px rgba(0, 0, 0, 0.5);
    }
</style>

<body>
    <!-- Header & Navigation -->
    <header>
        <nav>
            <div class="logo">
                SopirPilihan.id
            </div>
            <ul>
                <li><a href="index.php">Beranda</a></li>
                <li><a href="pages/daftar_driver.php">Cari Driver</a></li>
                <li><a href="pages/pengiriman_barang.php">Kirim Barang</a></li>
                <li><a href="pages/tentang.php">Tentang</a></li>
                <?php if(isset($_SESSION['id_user']) && !empty($_SESSION['id_user'])): ?>
                    <li><a href="<?php echo $_SESSION['role']; ?>/dashboard.php">Dashboard</a></li>
                    <li><a href="auth/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="auth/login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <!-- Hero Section -->
<section class="hero">
    <div class="container-hero"> 
        <h1 class="fade-in">Solusi Transportasi Pribadi Anti-Ribet</h1>
        <p class="fade-in" style="animation-delay: 0.2s;">Temukan driver berpengalaman untuk perjalanan Anda atau kirim barang dengan aman ke seluruh Indonesia</p>
        <div class="cta-buttons fade-in" style="animation-delay: 0.4s;">
            <a href="pages/daftar_driver.php" class="btn btn-primary">Cari Driver Sekarang</a>
            <a href="pages/pengiriman_barang.php" class="btn btn-secondary"> Kirim Barang</a>
            <a href="auth/register_driver.php" class="btn btn-secondary"> Daftar Sebagai Driver</a>
        </div>
    </div>
</section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="stat-card fade-in">
            <h3><?php echo number_format($stats['total_driver']); ?>+</h3>
            <p>Driver Terverifikasi</p>
        </div>
        <div class="stat-card fade-in" style="animation-delay: 0.1s;">
            <h3><?php echo number_format($stats['total_pelanggan']); ?>+</h3>
            <p>Pengguna Terdaftar</p>
        </div>
        <div class="stat-card fade-in" style="animation-delay: 0.2s;">
            <h3><?php echo number_format($stats['total_rute']); ?>+</h3>
            <p>Rute Tersedia</p>
        </div>
    </section>

    <!-- Keunggulan Section -->
    <div class="container">
        <h2 class="section-title">Mengapa Memilih SopirPilihan.id?</h2>
        <div class="grid-3">
            <div class="card">
                <div style="padding: 2rem; text-align: center;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;"></div>
                    <h3 style="margin-bottom: 1rem; color: #1a1a1a; font-size: 1.3rem;">Driver Terverifikasi</h3>
                    <p style="color: #64748b; line-height: 1.7;">Semua driver telah melalui proses verifikasi dokumen dan identitas untuk keamanan Anda</p>
                </div>
            </div>
            <div class="card">
                <div style="padding: 2rem; text-align: center;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;"></div>
                    <h3 style="margin-bottom: 1rem; color: #1a1a1a; font-size: 1.3rem;">Gratis Tanpa Biaya</h3>
                    <p style="color: #64748b; line-height: 1.7;">Platform 100% gratis tanpa biaya pendaftaran atau komisi untuk driver maupun pelanggan</p>
                </div>
            </div>
            <div class="card">
                <div style="padding: 2rem; text-align: center;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;"></div>
                    <h3 style="margin-bottom: 1rem; color: #1a1a1a; font-size: 1.3rem;">Rating & Ulasan</h3>
                    <p style="color: #64748b; line-height: 1.7;">Sistem rating transparan membantu Anda memilih driver terbaik berdasarkan pengalaman pengguna lain</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Driver Terbaru Section -->
    <div class="container">
        <h2 class="section-title">Driver Terbaru & Terpercaya</h2>
        
        <?php if(mysqli_num_rows($result_driver) > 0): ?>
            <div class="driver-grid">
                <?php while($driver = mysqli_fetch_assoc($result_driver)): ?>
                <div class="driver-card">
                    <img src="<?php echo !empty($driver['foto_profil']) ? 'uploads/drivers/profile/'.$driver['foto_profil'] : 'assets/images/default-avatar.png'; ?>" 
                         alt="<?php echo htmlspecialchars($driver['nama_lengkap']); ?>" 
                         class="driver-photo"
                         onerror="this.src='assets/images/default-avatar.png'">
                    <div class="driver-info">
                        <div class="driver-name"><?php echo htmlspecialchars($driver['nama_lengkap']); ?></div>
                        <div class="driver-rating">
                            ⭐ 
                            <?php if($driver['avg_rating']): ?>
                                <?php echo number_format($driver['avg_rating'], 1); ?>
                                <span style="color: #64748b; font-weight: 500;">(<?php echo $driver['total_rating']; ?> ulasan)</span>
                            <?php else: ?>
                                <span style="color: #64748b; font-weight: 500;">Belum ada rating</span>
                            <?php endif; ?>
                        </div>
                        <p><?php echo !empty($driver['deskripsi']) ? htmlspecialchars(substr($driver['deskripsi'], 0, 100)) : 'Driver profesional dengan pengalaman terpercaya'; ?>...</p>
                        <div class="driver-contact">
                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $driver['no_whatsapp']); ?>?text=Halo,%20saya%20tertarik%20dengan%20jasa%20driver%20Anda%20dari%20SopirPilihan.id" 
                               target="_blank" 
                               class="btn btn-whatsapp"> WhatsApp</a>
                            <a href="pages/detail_driver.php?id=<?php echo $driver['id_driver']; ?>" 
                               class="btn btn-detail"> Detail</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            
            <div style="text-align: center; margin-top: 3rem;">
                <a href="pages/daftar_driver.php" class="btn btn-primary">Lihat Semua Driver →</a>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <span style="font-size: 1.5rem;">ℹ️</span>
                <div>
                    <strong>Belum ada driver tersedia</strong><br>
                    Kami sedang memverifikasi driver baru. Silakan cek kembali nanti atau daftar sebagai driver!
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Cara Kerja Section -->
    <div class="container">
        <h2 class="section-title">Cara Kerja Platform Kami</h2>
        <div class="grid-2">
            <!-- Untuk Pengguna -->
            <div class="section">
                <h3 style="color: #2563eb; margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 700;"> Untuk Pengguna</h3>
                <ol style="padding-left: 1.5rem; color: #475569; line-height: 2;">
                    <li style="margin-bottom: 1rem;"><strong>Cari Driver:</strong> Browse daftar driver berdasarkan rute atau kebutuhan Anda</li>
                    <li style="margin-bottom: 1rem;"><strong>Lihat Profil & Rating:</strong> Periksa pengalaman, rating, dan ulasan dari pelanggan lain</li>
                    <li style="margin-bottom: 1rem;"><strong>Hubungi Langsung:</strong> Kontak driver via WhatsApp untuk negosiasi harga dan jadwal</li>
                    <li style="margin-bottom: 1rem;"><strong>Beri Ulasan:</strong> Setelah perjalanan, berikan rating untuk membantu pengguna lain</li>
                </ol>
            </div>

            <!-- Untuk Driver -->
            <div class="section">
                <h3 style="color: #10b981; margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 700;"> Untuk Driver</h3>
                <ol style="padding-left: 1.5rem; color: #475569; line-height: 2;">
                    <li style="margin-bottom: 1rem;"><strong>Daftar Gratis:</strong> Buat akun dan lengkapi profil dengan dokumen yang diperlukan</li>
                    <li style="margin-bottom: 1rem;"><strong>Verifikasi:</strong> Tim kami akan memverifikasi data Anda dalam 1-2 hari kerja</li>
                    <li style="margin-bottom: 1rem;"><strong>Tambah Rute:</strong> Input rute-rute yang Anda layani dan jadwal keberangkatan</li>
                    <li style="margin-bottom: 1rem;"><strong>Terima Order:</strong> Pelanggan akan menghubungi Anda langsung via WhatsApp</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="container">
        <div class="section" style="background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); color: white; text-align: center; padding: 3rem 2rem;">
            <h2 style="font-size: 2rem; margin-bottom: 1rem; color: white;">Siap Memulai Perjalanan Anda?</h2>
            <p style="font-size: 1.1rem; margin-bottom: 2rem; opacity: 0.95;">Bergabunglah dengan ribuan pengguna yang telah mempercayai SopirPilihan.id</p>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="auth/register_driver.php" class="btn btn-primary">Daftar Sebagai Driver</a>
                <a href="auth/register_pelanggan.php" class="btn btn-secondary">Daftar Sebagai Pengguna</a>
            </div>
        </div>
    </div>

    <!-- Footer -->
<footer>
        <div style="max-width: 1200px; margin: 0 auto;">
            <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">SopirPilihan.id</h3>
            <p style="margin-bottom: 0.5rem;">Solusi Transportasi Pribadi Anti-Ribet</p>
            <p style="margin-bottom: 1.5rem; opacity: 0.8;">Platform gratis tanpa biaya atau komisi untuk menghubungkan driver dan pelanggan</p>
            
            <div style="display: flex; gap: 2rem; justify-content: center; margin-bottom: 1.5rem; flex-wrap: wrap;">
                <a href="pages/tentang.php">Tentang Kami</a>
                <a href="pages/syarat-ketentuan.php">Syarat & Ketentuan</a>
                <a href="pages/kebijakan-privasi.php">Kebijakan Privasi</a>
                <a href="pages/kontak.php">Kontak</a>
            </div>
            
            <p style="opacity: 0.8;">&copy; 2026 SopirPilihan.id. All rights reserved.</p>
        </div>
    </footer>
    <!-- Smooth Scroll Script -->
    <script>
        // Smooth scroll untuk anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if(target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Lazy loading untuk gambar
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.src;
                        imageObserver.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img').forEach(img => {
                imageObserver.observe(img);
            });
        }

        // Animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.driver-card, .card, .section').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>
</html>