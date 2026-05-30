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

// 1. PROSES HAPUS MOBIL
if(isset($_GET['delete'])) {
    $id_mobil = intval($_GET['delete']);
    $query = "DELETE FROM mobil_driver WHERE id_mobil = $id_mobil AND id_driver = $id_driver";
    if(mysqli_query($conn, $query)) {
        $message = "Unit mobil berhasil dihapus dari daftar.";
    }
}

// 2. PROSES TAMBAH/EDIT MOBIL
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_mobil = isset($_POST['id_mobil']) ? intval($_POST['id_mobil']) : 0;
    $id_jenis_mobil = intval($_POST['id_jenis_mobil']);
    $merk = mysqli_real_escape_string($conn, $_POST['merk']);
    $model = mysqli_real_escape_string($conn, $_POST['model']);
    $warna = mysqli_real_escape_string($conn, $_POST['warna']);
    $no_polisi = mysqli_real_escape_string($conn, $_POST['no_polisi']);
    $tahun = intval($_POST['tahun']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $foto_sql = "";
    if(isset($_FILES['foto_mobil']) && $_FILES['foto_mobil']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['foto_mobil']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];
        
        if(in_array($ext, $allowed)) {
            $new_name = "car_" . uniqid() . '.' . $ext;
            if(move_uploaded_file($_FILES['foto_mobil']['tmp_name'], '../uploads/mobil/' . $new_name)) {
                $foto_sql = ", foto_mobil = '$new_name'";
                $foto_insert_val = "'$new_name'";
            }
        }
    } else {
        $foto_insert_val = "''";
    }
    
    if($id_mobil > 0) {
        $query = "UPDATE mobil_driver SET 
                  id_jenis_mobil = $id_jenis_mobil, merk = '$merk', model = '$model',
                  warna = '$warna', no_polisi = '$no_polisi', tahun = $tahun, status = '$status'
                  $foto_sql 
                  WHERE id_mobil = $id_mobil AND id_driver = $id_driver";
        $message = "Data armada berhasil diperbarui!";
    } else {
        $query = "INSERT INTO mobil_driver (id_driver, id_jenis_mobil, merk, model, warna, no_polisi, tahun, foto_mobil, status)
                  VALUES ($id_driver, $id_jenis_mobil, '$merk', '$model', '$warna', '$no_polisi', $tahun, $foto_insert_val, '$status')";
        $message = "Armada baru berhasil ditambahkan!";
    }
    
    if(!mysqli_query($conn, $query)) {
        $message = "Terjadi kesalahan: " . mysqli_error($conn);
        $message_type = "danger";
    }
}

// 3. AMBIL DATA TERBARU (Untuk Sidebar & List)
$driver_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama_lengkap, foto_profil FROM driver WHERE id_driver = $id_driver"));
$foto_path = !empty($driver_data['foto_profil']) ? '../uploads/drivers/profile/' . $driver_data['foto_profil'] : 'https://ui-avatars.com/api/?name=' . urlencode($driver_data['nama_lengkap']);

$result = mysqli_query($conn, "SELECT m.*, j.nama_jenis, j.kapasitas_penumpang 
                               FROM mobil_driver m
                               JOIN jenis_mobil j ON m.id_jenis_mobil = j.id_jenis_mobil
                               WHERE m.id_driver = $id_driver ORDER BY m.id_mobil DESC");

$result_jenis = mysqli_query($conn, "SELECT * FROM jenis_mobil ORDER BY nama_jenis");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Mobil - SopirPilihan.id</title>
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

        /* Sidebar Styling */
        .sidebar { width: 280px; background: var(--dark); color: white; height: 100vh; position: fixed; z-index: 100; display: flex; flex-direction: column; }
        .sidebar-header { padding: 2.5rem 1.5rem; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .sidebar-header img { width: 65px; height: 65px; border-radius: 18px; border: 2px solid var(--primary); margin-bottom: 1rem; object-fit: cover; }
        .sidebar-menu { list-style: none; padding: 1.5rem 0; flex-grow: 1; }
        .sidebar-menu a { color: #94a3b8; text-decoration: none; padding: 0.9rem 1.75rem; display: flex; align-items: center; gap: 12px; font-weight: 500; transition: 0.3s; }
        .sidebar-menu a:hover, .sidebar-menu a.active { color: white; background: rgba(255,255,255,0.05); border-left: 4px solid var(--primary); }

        /* Navigation Glass */
        .glass-nav { position: fixed; top: 0; right: 0; left: 280px; background: var(--glass); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(255,255,255,0.3); z-index: 90; padding: 0.85rem 2.5rem; display: flex; justify-content: space-between; align-items: center; }

        .main-content { flex: 1; margin-left: 280px; padding: 6.5rem 2.5rem 3rem; }
        .header-flex { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; }

        /* Grid & Cards */
        .armada-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 2rem; }
        .car-card { background: white; border-radius: 24px; overflow: hidden; border: 1px solid rgba(0,0,0,0.03); transition: 0.3s; position: relative; }
        .car-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0,0,0,0.06); }
        
        .car-img-container { position: relative; height: 200px; }
        .car-img { width: 100%; height: 100%; object-fit: cover; }
        .status-badge { position: absolute; top: 15px; right: 15px; padding: 6px 14px; border-radius: 30px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; }
        .status-active { background: #dcfce7; color: #166534; }
        .status-inactive { background: #fee2e2; color: #991b1b; }

        .car-body { padding: 1.5rem; }
        .car-type { color: var(--primary); font-weight: 700; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 0.5rem; display: block; }
        .car-title { font-size: 1.25rem; font-weight: 800; margin-bottom: 1rem; color: var(--dark); }
        
        .car-specs { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 1.5rem; background: var(--bg-light); padding: 12px; border-radius: 16px; }
        .spec-item { font-size: 0.85rem; color: var(--text-muted); display: flex; align-items: center; gap: 6px; }

        /* Buttons */
        .btn { padding: 0.85rem 1.2rem; border-radius: 14px; font-weight: 700; cursor: pointer; transition: 0.3s; border: none; font-size: 0.9rem; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-outline { background: #fff; color: var(--text-main); border: 1.5px solid #e2e8f0; }
        .btn-outline:hover { background: var(--bg-light); border-color: var(--primary); }
        .btn-danger { color: #ef4444; border: 1.5px solid #fee2e2; }
        .btn-danger:hover { background: #fef2f2; }

        /* Modal Glass Style */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(8px); padding: 2rem; align-items: center; }
        .modal-content { background: white; margin: auto; padding: 2.5rem; border-radius: 28px; max-width: 600px; width: 100%; box-shadow: 0 25px 50px rgba(0,0,0,0.1); }
        
        input, select { width: 100%; padding: 0.85rem 1.1rem; border: 1.5px solid #e2e8f0; border-radius: 14px; margin-bottom: 1.2rem; font-size: 0.95rem; }
        label { display: block; font-weight: 600; font-size: 0.8rem; margin-bottom: 6px; color: var(--text-muted); text-transform: uppercase; }

        @media (max-width: 1024px) {
            .sidebar { width: 80px; }
            .sidebar span:not(.icon), .sidebar-header h3 { display: none; }
            .main-content, .glass-nav { left: 80px; }
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
            <a href="dashboard.php"><span class="icon"></span> <span>Dashboard</span></a>
            <a href="profil.php"><span class="icon"></span> <span>Profil Saya</span></a>
            <a href="mobil.php" class="active"><span class="icon"></span> <span>Mobil Saya</span></a>
            <a href="rute.php"><span class="icon"></span> <span>Rute Layanan</span></a>
            <a href="rating.php"><span class="icon"></span> <span>Rating & Ulasan</span></a>
            <a href="../auth/logout.php" style="margin-top: 4rem; color: #f87171;"><span class="icon"></span> <span>Keluar</span></a>
        </nav>
    </aside>

    <nav class="glass-nav">
        <div style="font-weight: 800; color: var(--primary); font-size: 1.2rem;">SopirPilihan<span style="color: var(--dark);">.id</span></div>
        <button onclick="openModal()" class="btn btn-primary"> Tambah Unit Baru</button>
    </nav>

    <main class="main-content">
        <div class="header-flex">
            <div>
                <h1 style="font-size: 1.75rem; font-weight: 800;">Kelola Armada</h1>
                <p style="color: var(--text-muted);">Daftar kendaraan yang Anda gunakan untuk layanan.</p>
            </div>
        </div>

        <?php if($message): ?>
            <div style="padding: 1rem 1.5rem; border-radius: 16px; background: #f0fdf4; color: #166534; margin-bottom: 2rem; font-weight: 600; border: 1px solid #dcfce7;">
                ✅ <?= $message; ?>
            </div>
        <?php endif; ?>

        <div class="armada-grid">
            <?php while($mobil = mysqli_fetch_assoc($result)): ?>
            <div class="car-card">
                <div class="car-img-container">
                    <img src="<?= !empty($mobil['foto_mobil']) ? '../uploads/mobil/'.$mobil['foto_mobil'] : '../assets/images/default-car.png'; ?>" class="car-img">
                    <span class="status-badge <?= $mobil['status'] == 'active' ? 'status-active' : 'status-inactive'; ?>">
                        <?= $mobil['status'] == 'active' ? '🟢 Aktif' : '🔴 Perbaikan'; ?>
                    </span>
                </div>
                <div class="car-body">
                    <span class="car-type"><?= $mobil['nama_jenis']; ?> • <?= $mobil['kapasitas_penumpang']; ?> Kursi</span>
                    <h3 class="car-title"><?= $mobil['merk'] . ' ' . $mobil['model']; ?></h3>
                    
                    <div class="car-specs">
                        <div class="spec-item"> <?= $mobil['no_polisi']; ?></div>
                        <div class="spec-item"> <?= $mobil['tahun']; ?></div>
                        <div class="spec-item"> <?= $mobil['warna']; ?></div>
                        <div class="spec-item">⚙️ Manual/Matik</div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <button onclick='editMobil(<?= json_encode($mobil); ?>)' class="btn btn-outline">Edit</button>
                        <a href="?delete=<?= $mobil['id_mobil']; ?>" onclick="return confirm('Hapus unit ini?')" class="btn btn-danger">Hapus</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </main>

    <div id="mobilModal" class="modal">
        <div class="modal-content">
            <h2 id="modalTitle" style="font-weight: 800; margin-bottom: 1.5rem;">Tambah Armada</h2>
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" id="id_mobil" name="id_mobil">
                
                <label>Jenis & Kapasitas</label>
                <select id="id_jenis_mobil" name="id_jenis_mobil" required>
                    <?php 
                    mysqli_data_seek($result_jenis, 0);
                    while($jenis = mysqli_fetch_assoc($result_jenis)): ?>
                        <option value="<?= $jenis['id_jenis_mobil']; ?>"><?= $jenis['nama_jenis']; ?> (<?= $jenis['kapasitas_penumpang']; ?> Kursi)</option>
                    <?php endwhile; ?>
                </select>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label>Merk</label>
                        <input type="text" id="merk" name="merk" required placeholder="Toyota">
                    </div>
                    <div>
                        <label>Model</label>
                        <input type="text" id="model" name="model" required placeholder="Avanza">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label>No. Polisi</label>
                        <input type="text" id="no_polisi" name="no_polisi" required placeholder="B 1234 ABC">
                    </div>
                    <div>
                        <label>Tahun</label>
                        <input type="number" id="tahun" name="tahun" required placeholder="2022">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label>Warna</label>
                        <input type="text" id="warna" name="warna" placeholder="Hitam">
                    </div>
                    <div>
                        <label>Status</label>
                        <select id="status" name="status">
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <label>Foto Mobil</label>
                <input type="file" name="foto_mobil">

                <div style="display: flex; gap: 10px; margin-top: 1rem;">
                    <button type="button" onclick="closeModal()" class="btn btn-outline" style="flex: 1;">Batal</button>
                    <button type="submit" class="btn btn-primary" style="flex: 2;"> Simpan Data Armada</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('mobilModal');
        function openModal() {
            modal.style.display = 'flex';
            document.getElementById('modalTitle').textContent = 'Tambah Armada Baru';
            document.getElementById('id_mobil').value = '';
        }
        function closeModal() { modal.style.display = 'none'; }
        function editMobil(mobil) {
            modal.style.display = 'flex';
            document.getElementById('modalTitle').textContent = 'Edit Armada';
            document.getElementById('id_mobil').value = mobil.id_mobil;
            document.getElementById('id_jenis_mobil').value = mobil.id_jenis_mobil;
            document.getElementById('merk').value = mobil.merk;
            document.getElementById('model').value = mobil.model;
            document.getElementById('warna').value = mobil.warna;
            document.getElementById('tahun').value = mobil.tahun;
            document.getElementById('no_polisi').value = mobil.no_polisi;
            document.getElementById('status').value = mobil.status;
        }
    </script>
</body>
</html>