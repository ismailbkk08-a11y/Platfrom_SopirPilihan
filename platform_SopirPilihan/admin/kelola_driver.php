<?php
/**
 * Halaman Kelola Driver - SopirPilihan.id
 * Deskripsi: Mengelola data driver yang sudah diverifikasi (status 'verified')
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

// 2. Logika Hapus Driver
if (isset($_GET['hapus'])) {
    $id_user = mysqli_real_escape_string($conn, $_GET['hapus']);
    
    mysqli_begin_transaction($conn);
    try {
        mysqli_query($conn, "DELETE FROM driver WHERE id_user = '$id_user'");
        mysqli_query($conn, "DELETE FROM users WHERE id_user = '$id_user'");
        
        mysqli_commit($conn);
        $message = "Data driver dan akun berhasil dihapus secara permanen.";
        $message_type = "success";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $message = "Gagal menghapus data driver.";
        $message_type = "danger";
    }
}

// 3. Query Utama: Mengambil Driver Verified
$query = "SELECT d.*, u.username, u.status 
          FROM driver d 
          JOIN users u ON d.id_user = u.id_user
          WHERE d.status_verifikasi = 'verified'
          ORDER BY d.nama_lengkap ASC";
$result = mysqli_query($conn, $query);

// 4. Statistik
$total_driver = mysqli_num_rows($result);

$query_aktif = "SELECT COUNT(*) as total FROM driver d 
                JOIN users u ON d.id_user = u.id_user 
                WHERE u.status = 'active' AND d.status_verifikasi = 'verified'";
$driver_aktif = mysqli_fetch_assoc(mysqli_query($conn, $query_aktif))['total'];

$query_nonaktif = "SELECT COUNT(*) as total FROM driver d 
                   JOIN users u ON d.id_user = u.id_user 
                   WHERE u.status = 'nonactive' AND d.status_verifikasi = 'verified'";
$driver_nonaktif = mysqli_fetch_assoc(mysqli_query($conn, $query_nonaktif))['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Driver - SopirPilihan.id</title>
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
        .welcome-text h1 { font-size: 1.75rem; font-weight: 800; color: var(--dark); }
        .welcome-text p { color: var(--text-muted); }

        .btn-add {
            background: var(--primary);
            color: white;
            padding: 12px 24px;
            border-radius: 14px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9rem;
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2);
            transition: 0.3s;
        }
        .btn-add:hover { transform: translateY(-3px); box-shadow: 0 15px 25px rgba(37, 99, 235, 0.3); }

        /* Stats Grid (Sesuai Dashboard) */
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 2.5rem; }
        .stat-card { 
            background: var(--white); padding: 1.5rem; border-radius: 24px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.02);
            transition: 0.3s; border: 1px solid rgba(0,0,0,0.02);
        }
        .stat-card:hover { transform: translateY(-5px); }
        .icon-box { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; font-size: 1.5rem; }
        .stat-card h3 { font-size: 2rem; font-weight: 800; color: var(--dark); }
        .stat-card p { font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }

        /* Colors for icon boxes */
        .bg-p { background: var(--primary-soft); color: var(--primary); }
        .bg-s { background: #f0fdf4; color: var(--success); }
        .bg-w { background: #fff7ed; color: var(--warning); }

        /* Section Card & Table */
        .section-card { 
            background: var(--white); border-radius: 28px; padding: 2rem; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.02); 
        }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .card-header h2 { font-size: 1.25rem; font-weight: 800; }

        .search-box input {
            padding: 12px 20px;
            border-radius: 14px;
            border: 1.5px solid #e2e8f0;
            width: 300px;
            font-size: 0.9rem;
            outline: none;
            transition: 0.3s;
        }
        .search-box input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px var(--primary-soft); }

        table { width: 100%; border-collapse: separate; border-spacing: 0 10px; }
        th { text-align: left; padding: 1rem; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; font-weight: 800; }
        td { padding: 1.2rem 1rem; background: #fafafa; font-size: 0.9rem; vertical-align: middle; }
        td:first-child { border-radius: 16px 0 0 16px; }
        td:last-child { border-radius: 0 16px 16px 0; }
        tr:hover td { background: #f1f5f9; }

        /* Profile Cell */
        .driver-info { display: flex; align-items: center; gap: 15px; }
        .driver-info img { width: 45px; height: 45px; border-radius: 12px; object-fit: cover; border: 2px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .driver-name { font-weight: 700; color: var(--dark); display: block; }
        .driver-user { font-size: 0.8rem; color: var(--text-muted); }

        /* Badges */
        .badge { padding: 6px 14px; border-radius: 10px; font-size: 0.75rem; font-weight: 800; }
        .badge-active { background: #dcfce7; color: #166534; }
        .badge-nonactive { background: #fee2e2; color: #991b1b; }

        /* Action Buttons */
        .btn-group { display: flex; gap: 8px; justify-content: flex-end; }
        .btn-action { 
            padding: 8px 16px; border-radius: 10px; text-decoration: none; 
            font-size: 0.8rem; font-weight: 700; transition: 0.3s;
        }
        .btn-detail { background: var(--bg-light); color: var(--dark); }
        .btn-delete { background: #fff1f2; color: var(--danger); }
        .btn-action:hover { transform: scale(1.05); }

        /* Alert */
        .alert { 
            padding: 1rem 1.5rem; border-radius: 16px; margin-bottom: 2rem; 
            font-weight: 600; font-size: 0.95rem; border-left: 5px solid;
        }
        .alert-success { background: #f0fdf4; color: #166534; border-left-color: var(--success); }
        .alert-danger { background: #fef2f2; color: #991b1b; border-left-color: var(--danger); }

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
            <a href="dashboard.php" ><span></span> Dashboard</a>
            <a href="kelola_driver.php"class="active"><span></span> Kelola Driver</a>
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
                <h1>Manajemen Mitra Driver</h1>
                <p>Kelola data dan status pengemudi yang telah terverifikasi.</p>
            </div>
            <a href="verifikasi_driver.php" class="btn-add">Verifikasi Driver Baru</a>
        </div>

        <?php if($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo ($message_type == 'success' ? '✅ ' : '❌ ') . $message; ?>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon-box bg-p">👥</div>
                <h3><?php echo number_format($total_driver); ?></h3>
                <p>Total Mitra</p>
            </div>
            <div class="stat-card">
                <div class="icon-box bg-s">🟢</div>
                <h3 style="color: var(--success);"><?php echo number_format($driver_aktif); ?></h3>
                <p>Status Aktif</p>
            </div>
            <div class="stat-card">
                <div class="icon-box bg-w">🔴</div>
                <h3 style="color: var(--warning);"><?php echo number_format($driver_nonaktif); ?></h3>
                <p>Status Non-Aktif</p>
            </div>
        </div>

        <section class="section-card">
            <div class="card-header">
                <h2>Daftar Seluruh Mitra</h2>
                <div class="search-box">
                    <input type="text" id="driverSearch" placeholder="Cari nama atau username..." onkeyup="filterTable()">
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table id="driverTable">
                    <thead>
                        <tr>
                            <th>Identitas Mitra</th>
                            <th>Kontak & Lisensi</th>
                            <th>Status Akun</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>
                                    <div class="driver-info">
                                        <img src="../uploads/drivers/profile/<?php echo !empty($row['foto_profil']) ? $row['foto_profil'] : 'default.png'; ?>" alt="">
                                        <div>
                                            <span class="driver-name"><?php echo htmlspecialchars($row['nama_lengkap']); ?></span>
                                            <span class="driver-user">@<?php echo htmlspecialchars($row['username']); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--dark);"> <?php echo htmlspecialchars($row['no_whatsapp']); ?></div>
                                    <div style="color: var(--primary); font-size: 0.75rem; font-weight: 800; margin-top: 4px; text-transform: uppercase;"> SIM <?php echo $row['jenis_sim'] ?? 'A'; ?></div>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $row['status']; ?>">
                                        <?php echo strtoupper($row['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="detail_driver.php?id=<?php echo $row['id_driver']; ?>" class="btn-action btn-detail">Detail</a>
                                        <a href="?hapus=<?php echo $row['id_user']; ?>" class="btn-action btn-delete" onclick="return confirm('Hapus mitra ini secara permanen?')">Hapus</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 4rem;">
                                    <div style="font-size: 3rem; margin-bottom: 1rem;">🍃</div>
                                    <p style="color: var(--text-muted); font-weight: 600;">Belum ada mitra driver yang terverifikasi.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script>
    function filterTable() {
        const input = document.getElementById("driverSearch");
        const filter = input.value.toLowerCase();
        const table = document.getElementById("driverTable");
        const tr = table.getElementsByTagName("tr");

        for (let i = 1; i < tr.length; i++) {
            const rowText = tr[i].innerText.toLowerCase();
            tr[i].style.display = rowText.includes(filter) ? "" : "none";
        }
    }
    </script>
</body>
</html>