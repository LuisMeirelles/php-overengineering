<?php

namespace App\Core\Proxy;

use Attribute as T;

readonly class Property
{

    public function __construct(
        public string $name,
        public bool   $allowsNull,
        /** @var \Attribute[] $attributes */
        public array  $attributes,
        public string $type,
    )
    {
    }

    /**
     * @template T of \Attribute
     *
     * Returns an array of class attributes.
     *
     * @param class-string<T>|null $attributeClassName Name of an attribute class
     * @return T[]
     */
    public function getCachedAttributes(?string $attributeClassName = null): array
    {
        if ($attributeClassName === null) {
            return $this->attributes;
        }

        return array_filter($this->attributes, fn($attribute) => is_a($attribute, $attributeClassName));
    }
}