<?php
session_start();
require_once '../config/database.php';

$id_driver = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Menghapus u.email karena menyebabkan error. 
// d.* sudah mengambil email jika kolom tersebut ada di tabel driver.
$query = "SELECT d.*, u.status as user_status, u.created_at as tgl_daftar,
          (SELECT AVG(rating) FROM rating_driver WHERE id_driver = d.id_driver) as avg_rating,
          (SELECT COUNT(*) FROM rating_driver WHERE id_driver = d.id_driver) as total_rating
          FROM driver d 
          LEFT JOIN users u ON d.id_user = u.id_user
          WHERE d.id_driver = $id_driver";
          
$result = mysqli_query($conn, $query);

if(!$result || mysqli_num_rows($result) == 0) {
    header("Location: daftar_driver.php");
    exit();
}

$driver = mysqli_fetch_assoc($result);

// Ambil data SIM driver
$query_sim = "SELECT * FROM sim_driver WHERE id_driver = $id_driver";
$result_sim = mysqli_query($conn, $query_sim);

// Ambil mobil driver
$query_mobil = "SELECT m.*, j.nama_jenis, j.kapasitas_penumpang 
                FROM mobil_driver m
                JOIN jenis_mobil j ON m.id_jenis_mobil = j.id_jenis_mobil
                WHERE m.id_driver = $id_driver AND m.status = 'active'";
$result_mobil = mysqli_query($conn, $query_mobil);

// Ambil rute driver
$query_rute = "SELECT * FROM rute_driver WHERE id_driver = $id_driver ORDER BY kota_asal";
$result_rute = mysqli_query($conn, $query_rute);

// Ambil rating & review
$query_rating = "SELECT r.*, p.nama_lengkap, p.foto_profil 
                 FROM rating_driver r
                 JOIN pelanggan p ON r.id_pelanggan = p.id_pelanggan
                 WHERE r.id_driver = $id_driver
                 ORDER BY r.created_at DESC";
$result_rating = mysqli_query($conn, $query_rating);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($driver['nama_lengkap']); ?> - Detail Driver</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
    :root {
        --primary: #2563eb;
        --success: #22c55e;
        --slate-50: #f8fafc;
        --slate-100: #f1f5f9;
        --slate-200: #e2e8f0;
        --slate-600: #475569;
        --slate-800: #1e293b;
        --warning: #f59e0b;
    }

    header {
        background: rgba(255, 255, 255, 0.91) !important; 
        backdrop-filter: blur(12px) !important;
        -webkit-backdrop-filter: blur(12px) !important;
        border-bottom: 1px solid rgba(26, 31, 172, 0.86) !important;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1) !important;
        position: sticky;
        top: 0;
        z-index: 1000;
        padding: 1rem 0;
        transition: all 0.3s ease;
    }

    nav a {
        color: #0d4cbaf1 !important;
        font-weight: 500;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .logo {
        color: #2563eb !important;
        font-weight: 800;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    nav a:hover {
        color: rgba(37, 99, 235, 1) !important;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        padding: 0.5rem 1rem;
    }

    body { 
        font-family: 'Plus Jakarta Sans', sans-serif; 
        background: linear-gradient(rgba(6, 15, 25, 0.51), rgba(12, 24, 36, 0.47)), 
                    url('../assets/images/BG9.jpg') no-repeat center center fixed;
        background-size: cover !important;
        color: var(--slate-800);
        margin: 0;
        line-height: 1.6;
        min-height: 100vh;
    }

    .container { max-width: 1000px; margin: 2rem auto; padding: 0 1.5rem; }

    .card { 
        background: white; 
        border-radius: 20px; 
        padding: 2.5rem; 
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
        border: 1px solid var(--slate-200);
    }

    .profile-grid { display: flex; gap: 2.5rem; align-items: center; }
    .profile-photo { 
        width: 180px; height: 180px; 
        border-radius: 20px; object-fit: cover; 
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        border: 4px solid white;
    }

    .status-badge {
        padding: 6px 14px; border-radius: 99px; font-size: 0.75rem; font-weight: 700;
        text-transform: uppercase; display: inline-block; margin-bottom: 1rem;
    }
    .status-verified { background: #dcfce7; color: #166534; }
    .status-pending { background: #fef9c3; color: #854d0e; }

    .driver-name { font-size: 2.2rem; font-weight: 800; margin: 0; letter-spacing: -0.025em; color: #1a1a1a; }

    .info-grid { 
        display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
        gap: 1.5rem; margin-top: 2rem; border-top: 1px solid var(--slate-100); padding-top: 2rem;
    }
    .info-item label { display: block; font-size: 0.7rem; color: var(--slate-600); font-weight: 700; text-transform: uppercase; margin-bottom: 4px; }
    .info-item span { font-weight: 600; color: #1a1a1a; display: block; font-size: 1rem; }

    .action-buttons { display: flex; gap: 1rem; margin-top: 1.5rem; }
    .btn-wa { background: var(--success); color: white; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; flex: 1; justify-content: center; }
    .btn-rate { background: var(--primary); color: white; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 700; border: none; cursor: pointer; flex: 1; }

    .section-title { font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px; color: #1a1a1a; }
    .sim-box { 
        background: var(--slate-50); border: 1px solid var(--slate-200); 
        padding: 1.5rem; border-radius: 16px; position: relative; border-left: 5px solid var(--primary);
    }
    .modal {
    display: none; /* Sembunyi secara default */
    position: fixed;
    z-index: 2000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5); /* Latar belakang gelap transparan */
    justify-content: center;
    align-items: center;
}

.modal-content {
    background-color: white;
    padding: 30px;
    border-radius: 20px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

    .mobil-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1.5rem; }
    .mobil-card { border: 1px solid var(--slate-200); border-radius: 16px; overflow: hidden; background: white; }
    .mobil-img { width: 100%; height: 160px; object-fit: cover; }
    .mobil-info { padding: 1.25rem; }

    .rute-row { display: flex; justify-content: space-between; padding: 1.25rem; border-bottom: 1px solid var(--slate-100); align-items: center; }
    .rute-row:last-child { border-bottom: none; }

    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); }
    .modal-content { background: white; margin: 10% auto; padding: 2.5rem; border-radius: 20px; max-width: 500px; position: relative; }

    @media (max-width: 768px) {
        .profile-grid { flex-direction: column; text-align: center; }
        .action-buttons { flex-direction: column; }
    }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo"> SopirPilihan.id</div>
            <ul>
                <li><a href="../index.php">Beranda</a></li>
                <li><a href="daftar_driver.php">Cari Driver</a></li>
            </ul>
        </nav>
    </header>

<div class="container">
    <div class="card">
        <div class="profile-grid">
            <img src="<?php echo !empty($driver['foto_profil']) ? '../uploads/drivers/profile/'.$driver['foto_profil'] : '../assets/images/default-avatar.png'; ?>" 
                 class="profile-photo" onerror="this.src='../assets/images/default-avatar.png'">
            
            <div style="flex: 1;">
                <span class="status-badge <?php echo $driver['status_verifikasi'] == 'verified' ? 'status-verified' : 'status-pending'; ?>">
                    🟢 <?php echo strtoupper($driver['status_verifikasi']); ?>
                </span>
                <h1 class="driver-name"><?php echo htmlspecialchars($driver['nama_lengkap']); ?></h1>
                
                <div style="color: var(--warning); font-weight: 700; margin: 10px 0; font-size: 1.1rem;">
                    ★ <?php echo $driver['avg_rating'] ? number_format($driver['avg_rating'], 1) : '0.0'; ?>
                    <span style="color: var(--slate-600); font-weight: 400; font-size: 0.9rem;"> (<?php echo $driver['total_rating']; ?> ulasan)</span>
                </div>

                <div class="action-buttons">
                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $driver['no_whatsapp']); ?>" 
                        class="btn-wa btn-hitung-klik" 
                        data-id="<?php echo $id_driver; ?>" 
                        target="_blank">
                        Hubungi WhatsApp
                    </a>
                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'pelanggan'): ?>
                        <button type="button" class="btn-rate" onclick="openRatingModal()">
                        ⭐ Beri Rating
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <label>Email</label>
                <span><?php echo htmlspecialchars($driver['email']); ?></span>
            </div>
            <div class="info-item">
                <label>Nomor HP</label>
                <span><?php echo htmlspecialchars($driver['no_hp']); ?></span>
            </div>
            <div class="info-item">
                <label>Domisili</label>
                <span><?php echo htmlspecialchars($driver['alamat']); ?></span>
            </div>
            <div class="info-item">
                <label>Terdaftar Sejak</label>
                <span><?php echo date('d M Y', strtotime($driver['tgl_daftar'])); ?></span>
            </div>
        </div>
        
        <?php if(!empty($driver['deskripsi'])): ?>
        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--slate-100);">
            <label style="font-size: 0.7rem; font-weight: 700; color: var(--slate-600); text-transform: uppercase;">Tentang Driver</label>
            <p style="margin-top: 5px; color: var(--slate-600); font-size: 0.95rem;"><?php echo nl2br(htmlspecialchars($driver['deskripsi'])); ?></p>
        </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2 class="section-title"> Lisensi Mengemudi (SIM)</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
            <?php if(mysqli_num_rows($result_sim) > 0): ?>
                <?php while($sim = mysqli_fetch_assoc($result_sim)): ?>
                <div class="sim-box">
                    <div style="font-weight: 800; color: var(--primary); margin-bottom: 10px;">GOLONGAN SIM: <?php echo htmlspecialchars($sim['jenis_sim']); ?></div>
                    <div style="display: flex; justify-content: space-between;">
                        <div class="info-item">
                            <label>Nomor SIM</label>
                            <span><?php echo htmlspecialchars($sim['no_sim']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Masa Berlaku</label>
                            <span style="<?php echo (strtotime($sim['tanggal_berlaku']) < time()) ? 'color: red;' : ''; ?>">
                                <?php echo date('d/m/Y', strtotime($sim['tanggal_berlaku'])); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Data SIM belum tersedia.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <h2 class="section-title"> Unit Kendaraan</h2>
        <div class="mobil-grid">
            <?php while($mobil = mysqli_fetch_assoc($result_mobil)): ?>
            <div class="mobil-card">
                <img src="../uploads/mobil/<?php echo $mobil['foto_mobil']; ?>" class="mobil-img" onerror="this.src='../assets/images/default-car.png'">
                <div class="mobil-info">
                    <h4 style="margin: 0;"><?php echo htmlspecialchars($mobil['merk'].' '.$mobil['model']); ?></h4>
                    <p style="font-size: 0.85rem; color: var(--slate-600); margin: 5px 0;">
                        <?php echo htmlspecialchars($mobil['nama_jenis']); ?> • <?php echo $mobil['kapasitas_penumpang']; ?> Seat • <?php echo $mobil['tahun']; ?>
                    </p>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div class="card" style="padding: 0;">
        <div style="padding: 2rem 2.5rem 1rem;">
            <h2 class="section-title"> Rute & Estimasi Biaya</h2>
        </div>
        <div style="background: var(--slate-50); border-radius: 0 0 20px 20px;">
            <?php while($rute = mysqli_fetch_assoc($result_rute)): ?>
            <div class="rute-row">
                <div style="font-weight: 600;">
                    <?php echo htmlspecialchars($rute['kota_asal']); ?> <span style="color: var(--primary);">➔</span> <?php echo htmlspecialchars($rute['kota_tujuan']); ?>
                </div>
                <div style="font-weight: 800; color: var(--primary); font-size: 1.1rem;">
                    <?php echo $rute['harga_estimasi'] ? 'Rp '.number_format($rute['harga_estimasi'], 0, ',', '.') : 'Nego'; ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div class="card">
        <h2 class="section-title"> Ulasan Pelanggan</h2>
        <?php if(mysqli_num_rows($result_rating) > 0): ?>
            <?php while($review = mysqli_fetch_assoc($result_rating)): ?>
            <div style="padding: 1.5rem 0; border-bottom: 1px solid var(--slate-100);">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 10px;">
                    
                    <?php 
                    // LOGIKA PERBAIKAN FOTO PELANGGAN
                    $foto_p = $review['foto_profil'];
                    $path_p = "../uploads/pelanggan/";
                    if(!empty($foto_p) && file_exists($path_p . $foto_p)): ?>
                        <img src="<?= $path_p . $foto_p; ?>" style="width:45px; height:45px; border-radius:50%; object-fit:cover; border: 1px solid var(--slate-200);">
                    <?php else: ?>
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($review['nama_lengkap']); ?>&background=random" style="width:45px; border-radius:50%;">
                    <?php endif; ?>

                    <div>
                        <div style="font-weight: 700;"><?php echo htmlspecialchars($review['nama_lengkap']); ?></div>
                        <div style="color: var(--warning); font-size: 0.8rem;">
                            <?php for($i=1; $i<=5; $i++) echo $i <= $review['rating'] ? '★' : '☆'; ?>
                            <span style="color: var(--slate-600); margin-left: 8px; font-weight: 400;"><?php echo date('d/m/Y', strtotime($review['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
                <p style="margin: 0; color: var(--slate-600); font-style: italic;">"<?php echo nl2br(htmlspecialchars($review['komentar'])); ?>"</p>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Belum ada ulasan.</p>
        <?php endif; ?>
    </div>
</div>

<div id="ratingModal" class="modal">
    <div class="modal-content">
        
        <h2 style="margin-top:0;">Beri Rating</h2>
        <form action="submit_rating.php" method="POST">
            <input type="hidden" name="id_driver" value="<?php echo $id_driver; ?>">
            <div style="margin-bottom: 15px;">
                <label style="display:block; margin-bottom:5px;">Rating</label>
                <select name="rating" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--slate-200);">
                    <option value="5">⭐⭐⭐⭐⭐ Sangat Baik</option>
                    <option value="4">⭐⭐⭐⭐ Baik</option>
                    <option value="3">⭐⭐⭐ Cukup</option>
                    <option value="2">⭐⭐ Kurang</option>
                    <option value="1">⭐ Sangat Buruk</option>
                </select>
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display:block; margin-bottom:5px;">Ulasan Anda</label>
                <textarea name="komentar" rows="4" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--slate-200);" placeholder="Ceritakan pengalaman Anda..."></textarea>
            </div>
            <div style="display:flex; gap:10px;">
                <button type="submit" class="btn-rate">Kirim Rating</button>
                <button type="button" onclick="closeRatingModal()" style="background:var(--slate-200); border:none; padding:10px 20px; border-radius:12px; cursor:pointer;">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
// 1. Fungsi untuk Modal Rating
function openRatingModal() {
    const modal = document.getElementById('ratingModal');
    if (modal) modal.style.display = 'flex';
}

function closeRatingModal() {
    const modal = document.getElementById('ratingModal');
    if (modal) modal.style.display = 'none';
}

// Menutup modal jika pengguna mengklik di luar kotak modal
window.onclick = function(event) {
    const modal = document.getElementById('ratingModal');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

// 2. Fungsi untuk Hitung Klik WhatsApp
document.querySelectorAll('.btn-hitung-klik').forEach(button => {
    button.addEventListener('click', function() {
        const driverId = this.getAttribute('data-id');
        
        fetch('/platform_SopirPilihan/admin/hitung_klik_ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + driverId
        })
        .then(response => response.text())
        .then(data => {
            console.log('Klik detail tercatat!');
        })
        .catch(err => console.error('Error:', err));
    });
});
</script>
</body>
</html>