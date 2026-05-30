<?php
session_start();
require_once '../config/database.php';

$notif = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pesan = mysqli_real_escape_string($conn, $_POST['pesan']);

    $query = "INSERT INTO kontak_pesan (nama, email, pesan) VALUES ('$nama', '$email', '$pesan')";
    
    if (mysqli_query($conn, $query)) {
        $notif = "<div style='padding: 1rem; background: #dcfce7; color: #166534; border-radius: 8px; margin-bottom: 1rem;'>Pesan berhasil terkirim!</div>";
    } else {
        $notif = "<div style='padding: 1rem; background: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 1rem;'>Gagal mengirim pesan.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak Kami - SopirPilihan.id</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
:root {
            --primary-blue: #2563eb;
            --dark-slate: #0c0909; /* Perbaikan sintaks */
            --text-gray: #ffffff;    /* Diubah ke putih untuk deskripsi */
        }
        /* ================================================================
           LATAR BELAKANG GAMBAR (BACKGROUND)
           ================================================================ */
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, rgba(23, 24, 24, 0.46), rgba(23, 24, 26, 0.45)), 
                        url('../assets/images/BG6.jpg') no-repeat center center fixed;
            background-size: cover;
            color: var(--dark-slate: #0b0c0dff;);
            line-height: 1.7;
        }

        .content-container h1 { 
            color: white !important; 
            font-size: 3.5rem; 
            font-weight: 800; 
            text-align: center; 
            margin-bottom: 0.5rem;
            text-shadow: 2px 4px 10px rgba(245, 239, 239, 0.3); /* Agar terbaca jelas */
        }

        /* NAVBAR KONSISTEN */
        header {
            background: rgba(255, 255, 255, 1) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            border-bottom: 1px solid rgba(18, 23, 173, 1) !important;
            box-shadow: 0 4px 30px rgba(251, 242, 242, 0.1) !important;
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

        .logo { color: var(--primary-blue) !important; font-weight: 800; font-size: 1.5rem; text-decoration: none; }
        nav ul { display: flex; gap: 20px; list-style: none; margin: 0; padding: 0; }
        nav a { color: var(--primary-blue) !important; font-weight: 500; text-decoration: none; }

        /* CONTACT LAYOUT */
        .content-container {
            max-width: 1000px;
            margin: 4rem auto;
            padding: 0 1.5rem;
        }

        .contact-grid { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 2rem; 
        }

        .contact-box {
            background: rgba(255, 255, 255, 0.98);
            padding: 3rem;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            position: relative;
            overflow: hidden;
            border: 1px solid white;
        }

        .contact-box::before {
            content: "";
            position: absolute;
            top: 0; left: 0; width: 100%; height: 6px;
            background: var(--primary-blue);
        }

        .info-card {
            background: linear-gradient(135deg, #2563eb, #1e40af);
            color: white;
            padding: 3rem;
            border-radius: 24px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        h1 { font-size: 2.8rem; font-weight: 800; text-align: center; margin-bottom: 0.5rem; }
        h3 { font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem; }

        label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--dark-slate); }
        input, textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-family: inherit;
            transition: 0.3s;
        }
        input:focus, textarea:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn-send {
            background: var(--primary-blue);
            color: white;
            padding: 1rem;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-send:hover { background: #1e40af; transform: translateY(-2px); }

        .info-item { display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem; }
        .info-item strong { display: block; color: white; }
        .info-item p { margin: 0; color: rgba(255,255,255,0.8); }

        /* FOOTER KONSISTEN */
        footer {
            background: #1e293b;
            color: white;
            padding: 4rem 2rem;
            text-align: center;
            margin-top: 5rem;
        }
        footer a { color: rgba(255,255,255,0.7); text-decoration: none; margin: 0 10px; }

        @media (max-width: 768px) {
            .contact-grid { grid-template-columns: 1fr; }
            .content-container { margin: 2rem auto; }
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
        <h1>Hubungi Kami</h1>
        <p style="text-align: center; color: var(--text-gray); margin-bottom: 3rem;">Punya pertanyaan atau butuh bantuan? Tim kami siap melayani Anda.</p>

        <div class="contact-grid">
            <div class="contact-box">
                <h3>Kirim Pesan</h3>
                <form action="#" method="POST">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" placeholder="Masukkan nama Anda" required>
                    
                    <label>Email</label>
                    <input type="email" name="email" placeholder="nama@email.com" required>
                    
                    <label>Pesan</label>
                    <textarea name="pesan" rows="5" placeholder="Tuliskan pertanyaan atau pesan Anda..." required></textarea>
                    
                    <button type="submit" class="btn-send">Kirim Pesan Sekarang</button>
                </form>
            </div>

            <div class="info-card">
                <h3>Informasi Kontak</h3>
                <p style="margin-bottom: 2.5rem; color: rgba(255,255,255,0.9);">Gunakan saluran di bawah ini untuk respon yang lebih cepat mengenai layanan kami.</p>
                
                <div class="info-item">
                    <span></span>
                    <div>
                        <strong>Alamat Kantor</strong>
                        <p>Jl. Perintis Kemerdekaan KM. 9. Tamalanrea, Makassar.</p>
                    </div>
                </div>

                <div class="info-item">
                    <span></span>
                    <div>
                        <strong>WhatsApp Business</strong>
                        <p>+62 89669144192</p>
                    </div>
                </div>

                <div class="info-item">
                    <span></span>
                    <div>
                        <strong>Email Support</strong>
                        <p>sopirpilihan@gmail.com</p>
                    </div>
                </div>

                <div class="info-item">
                    <span></span>
                    <div>
                        <strong>Jam Kerja</strong>
                        <p>Senin - Jumat | 09:00 - 17:00 WIB</p>
                    </div>
                </div>
            </div>
        </div>
    </div>


</body>
</html>