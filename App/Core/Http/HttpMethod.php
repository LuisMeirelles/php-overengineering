<?php

namespace App\Core\Http;

enum HttpMethod: string
{
    case POST = 'POST';
    case GET = 'GET';
    case PUT = 'PUT';
    case DELETE = 'DELETE';
}
