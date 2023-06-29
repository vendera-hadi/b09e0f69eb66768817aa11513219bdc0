<?php
session_start();
require "bootstrap.php";

use Connections\PostgreSql as Connection;

$provider = new League\OAuth2\Client\Provider\Github([
    'clientId'          => $_ENV["OAUTH_CLIENT_ID"],
    'clientSecret'      => $_ENV["OAUTH_CLIENT_SECRET"],
    'redirectUri'       => $_ENV["OAUTH_REDIRECT_URL"]
]);

if (!isset($_SESSION['code'])) {
    // Oauth Authorization
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: '.$authUrl);
    exit;
};

// send some CORS headers so the API can be called from anywhere
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER["REQUEST_METHOD"];
$uriParts = explode('/', $uri);

// define all valid endpoints - this will act as a simple router
$routes = [
    'emails' => [
        'method' => 'POST',
        'expression' => '/^\/emails\/?$/',
        'controller_method' => 'insert',
        'title' => 'POST /emails',
        'desc' => 'execute send email'
    ]
];

// main url, print route list
if ($uri == "/") {
    $forview = array('title', 'desc');
    $response = [];
    $response["routes"] = [];
    foreach ($routes as $key => $val) {
        $response["routes"][$key] = array_filter($val, function ($v) use ($forview) {
            return in_array($v, $forview);
        }, ARRAY_FILTER_USE_KEY);
    }
    http_response_code(200);
    echo json_encode($response);
    exit();
}

$routeFound = null;
foreach ($routes as $route) {
    if ($route['method'] == $requestMethod && preg_match($route['expression'], $uri))
    {
        $routeFound = $route;
        break;
    }
}

if (! $routeFound) {
    http_response_code(404);
    $response = [];
    $response["message"] = "Route Not Found";
    echo json_encode($response);
    exit();
}

try {
    $connection = Connection::get()->connect();
    // echo 'A connection to the PostgreSQL database sever has been established successfully.';
    $table_query = "CREATE TABLE IF NOT EXISTS emails (
        id SERIAL,
        receiver varchar(200) NOT NULL,
        subject varchar(200),
        message TEXT)";
    $connection->exec($table_query);
} catch (\PDOException $e) {
    echo $e->getMessage();
    exit;
}

$methodName = $route['controller_method'];

$controller = new Controllers\EmailController($connection);
$controller->$methodName($uriParts);
