<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';

// Proteksi Role Admin (Menggunakan fungsi check_role jika tersedia di config, atau manual)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: kelola_driver.php");
    exit;
}

$id_driver = mysqli_real_escape_string($conn, $_GET['id']);

// Query Join 3 Tabel
$query = "SELECT d.*, u.username, u.status as status_akun, 
          sd.no_sim, sd.jenis_sim, sd.tanggal_berlaku, sd.foto_sim
          FROM driver d 
          JOIN users u ON d.id_user = u.id_user 
          LEFT JOIN sim_driver sd ON d.id_driver = sd.id_driver 
          WHERE d.id_driver = '$id_driver'";
          
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo "<script>alert('Data driver tidak ditemukan!'); window.location='kelola_driver.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Driver - SopirPilihan.id</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* CSS MASTER DARI DASHBOARD SEBELUMNYA */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #f8f9fd 0%, #f1f3f9 100%); min-height: 100vh; }
        .dashboard { display: flex; min-height: 100vh; }
        
        /* Sidebar Styles */
        /* Sidebar */
        :root {
            --primary: #2563eb;
            --primary-soft: rgba(37, 99, 235, 0.1);
            --dark: #0f172a;
            --bg-light: #f1f5f9;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --warning: #f59e0b;
            --success: #10b981;
            --white: #ffffff;
            --sidebar-width: 280px;
        }



        /* Sidebar Modern Styling */
        .sidebar { 
            width: var(--sidebar-width); 
            background: var(--dark); 
            color: white; 
            height: 100vh; 
            position: fixed; 
            z-index: 100; 
            display: flex; 
            flex-direction: column;
            box-shadow: 10px 0 30px rgba(0,0,0,0.05);
        }
        .sidebar-header { 
            padding: 2.5rem 1.5rem; 
            background: linear-gradient(135deg, var(--primary) 0%, #1e40af 100%); 
            margin: 1rem;
            border-radius: 16px;
        }
        .sidebar-header h2 { font-size: 1.2rem; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 4px; }
        .sidebar-menu { list-style: none; padding: 1rem; flex-grow: 1; }
        .sidebar-menu a { 
            color: #94a3b8; 
            text-decoration: none; 
            padding: 12px 16px; 
            display: flex; 
            align-items: center; 
            gap: 12px; 
            font-weight: 600; 
            font-size: 0.95rem;
            transition: all 0.3s; 
            border-radius: 12px;
            margin-bottom: 5px;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active { 
            color: white; 
            background: rgba(255,255,255,0.1); 
        }
        .sidebar-menu a.active { background: var(--primary); color: white; }
        .sidebar-menu span { font-size: 1.2rem; }
        /* Main Content Styles */
        .main-content { flex: 1; margin-left: 280px; padding: 2rem; }
        .header { background: white; padding: 1.75rem 2rem; border-radius: 16px; margin-bottom: 2rem; box-shadow: 0 4px 25px rgba(0,0,0,0.06); display: flex; justify-content: space-between; align-items: center; border: 1px solid rgba(0,0,0,0.05); }
        .header h1 { font-size: 1.8rem; color: #1a1a1a; font-weight: 800; }

        /* Detail Profile Specific Styles */
        .profile-container { background: white; border-radius: 16px; box-shadow: 0 4px 25px rgba(0,0,0,0.06); border: 1px solid rgba(0,0,0,0.05); overflow: hidden; }
        .profile-hero { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); padding: 3rem; color: white; display: flex; align-items: center; gap: 2.5rem; }
        .profile-img { width: 140px; height: 140px; border-radius: 20px; object-fit: cover; border: 4px solid rgba(255,255,255,0.2); box-shadow: 0 10px 20px rgba(0,0,0,0.2); }
        
        .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 2.5rem; padding: 3rem; }
        .info-group { margin-bottom: 1.5rem; }
        .label { font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.5rem; display: block; }
        .value { font-size: 1.05rem; color: #1e293b; font-weight: 600; line-height: 1.5; }
        
        .section-divider { padding: 1rem 3rem; background: #f8fafc; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-weight: 700; display: flex; align-items: center; gap: 0.5rem; }
        
        .doc-preview-card { background: #f8fafc; border-radius: 12px; padding: 1.5rem; border: 1px solid #e2e8f0; }
        .img-preview { width: 100%; height: 220px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd; margin-top: 1rem; transition: transform 0.3s ease; cursor: pointer; }
        .img-preview:hover { transform: translateY(-5px); box-shadow: 0 10px 15px rgba(0,0,0,0.1); }
        
        /* Badges & Buttons */
        .badge { padding: 0.5rem 1.2rem; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-verified { background: #d1fae5; color: #065f46; }
        
        .btn { padding: 0.75rem 1.5rem; border-radius: 10px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s; border: none; cursor: pointer; }
        .btn-primary { background: #2563eb; color: white; }
        .btn-success { background: #10b981; color: white; }
        .btn-danger { background: #ef4444; color: white; }
        .btn:hover { transform: translateY(-2px); opacity: 0.9; }

        .action-footer { padding: 2rem 3rem; background: #f8fafc; display: flex; justify-content: flex-end; gap: 1rem; border-top: 1px solid #f1f5f9; }

        @media (max-width: 1024px) { .sidebar { display: none; } .main-content { margin-left: 0; } }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>SopirPilihan</h2>
            <p style="font-size: 0.75rem; opacity: 0.8; font-weight: 600;">Control Center v2.0</p>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php"><span></span> Dashboard</a>
            <a href="kelola_driver.php"><span></span> Kelola Driver</a>
            <a href="verifikasi_driver.php"><span></span> Verifikasi Driver</a>
            <a href="kelola_pelanggan.php"><span></span> Pelanggan</a>
            <a href="jenis_mobil.php"><span></span> Jenis Mobil</a>
            <a href="kelola_pengiriman.php"><span></span> Pengiriman</a>
            <a href="laporan.php"><span></span> Laporan</a>
            
            <div style="margin-top: auto; padding-top: 2rem;">
                <a href="../auth/logout.php" style="color: #ef4444; background: rgba(239, 68, 68, 0.1);"><span></span> Keluar</a>
            </div>
        </nav>
    </aside>

        <main class="main-content">
            <div class="header">
                <div>
                    <a href="kelola_driver.php" style="text-decoration: none; color: #2563eb; font-weight: 600; font-size: 0.9rem;">← Kembali ke Daftar</a>
                    <h1 style="margin-top: 0.5rem;">Profil Detail Driver</h1>
                </div>
                <div class="badge <?= $data['status_verifikasi'] == 'pending' ? 'badge-pending' : 'badge-verified'; ?>">
                    Status: <?= strtoupper($data['status_verifikasi']); ?>
                </div>
            </div>

            <div class="profile-container">
                <div class="profile-hero">
                    <img src="../uploads/drivers/profile/<?= $data['foto_profil'] ?: 'default.png'; ?>" class="profile-img">
                    <div>
                        <h2 style="font-size: 2.2rem; font-weight: 800;"><?= htmlspecialchars($data['nama_lengkap']); ?></h2>
                        <p style="opacity: 0.7; font-size: 1.1rem; margin-top: 0.2rem;">@<?= htmlspecialchars($data['username']); ?> • Mitra Terdaftar</p>
                        <div style="margin-top: 1rem; display: flex; gap: 1rem;">
                            <span style="background: rgba(255,255,255,0.1); padding: 0.4rem 1rem; border-radius: 8px; font-size: 0.85rem;">ID: DRV-<?= str_pad($data['id_driver'], 4, '0', STR_PAD_LEFT); ?></span>
                            <span style="background: rgba(255,255,255,0.1); padding: 0.4rem 1rem; border-radius: 8px; font-size: 0.85rem;">Terdaftar: <?= date('d M Y', strtotime($data['created_at'])); ?></span>
                        </div>
                    </div>
                </div>

                <div class="info-grid">
                    <div class="info-left">
                        <div class="info-group">
                            <span class="label"> Kontak & WhatsApp</span>
                            <p class="value"><?= htmlspecialchars($data['email']); ?><br><?= htmlspecialchars($data['no_whatsapp']); ?></p>
                        </div>
                        <div class="info-group">
                            <span class="label"> Alamat Domisili</span>
                            <p class="value"><?= nl2br(htmlspecialchars($data['alamat'])); ?></p>
                        </div>
                        <div class="info-group">
                            <span class="label"> Rute Operasional</span>
                            <p class="value" style="color: #2563eb;">
                                <?= htmlspecialchars($data['rute_asal']); ?> 
                                <span style="color: #64748b; margin: 0 10px;">↔</span> 
                                <?= htmlspecialchars($data['rute_tujuan']); ?>
                            </p>
                        </div>
                    </div>
                    <div class="info-right">
                        <div class="info-group">
                            <span class="label"> Informasi Lisensi (SIM)</span>
                            <p class="value">
                                Tipe: <span style="color: #2563eb;"><?= $data['jenis_sim'] ?: '-'; ?></span><br>
                                Nomor: <?= $data['no_sim'] ?: '-'; ?><br>
                                Kadaluarsa: <?= $data['tanggal_berlaku'] ? date('d M Y', strtotime($data['tanggal_berlaku'])) : '-'; ?>
                            </p>
                        </div>
                        <div class="info-group">
                            <span class="label"> Deskripsi Driver</span>
                            <p class="value" style="font-weight: 400; font-style: italic; color: #475569;">"<?= htmlspecialchars($data['deskripsi']); ?>"</p>
                        </div>
                    </div>
                </div>

                <div class="section-divider"> Berkas & Dokumen Identitas</div>

                <div class="info-grid" style="padding-top: 2rem; padding-bottom: 2rem;">
                    <div class="doc-preview-card">
                        <span class="label">Kartu Tanda Penduduk (KTP)</span>
                        <?php if(!empty($data['foto_ktp'])): ?>
                            <img src="../uploads/drivers/ktp/<?= $data['foto_ktp']; ?>" class="img-preview" onclick="window.open(this.src)">
                        <?php else: ?>
                            <div style="height:220px; display:flex; align-items:center; justify-content:center; border: 2px dashed #e2e8f0; border-radius:8px; color:#94a3b8; margin-top:1rem;">Belum Diunggah</div>
                        <?php endif; ?>
                    </div>
                    <div class="doc-preview-card">
                        <span class="label">Surat Izin Mengemudi (SIM)</span>
                        <?php if(!empty($data['foto_sim'])): ?>
                            <img src="../uploads/drivers/sim/<?= $data['foto_sim']; ?>" class="img-preview" onclick="window.open(this.src)">
                        <?php else: ?>
                            <div style="height:220px; display:flex; align-items:center; justify-content:center; border: 2px dashed #e2e8f0; border-radius:8px; color:#94a3b8; margin-top:1rem;">Belum Diunggah</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="action-footer">
                    <button onclick="window.print()" class="btn" style="background: #e2e8f0; color: #1e293b;"> Cetak Profil</button>
                    
                    <?php if($data['status_verifikasi'] == 'pending'): ?>
                        <a href="verifikasi_driver.php?id=<?= $data['id_driver']; ?>&action=reject" class="btn btn-danger">✖ Tolak Pendaftaran</a>
                        <a href="verifikasi_driver.php?id=<?= $data['id_driver']; ?>&action=verify" class="btn btn-success">✔ Setujui Verifikasi</a>
                    <?php else: ?>
                        <a href="kelola_driver.php?edit=<?= $data['id_driver']; ?>" class="btn btn-primary"> Edit Data</a>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>