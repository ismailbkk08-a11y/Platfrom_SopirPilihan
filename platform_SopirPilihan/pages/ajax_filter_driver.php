<?php
require_once '../config/database.php';

if (isset($_POST['asal']) && isset($_POST['tujuan'])) {
    $asal = mysqli_real_escape_string($conn, $_POST['asal']);
    $tujuan = mysqli_real_escape_string($conn, $_POST['tujuan']);

    // Mengambil nama driver dari tabel driver berdasarkan rute di tabel rute_driver
    $query = "SELECT d.id_driver, d.nama_lengkap 
              FROM rute_driver rd
              JOIN driver d ON rd.id_driver = d.id_driver
              WHERE rd.kota_asal = '$asal' AND rd.kota_tujuan = '$tujuan'";
              
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        echo '<option value="">-- Pilih Driver Tersedia --</option>';
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<option value="'.$row['id_driver'].'">'.$row['nama_lengkap'].'</option>';
        }
    } else {
        echo '<option value="">Maaf, rute ini belum tersedia driver</option>';
    }
}
?>