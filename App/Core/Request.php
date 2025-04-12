<?php

namespace App\Core;

use App\Core\Http\HttpMethod;
use App\Exceptions\NotImplementedException;

class Request
{
    private HttpMethod $method;

    private string $uri;
    private object $routeParams;

    /**
     * @throws \App\Exceptions\NotImplementedException
     */
    public function __construct()
    {
        $requestedMethod = $_SERVER['REQUEST_METHOD'];
        $method = HttpMethod::tryFrom($requestedMethod);

        if ($method === null) {
            throw new NotImplementedException(
                'The requested method was not implemented',
                [
                    'requested' => $requestedMethod,
                    'allowed' => HttpMethod::cases()
                ]
            );
        }

        $this->method = $method;
        $this->uri = $_SERVER['REQUEST_URI'];
    }

    /**
     * @throws \JsonException
     */
    public function body()
    {
        return json_decode(file_get_contents('php://input'), flags: JSON_THROW_ON_ERROR);
    }

    public function route(): object
    {
        return $this->routeParams;
    }

    public function getMethod(): HttpMethod
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function setRouteParams(array $params): void
    {
        $this->routeParams = (object)$params;
    }
}