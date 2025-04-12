<?php

namespace App\Core\Environment;

use BackedEnum;
use App\Core\AppException;
use App\Core\Environment\Validators\EnvValidator;
use App\Core\Proxy\Property;
use App\Core\Proxy\Proxy;
use App\Env;

class EnvLoader
{
    /**
     * @throws \App\Core\AppException
     */
    public function __construct()
    {
        self::init();
    }

    /**
     * @return void
     * @throws \App\Core\AppException
     */
    public static function init(): void
    {
        self::validateVars();
    }

    /**
     * @return void
     * @throws \App\Core\AppException
     */
    private static function validateVars(): void
    {
        $environment = Env::getInstance();

        $environmentMetadata = Proxy::getCachedClass(Env::class);

        $properties = $environmentMetadata->properties;

        foreach ($properties as $property) {
            $variableName = preg_replace('/([A-Z])/', '_$1', $property->name);
            $variableName = mb_convert_case($variableName, MB_CASE_UPPER);

            $value = getenv($variableName);

            if ($value === false && !$property->allowsNull) {
                throw new AppException("The environment variable `$variableName` is required");
            }

            $value = $value ?: null;

            self::castType($value, $property, $environment);

            $attributes = $property->getCachedAttributes(EnvValidator::class);

            foreach ($attributes as $attribute) {
                $attribute->value = $value;
                $attribute->name = $property->name;

                $attribute->validate();
            }
        }
    }

    /**
     * @throws \App\Core\AppException
     */
    private static function castType(?string $value, Property $property, Env $environment): void
    {
        $propertyName = $property->name;

        if ($value === null) {
            $environment->{$propertyName} = null;
            return;
        }

        $typeName = $property->type;

        if (is_a($typeName, BackedEnum::class, true)) {
            $environment->{$propertyName} = $typeName::from($value);
        } else {
            $environment->{$propertyName} = match ($typeName) {
                'int' => (int)$value,
                'bool' => (bool)$value,
                'float' => (float)$value,
                'string' => $value,
                default => throw new AppException("Type `$typeName` not supported for property `$propertyName`"),
            };
        }
    }
}