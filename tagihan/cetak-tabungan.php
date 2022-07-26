<?php /* ====================================================
Buat Tagihan -> Untuk Proses Cetak Tabungan
========================================================== */
require('inc/tghn.php');
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
<?php // ========================================================
echo '<div class="table-responsive">';
foreach($tabungan as $jenis_tabungan){ ?>
	<table class="table table-borderless table-sm mt-4" id="<?php echo $fakultas . '-' . $jenis_tabungan . '-' . $periode; ?>">
		<?php // Proses untuk satu jenis pembiayaan
		$semua_kueri = array();
		$kueri_anggota = 'select kode, nama from anggota where unit = \'' . $fakultas . '\'';
		$kueri_anggota_hasil = mysqli_query($koneksi_ambil,$kueri_anggota);
		while($array_data_anggota = mysqli_fetch_assoc($kueri_anggota_hasil)){
			$kueri_jenis_tabungan = 'select rekening, pokok, status from tabungan where kode = \'' . $array_data_anggota['kode'] . '\' and tr = \'G\' and kodesp = \'' . $jenis_tabungan . '\'';
			$kueri_jenis_tabungan_hasil = mysqli_query($koneksi_ambil,$kueri_jenis_tabungan);
			while($array_data_tabungan = mysqli_fetch_assoc($kueri_jenis_tabungan_hasil)){
				if ($array_data_tabungan['status'] != 'C'){
					$semua_kueri[$array_data_tabungan['rekening']] = array(
						'anggota'	=> $array_data_anggota['kode'],
						'nama'		=> $array_data_anggota['nama'],
						'rekening'	=> $array_data_tabungan['rekening'],
						'nominal'	=> $array_data_tabungan['pokok'],
					);
				}
			}
		}
		ksort($semua_kueri);
		$semua_kueri_baru = array();
		foreach($semua_kueri as $semua_kueri_group){
			$semua_kueri_baru[$semua_kueri_group['nama']][] = $semua_kueri_group;
		}
		ksort($semua_kueri_baru);
		?>
		<thead>
			<tr><th colspan="5" class="center bold">DAFTAR TAGIHAN <?php echo ket_kode($koneksi_ambil, $jenis_tabungan); ?></th></tr>
			<tr><th colspan="5" class="center bold">UNIT : <?php echo $fakultas . ' - ' . list_fakultas($koneksi_ambil,$fakultas)[$fakultas]; ?></th></tr>
			<tr><th colspan="5" class="center bold">- <?php list_periode($periode, '', 'N'); ?> -</th></tr>
			<tr>
				<th class="center bold border-on">NO</th>
				<th class="center bold border-on">NO ANGGOTA</th>
				<th class="center bold border-on">NAMA</th>
				<th class="center bold border-on">REKENING</th>
				<th class="center bold border-on">JUMLAH</th>
			</tr>
		</thead>
		<tbody>
			<?php // Display data
			$total_nominal = 0;
			$nomor = 1;
			foreach($semua_kueri_baru as $table_group){
				foreach($table_group as $table){ ?>
			<tr>
				<td class="center"><?php echo $nomor++ . '.'; ?></td>
				<td class="center"><?php echo $table['anggota']; ?></td>
				<td><?php echo substr($table['nama'], 0, 30); ?></td>
				<td class="center"><?php echo $table['rekening']; ?></td>
				<td class="right"><?php echo number_format($table['nominal'],0,',','.'); ?></td>
			</tr>
			<?php $total_nominal += $table['nominal'];
				}
			}
			if($rekening != ''){
				$rekening_loop = 0;
				foreach($array_rekening as $rekening_group){
					$kueri_rekening_anggota = 'select kode from tabungan where rekening =\'' . $rekening_group . '\'';
					$rekening_no_anggota = mysqli_fetch_object( mysqli_query($koneksi_ambil,$kueri_rekening_anggota) )->kode;
					$kueri_rekening_nama = 'select nama from anggota where kode =\'' . $rekening_no_anggota . '\'';
					$rekening_nama_anggota = mysqli_fetch_object( mysqli_query($koneksi_ambil,$kueri_rekening_nama) )->nama; ?>
			<tr>
				<td class="center"><?php echo $nomor++ . '.'; ?></td>
				<td class="center"><?php echo $rekening_no_anggota; ?></td>
				<td><?php echo substr($rekening_nama_anggota, 0, 30); ?></td>
				<td class="center"><?php echo $rekening_group; ?></td>
				<td class="right"><?php echo number_format($array_nominal[$rekening_loop],0,',','.'); ?></td>
			</tr>
			<?php 	$total_nominal += $array_nominal[$rekening_loop];
					$rekening_loop++;
				}
			} ?>
			<tr>
				<td colspan="2"class="border-on"><?php echo 'Cut off : ' . $periode_akhir; ?></td>
				<td colspan="2" class="border-on"></td>
				<td class="right bold border-on"><?php echo number_format($total_nominal,0,',','.'); ?></td>
			</tr>
			<tr><td colspan="5" class="height"></td></tr>
			<tr>
				<td colspan="3"></td>
				<td colspan="2" <?php if($periode_akhir < 20){echo 'class="bold"';} ?>>Malang, <?php echo ($periode_akhir + 1) . ' '; 
					list_periode( date( 'Y-m', strtotime( '-1 month', strtotime($periode) ) ), '', 'N');
					echo '<br>'; ?>Divisi Simpan Pinjam KPRI-UB</td>
			</tr>
			<tr><td colspan="5" class="height"></td></tr>
			<tr><td colspan="5" class="height"></td></tr>
			<tr><td colspan="5" class="height"></td></tr>
			<tr>
				<td colspan="3"></td>
				<td colspan="2" style="position: relative;">
					<?php if($ttd != ''){ ?>
					<img src="img/<?php echo $tanda_tangan['TtdPimpinan']; ?>" style="position: absolute;width: 130px;top: -50px;z-index: 1;">
					<img src="img/stempel.png" style="position: absolute;width: 120px;top: -85px;left: -90px; z-index: 2;">
					<?php } ?>
				</td>
			</tr>
			<tr>
				<td colspan="3"></td>
				<td colspan="2" class="bold italic"><span style="border-bottom: 1px solid #3c3c3c;"><?php echo $tanda_tangan['NamaPimpinan'] ?></span></td>
				<td></td>
			</tr>
			<tr>
				<td colspan="3"></td>
				<td colspan="2"><?php echo $tanda_tangan['JabatanPimpinan'] ?></td>
				<td></td>
			</tr>
		</tbody>
	</table>
	<script type="text/javascript">
		$(".tombol-excel").click(function(e){
			$("#<?php echo $fakultas . '-' . $jenis_tabungan . '-' . $periode; ?>").table2excel({
				name: "<?php echo $fakultas . '-' . $jenis_tabungan . '-' . $periode; ?>",
				filename: "<?php echo $fakultas . '-' . $jenis_tabungan . '-' . $periode; ?>.xls"
			});
		});
	</script>
<?php if (count($tabungan) > 1){echo '<div style="margin: 6rem 0;"></div>';}
	}
echo '</div>';
}