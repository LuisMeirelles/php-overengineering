<?php

namespace Meirelles\BackendBrCryptography\Core;

use Meirelles\BackendBrCryptography\Env;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;

class EnvLoader
{
    /**
     * @throws \Meirelles\BackendBrCryptography\Core\AppException
     */
    public function __construct()
    {
        self::init();
    }

    /**
     * @return void
     * @throws \Meirelles\BackendBrCryptography\Core\AppException
     */
    public static function init(): void
    {
        self::validateVars();
    }

    /**
     * @return void
     * @throws \Meirelles\BackendBrCryptography\Core\AppException
     */
    private static function validateVars(): void
    {
        $environment = Env::getInstance();

        $reflectionClass = new ReflectionClass($environment);
        $reflectionProperties = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($reflectionProperties as $reflectionProperty) {
            $propertyName = $reflectionProperty->getName();

            $variableName = preg_replace('/([A-Z])/', '_$1', $propertyName);
            $variableName = mb_convert_case($variableName, MB_CASE_UPPER);

            $value = getenv($variableName);

            if ($value === false && !$reflectionProperty->getType()->allowsNull()) {
                throw new AppException("The environment variable `$variableName` is required");
            }

            $value = $value ?: null;

            self::castType($value, $reflectionProperty, $environment);

            /** @var ReflectionAttribute<EnvValidator>[] $reflectionAttributes */
            $reflectionAttributes = $reflectionProperty->getAttributes(EnvValidator::class, ReflectionAttribute::IS_INSTANCEOF);

            foreach ($reflectionAttributes as $reflectionAttribute) {
                $attribute = $reflectionAttribute->newInstance();

                $attribute->value = $value;
                $attribute->name = $propertyName;

                $attribute->validate();
            }
        }
    }

    /**
     * @throws \Meirelles\BackendBrCryptography\Core\AppException
     */
    private static function castType(?string $value, ReflectionProperty $reflectionProperty, Env $environment): void
    {
        if ($value === null) {
            $reflectionProperty->setValue($environment, null);
            return;
        }

        $type = $reflectionProperty->getType();

        switch ($type->getName()) {
            case 'int':
                $reflectionProperty->setValue($environment, (int)$value);
                break;
            case 'bool':
                $reflectionProperty->setValue($environment, (bool)$value);
                break;
            case 'float':
                $reflectionProperty->setValue($environment, (float)$value);
                break;
            case 'string':
                $reflectionProperty->setValue($environment, $value);
                break;
            default:
                throw new AppException('Type not supported');
        }
    }
}