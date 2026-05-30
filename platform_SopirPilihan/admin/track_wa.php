<?php
require_once '../config/database.php';

if (isset($_GET['id_driver']) && isset($_GET['phone'])) {
    $id_driver = intval($_GET['id_driver']);
    $phone = preg_replace('/[^0-9]/', '', $_GET['phone']);
    $pesan = isset($_GET['text']) ? $_GET['text'] : '';

    // Update jumlah klik di database
    mysqli_query($conn, "UPDATE driver SET jumlah_klik_wa = jumlah_klik_wa + 1 WHERE id_driver = $id_driver");

    // Redirect ke WhatsApp
    $wa_url = "https://wa.me/$phone?text=" . $pesan;
    header("Location: $wa_url");
    exit;
}