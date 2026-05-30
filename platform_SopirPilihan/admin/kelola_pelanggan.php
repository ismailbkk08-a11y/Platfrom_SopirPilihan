<?php
/**
 * Halaman Kelola Pelanggan - SopirPilihan.id
 * Deskripsi: Manajemen data pengguna dengan role 'pelanggan'
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

// 2. Logika Hapus Pelanggan
if (isset($_GET['hapus'])) {
    $id_user = intval($_GET['hapus']);
    
    mysqli_begin_transaction($conn);
    try {
        mysqli_query($conn, "DELETE FROM pelanggan WHERE id_user = $id_user");
        mysqli_query($conn, "DELETE FROM users WHERE id_user = $id_user");
        
        mysqli_commit($conn);
        $message = "Data pelanggan berhasil dihapus secara permanen.";
        $message_type = "success";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $message = "Gagal menghapus data pelanggan.";
        $message_type = "danger";
    }
}

// 3. Query Ambil Data Pelanggan
$query = "SELECT p.*, u.username, u.created_at as tgl_daftar 
          FROM pelanggan p 
          JOIN users u ON p.id_user = u.id_user 
          WHERE u.role = 'pelanggan'
          ORDER BY u.created_at DESC";
$result = mysqli_query($conn, $query);
$total_pelanggan = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pelanggan - SopirPilihan.id</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-soft: rgba(37, 99, 235, 0.1);
            --dark: #0f172a;
            --bg-light: #f1f5f9;
            --text-main: #1e293b;
            --text-muted: #000000ff;
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

        /* Main Content Styling */
        .main-content { flex: 1; margin-left: var(--sidebar-width); padding: 2rem 3rem; }
        
        .header-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
        }
        .welcome-text h1 { font-size: 1.75rem; font-weight: 800; color: var(--dark); }
        .welcome-text p { color: var(--text-muted); }

        /* Table & Card Section */
        .section-card { 
            background: var(--white); 
            border-radius: 28px; 
            padding: 2rem; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.03); 
            border: 1px solid rgba(0,0,0,0.02); 
        }
        .card-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 2rem; 
        }
        .card-header h2 { font-size: 1.25rem; font-weight: 800; color: var(--dark); }

        .search-container { position: relative; }
        .search-input { 
            padding: 12px 20px; 
            border: 1px solid #e2e8f0; 
            border-radius: 14px; 
            width: 300px; 
            outline: none; 
            transition: 0.3s;
            font-size: 0.9rem;
        }
        .search-input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px var(--primary-soft); }

        table { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
        th { text-align: left; padding: 1rem; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; font-weight: 800; }
        td { padding: 1.2rem 1rem; background: #fafafa; font-size: 0.95rem; vertical-align: middle; }
        td:first-child { border-radius: 12px 0 0 12px; }
        td:last-child { border-radius: 0 12px 12px 0; }
        tr:hover td { background: #f1f5f9; }

        /* Components */
        .user-profile { display: flex; align-items: center; gap: 12px; }
        .avatar { 
            width: 44px; height: 44px; border-radius: 12px; 
            background: var(--primary); color: white; 
            display: flex; align-items: center; justify-content: center; 
            font-weight: 800; font-size: 1.1rem;
        }
        .avatar-img { width: 44px; height: 44px; border-radius: 12px; object-fit: cover; }

        .wa-badge { 
            background: #f0fdf4; color: var(--success); 
            padding: 6px 12px; border-radius: 10px; 
            text-decoration: none; font-weight: 700; font-size: 0.85rem;
            display: inline-flex; align-items: center; gap: 6px;
        }

        .btn-delete { 
            background: #fef2f2; color: var(--danger); 
            padding: 8px 16px; border-radius: 10px; 
            text-decoration: none; font-weight: 700; font-size: 0.85rem;
            transition: 0.3s;
        }
        .btn-delete:hover { background: var(--danger); color: white; }

        .alert { 
            padding: 1.2rem; border-radius: 16px; margin-bottom: 2rem; 
            font-weight: 600; border-left: 6px solid;
        }
        .alert-success { background: #dcfce7; color: #166534; border-color: var(--success); }
        .alert-danger { background: #fee2e2; color: #991b1b; border-color: var(--danger); }
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
            <a href="kelola_pelanggan.php" class="active" ><span></span> Pelanggan</a>
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
                <h1>Data Pelanggan</h1>
                <p>Manajemen akun dan informasi pelanggan aktif.</p>
            </div>
            <div style="background: var(--white); padding: 10px 20px; border-radius: 14px; box-shadow: 0 4px 10px rgba(0,0,0,0.03); font-weight: 800; color: var(--primary);">
                Total: <?= number_format($total_pelanggan); ?>
            </div>
        </div>

        <?php if($message): ?>
            <div class="alert alert-<?= $message_type; ?>"><?= $message; ?></div>
        <?php endif; ?>

        <section class="section-card">
            <div class="card-header">
                <h2>Database Pelanggan</h2>
                <div class="search-container">
                    <input type="text" id="searchInput" class="search-input" placeholder="Cari nama atau email..." onkeyup="filterTable()">
                </div>
            </div>

            <div style="overflow-x: auto;">
                <?php if($total_pelanggan > 0): ?>
                <table id="customerTable">
                    <thead>
                        <tr>
                            <th>Nama Pelanggan</th>
                            <th>Kontak & Email</th>
                            <th>Alamat Domisili</th>
                            <th style="text-align: right;">Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>
                                <div class="user-profile">
                                    <?php 
                                    $path_foto = "../uploads/pelanggan/"; 
                                    $foto = $row['foto_profil'];
                                    if (!empty($foto) && file_exists($path_foto . $foto)): ?>
                                        <img src="<?= $path_foto . $foto; ?>" class="avatar-img" alt="Foto">
                                    <?php else: ?>
                                        <div class="avatar"><?= strtoupper(substr($row['nama_lengkap'], 0, 1)); ?></div>
                                    <?php endif; ?>
                                    <div>
                                        <div style="font-weight: 800; color: var(--dark);"><?= htmlspecialchars($row['nama_lengkap']); ?></div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">@<?= htmlspecialchars($row['username']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="margin-bottom: 6px;">
                                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $row['no_hp']); ?>" target="_blank" class="wa-badge">
                                        <span>📞</span> <?= htmlspecialchars($row['no_hp']); ?>
                                    </a>
                                </div>
                                <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;"><?= htmlspecialchars($row['email']); ?></div>
                            </td>
                            <td style="max-width: 250px;">
                                <div style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.4;">
                                    <?= htmlspecialchars($row['alamat']); ?>
                                </div>
                            </td>
                            <td style="text-align: right;">
                                <a href="?hapus=<?= $row['id_user']; ?>" class="btn-delete" onclick="return confirm('Hapus pelanggan ini secara permanen?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div style="text-align: center; padding: 4rem;">
                        <span style="font-size: 3rem;"></span>
                        <p style="color: var(--text-muted); margin-top: 1rem; font-weight: 600;">Belum ada data pelanggan terdaftar.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script>
    function filterTable() {
        const filter = document.getElementById("searchInput").value.toLowerCase();
        const rows = document.querySelectorAll("#customerTable tbody tr");
        rows.forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
        });
    }
    </script>
</body>
</html>