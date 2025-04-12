<?php

namespace App\Exceptions;

use App\Core\AppException;

class NotImplementedException extends AppException
{
    protected $code = 501;
    protected $message = 'Not Implemented';
}