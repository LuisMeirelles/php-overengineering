<?php

declare(strict_types=1);

namespace App\Core\Console\Command;

/**
 * Class Command
 *
 * The signature is inferred from command implementation location.
 * Each `:` denotes a subfolder of /bin (relative to your project root), and the commands should be kebab-cased
 *
 * @package App\Core\Console\Command
 */
abstract class Command
{
    use TerminalStylable;

    public string $description;
    public array $parameters = [];

    abstract public function handle(string ...$args): void;
}
