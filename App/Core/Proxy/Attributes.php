<?php

namespace App\Core\Proxy;

readonly class Attributes
{
    public function __construct(
        /** @var \Attribute[] $attributes */
        public array $attributes,

        /** @var Property[] */
        public array $properties,

        /** @var array<string, \Attribute[]> */
        public array $methods,
    )
    {
    }
}