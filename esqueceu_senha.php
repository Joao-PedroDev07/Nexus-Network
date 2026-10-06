<?php
session_start();
?>
<!DOCTYPE html> 
<html lang="en">
	
<!-- doccure/forgot-password.html  30 Nov 2019 04:12:20 GMT -->
<head>
		<meta charset="utf-8">
		<title>Esqueceu a Senha - Nexus Network</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
		
		<!-- Favicons -->
		<link href="assets/img/logo-transparente.png" rel="icon">
		
		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="assets/css/bootstrap.min.css">
		
		<!-- Fontawesome CSS -->
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
		
		<!-- Header CSS -->
		<link rel="stylesheet" href="assets/css/header.css">
		
		<!-- Custom CSS -->
		<link rel="stylesheet" href="assets/css/esqueceu_senha.css">
		
		<!-- Google Fonts -->
		<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
	
	</head>
	<body class="account-page">

		<!-- Main Wrapper -->
		<div class="main-wrapper">
		
			<!-- Header -->
			<?php include_once("header.php"); ?>
			<!-- /Header -->
			
			<!-- Page Content -->
			<div class="content">
				<div class="container-fluid">
					
					<div class="row">
						<div class="col-md-8 offset-md-2">
							
							<!-- Account Content -->
							<div class="account-content">
								<div class="row align-items-center justify-content-center">
									<div class="col-md-12 col-lg-6 login-right">
										<div class="login-header">
											<h3>Esqueceu a Senha?</h3>
											<p class="small text-muted">Selecione o tipo de usuário para continuar</p>
										</div>
										
										<!-- Forgot Password Form -->
										<form id="forgotPasswordForm" onsubmit="handleSubmit(event)">
											<div class="form-group">
												<label for="tipo_usuario">Tipo de Usuário</label>
												<select id="tipo_usuario" name="tipo_usuario" class="form-control" required>
													<option value="">Selecione o tipo de usuário</option>
													<option value="prestador">Prestador</option>
													<option value="cliente">Cliente</option>
												</select>
											</div>
											
											<div class="text-right">
												<a class="forgot-link" href="login.php">Lembrou de sua senha?</a>
											</div>
											
											<button class="btn btn-primary btn-block btn-lg login-btn" type="submit">Continuar</button>
										</form>
										<!-- /Forgot Password Form -->
										
									</div>
								</div>
							</div>
							<!-- /Account Content -->
							
						</div>
					</div>

				</div>

			</div>		
			<!-- /Page Content -->
   
			<!-- Footer -->
			<!-- /Footer -->
		   
		</div>
		<!-- /Main Wrapper -->
	  
		<!-- jQuery -->
		<script src="assets/js/jquery.min.js"></script>
		
		<!-- Bootstrap Core JS -->
		<script src="assets/js/popper.min.js"></script>
		<script src="assets/js/bootstrap.min.js"></script>
		
		<!-- Header JS -->
		<script src="assets/js/header.js"></script>
		
		<script>
			function handleSubmit(event) {
				event.preventDefault();
				
				const tipoUsuario = document.getElementById('tipo_usuario').value;
				
				if (!tipoUsuario) {
					alert('Por favor, selecione o tipo de usuário');
					return;
				}
				
				let url = '';
				
				if (tipoUsuario === 'prestador') {
					url = 'redefinir senha/esqueciasenha.php';
				} else if (tipoUsuario === 'cliente') {
					url = 'redefinir senha cliente/esqueciasenha.php';
				}
				
				// Redirecionar para a página correta
				window.location.href = url;
			}
		</script>
		
	</body>

<!-- doccure/forgot-password.html  30 Nov 2019 04:12:20 GMT -->
</html>