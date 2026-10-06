<?php
	session_start();
	include_once("conexao.php");
	require_once("foto_helper.php");

	$codigo = intval(filter_input(INPUT_GET, 'pres_codigo', FILTER_SANITIZE_NUMBER_INT));
	$stmt_pres = mysqli_prepare($conn, "SELECT * FROM prestadores WHERE pres_codigo = ?");
	mysqli_stmt_bind_param($stmt_pres, "i", $codigo);
	mysqli_stmt_execute($stmt_pres);
	$row_prestadores = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_pres));
	mysqli_stmt_close($stmt_pres);

	// Prestador inexistente (ex.: conta excluída) - volta para a busca
	if (!$row_prestadores) {
		header("Location: search.php");
		exit;
	}

	// Cliente logado (funciona tanto para login normal quanto para login com Google)
	$usuario_logado = isset($_SESSION['logado']) && $_SESSION['logado'] === true;
	$tipo_usuario = $_SESSION['tipo_usuario'] ?? '';
	$cliente_logado_id = ($usuario_logado && $tipo_usuario === 'cliente') ? intval($_SESSION['usuario_id']) : 0;

	// Buscar média de avaliações e total
	$stmt_avg = mysqli_prepare($conn, "SELECT COALESCE(AVG(avl_nota), 0) as media, COUNT(*) as total FROM avaliacao WHERE pres_codigo = ?");
	mysqli_stmt_bind_param($stmt_avg, "i", $codigo);
	mysqli_stmt_execute($stmt_avg);
	$result_avg = mysqli_stmt_get_result($stmt_avg);
	$avaliacao_data = mysqli_fetch_assoc($result_avg);
	$media_avaliacao = round($avaliacao_data['media'], 1);
	$total_avaliacoes = $avaliacao_data['total'];
	mysqli_stmt_close($stmt_avg);

	// Verificar se o usuário logado já avaliou este prestador
	$usuario_ja_avaliou = false;
	$avaliacao_usuario = null;
	
	if ($cliente_logado_id > 0 && $codigo > 0) {
		$stmt_check_user = mysqli_prepare($conn, "SELECT * FROM avaliacao WHERE cli_codigo = ? AND pres_codigo = ?");
		mysqli_stmt_bind_param($stmt_check_user, "ii", $cliente_logado_id, $codigo);
		mysqli_stmt_execute($stmt_check_user);
		$result_check_user = mysqli_stmt_get_result($stmt_check_user);
		
		if (mysqli_num_rows($result_check_user) > 0) {
			$usuario_ja_avaliou = true;
			$avaliacao_usuario = mysqli_fetch_assoc($result_check_user);
		}
		mysqli_stmt_close($stmt_check_user);
	}

	// Buscar avaliações do prestador
	$result_avaliacoes = null;
	if (isset($codigo) && $codigo > 0) {
		$sql_avaliacoes = "SELECT a.*, c.cli_nome as nome_cliente, c.cli_foto as cliente_foto 
						   FROM avaliacao a 
						   INNER JOIN clientes c ON a.cli_codigo = c.cli_codigo 
						   WHERE a.pres_codigo = ? 
						   ORDER BY a.avl_data DESC";
		
		$stmt_avaliacoes = mysqli_prepare($conn, $sql_avaliacoes);
		if ($stmt_avaliacoes) {
			mysqli_stmt_bind_param($stmt_avaliacoes, "i", $codigo);
			mysqli_stmt_execute($stmt_avaliacoes);
			$result_avaliacoes = mysqli_stmt_get_result($stmt_avaliacoes);
		}
	}
?>

<!DOCTYPE html> 
<html lang="pt-BR">
<head>
	<meta charset="utf-8">
	<title>Perfil do Prestador - <?php echo htmlspecialchars($row_prestadores['pres_nome']);?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
	
	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
	
	<!-- Fontawesome CSS -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	
	<!-- Fancybox CSS -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css">
	<link rel="stylesheet" href="assets/css/info_prestador.css">

	<link rel="icon" href="assets/img/logo-transparente.png">
	<link rel="stylesheet" href="assets/css/header.css">
	<script src="assets/js/header.js" defer></script>
</head>
<body>
	<?php include_once("header.php"); ?>
	<!-- Main Wrapper -->
	<div class="main-wrapper">
		
		<!-- Page Content -->
		<div class="content">
			<div class="container">
				<div class="row justify-content-center g-4">

					<!-- Card Informações -->
					<div class="col-lg-7 d-flex">
						<div class="card flex-fill">
							<div class="card-body">
								<div class="doctor-widget">
									<div class="doc-info-left">
										<div class="doctor-img">
											<img src="<?php echo htmlspecialchars(fotoOuPadrao($row_prestadores['pres_foto']));?>" class="img-fluid" alt="Foto do Prestador">
										</div>
										<div class="doc-info-cont">
											<h4 class="doc-name"><?php echo htmlspecialchars($row_prestadores['pres_nome']);?></h4>
											<p class="doc-speciality"><?php echo htmlspecialchars($row_prestadores['pres_profissao']);?></p>
											<p class="doc-location">
												<i class="fas fa-map-marker-alt"></i> 
												<?php echo htmlspecialchars($row_prestadores['pres_cidade'] . '-' . $row_prestadores['pres_estado']);?>
											</p>
											<div class="clinic-services">
												<span><?php echo htmlspecialchars($row_prestadores['pres_telefone']);?></span><br>
												<span><?php echo htmlspecialchars($row_prestadores['pres_email']);?></span>
											</div>
											<?php if (!$usuario_logado): ?>
												<a href="login.php" class="btn btn-white msg-btn">
													<i class="far fa-comment"></i> Entre para enviar mensagem
												</a>
											<?php elseif ($tipo_usuario === 'cliente'): ?>
												<a href="chat.php?pres_codigo=<?= $codigo ?>" class="btn btn-white msg-btn">
													<i class="far fa-comment"></i> Mensagem
												</a>
											<?php endif; ?>
										</div>
									</div>

									<!-- Média de avaliações -->
									<div class="rating-display">
										<div class="stars-average">
											<?php 
											for ($i = 1; $i <= 5; $i++) {
												if ($i <= floor($media_avaliacao)) {
													echo '<i class="fas fa-star"></i>';
												} elseif ($i - 0.5 <= $media_avaliacao) {
													echo '<i class="fas fa-star-half-alt"></i>';
												} else {
													echo '<i class="far fa-star"></i>';
												}
											}
											?>
										</div>
										<span class="rating-text">
											<?= $media_avaliacao > 0 ? number_format($media_avaliacao, 1) : 'Sem avaliações' ?> 
											(<?= $total_avaliacoes ?> <?= $total_avaliacoes == 1 ? 'avaliação' : 'avaliações' ?>)
										</span>
									</div>

									<div class="descricao mt-3">
										<p><?php echo htmlspecialchars($row_prestadores['pres_descricao']);?></p>
									</div>
								</div>
							</div>
						</div>
					</div>
					
				</div>

				<!-- SEÇÃO DE COMENTÁRIOS E AVALIAÇÕES -->
				<div class="comments-section">
					<h2><i class="fas fa-comments"></i> Comentários e Avaliações</h2>
					
					<?php
					// Exibir mensagens de sucesso ou erro
					if (isset($_SESSION['mensagem_sucesso'])) {
						echo '<div class="alert alert-success"><i class="fas fa-check-circle"></i> ' . htmlspecialchars($_SESSION['mensagem_sucesso']) . '</div>';
						unset($_SESSION['mensagem_sucesso']);
					}
					if (isset($_SESSION['mensagem_erro'])) {
						echo '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ' . htmlspecialchars($_SESSION['mensagem_erro']) . '</div>';
						unset($_SESSION['mensagem_erro']);
					}
					?>
					
					<!-- Formulário para adicionar comentário -->
					<?php if ($usuario_ja_avaliou): ?>
						<div class="add-comment-box">
							<div class="alert alert-info">
								<i class="fas fa-info-circle"></i> 
								<strong>Você já avaliou este prestador!</strong>
								<p class="mb-0 mt-2">Sua avaliação: 
									<?php 
									for ($i = 1; $i <= 5; $i++) {
										if ($i <= $avaliacao_usuario['avl_nota']) {
											echo '<i class="fas fa-star text-warning"></i>';
										} else {
											echo '<i class="far fa-star text-warning"></i>';
										}
									}
									?>
								</p>
								<p class="mb-0"><small>Para alterar sua avaliação, exclua a atual e envie uma nova.</small></p>
							</div>
						</div>
					<?php elseif ($cliente_logado_id <= 0): ?>
						<div class="add-comment-box">
							<div class="alert alert-info mb-0">
								<i class="fas fa-info-circle"></i>
								<?php if ($usuario_logado): ?>
									Apenas clientes podem avaliar prestadores.
								<?php else: ?>
									<a href="login.php">Entre como cliente</a> para deixar sua avaliação.
								<?php endif; ?>
							</div>
						</div>
					<?php else: ?>
						<div class="add-comment-box">
							<h3>Deixe sua avaliação</h3>
							<form id="commentForm" method="POST" action="processar_avaliacao.php">
								<input type="hidden" name="pres_codigo" value="<?= $codigo ?>">
								
								<!-- Sistema de estrelas -->
								<div class="rating-input">
									<label>Sua nota:</label>
									<div class="stars-input">
										<input type="radio" name="avaliacao" value="5" id="star5" required>
										<label for="star5"><i class="fas fa-star"></i></label>
										
										<input type="radio" name="avaliacao" value="4" id="star4">
										<label for="star4"><i class="fas fa-star"></i></label>
										
										<input type="radio" name="avaliacao" value="3" id="star3">
										<label for="star3"><i class="fas fa-star"></i></label>
										
										<input type="radio" name="avaliacao" value="2" id="star2">
										<label for="star2"><i class="fas fa-star"></i></label>
										
										<input type="radio" name="avaliacao" value="1" id="star1">
										<label for="star1"><i class="fas fa-star"></i></label>
									</div>
								</div>
								
								<div class="mb-3">
									<label for="comentario" class="form-label">Seu comentário:</label>
									<textarea name="comentario" id="comentario" class="comment-textarea" placeholder="Conte sua experiência..." maxlength="500" required></textarea>
									<small class="text-muted">Máximo 500 caracteres</small>
								</div>
								
								<button type="submit" class="btn-submit-comment">
									<i class="fas fa-paper-plane"></i> Enviar Avaliação
								</button>
							</form>
						</div>
					<?php endif; ?>

					<!-- Lista de comentários -->
					<div class="comments-list">
						<?php if ($result_avaliacoes && mysqli_num_rows($result_avaliacoes) > 0): ?>
							<?php while ($avaliacao = mysqli_fetch_assoc($result_avaliacoes)): ?>
								<div class="comment-item">
									<div class="comment-header">
										<div class="user-info">
											<?php 
											$user_img = htmlspecialchars(fotoOuPadrao($avaliacao['cliente_foto']));
											?>
											<img src="<?= $user_img ?>" alt="Avatar" class="user-avatar" onerror="this.src='assets/img/default-avatar.png'">
											<div>
												<strong><?= htmlspecialchars($avaliacao['nome_cliente']) ?></strong>
												<div class="comment-rating">
													<?php 
													$nota = isset($avaliacao['avl_nota']) ? $avaliacao['avl_nota'] : 0;
													for ($i = 1; $i <= 5; $i++) {
														if ($i <= $nota) {
															echo '<i class="fas fa-star"></i>';
														} else {
															echo '<i class="far fa-star"></i>';
														}
													}
													?>
												</div>
												<small class="comment-date">
													<?= date('d/m/Y H:i', strtotime($avaliacao['avl_data'])) ?>
												</small>
											</div>
										</div>
										
										<?php if ($cliente_logado_id > 0 && $cliente_logado_id == $avaliacao['cli_codigo']): ?>
											<button type="button" class="btn-delete-comment" 
													onclick="deletarAvaliacao(<?= $avaliacao['avl_codigo'] ?>)">
												<i class="fas fa-trash"></i> Excluir
											</button>
										<?php endif; ?>
									</div>
									
									<p class="comment-text"><?= nl2br(htmlspecialchars($avaliacao['avl_comentario'])) ?></p>
								</div>
							<?php endwhile; ?>
						<?php else: ?>
							<div class="no-comments">
								<i class="far fa-comments"></i>
								<p>Ainda não há avaliações para este prestador.</p>
								<p>Seja o primeiro a avaliar!</p>
							</div>
						<?php endif; ?>
					</div>
				</div>

			</div>
		</div>
		<!-- /Page Content -->
		
		<!-- Footer -->
		<footer class="footer">
			<div class="footer-bottom">
				<div class="container-fluid">
					<div class="copyright">
						<div class="row">
							<div class="col-md-6 col-lg-6">
								<div class="copyright-text">
									<p class="mb-0">&copy; 2025 Nexus Network. Todos os direitos reservados.</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</footer>
		<!-- /Footer -->
   
	</div>
	<!-- /Main Wrapper -->
  
	<!-- jQuery -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
	
	<!-- Bootstrap Core JS -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
	
	<!-- Fancybox JS -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>

	<script>
		// Validação do formulário
		const commentForm = document.getElementById('commentForm');
		if (commentForm) {
			commentForm.addEventListener('submit', function(e) {
				const rating = document.querySelector('input[name="avaliacao"]:checked');
				if (!rating) {
					e.preventDefault();
					alert('Por favor, selecione uma avaliação.');
					return false;
				}
				
				const comentario = document.getElementById('comentario').value.trim();
				if (comentario.length === 0) {
					e.preventDefault();
					alert('Por favor, escreva um comentário.');
					return false;
				}
			});
		}

		// Função para deletar avaliação
		function deletarAvaliacao(avaliacaoId) {
			if (!confirm('Tem certeza que deseja excluir esta avaliação?')) {
				return;
			}

			fetch('deletar_avaliacao.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: 'avaliacao_id=' + avaliacaoId
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					alert(data.message);
					location.reload();
				} else {
					alert(data.message);
				}
			})
			.catch(error => {
				alert('Erro ao excluir avaliação.');
				console.error('Error:', error);
			});
		}
	</script>
</body>
</html>

<?php
// Fechar statements se existirem
if (isset($stmt_avaliacoes)) {
	mysqli_stmt_close($stmt_avaliacoes);
}
mysqli_close($conn);
?>