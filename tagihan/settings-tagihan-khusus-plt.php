<?php
// Menambahkan tagihan custom otomatis untuk PLT
$fak_untuk_plt 	= array('K', 'E', 'G', 'M', 'L');

if(in_array($fakultas, $fak_untuk_plt)){
	// setting kwitansinya dan custom nilainya di array bawah ini
	// array('no_kwitansi','nominal_pokok','nominal_jasa')
	$kwitansi_plt 			= array();
	$kwitansi_plt['A'][0]	= array('BKK/IJR/21/001561','2908333','1666667');
	$kwitansi_plt['K'][0]	= array('BKK/IJR/21/000429','500000','50000');
	$kwitansi_plt['E'][0]	= array('BKK/MUS/19/000024','3600000','400000');
	$kwitansi_plt['G'][0] 	= array('BKK/PLT/20/000009','1100000','900000');
	$kwitansi_plt['M'][0] 	= array('BKK/PLT/19/000003','400000','100000');
	$kwitansi_plt['L'][0]	= array('BKK/PLT/20/000004','1000000','250000');

	$kwitansi_explode	= $kwitansi != '' ? explode(',', $kwitansi) : array();
	foreach($kwitansi_plt[$fakultas] as $kwt_plt){
		if(!in_array($kwt_plt[0], $kwitansi_explode)){
			$kwitansi 		= $kwt_plt[0] . ',' . $kwitansi;
			$custom_pokok 	= $kwt_plt[1] . ',' . $custom_pokok;
			$custom_jasa 	= $kwt_plt[2] . ',' . $custom_jasa;
		}
	}
}