<?php /* ====================================================
Buat Tagihan -> Untuk Proses Cetak Rekap
======================================================= */ ?>
<style type="text/css">
	.bold {font-weight: bold;}
	.italic {font-style: italic;}
	.center {text-align: center;}
	.right {text-align: right;}
	table.table {font-size: .9rem;}
	.table th.border-on,
	.table td.border-on { border: 1px solid #3c3c3c;border-left: none;border-right: none;}
	.table thead th.border-on {vertical-align: middle;}
	.table-sm tbody td {padding: 0 .3rem;}
	.table-sm tbody td.height {padding: .5rem .3rem;}
		
	@media print {
		html, body {width: 216mm;height: 356mm;font-size: .88rem;}
		nav.navbar, h2.satu, ul.dua, .tab-content.tiga, .tombol-print, .tombol-excel {display: none;}
		thead {display: table-header-group;}
		@page {
			size:legal;
			margin: 0.1cm 0.4cm 3.5cm 0;
		}
	}
</style>
<?php // ========================================================
echo '<div class="table-responsive">'; ?>
<table class="table table-borderless table-sm mt-4" id="<?php echo $fakultas . '-REKAP-' . $periode; ?>">
	<?php // Display data
	$semua_kueri = array();
	$tanggal_akhir = $periode . '-' . $periode_akhir;
	$kueri_anggota = 'select kode, nama, nip from anggota where unit = \'' . $fakultas . '\'';
	$kueri_anggota_hasil = mysqli_query($koneksi_ambil,$kueri_anggota);
	while($array_data_anggota = mysqli_fetch_assoc($kueri_anggota_hasil)){

		// Membuat array data tabungan
		$kueri_tabungan = 'select rekening, faktur, kodesp, status, pokok from tabungan where kode = \'' . $array_data_anggota['kode'] . '\' and tr = \'G\'';
		$kueri_tabungan_hasil = mysqli_query($koneksi_ambil,$kueri_tabungan);
		while($array_tabungan = mysqli_fetch_assoc($kueri_tabungan_hasil)){
			if($array_tabungan['status'] != 'C'){
				$semua_kueri['1-' . $array_tabungan['faktur']] = array(
					'nip'		=> $array_data_anggota['nip'],
					'anggota'	=> $array_data_anggota['kode'],
					'nama'		=> $array_data_anggota['nama'],
					'faktur'	=> $array_tabungan['faktur'],
					'jenis'		=> $array_tabungan['kodesp'],
					'pokok'		=> $array_tabungan['pokok'],
					'jasa'		=> 0,
					'jumlah'	=> $array_tabungan['pokok'],
					'lama'		=> 1,
					'ke'		=> 1,
				);
			}
		}

		// Memebuat array tambah tabungan manual
		if($rekening != ''){
			$rekening_loop = 0;
			foreach($array_rekening as $rekening_group){
				$kueri_rekening_anggota = 'select kode from tabungan where rekening =\'' . $rekening_group . '\'';
				$rekening_no_anggota = mysqli_fetch_object( mysqli_query($koneksi_ambil,$kueri_rekening_anggota) )->kode;
				if($array_data_anggota['kode'] == $rekening_no_anggota){
					$semua_kueri['2-' . $rekening_group] = array(
						'nip'		=> $array_data_anggota['nip'],
						'anggota'	=> $array_data_anggota['kode'],
						'nama'		=> $array_data_anggota['nama'],
						'faktur'	=> $rekening_group,
						'jenis'		=> substr($rekening_group, 0, 3),
						'pokok'		=> $array_nominal[$rekening_loop],
						'jasa'		=> 0,
						'jumlah'	=> $array_nominal[$rekening_loop],
						'lama'		=> 0,
						'ke'		=> 0,
					);
				}
				$rekening_loop++;
			}
		}

		// Membuat array data pembiayaan
		$kueri_pembiayaan = 'select faktur, tgl, kodesp, pokok, bunga, angsuran, lama, ke, lbunga, lunas from mpinjam where kode = \'' . $array_data_anggota['kode'] . '\' and tr = \'G\'';
		$kueri_pembiayaan_hasil = mysqli_query($koneksi_ambil,$kueri_pembiayaan);
		while($array_pembiayaan = mysqli_fetch_assoc($kueri_pembiayaan_hasil)){
			if( ($array_pembiayaan['lunas'] == '') && ($tanggal_akhir >= date('Y-m-d', strtotime('+1 month', strtotime($array_pembiayaan['tgl'])))) ){
				$selisih_tanggal = date_diff(date_create($array_pembiayaan['tgl']), date_create($tanggal_akhir));
				$angsuran = ($selisih_tanggal->y * 12) + $selisih_tanggal->m;
				if( ($kwitansi != '') && key_exists($array_pembiayaan['faktur'], $array_kwitansi_group) ){
					$hitung_pokok = $array_kwitansi_group[$array_pembiayaan['faktur']][0] != '' ? $array_kwitansi_group[$array_pembiayaan['faktur']][0] : $array_pembiayaan['pokok'] / $array_pembiayaan['lama'];
					if($array_kwitansi_group[$array_pembiayaan['faktur']][1] != ''){
						$hitung_jasa = $array_kwitansi_group[$array_pembiayaan['faktur']][1];
					} else {
						if($array_pembiayaan['kodesp'] == 'MUS'){
							$hitung_jasa = $array_pembiayaan['angsuran'] - $hitung_pokok;
						} else {
							$jasa_normal = $array_pembiayaan['bunga'] / $array_pembiayaan['lama'];
							if($kurangjasa == 'Y'){
								$total_jasa_seharusnya = ($angsuran - 1) * $jasa_normal;
								$total_jasa_terbayar = $array_pembiayaan['lbunga'];
								$hitung_jasa = $jasa_normal + ($total_jasa_seharusnya - $total_jasa_terbayar);
							} else { $hitung_jasa = $jasa_normal; }
						}
					}
				} else {
					$hitung_pokok = $array_pembiayaan['pokok'] / $array_pembiayaan['lama'];
					if($array_pembiayaan['kodesp'] == 'MUS'){
						$hitung_jasa = $array_pembiayaan['angsuran'] - $hitung_pokok;
					} else {
						$jasa_normal = $array_pembiayaan['bunga'] / $array_pembiayaan['lama'];
						if($kurangjasa == 'Y'){
							$total_jasa_seharusnya = ($angsuran - 1) * $jasa_normal;
							$total_jasa_terbayar = $array_pembiayaan['lbunga'];
							$hitung_jasa = $jasa_normal + ($total_jasa_seharusnya - $total_jasa_terbayar);
						} else { $hitung_jasa = $jasa_normal; }
					}
				}
				$hitung_jumlah	= $hitung_pokok + $hitung_jasa;
				$semua_kueri['3-' . $array_pembiayaan['faktur']] = array(
					'nip'		=> $array_data_anggota['nip'],
					'anggota'	=> $array_data_anggota['kode'],
					'nama'		=> $array_data_anggota['nama'],
					'faktur'	=> $array_pembiayaan['faktur'],
					'jenis'		=> $array_pembiayaan['kodesp'],
					'pokok'		=> $hitung_pokok,
					'jasa'		=> $hitung_jasa,
					'jumlah'	=> $hitung_jumlah,
					'lama'		=> $array_pembiayaan['lama'],
					'ke'		=> $array_pembiayaan['ke'],
				);
			}
		}
	}

	// Proses data semua array
	ksort($semua_kueri);
	$semua_kueri_baru = array();
	foreach($semua_kueri as $semua_kueri_group){
		$semua_kueri_baru[$semua_kueri_group['nama']][] = $semua_kueri_group;
	}
	ksort($semua_kueri_baru);
	?>
	<thead>
		<tr><th colspan="<?php if ($kolomfaktur == 'pakai') {echo '11';} else {echo '10';} ?>" class="bold center">REKAPITULASI TAGIHAN PEMBIAYAAN DAN TABUNGAN</th></tr>
		<tr><th colspan="<?php if ($kolomfaktur == 'pakai') {echo '11';} else {echo '10';} ?>" class="center bold">UNIT : <?php echo $fakultas . ' - ' . list_fakultas($koneksi_ambil,$fakultas)[$fakultas]; ?></th></tr>
		<tr><th colspan="<?php if ($kolomfaktur == 'pakai') {echo '11';} else {echo '10';} ?>" class="center bold">- <?php list_periode($periode, '', 'N'); ?> -</th></tr>
		<tr><th colspan="<?php if ($kolomfaktur == 'pakai') {echo '11';} else {echo '10';} ?>"></th></tr>
		<tr><th colspan="<?php if ($kolomfaktur == 'pakai') {echo '11';} else {echo '10';} ?>"></th></tr>
		<tr>
			<th class="center bold border-on">NO</th>
			<th class="center bold border-on">NIP / NIK</th>
			<th class="center bold border-on">NO ANGGOTA</th>
			<th class="center bold border-on">NAMA ANGGOTA</th>
			<th class="center bold border-on">JENIS</th>
			<?php if ($kolomfaktur == 'pakai') {echo '<th class="center bold border-on">NO PEMBIAYAAN</th>';} ?>
			<th class="center bold border-on">POKOK</th>
			<th class="center bold border-on">JASA</th>
			<th class="center bold border-on">JUMLAH</th>
			<th class="center bold border-on">LAMA</th>
			<th class="center bold border-on">TOTAL</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
			<?php if ($kolomfaktur == 'pakai') {echo '<td></td>';} ?>
		</tr>
		<?php // Display data
		$nomor			= 1;
		$total_pokok	= 0;
		$total_jasa 	= 0;
		$total_jumlah	= 0;
		$total_semua	= 0;
		foreach($semua_kueri_baru as $table_group){
			$hitung_semua = 0;
			foreach($table_group as $table){
				if($table != end($table_group)){
					$hitung_semua 	+= $table['jumlah'];
					$total_pokok 	+= $table['pokok'];
					$total_jasa 	+= $table['jasa'];
					$total_jumlah 	+= $table['jumlah'];
					$tebal 			 = $kwitansi != '' && key_exists($table['faktur'], $array_kwitansi_group) ? ' bold' : ''; ?>
		<tr>
			<td class="center<?php echo $tebal; ?>"><?php echo $nomor++ . '.'; ?></td>
			<td class="<?php echo $tebal; ?>"><?php echo '\'' . $table['nip']; ?></td>
			<td class="center<?php echo $tebal; ?>"><?php echo $table['anggota']; ?></td>
			<td class="<?php echo $tebal; ?>"><?php echo substr($table['nama'], 0, 27) . ' - ' . $table['ke']; ?></td>
			<td class="center<?php echo $tebal; ?>"><?php echo $table['jenis']; ?></td>
			<?php if ($kolomfaktur == 'pakai') {echo '<td class="' . $tebal . '">' . $table['faktur'] . '</td>';} ?>
			<td class="right<?php echo $tebal; ?>"><?php echo number_format($table['pokok'],0,',','.'); ?></td>
			<td class="right<?php echo $tebal; ?>"><?php echo number_format($table['jasa'],0,',','.'); ?></td>
			<td class="right<?php echo $tebal; ?>"><?php echo number_format($table['jumlah'],0,',','.'); ?></td>
			<td class="center<?php echo $tebal; ?>"><?php echo $table['lama'] . 'x'; ?></td>
			<td class="right"></td>
		</tr>
		<?php	} else if($table == end($table_group)){
					$hitung_semua 	+= $table['jumlah'];
					$total_pokok 	+= $table['pokok'];
					$total_jasa 	+= $table['jasa'];
					$total_jumlah 	+= $table['jumlah'];
					$total_semua	+= $hitung_semua;
					$tebal 			 = $kwitansi != '' && key_exists($table['faktur'], $array_kwitansi_group) ? ' bold' : ''; ?>
		<tr>
			<td class="center<?php echo $tebal; ?>"><?php echo $nomor++ . '.'; ?></td>
			<td class="<?php echo $tebal; ?>"><?php echo '\'' . $table['nip']; ?></td>
			<td class="center<?php echo $tebal; ?>"><?php echo $table['anggota']; ?></td>
			<td class="<?php echo $tebal; ?>"><?php echo substr($table['nama'], 0, 27) . ' - ' . $table['ke']; ?></td>
			<td class="center<?php echo $tebal; ?>"><?php echo $table['jenis']; ?></td>
			<?php if ($kolomfaktur == 'pakai') {echo '<td class="' . $tebal . '">' . $table['faktur'] . '</td>';} ?>
			<td class="right<?php echo $tebal; ?>"><?php echo number_format($table['pokok'],0,',','.'); ?></td>
			<td class="right<?php echo $tebal; ?>"><?php echo number_format($table['jasa'],0,',','.'); ?></td>
			<td class="right<?php echo $tebal; ?>"><?php echo number_format($table['jumlah'],0,',','.'); ?></td>
			<td class="center<?php echo $tebal; ?>"><?php echo $table['lama'] . 'x'; ?></td>
			<td class="right<?php echo $tebal; ?>"><?php echo number_format($hitung_semua,0,',','.'); ?></td>
		</tr>
		<?php	}
			}
			if( $bariskosong != 'OK' ){
				echo '<tr><td class="height"></td><td class="height"></td><td class="height"></td><td class="height"></td>
						<td class="height"></td><td class="height"></td><td class="height"></td><td class="height"></td>
						<td class="height"></td><td class="height"></td>';
				if ($kolomfaktur == 'pakai') {echo '<td class="height"></td>';}
				echo '</tr>';
			}
		} ?>
		<tr>
			<td colspan="2" class="border-on"><?php echo 'Cut off : ' . $periode_akhir; ?></td>
			<td colspan="<?php if ($kolomfaktur == 'pakai') {echo '4';} else {echo '3';} ?>" class="border-on"></td>
			<td class="right bold border-on"><?php echo number_format($total_pokok,0,',','.'); ?></td>
			<td class="right bold border-on"><?php echo number_format($total_jasa,0,',','.'); ?></td>
			<td class="right bold border-on"><?php echo number_format($total_jumlah,0,',','.'); ?></td>
			<td class="border-on"></td>
			<td class="right bold border-on"><?php echo number_format($total_semua,0,',','.'); ?></td>
		</tr>
	</tbody>
</table>
<script type="text/javascript">
	$(".tombol-excel").click(function(e){
		$("#<?php echo $fakultas . '-REKAP-' . $periode; ?>").table2excel({
			name: "<?php echo $fakultas . '-REKAP-' . $periode; ?>",
			filename: "<?php echo $fakultas . '-REKAP-' . $periode; ?>.xls"
		});
	});
</script>
<?php echo '</div>';
