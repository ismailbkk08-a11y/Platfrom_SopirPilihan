<?php
/**
 * Halaman Kelola Jenis Mobil - SopirPilihan.id
 * Deskripsi: Manajemen kategori kendaraan
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

$message = '';
$message_type = '';

// 1. Logika Tambah Jenis Mobil
if (isset($_POST['tambah'])) {
    $nama_jenis = mysqli_real_escape_string($conn, $_POST['nama_jenis']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $kapasitas = intval($_POST['kapasitas_penumpang']);

    if (!empty($nama_jenis)) {
        $query = "INSERT INTO jenis_mobil (nama_jenis, deskripsi, kapasitas_penumpang) 
                  VALUES ('$nama_jenis', '$deskripsi', $kapasitas)";
        if (mysqli_query($conn, $query)) {
            $message = "Jenis mobil baru berhasil ditambahkan!";
            $message_type = "success";
        } else {
            $message = "Gagal menambahkan data: " . mysqli_error($conn);
            $message_type = "danger";
        }
    }
}

// 2. Logika Hapus Jenis Mobil
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    if (mysqli_query($conn, "DELETE FROM jenis_mobil WHERE id_jenis_mobil = $id")) {
        $message = "Kategori mobil berhasil dihapus.";
        $message_type = "success";
    }
}

// 3. Query Ambil Data
$query = "SELECT * FROM jenis_mobil ORDER BY nama_jenis ASC";
$result = mysqli_query($conn, $query);
$total_jenis = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jenis Mobil - SopirPilihan.id</title>
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

        /* Main Content Styling */
        .main-content { flex: 1; margin-left: var(--sidebar-width); padding: 2rem 3rem; }
        
        .header-wrapper {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem;
        }
        .welcome-text h1 { font-size: 1.75rem; font-weight: 800; color: var(--dark); }

        /* Form Card */
        .form-card { 
            background: var(--white); border-radius: 24px; padding: 2rem; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.02); margin-bottom: 2.5rem; border: 1px solid rgba(0,0,0,0.02);
        }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr 120px 150px; gap: 20px; align-items: end; }
        .form-group label { display: block; font-size: 0.8rem; font-weight: 800; margin-bottom: 8px; color: var(--text-muted); text-transform: uppercase; }
        .input-style { 
            width: 100%; padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 12px; outline: none; transition: 0.3s;
        }
        .input-style:focus { border-color: var(--primary); box-shadow: 0 0 0 4px var(--primary-soft); }

        /* Table Card */
        .section-card { 
            background: var(--white); border-radius: 28px; padding: 2rem; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.02); 
        }
        table { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
        th { text-align: left; padding: 1rem; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; font-weight: 800; }
        td { padding: 1.2rem 1rem; background: #fafafa; font-size: 0.95rem; vertical-align: middle; }
        td:first-child { border-radius: 12px 0 0 12px; }
        td:last-child { border-radius: 0 12px 12px 0; }
        tr:hover td { background: #f1f5f9; }

        .cap-badge { 
            background: #eff6ff; color: var(--primary); padding: 6px 12px; border-radius: 10px; font-weight: 800; font-size: 0.85rem;
        }

        .btn-primary { 
            background: var(--dark); color: white; padding: 12px; border-radius: 12px; 
            border: none; font-weight: 700; cursor: pointer; transition: 0.3s;
        }
        .btn-primary:hover { background: var(--primary); transform: translateY(-2px); }

        .btn-delete { 
            background: #fff1f2; color: var(--danger); padding: 8px 16px; border-radius: 10px; 
            text-decoration: none; font-weight: 700; font-size: 0.85rem; transition: 0.3s;
        }
        .btn-delete:hover { background: var(--danger); color: white; }

        .alert { padding: 1.2rem; border-radius: 16px; margin-bottom: 2rem; font-weight: 600; border-left: 6px solid; }
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
            <a href="dashboard.php" ><span></span> Dashboard</a>
            <a href="kelola_driver.php"><span></span> Kelola Driver</a>
            <a href="verifikasi_driver.php"><span></span> Verifikasi Driver</a>
            <a href="kelola_pelanggan.php"><span></span> Pelanggan</a>
            <a href="jenis_mobil.php"class="active"><span></span> Jenis Mobil</a>
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
                <h1>Jenis Kendaraan</h1>
                <p>Atur kategori mobil yang tersedia di platform.</p>
            </div>
            <div style="background: var(--white); padding: 10px 20px; border-radius: 14px; font-weight: 800; color: var(--primary); box-shadow: 0 4px 10px rgba(0,0,0,0.03);">
                Total: <?= $total_jenis ?> Kategori
            </div>
        </div>

        <?php if($message): ?>
            <div class="alert alert-<?= $message_type; ?>"><?= $message; ?></div>
        <?php endif; ?>

        <div class="form-card">
            <form method="POST" class="form-grid">
                <div class="form-group">
                    <label>Nama Kategori</label>
                    <input type="text" name="nama_jenis" class="input-style" placeholder="Contoh: MPV Luas" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi Singkat</label>
                    <input type="text" name="deskripsi" class="input-style" placeholder="Keterangan kategori...">
                </div>
                <div class="form-group">
                    <label>Kapasitas</label>
                    <input type="number" name="kapasitas_penumpang" class="input-style" value="4" required>
                </div>
                <button type="submit" name="tambah" class="btn btn-primary"> Tambah</button>
            </form>
        </div>

        <section class="section-card">
            <div style="margin-bottom: 1.5rem;">
                <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--dark);">Database Kategori</h2>
            </div>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Kategori Mobil</th>
                            <th>Deskripsi Pelayanan</th>
                            <th>Kapasitas</th>
                            <th style="text-align: right;">Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($total_jenis > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 800; color: var(--primary); font-size: 1rem;">
                                         <?= htmlspecialchars($row['nama_jenis']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="color: var(--text-muted); font-size: 0.85rem; font-weight: 500;">
                                        <?= htmlspecialchars($row['deskripsi']); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="cap-badge">
                                         <?= htmlspecialchars($row['kapasitas_penumpang']); ?> Orang
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <a href="?hapus=<?= $row['id_jenis_mobil']; ?>" class="btn-delete" onclick="return confirm('Hapus kategori ini?')">Hapus</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                                    Belum ada data jenis mobil.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>