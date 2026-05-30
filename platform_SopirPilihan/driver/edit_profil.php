<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Proteksi: Hanya Driver yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'driver') {
    header("Location: ../auth/login.php");
    exit;
}

$id_user = $_SESSION['user_id'];
$id_driver = $_SESSION['profile_id'];
$message = '';
$message_type = '';

// 1. Ambil data driver saat ini
$query = "SELECT d.*, u.status FROM driver d 
          JOIN users u ON d.id_user = u.id_user 
          WHERE d.id_driver = $id_driver";
$result = mysqli_query($conn, $query);
$d = mysqli_fetch_assoc($result);

// 2. Logika Update Data
if (isset($_POST['update'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $no_wa = mysqli_real_escape_string($conn, $_POST['no_whatsapp']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $foto_lama = $_POST['foto_lama'];

    if ($_FILES['foto_profil']['error'] === 4) {
        $foto_final = $foto_lama;
    } else {
        $foto_final = uploadFoto($_FILES['foto_profil']['name'], $_FILES['foto_profil']['tmp_name'], 'drivers/profile');
        
        if ($foto_final && $foto_lama && $foto_lama != 'default-avatar.png') {
            $path_lama = "../uploads/drivers/profile/" . $foto_lama;
            if (file_exists($path_lama)) { unlink($path_lama); }
        }
    }

    $update_query = "UPDATE driver SET 
                    nama_lengkap = '$nama', 
                    no_whatsapp = '$no_wa', 
                    deskripsi = '$deskripsi', 
                    foto_profil = '$foto_final' 
                    WHERE id_driver = $id_driver";

    if (mysqli_query($conn, $update_query)) {
        $message = "Profil Anda berhasil diperbarui!";
        $message_type = "success";
        $res_refresh = mysqli_query($conn, "SELECT * FROM driver WHERE id_driver = $id_driver");
        $d = mysqli_fetch_assoc($res_refresh);
    } else {
        $message = "Gagal memperbarui database: " . mysqli_error($conn);
        $message_type = "error";
    }
}

$foto_path = !empty($d['foto_profil']) 
    ? "../uploads/drivers/profile/" . $d['foto_profil'] 
    : 'https://ui-avatars.com/api/?name=' . urlencode($d['nama_lengkap'] ?? 'Driver') . '&background=1e293b&color=fff';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil Driver - SopirPilihan.id</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* Standarisasi Font dan Box Model */
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
            font-family: 'Inter', sans-serif; /* Mengaktifkan Inter untuk semua elemen */
        }

        body {
            background: #f8f9fd;
            display: grid;
            grid-template-columns: 280px 1fr;
            min-height: 100vh;
            color: #1e293b;
        }

        /* Sidebar Styling (Konsisten) */
        .sidebar {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: white; padding: 2.5rem 1.5rem;
            position: sticky; top: 0; height: 100vh;
        }
        .sidebar-logo { 
            font-size: 1.5rem; 
            font-weight: 800; 
            margin-bottom: 3rem; 
            display: block; 
            text-align: center; 
            color: white; 
            text-decoration: none; 
            letter-spacing: -0.5px;
        }
        .sidebar-menu { list-style: none; }
        .sidebar-menu a {
            display: flex; align-items: center; gap: 1rem; padding: 0.9rem 1.2rem;
            border-radius: 12px; color: #cbd5e1; font-weight: 500; text-decoration: none; 
            transition: 0.3s; font-size: 0.95rem;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active { 
            background: rgba(255, 255, 255, 0.1); 
            color: white; 
        }

        /* Content Area */
        .main-content { padding: 3rem; }
        .profile-container { max-width: 850px; margin: 0 auto; }
        
        .page-header h1 { 
            font-size: 1.85rem; 
            font-weight: 800; 
            color: #0f172a; 
            letter-spacing: -0.8px; 
            margin-bottom: 0.5rem;
        }
        .page-header p { 
            color: #64748b; 
            font-weight: 400; 
            font-size: 1rem; 
            margin-bottom: 2.5rem;
        }

        /* Card Profile Styling */
        .profile-card {
            background: white; border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.04);
            overflow: hidden; border: 1px solid rgba(226, 232, 240, 0.8);
        }

        .header-banner { 
            height: 150px; 
            background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%); 
        }

        .photo-section {
            padding: 0 2.5rem; margin-top: -65px;
            display: flex; align-items: flex-end; gap: 1.8rem; margin-bottom: 2.5rem;
        }

        .photo-wrapper { position: relative; width: 150px; height: 150px; }
        .photo-wrapper img {
            width: 100%; height: 100%; border-radius: 24px;
            object-fit: cover; border: 6px solid white; box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        .photo-wrapper label {
            position: absolute; bottom: -8px; right: -8px;
            background: #2563eb; color: white; width: 42px; height: 42px;
            border-radius: 12px; display: flex; align-items: center; justify-content: center;
            cursor: pointer; border: 4px solid white; transition: 0.3s; font-size: 1.2rem;
        }

        /* Form Styling */
        .card-body { padding: 0 2.5rem 3rem 2.5rem; }
        .form-group { margin-bottom: 1.8rem; }
        
        label { 
            display: block; margin-bottom: 0.7rem; 
            font-weight: 600; color: #334155; font-size: 0.9rem; 
        }
        
        input, textarea {
            width: 100%; padding: 0.9rem 1.1rem; 
            border: 2px solid #f1f5f9; border-radius: 14px;
            font-size: 0.95rem; transition: 0.3s; background: #f8fafc;
            color: #1e293b;
        }
        
        input:focus, textarea:focus { 
            outline: none; border-color: #2563eb; 
            background: white;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.08); 
        }

        /* Buttons */
        .btn-submit {
            background: #2563eb; color: white; border: none; padding: 1.1rem;
            border-radius: 14px; font-weight: 700; cursor: pointer; 
            width: 100%; font-size: 1rem; transition: 0.3s;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }
        .btn-submit:hover { 
            background: #1d4ed8; transform: translateY(-2px); 
            box-shadow: 0 6px 15px rgba(37, 99, 235, 0.3); 
        }

        .alert { 
            padding: 1.1rem; border-radius: 14px; margin-bottom: 1.5rem; 
            font-size: 0.95rem; font-weight: 500; display: flex; align-items: center; gap: 0.8rem;
        }
        .alert-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

        @media (max-width: 992px) {
            body { grid-template-columns: 1fr; }
            .sidebar { display: none; }
            .main-content { padding: 1.5rem; }
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <a href="dashboard.php" class="sidebar-logo">🚗 Driver Panel</a>
    <ul class="sidebar-menu">
        <li><a href="dashboard.php"><span>📊</span> Dashboard</a></li>
        <li><a href="profil.php" class="active"><span>👤</span> Profil Saya</a></li>
        <li><a href="mobil.php"><span>🚙</span> Mobil Saya</a></li>
        <li><a href="rute.php"><span>🗺️</span> Rute Layanan</a></li>
        <li><a href="rating.php"><span>⭐</span> Rating & Ulasan</a></li>
        <li style="margin-top: 3rem;"><a href="../auth/logout.php" style="color: #fca5a5;"><span>🚪</span> Keluar</a></li>
    </ul>
</aside>

<main class="main-content">
    <div class="profile-container">
        <div class="page-header">
            <h1>Profil Driver</h1>
            <p>Kelola bagaimana pelanggan melihat profil profesional Anda.</p>
        </div>

        <?php if($message): ?>
            <div class="alert alert-<?= $message_type ?>">
                <?= $message_type === 'success' ? '✅' : '❌' ?> <?= $message ?>
            </div>
        <?php endif; ?>

        <div class="profile-card">
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="foto_lama" value="<?= $d['foto_profil']; ?>">
                <div class="header-banner"></div>
                
                <div class="photo-section">
                    <div class="photo-wrapper">
                        <img src="<?= $foto_path ?>" id="previewImg" alt="Profil">
                        <label for="fotoInput">📸</label>
                        <input type="file" name="foto_profil" id="fotoInput" hidden accept="image/*" onchange="preview(this)">
                    </div>
                    <div style="padding-bottom: 15px;">
                        <h2 style="font-weight: 800; font-size: 1.6rem; letter-spacing: -0.5px;"><?= htmlspecialchars($d['nama_lengkap']); ?></h2>
                        <div style="margin-top: 5px;">
                            <span style="background: #eff6ff; color: #2563eb; padding: 6px 14px; border-radius: 30px; font-size: 0.8rem; font-weight: 700; text-transform: uppercase;">
                                <?= $d['status']; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="form-group">
                        <label>Nama Lengkap Profesional</label>
                        <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($d['nama_lengkap']); ?>" required placeholder="Masukkan nama sesuai KTP">
                    </div>
                    
                    <div class="form-group">
                        <label>Nomor WhatsApp (Aktif)</label>
                        <input type="text" name="no_whatsapp" value="<?= htmlspecialchars($d['no_whatsapp']); ?>" required placeholder="628xxxxxxxx">
                        <p style="color: #94a3b8; font-size: 0.8rem; margin-top: 0.5rem;">Pelanggan akan menghubungi Anda melalui nomor ini.</p>
                    </div>

                    <div class="form-group">
                        <label>Deskripsi & Pengalaman Mengemudi</label>
                        <textarea name="deskripsi" rows="6" placeholder="Contoh: Pengalaman 10 tahun rute antar kota, menguasai mobil matic/manual..."><?= htmlspecialchars($d['deskripsi']); ?></textarea>
                    </div>

                    <div style="display: flex; gap: 1.2rem; margin-top: 1rem;">
                        <button type="submit" name="update" class="btn-submit">Simpan Perubahan</button>
                        <a href="dashboard.php" style="flex: 0.4; text-align: center; text-decoration: none; padding: 1.1rem; background: #f1f5f9; color: #475569; border-radius: 14px; font-weight: 600; font-size: 1rem; transition: 0.3s;">Batal</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
    // Fungsi preview gambar real-time
    function preview(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImg').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

</body>
</html>