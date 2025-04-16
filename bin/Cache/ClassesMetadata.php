<?php

declare(strict_types=1);

use App\Core\Caching\Cache;
use App\Core\Caching\FileCachingStrategy;
use App\Core\Console\Command\Command;
use App\Core\Proxy\Attributes;
use App\Core\Proxy\Property;

return new class extends Command {
    public string $description = 'Generate metadata cache';

    public array $parameters = [];

    public function handle(string ...$args): void
    {
        $this->info('Generating metadata cache...');

        $cache = new Cache(new FileCachingStrategy());

        $map = require BASE_PATH . '/vendor/composer/autoload_psr4.php';

        $attributes = [];

        foreach ($map as $namespace => $paths) {
            foreach ($paths as $path) {
                if (str_starts_with($path, BASE_PATH . '/vendor')) {
                    continue;
                }

                $classes = self::getAllClassesFromDirectory($path, $namespace);

                foreach ($classes as $className) {
                    $reflectionClass = new ReflectionClass($className);

                    $propertiesReflections = $reflectionClass->getProperties();
                    $methodsReflections = $reflectionClass->getMethods();

                    $attributes[$className] = new Attributes(
                        attributes: self::getClassAttributes($reflectionClass),
                        properties: self::getPropertiesMetadata($propertiesReflections),
                        methods: self::getMethodsAttributes($methodsReflections),
                    );
                }
            }
        }

        $cache->store('attributes', $attributes);
    }

    private static function getClassAttributes(ReflectionClass $reflectionClass): array
    {
        return array_map(fn($attribute) => $attribute->newInstance(), $reflectionClass->getAttributes());
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

            $reflectionType = $reflection->getType();

            $getTypeNames = fn($reflectionType) => array_map(fn($type) => $type->getName(), $reflectionType->getTypes());

            $isUnionType = $reflectionType instanceof ReflectionUnionType;
            $isIntersectionType = $reflectionType instanceof ReflectionIntersectionType;
            $isSimpleType = $reflectionType instanceof ReflectionNamedType;

            $typeName = match (true) {
                $isUnionType => implode('|', $getTypeNames($reflectionType)),
                $isIntersectionType => implode('&', $getTypeNames($reflectionType)),
                $isSimpleType => $reflectionType->getName(),

                default => 'mixed',
            };

            $metadata[] = new Property(
                name: $memberName,
                allowsNull: $reflectionType->allowsNull(),
                attributes: $attributes,
                type: $typeName,
            );
        }

        return $metadata;
    }

    static function getAllClassesFromDirectory(string $baseDir, string $baseNamespace = ''): array
    {
        $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir));
        $classes = [];

        foreach ($rii as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = str_replace([$baseDir . '/', '.php'], '', $file->getPathname());
            $relativePath = str_replace('/', '\\', $relativePath);
            $class = $baseNamespace . $relativePath;

            if (class_exists($class) || trait_exists($class) || interface_exists($class)) {
                $classes[] = $class;
            }
        }

        return $classes;
    }
};