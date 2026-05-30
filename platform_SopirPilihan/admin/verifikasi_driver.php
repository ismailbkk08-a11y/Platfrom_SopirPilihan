<?php
/**
 * Halaman Verifikasi Driver - SopirPilihan.id
 * Deskripsi: Meninjau pendaftaran driver baru yang berstatus 'pending'
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';

// 1. Proteksi halaman admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$message = '';
$message_type = '';

// 2. Proses verifikasi (Setujui/Tolak)
if (isset($_POST['verify'])) {
    $id_driver = intval($_POST['id_driver']);
    $status = mysqli_real_escape_string($conn, $_POST['status']); // 'verified' atau 'rejected'
    
    $query = "UPDATE driver SET status_verifikasi = '$status' WHERE id_driver = $id_driver";
    if (mysqli_query($conn, $query)) {
        $message = $status == 'verified' ? 'Driver berhasil disetujui sebagai mitra!' : 'Pendaftaran driver telah ditolak.';
        $message_type = $status == 'verified' ? 'success' : 'warning';
    } else {
        $message = "Gagal memperbarui status verifikasi.";
        $message_type = "danger";
    }
}

// 3. Query Utama: Driver status 'pending'
$query = "SELECT d.*, u.username, u.created_at as reg_date,
          (SELECT COUNT(*) FROM sim_driver WHERE id_driver = d.id_driver) as has_sim
          FROM driver d
          JOIN users u ON d.id_user = u.id_user
          WHERE d.status_verifikasi = 'pending'
          ORDER BY d.created_at DESC";
$result = mysqli_query($conn, $query);

// 4. Statistik
$total_pending = mysqli_num_rows($result);
$total_verified = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM driver WHERE status_verifikasi = 'verified'"))['total'];
$total_rejected = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM driver WHERE status_verifikasi = 'rejected'"))['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Driver - SopirPilihan.id</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
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

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--bg-light); color: var(--text-main); display: flex; min-height: 100vh; }

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

        /* Main Content */
        .main-content { flex: 1; margin-left: var(--sidebar-width); padding: 2rem 3rem; }
        
        .header-wrapper { margin-bottom: 2.5rem; }
        .header-wrapper h1 { font-size: 1.75rem; font-weight: 800; color: var(--dark); }
        .header-wrapper p { color: var(--text-muted); }

        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 2.5rem; }
        .stat-card { 
            background: var(--white); padding: 1.5rem; border-radius: 24px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.02); border: 1px solid rgba(0,0,0,0.02);
        }
        .icon-box { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; font-size: 1.5rem; }
        .stat-card h3 { font-size: 1.8rem; font-weight: 800; color: var(--dark); }
        .stat-card p { font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; }

        /* Driver Cards */
        .driver-card { 
            background: var(--white); border-radius: 24px; padding: 1.5rem; 
            display: flex; gap: 2rem; margin-bottom: 1.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.02);
            transition: 0.3s;
        }
        .driver-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.06); }
        
        .driver-photo { width: 140px; height: 140px; border-radius: 20px; object-fit: cover; background: #f8fafc; }
        
        .driver-details { flex: 1; }
        .driver-details h2 { font-size: 1.4rem; font-weight: 800; color: var(--dark); margin-bottom: 1rem; }
        
        .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 1.5rem; }
        .info-item { font-size: 0.9rem; color: var(--text-main); display: flex; align-items: center; gap: 8px; font-weight: 500; }
        .info-item strong { color: var(--text-muted); min-width: 100px; font-weight: 600; font-size: 0.8rem; text-transform: uppercase; }

        /* Badges */
        .badge { padding: 6px 14px; border-radius: 10px; font-size: 0.7rem; font-weight: 800; display: inline-flex; align-items: center; gap: 5px; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-danger { background: #fee2e2; color: #991b1b; }

        /* Action Buttons */
        .btn-group { display: flex; gap: 10px; margin-top: 1rem; }
        .btn { 
            padding: 10px 20px; border-radius: 12px; font-weight: 700; font-size: 0.85rem; 
            cursor: pointer; border: none; text-decoration: none; transition: 0.3s;
            display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-success { background: var(--success); color: white; box-shadow: 0 6px 15px rgba(16, 185, 129, 0.2); }
        .btn-danger { background: #fff1f2; color: var(--danger); }
        .btn-outline { background: var(--bg-light); color: var(--text-main); }
        .btn:hover:not(:disabled) { transform: translateY(-2px); opacity: 0.9; }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; }

        /* Alert */
        .alert { 
            padding: 1rem 1.5rem; border-radius: 16px; margin-bottom: 2rem; 
            font-weight: 600; font-size: 0.95rem; border-left: 5px solid;
        }
        .alert-success { background: #f0fdf4; color: #166534; border-left-color: var(--success); }
        .alert-warning { background: #fffbeb; color: #92400e; border-left-color: var(--warning); }

        .empty-state { 
            background: var(--white); padding: 5rem; text-align: center; 
            border-radius: 30px; border: 2px dashed #e2e8f0; 
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>SopirPilihan</h2>
            <p style="font-size: 0.75rem; opacity: 0.8; font-weight: 600;">Control Center v2.0</p>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" ><span></span> Dashboard</a>
            <a href="kelola_driver.php"><span></span> Kelola Driver</a>
            <a href="verifikasi_driver.php"class="active"><span></span> Verifikasi Driver</a>
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
        <div class="header-wrapper">
            <h1>Verifikasi Pendaftaran</h1>
            <p>Tinjau dokumen dan identitas calon mitra driver baru.</p>
        </div>

        <?php if($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo ($message_type == 'success' ? '✅ ' : '⚠️ ') . $message; ?>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon-box" style="background: #fff7ed; color: var(--warning);">⏳</div>
                <h3><?php echo number_format($total_pending); ?></h3>
                <p>Antrean Pending</p>
            </div>
            <div class="stat-card">
                <div class="icon-box" style="background: #f0fdf4; color: var(--success);">✅</div>
                <h3><?php echo number_format($total_verified); ?></h3>
                <p>Total Disetujui</p>
            </div>
            <div class="stat-card">
                <div class="icon-box" style="background: #fef2f2; color: var(--danger);">❌</div>
                <h3><?php echo number_format($total_rejected); ?></h3>
                <p>Total Ditolak</p>
            </div>
        </div>

        <div class="driver-list">
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($driver = mysqli_fetch_assoc($result)): 
                    $is_incomplete = (empty($driver['foto_ktp']) || $driver['has_sim'] == 0);
                ?>
                <div class="driver-card">
                    <img src="<?php echo !empty($driver['foto_profil']) ? '../uploads/drivers/profile/'.$driver['foto_profil'] : '../assets/images/default-avatar.png'; ?>" class="driver-photo">
                    
                    <div class="driver-details">
                        <h2><?php echo htmlspecialchars($driver['nama_lengkap']); ?></h2>
                        
                        <div class="info-grid">
                            <div class="info-item"><strong> WhatsApp</strong> <?php echo htmlspecialchars($driver['no_whatsapp'] ?? '-'); ?></div>
                            <div class="info-item"><strong> Tgl Daftar</strong> <?php echo date('d M Y', strtotime($driver['reg_date'])); ?></div>
                            <div class="info-item">
                                <strong> Dokumen KTP</strong> 
                                <?php if(!empty($driver['foto_ktp'])): ?>
                                    <span class="badge badge-success">✓ Tersedia</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">✕ Kosong</span>
                                <?php endif; ?>
                            </div>
                            <div class="info-item">
                                <strong> Dokumen SIM</strong> 
                                <?php if($driver['has_sim'] > 0): ?>
                                    <span class="badge badge-success">✓ Tersedia</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">✕ Kosong</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="btn-group">
                            <a href="detail_driver.php?id=<?= $driver['id_driver']; ?>" class="btn btn-outline"> Periksa Dokumen</a>
                            
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Setujui driver ini sebagai mitra resmi?')">
                                <input type="hidden" name="id_driver" value="<?php echo $driver['id_driver']; ?>">
                                <input type="hidden" name="status" value="verified">
                                <button type="submit" name="verify" class="btn btn-success" <?php echo $is_incomplete ? 'disabled title="Dokumen belum lengkap"' : ''; ?>>
                                    Setujui Mitra
                                </button>
                            </form>

                            <form method="POST" style="display: inline;" onsubmit="return confirm('Tolak pendaftaran ini?')">
                                <input type="hidden" name="id_driver" value="<?php echo $driver['id_driver']; ?>">
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" name="verify" class="btn btn-danger">Tolak</button>
                            </form>
                        </div>
                        
                        <?php if($is_incomplete): ?>
                            <p style="color: var(--danger); font-size: 0.75rem; margin-top: 12px; font-weight: 700;">
                                * Tombol setujui dinonaktifkan karena dokumen (KTP/SIM) belum lengkap.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div style="font-size: 3.5rem; margin-bottom: 1rem;">✨</div>
                    <h3 style="font-weight: 800; color: var(--dark);">Semua Beres!</h3>
                    <p style="color: var(--text-muted); font-weight: 500;">Tidak ada antrean pendaftaran driver saat ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>