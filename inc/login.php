<?php /* ====================================================
Konfigurasi halaman login
======================================================= */ ?>
<div class="container">
	<div class="row">
		<div class="col-lg-6 offset-lg-3 col-md-8 offset-md-2 col-sm-10 offset-sm-1">
			<div class="card mt-3">
				<div class="card-header">
					<div class="row">
						<div class="col-4"><img src="img/logo.png" style="width: 100%;"></div>
						<div class="col-8"><p class="font-weight-bold mt-3">Silakan Login Terlebih Dahulu</p></div>
					</div>
				</div>
				<div class="card-body">
				<form action="" method="POST">
					<div class="form-group">
						<label for="UserName">Nama Pengguna</label>
						<input type="text" class="form-control" id="UserName" name="UserName" required="required">
					</div>
					<div class="form-group">
						<label for="UserPassword">Password</label>
						<input type="password" class="form-control" id="UserPassword" name="UserPassword" required="required">
					</div>
					<button type="submit" class="btn btn-primary">Masuk</button>
				</form>
				</div>
			</div>
		</div>
	</div>
</div>