<?php
session_start();
require_once '../config/database.php';

// Proteksi halaman
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'driver') {
    header('Location: ../auth/login.php');
    exit;
}

$id_driver = $_SESSION['profile_id'];
$message = '';
$message_type = 'success';

// 1. PROSES UPDATE DATA (POST) - Tetap menggunakan logika Anda
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $email        = mysqli_real_escape_string($conn, $_POST['email']);
    $no_hp        = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $no_whatsapp  = mysqli_real_escape_string($conn, $_POST['no_whatsapp']);
    $alamat       = mysqli_real_escape_string($conn, $_POST['alamat']);
    $rute_asal    = mysqli_real_escape_string($conn, $_POST['rute_asal']);
    $rute_tujuan  = mysqli_real_escape_string($conn, $_POST['rute_tujuan']);
    $deskripsi    = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    
    $no_sim          = mysqli_real_escape_string($conn, $_POST['no_sim']);
    $jenis_sim       = mysqli_real_escape_string($conn, $_POST['jenis_sim']);
    $tanggal_berlaku = $_POST['tanggal_berlaku'];

    mysqli_begin_transaction($conn);

    try {
        // Handle Foto Profil
        $foto_profil_sql = "";
        if(isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == 0) {
            $ext = strtolower(pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION));
            $file_profil = "prof_" . uniqid() . '.' . $ext;
            if(move_uploaded_file($_FILES['foto_profil']['tmp_name'], '../uploads/drivers/profile/' . $file_profil)) {
                $foto_profil_sql = ", foto_profil = '$file_profil'";
            }
        }

        // Handle Foto KTP
        $foto_ktp_sql = "";
        if(isset($_FILES['foto_ktp']) && $_FILES['foto_ktp']['error'] == 0) {
            $ext = strtolower(pathinfo($_FILES['foto_ktp']['name'], PATHINFO_EXTENSION));
            $file_ktp = "ktp_" . uniqid() . '.' . $ext;
            if(move_uploaded_file($_FILES['foto_ktp']['tmp_name'], '../uploads/drivers/ktp/' . $file_ktp)) {
                $foto_ktp_sql = ", foto_ktp = '$file_ktp'";
            }
        }

        // Handle Foto SIM
        $foto_sim_sql = "";
        if(isset($_FILES['foto_sim']) && $_FILES['foto_sim']['error'] == 0) {
            $ext = strtolower(pathinfo($_FILES['foto_sim']['name'], PATHINFO_EXTENSION));
            $file_sim = "sim_" . uniqid() . '.' . $ext;
            if(move_uploaded_file($_FILES['foto_sim']['tmp_name'], '../uploads/drivers/sim/' . $file_sim)) {
                $foto_sim_sql = ", foto_sim = '$file_sim'";
            }
        }

        $sql_driver = "UPDATE driver SET 
                        nama_lengkap = '$nama_lengkap', 
                        email = '$email', 
                        no_hp = '$no_hp', 
                        no_whatsapp = '$no_whatsapp', 
                        alamat = '$alamat', 
                        rute_asal = '$rute_asal',
                        rute_tujuan = '$rute_tujuan',
                        deskripsi = '$deskripsi' 
                        $foto_profil_sql 
                        $foto_ktp_sql 
                       WHERE id_driver = $id_driver";
        mysqli_query($conn, $sql_driver);

        $check_sim = mysqli_query($conn, "SELECT id_driver FROM sim_driver WHERE id_driver = $id_driver");
        if(mysqli_num_rows($check_sim) > 0) {
            $sql_sim = "UPDATE sim_driver SET 
                        no_sim = '$no_sim', jenis_sim = '$jenis_sim', 
                        tanggal_berlaku = '$tanggal_berlaku' 
                        $foto_sim_sql 
                        WHERE id_driver = $id_driver";
        } else {
            $f_sim = str_replace(", foto_sim = ", "", $foto_sim_sql);
            $f_sim_db = $f_sim ? $f_sim : "''";
            $sql_sim = "INSERT INTO sim_driver (id_driver, no_sim, jenis_sim, tanggal_berlaku, foto_sim) 
                        VALUES ($id_driver, '$no_sim', '$jenis_sim', '$tanggal_berlaku', $f_sim_db)";
        }
        mysqli_query($conn, $sql_sim);

        mysqli_commit($conn);
        $message = "Profil dan dokumen berhasil diperbarui!";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $message = "Terjadi kesalahan: " . $e->getMessage();
        $message_type = 'danger';
    }
}

// 2. AMBIL DATA TERBARU
$query_driver = "SELECT d.*, u.username, s.no_sim, s.jenis_sim, s.tanggal_berlaku, s.foto_sim 
                 FROM driver d
                 JOIN users u ON d.id_user = u.id_user
                 LEFT JOIN sim_driver s ON d.id_driver = s.id_driver
                 WHERE d.id_driver = $id_driver";
$driver = mysqli_fetch_assoc(mysqli_query($conn, $query_driver));

// Dynamic Avatar Logic
$foto_path = !empty($driver['foto_profil']) 
    ? '../uploads/drivers/profile/' . $driver['foto_profil'] 
    : 'https://ui-avatars.com/api/?name=' . urlencode($driver['nama_lengkap']) . '&background=2563eb&color=fff';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - SopirPilihan.id</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --dark: #0f172a;
            --bg-light: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --glass: rgba(255, 255, 255, 0.75);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-light); color: var(--text-main); display: flex; min-height: 100vh; }

        /* Sidebar Glass Style */
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
        .sidebar-header { padding: 2.5rem 1.5rem; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .sidebar-header img { width: 65px; height: 65px; border-radius: 18px; border: 2px solid var(--primary); margin-bottom: 1rem; object-fit: cover; }
        .sidebar-menu { list-style: none; padding: 1.5rem 0; flex-grow: 1; }
        .sidebar-menu a {
            color: #94a3b8; text-decoration: none; padding: 0.9rem 1.75rem; display: flex; align-items: center; gap: 12px; font-weight: 500; transition: 0.3s;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active { color: white; background: rgba(255,255,255,0.05); border-left: 4px solid var(--primary); }

        /* Navigation Glass */
        .glass-nav {
            position: fixed; top: 0; right: 0; left: 280px;
            background: var(--glass); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255,255,255,0.3); z-index: 90;
            padding: 0.85rem 2.5rem; display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
        }

        .main-content { flex: 1; margin-left: 280px; padding: 6.5rem 2.5rem 3rem; }
        .page-header { margin-bottom: 2rem; }
        .page-header h1 { font-size: 1.75rem; font-weight: 800; letter-spacing: -0.5px; }

        /* Form Cards */
        .card {
            background: white; padding: 2.5rem; border-radius: 24px;
            box-shadow: 0 4px 25px rgba(0,0,0,0.02); border: 1px solid rgba(0,0,0,0.03); margin-bottom: 2rem;
        }
        .card-title { font-size: 1.15rem; font-weight: 700; margin-bottom: 1.75rem; display: flex; align-items: center; gap: 10px; }

        .profile-banner {
            display: flex; align-items: center; gap: 2rem; margin-bottom: 2.5rem; padding: 1.5rem;
            background: var(--bg-light); border-radius: 20px;
        }
        .profile-img-large { width: 100px; height: 100px; border-radius: 24px; object-fit: cover; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }

        /* Input Styles */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; font-weight: 600; margin-bottom: 8px; font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        input, select, textarea {
            width: 100%; padding: 0.85rem 1.1rem; border: 1.5px solid #e2e8f0; border-radius: 14px;
            background: #fff; transition: 0.3s; font-size: 0.95rem; color: var(--dark);
        }
        input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.08); }

        /* Buttons */
        .btn-save {
            background: var(--primary); color: white; border: none; padding: 1.1rem;
            border-radius: 16px; font-weight: 700; cursor: pointer; transition: 0.3s;
            width: 100%; font-size: 1rem; display: flex; justify-content: center; align-items: center; gap: 10px;
        }
        .btn-save:hover { background: #1d4ed8; transform: translateY(-2px); box-shadow: 0 10px 25px rgba(37, 99, 235, 0.25); }

        .alert { padding: 1rem 1.5rem; border-radius: 16px; margin-bottom: 2rem; font-weight: 500; border: 1px solid transparent; }
        .alert-success { background: #f0fdf4; color: #166534; border-color: #dcfce7; }
        .alert-danger { background: #fef2f2; color: #991b1b; border-color: #fee2e2; }

        .badge-status {
            background: var(--primary); color: white; padding: 5px 14px; border-radius: 30px; font-size: 0.7rem; font-weight: 800;
        }

        @media (max-width: 1024px) {
            .sidebar { width: 80px; }
            .sidebar span, .sidebar h3, .sidebar-header h3 { display: none; }
            .main-content, .glass-nav { left: 80px; }
            .grid-2 { grid-template-columns: 1fr; }
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
            <a href="profil.php" class="active"><span></span> <span>Profil Saya</span></a>
            <a href="mobil.php"><span></span> <span>Mobil Saya</span></a>
            <a href="rute.php"><span></span> <span>Rute Layanan</span></a>
            <a href="rating.php"><span></span> <span>Rating & Ulasan</span></a>
            <a href="../auth/logout.php" style="margin-top: 4rem; color: #f87171;"><span></span> <span>Keluar</span></a>
        </nav>
    </aside>

    <nav class="glass-nav">
        <div style="font-weight: 800; color: var(--primary); font-size: 1.2rem;">SopirPilihan<span style="color: var(--dark);">.id</span></div>
        <div style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">
            Log: <?= date('d M Y'); ?>
        </div>
    </nav>

    <main class="main-content">
        <div class="page-header">
            <h1>Pengaturan Profil</h1>
            <p style="color: var(--text-muted);">Perbarui informasi publik dan dokumen legalitas Anda.</p>
        </div>

        <?php if($message): ?>
            <div class="alert alert-<?= $message_type; ?>">
                <?= ($message_type == 'success' ? ' ' : ' ') . $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <div class="card">
                <div class="profile-banner">
                    <img src="<?= $foto_path ?>" class="profile-img-large">
                    <div>
                        <h2 style="font-weight: 800; font-size: 1.4rem; margin-bottom: 5px;"><?= htmlspecialchars($driver['nama_lengkap']); ?></h2>
                        <span class="badge-status">Status Verifikasi: <?= strtoupper($driver['status_verifikasi']); ?></span>
                    </div>
                </div>

                <div class="card-title"><span></span> Informasi Personal</div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($driver['nama_lengkap']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email Kontak</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($driver['email']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Nomor HP</label>
                        <input type="text" name="no_hp" value="<?= htmlspecialchars($driver['no_hp']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>WhatsApp</label>
                        <input type="text" name="no_whatsapp" value="<?= htmlspecialchars($driver['no_whatsapp']); ?>" placeholder="628..." required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Alamat Domisili</label>
                    <textarea name="alamat" rows="2" required><?= htmlspecialchars($driver['alamat']); ?></textarea>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label>Kota Asal</label>
                        <input type="text" name="rute_asal" value="<?= htmlspecialchars($driver['rute_asal'] ?? ''); ?>" placeholder="Contoh: Jakarta">
                    </div>
                    <div class="form-group">
                        <label>Jangkauan Rute</label>
                        <input type="text" name="rute_tujuan" value="<?= htmlspecialchars($driver['rute_tujuan'] ?? ''); ?>" placeholder="Contoh: Jawa Barat / Nasional">
                    </div>
                </div>

                <div class="form-group">
                    <label>Bio & Pengalaman (Akan tampil di profil publik)</label>
                    <textarea name="deskripsi" rows="4" placeholder="Ceritakan pengalaman mengemudi Anda..."><?= htmlspecialchars($driver['deskripsi']); ?></textarea>
                </div>
            </div>

            <div class="card">
                <div class="card-title"><span></span> Berkas & Legalitas</div>
                
                <div class="grid-2">
                    <div class="form-group">
                        <label>Update Foto Profil</label>
                        <input type="file" name="foto_profil" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label>Update Foto KTP</label>
                        <input type="file" name="foto_ktp" accept="image/*">
                        <?php if(!empty($driver['foto_ktp'])): ?>
                            <p style="margin-top: 10px;"><a href="../uploads/drivers/ktp/<?= $driver['foto_ktp']; ?>" target="_blank" style="color: var(--primary); font-size: 0.8rem; font-weight: 700; text-decoration: none;"> Lihat KTP Terunggah</a></p>
                        <?php endif; ?>
                    </div>
                </div>

                <hr style="margin: 2rem 0; border: 0; border-top: 1px solid #f1f5f9;">

                <div class="card-title" style="margin-top: 1rem;"><span></span> Lisensi Mengemudi (SIM)</div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Nomor SIM</label>
                        <input type="text" name="no_sim" value="<?= htmlspecialchars($driver['no_sim'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Jenis SIM</label>
                        <select name="jenis_sim" required>
                            <option value="A" <?= ($driver['jenis_sim'] ?? '') == 'A' ? 'selected' : ''; ?>>SIM A (Mobil Pribadi)</option>
                            <option value="B1" <?= ($driver['jenis_sim'] ?? '') == 'B1' ? 'selected' : ''; ?>>SIM B1 (Bus/Truk)</option>
                            <option value="B2" <?= ($driver['jenis_sim'] ?? '') == 'B2' ? 'selected' : ''; ?>>SIM B2 (Alat Berat)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Masa Berlaku SIM</label>
                        <input type="date" name="tanggal_berlaku" value="<?= $driver['tanggal_berlaku'] ?? ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Update Foto SIM</label>
                        <input type="file" name="foto_sim" accept="image/*">
                        <?php if(!empty($driver['foto_sim'])): ?>
                            <p style="margin-top: 10px;"><a href="../uploads/drivers/sim/<?= $driver['foto_sim']; ?>" target="_blank" style="color: var(--primary); font-size: 0.8rem; font-weight: 700; text-decoration: none;"> Lihat SIM Terunggah</a></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-save">
                <span></span> Simpan Seluruh Perubahan
            </button>
        </form>
    </main>

</body>
</html>