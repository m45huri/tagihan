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
<table class="table table-borderless table-sm mt-4" id="<?php echo $fakultas . '-POT-BARU-' . $periode; ?>">
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
</table>
<script type="text/javascript">
	$(".tombol-excel").click(function(e){
		$("#<?php echo $fakultas . '-POT-BARU-' . $periode; ?>").table2excel({
			name: "<?php echo $fakultas . '-POT-BARU-' . $periode; ?>",
			filename: "<?php echo $fakultas . '-POT-BARU-' . $periode; ?>.xls"
		});
	});
</script>
<?php echo '</div>';