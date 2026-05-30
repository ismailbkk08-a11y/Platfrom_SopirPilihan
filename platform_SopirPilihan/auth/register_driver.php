<?php
session_start();
require_once '../config/database.php';

if (!function_exists('clean_input')) {
    function clean_input($data) {
        global $conn;
        return mysqli_real_escape_string($conn, htmlspecialchars(strip_tags(trim($data))));
    }
}

if(isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    header("Location: ../".$_SESSION['role']."/dashboard.php");
    exit();
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username     = clean_input($_POST['username']);
    $password     = md5($_POST['password']);
    $nama_lengkap = clean_input($_POST['nama_lengkap']);
    $email        = clean_input($_POST['email'] ?? '');
    $no_hp        = clean_input($_POST['no_hp']);
    $no_whatsapp  = clean_input($_POST['no_whatsapp']);
    $alamat       = clean_input($_POST['alamat'] ?? '');
    $deskripsi    = clean_input($_POST['deskripsi'] ?? '');
    
    $jenis_sim       = clean_input($_POST['jenis_sim']);
    $no_sim          = clean_input($_POST['no_sim']);
    $tanggal_berlaku = clean_input($_POST['tanggal_berlaku']);

    function uploadBerkas($file, $subfolder) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $file['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if(in_array($ext, $allowed)) {
            $new_name = uniqid() . '.' . $ext;
            $upload_path = '../uploads/drivers/' . $subfolder . '/';
            if(!file_exists($upload_path)) mkdir($upload_path, 0777, true);
            
            if(move_uploaded_file($file['tmp_name'], $upload_path . $new_name)) {
                return $new_name;
            }
        }
        return '';
    }

    $check_query = "SELECT id_user FROM users WHERE username = '$username'";
    $check_result = mysqli_query($conn, $check_query);
    
    if(mysqli_num_rows($check_result) > 0) {
        $error = 'Username sudah digunakan! Silakan gunakan username lain.';
    } else {
        mysqli_begin_transaction($conn);
        try {
            $foto_profil = (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == 0) ? uploadBerkas($_FILES['foto_profil'], 'profile') : '';
            $foto_sim    = (isset($_FILES['foto_sim']) && $_FILES['foto_sim']['error'] == 0) ? uploadBerkas($_FILES['foto_sim'], 'sim') : '';
            $foto_ktp    = (isset($_FILES['foto_ktp']) && $_FILES['foto_ktp']['error'] == 0) ? uploadBerkas($_FILES['foto_ktp'], 'ktp') : '';

            // 1. Insert ke tabel users
            $insert_user = "INSERT INTO users (username, password, role, status) VALUES ('$username', '$password', 'driver', 'active')";
            mysqli_query($conn, $insert_user);
            $id_user = mysqli_insert_id($conn);
            
            // 2. Insert ke tabel driver (Sudah ditambahkan foto_ktp)
            $insert_driver = "INSERT INTO driver (id_user, nama_lengkap, email, no_hp, no_whatsapp, alamat, deskripsi, foto_profil, foto_ktp, status_verifikasi) 
                             VALUES ($id_user, '$nama_lengkap', '$email', '$no_hp', '$no_whatsapp', '$alamat', '$deskripsi', '$foto_profil', '$foto_ktp', 'pending')";
            mysqli_query($conn, $insert_driver);
            $id_driver = mysqli_insert_id($conn);

            // 3. Insert ke tabel sim_driver
            $insert_sim = "INSERT INTO sim_driver (id_driver, no_sim, jenis_sim, tanggal_berlaku, foto_sim) 
                           VALUES ('$id_driver', '$no_sim', '$jenis_sim', '$tanggal_berlaku', '$foto_sim')";
            mysqli_query($conn, $insert_sim);
            
            mysqli_commit($conn);
            $success = 'Registrasi berhasil! Akun Anda sedang dalam verifikasi admin (1x24 jam).';
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Driver - SopirPilihan.id</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --glass-white: rgba(255, 255, 255, 0.95);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, rgba(19, 23, 32, 0.29) 0%, rgba(15, 23, 42, 0.9) 100%), 
                        url('../assets/images/BG17.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .register-container {
            background: var(--glass-white);
            backdrop-filter: blur(12px);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            max-width: 850px;
            width: 100%;
            padding: 40px;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .register-header { text-align: center; margin-bottom: 30px; }
        .register-header h1 { font-size: 28px; font-weight: 800; color: var(--text-main); }
        .register-header p { color: var(--text-muted); margin-top: 5px; }

        .section-title {
            color: var(--primary-dark);
            font-size: 16px;
            font-weight: 700;
            margin: 25px 0 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px 20px;
        }

        .full-width { grid-column: span 2; }

        .form-group { margin-bottom: 10px; }
        
        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 6px;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-family: inherit;
            font-size: 14px;
            transition: all 0.2s;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        input[type="file"] {
            padding: 8px;
            background: #f8fafc;
            font-size: 12px;
        }

        .error-message {
            background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 12px;
            margin-bottom: 20px; border-left: 4px solid #ef4444; font-size: 14px;
        }

        .success-message {
            background: #d1fae5; color: #065f46; padding: 15px; border-radius: 12px;
            margin-bottom: 20px; border-left: 4px solid #10b981; font-size: 14px;
        }

        .btn-submit {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 30px;
        }

        .btn-submit:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3);
        }

        .footer-links {
            text-align: center; margin-top: 25px; padding-top: 20px;
            border-top: 1px solid #e2e8f0; font-size: 14px; color: var(--text-muted);
        }

        .footer-links a { color: var(--primary); text-decoration: none; font-weight: 600; }

        @media (max-width: 768px) {
            .form-grid { grid-template-columns: 1fr; }
            .full-width { grid-column: span 1; }
            .register-container { padding: 30px 20px; }
        }
    </style>
</head>
<body>

    <div class="register-container">
        <div class="register-header">
            <h1>Daftar Sebagai Driver</h1>
            <p>Mulai hasilkan pendapatan dengan keahlian mengemudi Anda</p>
        </div>

        <?php if($error): ?><div class="error-message"> <?php echo $error; ?></div><?php endif; ?>
        <?php if($success): ?>
            <div class="success-message">
                 <?php echo $success; ?> 
                <br><a href="login.php" style="font-weight: 700; color: #065f46; text-decoration: underline;">Klik di sini untuk Login</a>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            
            <h3 class="section-title">Kredensial Login</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" required placeholder="pilih_username">
                </div>
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" required placeholder="Minimal 6 karakter" minlength="6">
                </div>
            </div>

            <h3 class="section-title"> Data Pribadi</h3>
            <div class="form-grid">
                <div class="form-group full-width">
                    <label>Nama Lengkap (Sesuai KTP) *</label>
                    <input type="text" name="nama_lengkap" required placeholder="Nama lengkap Anda">
                </div>
                <div class="form-group">
                    <label>Email (Opsional)</label>
                    <input type="email" name="email" placeholder="contoh@mail.com">
                </div>
                <div class="form-group">
                    <label>No. Handphone *</label>
                    <input type="text" name="no_hp" required placeholder="0812xxxx">
                </div>
                <div class="form-group">
                    <label>No. WhatsApp *</label>
                    <input type="text" name="no_whatsapp" required placeholder="62812xxxx">
                </div>
                <div class="form-group">
                    <label>Foto Profil</label>
                    <input type="file" name="foto_profil" accept="image/*">
                </div>
                <div class="form-group full-width">
                    <label>Alamat Domisili</label>
                    <textarea name="alamat" rows="2" placeholder="Alamat lengkap saat ini"></textarea>
                </div>
            </div>

            <h3 class="section-title"> Lisensi & Dokumen</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>Jenis SIM *</label>
                    <select name="jenis_sim" required>
                        <option value="A">SIM A (Mobil Pribadi)</option>
                        <option value="B1">SIM B1 (Bus/Truk)</option>
                        <option value="B2">SIM B2 (Alat Berat)</option>

                    </select>
                </div>
                <div class="form-group">
                    <label>Nomor SIM *</label>
                    <input type="text" name="no_sim" required placeholder="Sesuai kartu SIM">
                </div>
                <div class="form-group">
                    <label>Masa Berlaku SIM *</label>
                    <input type="date" name="tanggal_berlaku" required>
                </div>
                <div class="form-group">
                    <label>Upload Foto SIM *</label>
                    <input type="file" name="foto_sim" accept="image/*" required>
                </div>
                <div class="form-group full-width">
                    <label>Upload Foto KTP *</label>
                    <input type="file" name="foto_ktp" accept="image/*" required>
                </div>
            </div>

            <h3 class="section-title"> Tentang Anda</h3>
            <div class="form-group">
                <label>Deskripsi Pengalaman</label>
                <textarea name="deskripsi" rows="3" placeholder="Ceritakan pengalaman mengemudi Anda..."></textarea>
            </div>

            <button type="submit" class="btn-submit">Daftar Sekarang</button>
        </form>

        <div class="footer-links">
            Sudah memiliki akun? <a href="login.php">Masuk Sekarang</a>
            <br>
            <a href="../index.php" style="display: inline-block; margin-top: 15px; color: var(--text-muted);">← Kembali ke Beranda</a>
        </div>
    </div>

</body>
</html>