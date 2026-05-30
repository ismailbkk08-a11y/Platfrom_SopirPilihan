<?php
session_start();
require_once '../config/database.php';

// Pastikan fungsi keamanan tersedia
if (!function_exists('clean_input')) {
    function clean_input($data) {
        global $conn;
        return mysqli_real_escape_string($conn, htmlspecialchars(strip_tags(trim($data))));
    }
}

// Cek Role Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$message = '';
$message_type = '';

// 1. UPDATE STATUS
if(isset($_POST['update_status'])) {
    $id_pengiriman = intval($_POST['id_pengiriman']);
    $status = clean_input($_POST['status']);
    
    $query = "UPDATE pengiriman_barang SET status = '$status' WHERE id_pengiriman = $id_pengiriman";
    if(mysqli_query($conn, $query)) {
        $message = "Status pesanan #$id_pengiriman berhasil diupdate menjadi " . ucfirst($status);
        $message_type = 'success';
    }
}

// 2. HAPUS DATA
if(isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM pengiriman_barang WHERE id_pengiriman = $id");
    $message = "Data pengiriman berhasil dihapus!";
    $message_type = 'success';
}

// 3. FILTER & QUERY DATA
$status_filter = isset($_GET['status']) ? clean_input($_GET['status']) : '';
$where = "1=1";
if($status_filter) { $where .= " AND p.status = '$status_filter'"; }

// Query mengambil data pengiriman dan info driver
$query = "SELECT p.*, d.nama_lengkap as nama_driver, d.no_hp as wa_driver 
          FROM pengiriman_barang p
          LEFT JOIN driver d ON p.id_driver = d.id_driver
          WHERE $where
          ORDER BY p.created_at DESC";
$result = mysqli_query($conn, $query);

// 4. STATISTIK
$stats = mysqli_fetch_assoc(mysqli_query($conn, "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'on_process' THEN 1 ELSE 0 END) as on_process,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
    FROM pengiriman_barang"));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengiriman - SopirPilihan.id</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #2563eb;
            --primary-soft: rgba(37, 99, 235, 0.1);
            --dark: #0f172a;
            --bg-light: #f1f5f9;
            --text-main: #1e293b;
            --text-muted: #19191aff;
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
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem;
        }
        .welcome-text h1 { font-size: 1.75rem; font-weight: 800; color: var(--dark); }

        /* Stats Section */
        .stats-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem; margin-bottom: 2.5rem;
        }
        .stat-card {
            background: var(--white); padding: 1.5rem; border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.02); border: 1px solid rgba(0,0,0,0.02);
        }
        .stat-card h3 { font-size: 2rem; font-weight: 800; color: var(--dark); margin-bottom: 4px; }
        .stat-card p { font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; }

        /* Filter Box */
        .filter-card {
            background: var(--white); padding: 1.25rem 2rem; border-radius: 20px;
            margin-bottom: 2rem; display: flex; align-items: center; gap: 1.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.02);
        }
        .filter-card select {
            padding: 10px 16px; border: 1px solid #e2e8f0; border-radius: 12px;
            outline: none; font-weight: 600; color: var(--text-main); cursor: pointer;
        }

        /* Table Card */
        .section-card { 
            background: var(--white); border-radius: 28px; padding: 2rem; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.02); 
        }
        table { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
        th { text-align: left; padding: 1rem; color: var(--text-muted); font-size: 0.7rem; text-transform: uppercase; font-weight: 800; }
        td { padding: 1.2rem 1rem; background: #fafafa; font-size: 0.9rem; vertical-align: middle; }
        td:first-child { border-radius: 12px 0 0 12px; }
        td:last-child { border-radius: 0 12px 12px 0; }
        tr:hover td { background: #f1f5f9; }

        /* Status Badges */
        .badge {
            padding: 6px 12px; border-radius: 10px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase;
        }
        .bg-pending { background: #fffbeb; color: #92400e; }
        .bg-on_process { background: #eff6ff; color: #1e40af; }
        .bg-completed { background: #f0fdf4; color: #166534; }
        .bg-cancelled { background: #fef2f2; color: #991b1b; }

        /* Buttons */
        .status-select {
            padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px;
            font-size: 0.8rem; font-weight: 700; outline: none; margin-bottom: 8px; width: 100%;
        }
        .btn-wa {
            background: #22c55e; color: white; padding: 8px 12px; border-radius: 8px;
            text-decoration: none; font-size: 0.75rem; font-weight: 700; display: flex;
            align-items: center; justify-content: center; gap: 6px; transition: 0.3s;
        }
        .btn-wa:hover { background: #16a34a; transform: translateY(-2px); }
        
        .btn-delete { 
            color: var(--danger); font-size: 1.1rem; text-decoration: none; 
            margin-left: 10px; transition: 0.3s;
        }
        .btn-delete:hover { opacity: 0.7; }

        .alert { padding: 1.2rem; border-radius: 16px; margin-bottom: 2rem; font-weight: 600; border-left: 6px solid; }
        .alert-success { background: #dcfce7; color: #166534; border-color: var(--success); }
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
            <a href="kelola_pengiriman.php"class="active"><span></span> Pengiriman</a>
            <a href="laporan.php"><span></span> Laporan</a>
            
            <div style="margin-top: auto; padding-top: 2rem;">
                <a href="../auth/logout.php" style="color: #ef4444; background: rgba(239, 68, 68, 0.1);"><span></span> Keluar</a>
            </div>
        </nav>
    </aside>

    <main class="main-content">
        <div class="header-wrapper">
            <div class="welcome-text">
                <h1>Logistik & Pengiriman</h1>
                <p>Pantau dan kelola status pengiriman barang pelanggan.</p>
            </div>
            <div style="font-weight: 700; color: var(--text-muted);"><?php echo date('l, d F Y'); ?></div>
        </div>

        <?php if($message): ?>
            <div class="alert alert-success">✅ <?php echo $message; ?></div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card" style="border-bottom: 4px solid var(--primary);">
                <h3><?php echo number_format($stats['total']); ?></h3>
                <p>Total Pesanan</p>
            </div>
            <div class="stat-card" style="border-bottom: 4px solid var(--warning);">
                <h3 style="color: var(--warning);"><?php echo number_format($stats['pending']); ?></h3>
                <p>Pending</p>
            </div>
            <div class="stat-card" style="border-bottom: 4px solid var(--primary);">
                <h3 style="color: var(--primary);"><?php echo number_format($stats['on_process']); ?></h3>
                <p>Proses</p>
            </div>
            <div class="stat-card" style="border-bottom: 4px solid var(--success);">
                <h3 style="color: var(--success);"><?php echo number_format($stats['completed']); ?></h3>
                <p>Selesai</p>
            </div>
        </div>

        <div class="filter-card">
            <span style="font-weight: 800; font-size: 0.9rem; color: var(--dark);">FILTER STATUS</span>
            <form method="GET">
                <select name="status" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>> Pending</option>
                    <option value="on_process" <?php echo $status_filter == 'on_process' ? 'selected' : ''; ?>> On Process</option>
                    <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </form>
        </div>
        

        <section class="section-card">
            <div style="overflow-x: auto;">
                <?php if(mysqli_num_rows($result) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Pengirim / Penerima</th>
                            <th>Detail Barang</th>
                            <th>Rute & Driver</th>
                            <th>Harga</th>
                            <th>Status</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 800; color: var(--primary);">#<?php echo $row['id_pengiriman']; ?></div>
                                <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600;"><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></div>
                            </td>
                            <td>
                                <div style="margin-bottom: 4px;">
                                    <span style="font-weight: 800; color: var(--dark);"> <?php echo htmlspecialchars($row['nama_pengirim']); ?></span>
                                </div>
                                <div>
                                    <span style="font-weight: 800; color: var(--success);"> <?php echo htmlspecialchars($row['nama_penerima']); ?></span>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px; max-width: 200px;">
                                     <?php echo substr(htmlspecialchars($row['alamat_penerima']), 0, 40); ?>...
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 700;"><?php echo htmlspecialchars($row['jenis_barang']); ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);"> <?php echo htmlspecialchars($row['berat']); ?> Kg</div>
                            </td>
                            <td>
                                <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-main);">
                                    <?php echo htmlspecialchars($row['lokasi_penjemputan']); ?> <span style="color: var(--primary);">→</span> <?php echo htmlspecialchars($row['rute_tujuan']); ?>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">
                                     Driver: <span style="color: var(--dark); font-weight: 700;"><?php echo $row['nama_driver'] ?? 'N/A'; ?></span>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 800; color: var(--success);">Rp<?php echo number_format($row['total_harga'], 0, ',', '.'); ?></div>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $row['status']; ?>">
                                    <?php echo str_replace('_', ' ', $row['status']); ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <form method="POST" style="display: inline-block; width: 140px;">
                                    <input type="hidden" name="id_pengiriman" value="<?php echo $row['id_pengiriman']; ?>">
                                    <select name="status" onchange="this.form.submit()" class="status-select">
                                        <option value="">Update Status</option>
                                        <option value="pending">Pending</option>
                                        <option value="on_process">On Process</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>

                                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 8px;">
                                    <a href="?delete=<?php echo $row['id_pengiriman']; ?>" 
                                       class="btn-delete" 
                                       onclick="return confirm('Hapus data pengiriman ini?')">🗑️</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div style="text-align: center; padding: 4rem;">
                        <span style="font-size: 3rem;"></span>
                        <p style="color: var(--text-muted); margin-top: 1rem; font-weight: 600;">Tidak ada data pengiriman ditemukan.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

</body>
</html>