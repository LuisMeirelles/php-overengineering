<?php

namespace App\Exceptions;

use App\Core\AppException;

class NotFoundException extends AppException
{
    protected $code = 404;
    protected $message = 'Not Found';
}