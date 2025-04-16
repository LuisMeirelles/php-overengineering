<?php
declare(strict_types=1);

namespace App\Core\Caching;

class FileCachingStrategy implements CachingStrategy
{

    public function store(string $key, mixed $value): void
    {
        $content = serialize($value);
        $path = BASE_PATH . "/storage/cache/$key.cache";
        file_put_contents($path, $content);
    }

    public function fetch(string $key): mixed
    {
        $path = BASE_PATH . "/storage/cache/$key.cache";
        if (!file_exists($path)) {
            return null;
        }
        $content = file_get_contents($path);
        return unserialize($content);
    }
}