<?php

namespace App\Core\Caching;

interface CachingStrategy
{
    public function store(string $key, mixed $value): void;
    public function fetch(string $key): mixed;
}