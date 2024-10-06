<?php

namespace Meirelles\BackendBrCryptography\Cryptography\Exceptions;

use Meirelles\BackendBrCryptography\Core\AppException;

class InternalServerErrorException extends AppException
{
    protected $code = 500;
    protected $message = 'Internal Server Error';
}