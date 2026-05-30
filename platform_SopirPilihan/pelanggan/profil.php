<?php
session_start();
require_once '../config/database.php';

// 1. KEAMANAN SESSION
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pelanggan') {
    header("Location: ../auth/login.php");
    exit;
}

$id_user = $_SESSION['user_id'];
$message = '';
$message_type = '';

// 2. PROSES UPDATE (Logika Tetap Sama)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama   = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $no_hp  = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    
    $old_res = mysqli_query($conn, "SELECT foto_profil FROM pelanggan WHERE id_user = $id_user");
    $old_data = mysqli_fetch_assoc($old_res);
    $nama_file_foto = $old_data['foto_profil'] ?? null;

    if (isset($_FILES['foto']) && $_FILES['foto']['name'] != '') {
        $file_name = $_FILES['foto']['name'];
        $file_tmp  = $_FILES['foto']['tmp_name'];
        $ext       = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed   = ['jpg', 'jpeg', 'png'];

        if (in_array($ext, $allowed)) {
            $new_name = "profil_" . $id_user . "_" . time() . "." . $ext;
            $target_dir = "../uploads/pelanggan/";
            
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

            if (move_uploaded_file($file_tmp, $target_dir . $new_name)) {
                if ($nama_file_foto && file_exists($target_dir . $nama_file_foto)) {
                    unlink($target_dir . $nama_file_foto);
                }
                $nama_file_foto = $new_name;
            }
        } else {
            $message = "Format harus JPG/PNG!";
            $message_type = "error";
        }
    }

    if ($message_type != 'error') {
        $cek = mysqli_query($conn, "SELECT id_user FROM pelanggan WHERE id_user = $id_user");
        if (mysqli_num_rows($cek) > 0) {
            $sql = "UPDATE pelanggan SET nama_lengkap='$nama', no_hp='$no_hp', alamat='$alamat', foto_profil='$nama_file_foto' WHERE id_user=$id_user";
        } else {
            $sql = "INSERT INTO pelanggan (id_user, nama_lengkap, no_hp, alamat, foto_profil) VALUES ($id_user, '$nama', '$no_hp', '$alamat', '$nama_file_foto')";
        }

        if (mysqli_query($conn, $sql)) {
            $message = "Profil berhasil diperbarui!";
            $message_type = "success";
        }
    }
}

// 3. AMBIL DATA TERBARU
$query = "SELECT u.username, p.* FROM users u LEFT JOIN pelanggan p ON u.id_user = p.id_user WHERE u.id_user = $id_user";
$res_user = mysqli_query($conn, $query);
$pelanggan = mysqli_fetch_assoc($res_user);

$foto_path = !empty($pelanggan['foto_profil']) 
    ? '../uploads/pelanggan/' . $pelanggan['foto_profil'] 
    : 'https://ui-avatars.com/api/?name=' . urlencode($pelanggan['nama_lengkap'] ?? 'User') . '&background=2563eb&color=fff';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil - SopirPilihan.id</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --bg-body: #f8fafc;
            --sidebar-bg: #0f172a;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            display: flex;
            min-height: 100vh;
            color: #1e293b;
        }

        /* --- STYLED NAVBAR (Konsisten) --- */
        .glass-nav {
            position: fixed;
            top: 0; right: 0; left: 280px;
            background: rgba(255, 255, 255, 0.48) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2) !important;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05) !important;
            z-index: 90;
            padding: 0.75rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .nav-brand { color: var(--primary); font-weight: 800; font-size: 1.2rem; }

        /* --- SIDEBAR (Konsisten) --- */
        .sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            color: white;
            height: 100vh;
            position: fixed;
            z-index: 100;
        }
        .sidebar-profile {
            padding: 3rem 1.5rem 2rem;
            text-align: center;
            background: linear-gradient(to bottom, rgba(37, 99, 235, 0.2), transparent);
        }
        .sidebar-profile img {
            width: 80px; height: 80px;
            border-radius: 50%;
            border: 3px solid var(--primary);
            padding: 3px;
            object-fit: cover;
            margin-bottom: 1rem;
        }
        .sidebar-menu { list-style: none; padding: 1rem 0; }
        .sidebar-menu a {
            color: #94a3b8;
            text-decoration: none;
            padding: 0.9rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: 0.3s;
            font-weight: 500;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255,255,255,0.05);
            color: white;
            border-right: 4px solid var(--primary);
        }

        /* --- MAIN CONTENT --- */
        .main-content { flex: 1; margin-left: 280px; padding: 6rem 2rem 2rem; }
        
        .profile-container { max-width: 850px; margin: 0 auto; }

        .profile-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.03);
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .card-header-banner {
            height: 140px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            position: relative;
        }

        .photo-section {
            padding: 0 2.5rem;
            margin-top: -50px;
            display: flex;
            align-items: flex-end;
            gap: 1.5rem;
            margin-bottom: 2rem;
            position: relative;
        }

        .photo-edit {
            position: relative;
            width: 130px; height: 130px;
        }

        .photo-edit img {
            width: 100%; height: 100%;
            border-radius: 20px;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .photo-edit label {
            position: absolute; bottom: -8px; right: -8px;
            background: var(--primary); color: white;
            width: 36px; height: 36px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; border: 3px solid white;
            transition: 0.3s; font-size: 0.9rem;
        }
        .photo-edit label:hover { transform: scale(1.1); background: var(--primary-dark); }

        .card-body { padding: 0 2.5rem 2.5rem; }

        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }
        .form-group { margin-bottom: 0.5rem; }
        .form-group.full { grid-column: span 2; }

        label {
            display: block; margin-bottom: 0.5rem;
            font-weight: 700; color: #475569; font-size: 0.85rem;
            text-transform: uppercase; letter-spacing: 0.5px;
        }

        input, textarea {
            width: 100%; padding: 0.85rem 1.1rem;
            border: 1.5px solid #e2e8f0; border-radius: 14px;
            font-size: 0.95rem; font-family: inherit;
            transition: 0.3s; color: #1e293b; background: #fcfdfe;
        }

        input:focus, textarea:focus {
            outline: none; border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.08);
            background: white;
        }

        input:disabled { background: #f1f5f9; color: #94a3b8; cursor: not-allowed; border-color: #e2e8f0; }

        .btn-save {
            background: var(--primary); color: white;
            border: none; padding: 1.1rem;
            border-radius: 14px; cursor: pointer;
            font-weight: 700; font-size: 1rem;
            width: 100%; margin-top: 2rem;
            transition: 0.3s;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }
        .btn-save:hover { 
            background: var(--primary-dark); 
            transform: translateY(-2px); 
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3); 
        }

        /* Alert Styling */
        .alert {
            padding: 1.25rem; border-radius: 16px; margin-bottom: 2rem;
            font-weight: 600; font-size: 0.95rem; display: flex; align-items: center; gap: 10px;
        }
        .alert-success { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
        .alert-error { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

        @media (max-width: 1024px) {
            .sidebar { width: 80px; }
            .sidebar-profile h3, .sidebar-profile p, .sidebar-menu span:last-child { display: none; }
            .main-content, .glass-nav { left: 80px; }
        }

        @media (max-width: 768px) {
            .form-grid { grid-template-columns: 1fr; }
            .form-group.full { grid-column: span 1; }
            .photo-section { flex-direction: column; align-items: center; text-align: center; }
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-profile">
            <img src="<?= $foto_path ?>" alt="Profil">
            <h3><?= htmlspecialchars(explode(' ', $pelanggan['nama_lengkap'] ?? 'User')[0]); ?></h3>
            <p style="font-size: 0.75rem; color: #94a3b8;">Pelanggan</p>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php"><span></span> <span>Dashboard</span></a>
            <a href="profil.php" class="active"><span></span> <span>Profil Saya</span></a>
            <a href="riwayat_rating.php"><span></span> <span>Riwayat Rating</span></a>
            <a href="../pages/daftar_driver.php"><span></span> <span>Cari Driver</span></a>
            <a href="../auth/logout.php" style="margin-top:2rem; color:#f87171;"><span></span> <span>Keluar</span></a>
        </nav>
    </aside>

    <nav class="glass-nav">
        <div class="nav-brand">SopirPilihan.id</div>
        <div style="font-size: 0.85rem; font-weight: 600; color: #64748b;">
            Pengaturan Akun
        </div>
    </nav>

    <main class="main-content">
        <div class="profile-container">
            
            <?php if($message): ?>
                <div class="alert alert-<?= $message_type ?>">
                    <span><?= $message_type == 'success' ? '✅' : '❌' ?></span>
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <div class="profile-card">
                <form method="POST" enctype="multipart/form-data">
                    <div class="card-header-banner"></div>
                    
                    <div class="photo-section">
                        <div class="photo-edit">
                            <img src="<?= $foto_path ?>" id="previewImg" alt="Foto">
                            <label for="fotoInput">📷</label>
                            <input type="file" name="foto" id="fotoInput" hidden accept="image/*" onchange="preview(this)">
                        </div>
                        <div style="padding-bottom: 10px;">
                            <h2 style="color: #1e293b; font-weight: 800;"><?= htmlspecialchars($pelanggan['nama_lengkap'] ?? 'Nama Lengkap') ?></h2>
                            <p style="color: #64748b; font-weight: 500;">@<?= htmlspecialchars($pelanggan['username']) ?></p>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>ID / Username</label>
                                <input type="text" value="<?= htmlspecialchars($pelanggan['username']) ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label>Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($pelanggan['nama_lengkap'] ?? '') ?>" placeholder="Nama lengkap sesuai KTP" required>
                            </div>
                            <div class="form-group">
                                <label>WhatsApp / No. HP</label>
                                <input type="text" name="no_hp" value="<?= htmlspecialchars($pelanggan['no_hp'] ?? '') ?>" placeholder="0812xxxx" required>
                            </div>
                            <div class="form-group">
                                <label>Member Sejak</label>
                                <input type="text" value="<?= !empty($pelanggan['created_at']) ? date('d M Y', strtotime($pelanggan['created_at'])) : '-' ?>" disabled>
                            </div>
                            <div class="form-group full">
                                <label>Alamat Utama</label>
                                <textarea name="alamat" rows="3" placeholder="Masukkan alamat lengkap untuk memudahkan koordinasi dengan driver"><?= htmlspecialchars($pelanggan['alamat'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn-save">Simpan Perubahan Profil</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
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