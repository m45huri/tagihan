<?php /* ====================================================
Buat Import -> Untuk Proses Kerjakan Tagihan Tabungan
========================================================== */
if($tabungan != ''){ ?>
<style type="text/css">
	.bold {font-weight: bold;}
	.italic {font-style: italic;}
	.center {text-align: center;}
	.right {text-align: right;}
	table.table {font-size: .95rem;}
	.table th.border-on,
	.table td.border-on { border: 1px solid #3c3c3c;border-left: none;border-right: none;}
	.table thead th.border-on {vertical-align: middle;}
	.table-sm tbody td {padding: 0 .3rem;}
	.table-sm tbody td.height {padding: .5rem .3rem;}
		
	@media print {
		html, body {width: 216mm;height: 356mm;font-size: .9rem;}
		nav.navbar,h2.satu, ul.dua, .tab-content.tiga, .tombol-print, .tombol-excel {display: none;}
		thead {display: table-header-group;}
		@page {
			size:legal;
			margin: 0.1cm 0.4cm 3.5cm 0;
		}
	}
</style>
<div class="table-responsive">
	<table class="table table-borderless table-sm mt-4" id="<?php echo 'TAB-' . $fakultas . '-' . str_replace(' ', '-', list_fakultas($koneksi_ambil,$fakultas)[$fakultas]) . '-' . $periode; ?>">
		<?php // ========================================================
		$semua_kueri = array();
		$nama_urut	= 1;
		foreach($tabungan as $jenis_tabungan){
			$kueri_anggota = 'select kode, nama from anggota where unit = \'' . $fakultas . '\'';
			$kueri_anggota_hasil = mysqli_query($koneksi_ambil,$kueri_anggota);
			while($array_data_anggota = mysqli_fetch_assoc($kueri_anggota_hasil)){
				$kueri_jenis_tabungan = 'select rekening, kodesp, pokok, status from tabungan where kode = \'' . $array_data_anggota['kode'] . '\' and tr = \'G\' and kodesp = \'' . $jenis_tabungan . '\'';
				$kueri_jenis_tabungan_hasil = mysqli_query($koneksi_ambil,$kueri_jenis_tabungan);
				while($array_data_tabungan = mysqli_fetch_assoc($kueri_jenis_tabungan_hasil)){
					if ($array_data_tabungan['status'] != 'C'){
						if ($array_data_tabungan['kodesp'] == 'SKP'){$kode_mutasi = '400';}
						else if ($array_data_tabungan['kodesp'] == 'MAP'){$kode_mutasi = '600';}
						else if ( $array_data_tabungan['kodesp'] == ('MUD' || 'WAD') ){$kode_mutasi = '500';}
						$semua_kueri[$array_data_anggota['nama'] . '-' . $nama_urut] = array(
							'rekening'	=> $array_data_tabungan['rekening'],
							'anggota'	=> $array_data_anggota['kode'],
							'kodesp'	=> $array_data_tabungan['kodesp'],
							'mutasi'	=> $kode_mutasi,
							'ket'		=> 'SETORAN TAB PG - ' . $array_data_tabungan['rekening'],
							'nominal'	=> $array_data_tabungan['pokok'],
							'nama'		=> $array_data_anggota['nama'],							
						);
						$nama_urut++;
					}
				}
			}
		}
		ksort($semua_kueri);
		$semua_kueri_baru = array();
		foreach($semua_kueri as $semua_kueri_group){
			$semua_kueri_baru[$semua_kueri_group['kodesp']][] = $semua_kueri_group;
		}
		ksort($semua_kueri_baru); ?>
		<thead>
			<tr>
				<th class="center bold border-on">TANGGAL</th>
				<th class="center bold border-on">REKENING</th>
				<th class="center bold border-on">NO ANGGOTA</th>
				<th class="center bold border-on">JENIS</th>
				<th class="center bold border-on">MUTASI</th>
				<th class="center bold border-on">KETERANGAN</th>
				<th class="center bold border-on">JUMLAH</th>
				<th class="center bold border-on">NAMA</th>
			</tr>
		</thead>
		<tbody>
			<?php // Display data
			$total_nominal = 0;
			foreach($semua_kueri_baru as $table_group){
				foreach($table_group as $table){ ?>
			<tr>
				<td class="center"><?php echo date('d/m/Y'); ?></td>
				<td class="center"><?php echo $table['rekening']; ?></td>
				<td class="center"><?php echo $table['anggota']; ?></td>
				<td class="center"><?php echo $table['kodesp']; ?></td>
				<td class="center"><?php echo $table['mutasi']; ?></td>
				<td><?php echo $table['ket']; ?></td>
				<td class="right"><?php echo number_format($table['nominal'],0,',','.'); ?></td>
				<td><?php echo substr($table['nama'], 0, 30); ?></td>
			</tr>
			<?php 	$total_nominal += $table['nominal'];
				}
			}
			if($rekening != ''){
				$rekening_loop = 0;
				foreach($array_rekening as $rekening_group){
					$kueri_rekening_anggota = 'select kode from tabungan where rekening =\'' . $rekening_group . '\'';
					$rekening_no_anggota = mysqli_fetch_object( mysqli_query($koneksi_ambil,$kueri_rekening_anggota) )->kode;
					$kueri_rekening_nama = 'select nama from anggota where kode =\'' . $rekening_no_anggota . '\'';
					$rekening_nama_anggota = mysqli_fetch_object( mysqli_query($koneksi_ambil,$kueri_rekening_nama) )->nama;
					$rekening_kodesp = substr($rekening_group, 0, 3);
					if ($rekening_kodesp == 'SKP'){$kode_mutasi = '400';}
					else if ($rekening_kodesp == 'MAP'){$kode_mutasi = '600';}
					else if ( $rekening_kodesp == ('MUD' || 'WAD') ){$kode_mutasi = '500';} ?>
			<tr>
				<td class="center"><?php echo date('d/m/Y'); ?></td>
				<td class="center"><?php echo $rekening_group; ?></td>
				<td class="center"><?php echo $rekening_no_anggota; ?></td>
				<td class="center"><?php echo $rekening_kodesp; ?></td>
				<td class="center"><?php echo $kode_mutasi; ?></td>
				<td><?php echo 'SETORAN TAB PG - ' . $rekening_group; ?></td>
				<td class="right"><?php echo number_format($array_nominal[$rekening_loop],0,',','.'); ?></td>
				<td><?php echo substr($rekening_nama_anggota, 0, 30); ?></td>
			</tr>
			<?php 	$total_nominal += $array_nominal[$rekening_loop];
					$rekening_loop++;
				}
			} ?>
			<tr>
				<td colspan="2" class="border-on"><?php echo 'Cut off : ' . $periode_akhir; ?></td>
				<td colspan="4" class="border-on"></td>
				<td class="right bold border-on"><?php echo number_format($total_nominal,0,',','.'); ?></td>
				<td class="border-on"></td>
			</tr>
		</tbody>
	</table>
</div>
<script type="text/javascript">
	$(".tombol-excel").click(function(e){
		$("#<?php echo 'TAB-' . $fakultas . '-' . str_replace(' ', '-', list_fakultas($koneksi_ambil,$fakultas)[$fakultas]) . '-' . $periode; ?>").table2excel({
			name: "<?php echo 'TAB-' . $fakultas . '-' . str_replace(' ', '-', list_fakultas($koneksi_ambil,$fakultas)[$fakultas]) . '-' . $periode; ?>",
			filename: "<?php echo 'TAB-' . $fakultas . '-' . str_replace(' ', '-', list_fakultas($koneksi_ambil,$fakultas)[$fakultas]) . '-' . $periode; ?>.xls"
		});
	});
</script>
<?php }