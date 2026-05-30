<?php
include '../config/database.php';

if (isset($_POST['kirim'])) {
    $nama = $_POST['nama'];
    $wa = $_POST['wa'];
    $asal = $_POST['asal'];
    $tujuan = $_POST['tujuan'];
    $berat = $_POST['berat'];

    $query = "INSERT INTO pengiriman_barang (nama_pengirim, wa_pengirim, kota_asal, kota_tujuan, berat_kg) 
              VALUES ('$nama', '$wa', '$asal', '$tujuan', '$berat')";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Data pengiriman berhasil dikirim! Admin akan segera menghubungi Anda.'); window.location='pengiriman_barang.php';</script>";
    }
}
?>

<form action="" method="POST">
    <input type="text" name="nama" placeholder="Nama Pengirim" required>
    <input type="text" name="wa" placeholder="Nomor WhatsApp (Aktif)" required>
    <input type="text" name="asal" placeholder="Kota Asal" required>
    <input type="text" name="tujuan" placeholder="Kota Tujuan" required>
    <input type="number" name="berat" placeholder="Estimasi Berat (Kg)" required>
    <button type="submit" name="kirim">Ajukan Pengiriman</button>
</form>