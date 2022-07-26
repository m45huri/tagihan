<?php /* =================================================
Halaman konfigurasi untuk setting tagihan
======================================================= */
// Simpan Konfigurasi yang sudah di buat
if(isset($_POST['tghn_save'])){
$nama_pimpinan 		= filter_input(INPUT_POST, 'NamaPimpinan', FILTER_SANITIZE_STRING);
$jabatan_pimpinan	= filter_input(INPUT_POST, 'JabatanPimpinan', FILTER_SANITIZE_STRING);
$ttd_pimpinan		= filter_input(INPUT_POST, 'TtdPimpinan', FILTER_SANITIZE_STRING);
$nama_kasir			= filter_input(INPUT_POST, 'NamaKasir', FILTER_SANITIZE_STRING);
$jabatan_kasir		= filter_input(INPUT_POST, 'JabatanKasir', FILTER_SANITIZE_STRING);
$ttd_kasir			= filter_input(INPUT_POST, 'TtdKasir', FILTER_SANITIZE_STRING);
$new_file_tghn		= fopen('inc/tghn.php', 'w');
$write_file			= '<?php /* ====================================================
Konfigurasi untuk tagihan
========================================================== */

// TTD
$tanda_tangan = array(
	\'NamaPimpinan\' 		=> \'' . $nama_pimpinan . '\',
	\'JabatanPimpinan\' 	=> \'' . $jabatan_pimpinan . '\',
	\'TtdPimpinan\' 	=> \'' . $ttd_pimpinan . '\',
	\'NamaKasir\' 		=> \'' . $nama_kasir . '\',
	\'JabatanKasir\' 		=> \'' . $jabatan_kasir . '\',
	\'TtdKasir\' 	=> \'' . $ttd_kasir . '\',
);';
fwrite($new_file_tghn, $write_file);
fclose($new_file_tghn);
}

// Ambil data konfigurasi tagihan
$file_tghn = fopen('inc/tghn.php', 'r');
$text_tghn = array();
while (!feof($file_tghn) ){
	$line = fgets($file_tghn);
	$part = explode('\'', $line);
	if($part[1] != ''){
		$text_tghn[] = $part[1];
		$text_tghn[] = $part[3];
	}
}
fclose($file_tghn);
?>
<h4 class="my-3">Halaman konfigurasi untuk setting cetak tagihan</h4>
<form action="" method="POST">
	<div class="row">
		<div class="col-md-6">
			<div class="card mb-4">
				<div class="card-header">Set Pimpinan</div>
				<div class="card-body">
					<div class="form-group row">
						<label for="NamaPimpinan" class="col-sm-3">Nama</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" id="NamaPimpinan" name="NamaPimpinan" value="<?php echo $text_tghn[1]; ?>">
						</div>
					</div>
					<div class="form-group row">
						<label for="JabatanPimpinan" class="col-sm-3">Jabatan</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" id="JabatanPimpinan" name="JabatanPimpinan" value="<?php echo $text_tghn[3]; ?>">
						</div>
					</div>
					<div class="form-group row">
						<label for="TtdPimpinan" class="col-sm-3">TTD</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" id="TtdPimpinan" name="TtdPimpinan" value="<?php echo $text_tghn[5]; ?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="card mb-4">
				<div class="card-header">Set Kasir</div>
				<div class="card-body">
					<div class="form-group row">
						<label for="NamaKasir" class="col-sm-3">Nama</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" id="NamaKasir" name="NamaKasir" value="<?php echo $text_tghn[7]; ?>">
						</div>
					</div>
					<div class="form-group row">
						<label for="JabatanKasir" class="col-sm-3">Jabatan</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" id="JabatanKasir" name="JabatanKasir" value="<?php echo $text_tghn[9]; ?>">
						</div>
					</div>
					<div class="form-group row">
						<label for="TtdKasir" class="col-sm-3">TTD</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" id="TtdKasir" name="TtdKasir" value="<?php echo $text_tghn[11]; ?>">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<input type="hidden" name="tghn_save">
	<button type="submit" class="btn btn-success float-right">Simpan Konfigurasi</button>
</form>