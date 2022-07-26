<?php /* ====================================================
Buat Tagihan -> Untuk Proses Cetak Pembiayaan
========================================================== */
require('inc/tghn.php');
if($pembiayaan != ''){ ?>
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
		nav.navbar, h2.satu, ul.dua, .tab-content.tiga, .tombol-print, .tombol-excel {display: none;}
		thead {display: table-header-group;}
		@page {
			size:legal;
			margin: 0.1cm 0.4cm 3.5cm 0;
		}
	}
</style>
<?php // ========================================================
echo '<div class="table-responsive">';
foreach($pembiayaan as $jenis_pembiayaan){ ?>
	<table class="table table-borderless table-sm mt-4" id="<?php echo $fakultas . '-' . $jenis_pembiayaan . '-' . $periode; ?>">
		<?php // Proses untuk satu jenis pembiayaan
		$semua_kueri = array();
		$tanggal_akhir = $periode . '-' . $periode_akhir;
		$kueri_anggota = 'select kode, nama from anggota where unit = \'' . $fakultas . '\'';
		$kueri_anggota_hasil = mysqli_query($koneksi_ambil,$kueri_anggota);
		while($array_data_anggota = mysqli_fetch_assoc($kueri_anggota_hasil)){
			$kueri_jenis_pembiayaan = 'select faktur, tgl, kodesp, pokok, bunga, angsuran, lama, ke, lbunga, lunas from mpinjam where kode = \'' . $array_data_anggota['kode'] . '\' and tr = \'G\' and kodesp = \'' . $jenis_pembiayaan . '\'';
			$kueri_jenis_pembiayaan_hasil = mysqli_query($koneksi_ambil,$kueri_jenis_pembiayaan);
			while($array_data_pembiayaan = mysqli_fetch_assoc($kueri_jenis_pembiayaan_hasil)){
				if( ($array_data_pembiayaan['lunas'] == '') && ($tanggal_akhir >= date('Y-m-d', strtotime('+1 month', strtotime($array_data_pembiayaan['tgl'])))) ){
					$selisih_tanggal = date_diff(date_create($array_data_pembiayaan['tgl']), date_create($tanggal_akhir));
					$angsuran = ($selisih_tanggal->y * 12) + $selisih_tanggal->m;
					if( ($kwitansi != '') && key_exists($array_data_pembiayaan['faktur'], $array_kwitansi_group) ){
						$hitung_pokok = $array_kwitansi_group[$array_data_pembiayaan['faktur']][0] != '' ? $array_kwitansi_group[$array_data_pembiayaan['faktur']][0] : $array_data_pembiayaan['pokok'] / $array_data_pembiayaan['lama'];
						if($array_kwitansi_group[$array_data_pembiayaan['faktur']][1] != ''){
							$hitung_jasa = $array_kwitansi_group[$array_data_pembiayaan['faktur']][1];
						} else {
							if($array_data_pembiayaan['kodesp'] == 'MUS'){
								$hitung_jasa = $array_data_pembiayaan['angsuran'] - $hitung_pokok;
							} else {
								$jasa_normal = $array_data_pembiayaan['bunga'] / $array_data_pembiayaan['lama'];
								if($kurangjasa == 'Y'){
									$total_jasa_seharusnya = ($angsuran - 1) * $jasa_normal;
									$total_jasa_terbayar = $array_data_pembiayaan['lbunga'];
									$hitung_jasa = $jasa_normal + ($total_jasa_seharusnya - $total_jasa_terbayar);
								} else { $hitung_jasa = $jasa_normal; }
							}
						}
					} else {
						$hitung_pokok = $array_data_pembiayaan['pokok'] / $array_data_pembiayaan['lama'];
						if($array_data_pembiayaan['kodesp'] == 'MUS'){
							$hitung_jasa = $array_data_pembiayaan['angsuran'] - $hitung_pokok;
						} else {
							$jasa_normal = $array_data_pembiayaan['bunga'] / $array_data_pembiayaan['lama'];
							if($kurangjasa == 'Y'){
								$total_jasa_seharusnya = ($angsuran - 1) * $jasa_normal;
								$total_jasa_terbayar = $array_data_pembiayaan['lbunga'];
								$hitung_jasa = $jasa_normal + ($total_jasa_seharusnya - $total_jasa_terbayar);
							} else { $hitung_jasa = $jasa_normal; }
						}
					}
					$hitung_jumlah	= $hitung_pokok + $hitung_jasa;
					$semua_kueri[$array_data_pembiayaan['faktur']] = array(
						'apa'		=> 'data',
						'angsuran'	=> $angsuran,
						'nama'		=> $array_data_anggota['nama'],
						'faktur'	=> $array_data_pembiayaan['faktur'],
						'kode'		=> $array_data_anggota['kode'],
						'pokok'		=> $hitung_pokok,
						'jasa'		=> $hitung_jasa,
						'lama'		=> $array_data_pembiayaan['lama'],
						'ke'		=> $array_data_pembiayaan['ke'],
						'jumlah'	=> $hitung_jumlah,
					);
				}
			}
		}
		ksort($semua_kueri);
		$semua_kueri_baru = array();
		foreach($semua_kueri as $semua_kueri_group){
			$semua_kueri_baru[$semua_kueri_group['angsuran']][0] = array(
				'apa'		=> 'label',
				'angsuran'	=> 'Angsuran ke : ' . $semua_kueri_group['angsuran'], );
			$semua_kueri_baru[$semua_kueri_group['angsuran']][] = $semua_kueri_group;
		}
		?>
		<thead>
			<tr><th colspan="8" class="bold center">DAFTAR TAGIHAN <?php echo ket_kode($koneksi_ambil, $jenis_pembiayaan); ?></th></tr>
			<tr><th colspan="8" class="center bold">UNIT : <?php echo $fakultas . ' - ' . list_fakultas($koneksi_ambil,$fakultas)[$fakultas]; ?></th></tr>
			<tr><th colspan="8" class="center bold">- <?php list_periode($periode, '', 'N'); ?> -</th></tr>
			<tr>
				<th class="center bold border-on">NO URUT</th>
				<th class="center bold border-on">NAMA</th>
				<th class="center bold border-on">NO PEMBIAYAAN</th>
				<th class="center bold border-on">NO ANGGOTA</th>
				<th class="center bold border-on">POKOK</th>
				<th class="center bold border-on">JASA</th>
				<th class="center bold border-on">JUMLAH</th>
				<th class="center bold border-on">LAMA</th>
			</tr>
		</thead>
		<tbody>
			<?php // Display data
			$total_pokok 	= 0;
			$total_jasa 	= 0;
			$total_jumlah 	= 0;
			foreach($semua_kueri_baru as $table_group){
				$nomor 		= 1;
				foreach($table_group as $table){
					if($table['apa'] == 'label'){ ?>
			<tr>
				<td class="height"></td><td class="height"></td><td class="height"></td><td class="height"></td>
				<td class="height"></td><td class="height"></td><td class="height"></td><td class="height"></td>
			</tr>
			<tr>
				<td class="center"><?php echo $table['angsuran']; ?></td>
				<td></td><td></td><td></td><td></td><td></td><td></td><td></td>
			</tr>
			<?php	} else {
					$tebal = ($kwitansi != '') && key_exists($table['faktur'], $array_kwitansi_group) ? ' bold' : ''; ?>
			<tr>
				<td class="center<?php echo $tebal; ?>"><?php echo $nomor++ . '.'; ?></td>
				<td class="<?php echo $tebal; ?>"><?php echo substr($table['nama'], 0, 30) . ' - ' . $table['ke']; ?></td>
				<td class="center<?php echo $tebal; ?>"><?php echo $table['faktur']; ?></td>
				<td class="center<?php echo $tebal; ?>"><?php echo $table['kode']; ?></td>
				<td class="right<?php echo $tebal; ?>"><?php echo number_format($table['pokok'],0,',','.'); ?></td>
				<td class="right<?php echo $tebal; ?>"><?php echo number_format($table['jasa'],0,',','.'); ?></td>
				<td class="right<?php echo $tebal; ?>"><?php echo number_format($table['jumlah'],0,',','.'); ?></td>
				<td class="center<?php echo $tebal; ?>"><?php echo $table['lama'] . 'x'; ?></td>
			</tr>
			<?php 	$total_pokok	+= $table['pokok'];
					$total_jasa 	+= $table['jasa'];
					$total_jumlah	+= $table['jumlah'];
					}
				}
			} ?>
			<tr>
				<td colspan="2" class="border-on"><?php echo 'Cut off : ' . $periode_akhir; ?></td>
				<td colspan="2" class="border-on"></td>
				<td class="right bold border-on"><?php echo number_format($total_pokok,0,',','.'); ?></td>
				<td class="right bold border-on"><?php echo number_format($total_jasa,0,',','.'); ?></td>
				<td class="right bold border-on"><?php echo number_format($total_jumlah,0,',','.'); ?></td>
				<td class="border-on"></td>
			</tr>
			<tr><td colspan="8" class="height"></td></tr>
			<tr>
				<td colspan="4"></td>
				<td colspan="3" <?php if($periode_akhir < 20){echo 'class="bold"';} ?>>Malang, <?php echo ($periode_akhir + 1) . ' '; 
					list_periode( date( 'Y-m', strtotime( '-1 month', strtotime($periode) ) ), '', 'N');
					echo '<br>'; ?>Divisi Simpan Pinjam KPRI-UB</td>
				<td></td>
			</tr>
			<tr><td colspan="8" class="height"></td></tr>
			<tr><td colspan="8" class="height"></td></tr>
			<tr><td colspan="8" class="height"></td></tr>
			<tr>
				<td colspan="4"></td>
				<td colspan="3" style="position: relative;">
					<?php if($ttd != ''){ ?>
					<img src="img/<?php echo $tanda_tangan['TtdPimpinan']; ?>" style="position: absolute;width: 130px;top: -50px;z-index: 1;">
					<img src="img/stempel.png" style="position: absolute;width: 120px;top: -85px;left: -90px; z-index: 2;">
					<?php } ?>
				</td>
				<td></td>
			</tr>
			<tr>
				<td colspan="4"></td>
				<td colspan="3" class="bold italic"><span style="border-bottom: 1px solid #3c3c3c;"><?php echo $tanda_tangan['NamaPimpinan'] ?></span></td>
				<td></td>
			</tr>
			<tr>
				<td colspan="4"></td>
				<td colspan="3"><?php echo $tanda_tangan['JabatanPimpinan'] ?></td>
				<td></td>
			</tr>
		</tbody>
	</table>
	<script type="text/javascript">
		$(".tombol-excel").click(function(e){
			$("#<?php echo $fakultas . '-' . $jenis_pembiayaan . '-' . $periode; ?>").table2excel({
				name: "<?php echo $fakultas . '-' . $jenis_pembiayaan . '-' . $periode; ?>",
				filename: "<?php echo $fakultas . '-' . $jenis_pembiayaan . '-' . $periode; ?>.xls"
			});
		});
	</script>
<?php if (count($pembiayaan) > 1){echo '<div style="margin: 6rem 0;"></div>';}
	}
echo '</div>';
}