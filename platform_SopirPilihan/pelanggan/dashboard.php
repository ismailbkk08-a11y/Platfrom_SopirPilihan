<?php
session_start();
require_once '../config/database.php';

// 1. KEAMANAN & SESSION
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pelanggan') {
    header("Location: ../auth/login.php");
    exit;
}

$id_user = $_SESSION['user_id'];

// 2. AMBIL DATA PELANGGAN & USER
$query_user = "SELECT p.*, u.username FROM users u 
               LEFT JOIN pelanggan p ON u.id_user = p.id_user 
               WHERE u.id_user = $id_user";
$res_user = mysqli_query($conn, $query_user);
$pelanggan = mysqli_fetch_assoc($res_user);

$id_pelanggan = $pelanggan['id_pelanggan'] ?? 0;

// 3. STATISTIK
$q_stat = mysqli_query($conn, "SELECT COUNT(*) as total FROM rating_driver WHERE id_pelanggan = $id_pelanggan");
$stat_rating = mysqli_fetch_assoc($q_stat)['total'] ?? 0;

// 4. QUERY DRIVER LANGGANAN
$res_favorit = mysqli_query($conn, "SELECT d.id_driver, d.nama_lengkap, d.foto_profil, d.no_hp, d.no_whatsapp,
                COUNT(r.id_rating) as jumlah_pake, AVG(r.rating) as skor_anda 
                FROM rating_driver r 
                JOIN driver d ON r.id_driver = d.id_driver 
                WHERE r.id_pelanggan = $id_pelanggan 
                GROUP BY d.id_driver ORDER BY jumlah_pake DESC LIMIT 3");

// 5. ULASAN TERBARU
$res_recent = mysqli_query($conn, "SELECT r.*, d.nama_lengkap as nama_driver 
                FROM rating_driver r 
                JOIN driver d ON r.id_driver = d.id_driver 
                WHERE r.id_pelanggan = $id_pelanggan 
                ORDER BY r.created_at DESC LIMIT 3");

// Logic Foto Profil Pelanggan
$foto_pelanggan_path = !empty($pelanggan['foto_profil']) 
    ? '../uploads/pelanggan/' . $pelanggan['foto_profil'] 
    : 'https://ui-avatars.com/api/?name=' . urlencode($pelanggan['nama_lengkap'] ?? 'User') . '&background=2563eb&color=fff';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pelanggan - SopirPilihan.id</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --secondary: #64748b;
            --bg-body: #f8fafc;
            --sidebar-bg: #0f172a;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            display: flex;
            min-height: 100vh;
            color: #1e293b;
        }

        /* --- STYLED NAVBAR (Sesuai Preferensi Anda) --- */
        .glass-nav {
            position: fixed;
            top: 0;
            right: 0;
            left: 280px; /* Lebar Sidebar */
            background: rgba(255, 255, 255, 0.48) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2) !important;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05) !important;
            z-index: 90;
            padding: 0.75rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-brand { color: var(--primary); font-weight: 800; font-size: 1.2rem; }

        /* --- SIDEBAR REFINED --- */
        .sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            color: white;
            height: 100vh;
            position: fixed;
            z-index: 100;
            transition: all 0.3s ease;
        }

        .sidebar-profile {
            padding: 3rem 1.5rem 2rem;
            text-align: center;
            background: linear-gradient(to bottom, rgba(37, 99, 235, 0.2), transparent);
        }

        .sidebar-profile img {
            width: 85px; height: 85px;
            border-radius: 50%;
            border: 3px solid var(--primary);
            padding: 3px;
            object-fit: cover;
            margin-bottom: 1rem;
        }

        .sidebar-menu { list-style: none; padding: 1rem 0; }
        .sidebar-menu a {
            color: #94a3b8;
            text-decoration: none;
            padding: 0.9rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: 0.3s;
            font-weight: 500;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255,255,255,0.05);
            color: white;
            border-right: 4px solid var(--primary);
        }

        .sidebar-menu a.logout { color: #f87171; margin-top: 2rem; }

        /* --- MAIN CONTENT --- */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 6rem 2rem 2rem; /* padding top ditambah karena nav fixed */
        }

        /* --- WELCOME BANNER & STATS --- */
        .dashboard-header {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .welcome-banner {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.02);
            border: 1px solid rgba(0,0,0,0.05);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .welcome-banner::after {
            content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 6px;
            background: linear-gradient(90deg, var(--primary), #60a5fa);
            border-radius: 20px 20px 0 0;
        }

        .stat-card-mini {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 2rem;
            border-radius: 20px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* --- ACTION CARDS --- */
        .action-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .action-card {
            background: white;
            padding: 1.5rem;
            border-radius: 16px;
            text-align: center;
            text-decoration: none;
            color: #334155;
            transition: 0.3s;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0,0,0,0.05);
            border-color: var(--primary);
        }

        .action-card i { font-size: 1.8rem; display: block; margin-bottom: 10px; }

        /* --- INFO SECTIONS (Driver & Rating) --- */
        .info-grid {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 2rem;
        }

        .section-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        }

        .section-title {
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .driver-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-radius: 12px;
            background: #f8fafc;
            margin-bottom: 0.75rem;
            transition: 0.2s;
        }

        .driver-item:hover { background: #f1f5f9; }

        .driver-avatar {
            width: 50px; height: 50px; border-radius: 12px;
            object-fit: cover; margin-right: 1rem;
        }

        .btn-wa-sm {
            background: #22c55e; color: white;
            text-decoration: none; padding: 6px 12px;
            border-radius: 8px; font-size: 0.8rem; font-weight: 600;
        }

        .rating-item {
            padding: 1rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .rating-item:last-child { border: none; }

        .stars { color: #f59e0b; font-size: 0.8rem; }

        @media (max-width: 1024px) {
            .sidebar { width: 80px; }
            .sidebar-profile h3, .sidebar-profile p, .sidebar-menu span { display: none; }
            .main-content, .glass-nav { left: 80px; }
            .dashboard-header, .info-grid { grid-template-columns: 1fr; }
            .action-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-profile">
            <img src="<?= $foto_pelanggan_path ?>" alt="Profil">
            <h3><?= htmlspecialchars(explode(' ', $pelanggan['nama_lengkap'] ?? 'User')[0]); ?></h3>
            <p style="font-size: 0.75rem; color: #94a3b8;">Pelanggan</p>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="active"><span></span> <span>Dashboard</span></a>
            <a href="profil.php"><span></span> <span>Profil Saya</span></a>
            <a href="riwayat_rating.php"><span></span> <span>Riwayat Rating</span></a>
            <a href="../pages/daftar_driver.php"><span></span> <span>Cari Driver</span></a>
            <a href="../auth/logout.php" class="logout"><span></span> <span>Keluar</span></a>
        </nav>
    </aside>

    <nav class="glass-nav">
        <div class="nav-brand">SopirPilihan.id</div>
        <div style="font-size: 0.85rem; font-weight: 600; color: var(--secondary);">
            Pelanggan Dashboard
        </div>
    </nav>

    <main class="main-content">
        
        <div class="dashboard-header">
            <div class="welcome-banner">
                <h1 style="font-size: 1.8rem; font-weight: 800; color: #0f172a;">Halo, <?= htmlspecialchars($pelanggan['nama_lengkap'] ?? 'User'); ?>! </h1>
                <p style="color: #64748b; margin-top: 5px;">Senang melihat Anda kembali. Mau pergi ke mana hari ini?</p>
            </div>
            <div class="stat-card-mini">
                <span style="font-size: 0.8rem; text-transform: uppercase; opacity: 0.8; letter-spacing: 1px;">Review Diberikan</span>
                <h2 style="font-size: 2.5rem; font-weight: 800;"><?= number_format($stat_rating); ?></h2>
            </div>
        </div>

        <div class="action-grid">
            <a href="../pages/daftar_driver.php" class="action-card"><i>🔍</i><p>Cari Driver</p></a>
            <a href="../pages/pengiriman_barang.php" class="action-card"><i>📦</i><p>Kirim Barang</p></a>
            <a href="riwayat_rating.php" class="action-card"><i>📜</i><p>Riwayat</p></a>
            <a href="profil.php" class="action-card"><i>⚙️</i><p>Pengaturan</p></a>
        </div>

        <div class="info-grid">
            <div class="section-card">
                <div class="section-title">
                    <span> Driver Langganan</span>
                    <a href="../pages/daftar_driver.php" style="font-size: 0.8rem; color: var(--primary); text-decoration: none;">Cari Baru →</a>
                </div>
                
                <?php if($res_favorit && mysqli_num_rows($res_favorit) > 0): ?>
                    <?php while($fav = mysqli_fetch_assoc($res_favorit)): 
                        $img = !empty($fav['foto_profil']) ? '../uploads/drivers/profile/'.$fav['foto_profil'] : 'https://ui-avatars.com/api/?name='.urlencode($fav['nama_lengkap']);
                        $wa_clean = preg_replace('/[^0-9]/', '', $fav['no_whatsapp'] ?: $fav['no_hp']);
                    ?>
                        <div class="driver-item">
                            <img src="<?= $img ?>" class="driver-avatar" alt="Driver">
                            <div style="flex: 1;">
                                <h4 style="font-size: 0.95rem; font-weight: 700;"><?= htmlspecialchars($fav['nama_lengkap']); ?></h4>
                                <div class="stars">⭐ <?= number_format($fav['skor_anda'], 1); ?> <span style="color: #94a3b8; font-size: 0.75rem;">(<?= $fav['jumlah_pake']; ?>x order)</span></div>
                            </div>
                            <a href="https://wa.me/<?= $wa_clean; ?>" target="_blank" class="btn-wa-sm">WA</a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 2rem; color: #94a3b8;">Belum ada driver favorit.</div>
                <?php endif; ?>
            </div>

            <div class="section-card">
                <div class="section-title"> Ulasan Anda</div>
                <?php if($res_recent && mysqli_num_rows($res_recent) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($res_recent)): ?>
                        <div class="rating-item">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <strong style="font-size: 0.85rem;"><?= htmlspecialchars($row['nama_driver']); ?></strong>
                                <small style="color: #94a3b8; font-size: 0.7rem;"><?= date('d/m/y', strtotime($row['created_at'])); ?></small>
                            </div>
                            <div class="stars"><?= str_repeat('⭐', $row['rating']); ?></div>
                            <p style="font-size: 0.8rem; color: #64748b; margin-top: 5px; font-style: italic;">"<?= htmlspecialchars($row['komentar']); ?>"</p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 2rem; color: #94a3b8;">Belum ada ulasan.</div>
                <?php endif; ?>
            </div>
        </div>
    </main>

</body>
</html>