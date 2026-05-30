<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Proteksi Halaman
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    header("Location: ../auth/login.php");
    exit;
}

$id_driver = $_SESSION['profile_id'];

// Ambil data driver untuk sidebar
$driver_res = mysqli_query($conn, "SELECT nama_lengkap, foto_profil FROM driver WHERE id_driver = $id_driver");
$driver = mysqli_fetch_assoc($driver_res);
$foto_path = !empty($driver['foto_profil']) ? '../uploads/drivers/profile/'.$driver['foto_profil'] : 'https://ui-avatars.com/api/?name='.urlencode($driver['nama_lengkap']);

// Statistik rating
$rating_stats = get_driver_rating($id_driver);
$avg_rating = $rating_stats['avg_rating'] ? number_format($rating_stats['avg_rating'], 1) : '0.0';
$total_rating = $rating_stats['total_rating'];

// Rating breakdown
$query_breakdown = "SELECT rating, COUNT(*) as jumlah 
                    FROM rating_driver 
                    WHERE id_driver = $id_driver 
                    GROUP BY rating 
                    ORDER BY rating DESC";
$result_breakdown = mysqli_query($conn, $query_breakdown);

$breakdown = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
while($row = mysqli_fetch_assoc($result_breakdown)) {
    $breakdown[$row['rating']] = $row['jumlah'];
}

// Ambil semua ulasan
$query_rating = "SELECT r.*, p.nama_lengkap, p.foto_profil 
                 FROM rating_driver r
                 JOIN pelanggan p ON r.id_pelanggan = p.id_pelanggan
                 WHERE r.id_driver = $id_driver
                 ORDER BY r.created_at DESC";
$result_rating = mysqli_query($conn, $query_rating);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rating & Ulasan - SopirPilihan.id</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --dark: #0f172a;
            --bg-light: #f8fafc;
            --text-muted: #64748b;
            --glass: rgba(255, 255, 255, 0.75);
            --star: #fbbf24;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-light); display: flex; min-height: 100vh; color: var(--dark); }

        /* Sidebar Styling (Konsisten) */
        .sidebar { width: 280px; background: var(--dark); color: white; height: 100vh; position: fixed; z-index: 100; display: flex; flex-direction: column; }
        .sidebar-header { padding: 2.5rem 1.5rem; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .sidebar-header img { width: 65px; height: 65px; border-radius: 18px; border: 2px solid var(--primary); margin-bottom: 1rem; object-fit: cover; }
        .sidebar-menu { list-style: none; padding: 1.5rem 0; flex-grow: 1; }
        .sidebar-menu a { color: #94a3b8; text-decoration: none; padding: 0.9rem 1.75rem; display: flex; align-items: center; gap: 12px; font-weight: 500; transition: 0.3s; }
        .sidebar-menu a.active { color: white; background: rgba(255,255,255,0.05); border-left: 4px solid var(--primary); }

        /* Navigation Glass */
        .glass-nav { position: fixed; top: 0; right: 0; left: 280px; background: var(--glass); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(255,255,255,0.3); z-index: 90; padding: 0.85rem 2.5rem; display: flex; justify-content: space-between; align-items: center; }

        .main-content { flex: 1; margin-left: 280px; padding: 6.5rem 2.5rem 3rem; }

        /* Summary Card */
        .summary-grid { display: grid; grid-template-columns: 300px 1fr; gap: 2rem; background: white; padding: 2.5rem; border-radius: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); margin-bottom: 2.5rem; border: 1px solid rgba(0,0,0,0.04); }
        .score-box { text-align: center; padding-right: 2rem; border-right: 1px solid #f1f5f9; }
        .big-number { font-size: 5rem; font-weight: 800; color: var(--dark); line-height: 1; }
        .star-icons { color: var(--star); font-size: 1.5rem; margin: 10px 0; }
        
        /* Progress Bars */
        .breakdown-row { display: flex; align-items: center; gap: 1rem; margin-bottom: 10px; }
        .star-label { font-size: 0.85rem; font-weight: 600; color: var(--text-muted); width: 60px; }
        .bar-bg { flex: 1; height: 10px; background: #f1f5f9; border-radius: 20px; overflow: hidden; }
        .bar-fill { height: 100%; background: var(--star); border-radius: 20px; }
        .bar-count { font-size: 0.85rem; font-weight: 700; width: 30px; text-align: right; }

        /* Review List */
        .review-card { background: white; border-radius: 20px; padding: 1.5rem; margin-bottom: 1.5rem; border: 1px solid rgba(0,0,0,0.03); display: flex; gap: 1.5rem; }
        .cust-photo { width: 50px; height: 50px; border-radius: 14px; object-fit: cover; }
        .review-body { flex: 1; }
        .review-meta { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .comment-box { background: var(--bg-light); padding: 1rem; border-radius: 14px; margin-top: 10px; font-size: 0.95rem; color: #475569; line-height: 1.5; }

        @media (max-width: 1024px) { 
            .main-content, .glass-nav { left: 0; margin-left: 0; }
            .sidebar { display: none; }
            .summary-grid { grid-template-columns: 1fr; }
            .score-box { border-right: none; border-bottom: 1px solid #f1f5f9; padding-right: 0; padding-bottom: 2rem; }
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
            <a href="dashboard.php"><span></span> <span>Dashboard</span></a>
            <a href="profil.php"><span></span> <span>Profil Saya</span></a>
            <a href="mobil.php"><span></span> <span>Mobil Saya</span></a>
            <a href="rute.php"><span></span> <span>Rute Layanan</span></a>
            <a href="rating.php" class="active"><span></span> <span>Rating & Ulasan</span></a>
            <a href="../auth/logout.php" style="margin-top: 4rem; color: #f87171;"><span></span> <span>Keluar</span></a>
        </nav>
    </aside>

    <nav class="glass-nav">
        <div style="font-weight: 800; color: var(--primary); font-size: 1.2rem;">SopirPilihan<span style="color: var(--dark);">.id</span></div>
        <div style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">Nilai Anda: <span style="color: var(--primary); font-weight: 800;">★ <?= $avg_rating ?></span></div>
    </nav>

    <main class="main-content">
        <div style="margin-bottom: 2rem;">
            <h1 style="font-size: 1.75rem; font-weight: 800;">Ulasan Pelanggan</h1>
            <p style="color: var(--text-muted);">Lihat apa yang pelanggan katakan tentang layanan Anda.</p>
        </div>

        <div class="summary-grid">
            <div class="score-box">
                <div class="big-number"><?= $avg_rating ?></div>
                <div class="star-icons">
                    <?php 
                    $rounded = round((float)$avg_rating);
                    for($i = 1; $i <= 5; $i++) echo $i <= $rounded ? '★' : '☆';
                    ?>
                </div>
                <p style="font-weight: 600; color: var(--text-muted); font-size: 0.9rem;">Total <?= $total_rating ?> Ulasan</p>
            </div>
            
            <div class="breakdown-list">
                <?php foreach([5,4,3,2,1] as $star): 
                    $pct = $total_rating > 0 ? ($breakdown[$star] / $total_rating * 100) : 0;
                ?>
                <div class="breakdown-row">
                    <span class="star-label"><?= $star ?> Bintang</span>
                    <div class="bar-bg">
                        <div class="bar-fill" style="width: <?= $pct ?>%"></div>
                    </div>
                    <span class="bar-count"><?= $breakdown[$star] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <h3 style="margin-bottom: 1.5rem; font-weight: 800; display: flex; align-items: center; gap: 10px;">
            <span style="background: var(--primary); width: 8px; height: 25px; border-radius: 4px; display: inline-block;"></span>
            Ulasan Terbaru
        </h3>

        <?php if(mysqli_num_rows($result_rating) > 0): ?>
            <?php while($review = mysqli_fetch_assoc($result_rating)): 
                $p_photo = !empty($review['foto_profil']) 
                           ? '../uploads/pelanggan/' . $review['foto_profil'] 
                           : 'https://ui-avatars.com/api/?name=' . urlencode($review['nama_lengkap']) . '&background=random';
            ?>
            <div class="review-card">
                <img src="<?= $p_photo ?>" class="cust-photo" alt="Avatar">
                <div class="review-body">
                    <div class="review-meta">
                        <strong style="font-size: 1.05rem;"><?= htmlspecialchars($review['nama_lengkap']) ?></strong>
                        <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">
                            <?= date('d M Y', strtotime($review['created_at'])) ?>
                        </span>
                    </div>
                    <div style="color: var(--star); font-size: 0.9rem; margin-bottom: 5px;">
                        <?php for($i = 1; $i <= 5; $i++) echo $i <= $review['rating'] ? '★' : '☆'; ?>
                    </div>
                    <div class="comment-box">
                        <?= nl2br(htmlspecialchars($review['komentar'])) ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 4rem; background: white; border-radius: 24px; border: 1px dashed #cbd5e1;">
                <p style="font-size: 1.1rem; color: var(--text-muted); font-weight: 500;">Belum ada ulasan yang masuk saat ini.</p>
            </div>
        <?php endif; ?>
    </main>

</body>
</html>