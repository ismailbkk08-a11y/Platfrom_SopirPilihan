<?php
// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'platform_sopirpilihan');

// Koneksi ke database
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, "utf8");

// Fungsi untuk membersihkan input
if (!function_exists('clean_input')) {
    function clean_input($data) {
        global $conn;
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        $data = mysqli_real_escape_string($conn, $data);
        return $data;
    }
}

if (!function_exists('redirect')) {
    function redirect($url) {
        header("Location: $url");
        exit();
    }
}

// Fungsi untuk cek login
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Fungsi untuk cek role
function check_role($role) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] != $role) {
        redirect('../auth/login.php');
    }
}

// Fungsi untuk get rating driver
function get_driver_rating($id_driver) {
    global $conn;
    $query = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_rating 
              FROM rating_driver WHERE id_driver = $id_driver";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}
?>