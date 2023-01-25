<?php /* ====================================================
Buat Tagihan -> Untuk Proses Potongan Format Baru
======================================================= */ ?>
<style type="text/css">
	.bold {font-weight: bold;}
	.italic {font-style: italic;}
	.center {text-align: center;}
	.right {text-align: right;}
	table.table {font-size: .9rem;}
	.table th.border-on,
	.table td.border-on { border: 1px solid #3c3c3c;border-left: none;border-right: none;}
	.table th.border-t,
	.table td.border-t { border: 1px solid #3c3c3c;border-left: none;border-right: none;border-bottom: none;}
	.table th.border-b,
	.table td.border-b { border: 1px solid #3c3c3c;border-left: none;border-right: none;border-top: none;}
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
$fakultas_unit_kerja = str_replace( ' ', '-', list_fakultas($koneksi_ambil,$fakultas)[$fakultas] );
echo '<div class="table-responsive">'; ?>
<table class="table table-borderless table-sm mt-4" id="<?php echo $fakultas_unit_kerja . '-' . $periode; ?>">
	<?php // Proses data
	$semua_kueri = array();
	$tanggal_akhir = $periode . '-' . $periode_akhir;
	$kueri_anggota = 'select kode, nama, nip, ktp, wajib from anggota where unit = \'' . $fakultas . '\'';
	$kueri_anggota_hasil = mysqli_query($koneksi_ambil,$kueri_anggota);
	while($array_data_anggota = mysqli_fetch_assoc($kueri_anggota_hasil)){
		if( $array_data_anggota['wajib'] > 0 ){

			// Membuat array data sw
			$faktur_sw = 'BKM/SW /' . substr( $array_data_anggota['kode'], 2, 2 ) . '/' . substr( $array_data_anggota['kode'], 4 );
			$semua_kueri['0-' . $faktur_sw] = array(
				'nip'		=> $array_data_anggota['nip'],
				'anggota'	=> $array_data_anggota['kode'],
				'nama'		=> $array_data_anggota['nama'],
				'faktur'	=> $faktur_sw,
				'jenis'		=> 'SW',
				'pokok'		=> $array_data_anggota['wajib'],
				'jasa'		=> 0,
				'jumlah'	=> $array_data_anggota['wajib'],
				'lama'		=> 1,
				'ke'		=> 1,
			);

			// Membuat array data tabungan
			$kueri_tabungan = 'select rekening, faktur, kodesp, tgl, status, pokok from tabungan where kode = \'' . $array_data_anggota['kode'] . '\' and tr = \'G\'';
			$kueri_tabungan_hasil = mysqli_query($koneksi_ambil,$kueri_tabungan);
			while($array_tabungan = mysqli_fetch_assoc($kueri_tabungan_hasil)){
				if($array_tabungan['status'] != 'C'){
					$revisi_faktur = substr( $array_tabungan['faktur'], 0, 1 ) != 'B' ? 'BKM/' . $array_tabungan['kodesp'] . '/' . substr( $array_tabungan['tgl'], 2, 2 ) . '/' . explode( '-', $array_tabungan['faktur'] )[1] : $array_tabungan['faktur'];
					$semua_kueri['1-' . $array_tabungan['faktur']] = array(
						'nip'		=> $array_data_anggota['nip'],
						'anggota'	=> $array_data_anggota['kode'],
						'nama'		=> $array_data_anggota['nama'],
						'faktur'	=> $revisi_faktur,
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
						'ke'		=> $angsuran,
					);
				}
			}

		}
	}

	// Proses data semua array
	ksort($semua_kueri);
	$semua_kueri_baru = array();
	if( $totalsaja != 'OK' ){
		foreach($semua_kueri as $semua_kueri_group){
			$semua_kueri_baru[$semua_kueri_group['nama']][] = $semua_kueri_group;
		}
	} else{
		foreach($semua_kueri as $semua_kueri_group){
			$semua_kueri_baru[$semua_kueri_group['nama']][0]['nip'] 	= $semua_kueri_group['nip'];
			$semua_kueri_baru[$semua_kueri_group['nama']][0]['anggota'] = $semua_kueri_group['anggota'];
			$semua_kueri_baru[$semua_kueri_group['nama']][0]['nama'] 	= $semua_kueri_group['nama'];
			$semua_kueri_baru[$semua_kueri_group['nama']][0]['faktur'] 	= 'KJPRI/TAG/' . substr( $periode, 5, 2 ) . '/' . substr( $periode, 0, 4 );
			$semua_kueri_baru[$semua_kueri_group['nama']][0]['jenis'] 	= 'Total';
			$semua_kueri_baru[$semua_kueri_group['nama']][0]['lama'] 	= '1';
			$semua_kueri_baru[$semua_kueri_group['nama']][0]['ke'] 		= '1';
			
			if( !isset( $semua_kueri_baru[$semua_kueri_group['nama']][0]['pokok'] ) ){
				$semua_kueri_baru[$semua_kueri_group['nama']][0]['pokok'] = $semua_kueri_group['pokok'];
			} else {
				$semua_kueri_baru[$semua_kueri_group['nama']][0]['pokok'] += $semua_kueri_group['pokok'];
			}
			
			if( !isset( $semua_kueri_baru[$semua_kueri_group['nama']][0]['jasa'] ) ){
				$semua_kueri_baru[$semua_kueri_group['nama']][0]['jasa'] = $semua_kueri_group['jasa'];
			} else {
				$semua_kueri_baru[$semua_kueri_group['nama']][0]['jasa'] += $semua_kueri_group['jasa'];
			}
			
			if( !isset( $semua_kueri_baru[$semua_kueri_group['nama']][0]['jumlah'] ) ){
				$semua_kueri_baru[$semua_kueri_group['nama']][0]['jumlah'] = $semua_kueri_group['jumlah'];
			} else {
				$semua_kueri_baru[$semua_kueri_group['nama']][0]['jumlah'] += $semua_kueri_group['jumlah'];
			}
			
		}
	}
	unset($semua_kueri);
	ksort($semua_kueri_baru); ?>
	<thead>
		<tr>
			<th class="center bold border-on">No</th>
			<th class="center bold border-on">Tahun</th>
			<th class="center bold border-on">Bulan</th>
			<th class="center bold border-on">No Anggota KJPRI</th>
			<th class="center bold border-on">NIP/NIK</th>
			<th class="center bold border-on">No Pembiayaan</th>
			<th class="center bold border-on">Kode Potongan</th>
			<th class="center bold border-on">Tagihan ke</th>
			<th class="center bold border-on">Lama Cicilan</th>
			<th class="center bold border-on">Pokok (Rp)</th>
			<th class="center bold border-on">Jasa (Rp)</th>
			<th class="center bold border-on">Nama</th>
		</tr>
	</thead>
	<tbody>
		<?php // Menyusun data
		$nomor			= 1;
		$total_pokok	= 0;
		$total_jasa 	= 0;
		foreach($semua_kueri_baru as $table_group){
			foreach($table_group as $table){
				$total_pokok 	+= $table['pokok'];
				$total_jasa 	+= $table['jasa'];
				$koreksi_nip	 = str_replace(' ', '', $table['nip']);
				$revisi_nip		 = $koreksi_nip != '' ? '\'' . $koreksi_nip : ''; ?>
		<tr>
			<td class="center"><?php echo $nomor++ . '.'; ?></td>
			<td class="center"><?php echo substr( $periode, 0, 4 ); ?></td>
			<td class="center"><?php echo substr( $periode, 5, 2 ); ?></td>
			<td class="center"><?php echo $table['anggota']; ?></td>
			<td><?php echo $revisi_nip; ?></td>
			<td><?php echo $table['faktur']; ?></td>
			<td class="center"><?php echo $table['jenis']; ?></td>
			<td class="center"><?php echo $table['ke']; ?></td>
			<td class="center"><?php echo $table['lama']; ?></td>
			<td class="right"><?php echo number_format($table['pokok'],0,',','.'); ?></td>
			<td class="right"><?php echo number_format($table['jasa'],0,',','.'); ?></td>
			<td><?php echo substr($table['nama'], 0, 27) . ' - ' . $fakultas; ?></td>
		</tr>
		<?php }
			if( $bariskosong != 'OK' && $table_group != end($semua_kueri_baru) ){
				echo '<tr><td class="height"></td><td class="height"></td><td class="height"></td><td class="height"></td><td class="height"></td><td class="height"></td><td class="height"></td><td class="height"></td><td class="height"></td><td class="height"></td><td class="height"></td><td class="height"></td></tr>';
			}
		} ?>
		<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
		<tr>
			<td colspan="3" class="border-t"><?php echo 'Cut off : tgl ' . $periode_akhir; ?></td>
			<td colspan="6" class="border-t"></td>
			<td class="right border-t"><?php echo number_format($total_pokok,0,',','.'); ?></td>
			<td class="right border-t"><?php echo number_format($total_jasa,0,',','.'); ?></td>
			<td class="border-t"></td>
		</tr>
		<tr>
			<td colspan="9" class="border-b"></td>
			<td class="right bold border-b">Total</td>
			<td class="right bold border-b"><?php echo number_format($total_pokok + $total_jasa,0,',','.'); ?></td>
			<td class="border-b"></td>
		</tr>
	</tbody>
</table>
<script type="text/javascript">
	$(".tombol-excel").click(function(e){
		$("#<?php echo $fakultas_unit_kerja . '-' . $periode; ?>").table2excel({
			name: "<?php echo $fakultas_unit_kerja . '-' . $periode; ?>",
			filename: "<?php echo $fakultas_unit_kerja . '-' . $periode; ?>.xls"
		});
	});
</script>
<?php echo '</div>';
