<?php

namespace App\Core\Environment\Enums;

enum Environment: string
{
    case Local = 'local';
    case Staging = 'staging';
    case Production = 'production';
}
