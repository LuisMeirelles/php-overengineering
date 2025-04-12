<?php

global $router;

use App\Modules\User\Controllers\CreateUser;
use App\Modules\User\Controllers\GetUser;

$router->post('/users', new CreateUser());
$router->get('/users/:id', new GetUser());
