<?php

namespace App\Exceptions;

use App\Core\AppException;

class InternalServerErrorException extends AppException
{
    protected $code = 500;
    protected $message = 'Internal Server Error';
}