<?php
session_start();
require_once '../config/database.php';

// Cek apakah user adalah pelanggan
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'pelanggan') {
    die("Anda harus login sebagai pelanggan untuk memberikan rating!");
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_driver = intval($_POST['id_driver']);
    $id_pelanggan = $_SESSION['profile_id'];
    $rating = intval($_POST['rating']);
    $komentar = clean_input($_POST['komentar']);
    
    // Cek apakah sudah pernah memberi rating
    $check_query = "SELECT * FROM rating_driver 
                    WHERE id_driver = $id_driver AND id_pelanggan = $id_pelanggan";
    $check_result = mysqli_query($conn, $check_query);
    
    if(mysqli_num_rows($check_result) > 0) {
        // Update rating yang sudah ada
        $update_query = "UPDATE rating_driver 
                        SET rating = $rating, komentar = '$komentar', created_at = NOW()
                        WHERE id_driver = $id_driver AND id_pelanggan = $id_pelanggan";
        mysqli_query($conn, $update_query);
        $message = "Rating Anda berhasil diperbarui!";
    } else {
        // Insert rating baru
        $insert_query = "INSERT INTO rating_driver (id_driver, id_pelanggan, rating, komentar) 
                        VALUES ($id_driver, $id_pelanggan, $rating, '$komentar')";
        mysqli_query($conn, $insert_query);
        $message = "Terima kasih atas rating Anda!";
    }
    
    // Redirect kembali ke halaman detail driver
    header("Location: detail_driver.php?id=$id_driver&success=" . urlencode($message));
    exit();
} else {
    redirect('../index.php');
}
?>