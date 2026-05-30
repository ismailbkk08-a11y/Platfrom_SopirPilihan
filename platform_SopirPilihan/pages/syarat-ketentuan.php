<?php
session_start();
require_once '../config/database.php';

if (!function_exists('is_logged_in')) {
    function is_logged_in() {
        return isset($_SESSION['id_user']) && !empty($_SESSION['id_user']);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Syarat & Ketentuan - SopirPilihan.id</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        :root {
            --primary-blue: #2563eb;
            --dark-slate: #1e293b;
            --text-gray: #475569;
        }

        /* ================================================================
           LATAR BELAKANG GAMBAR (BACKGROUND)
           ================================================================ */
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            /* Menggunakan overlay gradasi agar gambar tidak menutupi keterbacaan teks */
            background: linear-gradient(135deg, rgba(17, 19, 20, 0.32), rgba(17, 18, 19, 0.41)), 
                        url('../assets/images/BG5.jpg') no-repeat center center fixed;
            background-size: cover;
            color: var(--dark-slate);
            line-height: 1.7;
        }

        /* NAVBAR KONSISTEN (GLASSMORPHISM) */
        header {
            background: rgba(255, 255, 255, 0.72) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2) !important;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1) !important;
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 1rem 0;
        }

        nav {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        .logo { color: var(--primary-blue) !important; font-weight: 800; font-size: 1.5rem; }
        nav ul { display: flex; gap: 20px; list-style: none; margin: 0; padding: 0; }
        nav a { color: var(--primary-blue) !important; font-weight: 500; text-decoration: none; }

        /* CONTENT CARD */
        .content-container {
            max-width: 950px;
            margin: 4rem auto;
            padding: 0 1.5rem;
        }

        .legal-card {
            background: rgba(255, 255, 255, 0.98);
            padding: 4rem;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            position: relative;
            overflow: hidden;
            border: 1px solid white;
        }

        /* Aksen Garis Biru Atas */
        .legal-card::before {
            content: "";
            position: absolute;
            top: 0; left: 0; width: 100%; height: 6px;
            background: linear-gradient(90deg, #2563eb, #1e40af);
        }

        h1 { font-size: 2.8rem; font-weight: 800; color: var(--dark-slate); margin-bottom: 0.5rem; text-align: center; }
        h2 { font-size: 1.6rem; font-weight: 700; color: var(--dark-slate); margin-top: 3rem; border-left: 6px solid var(--primary-blue); padding-left: 15px; }
        h3 { font-size: 1.2rem; font-weight: 600; color: var(--primary-blue); margin-top: 1.5rem; }
        
        .highlight-box {
            background: #eff6ff;
            padding: 1.5rem;
            border-radius: 12px;
            border-left: 5px solid var(--primary-blue);
            margin: 2rem 0;
            color: #1e40af;
        }

        ul { padding-left: 1.5rem; }
        li { margin-bottom: 0.8rem; color: var(--text-gray); }
        p { color: var(--text-gray); margin-bottom: 1.2rem; }

        /* FOOTER KONSISTEN */
footer {
            background: rgba(15, 23, 42, 0.95);
            color: white;
            padding: 4rem 2rem;
            text-align: center;
            backdrop-filter: blur(10px);
        }

        footer a { color: #94a3b8; text-decoration: none; margin: 0 10px; }
        .btn-home {
            display: inline-block; background: var(--primary); color: white !important;
            padding: 1rem 2rem; border-radius: 10px; text-decoration: none; font-weight: 600;
            margin-top: 2rem;
        }

              .btn-home {
            display: inline-block; background: var(--primary-blue); color: white !important;
            padding: 0.8rem 2rem; border-radius: 12px; text-decoration: none; font-weight: 600;
            transition: 0.3s; margin: 10px;
        }
        .btn-outline {
            display: inline-block; background: transparent; color: var(--primary-blue) !important;
            padding: 0.8rem 2rem; border-radius: 12px; text-decoration: none; font-weight: 600;
            border: 2px solid var(--primary-blue); transition: 0.3s; margin: 10px;
        }
    </style>
</head>
<body>

    <header>
        <nav>
            <div class="logo">SopirPilihan.id</div>
            <ul>
                <li><a href="../index.php">Beranda</a></li>
                <li><a href="daftar_driver.php">Cari Driver</a></li>
                <li><a href="pengiriman_barang.php">Kirim Barang</a></li>
                <li><a href="tentang.php">Tentang</a></li>
                <?php if(is_logged_in()): ?>
                    <li><a href="../<?php echo $_SESSION['role']; ?>/dashboard.php">Dashboard</a></li>
                <?php else: ?>
                    <li><a href="../auth/login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <div class="content-container">
        <div class="legal-card">
            <h1>Syarat & Ketentuan</h1>
            <p style="text-align: center; color: #94a3b8; font-weight: 500; margin-bottom: 3rem;">Terakhir diperbarui: 28 Desember 2025</p>

            <p>Selamat datang di <strong>SopirPilihan.id</strong>. Dengan mengakses dan menggunakan platform ini, Anda menyetujui untuk terikat dengan syarat dan ketentuan berikut.</p>

            <div class="highlight-box">
                <strong>Penting:</strong> Platform SopirPilihan.id adalah platform penghubung (marketplace) antara driver dan penumpang. Kami TIDAK bertanggung jawab atas transaksi, keamanan perjalanan, atau perselisihan antara driver dan penumpang.
            </div>

            <h2>1. Definisi</h2>
            <ul>
                <li><strong>Platform:</strong> Website SopirPilihan.id dan semua fitur terkait</li>
                <li><strong>Driver:</strong> Penyedia jasa transportasi pribadi yang terdaftar di platform</li>
                <li><strong>Pelanggan:</strong> Pengguna yang mencari dan menggunakan jasa driver</li>
                <li><strong>Admin:</strong> Pengelola platform SopirPilihan.id</li>
                <li><strong>Layanan:</strong> Semua fitur dan fungsi yang disediakan oleh platform</li>
            </ul>

            <h2>2. Ketentuan Umum</h2>
            <h3>2.1. Gratis & Tanpa Komisi</h3>
            <ul>
                <li>Platform ini 100% GRATIS untuk semua pengguna</li>
                <li>Tidak ada biaya pendaftaran, biaya berlangganan, atau komisi transaksi</li>
                <li>Negosiasi harga dilakukan langsung antara driver dan pelanggan</li>
                <li>Pembayaran dilakukan langsung kepada driver (cash/transfer)</li>
            </ul>
            <h3>2.2. Fungsi Platform</h3>
            <p>SopirPilihan.id berfungsi sebagai media informasi dan promosi bagi driver, platform pencarian bagi pelanggan, serta sistem transparansi melalui rating.</p>

            <h2>3. Ketentuan untuk Driver</h2>
            <h3>3.1. Persyaratan Pendaftaran</h3>
            <ul>
                <li>Berusia minimal 21 tahun dan memiliki SIM yang masih berlaku</li>
                <li>Memiliki kendaraan dalam kondisi baik dan layak jalan</li>
                <li>Lulus verifikasi identitas yang dilakukan oleh admin</li>
            </ul>
            <h3>3.2. Kewajiban Driver</h3>
            <ul>
                <li>Memberikan pelayanan terbaik dan menjaga keamanan penumpang</li>
                <li>Bersikap profesional, sopan, dan transparan mengenai harga</li>
                <li>Memperbarui informasi profil jika ada perubahan data</li>
            </ul>
            <h3>3.3. Larangan untuk Driver</h3>
            <ul>
                <li>Memberikan informasi palsu atau menyalahgunakan identitas</li>
                <li>Mengemudi di bawah pengaruh obat-obatan atau alkohol</li>
                <li>Melakukan tindakan kriminal atau tidak pantas</li>
            </ul>

            <h2>4. Ketentuan untuk Pelanggan</h2>
            <h3>4.1. Hak Pelanggan</h3>
            <ul>
                <li>Memilih driver dan menegosiasikan harga secara langsung</li>
                <li>Memberikan rating dan review berdasarkan pengalaman nyata</li>
            </ul>
            <h3>4.2. Kewajiban Pelanggan</h3>
            <ul>
                <li>Memberikan informasi akurat saat memesan dan membayar sesuai kesepakatan</li>
                <li>Bersikap sopan dan menghormati driver selama perjalanan</li>
            </ul>
            <h3>4.3. Rating & Review</h3>
            <ul>
                <li>Review harus objektif, jujur, dan tidak mengandung unsur SARA</li>
                <li>Admin berhak menghapus review yang melanggar ketentuan atau bersifat spam</li>
            </ul>

            <h2>5. Transaksi & Pembayaran</h2>
            <div class="highlight-box">
                <strong> Catatan Penting:</strong> Platform ini TIDAK memfasilitasi pembayaran. Semua transaksi dilakukan langsung antara driver dan pelanggan (Cash, Transfer, atau E-Wallet).
            </div>

            <h2>6. Privasi & Keamanan Data</h2>
            <ul>
                <li>Data pribadi hanya digunakan untuk keperluan fungsional platform</li>
                <li>Kami berkomitmen tidak menjual data pengguna kepada pihak ketiga</li>
            </ul>

            <h2>7. Tanggung Jawab & Batasan</h2>
            <h3>7.1. Batasan Tanggung Jawab Platform</h3>
            <p>SopirPilihan.id TIDAK bertanggung jawab atas kecelakaan, kehilangan barang, keterlambatan, atau sengketa kualitas layanan antara pihak-pihak terkait.</p>
            <h3>7.2. Tanggung Jawab Pengguna</h3>
            <p>Setiap pengguna bertanggung jawab penuh atas tindakannya. Konflik diselesaikan secara kekeluargaan atau hukum yang berlaku antara driver dan pelanggan.</p>

            <h2>8. Verifikasi & Penangguhan Akun</h2>
            <ul>
                <li>Admin berhak menolak pendaftaran atau mencabut status verifikasi driver</li>
                <li>Akun dapat dihapus jika melanggar syarat ketentuan atau menerima banyak laporan negatif</li>
            </ul>

            <h2>9. Hak Kekayaan Intelektual</h2>
            <p>Semua konten, logo, dan desain adalah milik SopirPilihan.id dan dilindungi hak cipta.</p>

            <h2>10. Perubahan Syarat & Ketentuan</h2>
            <p>Kami berhak mengubah ketentuan ini sewaktu-waktu. Perubahan akan diumumkan melalui website.</p>

            <h2>11. Hukum yang Berlaku</h2>
            <p>Ketentuan ini diatur sesuai hukum Republik Indonesia. Sengketa diselesaikan melalui musyawarah atau pengadilan yang berwenang.</p>

            <h2>12. Kontak</h2>
            <p>Jika Anda memiliki pertanyaan, silakan hubungi kami:</p>
            <ul style="list-style: none; padding: 0;">
                <li><strong>Email:</strong> sopirpilihan@gmail.com</li>
                <li><strong>WhatsApp:</strong> +6289669144192</li>
                <li><strong>Alamat:</strong> Jl. Perintis Kemerdekaan KM. 9, Kota Makassar</li>
            </ul>

            <div class="highlight-box" style="margin-top: 3rem; text-align: center; border-left: none; border-top: 5px solid var(--primary-blue);">
                <strong>Dengan menggunakan platform SopirPilihan.id, Anda menyatakan telah membaca dan menyetujui seluruh ketentuan di atas.</strong>
            </div>
            
                <div style="text-align: center; margin-top: 2rem;">
                <a href="../index.php" class="btn-home">Kembali ke Beranda</a>
                <a href="kebijakan-privasi.php" class="btn-outline">Kebijakan Privasi</a>
            </div>
        </div>
    </div>

</body>
</html>