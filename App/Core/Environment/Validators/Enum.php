<?php

namespace App\Core\Environment\Validators;

use Attribute;
use BackedEnum;
use App\Core\AppException;
use ValueError;

#[Attribute]
class Enum extends EnvValidator
{
    /**
     * @param class-string<BackedEnum> $enum
     */
    public function __construct(private readonly string $enum)
    {
    }

    /**
     * @throws \App\Core\AppException
     */
    public function validate(): void
    {
        try {
            /** @noinspection PhpExpressionResultUnusedInspection */
            $this->enum::from($this->value);
        } catch (ValueError) {
            $cases = $this->enum::cases();
            $caseValues = array_map(fn($case) => $case->value, $cases);
            $validValues = implode(', ', $caseValues);

            $message = "Invalid value for environment variable $this->name. Valid values are: $validValues.";

            throw new AppException($message);
        }
    }
}
