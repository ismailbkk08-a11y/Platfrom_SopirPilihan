<?php
// Pastikan path ke database.php benar (naik 1 tingkat ke folder config)
require_once '../config/database.php';

if (isset($_POST['id'])) {
    $id_driver = mysqli_real_escape_string($conn, $_POST['id']);
    
    // Query untuk update
    $sql = "UPDATE driver SET jumlah_klik_wa = jumlah_klik_wa + 1 WHERE id_driver = '$id_driver'";
    
    if(mysqli_query($conn, $sql)) {
        echo "success";
    } else {
        echo "error: " . mysqli_error($conn);
    }
} else {
    echo "no_id_received";
}
?>