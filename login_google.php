<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
require_once 'vendor/autoload.php';
require_once 'google_config.php';

$clientID = GOOGLE_CLIENT_ID;
$clientSecret = GOOGLE_CLIENT_SECRET;
$redirectUri = GOOGLE_REDIRECT_URI;

$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope("email");
$client->addScope("profile");

// Redireciona para a página de autorização do Google
header('Location: ' . $client->createAuthUrl());
exit;
?>