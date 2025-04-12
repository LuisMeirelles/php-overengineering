<?php

namespace App\Core\Caching;

use App\Core\AppException;
use Override;

class ApcuCachingStrategy implements CachingStrategy
{

    /**
     * @throws \App\Core\AppException
     */
    #[Override]
    public function store(string $key, mixed $value): void
    {
        $success = apcu_store($key, $value);

        if ($success !== true) {
            throw new AppException('Caching strategy store failed.');
        }
    }

    #[Override]
    public function fetch(string $key): mixed
    {
        $value = apcu_fetch($key, $success);

        if (!$success) {
            return null;
        }

        return $value;
    }
}