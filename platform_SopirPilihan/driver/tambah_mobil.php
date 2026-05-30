<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Proteksi halaman
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'driver') {
    header('Location: ../auth/login.php');
    exit;
}

$id_user = $_SESSION['id_user'];
$id_driver = $_SESSION['profile_id']; // Menggunakan profile_id dari session agar konsisten

$message = '';

// Logika Simpan Data
if (isset($_POST['simpan'])) {
    $merk_model = mysqli_real_escape_string($conn, $_POST['merk_model']);
    $id_jenis_mobil = intval($_POST['id_jenis_mobil']);
    $no_polisi = mysqli_real_escape_string($conn, $_POST['no_polisi']);
    $tahun = intval($_POST['tahun']);
    $warna = mysqli_real_escape_string($conn, $_POST['warna']);
    
    // Handle Upload Foto menggunakan fungsi yang sudah Anda miliki atau standar
    $foto_mobil = "";
    if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $new_name = "car_" . uniqid() . '.' . $ext;
        if(move_uploaded_file($_FILES['foto']['tmp_name'], '../uploads/mobil/' . $new_name)) {
            $foto_mobil = $new_name;
        }
    }

    // Sesuaikan query dengan struktur tabel mobil_driver Anda
    $query = "INSERT INTO mobil_driver (id_driver, id_jenis_mobil, merk, model, no_polisi, tahun, warna, foto_mobil, status) 
              VALUES ($id_driver, $id_jenis_mobil, '$merk_model', '', '$no_polisi', $tahun, '$warna', '$foto_mobil', 'active')";

    if(mysqli_query($conn, $query)) {
        header("Location: mobil.php?msg=success");
        exit;
    } else {
        $message = "Gagal menambahkan mobil: " . mysqli_error($conn);
    }
}

// Ambil data driver untuk sidebar
$driver_res = mysqli_query($conn, "SELECT nama_lengkap, foto_profil FROM driver WHERE id_driver = $id_driver");
$driver = mysqli_fetch_assoc($driver_res);
$foto_path = !empty($driver['foto_profil']) ? '../uploads/drivers/profile/'.$driver['foto_profil'] : 'https://ui-avatars.com/api/?name='.urlencode($driver['nama_lengkap']);

$jenis_mobil = mysqli_query($conn, "SELECT * FROM jenis_mobil ORDER BY nama_jenis");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Armada - SopirPilihan.id</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --dark: #0f172a;
            --bg-light: #f8fafc;
            --text-muted: #64748b;
            --glass: rgba(255, 255, 255, 0.75);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-light); display: flex; min-height: 100vh; }

        /* Sidebar Glass Style (Konsisten dengan halaman lain) */
        .sidebar { width: 280px; background: var(--dark); color: white; height: 100vh; position: fixed; display: flex; flex-direction: column; }
        .sidebar-header { padding: 2.5rem 1.5rem; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .sidebar-header img { width: 65px; height: 65px; border-radius: 18px; border: 2px solid var(--primary); margin-bottom: 1rem; object-fit: cover; }
        .sidebar-menu { list-style: none; padding: 1.5rem 0; flex-grow: 1; }
        .sidebar-menu a { color: #94a3b8; text-decoration: none; padding: 0.9rem 1.75rem; display: flex; align-items: center; gap: 12px; font-weight: 500; transition: 0.3s; }
        .sidebar-menu a.active { color: white; background: rgba(255,255,255,0.05); border-left: 4px solid var(--primary); }

        /* Navigation Glass */
        .glass-nav { position: fixed; top: 0; right: 0; left: 280px; background: var(--glass); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(255,255,255,0.3); z-index: 90; padding: 0.85rem 2.5rem; display: flex; justify-content: space-between; align-items: center; }

        .main-content { flex: 1; margin-left: 280px; padding: 6.5rem 2.5rem 3rem; display: flex; justify-content: center; }
        
        /* Card Form Style */
        .form-card { background: white; padding: 2.5rem; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.02); border: 1px solid rgba(0,0,0,0.04); width: 100%; max-width: 700px; }
        .card-header { margin-bottom: 2rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 1rem; }
        
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 8px; color: var(--text-muted); text-transform: uppercase; }
        input, select { width: 100%; padding: 0.85rem 1.1rem; border: 1.5px solid #e2e8f0; border-radius: 14px; background: #fff; font-size: 0.95rem; transition: 0.3s; }
        input:focus, select:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.08); }

        .btn-submit { background: var(--primary); color: white; border: none; padding: 1.1rem; border-radius: 16px; font-weight: 700; cursor: pointer; transition: 0.3s; width: 100%; font-size: 1rem; margin-top: 1rem; }
        .btn-submit:hover { background: #1d4ed8; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2); }
        .btn-back { display: inline-block; margin-bottom: 1.5rem; text-decoration: none; color: var(--text-muted); font-size: 0.9rem; font-weight: 600; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="<?= $foto_path ?>" alt="Profile">
            <h3 style="font-size: 1rem; font-weight: 700;">Driver Panel</h3>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php"><span>📊</span> <span>Dashboard</span></a>
            <a href="profil.php"><span>👤</span> <span>Profil Saya</span></a>
            <a href="mobil.php" class="active"><span>🚙</span> <span>Mobil Saya</span></a>
            <a href="rute.php"><span>🗺️</span> <span>Rute Layanan</span></a>
            <a href="rating.php"><span>⭐</span> <span>Rating & Ulasan</span></a>
        </nav>
    </aside>

    <nav class="glass-nav">
        <div style="font-weight: 800; color: var(--primary); font-size: 1.2rem;">SopirPilihan<span style="color: var(--dark);">.id</span></div>
    </nav>

    <main class="main-content">
        <div class="form-card">
            <a href="mobil.php" class="btn-back">← Kembali ke Daftar</a>
            <div class="card-header">
                <h2 style="font-weight: 800; font-size: 1.5rem;">Tambah Armada Baru</h2>
                <p style="color: var(--text-muted); font-size: 0.9rem;">Masukkan detail kendaraan yang akan Anda gunakan.</p>
            </div>

            <?php if($message): ?>
                <p style="color: #ef4444; background: #fef2f2; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem;"><?= $message; ?></p>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Merk & Model Kendaraan</label>
                    <input type="text" name="merk_model" placeholder="Contoh: Toyota Avanza Veloz" required>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label>Kategori Jenis</label>
                        <select name="id_jenis_mobil" required>
                            <option value="">Pilih Jenis Mobil</option>
                            <?php while($j = mysqli_fetch_assoc($jenis_mobil)) : ?>
                                <option value="<?= $j['id_jenis_mobil']; ?>"><?= $j['nama_jenis']; ?> (<?= $j['kapasitas_penumpang']; ?> Kursi)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nomor Polisi (Plat)</label>
                        <input type="text" name="no_polisi" placeholder="B 1234 ABC" required>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label>Tahun Pembuatan</label>
                        <input type="number" name="tahun" placeholder="2022" required>
                    </div>
                    <div class="form-group">
                        <label>Warna Kendaraan</label>
                        <input type="text" name="warna" placeholder="Hitam Metalik" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Foto Kendaraan</label>
                    <input type="file" name="foto" style="padding: 0.7rem;" required>
                    <small style="color: var(--text-muted); display: block; margin-top: 5px;">Format: JPG, PNG. Maksimal 2MB.</small>
                </div>

                <button type="submit" name="simpan" class="btn-submit">💾 Simpan Armada</button>
            </form>
        </div>
    </main>

</body>
</html>