<?php
/**
 * Halaman Laporan - SopirPilihan.id
 * Deskripsi: Analitik pendapatan, performa layanan, dan tracking interaksi WA
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';

// Proteksi halaman admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$query_pesan = "SELECT * FROM kontak_pesan ORDER BY created_at DESC LIMIT 10";
$result_pesan = mysqli_query($conn, $query_pesan);
// 1. Logika Filter Tanggal
$tgl_mulai = isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : date('Y-m-01');
$tgl_selesai = isset($_GET['tgl_selesai']) ? $_GET['tgl_selesai'] : date('Y-m-t');

// 2. Query Statistik Utama
$query_stats = "SELECT 
    COUNT(*) as total_transaksi,
    SUM(total_harga) as total_pendapatan,
    AVG(total_harga) as rata_rata
    FROM pengiriman_barang 
    WHERE status = 'completed' 
    AND DATE(created_at) BETWEEN '$tgl_mulai' AND '$tgl_selesai'";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $query_stats));

// 3. Query Performa Driver (Top 5 berdasarkan Order Selesai)
$query_driver = "SELECT d.nama_lengkap, COUNT(p.id_pengiriman) as jumlah_order
    FROM pengiriman_barang p
    JOIN driver d ON p.id_driver = d.id_driver
    WHERE p.status = 'completed'
    AND DATE(p.created_at) BETWEEN '$tgl_mulai' AND '$tgl_selesai'
    GROUP BY d.id_driver
    ORDER BY jumlah_order DESC LIMIT 5";
$result_driver = mysqli_query($conn, $query_driver);

// 4. Query Driver Terpopuler (Berdasarkan Klik WA - Akumulasi Terbanyak)
$query_populer = "SELECT nama_lengkap, jumlah_klik_wa, foto_profil 
                  FROM driver 
                  WHERE status_verifikasi = 'verified'
                  ORDER BY jumlah_klik_wa DESC LIMIT 5";
$result_populer = mysqli_query($conn, $query_populer);

// Total Klik Global untuk persentase
// Ambil total klik
$result_total = mysqli_query($conn, "SELECT SUM(jumlah_klik_wa) as total FROM driver");
$data_total = mysqli_fetch_assoc($result_total);
$total_klik_global = $data_total['total'];

// Cegah error pembagian nol: Jika total 0, set ke 1 agar tidak error saat membagi
if ($total_klik_global <= 0) {
    $total_klik_global = 1;
}

// 5. Query Detail Transaksi
$query_transaksi = "SELECT p.*, d.nama_lengkap as nama_driver 
    FROM pengiriman_barang p
    LEFT JOIN driver d ON p.id_driver = d.id_driver
    WHERE p.status = 'completed'
    AND DATE(p.created_at) BETWEEN '$tgl_mulai' AND '$tgl_selesai'
    ORDER BY p.created_at DESC";
$result_transaksi = mysqli_query($conn, $query_transaksi);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Performa - SopirPilihan.id</title>
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
        .main-content { flex: 1; margin-left: var(--sidebar-width); padding: 2rem 3rem; }
        .header-wrapper { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        
        .filter-card { background: var(--white); padding: 1.5rem; border-radius: 20px; margin-bottom: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.02); display: flex; align-items: flex-end; gap: 1rem; border: 1px solid rgba(0,0,0,0.05); }
        .form-group { display: flex; flex-direction: column; gap: 5px; }
        .form-group label { font-size: 0.75rem; font-weight: 800; color: var(--text-muted); }
        .input-style { padding: 10px 15px; border: 1px solid #e2e8f0; border-radius: 10px; font-weight: 600; outline: none; transition: 0.3s; }
        .input-style:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-soft); }
        .btn-filter { background: var(--dark); color: white; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .btn-filter:hover { background: var(--primary); transform: translateY(-2px); }

        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: var(--white); padding: 1.5rem; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.02); position: relative; overflow: hidden; }
        .stat-card p { color: var(--text-muted); font-weight: 700; font-size: 0.8rem; text-transform: uppercase; margin-bottom: 5px; }
        .stat-card h2 { font-size: 1.8rem; font-weight: 800; color: var(--dark); }

        .section-card { background: var(--white); border-radius: 24px; padding: 2rem; box-shadow: 0 10px 30px rgba(0,0,0,0.02); margin-bottom: 2rem; border: 1px solid rgba(0,0,0,0.02); }
        .section-title { font-size: 1.1rem; font-weight: 800; color: var(--dark); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px; border-left: 4px solid var(--primary); padding-left: 15px; }
        
        table { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
        th { text-align: left; padding: 1rem; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; font-weight: 800; }
        td { padding: 1.2rem 1rem; background: #fafafa; font-size: 0.9rem; vertical-align: middle; }
        td:first-child { border-radius: 12px 0 0 12px; }
        td:last-child { border-radius: 0 12px 12px 0; }
        tr:hover td { background: #f1f5f9; }

        .popularity-bar { height: 6px; background: #e2e8f0; border-radius: 10px; margin-top: 8px; overflow: hidden; width: 100px; }
        .popularity-fill { height: 100%; background: var(--primary); border-radius: 10px; }

        .btn-print { background: var(--primary-soft); color: var(--primary); border: none; padding: 10px 20px; border-radius: 12px; font-weight: 800; cursor: pointer; transition: 0.3s; display: flex; align-items: center; gap: 8px; }
        .btn-print:hover { background: var(--primary); color: white; }
        
        @media print {
            .sidebar, .filter-card, .btn-print { display: none; }
            .main-content { margin-left: 0; padding: 0; }
            .section-card { box-shadow: none; border: 1px solid #eee; }
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
            <a href="verifikasi_driver.php"><span></span> Verifikasi Driver</a>
            <a href="kelola_pelanggan.php"><span></span> Pelanggan</a>
            <a href="jenis_mobil.php"><span></span> Jenis Mobil</a>
            <a href="kelola_pengiriman.php"><span></span> Pengiriman</a>
            <a href="laporan.php" class="active"><span></span> Laporan</a>
            
            <div style="margin-top: auto; padding-top: 2rem;">
                <a href="../auth/logout.php" style="color: #ef4444; background: rgba(239, 68, 68, 0.1);"><span></span> Keluar</a>
            </div>
        </nav>
    </aside>

    <main class="main-content">
        <div class="header-wrapper">
            <div>
                <h1 style="font-size: 1.75rem; font-weight: 800;">Laporan Performa & Interaksi</h1>
                <p style="color: var(--text-muted);">Data transaksi dari <?= date('d M Y', strtotime($tgl_mulai)) ?> - <?= date('d M Y', strtotime($tgl_selesai)) ?></p>
            </div>
            <button onclick="window.print()" class="btn-print"><span></span> Cetak Laporan</button>
        </div>

        <div class="filter-card">
            <form method="GET" style="display: flex; gap: 1.5rem; align-items: flex-end;">
                <div class="form-group">
                    <label>MULAI TANGGAL</label>
                    <input type="date" name="tgl_mulai" class="input-style" value="<?= $tgl_mulai ?>">
                </div>
                <div class="form-group">
                    <label>SAMPAI TANGGAL</label>
                    <input type="date" name="tgl_selesai" class="input-style" value="<?= $tgl_selesai ?>">
                </div>
                <button type="submit" class="btn-filter">Perbarui Data</button>
            </form>
        </div>

        <section class="section-card" style="margin-top: 2rem;">
    <div class="section-title"> Pesan Masuk (Kontak Kami)</div>
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Pengirim</th>
                    <th>Email</th>
                    <th>Isi Pesan</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($result_pesan) > 0): ?>
                    <?php while($msg = mysqli_fetch_assoc($result_pesan)): ?>
                    <tr>
                        <td style="white-space: nowrap; font-size: 0.8rem; color: var(--text-muted);">
                            <?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?>
                        </td>
                        <td style="font-weight: 700;"><?= htmlspecialchars($msg['nama']) ?></td>
                        <td><?= htmlspecialchars($msg['email']) ?></td>
                        <td style="color: var(--text-main); font-style: italic;">
                            "<?= nl2br(htmlspecialchars($msg['pesan'])) ?>"
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align: center; padding: 2rem;">Belum ada pesan masuk.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

        <div class="stats-grid">
            <div class="stat-card" style="border-top: 5px solid var(--success);">
                <p>Total Pendapatan</p>
                <h2>Rp <?= number_format($stats['total_pendapatan'] ?? 0, 0, ',', '.') ?></h2>
            </div>
            <div class="stat-card" style="border-top: 5px solid var(--primary);">
                <p>Order Selesai</p>
                <h2><?= number_format($stats['total_transaksi'] ?? 0) ?> Transaksi</h2>
            </div>
            <div class="stat-card" style="border-top: 5px solid var(--warning);">
                <p>Rata-rata Pendapatan</p>
                <h2>Rp <?= number_format($stats['rata_rata'] ?? 0, 0, ',', '.') ?></h2>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
            <section class="section-card">
                <div class="section-title"> Top Performa (Order Selesai)</div>
                <table>
                    <thead>
                        <tr>
                            <th>Driver</th>
                            <th style="text-align: right;">Total Selesai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($result_driver) > 0): ?>
                            <?php while($d = mysqli_fetch_assoc($result_driver)): ?>
                            <tr>
                                <td style="font-weight: 700;"><?= $d['nama_lengkap'] ?></td>
                                <td style="text-align: right; font-weight: 800; color: var(--success);"><?= $d['jumlah_order'] ?> Order</td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="2" style="text-align: center; color: var(--text-muted); padding: 2rem;">Tidak ada transaksi di periode ini.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>

            <section class="section-card">
                <div class="section-title"> Popularitas (Klik WhatsApp)</div>
                <table>
                    <thead>
                        <tr>
                            <th>Driver</th>
                            <th style="text-align: right;">Engagement</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($result_populer) > 0): ?>
                            <?php while($p = mysqli_fetch_assoc($result_populer)): 
                                $persen = ($p['jumlah_klik_wa'] > 0) ? ($p['jumlah_klik_wa'] / $total_klik_global) * 100 : 0;
                            ?>
                            <tr>
                                <td style="font-weight: 700;">
                                    <?= $p['nama_lengkap'] ?>
                                    <div class="popularity-bar"><div class="popularity-fill" style="width: <?= $persen ?>%;"></div></div>
                                </td>
                                <td style="text-align: right; font-weight: 800; color: var(--primary);">
                                    <?= number_format($p['jumlah_klik_wa']) ?> <span style="font-size: 0.7rem; color: var(--text-muted);">KLIK</span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="2" style="text-align: center; color: var(--text-muted); padding: 2rem;">Belum ada interaksi WA tercatat.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </div>

        <section class="section-card">
            <div class="section-title"> Rincian Transaksi Selesai</div>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID Transaksi</th>
                            <th>Driver Terpilih</th>
                            <th>Rute & Lokasi</th>
                            <th style="text-align: right;">Biaya Layanan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($result_transaksi) > 0): ?>
                            <?php while($t = mysqli_fetch_assoc($result_transaksi)): ?>
                            <tr>
                                <td style="font-weight: 800; color: var(--primary);">#TRX-<?= $t['id_pengiriman'] ?></td>
                                <td style="font-weight: 600;"><?= $t['nama_driver'] ?></td>
                                <td style="font-size: 0.8rem; color: var(--text-muted);">
                                    <span style="color: var(--dark); font-weight: 600;">Dari:</span> <?= htmlspecialchars($t['lokasi_penjemputan']) ?> <br>
                                    <span style="color: var(--dark); font-weight: 600;">Ke:</span> <?= htmlspecialchars($t['rute_tujuan']) ?>
                                </td>
                                <td style="text-align: right; font-weight: 800; color: var(--success);">Rp <?= number_format($t['total_harga'], 0, ',', '.') ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="text-align: center; color: var(--text-muted); padding: 3rem;">Data transaksi tidak ditemukan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

</body>
</html>