<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Proteksi Halaman
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'driver') {
    header('Location: ../auth/login.php');
    exit;
}

$id_driver = $_SESSION['profile_id'];
$message = '';

// 1. PROSES HAPUS RUTE
if(isset($_GET['delete'])) {
    $id_rute = intval($_GET['delete']);
    $query = "DELETE FROM rute_driver WHERE id_rute = $id_rute AND id_driver = $id_driver";
    if(mysqli_query($conn, $query)) {
        $message = "Rute berhasil dihapus dari daftar layanan.";
    }
}

// 2. PROSES TAMBAH/EDIT RUTE
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_rute = isset($_POST['id_rute']) ? intval($_POST['id_rute']) : 0;
    $kota_asal = mysqli_real_escape_string($conn, $_POST['kota_asal']);
    $kota_tujuan = mysqli_real_escape_string($conn, $_POST['kota_tujuan']);
    $harga_estimasi = floatval($_POST['harga_estimasi']);
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    
    if($id_rute > 0) {
        $query = "UPDATE rute_driver SET 
                  kota_asal = '$kota_asal', kota_tujuan = '$kota_tujuan',
                  harga_estimasi = $harga_estimasi, keterangan = '$keterangan'
                  WHERE id_rute = $id_rute AND id_driver = $id_driver";
        $message = "Rute berhasil diperbarui!";
    } else {
        $query = "INSERT INTO rute_driver (id_driver, kota_asal, kota_tujuan, harga_estimasi, keterangan)
                  VALUES ($id_driver, '$kota_asal', '$kota_tujuan', $harga_estimasi, '$keterangan')";
        $message = "Rute layanan baru berhasil ditambahkan!";
    }
    mysqli_query($conn, $query);
}

// 3. AMBIL DATA DRIVER & RUTE
$driver_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama_lengkap, foto_profil FROM driver WHERE id_driver = $id_driver"));
$foto_path = !empty($driver_data['foto_profil']) ? '../uploads/drivers/profile/' . $driver_data['foto_profil'] : 'https://ui-avatars.com/api/?name=' . urlencode($driver_data['nama_lengkap']);

$query_rute = "SELECT * FROM rute_driver WHERE id_driver = $id_driver ORDER BY kota_asal ASC";
$result_rute = mysqli_query($conn, $query_rute);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Rute - SopirPilihan.id</title>
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

        /* Sidebar Styling (Konsisten) */
        .sidebar { width: 280px; background: var(--dark); color: white; height: 100vh; position: fixed; z-index: 100; display: flex; flex-direction: column; }
        .sidebar-header { padding: 2.5rem 1.5rem; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .sidebar-header img { width: 65px; height: 65px; border-radius: 18px; border: 2px solid var(--primary); margin-bottom: 1rem; object-fit: cover; }
        .sidebar-menu { list-style: none; padding: 1.5rem 0; flex-grow: 1; }
        .sidebar-menu a { color: #94a3b8; text-decoration: none; padding: 0.9rem 1.75rem; display: flex; align-items: center; gap: 12px; font-weight: 500; transition: 0.3s; }
        .sidebar-menu a:hover, .sidebar-menu a.active { color: white; background: rgba(255,255,255,0.05); border-left: 4px solid var(--primary); }

        /* Navigation Header */
        .glass-nav { position: fixed; top: 0; right: 0; left: 280px; background: var(--glass); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(255,255,255,0.3); z-index: 90; padding: 0.85rem 2.5rem; display: flex; justify-content: space-between; align-items: center; }

        .main-content { flex: 1; margin-left: 280px; padding: 6.5rem 2.5rem 3rem; }

        /* Tips & Stats */
        .tips-card { background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 1px solid #bfdbfe; padding: 1.5rem; border-radius: 20px; display: flex; gap: 1rem; align-items: center; margin-bottom: 2rem; }
        .tips-icon { font-size: 2rem; }

        /* Rute Grid */
        .rute-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 1.5rem; }
        .rute-card { background: white; border-radius: 24px; padding: 1.8rem; border: 1px solid rgba(0,0,0,0.03); transition: 0.3s; }
        .rute-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.05); }

        .rute-visual { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; }
        .city-box { flex: 1; }
        .city-label { font-size: 0.7rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px; display: block; }
        .city-val { font-size: 1.15rem; font-weight: 800; color: var(--dark); }
        .path-line { flex: 0.3; height: 2px; background: #e2e8f0; position: relative; display: flex; justify-content: center; align-items: center; }
        .path-line::after { content: '➔'; position: absolute; color: #cbd5e1; font-size: 0.8rem; background: white; padding: 0 5px; }

        .price-info { background: var(--bg-light); border-radius: 16px; padding: 1rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; }
        .price-val { font-weight: 800; color: var(--primary); font-size: 1.1rem; }

        /* Buttons */
        .btn { padding: 0.8rem 1.2rem; border-radius: 12px; font-weight: 700; cursor: pointer; transition: 0.3s; border: none; font-size: 0.85rem; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-outline { background: #fff; color: var(--text-main); border: 1px solid #e2e8f0; }
        .btn-danger { color: #ef4444; }

        /* Modal */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(8px); align-items: center; justify-content: center; }
        .modal-content { background: white; padding: 2.5rem; border-radius: 28px; max-width: 550px; width: 90%; }
        
        input, textarea { width: 100%; padding: 0.85rem 1.1rem; border: 1.5px solid #e2e8f0; border-radius: 14px; margin-bottom: 1.2rem; font-size: 0.95rem; }
        label { display: block; font-weight: 600; font-size: 0.8rem; margin-bottom: 6px; color: var(--text-muted); text-transform: uppercase; }

        @media (max-width: 1024px) { .sidebar { width: 80px; } .sidebar span:not(.icon) { display: none; } .main-content, .glass-nav { left: 80px; } }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="<?= $foto_path ?>" alt="Profile">
            <h3 style="font-size: 1rem; font-weight: 700;">Driver Panel</h3>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php"><span class="icon"></span> <span>Dashboard</span></a>
            <a href="profil.php"><span class="icon"></span> <span>Profil Saya</span></a>
            <a href="mobil.php"><span class="icon"></span> <span>Mobil Saya</span></a>
            <a href="rute.php" class="active"><span class="icon"></span> <span>Rute Layanan</span></a>
            <a href="rating.php"><span class="icon"></span> <span>Rating & Ulasan</span></a>
            <a href="../auth/logout.php" style="margin-top: 4rem; color: #f87171;"><span class="icon"></span> <span>Keluar</span></a>
        </nav>
    </aside>

    <nav class="glass-nav">
        <div style="font-weight: 800; color: var(--primary); font-size: 1.2rem;">SopirPilihan<span style="color: var(--dark);">.id</span></div>
        <button onclick="openModal()" class="btn btn-primary">Tambah Rute Baru</button>
    </nav>

    <main class="main-content">
        <div style="margin-bottom: 2rem;">
            <h1 style="font-size: 1.75rem; font-weight: 800;">Jangkauan Rute</h1>
            <p style="color: var(--text-muted);">Kelola kota asal dan tujuan layanan Anda.</p>
        </div>

        <?php if($message): ?>
            <div style="padding: 1rem; background: #f0fdf4; color: #166534; border-radius: 16px; margin-bottom: 2rem; border: 1px solid #dcfce7; font-weight: 600;">
                ✅ <?= $message; ?>
            </div>
        <?php endif; ?>

        <div class="tips-card">
            <span class="tips-icon">💡</span>
            <div>
                <h4 style="color: #1e40af; font-weight: 800;">Tips Penumpang</h4>
                <p style="color: #1e40af; font-size: 0.9rem; opacity: 0.8;">Pelanggan lebih suka melihat rute yang spesifik. Sebutkan jika harga sudah termasuk Tol atau BBM di kolom keterangan.</p>
            </div>
        </div>

        <div class="rute-grid">
            <?php while($rute = mysqli_fetch_assoc($result_rute)): ?>
            <div class="rute-card">
                <div class="rute-visual">
                    <div class="city-box">
                        <span class="city-label">Dari</span>
                        <div class="city-val"><?= $rute['kota_asal']; ?></div>
                    </div>
                    <div class="path-line"></div>
                    <div class="city-box" style="text-align: right;">
                        <span class="city-label">Ke</span>
                        <div class="city-val"><?= $rute['kota_tujuan']; ?></div>
                    </div>
                </div>

                <div class="price-info">
                    <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">Estimasi Biaya</span>
                    <span class="price-val">
                        <?= $rute['harga_estimasi'] > 0 ? 'Rp ' . number_format($rute['harga_estimasi'], 0, ',', '.') : 'Harga Nego'; ?>
                    </span>
                </div>

                <div style="margin-bottom: 1.5rem; font-size: 0.9rem; line-height: 1.6; color: var(--text-muted); min-height: 50px;">
                    <?= !empty($rute['keterangan']) ? '📝 ' . $rute['keterangan'] : '<em>Tidak ada catatan tambahan.</em>'; ?>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <button onclick='editRute(<?= json_encode($rute); ?>)' class="btn btn-outline">Edit Rute</button>
                    <a href="?delete=<?= $rute['id_rute']; ?>" onclick="return confirm('Hapus rute ini?')" class="btn btn-outline btn-danger" style="justify-content: center;">Hapus</a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </main>

    <div id="ruteModal" class="modal">
        <div class="modal-content">
            <h2 id="modalTitle" style="font-weight: 800; margin-bottom: 1.5rem;">Atur Rute Layanan</h2>
            <form method="POST" action="">
                <input type="hidden" id="id_rute" name="id_rute">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label>Kota Asal</label>
                        <input type="text" id="kota_asal" name="kota_asal" required placeholder="Contoh: Surabaya">
                    </div>
                    <div>
                        <label>Kota Tujuan</label>
                        <input type="text" id="kota_tujuan" name="kota_tujuan" required placeholder="Contoh: Malang">
                    </div>
                </div>

                <label>Harga Estimasi (Rp)</label>
                <input type="number" id="harga_estimasi" name="harga_estimasi" placeholder="Misal: 500000">
                <small style="display: block; margin-top: -10px; margin-bottom: 15px; color: var(--text-muted); font-size: 0.75rem;">Kosongkan jika harga bersifat negosiasi.</small>

                <label>Keterangan Tambahan</label>
                <textarea id="keterangan" name="keterangan" rows="3" placeholder="Contoh: Sudah termasuk bensin & supir, tidak termasuk tol & parkir."></textarea>

                <div style="display: flex; gap: 10px; margin-top: 1rem;">
                    <button type="button" onclick="closeModal()" class="btn btn-outline" style="flex: 1;">Batal</button>
                    <button type="submit" class="btn btn-primary" style="flex: 2;"> Simpan Rute</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('ruteModal');
        function openModal() {
            modal.style.display = 'flex';
            document.getElementById('modalTitle').textContent = 'Tambah Rute Baru';
            document.getElementById('id_rute').value = '';
            document.querySelector('form').reset();
        }
        function closeModal() { modal.style.display = 'none'; }
        function editRute(rute) {
            modal.style.display = 'flex';
            document.getElementById('modalTitle').textContent = 'Edit Data Rute';
            document.getElementById('id_rute').value = rute.id_rute;
            document.getElementById('kota_asal').value = rute.kota_asal;
            document.getElementById('kota_tujuan').value = rute.kota_tujuan;
            document.getElementById('harga_estimasi').value = rute.harga_estimasi;
            document.getElementById('keterangan').value = rute.keterangan;
        }
        window.onclick = function(e) { if (e.target == modal) closeModal(); }
    </script>
</body>
</html>