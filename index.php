<?php /* ====================================================
PHP Processor
========================================================== */
// Root Url
$svr_port = $_SERVER['SERVER_PORT'] === '443' ? 'https://' : ( $_SERVER['SERVER_PORT'] === '80' ? 'http://' : '' );
$host = $_SERVER['HTTP_HOST'] == 'localhost' ? $_SERVER['HTTP_HOST'] . '/tagihan/' :  $_SERVER['HTTP_HOST'] . '/';
$root = $svr_port . $host;

// Set zona waktu ke wib Jakarta
date_default_timezone_set('Asia/Jakarta');
require('inc/cnfg.php');
session_start();
if(isset($_POST['UserName'])){
	$username 		= filter_input(INPUT_POST, 'UserName', FILTER_SANITIZE_STRING);
	$userpassword 	= filter_input(INPUT_POST, 'UserPassword', FILTER_SANITIZE_STRING);
	$userpsw 		= array('superadmin' => 'kpnunibraw');
	if(key_exists($username, $userpsw) && ($userpsw[$username] == $userpassword)){
		$_SESSION['username'] = $username;
	} else {
		$user_kueri = 'select username.`password` from username where username = \'' . $username . '\'';
		$user_hasil = mysqli_fetch_assoc( mysqli_query( $koneksi_proses,$user_kueri ) );
		if($userpassword == $user_hasil['password']){
			$_SESSION['username'] = $username;
		}
	}
}
require('inc/header.php');
if(!isset($_SESSION['username'])){
	require('inc/login.php');
} else {
	require('inc/menu.php');
	echo '<div class="container-fluid">';
	if($_SESSION['username'] == 'superadmin'){
		if($_GET['menu'] == 'cnfg'){ require('config/config.php'); }
		if($_GET['menu'] == 'set_tag'){ require('config/set-tag.php'); }
	}
	if(isset($_GET['menu']) && $_GET['menu'] == 'tghn'){ require('tagihan/tagihan.php'); }
	echo '</div>';
}
require('inc/footer.php');