<?php
session_start();
require_once '../config/database.php';

// Jika sudah login, redirect ke dashboard
if(isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    header("Location: ../".$_SESSION['role']."/dashboard.php");
    exit();
}

$error = '';

// Proses login
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = clean_input($_POST['username']);
    $password = md5($_POST['password']); // Gunakan password_hash di produksi
    
    $query = "SELECT u.*, 
              CASE 
                WHEN u.role = 'pelanggan' THEN p.id_pelanggan
                WHEN u.role = 'driver' THEN d.id_driver
                ELSE NULL
              END as profile_id,
              CASE
                WHEN u.role = 'driver' THEN d.status_verifikasi
                ELSE NULL
              END as status_verifikasi
              FROM users u
              LEFT JOIN pelanggan p ON u.id_user = p.id_user
              LEFT JOIN driver d ON u.id_user = d.id_user
              WHERE u.username = '$username' AND u.password = '$password' AND u.status = 'active'";
    
    $result = mysqli_query($conn, $query);
    
    if(mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Cek verifikasi driver
        if($user['role'] == 'driver' && $user['status_verifikasi'] != 'verified') {
            $error = 'Akun driver Anda masih menunggu verifikasi admin. Mohon tunggu maksimal 1x24 jam.';
        } else {
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['profile_id'] = $user['profile_id'];
            
            // Redirect berdasarkan role
            header("Location: ../".$user['role']."/dashboard.php");
            exit();
        }
    } else {
        $error = 'Username atau password salah! Pastikan akun Anda sudah aktif.';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SopirPilihan.id</title>
    <meta name="description" content="Login ke akun SopirPilihan.id - Platform transportasi pribadi gratis tanpa komisi">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
body {
    font-family: 'Inter', 'Segoe UI', sans-serif;
    /* Gabungan Gambar Latar dan Overlay Biru Transparan */
    background: linear-gradient(135deg, rgba(16, 19, 27, 0.42) 0%, rgba(30, 64, 175, 0.48) 100%), 
                url('../assets/images/BG13.jpg'); /* Sesuaikan path gambar Anda */
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 2rem;
    position: relative;
    overflow-x: hidden;
}
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="white" opacity="0.1"/></svg>');
            opacity: 0.3;
        }
        
.login-container {
    background: rgba(255, 255, 255, 0.95); /* Sedikit transparan */
    backdrop-filter: blur(10px); /* Efek blur di belakang kotak login */
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    width: 100%;
    max-width: 450px;
    padding: 3rem;
    position: relative;
    z-index: 1;
    animation: slideUp 0.5s ease-out;
}
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .login-header .logo {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .login-header h1 {
            color: #1a1a1a;
            margin-bottom: 0.5rem;
            font-size: 2rem;
            font-weight: 800;
        }
        
        .login-header p {
            color: #000000ff;
            font-size: 1rem;
        }
        
        .form-group {
            margin-bottom: 1.75rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.6rem;
            color: #1a1a1a;
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 0.9rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s ease;
            background: #fafafa;
        }
        
        input:focus {
            outline: none;
            border-color: #2563eb;
            background: white;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }
        
        input:hover {
            border-color: #cbd5e1;
        }
        
        .error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: 1.2rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #ef4444;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
        }
        
        .btn {
            width: 100%;
            padding: 1.1rem;
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            letter-spacing: 0.3px;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.4);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .register-links {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #f1f5f9;
        }
        
        .register-links p {
            color: #64748b;
            margin-bottom: 1rem;
            font-weight: 500;
        }
        
        .register-links .btn-group {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
        }
        
        .register-links a {
            flex: 1;
            padding: 0.85rem 1rem;
            background: linear-gradient(135deg, #f1f5f9 0%, #e5e7eb 100%);
            color: #1a1a1a;
            text-decoration: none;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .register-links a:hover {
            background: linear-gradient(135deg, #e5e7eb 0%, #cbd5e1 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .back-home {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .back-home a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .back-home a:hover {
            color: #1e40af;
        }
        
        @media (max-width: 480px) {
            .login-container {
                padding: 2rem;
            }
            
            .register-links .btn-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo"></div>
            <h1>Selamat Datang</h1>
            <p>Login ke SopirPilihan.id</p>
        </div>
        
        <?php if($error): ?>
            <div class="error-message">
                <span style="font-size: 1.5rem;"></span>
                <div><?php echo $error; ?></div>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required placeholder="Masukkan username Anda">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Masukkan password Anda">
            </div>
            
            <button type="submit" class="btn"> Login Sekarang</button>
        </form>
        
        <div class="register-links">
            <p>Belum punya akun?</p>
            <div class="btn-group">
                <a href="register_pelanggan.php"> Daftar Pengguna</a>
                <a href="register_driver.php"> Daftar Driver</a>
            </div>
        </div>
        
        <div class="back-home">
            <a href="../index.php">← Kembali ke Beranda</a>
        </div>
    </div>
</body>
</html>