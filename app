#!/usr/bin/env php

<?php

use App\Core\Console\Command\Command;

require_once __DIR__ . '/vendor/autoload.php';

const INCORRECT_USAGE = 1;
const FILE_NOT_FOUND = 2;
const CLASS_IS_NOT_COMMAND = 3;

if (count($argv) < 2) {
    Command::error("Incorrect usage");
    Command::info("Usage: php $argv[0] <file>\n");

    exit(INCORRECT_USAGE);
}

function toPascalCase(string $input): ?string
{
    return preg_replace_callback(
        '/-([a-z])/',
        fn($matches) => strtoupper($matches[1]),
        $input
    );
}

function getFilePathFromSignature(string $signature): string
{
    $signature = mb_ucfirst($signature);

    $parts = explode(':', $signature);
    $parts = array_map(toPascalCase(...), $parts);
    $parts = array_map(ucfirst(...), $parts);

    $partialFilePath = implode('/', $parts);

    return "bin/$partialFilePath.php";
}

$signature = $argv[1];

$filePath = getFilePathFromSignature($signature);

if (!file_exists($filePath)) {
    Command::error("File `$filePath` not found\n");
    exit(FILE_NOT_FOUND);
}

$command = include_once $filePath;

if (!$command instanceof \App\Core\Console\Command\Command) {
    Command::error("Class `" . $command::class . "` should extend `\App\Core\Console\Command\Command`\n");
    exit(CLASS_IS_NOT_COMMAND);
}

$params = $command->parameters;
$paramsCount = count($params);

$paramNames = [];

foreach ($params as $name => $param) {
    $paramNames[] = $name;
    $params[$name]['optional'] ??= false;
}

$arguments = array_slice($argv, 2);
$argumentsCount = count($arguments);

$minParamsCount = count(array_filter($params, fn($param) => !$param['optional']));

if ($argumentsCount < $minParamsCount) {
    Command::error("Not enough arguments");
    Command::info("Usage: php $argv[0] $signature " . implode(' ', array_map(fn($param, $name) => $param['optional'] ? "[<$name>]" : "<$name>", $params, $paramNames)) . "\n");

    exit(INCORRECT_USAGE);
} elseif ($argumentsCount > $paramsCount) {
    Command::info("Ignoring extra arguments: " . implode(' ', array_slice($arguments, $paramsCount)) . "\n");

    exit(INCORRECT_USAGE);
}

foreach ($params as $name => $param) {
    if ($param['optional'] && !isset($arguments[array_search($name, $paramNames)])) {
        $arguments[array_search($name, $paramNames)] = $param['default'];
    }
}

$command->handle(...$arguments);
