<?php
session_start();
require_once '../config/database.php';

// Fungsi bantuan untuk membersihkan input (pastikan sudah ada di database.php atau buat di sini)
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
    $username = clean_input($_POST['username']);
    $password = md5($_POST['password']);
    $nama_lengkap = clean_input($_POST['nama_lengkap']);
    $email = clean_input($_POST['email']);
    $no_hp = clean_input($_POST['no_hp']);
    $alamat = clean_input($_POST['alamat']);
    
    $check_query = "SELECT * FROM users WHERE username = '$username'";
    $check_result = mysqli_query($conn, $check_query);
    
    if(mysqli_num_rows($check_result) > 0) {
        $error = 'Username sudah digunakan! Silakan gunakan username lain.';
    } else {
        mysqli_begin_transaction($conn);
        try {
            $insert_user = "INSERT INTO users (username, password, role, status) 
                           VALUES ('$username', '$password', 'pelanggan', 'active')";
            mysqli_query($conn, $insert_user);
            $id_user = mysqli_insert_id($conn);
            
            $insert_pelanggan = "INSERT INTO pelanggan (id_user, nama_lengkap, email, no_hp, alamat) 
                                VALUES ($id_user, '$nama_lengkap', '$email', '$no_hp', '$alamat')";
            mysqli_query($conn, $insert_pelanggan);
            
            mysqli_commit($conn);
            $success = 'Registrasi berhasil! Akun Anda sudah aktif dan siap digunakan.';
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = 'Terjadi kesalahan saat registrasi: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Pelanggan - SopirPilihan.id</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --accent: #10b981;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --glass-white: rgba(255, 255, 255, 0.92);
        }

        * {
            margin: 0; padding: 0; box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            /* BACKGROUND DENGAN OVERLAY BIRU TRANSPARAN */
            background: linear-gradient(135deg, rgba(21, 24, 31, 0.46) 0%, rgba(15, 23, 42, 0.9) 100%), 
                        url('../assets/images/BG15.jpg');
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
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            max-width: 550px;
            width: 100%;
            padding: 40px;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-icon {
            font-size: 40px;
            margin-bottom: 10px;
            display: inline-block;
        }

        .register-header h1 {
            color: var(--text-main);
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .register-header p {
            color: var(--text-muted);
            margin-top: 5px;
        }

        /* INFO BOX */
        .info-box {
            background: rgba(16, 185, 129, 0.1);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .info-box h3 {
            font-size: 14px;
            color: #065f46;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-box ul {
            list-style: none;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .info-box li {
            font-size: 12px;
            color: #064e3b;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .info-box li::before {
            content: "✓";
            color: var(--accent);
            font-weight: bold;
        }

        /* FORM STYLING */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .full-width { grid-column: span 2; }

        .form-group { margin-bottom: 20px; }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        input, textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-family: inherit;
            font-size: 14px;
            transition: all 0.2s;
            background: #ffffff;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        /* ALERT MESSAGES */
        .error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #ef4444;
        }

        .success-message {
            background: #d1fae5;
            color: #065f46;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #10b981;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }

        .btn-submit:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3);
        }

        .footer-links {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            font-size: 14px;
            color: var(--text-muted);
        }

        .footer-links a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 15px;
            text-decoration: none;
            color: var(--text-muted);
            font-size: 13px;
        }

        @media (max-width: 600px) {
            .form-grid { grid-template-columns: 1fr; }
            .full-width { grid-column: span 1; }
            .register-container { padding: 30px 20px; }
            .info-box ul { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <div class="register-container">
        <div class="register-header">
            <span class="logo-icon"></span>
            <h1>Daftar Akun</h1>
            <p>Bergabunglah dengan SopirPilihan.id sekarang</p>
        </div>

        <div class="info-box">
            <h3>Keuntungan Member</h3>
            <ul>
                <li>Gratis Selamanya</li>
                <li>Driver Terverifikasi</li>
                <li>WhatsApp Langsung</li>
                <li>Sistem Rating</li>
            </ul>
        </div>

        <?php if($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="success-message">
                <?php echo $success; ?>
                <br><a href="login.php" style="color: #059669; font-weight: 800; text-decoration: underline;">Login Sekarang →</a>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-grid">
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" required placeholder="User123">
                </div>

                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required placeholder="Min. 6 Karakter" minlength="6">
                </div>

                <div class="form-group full-width">
                    <label for="nama_lengkap">Nama Lengkap *</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" required placeholder="Masukkan nama sesuai KTP">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="nama@email.com">
                </div>

                <div class="form-group">
                    <label for="no_hp">Nomor WhatsApp *</label>
                    <input type="text" id="no_hp" name="no_hp" required placeholder="08123xxx" pattern="[0-9]{10,13}">
                </div>

                <div class="form-group full-width">
                    <label for="alamat">Alamat Lengkap</label>
                    <textarea id="alamat" name="alamat" placeholder="Tuliskan alamat domisili saat ini"></textarea>
                </div>
            </div>

            <button type="submit" class="btn-submit">Daftar Akun Gratis</button>
        </form>

        <div class="footer-links">
            Sudah punya akun? <a href="login.php">Masuk di sini</a>
            <br>
            <a href="../index.php" class="back-btn">← Kembali ke Beranda</a>
        </div>
    </div>

</body>
</html>