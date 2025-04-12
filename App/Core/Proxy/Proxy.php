<?php

namespace App\Core\Proxy;

use App\Core\Caching\Cache;
use App\Core\Caching\ApcuCachingStrategy;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

class Proxy
{
    /**
     * @param ?class-string $className
     */
    public static function getCachedClass(?string $className = null): Attributes
    {
        $cache = new Cache(new ApcuCachingStrategy);

        $className ??= static::class;
        $cacheKey = "attributes_$className";

        $attributes = $cache->fetch($cacheKey);

        if (!$attributes) {
            $classReflection = new ReflectionClass($className);

            $propertiesReflections = $classReflection->getProperties();
            $methodsReflections = $classReflection->getMethods();

            $attributes = new Attributes(
                attributes: self::getClassAttributes($classReflection),
                properties: self::getPropertiesMetadata($propertiesReflections),
                methods: self::getMethodsAttributes($methodsReflections),
            );

            $cache->store($cacheKey, $attributes);
        }

        return $attributes;
    }

    /**
     * @return \Attribute[]
     */
    private static function getClassAttributes(ReflectionClass $classReflection): array
    {
        return array_map(fn($attribute) => $attribute->newInstance(), $classReflection->getAttributes());
    }

    /**
     * @param ReflectionMethod[] $reflections
     * @return array<string, array<int, \Attribute>>
     */
    private static function getMethodsAttributes(array $reflections): array
    {
        $attributes = [];

        foreach ($reflections as $reflection) {
            $memberName = $reflection->getName();
            $attributesReflections = $reflection->getAttributes();

            $attributes[$memberName] = [];

            foreach ($attributesReflections as $attributeReflection) {
                $attribute = $attributeReflection->newInstance();
                $attributes[$memberName][] = $attribute;
            }
        }

        return $attributes;
    }

    /**
     * @param ReflectionProperty[] $reflections
     * @return Property[]
     */
    private static function getPropertiesMetadata(array $reflections): array
    {
        $metadata = [];

        foreach ($reflections as $reflection) {
            if (!$reflection->isPublic()) {
                continue;
            }

            $memberName = $reflection->getName();
            $attributesReflections = $reflection->getAttributes();

            $attributes = [];

            foreach ($attributesReflections as $attributeReflection) {
                $attribute = $attributeReflection->newInstance();
                $attributes[] = $attribute;
            }

            $reflectionIntersectionType = $reflection->getType();

            $metadata[] = new Property(
                name: $memberName,
                allowsNull: $reflectionIntersectionType->allowsNull(),
                attributes: $attributes,
                type: $reflectionIntersectionType->getName(),
            );
        }

        return $metadata;
    }
}
