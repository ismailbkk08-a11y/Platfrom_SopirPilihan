<?php
session_start();
include '../config/database.php';
$id_user = $_SESSION['id_user'];
$data = mysqli_query($conn, "SELECT * FROM pelanggan WHERE id_user = $id_user");
$p = mysqli_fetch_assoc($data);

if(isset($_POST['update'])) {
    $nama = $_POST['nama'];
    $wa = $_POST['no_wa'];
    mysqli_query($conn, "UPDATE pelanggan SET nama_lengkap='$nama', no_wa='$wa' WHERE id_user=$id_user");
    header("Location: profil.php?msg=sukses");
}
?>