<?php
session_start();
require_once '../config/database.php';

// Proteksi Session
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pelanggan') {
    header("Location: ../auth/login.php");
    exit;
}

$id_pelanggan = $_SESSION['profile_id'];

// 1. Ambil data pelanggan untuk Sidebar
$query_pelanggan = "SELECT nama_lengkap, foto_profil FROM pelanggan WHERE id_pelanggan = $id_pelanggan";
$res_p = mysqli_query($conn, $query_pelanggan);
$data_p = mysqli_fetch_assoc($res_p);

// 2. Ambil statistik
$query_stats = "SELECT 
                COUNT(*) as total_rating,
                AVG(rating) as avg_rating
                FROM rating_driver 
                WHERE id_pelanggan = $id_pelanggan";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $query_stats));

// 3. Ambil semua rating
$query_rating = "SELECT r.*, d.nama_lengkap as nama_driver, d.foto_profil as foto_driver, d.no_whatsapp, d.no_hp
                 FROM rating_driver r
                 JOIN driver d ON r.id_driver = d.id_driver
                 WHERE r.id_pelanggan = $id_pelanggan
                 ORDER BY r.created_at DESC";
$result_rating = mysqli_query($conn, $query_rating);

// Logic Foto Profil Pelanggan (Sidebar)
$foto_path = !empty($data_p['foto_profil']) 
    ? '../uploads/pelanggan/' . $data_p['foto_profil'] 
    : 'https://ui-avatars.com/api/?name=' . urlencode($data_p['nama_lengkap'] ?? 'User') . '&background=2563eb&color=fff';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Rating - SopirPilihan.id</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
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

        /* --- GLASSMORPHISM NAVBAR (KONSISTEN) --- */
        .glass-nav {
            position: fixed;
            top: 0; right: 0; left: 280px;
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

        /* --- SIDEBAR (KONSISTEN) --- */
        .sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            color: white;
            height: 100vh;
            position: fixed;
            z-index: 100;
        }
        .sidebar-profile {
            padding: 3rem 1.5rem 2rem;
            text-align: center;
            background: linear-gradient(to bottom, rgba(37, 99, 235, 0.2), transparent);
        }
        .sidebar-profile img {
            width: 80px; height: 80px;
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

        /* --- MAIN CONTENT --- */
        .main-content { flex: 1; margin-left: 280px; padding: 6rem 2rem 2rem; }

        .page-header { margin-bottom: 2.5rem; }
        .page-header h1 { font-size: 1.8rem; color: #1e293b; font-weight: 800; }
        .page-header p { color: #64748b; margin-top: 0.25rem; }

        /* Stats Card Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.02);
            display: flex;
            align-items: center;
            gap: 1.25rem;
            border: 1px solid rgba(0,0,0,0.05);
        }
        .stat-icon {
            width: 48px; height: 48px;
            border-radius: 14px;
            background: #eff6ff;
            color: var(--primary);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem;
        }
        .stat-info h3 { font-size: 1.5rem; color: #1e293b; font-weight: 800; }
        .stat-info p { font-size: 0.8rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }

        /* Rating List */
        .rating-container { display: grid; gap: 1.25rem; }
        .rating-card {
            background: white;
            padding: 1.5rem;
            border-radius: 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.02);
            border: 1px solid rgba(0,0,0,0.04);
            display: flex;
            gap: 1.5rem;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .rating-card:hover { transform: translateY(-3px); box-shadow: 0 12px 25px rgba(0,0,0,0.06); }
        
        .driver-img {
            width: 75px; height: 75px;
            border-radius: 18px;
            object-fit: cover;
            border: 2px solid #f1f5f9;
        }

        .rating-body { flex: 1; }
        .rating-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        .driver-name { font-weight: 700; color: #1e293b; font-size: 1.1rem; }
        .rating-date { font-size: 0.8rem; color: #94a3b8; font-weight: 500; }
        
        .stars { color: #f59e0b; margin-bottom: 0.75rem; font-size: 1rem; letter-spacing: 2px; }
        
        .comment {
            color: #475569;
            line-height: 1.6;
            background: #f8fafc;
            padding: 1rem 1.25rem;
            border-radius: 16px;
            margin-bottom: 1.25rem;
            font-size: 0.95rem;
            border-left: 4px solid #e2e8f0;
        }

        .actions { display: flex; gap: 0.75rem; }
        .btn {
            padding: 0.7rem 1.25rem;
            border-radius: 12px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
        }
        .btn-wa { background: #dcfce7; color: #15803d; }
        .btn-wa:hover { background: #22c55e; color: white; transform: translateY(-2px); }
        .btn-profile { background: #f1f5f9; color: #475569; }
        .btn-profile:hover { background: #e2e8f0; color: #1e293b; }

        .empty-state {
            text-align: center;
            padding: 5rem 2rem;
            background: white;
            border-radius: 24px;
            border: 2px dashed #e2e8f0;
            color: #94a3b8;
        }

        @media (max-width: 1024px) {
            .sidebar { width: 80px; }
            .sidebar-profile h3, .sidebar-profile p, .sidebar-menu span:last-child { display: none; }
            .main-content, .glass-nav { left: 80px; }
        }

        @media (max-width: 768px) {
            .rating-card { flex-direction: column; text-align: center; align-items: center; }
            .rating-top { flex-direction: column; gap: 0.5rem; }
            .actions { justify-content: center; }
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-profile">
            <img src="<?= $foto_path ?>" alt="Profil">
            <h3><?= htmlspecialchars(explode(' ', $data_p['nama_lengkap'] ?? 'User')[0]); ?></h3>
            <p style="font-size: 0.75rem; color: #94a3b8;">Pelanggan</p>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php"><span></span> <span>Dashboard</span></a>
            <a href="profil.php"><span></span> <span>Profil Saya</span></a>
            <a href="riwayat_rating.php" class="active"><span></span> <span>Riwayat Rating</span></a>
            <a href="../pages/daftar_driver.php"><span></span> <span>Cari Driver</span></a>
            <a href="../auth/logout.php" style="margin-top:2rem; color:#f87171;"><span></span> <span>Keluar</span></a>
        </nav>
    </aside>

    <nav class="glass-nav">
        <div class="nav-brand">SopirPilihan.id</div>
        <div style="font-size: 0.85rem; font-weight: 600; color: #64748b;">
            Review Center
        </div>
    </nav>

    <main class="main-content">
        <div class="page-header">
            <h1>Riwayat Rating & Ulasan</h1>
            <p>Daftar penilaian yang telah Anda berikan kepada mitra driver</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📝</div>
                <div class="stat-info">
                    <h3><?= $stats['total_rating']; ?></h3>
                    <p>Total Ulasan</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⭐</div>
                <div class="stat-info">
                    <h3><?= $stats['avg_rating'] ? number_format($stats['avg_rating'], 1) : '0'; ?></h3>
                    <p>Rata-rata Skor</p>
                </div>
            </div>
        </div>

        <div class="rating-container">
            <?php if(mysqli_num_rows($result_rating) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result_rating)): 
                    $foto_dr = !empty($row['foto_driver']) ? '../uploads/drivers/profile/'.$row['foto_driver'] : 'https://ui-avatars.com/api/?name='.urlencode($row['nama_driver']);
                    $wa_link = "https://wa.me/" . preg_replace('/[^0-9]/', '', ($row['no_whatsapp'] ?: $row['no_hp']));
                ?>
                    <div class="rating-card">
                        <img src="<?= $foto_dr ?>" class="driver-img" alt="Driver">
                        <div class="rating-body">
                            <div class="rating-top">
                                <span class="driver-name"><?= htmlspecialchars($row['nama_driver']); ?></span>
                                <span class="rating-date"> <?= date('d M Y', strtotime($row['created_at'])); ?></span>
                            </div>
                            <div class="stars">
                                <?php 
                                for($i=1; $i<=5; $i++) {
                                    echo ($i <= $row['rating']) ? '★' : '☆';
                                }
                                ?>
                            </div>
                            <div class="comment">
                                "<?= nl2br(htmlspecialchars($row['komentar'])); ?>"
                            </div>
                            <div class="actions">
                                <a href="<?= $wa_link ?>" target="_blank" class="btn btn-wa">
                                    <span></span> Hubungi Lagi
                                </a>
                                <a href="../pages/detail_driver.php?id=<?= $row['id_driver']; ?>" class="btn btn-profile">
                                    <span></span> Profil Driver
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div style="font-size: 4rem; margin-bottom: 1.5rem; opacity: 0.5;">💬</div>
                    <h3 style="color: #475569; font-weight: 800;">Belum Ada Ulasan</h3>
                    <p style="margin-bottom: 2rem;">Ulasan Anda akan membantu pelanggan lain memilih driver terbaik.</p>
                    <a href="../pages/daftar_driver.php" class="btn" style="background: var(--primary); color: white;">Cari Driver Sekarang</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>