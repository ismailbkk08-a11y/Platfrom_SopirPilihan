<?php
session_start();
require_once '../config/database.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - SopirPilihan.id</title>
    <meta name="description" content="Pelajari lebih lanjut tentang SopirPilihan.id - Platform transportasi pribadi gratis tanpa komisi untuk menghubungkan driver dan penumpang.">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        /* Navbar Glassmorphism - SAMA SEPERTI HOMEPAGE */
        header {
            background: rgba(255, 255, 255, 0.48) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2) !important;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1) !important;
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 1rem 0;
            transition: all 0.3s ease;
        }

        nav a {
            color: #2563eb !important;
            font-weight: 500;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .logo {
            color: #2563eb !important;
            font-weight: 800;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        nav a:hover {
            color: rgba(37, 99, 235, 1) !important;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            padding: 0.5rem 1rem;
        }

        /* Hero dengan Background Image - DIKECILKAN */
        .hero {
            background: linear-gradient(135deg, rgba(16, 18, 23, 0.41), rgba(15, 23, 42, 0.7)), 
                        url('../assets/images/BG001.jpg') no-repeat center center !important;
            background-size: cover !important;
            min-height: 2vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            position: relative;
        }

        .hero h1 {
            text-shadow: 2px 4px 15px rgba(0, 0, 0, 0.5);
            font-size: 400pxrem;
            font-weight: 900;
            margin-bottom: 0.75rem;
        }

        .hero p {
            text-shadow: 1px 2px 10px rgba(0, 0, 0, 0.5);
            font-size: 1.1rem;
        }

        /* GARIS BIRU DI ATAS CARD - SAMA SEPERTI HOMEPAGE */
        .section, .card {
            position: relative;
            overflow: hidden;
        }

        .section::before, 
        .card::before {
            content: "" !important;
            display: block !important;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, #2563eb, #1e40af) !important;
            z-index: 10;
        }

        /* Perbaikan spesifik untuk bagian Siapa Kami agar lebih besar */
.siapa-kami-grid {
    display: grid; 
    grid-template-columns: 1fr 1fr; /* Diubah menjadi 1:1 agar gambar lebih besar */
    gap: 4rem; 
    align-items: center; /* Gambar di tengah secara vertikal */
}

.img-container {
    position: relative;
    width: 100%;
}

.img-featured {
    width: 100%;
    height: 500px; /* Memberikan tinggi minimal agar gambar terlihat gagah */
    border-radius: 24px;
    box-shadow: 25px 25px 50px rgba(0, 0, 0, 0.15);
    object-fit: cover;
    display: block;
}

@media (max-width: 1024px) {
    .siapa-kami-grid {
        grid-template-columns: 1fr; /* Stacked untuk tablet/mobile */
        gap: 2rem;
    }
    .img-featured {
        height: 350px;
    }
}

        /* Visi Misi Box tanpa garis atas */
        .visi-misi-box {
            position: relative;
        }

        .visi-misi-box::before {
            display: none !important;
        }

        /* Values Box tanpa garis atas */
        .values-box {
            position: relative;
        }

        .values-box::before {
            display: none !important;
        }

        /* Feature Box dengan border-left */
        .feature-box {
            position: relative;
        }

        .feature-box::before {
            display: none !important;
        }

        /* Responsive untuk section Siapa Kami */
        @media (max-width: 768px) {
            .siapa-kami-grid {
                grid-template-columns: 1fr !important;
            }
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
                <?php if(isset($_SESSION['id_user']) && !empty($_SESSION['id_user'])): ?>
                    <li><a href="../<?php echo $_SESSION['role']; ?>/dashboard.php">Dashboard</a></li>
                    <li><a href="../auth/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="../auth/login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <section class="hero">
        <div style="max-width: 800px; padding: 0 2rem;">
            <h1 class="fade-in">Tentang SopirPilihan.id</h1>
            <p style="font-size: 1.3rem;" class="fade-in">Solusi Transportasi Pribadi Anti-Ribet</p>
        </div>
    </section>

    <div class="container">
        <!-- Siapa Kami dengan Gambar -->
                    <div class="section fade-in" style="background: white; padding: 4rem 3rem; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.05); margin-bottom: 3rem;">
    <div class="siapa-kami-grid">
        <div>
            <h2 style="font-size: 3rem; font-weight: 800; margin-bottom: 0.5rem; color: #1e293b; letter-spacing: -1px;">Siapa Kami?</h2>
            <div style="width: 80px; height: 6px; background: #2563eb; margin-bottom: 2.5rem; border-radius: 3px;"></div>
            
            <p style="font-size: 1.2rem; color: #475569; line-height: 1.8; margin-bottom: 1.5rem; text-align: justify;">
                <strong style="color: #2563eb;">SopirPilihan.id</strong> lahir dari pengalaman nyata mahasiswa perantauan yang sering menggunakan jasa driver pribadi untuk pulang kampung. Kami memahami betul bagaimana sulitnya mencari driver terpercaya, serta betapa pentingnya faktor keamanan dan kenyamanan dalam setiap perjalanan.
            </p>
            
            <p style="font-size: 1.2rem; color: #475569; line-height: 1.8; margin-bottom: 2rem; text-align: justify;">
                Sebagai bentuk kepedulian, kami mendengar keluh kesah para driver yang sulit mendapatkan akses pemasaran. Banyak platform yang memotong pendapatan hingga <strong style="color: #ef4444;">20-25%</strong>. Kami merasa ini tidak adil bagi mereka yang menghidupi keluarga dari profesi ini.
            </p>

            <div style="background: #f0f7ff; border-left: 6px solid #2563eb; padding: 2rem; border-radius: 0 20px 20px 0; box-shadow: 5px 5px 15px rgba(37, 99, 235, 0.05);">
                <p style="font-size: 1.15rem; color: #1e40af; line-height: 1.7; font-style: italic; font-weight: 500;">
                    "Maka terciptalah SopirPilihan.id — Platform <span style="font-weight: 800; border-bottom: 2px solid #2563eb;">100% gratis tanpa komisi</span>, di mana driver mendapat seluruh hasil jerih payahnya secara utuh."
                </p>
            </div>
        </div>

        <div class="img-container">
            <img src="../assets/images/A (1).png" 
                 alt="Tentang SopirPilihan.id" 
                 class="img-featured"
                 onerror="this.src='https://images.unsplash.com/photo-1449965072335-64441ddc223f?q=80&w=1000&auto=format&fit=crop'">
            
            <div style="position: absolute; bottom: -20px; left: -20px; width: 150px; height: 150px; background: rgba(37, 99, 235, 0.05); border-radius: 30px; z-index: -1;"></div>
            <div style="position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; background: rgba(37, 99, 235, 0.1); border-radius: 20px; z-index: -1;"></div>
        </div>
    </div>
</div>


        <!-- Keunggulan -->
        <div class="section fade-in">
            <h2 class="section-title"> Keunggulan Kami</h2>
            <div class="grid-3">
                <div class="card">
                    <div style="padding: 2rem; text-align: center;">
                        <div style="font-size: 3.5rem; margin-bottom: 1rem;"></div>
                        <h3 style="margin-bottom: 1rem; color: #1a1a1a; font-size: 1.3rem; font-weight: 700;">100% Gratis</h3>
                        <p style="color: #64748b; line-height: 1.7;">Tidak ada biaya pendaftaran, tidak ada komisi. Platform kami sepenuhnya gratis untuk driver dan penumpang.</p>
                    </div>
                </div>
                
                <div class="card">
                    <div style="padding: 2rem; text-align: center;">
                        <div style="font-size: 3.5rem; margin-bottom: 1rem;"></div>
                        <h3 style="margin-bottom: 1rem; color: #1a1a1a; font-size: 1.3rem; font-weight: 700;">Driver Terverifikasi</h3>
                        <p style="color: #64748b; line-height: 1.7;">Semua driver melalui proses verifikasi ketat oleh tim kami untuk menjamin keamanan perjalanan Anda.</p>
                    </div>
                </div>
                
                <div class="card">
                    <div style="padding: 2rem; text-align: center;">
                        <div style="font-size: 3.5rem; margin-bottom: 1rem;"></div>
                        <h3 style="margin-bottom: 1rem; color: #1a1a1a; font-size: 1.3rem; font-weight: 700;">Rating & Ulasan</h3>
                        <p style="color: #64748b; line-height: 1.7;">Sistem rating transparan membantu Anda memilih driver terbaik berdasarkan pengalaman pengguna lain.</p>
                    </div>
                </div>
                
                <div class="card">
                    <div style="padding: 2rem; text-align: center;">
                        <div style="font-size: 3.5rem; margin-bottom: 1rem;"></div>
                        <h3 style="margin-bottom: 1rem; color: #1a1a1a; font-size: 1.3rem; font-weight: 700;">Kontak Langsung</h3>
                        <p style="color: #64748b; line-height: 1.7;">Hubungi driver langsung via WhatsApp tanpa perantara untuk negosiasi harga dan detail perjalanan.</p>
                    </div>
                </div>
                
                <div class="card">
                    <div style="padding: 2rem; text-align: center;">
                        <div style="font-size: 3.5rem; margin-bottom: 1rem;"></div>
                        <h3 style="margin-bottom: 1rem; color: #1a1a1a; font-size: 1.3rem; font-weight: 700;">Pengiriman Barang</h3>
                        <p style="color: #64748b; line-height: 1.7;">Tidak hanya penumpang, kami juga melayani pengiriman barang antar kota dengan aman.</p>
                    </div>
                </div>
                
                <div class="card">
                    <div style="padding: 2rem; text-align: center;">
                        <div style="font-size: 3.5rem; margin-bottom: 1rem;"></div>
                        <h3 style="margin-bottom: 1rem; color: #1a1a1a; font-size: 1.3rem; font-weight: 700;">Beragam Pilihan</h3>
                        <p style="color: #64748b; line-height: 1.7;">Dari sedan, MPV, hingga minibus - temukan kendaraan yang sesuai dengan kebutuhan Anda.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Visi Misi -->
        <div class="section fade-in">
            <h2 class="section-title">Visi & Misi</h2>
            <div class="grid-2">
                <div class="visi-misi-box" style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); padding: 2.5rem; border-radius: 16px; border-left: 5px solid #2563eb;">
                    <h3 style="color: #1e40af; margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 700;"> Visi</h3>
                    <p style="color: #1e40af; line-height: 1.8; font-size: 1.05rem;">
                        Menjadi platform transportasi pribadi terpercaya dan terbesar di Indonesia yang 
                        mengedepankan transparansi dan keadilan bagi semua pihak.
                    </p>
                </div>
                <div class="visi-misi-box" style="background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); padding: 2.5rem; border-radius: 16px; border-left: 5px solid #10b981;">
                    <h3 style="color: #065f46; margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 700;"> Misi</h3>
                    <ul style="color: #065f46; line-height: 2; list-style-position: inside; font-size: 1.05rem;">
                        <li>Menyediakan platform gratis yang menghubungkan driver dan penumpang</li>
                        <li>Meningkatkan pendapatan driver tanpa potongan komisi</li>
                        <li>Memberikan pilihan transportasi yang aman dan terpercaya</li>
                        <li>Membangun komunitas driver dan penumpang yang saling percaya</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Nilai-Nilai -->


        <!-- Mengapa Memilih Kami -->
        <div class="section fade-in">
            <h2 class="section-title">Mengapa Memilih Kami?</h2>
            <p style="font-size: 1.1rem; color: #475569; line-height: 1.8; margin-bottom: 2rem;">
                Di era digital ini, banyak platform transportasi memungut komisi hingga 20-25% dari setiap transaksi. 
                Kami percaya ini tidak adil. <strong style="color: #2563eb;">SopirPilihan.id</strong> hadir dengan pendekatan berbeda:
            </p>
            <div class="grid-2">
                <div class="feature-box" style="display: flex; align-items: start; gap: 1.5rem; padding: 2rem; background: #f8fafc; border-radius: 12px; border-left: 5px solid #2563eb;">
                    <div style="font-size: 2.5rem;"></div>
                    <div>
                        <h4 style="margin-bottom: 0.75rem; color: #1a1a1a; font-size: 1.2rem;">0% Komisi</h4>
                        <p style="color: #64748b; line-height: 1.7;">Driver mendapat 100% pembayaran tanpa potongan sepeserpun</p>
                    </div>
                </div>
                <div class="feature-box" style="display: flex; align-items: start; gap: 1.5rem; padding: 2rem; background: #f8fafc; border-radius: 12px; border-left: 5px solid #10b981;">
                    <div style="font-size: 2.5rem;"></div>
                    <div>
                        <h4 style="margin-bottom: 0.75rem; color: #1a1a1a; font-size: 1.2rem;">Negosiasi Bebas</h4>
                        <p style="color: #64748b; line-height: 1.7;">Harga disepakati langsung antara driver dan penumpang</p>
                    </div>
                </div>
                <div class="feature-box" style="display: flex; align-items: start; gap: 1.5rem; padding: 2rem; background: #f8fafc; border-radius: 12px; border-left: 5px solid #f59e0b;">
                    <div style="font-size: 2.5rem;"></div>
                    <div>
                        <h4 style="margin-bottom: 0.75rem; color: #1a1a1a; font-size: 1.2rem;">Pembayaran Fleksibel</h4>
                        <p style="color: #64748b; line-height: 1.7;">Cash atau transfer langsung ke driver, bebas pilih</p>
                    </div>
                </div>
                <div class="feature-box" style="display: flex; align-items: start; gap: 1.5rem; padding: 2rem; background: #f8fafc; border-radius: 12px; border-left: 5px solid #8b5cf6;">
                    <div style="font-size: 2.5rem;"></div>
                    <div>
                        <h4 style="margin-bottom: 0.75rem; color: #1a1a1a; font-size: 1.2rem;">Data Terlindungi</h4>
                        <p style="color: #64748b; line-height: 1.7;">Privasi dan keamanan data Anda adalah prioritas utama kami</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA -->
        <div class="section fade-in" style="background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); color: white; text-align: center; padding: 4rem 2rem; border-radius: 16px;">
            <h2 style="font-size: 2.2rem; margin-bottom: 1rem; color: white; font-weight: 800;">Siap Memulai Perjalanan Anda?</h2>
            <p style="font-size: 1.2rem; margin-bottom: 2.5rem; opacity: 0.95; max-width: 600px; margin-left: auto; margin-right: auto;">Bergabunglah dengan ribuan pengguna yang sudah merasakan kemudahan SopirPilihan.id</p>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="../auth/register_driver.php" class="btn btn-primary" style="background: white; color: #2563eb;"> Daftar Sebagai Driver</a>
                <a href="../auth/register_pelanggan.php" class="btn btn-secondary"> Daftar Sebagai Pengguna</a>
            </div>
        </div>

        <!-- Hubungi Kami -->
        <div class="section fade-in">
            <h2 class="section-title"> Hubungi Kami</h2>
            <div class="grid-2">
                <div>
                    <p style="font-size: 1.15rem; color: #475569; line-height: 1.8; margin-bottom: 2rem; font-weight: 500;">
                        Ada pertanyaan atau saran? Kami siap membantu Anda!
                    </p>
                    <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                        <div style="display: flex; align-items: center; gap: 1.25rem; padding: 1.5rem; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-radius: 12px; border-left: 4px solid #2563eb;">
                            <span style="font-size: 2rem;"></span>
                            <div>
                                <strong style="color: #1a1a1a; font-size: 1.05rem; display: block; margin-bottom: 0.25rem;">Email</strong>
                                <a href="mailto:info@sopirpilihan.id" style="color: #2563eb; text-decoration: none; font-weight: 500;">sopirpilihan@gmail.com</a>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 1.25rem; padding: 1.5rem; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-radius: 12px; border-left: 4px solid #25d366;">
                            <span style="font-size: 2rem;"></span>
                            <div>
                                <strong style="color: #1a1a1a; font-size: 1.05rem; display: block; margin-bottom: 0.25rem;">WhatsApp</strong>
                                <a href="https://wa.me/6289669144192" style="color: #25d366; text-decoration: none; font-weight: 500;">+62 896-6914-4192</a>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 1.25rem; padding: 1.5rem; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-radius: 12px; border-left: 4px solid #f59e0b;">
                            <span style="font-size: 2rem;"></span>
                            <div>
                                <strong style="color: #1a1a1a; font-size: 1.05rem; display: block; margin-bottom: 0.25rem;">Jam Operasional</strong>
                                <span style="color: #64748b; font-weight: 500;">Senin - Jumat, 09:00 - 17:00 WIB</span>
                            </div>
                        </div>
                    </div>
                </div>
                    <div style="background: linear-gradient(135deg, rgba(37, 100, 235, 0.2) 0%, rgba(30, 64, 175, 0.03) 100%), url('../assets/images/BG002.jpg') no-repeat center center; background-size: cover; padding: 3rem 2rem; border-radius: 16px; display: flex; align-items: center; justify-content: center; color: white; position: relative; overflow: hidden;">
                        <div style="text-align: center; position: relative; z-index: 2;">
                            <div style="font-size: 6rem; margin-bottom: 1.5rem;"></div>
                            <h3 style="font-size: 1.8rem; margin-bottom: 0.75rem; font-weight: 800; text-shadow: 2px 2px 10px rgba(0,0,0,0.3);"></h3>
                            <p style="font-size: 1.1rem; opacity: 0.95; text-shadow: 1px 1px 5px rgba(0,0,0,0.3);"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<footer style="background: #1e293b; color: white; padding: 4rem 2rem; text-align: center; margin-top: 5rem;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <h3 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 1rem; color: white;">SopirPilihan.id</h3>
        <p style="margin-bottom: 0.5rem; font-weight: 500;">Solusi Transportasi Pribadi Anti-Ribet</p>
        <p style="margin-bottom: 2rem; opacity: 0.7; max-width: 600px; margin-left: auto; margin-right: auto;">
            Platform gratis tanpa biaya atau komisi untuk menghubungkan driver dan pelanggan dengan aman dan nyaman.
        </p>
        
        <div style="display: flex; gap: 2rem; justify-content: center; margin-bottom: 2.5rem; flex-wrap: wrap;">
            <a href="tentang.php" style="color: rgba(241, 83%, 49%, 0.98); text-decoration: none; transition: 0.3s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.8)'">Tentang Kami</a>
            <a href="syarat-ketentuan.php" style="color: rgba(241, 83%, 49%, 0.98); text-decoration: none; transition: 0.3s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.8)'">Syarat & Ketentuan</a>
            <a href="kebijakan-privasi.php" style="color: rgba(241, 83%, 49%, 0.98); text-decoration: none; transition: 0.3s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.8)'">Kebijakan Privasi</a>
            <a href="kontak.php" style="color: rgba(241, 83%, 49%, 0.98); text-decoration: none; transition: 0.3s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.8)'">Kontak</a>
        </div>
        
        <div style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 2rem;">
            <p style="opacity: 0.5; font-size: 0.9rem;">&copy; 2026 SopirPilihan.id. Semua hak cipta dilindungi undang-undang.</p>
        </div>
    </div>
</footer>

    <script>
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

        document.querySelectorAll('.section, .card').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>
</html>