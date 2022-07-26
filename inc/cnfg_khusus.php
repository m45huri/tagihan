<?php /* ====================================================
Konfigurasi untuk koneksi ke database
========================================================== */

if( isset($_GET['fakultas']) ){
	$cut_of_tgl_7 = ['A','C','D','F','L','M','Q','R'];
	$cut_of_tgl_8 = ['S','EE'];
	$cut_of_tgl_9 = ['Y','DD'];
	$cut_of_tgl_11 = ['E'];
	$cut_of_tgl_13 = ['B','O','CC'];
	$cut_of_tgl_15 = ['H'];
	$cut_of_tgl_21 = ['BU'];
	if( in_array($_GET['fakultas'], $cut_of_tgl_7) ){ $data_base_pakai = 'ubsptgl7'; } else
	if( in_array($_GET['fakultas'], $cut_of_tgl_8) ){ $data_base_pakai = 'ubsptgl8'; } else
	if( in_array($_GET['fakultas'], $cut_of_tgl_9) ){ $data_base_pakai = 'ubsptgl9'; } else
	if( in_array($_GET['fakultas'], $cut_of_tgl_11) ){ $data_base_pakai = 'ubsptgl11'; } else
	if( in_array($_GET['fakultas'], $cut_of_tgl_13) ){ $data_base_pakai = 'ubsptgl13'; } else
	if( in_array($_GET['fakultas'], $cut_of_tgl_15) ){ $data_base_pakai = 'ubsptgl15'; } else
	if( in_array($_GET['fakultas'], $cut_of_tgl_21) ){ $data_base_pakai = 'ubsptgl21'; } else
	{ $data_base_pakai = 'ubsptagihan'; }
} else { $data_base_pakai = 'ubsptagihan'; }


// Koneksi ambil data 
$server_ambil	= 'localhost';
$data_ambil		= $data_base_pakai;
$user_ambil		= 'root';
$pass_ambil		= '5ubekt1';
$port_ambil		= '3306';
$koneksi_ambil	= mysqli_connect($server_ambil, $user_ambil, $pass_ambil, $data_ambil, $port_ambil);

// Koneksi proses data
$server_proses	= 'localhost';
$data_proses	= $data_base_pakai;
$user_proses	= 'root';
$pass_proses	= '5ubekt1';
$port_proses	= '3306';
$koneksi_proses	= mysqli_connect($server_proses, $user_proses, $pass_proses, $data_proses, $port_proses);