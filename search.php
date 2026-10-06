<?php
	session_start();
	include_once("conexao.php");
	include_once("geocode_helper.php");
	require_once("foto_helper.php");

	// Obter localização do usuário
	$user_lat = null;
	$user_lon = null;
	$user_location = null;

	// Tentar obter localização da sessão (se usuário logado como cliente)
	if(isset($_SESSION['usuario_id']) && ($_SESSION['tipo_usuario'] ?? '') === 'cliente') {
		$cli_codigo = intval($_SESSION['usuario_id']);
		$sql_cliente = "SELECT cli_latitude, cli_longitude, cli_cidade, cli_estado 
		                FROM clientes WHERE cli_codigo = $cli_codigo";
		$result_cliente = mysqli_query($conn, $sql_cliente);
		
		if($result_cliente && mysqli_num_rows($result_cliente) > 0) {
			$cliente = mysqli_fetch_assoc($result_cliente);
			
			// Se não tem coordenadas, obter e salvar
			if(empty($cliente['cli_latitude']) || empty($cliente['cli_longitude'])) {
				$coords = obterCoordenadas($cliente['cli_cidade'], $cliente['cli_estado'], $conn);
				if($coords) {
					$update = "UPDATE clientes 
					          SET cli_latitude = {$coords['latitude']}, 
					              cli_longitude = {$coords['longitude']}
					          WHERE cli_codigo = $cli_codigo";
					mysqli_query($conn, $update);
					$user_lat = $coords['latitude'];
					$user_lon = $coords['longitude'];
				}
			} else {
				$user_lat = $cliente['cli_latitude'];
				$user_lon = $cliente['cli_longitude'];
			}
			$user_location = $cliente['cli_cidade'] . ', ' . $cliente['cli_estado'];
		}
	}
	
	// Se não conseguiu pegar da sessão, tentar por geolocalização do navegador (via POST)
	if(isset($_POST['user_lat']) && isset($_POST['user_lon'])) {
		$user_lat = floatval($_POST['user_lat']);
		$user_lon = floatval($_POST['user_lon']);
		$_SESSION['user_lat'] = $user_lat;
		$_SESSION['user_lon'] = $user_lon;
	} elseif(isset($_SESSION['user_lat']) && isset($_SESSION['user_lon'])) {
		$user_lat = $_SESSION['user_lat'];
		$user_lon = $_SESSION['user_lon'];
	}
	
	// Fallback: tentar por IP
	if($user_lat === null || $user_lon === null) {
		$location = obterLocalizacaoPorIP();
		if($location) {
			$user_lat = $location['latitude'];
			$user_lon = $location['longitude'];
			$user_location = $location['cidade'] . ', ' . $location['estado'];
		}
	}
	
	// Garante que as coordenadas sejam numéricas (são usadas direto no SQL)
	if($user_lat !== null && $user_lon !== null) {
		$user_lat = (float)$user_lat;
		$user_lon = (float)$user_lon;
	}

	// Raio de busca (em km)
	$raio_busca = isset($_GET['raio']) ? intval($_GET['raio']) : 999999;
	if($raio_busca <= 0) {
		$raio_busca = 999999;
	}

	// Ordenação escolhida (somente valores permitidos)
	$ordem = (isset($_GET['ordem']) && in_array($_GET['ordem'], ['distancia', 'avaliacao', 'preco'])) ? $_GET['ordem'] : 'distancia';

	// Profissões cadastradas no banco (para o filtro)
	$profissoes_list = [];
	$result_prof = mysqli_query($conn, "SELECT DISTINCT pres_profissao FROM prestadores WHERE pres_profissao <> '' ORDER BY pres_profissao");
	if($result_prof) {
		while($row_prof = mysqli_fetch_assoc($result_prof)) {
			$profissoes_list[] = $row_prof['pres_profissao'];
		}
	}
?>

<!DOCTYPE html> 
<html lang="pt-BR">
	
<head>
		<meta charset="utf-8">
		<title>Buscar Prestadores</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
		
		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="assets/css/bootstrap.min.css">
		
		<!-- Fontawesome CSS -->
		<link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css">
		<link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">
		
		<!-- Datetimepicker CSS -->
		<link rel="stylesheet" href="assets/css/bootstrap-datetimepicker.min.css">
		
		<!-- Select2 CSS -->
		<link rel="stylesheet" href="assets/plugins/select2/css/select2.min.css">
		
		<!-- Fancybox CSS -->
		<link rel="stylesheet" href="assets/plugins/fancybox/jquery.fancybox.min.css">
		
		<!-- Main CSS -->
		<link rel="stylesheet" href="assets/css/style.css">
		<link rel="stylesheet" href="assets/css/header.css">

		<script src="assets/js/header.js" defer></script>
		<link rel="icon" href="assets/img/logo-transparente.png">
		<link rel="stylesheet" href="assets/css/search.css">

	</head>
	<body>
		<!-- Header -->
         <?php include_once("header.php"); ?>
        <!-- /Header -->

		<!-- Main Wrapper -->
		<div class="main-wrapper">
			
			<!-- Breadcrumb -->
				<div class="container-fluid">
					<div class="row align-items-center">
						<h2 class="breadcrumb-title">
							<?php
								$termo_pesquisa = "";
								if(isset($_GET['search']) && !empty($_GET['search'])) {
									$termo_pesquisa = $_GET['search'];
									echo "Resultados para: " . htmlspecialchars($termo_pesquisa);
								} else {
									echo "Prestadores próximos a você";
								}
							?>
						</h2>
					</div>
				</div>
			<!-- /Breadcrumb -->
			
			<!-- Page Content -->
			<div class="content">
				<div class="container-fluid">

					<div class="row">
						<div class="col-md-12 col-lg-4 col-xl-3 theiaStickySidebar">
						
							<!-- Search Filter -->
							<div class="card search-filter">
								<div class="card-header">
									<h4 class="card-title mb-0">Filtros de Busca</h4>
								</div>

								<div class="card-body">

									<div class="container">
										<div class="row">
											<div class="col-md-4 col-12 d-md-block d-none">
												<div class="sort-by">
													<strong>Ordenar</strong>
													<span class="sortby-fliter">
														<select class="select" id="ordenacao" onchange="ordenarResultados()">
															<option value="distancia" class="opcao" <?php echo $ordem == 'distancia' ? 'selected' : ''; ?>>Distância</option>
															<option value="avaliacao" class="opcao" <?php echo $ordem == 'avaliacao' ? 'selected' : ''; ?>>Avaliação</option>
															<option value="preco" class="opcao" <?php echo $ordem == 'preco' ? 'selected' : ''; ?>>Menor Preço</option>
														</select>
												</div>
											</div>
										</div>
									</div><br><br>

									<?php if($user_location): ?>
									<div class="location-info">
										<i class="fas fa-map-marker-alt"></i>
										<strong>Sua localização:</strong><br>
										<small><?php echo htmlspecialchars($user_location); ?></small>
									</div>
									<?php endif; ?>
									
									<form method="GET" action="">
										<div class="filter-widget">
											<h4>Pesquisar</h4>
											<input type="text" class="form-control" name="search" placeholder="Nome ou profissão" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
										</div>
										
										<div class="filter-widget">
											<h4>Raio de Busca</h4>
											<select class="form-control" name="raio">
												<option value="5" <?php echo $raio_busca == 5 ? 'selected' : ''; ?>>5 km</option>
												<option value="10" <?php echo $raio_busca == 10 ? 'selected' : ''; ?>>10 km</option>
												<option value="25" <?php echo $raio_busca == 25 ? 'selected' : ''; ?>>25 km</option>
												<option value="50" <?php echo $raio_busca == 50 ? 'selected' : ''; ?>>50 km</option>
												<option value="100" <?php echo $raio_busca == 100 ? 'selected' : ''; ?>>100 km</option>
												<option value="999999" <?php echo $raio_busca == 999999 ? 'selected' : ''; ?>>Todos</option>
											</select>
										</div>
										
										<div class="filter-widget">
											<h4>Profissão</h4>
											<?php
											foreach ($profissoes_list as $profissao) {
												$checked = '';
												if(isset($_GET['profissao']) && is_array($_GET['profissao']) && in_array($profissao, $_GET['profissao'])) {
													$checked = 'checked';
												}
												$profissao_html = htmlspecialchars($profissao);
												echo '
												<div>
													<label class="custom_check">
														<input type="checkbox" name="profissao[]" value="'.$profissao_html.'" '.$checked.'>
														<span class="checkmark"></span> '.$profissao_html.'
													</label>
												</div>';
											}
											?>
										</div>
										
										<input type="hidden" name="ordem" value="<?php echo htmlspecialchars($ordem); ?>">
										
										<div class="btn-search">
											<button type="submit" class="btn btn-block">Buscar Prestadores</button>
										</div>
									</form>
								</div>
							</div>
							<!-- /Search Filter -->
							
						</div>
						
						<div class="col-md-12 col-lg-8 col-xl-9">

							<!-- Prestadores -->
							<?php
								if(isset($_SESSION['msg'])) {
									echo $_SESSION['msg'];
									unset($_SESSION['msg']);
								}
								
								// Paginação
								$pagina_atual = filter_input(INPUT_GET,'pagina', FILTER_SANITIZE_NUMBER_INT);		
								$pagina = max(1, intval($pagina_atual));
								$qnt_result_pg = 20;
								$inicio = ($qnt_result_pg * $pagina) - $qnt_result_pg;
								
								// Construir WHERE
								$where_conditions = [];
								
								// Filtro de profissão
								if(isset($_GET['profissao']) && is_array($_GET['profissao']) && !empty($_GET['profissao'])) {
									$profissoes = array_map(function($prof) use ($conn) {
										return mysqli_real_escape_string($conn, $prof);
									}, $_GET['profissao']);
									$where_conditions[] = "prestadores.pres_profissao IN ('" . implode("','", $profissoes) . "')";
								}

								// Filtro de pesquisa
								if(isset($_GET['search']) && !empty($_GET['search'])) {
									$search_term = mysqli_real_escape_string($conn, $_GET['search']);
									$where_conditions[] = "(prestadores.pres_nome LIKE '%$search_term%' OR prestadores.pres_profissao LIKE '%$search_term%')";
								}
								
								// Filtro de coordenadas (não nulas)
								$where_conditions[] = "prestadores.pres_latitude IS NOT NULL";
								$where_conditions[] = "prestadores.pres_longitude IS NOT NULL";

								$where_clause = "";
								if (!empty($where_conditions)) {
									$where_clause = "WHERE " . implode(" AND ", $where_conditions);
								}
								
								// Ordenação ($ordem já validada no topo da página)
								$order_clause = "";
								
								if($user_lat !== null && $user_lon !== null) {
									// Calcular distância na query E incluir média de avaliação
									$sql = "SELECT prestadores.*, 
									        COUNT(avaliacao.avl_comentario) AS total_comentarios,
									        AVG(avaliacao.avl_nota) as media_avaliacao,
									        ROUND(
									            6371 * acos(
									                cos(radians($user_lat)) * 
									                cos(radians(prestadores.pres_latitude)) * 
									                cos(radians(prestadores.pres_longitude) - radians($user_lon)) + 
									                sin(radians($user_lat)) * 
									                sin(radians(prestadores.pres_latitude))
									            ), 2
									        ) AS distancia
									        FROM prestadores
									        LEFT JOIN avaliacao ON prestadores.pres_codigo = avaliacao.pres_codigo
									        $where_clause
									        GROUP BY prestadores.pres_codigo
									        HAVING distancia <= $raio_busca";
									
									// Adicionar ordenação
									switch($ordem) {
										case 'avaliacao':
											$sql .= " ORDER BY media_avaliacao DESC, total_comentarios DESC, distancia ASC";
											break;
										case 'preco':
											$sql .= " ORDER BY prestadores.pres_precomin ASC, distancia ASC";
											break;
										default:
											$sql .= " ORDER BY distancia ASC";
									}
								} else {
									// Sem geolocalização - INCLUIR média de avaliação
									$sql = "SELECT prestadores.*, 
									        COUNT(avaliacao.avl_comentario) AS total_comentarios,
									        AVG(avaliacao.avl_nota) as media_avaliacao
									        FROM prestadores
									        LEFT JOIN avaliacao ON prestadores.pres_codigo = avaliacao.pres_codigo
									        $where_clause
									        GROUP BY prestadores.pres_codigo";
									
									switch($ordem) {
										case 'avaliacao':
											$sql .= " ORDER BY media_avaliacao DESC, total_comentarios DESC";
											break;
										case 'preco':
											$sql .= " ORDER BY prestadores.pres_precomin ASC";
											break;
										default:
											$sql .= " ORDER BY prestadores.pres_nome ASC";
									}
								}
								
								$sql .= " LIMIT $inicio, $qnt_result_pg";
								
								$comando = mysqli_query($conn, $sql);

								if($comando && mysqli_num_rows($comando) > 0) {
									while($row_prestadores = mysqli_fetch_assoc($comando)) {
										// Calcular média de avaliação
										$media_avaliacao = $row_prestadores['media_avaliacao'] ? round($row_prestadores['media_avaliacao'], 1) : 0;
										$total_avaliacoes = $row_prestadores['total_comentarios'];

										// Escapa os textos vindos do banco antes de exibir
										foreach (['pres_nome', 'pres_profissao', 'pres_telefone', 'pres_email', 'pres_cidade', 'pres_estado', 'pres_descricao'] as $campo) {
											// double_encode = false: dados já gravados com entidades HTML não ficam "&amp;amp;"
											$row_prestadores[$campo] = htmlspecialchars($row_prestadores[$campo] ?? '', ENT_QUOTES, 'UTF-8', false);
										}
										$row_prestadores['pres_foto'] = htmlspecialchars(fotoOuPadrao($row_prestadores['pres_foto']));
							?>
								<div class="card">
									<div class="card-body">
										<div class="doctor-widget">
											<div class="doc-info-left">
												<div class="doctor-img">
													<a href="info_prestador.php?pres_codigo=<?php echo $row_prestadores['pres_codigo']; ?>">
														<img src="<?php echo $row_prestadores['pres_foto'];?>" class="img-fluid" alt="<?php echo $row_prestadores['pres_nome'];?>">
													</a>
												</div>
												
												<div class="doc-info-cont">
													<h4 class="doc-name">
														<a href="info_prestador.php?pres_codigo=<?php echo $row_prestadores['pres_codigo']; ?>">
															<?php echo $row_prestadores['pres_nome'];?>
														</a>
														<?php if(isset($row_prestadores['distancia'])): ?>
															<span class="distance-badge">
																<i class="fas fa-map-marker-alt"></i>
																<?php echo formatarDistancia($row_prestadores['distancia']); ?>
															</span>
															<?php if($row_prestadores['distancia'] <= 5): ?>
																<span class="nearby-label">PRÓXIMO</span>
															<?php endif; ?>
														<?php endif; ?>
													</h4>

													<h5 class="doc-department">
														<?php echo $row_prestadores['pres_profissao']; ?>
													</h5>
													
													<!-- Avaliação com estrelas dinâmicas do BD -->
													 
													<div class="rating" data-pres-id="<?php echo $row_prestadores['pres_codigo']; ?>">
														<?php
														// Exibir estrelas baseadas na média
														for ($i = 1; $i <= 5; $i++) {
															$class = ($i <= round($media_avaliacao)) ? 'filled' : '';
															echo '<i class="fas fa-star ' . $class . '" data-star="' . $i . '"></i>';
														}
														?>
														
														<?php if($total_avaliacoes > 0): ?> 
															<span class="rating-info">
																(<?php echo number_format($media_avaliacao, 1, ',', '.'); ?> - <?php echo $total_avaliacoes; ?>)
															</span>
														<?php else: ?>
															<span class="rating-info">
																(Sem avaliações)
															</span>
														<?php endif; ?>
													</div>
														
													<div class="clinic-services">
														<span><i class="fas fa-phone"></i> <?php echo $row_prestadores['pres_telefone'];?></span>
														<span><i class="fas fa-envelope"></i> <?php echo $row_prestadores['pres_email'];?></span>
													</div>
												</div>
											</div>

											<div class="doc-info-right">
												<div class="clini-infos">
													<ul>
														<li>
															<i class="far fa-comment"></i>
															<?php echo $row_prestadores['total_comentarios']; ?> Comentários
														</li>
														<li>
															<i class="fas fa-map-marker-alt"></i>
															<?php echo $row_prestadores['pres_cidade'] . ', ' . $row_prestadores['pres_estado'];?>
														</li>
														<li>
															<i class="far fa-money-bill-alt"></i>
															R$ <?php echo number_format($row_prestadores['pres_precomin'], 2, ',', '.'); ?> - 
															R$ <?php echo number_format($row_prestadores['pres_precomax'], 2, ',', '.'); ?>
														</li>
													</ul>
												</div>
												<a href="info_prestador.php?pres_codigo=<?php echo $row_prestadores['pres_codigo']; ?>" class="btn btn-primary btn-sm">
													Ver Perfil
												</a>
											</div>
											
											<?php if(!empty($row_prestadores['pres_descricao'])): ?>
											<div class="descricao">
												<p><?php echo $row_prestadores['pres_descricao'];?></p>
											</div>
											<?php endif; ?>
										</div>
									</div>
								</div>
							
							<?php
									}
								} else {
									echo "<div class='card'><div class='card-body'><p class='text-center'>Nenhum prestador encontrado nesta região.</p></div></div>";
								}
							?> 
							<!-- /Prestadores -->

						</div>
					</div>

				</div>
			</div>		
		</div>
			<!-- Footer -->
			<?php include_once("footer.php"); ?>
		<!-- /Main Wrapper -->
	  
		<!-- jQuery -->
		<script src="assets/js/jquery.min.js"></script>
		
		<!-- Bootstrap Core JS -->
		<script src="assets/js/popper.min.js"></script>
		<script src="assets/js/bootstrap.min.js"></script>
		
		<!-- Sticky Sidebar JS -->
        <script src="assets/plugins/theia-sticky-sidebar/ResizeSensor.js"></script>
        <script src="assets/plugins/theia-sticky-sidebar/theia-sticky-sidebar.js"></script>
		
		<!-- Select2 JS -->
		<script src="assets/plugins/select2/js/select2.min.js"></script>
		
		<!-- Datetimepicker JS -->
		<script src="assets/js/moment.min.js"></script>
		<script src="assets/js/bootstrap-datetimepicker.min.js"></script>
		
		<!-- Fancybox JS -->
		<script src="assets/plugins/fancybox/jquery.fancybox.min.js"></script>
		
		<!-- Custom JS -->
		<script src="assets/js/script.js"></script>
		
		<script>
		// Solicitar geolocalização do navegador
		if (navigator.geolocation && !<?php echo ($user_lat !== null) ? 'true' : 'false'; ?>) {
			navigator.geolocation.getCurrentPosition(function(position) {
				// Enviar coordenadas via AJAX
				$.ajax({
					url: 'salvar_localizacao.php',
					method: 'POST',
					data: {
						lat: position.coords.latitude,
						lon: position.coords.longitude
					},
					success: function() {
						// Recarregar página com nova localização
						location.reload();
					}
				});
			}, function(error) {
				console.log('Erro ao obter localização:', error);
			});
		}
		
		// Função para ordenar resultados
		function ordenarResultados() {
			var ordem = document.getElementById('ordenacao').value;
			var url = new URL(window.location.href);
			url.searchParams.set('ordem', ordem);
			window.location.href = url.toString();
		}
		</script>

		<!-- Cole este código NO FINAL da página search.php, depois de todos os scripts -->
<style>
/* FORÇA ABSOLUTA - Select2 Tema Escuro */
.select2-container--default .select2-selection--single,
.select2-container .select2-selection--single,
span.select2-selection.select2-selection--single {
    background-color: #0a0a0a !important;
    border: 2px solid #20cd8d !important;
    border-radius: 10px !important;
}

.select2-dropdown,
.select2-container--default .select2-dropdown {
    background-color: #0a0a0a !important;
    border: 2px solid #20cd8d !important;
    border-radius: 10px !important;
}

.select2-results__option,
.select2-container--default .select2-results__option {
    background-color: #0a0a0a !important;
    color: #ffffff !important;
}

.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #20cd8d !important;
    color: #000000 !important;
}

/* Força TUDO */
.select2-container *, .select2-dropdown * {
    background-color: #0a0a0a !important;
    color: #ffffff !important;
}

.select2-container--default .select2-results__option--highlighted * {
    background-color: #20cd8d !important;
    color: #000000 !important;
}

.select2-selection__arrow * {
    background-color: transparent !important;
}
</style>

<script>
// JavaScript que força os estilos após o Select2 ser inicializado
$(document).ready(function() {
    // Aguarda o Select2 ser totalmente inicializado
    setTimeout(function() {
        // Força estilos no dropdown quando ele abrir
        $(document).on('select2:open', function(e) {
            // Pega o dropdown que acabou de abrir
            const dropdown = $('.select2-dropdown');
            const results = $('.select2-results');
            const options = $('.select2-results__option');
            
            // Força estilos
            dropdown.css({
                'background-color': '#0a0a0a',
                'border': '2px solid #20cd8d',
                'border-radius': '10px'
            });
            
            results.css('background-color', '#0a0a0a');
            
            options.css({
                'background-color': '#0a0a0a',
                'color': '#ffffff'
            });
            
            // Força hover
            options.hover(
                function() {
                    $(this).css({
                        'background-color': '#20cd8d',
                        'color': '#000000'
                    });
                },
                function() {
                    if (!$(this).attr('aria-selected')) {
                        $(this).css({
                            'background-color': '#0a0a0a',
                            'color': '#ffffff'
                        });
                    }
                }
            );
        });
        
        // Força estilos no container principal
        $('.select2-container').each(function() {
            $(this).find('.select2-selection').css({
                'background-color': '#0a0a0a',
                'border': '2px solid #20cd8d',
                'color': '#ffffff'
            });
        });
    }, 100);
});
</script>
		
	</body>
</html>