<?php
require 'vendor/autoload.php';
session_start();

// load env
use Dotenv\Dotenv;

$dotenv = DotEnv::createImmutable(__DIR__);
$env = $dotenv->load();

$provider = new League\OAuth2\Client\Provider\Github([
        'clientId'          => $_ENV["OAUTH_CLIENT_ID"],
        'clientSecret'      => $_ENV["OAUTH_CLIENT_SECRET"],
        'redirectUri'       => $_ENV["OAUTH_REDIRECT_URL"]
    ]);
var_dump($provider);
$token = $provider->getAccessToken('authorizationCode', [
    'code' => $_GET['code']
]);

// Optional: Now you have a token you can look up a users profile data
try {
    // We got an access token, let's now get the user's details
    $user = $provider->getResourceOwner($token);
    // Use these details to create a new profile
    printf('Hello %s!', $user->getNickname());
} catch (Exception $e) {
    // Failed to get user details
    exit('Oh dear...');
}

$_SESSION["code"] = $_GET['code'];
$_SESSION["access_token"] = $token->getToken();
header("Location: index.php");
