<?php
session_start();
require_once '../config/database.php';

// Fungsi keamanan input
if (!function_exists('clean_input')) {
    function clean_input($data) {
        global $conn;
        $data = $data ?? '';
        return mysqli_real_escape_string($conn, htmlspecialchars(strip_tags(trim($data))));
    }
}

$success = '';
$error = '';

// Proses Simpan Data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nama_pengirim'])) {
    
    $nama_pengirim    = clean_input($_POST['nama_pengirim'] ?? '');
    $no_hp_pengirim   = clean_input($_POST['no_hp_pengirim'] ?? '');
    $email_pengirim    = clean_input($_POST['email_pengirim'] ?? '');
    $alamat_pengirim   = clean_input($_POST['alamat_pengirim'] ?? '');
    
    $nama_penerima     = clean_input($_POST['nama_penerima'] ?? '');
    $no_hp_penerima    = clean_input($_POST['no_hp_penerima'] ?? '');
    $alamat_penerima   = clean_input($_POST['alamat_penerima'] ?? '');
    
    $deskripsi_barang  = clean_input($_POST['deskripsi_barang'] ?? '');
    $jenis_barang      = clean_input($_POST['jenis_barang'] ?? '');
    $berat             = floatval($_POST['berat'] ?? 0);
    $catatan_barang    = clean_input($_POST['catatan_barang'] ?? '');
    
    $lokasi_penjemputan = clean_input($_POST['lokasi_penjemputan'] ?? '');
    $rute_tujuan       = clean_input($_POST['rute_tujuan'] ?? '');
    $tanggal_pengantaran = $_POST['tanggal_pengantaran'] ?? '';
    $id_driver         = intval($_POST['id_driver'] ?? 0);
    $total_harga       = floatval($_POST['total_harga_input'] ?? 0);
    $catatan_travel    = clean_input($_POST['catatan_travel'] ?? '');

    mysqli_begin_transaction($conn);
    try {
        $query = "INSERT INTO pengiriman_barang (
            nama_pengirim, no_hp_pengirim, email_pengirim, alamat_pengirim,
            nama_penerima, no_hp_penerima, alamat_penerima,
            deskripsi_barang, jenis_barang, berat, catatan_barang,
            lokasi_penjemputan, rute_tujuan, tanggal_pengantaran, 
            id_driver, total_harga, catatan_travel, status
        ) VALUES (
            '$nama_pengirim', '$no_hp_pengirim', '$email_pengirim', '$alamat_pengirim',
            '$nama_penerima', '$no_hp_penerima', '$alamat_penerima',
            '$deskripsi_barang', '$jenis_barang', '$berat', '$catatan_barang',
            '$lokasi_penjemputan', '$rute_tujuan', '$tanggal_pengantaran',
            $id_driver, $total_harga, '$catatan_travel', 'pending'
        )";
        
        if(mysqli_query($conn, $query)) {
            mysqli_commit($conn);
            $success = " Data pengiriman berhasil dikirim! Driver akan segera menghubungi Anda.";
        } else {
            throw new Exception(mysqli_error($conn));
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error = " Gagal menyimpan: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kirim Barang - SopirPilihan.id</title>
    <meta name="description" content="Layanan pengiriman barang antar kota dengan driver terpercaya. Gratis tanpa biaya atau komisi.">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
    body {
        /* Menggunakan gambar BG1.jpg dengan overlay putih transparan agar form tetap terbaca jelas */
        background: linear-gradient(rgba(47, 51, 54, 0), rgba(20, 25, 31, 0)), 
                    url('../assets/images/A.png') no-repeat center center fixed;
        background-size: cover !important;
        min-height: 100vh;
    }

        header {
    /* Background putih transparan */
    background: rgba(255, 255, 255, 0.91) !important; 
    
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

    /* Membuat box form terlihat seperti kaca (Glassmorphism) */
    .section {
        background: rgba(255, 255, 255, 0.85) !important;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.87);
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        border-radius: 15px;
        padding: 2.5rem;
    }

    /* Mempercantik tampilan header di atas background */
    .section h1 {
        color: #1e40af;
        text-shadow: 1px 1px 2px rgba(255,255,255,0.8);
    }

    /* Form control transparansi */
    input, select, textarea {
        background: rgba(255, 255, 255, 0.9) !important;
    }
</style>
</head>
<body>
    <header>
        <nav>
            <div class="logo"> SopirPilihan.id</div>
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
        <?php if($success): ?>
        <div class="alert alert-success">
            <span style="font-size: 1.5rem;"></span>
            <div>
                <strong>Berhasil!</strong><br>
                <?php echo $success; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if($error): ?>
        <div class="alert alert-danger">
            <span style="font-size: 1.5rem;"></span>
            <div>
                <strong>Gagal!</strong><br>
                <?php echo $error; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="section" style="text-align: center; margin-bottom: 2rem;">
            <h1 style="font-size: 2.5rem; margin-bottom: 1rem; color: #1a1a1a;"> Kirim Barang Antar Kota</h1>
            <p style="font-size: 1.1rem; color: #64748b;">Kirim barang Anda dengan aman bersama driver terpercaya</p>
        </div>

        <div class="section">
            <form method="POST">
                <!-- Data Pengirim & Penerima -->
                <div style="margin-bottom: 2.5rem; padding-bottom: 2rem; border-bottom: 2px solid #f1f5f9;">
                    <h2 style="font-size: 1.5rem; color: #2563eb; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                        <span></span> Data Pengirim & Penerima
                    </h2>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Nama Pengirim <span class="required">*</span></label>
                            <input type="text" name="nama_pengirim" required placeholder="Masukkan nama lengkap">
                        </div>
                        <div class="form-group">
                            <label>Nama Penerima <span class="required">*</span></label>
                            <input type="text" name="nama_penerima" required placeholder="Masukkan nama penerima">
                        </div>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>WhatsApp Pengirim <span class="required">*</span></label>
                            <input type="text" name="no_hp_pengirim" placeholder="08..." required>
                        </div>
                        <div class="form-group">
                            <label>WhatsApp Penerima <span class="required">*</span></label>
                            <input type="text" name="no_hp_penerima" placeholder="08..." required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Email Pengirim <span class="required">*</span></label>
                        <input type="email" name="email_pengirim" required placeholder="contoh@email.com">
                    </div>
                    
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Alamat Lengkap Pengirim (Jemput) <span class="required">*</span></label>
                            <textarea name="alamat_pengirim" rows="3" placeholder="Jl. Contoh No. 1, Kecamatan, Kota" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Alamat Lengkap Penerima (Tujuan) <span class="required">*</span></label>
                            <textarea name="alamat_penerima" rows="3" placeholder="Jl. Tujuan No. 2, Kecamatan, Kota" required></textarea>
                        </div>
                    </div>
                </div>

                <!-- Detail Barang -->
                <div style="margin-bottom: 2.5rem; padding-bottom: 2rem; border-bottom: 2px solid #f1f5f9;">
                    <h2 style="font-size: 1.5rem; color: #2563eb; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                        <span></span> Detail Barang
                    </h2>
                    <div class="form-group">
                        <label>Deskripsi Barang <span class="required">*</span></label>
                        <input type="text" name="deskripsi_barang" placeholder="Contoh: 2 Kardus Pakaian" required>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Jenis Barang <span class="required">*</span></label>
                            <select name="jenis_barang" required>
                                <option value="">-- Pilih Jenis --</option>
                                <option value="Pakaian">Pakaian</option>
                                <option value="Dokumen">Dokumen</option>
                                <option value="Elektronik">Elektronik</option>
                                <option value="Makanan">Makanan</option>
                                <option value="Furniture">Furniture</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Berat Total (Kg) <span class="required">*</span></label>
                            <input type="number" name="berat" id="berat" step="0.1" min="1" required placeholder="Contoh: 5">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Catatan Barang (Opsional)</label>
                        <textarea name="catatan_barang" rows="2" placeholder="Barang pecah belah, dsb..."></textarea>
                    </div>
                </div>

                <!-- Lokasi & Driver -->
                <div style="margin-bottom: 2.5rem;">
                    <h2 style="font-size: 1.5rem; color: #2563eb; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                        <span></span> Lokasi & Pilih Driver
                    </h2>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Kota Asal <span class="required">*</span></label>
                            <select name="lokasi_penjemputan" id="kota_asal" required>
                                <option value="">-- Pilih Kota Asal --</option>
                                <option value="Makassar">Makassar</option>
                                <option value="Bone">Bone</option>
                                <option value="Gowa">Gowa</option>
                                <option value="Malino">Malino</option>
                                <option value="Soppeng">Soppeng</option>
                                <option value="Parepare">Parepare</option>
                                <option value="Tana Toraja">Tana Toraja</option>
                                <option value="Bulukumba">Bulukumba</option>
                                <option value="Pangkep">Pangkep</option>
                                <option value="Barru">Barru</option>
                                <option value="Sidrap">Sidrap</option>
                                <option value="Maros">Maros</option>
                                <option value="Takalar">Takalar</option>
                                <option value="Jeneponto">Jeneponto</option>
                                <option value="Luwu">Luwu</option>
                                <option value="Palopo">Palopo</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Kota Tujuan <span class="required">*</span></label>
                            <select name="rute_tujuan" id="kota_tujuan" required>
                                <option value="">-- Pilih Kota Tujuan --</option>
                                <option value="Makassar">Makassar</option>
                                <option value="Bone">Bone</option>
                                <option value="Sinjai">Sinjai</option>
                                <option value="Toraja">Toraja</option>
                                <option value="Malino">Malino</option>
                                <option value="Gowa">Gowa</option>
                                <option value="Parepare">Parepare</option>
                                <option value="Soppeng">Soppeng</option>
                                <option value="Bulukumba">Bulukumba</option>
                                <option value="Barru">Barru</option>
                                <option value="Pangkep">Pangkep</option>
                                <option value="Sidrap">Sidrap</option>
                                <option value="Jeneponto">Jeneponto</option>
                                <option value="Takalar">Takalar</option>
                                <option value="Palopo">Palopo</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Tanggal Pengantaran <span class="required">*</span></label>
                            <input type="date" name="tanggal_pengantaran" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label>Pilih Driver <span class="required">*</span></label>
                            <select name="id_driver" id="driver_list" required>
                                <option value="">-- Pilih Kota Dahulu --</option>
                            </select>
                        </div>
                    </div>
                    
                    <div style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); padding: 2rem; border-radius: 12px; text-align: center; margin: 1.5rem 0;">
                        <label style="display: block; margin-bottom: 0.5rem; font-size: 1rem; color: #1e40af;">Estimasi Total Biaya</label>
                        <h2 id="total_harga_display" style="font-size: 2.5rem; color: #1e40af; font-weight: 800;">Rp 0</h2>
                        <p style="font-size: 0.9rem; color: #1e40af; margin-top: 0.5rem;">* Tarif Rp 5.000 per Kg</p>
                        <input type="hidden" name="total_harga_input" id="total_harga_input" value="0">
                    </div>
                    
                    <div class="form-group">
                        <label>Catatan Tambahan untuk Driver</label>
                        <textarea name="catatan_travel" rows="3" placeholder="Instruksi khusus untuk driver..."></textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 1.1rem; padding: 1.2rem;">
                     Kirim Pesanan Sekarang
                </button>
            </form>
        </div>
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
    $(document).ready(function() {
        // Tarif per Kg
        const tarifPerKg = 5000;

        // Hitung Total Berdasarkan Berat
        $('#berat').on('input', function() {
            let beratVal = parseFloat($(this).val()) || 0;
            let total = beratVal * tarifPerKg;
            
            $('#total_harga_display').text('Rp ' + total.toLocaleString('id-ID'));
            $('#total_harga_input').val(total);
        });

        // Filter Driver via AJAX berdasarkan Rute
        $('#kota_asal, #kota_tujuan').on('change', function() {
            let asal = $('#kota_asal').val();
            let tujuan = $('#kota_tujuan').val();
            
            if (asal && tujuan) {
                $.ajax({
                    url: 'ajax_filter_driver.php',
                    method: 'POST',
                    data: { asal: asal, tujuan: tujuan },
                    success: function(res) { 
                        $('#driver_list').html(res);
                    },
                    error: function() {
                        $('#driver_list').html('<option value="">Driver tidak tersedia</option>');
                    }
                });
            }
        });
    });
    </script>
</body>
</html>