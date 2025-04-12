<?php

namespace App\Core\Database;

use App\Core\Database\Model as T;
use App\Env;
use PDO;
use PDOStatement;
use Symfony\Component\String\UnicodeString;

class Database extends PDO
{
    private static Database $instance;

    private function __construct()
    {
        $env = Env::getInstance();

        $host = $env->dbHost;
        $database = $env->dbDatabase;
        $username = $env->dbUsername;
        $password = $env->dbPassword;

        parent::__construct("mysql:host=$host;dbname=$database", $username, $password);

        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public static function getInstance(): PDO
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function execWithParams(string $sql, array $params): void
    {
        self::getInstance()
            ->prepare($sql)
            ->execute($params);
    }

    /**
     * @template T of Model
     *
     * @param string $sql
     * @param array $params
     * @param class-string<T> $class
     * @return T[]
     * @throws \ReflectionException
     */
    public static function fetchObjects(string $sql, array $params, string $class): array
    {
        $stmt = self::prepareStatement($sql, $params);

        $resultArray = $stmt->fetchAll();

        return array_map(
            fn(array $row) => self::instantiateModel($row, $class),
            $resultArray
        );
    }

    /**
     * @template T of Model
     *
     * @param string $sql
     * @param array $params
     * @param class-string<T> $class
     * @return null|T
     * @throws \ReflectionException
     */
    public static function fetchObject(string $sql, array $params, string $class)
    {
        $stmt = self::prepareStatement($sql, $params);
        $result = $stmt->fetch();

        if ($result === false) {
            return null;
        }

        return self::instantiateModel($result, $class);
    }

    /**
     * @template T of Model
     *
     * @param class-string<T> $class
     * @return T
     * @throws \ReflectionException
     */
    private static function instantiateModel(mixed $row, string $class)
    {
        $props = [];

        foreach ($row as $columnName => $columnValue) {
            $propName = (new UnicodeString($columnName))
                ->camel()
                ->toString();

            $props[$propName] = $columnValue;
        }

        return $class::rawInstance($props);
    }

    /**
     * @param string $sql
     * @param array $params
     * @return PDOStatement
     * @throws \PDOException
     */
    private static function prepareStatement(string $sql, array $params): PDOStatement
    {
        $stmt = self::getInstance()
            ->prepare($sql);

        $stmt->execute($params);
        return $stmt;
    }
}