<?php

namespace App\Core\Environment\Validators;

use Attribute;
use BackedEnum;
use App\Core\AppException;
use ValueError;

#[Attribute]
class OpensslCipherAlgo extends EnvValidator
{
    /**
     * @throws \App\Core\AppException
     */
    public function validate(): void
    {
        $opensslCipherMethods = openssl_get_cipher_methods();
        $validCypherMethod = in_array($this->value, $opensslCipherMethods);

        if (!$validCypherMethod) {
            $opensslCypherMethods = implode(', ', $opensslCipherMethods);
            $message = "Invalid OPENSSL_CIPHER_ALGO value: `$this->value`. Valid values are: $opensslCypherMethods";

            throw new AppException($message);
        }
    }
}
