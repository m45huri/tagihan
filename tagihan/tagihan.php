<?php /* ====================================================
Buat Tagihan
========================================================== */ 

// Setting variable tagihan
$tab 			= isset($_GET['tab']) ? $_GET['tab'] : '1';
$pembiayaan 	= isset($_GET['pembiayaan']) ? $_GET['pembiayaan'] : [];
$fakultas 		= isset($_GET['fakultas']) ? $_GET['fakultas'] : '';
$periode 		= isset($_GET['periode']) ? $_GET['periode'] : date( 'Y-m', strtotime('+1 month') );
$kwitansi		= isset($_GET['kwitansi']) ? strtoupper( str_replace(' ', '', $_GET['kwitansi']) ) : '';
$custom_pokok	= isset($_GET['custom-pokok']) ? str_replace(' ', '', $_GET['custom-pokok']) : '';
$custom_jasa	= isset($_GET['custom-jasa']) ? str_replace(' ', '', $_GET['custom-jasa']) : '';
$periode_akhir	= isset($_GET['periode-akhir']) ? $_GET['periode-akhir'] : 20;
$kurangjasa		= isset($_GET['kurangjasa']) ? $_GET['kurangjasa'] : '';
$tabungan		= isset($_GET['tabungan']) ? $_GET['tabungan'] : [] ;
$rekening		= isset($_GET['rekening']) ? strtoupper( str_replace(' ', '', $_GET['rekening']) ) : '';
$nominal		= isset($_GET['nominal']) ? str_replace(' ', '', $_GET['nominal']) : '';
$bariskosong	= isset($_GET['bariskosong']) ? $_GET['bariskosong'] : '';
$kolomfaktur	= isset($_GET['kolomfaktur']) ? $_GET['kolomfaktur'] : '';
$ttd 			= isset($_GET['ttd']) ? $_GET['ttd'] : '';
$url_import 	= explode('?', $_SERVER['REQUEST_URI']);

// Menambahkan tagihan custom otomatis untuk PLT
require('settings-tagihan-khusus-plt.php');

// Fungsi tagihan
function list_fakultas($koneksi, $kode = ''){ // output array kode fakultas
	$fak_kueri = $kode == '' ? 'select * from dept' : 'select * from dept where kode =\'' . $kode . '\'';
	$fak_hasil = mysqli_query($koneksi,$fak_kueri);
	$daftar_fak = array();
	while ($fak_array = mysqli_fetch_assoc($fak_hasil)){
		if ($kode == ''){
			$daftar_fak[$fak_array['kode']] = $fak_array;
		} else { $daftar_fak[$fak_array['kode']] = $fak_array['keterangan']; }
	}
	ksort($daftar_fak);
	return $daftar_fak;
}
function list_periode($periode, $id_select = '', $select = 'Y'){ // Y for output html select OR N for output string one periode
	$periode_angka = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');
	$periode_huruf = array('', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
	$periode_pilih = explode('-', $periode);
	if($select == 'Y'){
		echo '<select id="' . $id_select . '" name="' . $id_select . '" class="custom-select">';
		if ( date('m') == '11' ) {
			echo '<option value="' . date('Y') . '-11" ';
			if( $periode_pilih[1] == date('m') ){ echo 'selected="selected"'; }
			echo '>November ' . date('Y') . '</option><option value="' . date('Y') . '-12" ';
			if( $periode_pilih[1] != date('m') ){ echo 'selected="selected"'; }
			echo '>Desember ' . date('Y') . '</option>';
		}
		if ( date('m') == '12' ) { echo '<option value="' . date('Y') . '-12" selected="selected">Desember ' . date('Y') . '</option>'; }
		foreach ($periode_angka as $periode_bulan){
			echo '<option value="';
			if ( $periode == (date('Y') . '-12') ) { echo $periode_pilih[0] + 1; } else { echo $periode_pilih[0]; }
			echo '-' . $periode_bulan . '"';
			if ( $periode_bulan == $periode_pilih[1] && $periode != (date('Y') . '-12') ){ echo ' selected="selected"'; }
			echo '>' . $periode_huruf[(int)$periode_bulan] . ' ';
			if ( $periode == (date('Y') . '-12') ) { echo $periode_pilih[0] + 1; } else { echo $periode_pilih[0]; }
			echo '</option>';
		}
		echo '</select>';
	} else if($select == 'N'){
		echo $periode_huruf[(int)$periode_pilih[1]] . ' ' . $periode_pilih[0];
	}
}
function ket_kode($koneksi, $kode){ // Mencari keterangan dari kode pembiayaan atau tabungan
	$kueri_kode_pinjaman = 'select keterangan from gpinjam where kode = \'' . $kode . '\'';
	$hasil_kode_pinjaman = mysqli_fetch_assoc( mysqli_query( $koneksi,$kueri_kode_pinjaman ) );
	if($hasil_kode_pinjaman == ''){
		$kueri_kode_tabungan = 'select keterangan from gsimpan where kode = \'' . $kode . '\'';
		$hasil_kode_tabungan = mysqli_fetch_assoc( mysqli_query( $koneksi,$kueri_kode_tabungan ) );
	}
	$keterangan = $hasil_kode_pinjaman != '' ? $hasil_kode_pinjaman : $hasil_kode_tabungan;
	return $keterangan['keterangan'];
}
?>
<div class="mt-3"></div>
<ul class="nav nav-pills mb-3 dua" id="pills-tab" role="tablist">
	<li class="nav-item">
		<a class="nav-link <?php if ($tab == '1'){echo 'active';} ?>" id="pills-pemb-tab" data-toggle="pill" href="#pembiayaan" role="tab" aria-controls="pembiayaan" aria-selected="<?php if ($tab == '1'){echo 'true';} else {echo 'false';} ?>">Tagihan Pembiayaan</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?php if ($tab == '2'){echo 'active';} ?>" id="pills-tabungan-tab" data-toggle="pill" href="#tabungan" role="tab" aria-controls="tabungan" aria-selected="<?php if ($tab == '2'){echo 'true';} else {echo 'false';} ?>">Tagihan Tabungan</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?php if ($tab == '3'){echo 'active';} ?>" id="pills-rekap-tab" data-toggle="pill" href="#rekap" role="tab" aria-controls="rekap" aria-selected="<?php if ($tab == '3'){echo 'true';} else {echo 'false';} ?>">Rekap Tagihan</a>
	</li>
</ul>
<div class="tab-content tiga" id="pills-tabContent">
	<script type="text/javascript">
		$(document).ready(function(){
			$("#input-manual").collapse('<?php if ($kwitansi != '') {echo'show';} else {echo'hide';} ?>');
			$("#input-tabungan").collapse('<?php if ($rekening != '') {echo'show';} else {echo'hide';} ?>');
			$("#input-manual-rekap").collapse('<?php if ($kwitansi != '') {echo'show';} else {echo'hide';} ?>');
			$("#input-tab-rekap").collapse('<?php if ($rekening != '') {echo'show';} else {echo'hide';} ?>');
		});
	</script>

	<!-- Set Tagihan Pembiayaan -->
	<div class="tab-pane fade <?php if ($tab == '1'){echo 'show active';} ?>" id="pembiayaan" role="tabpanel" aria-labelledby="pills-pemb-tab">
	<div class="card mb-3">
		<div class="card-header">
			<h5>Tagihan Pembiayaan</h5>
		</div>
		<div class="card-body">
			<form action="">
			<input type="hidden" name="menu" value="tghn">
			<input type="hidden" name="tab" value="1">
			<div class="row">
				<div class="col-md-9">
				<div class="row">
					<div class="col-sm-4">
						<div class="form-group">
							<label for="pembiayaan">Jenis Pembiayaan</label><br>
							<?php // Kueri untuk menampilkan jenis pembiayaan
							$kode_pembiayaan_array = array();
							if ($fakultas == '' || $fakultas == '-1'){ // Jika fakultas kosong tampilkan semua yang aktif
								$kueri_pembiayaan = 'select distinct kodesp, lunas from mpinjam';
								$kueri_pembiayaan_hasil = mysqli_query($koneksi_ambil,$kueri_pembiayaan);
								while($kodesp_array = mysqli_fetch_assoc($kueri_pembiayaan_hasil)){
									if ( ($kodesp_array['kodesp'] != '') && ($kodesp_array['lunas'] == '') ){
										$kode_pembiayaan_array[] = $kodesp_array['kodesp'];
									}
								}
							} else { // Jika fakultas terpilih tampilkan kode pinjaman yang di miliki fakultas tersebut
								$kueri_pembiayaan_anggota = 'select kode from anggota where unit = \'' . $fakultas . '\'';
								$kueri_pembiayaan_anggota_hasil = mysqli_query($koneksi_ambil,$kueri_pembiayaan_anggota);
								while($pembiayaan_anggota = mysqli_fetch_assoc($kueri_pembiayaan_anggota_hasil)){
									$kueri_pembiayaan = 'select distinct kodesp, lunas from mpinjam where kode = \'' . $pembiayaan_anggota['kode'] . '\' and tr = \'G\'';
									$kueri_pembiayaan_hasil = mysqli_query($koneksi_ambil,$kueri_pembiayaan);
									while($kodesp_array = mysqli_fetch_assoc($kueri_pembiayaan_hasil)){
										if ( ($kodesp_array['kodesp'] != '') && ($kodesp_array['lunas'] == '') ){
											$kode_pembiayaan_array[] = $kodesp_array['kodesp'];
										}
									}
								}
							}
							$kode_pembiayaan_array_unique = array_unique($kode_pembiayaan_array);
							asort($kode_pembiayaan_array_unique);
							$hitung_pembiayaan = 1;
							foreach($kode_pembiayaan_array_unique as $kode_pembiayaan){
								echo '<div class="form-check form-check-inline">';
								echo '<input class="form-check-input" type="checkbox" id="pembiayaan' . $hitung_pembiayaan . '" value="' . $kode_pembiayaan . '" name="pembiayaan[]"';
								if ( in_array($kode_pembiayaan, $pembiayaan) ){echo ' checked="checked"';}
								echo '>';
								echo '<label class="form-check-label" for="pembiayaan' . $hitung_pembiayaan . '">' . $kode_pembiayaan . '</label>';
								echo '</div>';
								$hitung_pembiayaan++;
							}
							?>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<label for="fakultas">Fakultas</label>
							<select id="fakultas" name="fakultas" class="custom-select">
								<option value="-1">Pilih</option>
								<?php // Kueri untuk menampilkan fakultas 
								foreach(list_fakultas($koneksi_ambil) as $list_fakultas){
									echo '<option value="' . $list_fakultas['kode'] . '"';
									if ($list_fakultas['kode'] == $fakultas){echo ' selected="selected"';}
									echo '>' . $list_fakultas['kode'] . ' - ' . $list_fakultas['keterangan'] . '</option>';
								} ?>
							</select>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<label for="periode">Periode</label>
							<?php list_periode($periode, 'periode'); ?>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-9">
						<label>Input nominal manual untuk jumlah yang berbeda tidak mengikuti sistem. Ketiga kolom harus di isi dengan jumlah input yang sama. Jika tidak ingin mengganti salah satunya (pokoknya saja atau jasanya saja) silakan masukkan " " (spasi) kemudian diikuti tanda "," (koma)</label>
					</div>
					<div class="col-sm-3">
						<a href="#" role="button" class="btn btn-outline-secondary btn-sm mb-3" data-toggle="collapse" data-target="#input-manual" style="width: 100%;">Input Nominal Manual</a>
					</div>
				</div>
				<div class="row collapse" id="input-manual">
					<div class="col-sm-12">
						<div class="form-group">
							<label for="kwitansi">NO Kuwitansi (pisahkan dg "," koma)</label>
							<textarea id="kwitansi" name="kwitansi" class="form-control" rows="3"><?php echo $kwitansi; ?></textarea>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="custom-pokok">Pokok (pisahkan dg "," koma)</label>
							<textarea id="custom-pokok" name="custom-pokok" class="form-control" rows="3"><?php echo $custom_pokok; ?></textarea>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="custom-jasa">Jasa (pisahkan dg "," koma)</label>
							<textarea id="custom-jasa" name="custom-jasa" class="form-control" rows="3"><?php echo $custom_jasa; ?></textarea>
						</div>
					</div>
				</div>
				<input type="hidden" value="<?php echo $rekening; ?>" id="rekening" name="rekening">
				<input type="hidden" value="<?php echo $nominal; ?>" id="nominal" name="nominal">
				</div>
				<div class="col-md-3">
					<div class="card border-primary">
						<div class="card-body">
							<div class="form-group">
								<label for="periode-akhir">Tgl Cut Off</label>
								<input class="form-control" value="<?php echo $periode_akhir; ?>" type="text" id="periode-akhir" name="periode-akhir">
							</div>
							<div class="form-group form-check">
								<input class="form-check-input" type="checkbox" value="Y" id="kurangjasa" name="kurangjasa" <?php if ($kurangjasa == 'Y') {echo 'checked="checked"';} ?>>
								<label class="form-check-label" for="kurangjasa">Hitung kekurangan jasa belum terbayar</label>
							</div>
							<div class="form-group form-check">
								<input class="form-check-input" type="checkbox" value="Y" id="ttd" name="ttd" <?php if ($ttd == 'Y') {echo 'checked="checked"';} ?>>
								<label class="form-check-label" for="ttd">TTD dan Stempel</label>
							</div>
							<button type="submit" class="btn btn-primary mb-3" style="width: 100%;">Proses Tagihan Pembiayaan</button>
							<?php if (isset($_GET['tab']) && $_GET['tab'] == '1'){
								$import_pembiayaan = $url_import[0] . '?menu=tghn&tab=' . $tab . '&';
								foreach($pembiayaan as $imp_pmb){
									$import_pembiayaan .= 'pembiayaan%5B%5D=' . $imp_pmb . '&';
								}
								$import_pembiayaan .= 'fakultas=' . $fakultas . '&periode=' . $periode . '&kwitansi=' . $kwitansi . '&custom-pokok=' . $custom_pokok . '&custom-jasa=' . $custom_jasa . '&kurangjasa=' . $kurangjasa . '&periode-akhir=' . $periode_akhir . '&import=Y'; ?>
								<a href="<?php echo $import_pembiayaan; ?>" class="btn btn-outline-primary btn-sm" style="width: 100%;">Set Untuk Import</a>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
			</form>
		</div>
	</div>
	</div>

	<!-- Set Tagihan Tabungan -->
	<div class="tab-pane fade <?php if ($tab == '2'){echo 'show active';} ?>" id="tabungan" role="tabpanel" aria-labelledby="pills-tabungan-tab">
	<div class="card mb-3">
		<div class="card-header">
			<h5>Tagihan Tabungan</h5>
		</div>
		<div class="card-body">
			<form action="">
			<input type="hidden" name="menu" value="tghn">
			<input type="hidden" name="tab" value="2">
			<div class="row">
				<div class="col-md-9">
				<div class="row">
					<div class="col-sm-4">
						<div class="form-group">
							<label for="tabungan">Jenis Tabungan</label><br>
							<?php // Kueri untuk menampilkan jenis tabungan
							$kode_tabungan_array = array();
							if ($fakultas == '' || $fakultas == '-1'){ // Jika fakultas kosong tampilkan semua yang aktif
								$kueri_tabungan = 'select distinct kodesp, status from tabungan where tr = \'G\'';
								$kueri_tabungan_hasil = mysqli_query($koneksi_ambil,$kueri_tabungan);
								while($kodesp_array = mysqli_fetch_assoc($kueri_tabungan_hasil)){
									if( ($kodesp_array['kodesp'] != '') && ($kodesp_array['status'] == '') ){ $kode_tabungan_array[] = $kodesp_array['kodesp']; }
								}
							} else { // Jika fakultas terpilih tampilkan kode tabungan yang di miliki fakultas tersebut
								$kueri_tabungan_anggota = 'select kode from anggota where unit = \'' . $fakultas . '\'';
								$kueri_tabungan_anggota_hasil = mysqli_query($koneksi_ambil,$kueri_tabungan_anggota);
								while($tabungan_anggota = mysqli_fetch_assoc($kueri_tabungan_anggota_hasil)){
									$kueri_tabungan = 'select distinct kodesp, status from tabungan where kode = \'' . $tabungan_anggota['kode'] . '\' and tr = \'G\'';
									$kueri_tabungan_hasil = mysqli_query($koneksi_ambil,$kueri_tabungan);
									while($kodesp_array = mysqli_fetch_assoc($kueri_tabungan_hasil)){
										if( ($kodesp_array['kodesp'] != '') && ($kodesp_array['status'] == '') ){ $kode_tabungan_array[] = $kodesp_array['kodesp']; }
									}
								}
							}
							$kode_tabungan_array_unique = array_unique($kode_tabungan_array);
							asort($kode_tabungan_array_unique);
							$hitung_tabungan = 1;
							foreach($kode_tabungan_array_unique as $kode_tabungan){
								echo '<div class="form-check form-check-inline">';
								echo '<input class="form-check-input" type="checkbox" id="tabungan' . $hitung_tabungan . '" value="' . $kode_tabungan . '" name="tabungan[]"';
								if ( in_array($kode_tabungan, $tabungan) ){echo ' checked="checked"';}
								echo '>';
								echo '<label class="form-check-label" for="tabungan' . $hitung_tabungan . '">' . $kode_tabungan . '</label>';
								echo '</div>';
							}
							?>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<label for="fakultas">Fakultas</label>
							<select id="fakultas" name="fakultas" class="custom-select">
								<option value="-1">Pilih</option>
								<?php // Kueri untuk menampilkan fakultas 
								foreach(list_fakultas($koneksi_ambil) as $list_fakultas){
									echo '<option value="' . $list_fakultas['kode'] . '"';
									if ($list_fakultas['kode'] == $fakultas){echo ' selected="selected"';}
									echo '>' . $list_fakultas['kode'] . ' - ' . $list_fakultas['keterangan'] . '</option>';
								} ?>
							</select>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<label for="periode">Periode</label>
							<?php list_periode($periode, 'periode'); ?>
						</div>
					</div>
				</div>
				<input type="hidden" value="<?php echo $kwitansi; ?>" id="kwitansi" name="kwitansi">
				<input type="hidden" value="<?php echo $custom_pokok; ?>" id="custom-pokok" name="custom-pokok">
				<input type="hidden" value="<?php echo $custom_jasa; ?>" id="custom-jasa" name="custom-jasa">
				<div class="row">
					<div class="col-sm-9">
						Tambahkan tabungan secara manual yang belum di set secara system. Kedua kolom harus di isi dengan jumlah input yang sama. Setiap inputan pisahkan dengan tanda "," (koma).
					</div>
					<div class="col-sm-3">
						<a href="#" role="button" class="btn btn-outline-secondary btn-sm mb-3" data-toggle="collapse" data-target="#input-tabungan" style="width: 100%;">Tambah Tabungan</a>
					</div>
				</div>
				<div class="row collapse" id="input-tabungan">
					<div class="col-sm-6">
						<div class="form-group">
							<label for="rekening">NO Rekening (pisahkan dg "," koma)</label>
							<textarea id="rekening" name="rekening" class="form-control" rows="2"><?php echo $rekening; ?></textarea>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="nominal">Nominal (pisahkan dg "," koma)</label>
							<textarea id="nominal" name="nominal" class="form-control" rows="2"><?php echo $nominal; ?></textarea>
						</div>
					</div>
				</div>
				</div>
				<div class="col-md-3">
					<div class="card border-primary">
						<div class="card-body">
							<div class="form-group">
								<label for="periode-akhir">Tgl Cut Off</label>
								<input class="form-control" value="<?php echo $periode_akhir; ?>" type="text" id="periode-akhir" name="periode-akhir">
							</div>
							<div class="form-group form-check">
								<input class="form-check-input" type="checkbox" value="Y" id="ttd" name="ttd" <?php if ($ttd == 'Y') {echo 'checked="checked"';} ?>>
								<label class="form-check-label" for="ttd">TTD dan Stempel</label>
							</div>
							<button type="submit" class="btn btn-primary mb-3" style="width: 100%;">Proses Tagihan Tabungan</button>
							<?php if (isset($_GET['tab']) && $_GET['tab'] == '2'){
								$import_tabungan = $url_import[0] . '?menu=tghn&tab=' . $tab . '&';
								foreach($tabungan as $imp_tab){
									$import_tabungan .= 'tabungan%5B%5D=' . $imp_tab . '&';
								}
								$import_tabungan .= 'fakultas=' . $fakultas . '&periode=' . $periode . '&rekening=' . $rekening . '&nominal=' . $nominal . '&periode-akhir=' . $periode_akhir . '&import=Y'; ?>
								<a href="<?php echo $import_tabungan; ?>" class="btn btn-outline-primary btn-sm" style="width: 100%;">Set Untuk Import</a>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
			</form>
		</div>
	</div>
	</div>

	<!-- Set Rekap Tagihan -->
	<div class="tab-pane fad <?php if ($tab == '3'){echo 'show active';} ?>" id="rekap" role="tabpanel" aria-labelledby="pills-rekap-tab">
	<div class="card mb-3">
		<div class="card-header">
			<h5>Rekap Semua Tagihan</h5>
		</div>
		<div class="card-body">
			<form action="">
			<input type="hidden" name="menu" value="tghn">
			<input type="hidden" name="tab" value="3">
			<div class="row">
				<div class="col-md-9">
				<div class="row">
					<div class="col-sm-6">
						<div class="form-group">
							<label for="fakultas">Fakultas</label>
							<select id="fakultas" name="fakultas" class="custom-select">
								<option value="-1">Pilih</option>
								<?php // Kueri untuk menampilkan fakultas 
								foreach(list_fakultas($koneksi_ambil) as $list_fakultas){
									echo '<option value="' . $list_fakultas['kode'] . '"';
									if ($list_fakultas['kode'] == $fakultas){echo ' selected="selected"';}
									echo '>' . $list_fakultas['kode'] . ' - ' . $list_fakultas['keterangan'] . '</option>';
								} ?>
							</select>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="periode">Periode</label>
							<?php list_periode($periode, 'periode'); ?>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-9">
						<label>Input nominal manual untuk jumlah yang berbeda tidak mengikuti sistem. Ketiga kolom harus di isi dengan jumlah input yang sama. Jika tidak ingin mengganti salah satunya (pokoknya saja atau jasanya saja) silakan masukkan " " (spasi) kemudian diikuti tanda "," (koma)</label>
					</div>
					<div class="col-sm-3">
						<a href="#" role="button" class="btn btn-outline-secondary btn-sm mb-3" data-toggle="collapse" data-target="#input-manual-rekap" style="width: 100%;">Input Nominal Manual</a>
					</div>
				</div>
				<div class="row collapse" id="input-manual-rekap">
					<div class="col-sm-12">
						<div class="form-group">
							<label for="kwitansi">NO Kuwitansi (pisahkan dg "," koma)</label>
							<textarea id="kwitansi" name="kwitansi" class="form-control" rows="3"><?php echo $kwitansi; ?></textarea>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="custom-pokok">Pokok (pisahkan dg "," koma)</label>
							<textarea id="custom-pokok" name="custom-pokok" class="form-control" rows="3"><?php echo $custom_pokok; ?></textarea>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="custom-jasa">Jasa (pisahkan dg "," koma)</label>
							<textarea id="custom-jasa" name="custom-jasa" class="form-control" rows="3"><?php echo $custom_jasa; ?></textarea>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-9">
						Tambahkan tabungan secara manual yang belum di set secara system. Kedua kolom harus di isi dengan jumlah input yang sama. Setiap inputan pisahkan dengan tanda "," (koma).
					</div>
					<div class="col-sm-3">
						<a href="#" role="button" class="btn btn-outline-secondary btn-sm mb-3" data-toggle="collapse" data-target="#input-tab-rekap" style="width: 100%;">Tambah Tabungan</a>
					</div>
				</div>
				<div class="row collapse" id="input-tab-rekap">
					<div class="col-sm-6">
						<div class="form-group">
							<label for="rekening">NO Rekening (pisahkan dg "," koma)</label>
							<textarea id="rekening" name="rekening" class="form-control" rows="2"><?php echo $rekening; ?></textarea>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="nominal">Nominal (pisahkan dg "," koma)</label>
							<textarea id="nominal" name="nominal" class="form-control" rows="2"><?php echo $nominal; ?></textarea>
						</div>
					</div>
				</div>
				</div>
				<div class="col-md-3">
					<div class="card border-primary">
						<div class="card-body">
							<div class="form-group">
								<label for="periode-akhir">Tgl Cut Off</label>
								<input class="form-control" value="<?php echo $periode_akhir; ?>" type="text" id="periode-akhir" name="periode-akhir">
							</div>
							<div class="form-group form-check">
								<input class="form-check-input" type="checkbox" value="Y" id="kurangjasa" name="kurangjasa" <?php if ($kurangjasa == 'Y') {echo 'checked="checked"';} ?>>
								<label class="form-check-label" for="kurangjasa">Hitung kekurangan jasa belum terbayar</label>
							</div>
							<div class="form-group form-check">
								<input class="form-check-input" type="checkbox" value="OK" id="bariskosong" name="bariskosong" <?php if ($bariskosong == 'OK' || $fakultas == 'L') {echo 'checked="checked"';} ?>>
								<label class="form-check-label" for="bariskosong">Hilangkan baris kosong</label>
							</div>
							<div class="form-group form-check">
								<input class="form-check-input" type="checkbox" value="pakai" id="kolomfaktur" name="kolomfaktur" <?php if ($kolomfaktur == 'pakai' || $fakultas == 'A') {echo 'checked="checked"';} ?>>
								<label class="form-check-label" for="kolomfaktur">Sertakan Kolom Faktur (No Pinjaman)</label>
							</div>
							<input type="hidden" value="<?php echo $ttd; ?>" id="ttd" name="ttd">
							<button type="submit" class="btn btn-primary" style="width: 100%;">Proses Rekap Tagihan</button>
						</div>
					</div>
				</div>
			</div>
			</form>
		</div>
	</div>
	</div>
</div>
<?php // ====================================================
// Array kwitansi manual
if($kwitansi != ''){
	$array_kwitansi 		= explode(',', $kwitansi);
	$array_custom_pokok		= explode(',', $custom_pokok);
	$array_custom_jasa		= explode(',', $custom_jasa);
	$array_kwitansi_group 	= array();
	$nomor_kwitansi 		= 0;
	foreach($array_kwitansi as $arr_kw){
		$array_kwitansi_group[$arr_kw][] = $array_custom_pokok[$nomor_kwitansi];
		$array_kwitansi_group[$arr_kw][] = $array_custom_jasa[$nomor_kwitansi];
		$nomor_kwitansi++;
	}
}
if($rekening != ''){
	$array_rekening	= explode(',', $rekening);
	$array_nominal	= explode(',', $nominal);
}
if(isset($_GET['tab']) && $_GET['tab'] != ''){
	echo '<button class="btn btn-primary float-left mt-4 mb-2 tombol-print" onclick="window.print()">Siap Cetak</button>';
	echo '<button class="btn btn-success float-right mt-4 mb-2 tombol-excel">Ekport Excel (Hanya Tabel Teratas)</button>';
	if( ($_GET['tab'] == '1') && (!isset($_GET['import'])) ){ require('cetak-pembiayaan.php'); } 
	else if( ($_GET['tab'] == '2') && (!isset($_GET['import'])) ){ require('cetak-tabungan.php'); }
	else if($_GET['tab'] == '3'){ require('cetak-rekap.php'); }
	else if( ($_GET['tab'] == '1') && ($_GET['import'] == 'Y') ){ require('import-pembiayaan.php'); }
	else if( ($_GET['tab'] == '2') && ($_GET['import'] == 'Y') ){ require('import-tabungan.php'); }
}
