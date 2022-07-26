<?php /* ====================================================
Konfigurasi untuk koneksi ke database
========================================================== */

// Koneksi ambil data 
$server_ambil	= 'localhost';
$data_ambil		= 'ubsptagihan';
$user_ambil		= 'root';
$pass_ambil		= '5ubekt1';
$port_ambil		= '3306';
$koneksi_ambil	= mysqli_connect($server_ambil, $user_ambil, $pass_ambil, $data_ambil, $port_ambil);

// Koneksi proses data
$server_proses	= 'localhost';
$data_proses	= 'ubsptagihan';
$user_proses	= 'root';
$pass_proses	= '5ubekt1';
$port_proses	= '3306';
$koneksi_proses	= mysqli_connect($server_proses, $user_proses, $pass_proses, $data_proses, $port_proses);