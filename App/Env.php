<?php

namespace App;

use App\Core\Environment\BaseEnv;
use App\Core\Environment\Enums\Environment;
use App\Core\Environment\Validators\Enum;
use App\Core\Environment\Validators\OpensslCipherAlgo;

class Env extends BaseEnv
{
    #[Enum(Environment::class)]
    public Environment $environment;

    public string $dbHost;
    public string $dbDatabase;
    public string $dbUsername;
    public string $dbPassword;
    public string $encryptionPassphrase;

    #[OpensslCipherAlgo]
    public string $cypherAlgo;
}