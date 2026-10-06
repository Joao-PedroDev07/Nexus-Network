<?php
/**
 * Configuração do login com Google (usada por login_google.php e google-callback.php)
 *
 * IMPORTANTE: GOOGLE_REDIRECT_URI precisa ser EXATAMENTE igual a uma das
 * "URIs de redirecionamento autorizadas" cadastradas no Google Cloud Console
 * (APIs e serviços > Credenciais > ID do cliente OAuth).
 *
 * O valor abaixo funciona quando o projeto está em:
 *   C:\xampp\htdocs\Nexus Network - Grupo 4\NexusNetwork
 * Se a pasta do projeto for outra, altere aqui E cadastre o novo endereço no Google.
 */
define('GOOGLE_CLIENT_ID', '413048648794-vr0kcperf75a3c4qk644b7e1lb80c57n.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', 'http://localhost/Nexus%20Network%20-%20Grupo%204/NexusNetwork/google-callback.php');
