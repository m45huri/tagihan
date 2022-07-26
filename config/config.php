<?php /* =================================================
Halaman konfigurasi untuk setting koneksi
======================================================= */ 
// Simpan Konfigurasi yang dudah di buat
if(isset($_POST['cnfg_save'])){
$server_ambil 	= filter_input(INPUT_POST, 'ServerAmbil', FILTER_SANITIZE_STRING);
$data_ambil		= filter_input(INPUT_POST, 'DataAmbil', FILTER_SANITIZE_STRING);
$user_ambil		= filter_input(INPUT_POST, 'UserAmbil', FILTER_SANITIZE_STRING);
$pass_ambil		= filter_input(INPUT_POST, 'PassAmbil', FILTER_SANITIZE_STRING);
$port_ambil		= filter_input(INPUT_POST, 'PortAmbil', FILTER_SANITIZE_STRING);
$server_proses 	= filter_input(INPUT_POST, 'ServerProses', FILTER_SANITIZE_STRING);
$data_proses 	= filter_input(INPUT_POST, 'DataProses', FILTER_SANITIZE_STRING);
$user_proses	= filter_input(INPUT_POST, 'UserProses', FILTER_SANITIZE_STRING);
$pass_proses	= filter_input(INPUT_POST, 'PassProses', FILTER_SANITIZE_STRING);
$port_proses	= filter_input(INPUT_POST, 'PortProses', FILTER_SANITIZE_STRING);
$new_handle_f	= fopen('inc/cnfg.php', 'w');
$write_file 	= '<?php /* ====================================================
Konfigurasi untuk koneksi ke database
========================================================== */

// Koneksi ambil data 
$server_ambil	= \'' . $server_ambil . '\';
$data_ambil		= \'' . $data_ambil . '\';
$user_ambil		= \'' . $user_ambil . '\';
$pass_ambil		= \'' . $pass_ambil . '\';
$port_ambil		= \'' . $port_ambil . '\';
$koneksi_ambil	= mysqli_connect($server_ambil, $user_ambil, $pass_ambil, $data_ambil, $port_ambil);

// Koneksi proses data
$server_proses	= \'' . $server_proses . '\';
$data_proses	= \'' . $data_proses . '\';
$user_proses	= \'' . $user_proses . '\';
$pass_proses	= \'' . $pass_proses . '\';
$port_proses	= \'' . $port_proses . '\';
$koneksi_proses	= mysqli_connect($server_proses, $user_proses, $pass_proses, $data_proses, $port_proses);';
fwrite($new_handle_f, $write_file);
fclose($new_handle_f);
}

// Ambil data konfigurasi dari file konfigurasi
$file_handle = fopen('inc/cnfg.php', 'r');
$text_handle = array();
while (!feof($file_handle) ){
	$line = fgets($file_handle);
	$part = explode('\'', $line);
	if($part[1] != ''){$text_handle[] = $part[1];}
}
fclose($file_handle);
?>
<h4 class="my-3">Halaman konfigurasi untuk setting koneksi ke database</h4>
<form action="" method="POST">
	<div class="row">
		<div class="col-md-6">
			<div class="card mb-4">
				<div class="card-header">Koneksi untuk ambil data</div>
				<div class="card-body">
					<div class="form-group row">
						<label for="ServerAmbil" class="col-sm-3">Server</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" id="ServerAmbil" name="ServerAmbil" value="<?php echo $text_handle[0]; ?>">
						</div>
					</div>
					<div class="form-group row">
						<label for="DataAmbil" class="col-sm-3">Data Base</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" id="DataAmbil" name="DataAmbil" value="<?php echo $text_handle[1]; ?>">
						</div>
					</div>
					<div class="form-group row">
						<label for="UserAmbil" class="col-sm-3">User Name</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" id="UserAmbil" name="UserAmbil" value="<?php echo $text_handle[2]; ?>">
						</div>
					</div>
					<div class="form-group row">
						<label for="PassAmbil" class="col-sm-3">Password</label>
						<div class="col-sm-9">
							<input type="password" class="form-control" id="PassAmbil" name="PassAmbil" value="<?php echo $text_handle[3]; ?>">
						</div>
					</div>
					<div class="form-group row">
						<label for="PortAmbil" class="col-sm-3">Port</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" id="PortAmbil" name="PortAmbil" value="<?php echo $text_handle[4]; ?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="card mb-4">
				<div class="card-header">Koneksi untuk proses data</div>
				<div class="card-body">
					<div class="form-group row">
						<label for="ServerProses" class="col-sm-3">Server</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" id="ServerProses" name="ServerProses" value="<?php echo $text_handle[5]; ?>">
						</div>
					</div>
					<div class="form-group row">
						<label for="DataProses" class="col-sm-3">Data Base</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" id="DataProses" name="DataProses" value="<?php echo $text_handle[6]; ?>">
						</div>
					</div>
					<div class="form-group row">
						<label for="UserProses" class="col-sm-3">User Name</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" id="UserProses" name="UserProses" value="<?php echo $text_handle[7]; ?>">
						</div>
					</div>
					<div class="form-group row">
						<label for="PassProses" class="col-sm-3">Password</label>
						<div class="col-sm-9">
							<input type="password" class="form-control" id="PassProses" name="PassProses" value="<?php echo $text_handle[8]; ?>">
						</div>
					</div>
					<div class="form-group row">
						<label for="PortProses" class="col-sm-3">Port</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" id="PortProses" name="PortProses" value="<?php echo $text_handle[9]; ?>">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<input type="hidden" name="cnfg_save">
	<button type="submit" class="btn btn-success float-right">Simpan Konfigurasi</button>
</form>