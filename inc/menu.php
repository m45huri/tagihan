<nav id="nav-menu" class="navbar navbar-expand-lg navbar-light sticky-top">
	<a href="<?php echo $root; ?>" class="navbar-brand">
		<img src="img/logo.png" class="logo">
	</a>
	<div class="user-info">
		<h5 class="text-capitalize"><?php echo $_SESSION['username']; ?></h5>
		<a href="logout.php" class="font-weight-bolder">Keluar</a>
	</div>
	<button class="navbar-toggler toggle" id="toggle" type="button" data-toggle="collapse" data-target="#KPNnavbar" aria-controls="KPNnavbar" aria-expanded="false" aria-label="Toggle navigation" onclick="add_class_toggle()"><span></span><span></span></button>
	<script type="text/javascript">
		function add_class_toggle(){
			var element = document.getElementById('toggle');
			element.classList.toggle('active');
		}
	</script>
	<div class="collapse navbar-collapse" id="KPNnavbar">
		<ul class="navbar-nav">
			<li class="nav-item">
				<a class="nav-link" href="<?php echo $root; ?>?menu=tghn">Tagihan</a>
			</li>
			<?php if($_SESSION['username'] == 'superadmin'){ ?>
			<li class="nav-item dropdown">
				<a class="nav-link dropdown-toggle" href="#" id="KPNnavbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Konfigurasi</a>
				<div class="dropdown-menu" aria-labelledby="KPNnavbarDropdown">
					<a class="dropdown-item" href="<?php echo $root; ?>?menu=cnfg">Koneksi Data Base</a>
					<a class="dropdown-item" href="<?php echo $root; ?>?menu=set_tag">Set Tagihan</a>
				</div>
			</li>
			<?php } ?>
		</ul>
	</div>
</nav>