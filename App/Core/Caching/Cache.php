<?php

namespace App\Core\Caching;

/**
 * @mixin CachingStrategy
 */
class Cache
{
    public function __construct(private CachingStrategy $strategy)
    {
    }

    public function __call(string $name, array $arguments)
    {
        return call_user_func_array([$this->strategy, $name], $arguments);
    }

    public function getStrategy(): CachingStrategy
    {
        return $this->strategy;
    }

    public function setStrategy(CachingStrategy $strategy): Cache
    {
        $this->strategy = $strategy;
        return $this;
    }
}