<?php

namespace App\Core\Environment\Validators;

use Attribute;
use App\Core\AppException;

#[Attribute]
abstract class EnvValidator
{
    public string $value;
    public string $name;

    /**
     * @return void
     * @throws \App\Core\AppException
     */
    abstract public function validate(): void;

    /**
     * @param class-string<EnvValidator> $validatorClass
     * @param array $constructorArgs
     * @return bool
     */
    protected function call(string $validatorClass, array $constructorArgs = []): bool
    {
        try {
            $validator = (new $validatorClass(...$constructorArgs));

            $validator->name = $this->name;
            $validator->value = $this->value;

            $validator->validate();

            return true;
        } catch (AppException) {
            return false;
        }
    }
}