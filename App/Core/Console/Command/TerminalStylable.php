<?php

namespace App\Core\Console\Command;

trait TerminalStylable
{
    final public static function success(string $message): void
    {
        echo "\033[32m$message\033[0m\n";
    }

    final public static function error(string $message): void
    {
        echo "\033[31m$message\033[0m\n";
    }

    final public static function warning(string $message): void
    {
        echo "\033[33m$message\033[0m\n";
    }

    final public static function info(string $message): void
    {
        echo "\033[36m$message\033[0m\n";
    }

    final public static function debug(string $message): void
    {
        echo "\033[35m$message\033[0m\n";
    }
}