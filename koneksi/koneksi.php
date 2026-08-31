<?php

$username = "root";
$password = ""; 
$hostname = "localhost";
$database = "db_nilai";

$koneksi = mysqli_connect($hostname, $username, $password, $database);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
   //echo"koneksi database berhasil"//

?>