<?php
session_start();
require_once '../config/database.php';

// Proteksi Session Driver
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    header("Location: ../auth/login.php");
    exit;
}

$id_driver = $_SESSION['profile_id'];

// Ambil data driver
// Ambil data driver
// u.status diambil dari tabel users, sisanya (termasuk email) diambil dari tabel driver (d)
$query = "SELECT d.*, u.status FROM driver d 
          JOIN users u ON d.id_user = u.id_user
          WHERE d.id_driver = $id_driver";
          
$result = mysqli_query($conn, $query);
if (!$result) {
    die("Query Error: " . mysqli_error($conn));
}
$driver = mysqli_fetch_assoc($result);

// Statistik driver
$stats = [];
$query_m = "SELECT COUNT(*) as total FROM mobil_driver WHERE id_driver = $id_driver";
$stats['total_mobil'] = mysqli_fetch_assoc(mysqli_query($conn, $query_m))['total'];

$query_r = "SELECT COUNT(*) as total FROM rute_driver WHERE id_driver = $id_driver";
$stats['total_rute'] = mysqli_fetch_assoc(mysqli_query($conn, $query_r))['total'];

$query_rt = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_rating FROM rating_driver WHERE id_driver = $id_driver";
$rating_res = mysqli_fetch_assoc(mysqli_query($conn, $query_rt));
$stats['avg_rating'] = $rating_res['avg_rating'] ? number_format($rating_res['avg_rating'], 1) : '0.0';
$stats['total_rating'] = $rating_res['total_rating'];

// Rating terbaru
$query_rev = "SELECT r.*, p.nama_lengkap 
              FROM rating_driver r
              JOIN pelanggan p ON r.id_pelanggan = p.id_pelanggan
              WHERE r.id_driver = $id_driver
              ORDER BY r.created_at DESC
              LIMIT 5";
$result_rating = mysqli_query($conn, $query_rev);

// Logic Foto Profil
$foto_path = !empty($driver['foto_profil']) 
    ? '../uploads/drivers/profile/' . $driver['foto_profil'] 
    : 'https://ui-avatars.com/api/?name=' . urlencode($driver['nama_lengkap']) . '&background=0f172a&color=fff';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard - SopirPilihan.id</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #2563eb;
            --dark: #0f172a;
            --bg-light: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-main);
            display: flex;
            min-height: 100vh;
        }

        /* --- GLASSMORPHISM NAVBAR --- */
        .glass-nav {
            position: fixed;
            top: 0; right: 0; left: 280px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            z-index: 90;
            padding: 0.85rem 2.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        }

        /* --- SIDEBAR --- */
        .sidebar {
            width: 280px;
            background: var(--dark);
            color: white;
            height: 100vh;
            position: fixed;
            z-index: 100;
            display: flex;
            flex-direction: column;
        }
        .sidebar-header {
            padding: 2.5rem 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .sidebar-header img {
            width: 70px; height: 70px;
            border-radius: 20px;
            border: 2px solid var(--primary);
            margin-bottom: 1rem;
            object-fit: cover;
        }
        .sidebar-menu { list-style: none; padding: 1.5rem 0; flex-grow: 1; }
        .sidebar-menu a {
            color: #94a3b8;
            text-decoration: none;
            padding: 0.9rem 1.75rem;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            transition: 0.3s;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            color: white;
            background: rgba(255,255,255,0.05);
            border-left: 4px solid var(--primary);
        }

        /* --- MAIN CONTENT --- */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 6.5rem 2.5rem 2rem;
        }

        .header-box { margin-bottom: 2.5rem; }
        .header-box h1 { font-size: 1.75rem; font-weight: 800; letter-spacing: -0.5px; }

        /* Status Badges */
        .badge {
            padding: 0.4rem 1rem;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-info { background: #dbeafe; color: #1e40af; }
        .alert {
            padding: 1.25rem;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            font-size: 0.9rem;
            border: 1px solid transparent;
        }
        .alert-warning { background: #fffbeb; color: #92400e; border-color: #fef3c7; }
        .alert-success { background: #f0fdf4; color: #166534; border-color: #dcfce7; }
        .alert-danger { background: #fef2f2; color: #991b1b; border-color: #fee2e2; }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }
        .stat-card {
            background: white;
            padding: 1.75rem;
            border-radius: 24px;
            border: 1px solid rgba(0,0,0,0.03);
            box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        }
        .stat-card h3 { font-size: 1.8rem; font-weight: 800; margin-bottom: 0.25rem; }
        .stat-card p { font-size: 0.8rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; }

        /* Info & Review Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        .card {
            background: white;
            padding: 2rem;
            border-radius: 24px;
            box-shadow: 0 4px 25px rgba(0,0,0,0.02);
        }
        .card-title { font-size: 1.1rem; font-weight: 700; margin-bottom: 1.5rem; }

        /* Review Item */
        .review-item {
            padding-bottom: 1rem;
            margin-bottom: 1rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .review-item:last-child { border: none; }

        .btn-edit {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.6rem 1.2rem;
            background: var(--bg-light);
            color: var(--text-main);
            text-decoration: none;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-edit:hover { background: #e2e8f0; }

        @media (max-width: 1024px) {
            .sidebar { width: 80px; }
            .sidebar span, .sidebar h3 { display: none; }
            .main-content, .glass-nav { left: 80px; }
            .content-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="<?= $foto_path ?>" alt="Profile">
            <h3 style="font-size: 1rem; font-weight: 700;">Driver Panel</h3>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="active"><span></span> <span>Dashboard</span></a>
            <a href="profil.php"><span></span> <span>Profil Saya</span></a>
            <a href="mobil.php"><span></span> <span>Mobil Saya</span></a>
            <a href="rute.php"><span></span> <span>Rute Layanan</span></a>
            <a href="rating.php"><span></span> <span>Rating & Ulasan</span></a>
            <a href="../auth/logout.php" style="margin-top: 2rem; color: #f87171;"><span></span> <span>Keluar</span></a>
        </nav>
    </aside>

    <nav class="glass-nav">
        <div style="font-weight: 800; color: var(--primary); font-size: 1.2rem;">SopirPilihan<span style="color: var(--dark);">.id</span></div>
        <div class="badge badge-info">Status: <?= ucfirst($driver['status']); ?></div>
    </nav>

    <main class="main-content">
        <div class="header-box">
            <h1>Dashboard Overview</h1>
            <p style="color: var(--text-muted); margin-top: 0.4rem;">
                Selamat datang kembali, <span style="color: var(--dark); font-weight: 700;"><?= htmlspecialchars($driver['nama_lengkap']); ?></span>!
            </p>
        </div>

        <?php if($driver['status_verifikasi'] == 'pending'): ?>
            <div class="alert alert-warning">
                <span style="font-size: 1.5rem;"></span>
                <div>
                    <strong>Akun Menunggu Verifikasi</strong><br>
                    Profil Anda belum muncul di pencarian publik hingga tim kami selesai memvalidasi dokumen Anda.
                </div>
            </div>
        <?php elseif($driver['status_verifikasi'] == 'verified'): ?>
            <div class="alert alert-success">
                <span style="font-size: 1.5rem;"></span>
                <div><strong>Akun Terverifikasi!</strong> Profil Anda aktif dan dapat ditemukan oleh calon pelanggan.</div>
            </div>
        <?php elseif($driver['status_verifikasi'] == 'rejected'): ?>
            <div class="alert alert-danger">
                <span style="font-size: 1.5rem;"></span>
                <div><strong>Verifikasi Ditolak.</strong> Silakan periksa kembali profil Anda atau hubungi admin.</div>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <p>Armada Mobil</p>
                <h3><?= $stats['total_mobil']; ?></h3>
            </div>
            <div class="stat-card">
                <p>Rute Aktif</p>
                <h3><?= $stats['total_rute']; ?></h3>
            </div>
            <div class="stat-card">
                <p>Rating Driver</p>
                <h3 style="color: #f59e0b;">⭐ <?= $stats['avg_rating']; ?></h3>
            </div>
            <div class="stat-card">
                <p>Total Ulasan</p>
                <h3><?= $stats['total_rating']; ?></h3>
            </div>
        </div>

        <div class="content-grid">
            <div class="card">
<div class="card-title">Informasi Profil</div>
    <div style="display: flex; gap: 1.5rem; align-items: center; margin-bottom: 1.5rem;">
        <img src="<?= $foto_path ?>" style="width: 90px; height: 90px; border-radius: 20px; object-fit: cover;">
        <div>
            <h4 style="font-size: 1.1rem; margin-bottom: 0.3rem;"><?= htmlspecialchars($driver['nama_lengkap']); ?></h4>
            <p style="font-size: 0.85rem; color: var(--text-muted);"> <?= htmlspecialchars($driver['email'] ?? 'Email tidak tersedia'); ?></p>
            <p style="font-size: 0.85rem; color: var(--text-muted);"> <?= htmlspecialchars($driver['no_whatsapp']); ?></p>
            <a href="profil.php" class="btn-edit">Edit Profil</a>
        </div>
                </div>
                <div style="background: var(--bg-light); padding: 1.25rem; border-radius: 16px; font-size: 0.9rem; color: #475569; font-style: italic;">
                    "<?= !empty($driver['deskripsi']) ? htmlspecialchars(substr($driver['deskripsi'], 0, 150)).'...' : 'Belum ada deskripsi profil untuk menarik pelanggan.'; ?>"
                </div>
            </div>

            <div class="card">
                <div class="card-title">Ulasan Terbaru</div>
                <?php if(mysqli_num_rows($result_rating) > 0): ?>
                    <?php while($review = mysqli_fetch_assoc($result_rating)): ?>
                        <div class="review-item">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.4rem;">
                                <strong style="font-size: 0.9rem;"><?= htmlspecialchars($review['nama_lengkap']); ?></strong>
                                <span style="font-size: 0.75rem; color: var(--text-muted);"><?= date('d M Y', strtotime($review['created_at'])); ?></span>
                            </div>
                            <div style="color: #f59e0b; font-size: 0.8rem; margin-bottom: 0.4rem;">
                                <?= str_repeat('★', $review['rating']) . str_repeat('☆', 5-$review['rating']); ?>
                            </div>
                            <p style="font-size: 0.85rem; color: #475569; line-height: 1.5;">
                                "<?= htmlspecialchars($review['komentar']); ?>"
                            </p>
                        </div>
                    <?php endwhile; ?>
                    <a href="rating.php" style="color: var(--primary); font-weight: 700; font-size: 0.85rem; text-decoration: none;">Lihat Semua Ulasan →</a>
                <?php else: ?>
                    <div style="text-align: center; padding: 2rem 0;">
                        <p style="color: var(--text-muted); font-size: 0.9rem;">Belum ada ulasan dari pelanggan.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

</body>
</html>