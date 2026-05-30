<?php
session_start();
include '../config/database.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    
    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['password'])) {
            $_SESSION['login'] = true;
            $_SESSION['id_user'] = $row['id_user'];
            $_SESSION['role'] = $row['role'];

            if ($row['role'] == 'admin') header("Location: ../admin/dashboard.php");
            elseif ($row['role'] == 'driver') header("Location: ../driver/dashboard.php");
            else header("Location: ../pelanggan/dashboard.php");
            exit;
        }
    }
    header("Location: login.php?pesan=gagal");
}
?>