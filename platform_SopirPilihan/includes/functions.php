<?php
function query($query) {
    global $conn;
    $result = mysqli_query($conn, $query);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function uploadFoto($namaFile, $tmpName, $folder) {
    $ekstensiValid = ['jpg', 'jpeg', 'png'];
    $ekstensi = explode('.', $namaFile);
    $ekstensi = strtolower(end($ekstensi));
    
    if(!in_array($ekstensi, $ekstensiValid)) return false;
    
    $namaBaru = uniqid() . '.' . $ekstensi;
    move_uploaded_file($tmpName, '../uploads/' . $folder . '/' . $namaBaru);
    return $namaBaru;
}
?>