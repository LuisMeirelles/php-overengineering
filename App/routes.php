<?php

declare(strict_types=1);

use App\Core\Routing\Router;
use App\Modules\User\Controllers\CreateUser;
use App\Modules\User\Controllers\GetUser;

$router = Router::getInstance();

$router->post('/users', new CreateUser());
$router->get('/users/:id', new GetUser());
