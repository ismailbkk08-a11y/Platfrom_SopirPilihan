<?php
require_once '../config/database.php';

if (isset($_GET['id'])) {
    $id_driver = mysqli_real_escape_string($conn, $_GET['id']);
    
    // 1. Tambah jumlah klik di database
    $update = "UPDATE driver SET jumlah_klik_wa = jumlah_klik_wa + 1 WHERE id_driver = '$id_driver'";
    mysqli_query($conn, $update);
    
    // 2. Ambil nomor WA driver untuk redirect
    $res = mysqli_query($conn, "SELECT no_whatsapp FROM driver WHERE id_driver = '$id_driver'");
    $data = mysqli_fetch_assoc($res);
    
    if ($data) {
        $no_wa = $data['no_whatsapp'];
        // Bersihkan karakter non-angka
        $no_wa = preg_replace('/[^0-9]/', '', $no_wa);
        // Pastikan format internasional (ganti 0 di depan dengan 62)
        if (substr($no_wa, 0, 1) === '0') {
            $no_wa = '62' . substr($no_wa, 1);
        }
        
        header("Location: https://wa.me/" . $no_wa);
        exit;
    }
}
header("Location: kelola_driver.php");
?>