<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

use App\Core\AppException;
use App\Core\Environment\EnvLoader;
use App\Core\Request;
use App\Core\Routing\Router;
use App\Exceptions\InternalServerErrorException;

require 'vendor/autoload.php';

$router = Router::getInstance();

require 'App/routes.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

header('Content-Type: application/json');

try {
    EnvLoader::init();
    $request = new Request();
    $response = $router->dispatch($request);
} catch (AppException $e) {
    $response = $e->handleResponse();
} catch (Throwable $throwable) {
    $response = new InternalServerErrorException(previous: $throwable)->handleResponse();
}

if ($response !== null) {
    echo json_encode($response);
}
