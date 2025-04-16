<?php

declare(strict_types=1);

namespace App\Core;

trait Singleton
{
    protected static ?self $instance = null;

    private function __construct()
    {
    }

    public static function getInstance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }
}