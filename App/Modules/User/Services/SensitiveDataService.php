<?php

namespace App\Modules\User\Services;

use App\Env;

class SensitiveDataService
{
    private static function getCypherAlgo(): string
    {
        return Env::getInstance()->cypherAlgo;
    }

    private static function getPassphrase(): string
    {
        return Env::getInstance()->encryptionPassphrase;
    }

    public function encrypt($data): string
    {
        $cypher_algo = self::getCypherAlgo();

        $ivlen = openssl_cipher_iv_length($cypher_algo);
        $iv = openssl_random_pseudo_bytes($ivlen);

        $cyphertext = openssl_encrypt($data, $cypher_algo, self::getPassphrase(), OPENSSL_RAW_DATA, $iv);

        return $iv . $cyphertext;
    }

    public function decrypt($cyphertext): false|string
    {
        $cypher_algo = self::getCypherAlgo();
        $ivlen = openssl_cipher_iv_length($cypher_algo);
        $iv = substr($cyphertext, 0, $ivlen);

        $cyphertext = substr($cyphertext, $ivlen);

        return openssl_decrypt($cyphertext, self::getCypherAlgo(), self::getPassphrase(), true, $iv);
    }
}