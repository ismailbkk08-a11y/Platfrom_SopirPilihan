<?php
session_start();
require_once '../config/database.php';
// require_once '../includes/functions.php'; // Aktifkan jika file sudah ada

// Proteksi Halaman Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Statistik
$stats = [];
$stats['total_driver'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM driver"))['total'];
$stats['pending_driver'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM driver WHERE status_verifikasi = 'pending'"))['total'];
$stats['total_pelanggan'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pelanggan"))['total'];
$stats['total_pengiriman'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pengiriman_barang"))['total'];
$stats['pending_pengiriman'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pengiriman_barang WHERE status = 'pending'"))['total'];

// Driver pending verifikasi
$query_pending = "SELECT d.*, u.username, u.created_at as reg_date
                  FROM driver d
                  JOIN users u ON d.id_user = u.id_user
                  WHERE d.status_verifikasi = 'pending'
                  ORDER BY d.created_at DESC LIMIT 5";
$result_pending = mysqli_query($conn, $query_pending);

// Pengiriman terbaru
$result_pengiriman = mysqli_query($conn, "SELECT * FROM pengiriman_barang ORDER BY created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SopirPilihan.id</title>
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
        
        .header-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
        }
        .welcome-text h1 { font-size: 1.75rem; font-weight: 800; color: var(--dark); margin-bottom: 4px; }
        .welcome-text p { color: var(--text-muted); font-size: 1rem; }

        .date-badge {
            background: var(--white);
            padding: 10px 20px;
            border-radius: 14px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.03);
            font-weight: 700;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Stats Card Modern */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2.5rem; }
        .stat-card { 
            background: var(--white); 
            padding: 1.5rem; 
            border-radius: 24px; 
            border: 1px solid rgba(255,255,255,0.8);
            box-shadow: 0 10px 25px rgba(0,0,0,0.02);
            transition: transform 0.3s ease;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card .icon-box {
            width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;
            font-size: 1.5rem;
        }
        .stat-card h3 { font-size: 2.25rem; font-weight: 800; color: var(--dark); margin-bottom: 4px; }
        .stat-card p { font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        
        /* Colors for icon boxes */
        .bg-p { background: var(--primary-soft); color: var(--primary); }
        .bg-w { background: #fff7ed; color: var(--warning); }
        .bg-s { background: #f0fdf4; color: var(--success); }

        /* Tables & Sections */
        .section-card { 
            background: var(--white); 
            border-radius: 28px; 
            padding: 2rem; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.03); 
            margin-bottom: 2.5rem; 
            border: 1px solid rgba(0,0,0,0.02); 
        }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .card-header h2 { font-size: 1.25rem; font-weight: 800; color: var(--dark); }
        .view-all { 
            color: var(--primary); 
            text-decoration: none; 
            font-weight: 700; 
            font-size: 0.9rem; 
            background: var(--primary-soft); 
            padding: 8px 16px; 
            border-radius: 10px;
            transition: 0.3s;
        }
        .view-all:hover { background: var(--primary); color: white; }

        table { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
        th { text-align: left; padding: 1rem; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; font-weight: 800; }
        td { padding: 1.2rem 1rem; background: #fafafa; font-size: 0.95rem; transition: 0.2s; }
        td:first-child { border-radius: 12px 0 0 12px; }
        td:last-child { border-radius: 0 12px 12px 0; }
        tr:hover td { background: #f1f5f9; }
        
        .badge { padding: 6px 14px; border-radius: 10px; font-size: 0.75rem; font-weight: 800; display: inline-block; }
        .badge-warning { background: #ffedd5; color: #9a3412; }
        .badge-success { background: #dcfce7; color: #166534; }
        
        .btn-action { 
            padding: 8px 18px; 
            border-radius: 10px; 
            text-decoration: none; 
            background: var(--dark); 
            color: white; 
            font-size: 0.85rem; 
            font-weight: 700; 
            transition: 0.3s; 
            display: inline-block;
        }
        .btn-action:hover { background: var(--primary); transform: scale(1.05); }

        @media (max-width: 1200px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
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
            <a href="dashboard.php" class="active"><span></span> Dashboard</a>
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
        <div class="header-wrapper">
            <div class="welcome-text">
                <h1>Dashboard Overview</h1>
                <p>Halo, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>. Berikut adalah update hari ini.</p>
            </div>
            <div class="date-badge">
                <span></span> <?= date('d M Y') ?>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon-box bg-p">👥</div>
                <h3><?= $stats['total_driver'] ?></h3>
                <p>Total Driver</p>
            </div>
            <div class="stat-card">
                <div class="icon-box bg-w">⏳</div>
                <h3 style="color: var(--warning);"><?= $stats['pending_driver'] ?></h3>
                <p>Verifikasi Pending</p>
            </div>
            <div class="stat-card">
                <div class="icon-box bg-s">👤</div>
                <h3><?= $stats['total_pelanggan'] ?></h3>
                <p>Total Pelanggan</p>
            </div>
            <div class="stat-card">
                <div class="icon-box bg-p">📦</div>
                <h3><?= $stats['total_pengiriman'] ?></h3>
                <p>Total Order</p>
            </div>
        </div>

        <section class="section-card">
            <div class="card-header">
                <h2> Driver Menunggu Verifikasi</h2>
                <a href="verifikasi_driver.php" class="view-all">Lihat Semua</a>
            </div>
            <?php if(mysqli_num_rows($result_pending) > 0): ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Lengkap</th>
                            <th>No. HP</th>
                            <th>Tgl Daftar</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($driver = mysqli_fetch_assoc($result_pending)): ?>
                        <tr>
                            <td style="font-weight: 700; color: var(--dark);"><?= htmlspecialchars($driver['nama_lengkap']) ?></td>
                            <td><?= htmlspecialchars($driver['no_hp']) ?></td>
                            <td><?= date('d M Y', strtotime($driver['reg_date'])) ?></td>
                            <td><span class="badge badge-warning">Pending</span></td>
                            <td><a href="verifikasi_driver.php?id=<?= $driver['id_driver'] ?>" class="btn-action">Proses</a></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem;">
                    <span style="font-size: 3rem;">✅</span>
                    <p style="color: var(--text-muted); margin-top: 1rem; font-weight: 600;">Hebat! Tidak ada antrean verifikasi saat ini.</p>
                </div>
            <?php endif; ?>
        </section>

        <section class="section-card">
            <div class="card-header">
                <h2> Order Pengiriman Terbaru</h2>
                <a href="kelola_pengiriman.php" class="view-all">Lihat Semua</a>
            </div>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID Order</th>
                            <th>Nama Pengirim</th>
                            <th>Tujuan</th>
                            <th>Status</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($p = mysqli_fetch_assoc($result_pengiriman)): ?>
                        <tr>
                            <td style="font-weight: 800; color: var(--primary);">#<?= $p['id_pengiriman'] ?></td>
                            <td style="font-weight: 600;"><?= htmlspecialchars($p['nama_pengirim']) ?></td>
                            <td style="color: var(--text-muted); font-size: 0.85rem; max-width: 250px;"><?= htmlspecialchars($p['rute_tujuan']) ?></td>
                            <td>
                                <span class="badge <?= $p['status'] == 'completed' ? 'badge-success' : 'badge-warning' ?>">
                                    <?= ucfirst($p['status']) ?>
                                </span>
                            </td>
                            <td><a href="kelola_pengiriman.php?id=<?= $p['id_pengiriman'] ?>" class="btn-action" style="background: var(--bg-light); color: var(--dark);">Detail</a></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>