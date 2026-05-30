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
    <title>Kebijakan Privasi - SopirPilihan.id</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        :root {
            --primary-blue: #2563eb;
            --dark-slate: #1e293b;
            --text-gray: #475569;
            --light-blue: #eff6ff;
        }

        /* ================================================================
           LATAR BELAKANG GAMBAR (BACKGROUND)
           ================================================================ */
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, rgba(19, 23, 28, 0.69), rgba(13, 18, 25, 0.64)), 
                        url('../assets/images/BG12.jpeg') no-repeat center center fixed;
            background-size: cover;
            color: var(--dark-slate);
            line-height: 1.7;
        }

        /* NAVBAR KONSISTEN */
        header {
            background: rgba(255, 255, 255, 0.82) !important;
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

        .logo { color: var(--primary-blue) !important; font-weight: 800; font-size: 1.5rem; text-decoration: none;}
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

        .legal-card::before {
            content: "";
            position: absolute;
            top: 0; left: 0; width: 100%; height: 6px;
            background: linear-gradient(90deg, #10b981, #2563eb); /* Hijau ke Biru untuk Privasi */
        }

        h1 { font-size: 2.8rem; font-weight: 800; color: var(--dark-slate); margin-bottom: 0.5rem; text-align: center; }
        h2 { font-size: 1.6rem; font-weight: 700; color: var(--dark-slate); margin-top: 3rem; border-left: 6px solid var(--primary-blue); padding-left: 15px; }
        h3 { font-size: 1.2rem; font-weight: 600; color: var(--primary-blue); margin-top: 1.5rem; }
        
        .highlight-box {
            background: var(--light-blue);
            padding: 1.5rem;
            border-radius: 12px;
            border-left: 5px solid var(--primary-blue);
            margin: 2rem 0;
            color: #1e40af;
        }

        /* TABLE STYLING */
        .data-table {
            width: 100%;
            margin: 1.5rem 0;
            border-collapse: collapse;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        
        .data-table th {
            background: var(--primary-blue);
            color: white;
            padding: 1rem;
            text-align: left;
        }
        
        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            background: white;
            font-size: 0.95rem;
        }

        ul { padding-left: 1.5rem; }
        li { margin-bottom: 0.8rem; color: var(--text-gray); }
        p { color: var(--text-gray); margin-bottom: 1.2rem; }

        /* FOOTER KONSISTEN */
        footer {
            background: #1e293b;
            color: white;
            padding: 4rem 2rem;
            text-align: center;
            margin-top: 5rem;
        }

        footer a { color: rgba(255,255,255,0.7); text-decoration: none; margin: 0 10px; transition: 0.3s; }
        footer a:hover { color: white; }

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
            <a href="../index.php" class="logo">SopirPilihan.id</a>
            <ul>
                <li><a href="../index.php">Beranda</a></li>
                <li><a href="daftar_driver.php">Cari Driver</a></li>
                <li><a href="tentang.php">Tentang</a></li>
                <li><a href="kontak.php">Kontak</a></li>
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
            <h1>Kebijakan Privasi</h1>
            <p style="text-align: center; color: #94a3b8; font-weight: 500; margin-bottom: 3rem;">Terakhir diperbarui: 28 Desember 2024</p>

            <p><strong>SopirPilihan.id</strong> berkomitmen untuk melindungi privasi Anda. Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, menyimpan, dan melindungi informasi pribadi Anda.</p>

            <div class="highlight-box">
                <strong> Komitmen Kami:</strong> Kami TIDAK akan menjual, menyewakan, atau membagikan data pribadi Anda kepada pihak ketiga untuk tujuan komersial tanpa persetujuan Anda.
            </div>

            <h2>1. Informasi yang Kami Kumpulkan</h2>
            <h3>1.1. Informasi yang Anda Berikan Langsung</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Jenis Data</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td><strong>Data Akun</strong></td><td>Username, password (terenkripsi), email, nomor telepon</td></tr>
                    <tr><td><strong>Data Profil Driver</strong></td><td>Nama lengkap, alamat, foto profil, nomor SIM, informasi kendaraan, rute layanan</td></tr>
                    <tr><td><strong>Data Profil Pelanggan</strong></td><td>Nama lengkap, alamat, nomor telepon</td></tr>
                    <tr><td><strong>Data Pengiriman</strong></td><td>Nama pengirim/penerima, alamat jemput/tujuan, detail barang</td></tr>
                    <tr><td><strong>Rating & Review</strong></td><td>Rating numerik, komentar, tanggal</td></tr>
                </tbody>
            </table>

            <h3>1.2. Informasi yang Dikumpulkan Otomatis</h3>
            <ul>
                <li><strong>Log Aktivitas:</strong> Halaman yang dikunjungi, waktu akses, durasi sesi.</li>
                <li><strong>Informasi Perangkat:</strong> Jenis browser, sistem operasi, alamat IP.</li>
                <li><strong>Data Teknis:</strong> Cookies untuk mempertahankan status login Anda.</li>
            </ul>

            <h2>2. Cara Kami Menggunakan Informasi Anda</h2>
            <h3>2.1. Tujuan Penggunaan Data</h3>
            <ul>
                <li> Menyediakan dan mengelola layanan platform</li>
                <li> Verifikasi identitas driver agar aman bagi pelanggan</li>
                <li> Memfasilitasi koneksi WhatsApp antara driver dan pelanggan</li>
                <li> Menampilkan profil driver dan mengelola sistem rating</li>
                <li> Mencegah penyalahgunaan platform dan mematuhi hukum</li>
            </ul>

            <h2>3. Pembagian Informasi</h2>
            <h3>3.1. Informasi yang Dibagikan Secara Publik</h3>
            <p>Data berikut <strong>AKAN DITAMPILKAN</strong> publik untuk keperluan layanan:</p>
            <ul>
                <li>Nama lengkap, foto profil, dan nomor WhatsApp driver.</li>
                <li>Informasi kendaraan, rute layanan, dan estimasi harga.</li>
                <li>Rating dan review dari pelanggan.</li>
            </ul>

            <div class="highlight-box">
                <strong> Catatan Driver:</strong> Dengan mendaftar, Anda menyetujui bahwa profil Anda dapat dilihat secara publik agar pelanggan dapat menghubungi Anda.
            </div>

            <h2>4. Penyimpanan & Keamanan Data</h2>
            <ul>
                <li> <strong>Enkripsi:</strong> Password dienkripsi menggunakan algoritma hash modern.</li>
                <li> <strong>Akses Terbatas:</strong> Hanya admin berwenang yang dapat mengakses database.</li>
                <li> <strong>HTTPS:</strong> Semua transmisi data dilindungi koneksi aman.</li>
            </ul>

            <h2>5. Hak Anda atas Data Pribadi</h2>
            <p>Anda berhak untuk mengakses, memperbarui informasi profil, atau meminta penghapusan akun Anda kapan saja melalui dashboard atau menghubungi kami.</p>

            <h2>6. Hubungi Kami</h2>
            <p>Jika Anda memiliki pertanyaan tentang kebijakan privasi ini:</p>
            <ul style="list-style: none; padding: 0;">
                <li><strong> Email:</strong> sopirpilihan@gmail.com</li>
                <li><strong> WhatsApp:</strong> +62 89669144192</li>
                <li><strong> Alamat:</strong> Jl. Perintis Kemerdekaan KM. 9. Tamalanrea, Makassar.</li>
            </ul>

            <h2>7. Dasar Hukum</h2>
            <p>Kebijakan ini disusun sesuai dengan <strong>UU No. 27 Tahun 2022 (PDP)</strong> dan <strong>UU ITE</strong> yang berlaku di Republik Indonesia.</p>

            <div class="highlight-box" style="margin-top: 3rem; text-align: center; border-left: none; border-top: 5px solid var(--primary-blue);">
                <strong> Persetujuan:</strong> Dengan menggunakan platform ini, Anda menyatakan telah menyetujui Kebijakan Privasi ini secara penuh.
            </div>

            <div style="text-align: center; margin-top: 2rem;">
                <a href="../index.php" class="btn-home">Kembali ke Beranda</a>
                <a href="syarat-ketentuan.php" class="btn-outline">Syarat & Ketentuan</a>
            </div>
        </div>
    </div>


</body>
</html>